<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent(): void
    {
        $response = $this->get('/fr/connexion');

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy')
            ->assertHeaderMissing('Strict-Transport-Security');

        $this->assertStringContainsString('geolocation=(self)', $response->headers->get('Permissions-Policy'));

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString('https://tile.openstreetmap.org', $csp);
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[A-Za-z0-9]+'/", $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
    }

    public function test_error_pages_also_get_security_headers(): void
    {
        $this->get('/fr/nope')->assertNotFound()->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_privately_served_files_keep_their_sandbox_policy(): void
    {
        $path = 'tests/csp-'.uniqid().'.html';
        Storage::disk('local')->put($path, '<script>alert(1)</script>');

        try {
            $response = $this->get(Storage::disk('local')->temporaryUrl($path, now()->addMinutes(5)));
        } finally {
            Storage::disk('local')->delete($path);
        }

        $response->assertOk();
        $this->assertStringContainsString('sandbox', $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("default-src 'none'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_absolute_urls_ignore_the_request_host_header(): void
    {
        $this->seed(CategorySeeder::class);

        $this->get('http://evil.example/fr')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.rtrim(config('app.url'), '/').'/fr">', false)
            ->assertDontSee('evil.example');
    }

    public function test_hsts_is_sent_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->get('/fr/connexion')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
