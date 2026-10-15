<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ProviderStatus;
use App\Models\Category;
use App\Models\Claim;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\RequestReceived;
use App\Rules\BelgianCompanyNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Notification::fake();
    }

    /**
     * A tiny real PDF: the validation sniffs the content, not the file name.
     */
    private function pdf(string $name = 'extrait-bce.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n");
    }

    private function claim(User $user, Provider $provider, array $data = []): TestResponse
    {
        return $this->actingAs($user)->post('/fr/pro/'.$provider->slug.'/reclamer', $data + [
            'action' => 'manage',
            'bce_number' => '0403 170 701',
            'position' => 'Gérante',
            'message' => 'Je suis la gérante du salon.',
            'proof' => $this->pdf(),
        ]);
    }

    public function test_belgian_company_number_checks_the_modulo_97(): void
    {
        $this->assertSame('0403.170.701', BelgianCompanyNumber::normalize('0403170701'));
        $this->assertSame('0403.170.701', BelgianCompanyNumber::normalize('BE 0403.170.701'));
        $this->assertNull(BelgianCompanyNumber::normalize('0403.170.702'));
        $this->assertNull(BelgianCompanyNumber::normalize('2403.170.701'));
        $this->assertNull(BelgianCompanyNumber::normalize('403170701'));
    }

    public function test_visitors_are_sent_to_the_pro_sign_up_and_come_back_to_the_claim(): void
    {
        $provider = Provider::factory()->create();

        $this->get('/fr/pro/'.$provider->slug.'/reclamer')->assertRedirect('/fr/inscription?type=pro');
        $this->assertStringEndsWith('/fr/pro/'.$provider->slug.'/reclamer', session('url.intended'));
    }

    public function test_owner_accounts_and_unverified_pros_cannot_claim(): void
    {
        $provider = Provider::factory()->create();

        $this->actingAs(User::factory()->create())->get('/fr/pro/'.$provider->slug.'/reclamer')->assertForbidden()->assertSee(__('pro.not_pro.title'));
        $this->actingAs(User::factory()->pro()->unverified()->create())->get('/fr/pro/'.$provider->slug.'/reclamer')->assertRedirect('/fr/verifier-email');
    }

    public function test_pro_claims_a_listing_with_a_private_proof_and_gets_a_confirmation(): void
    {
        $pro = User::factory()->pro()->create();
        $provider = Provider::factory()->create();

        $this->actingAs($pro)->get('/fr/pro/'.$provider->slug.'/reclamer')->assertOk()->assertSee($provider->name);
        $this->claim($pro, $provider)->assertRedirect('/fr/mon-compte/espace-pro')->assertSessionHasNoErrors();

        $claim = Claim::query()->sole();
        $this->assertSame('0403.170.701', $claim->bce_number);
        $this->assertSame('Gérante', $claim->position);
        $this->assertSame(ClaimStatus::Pending, $claim->status);
        $this->assertSame('fr', $claim->locale);
        $this->assertStringStartsWith('proofs/', $claim->proof_path);
        $this->assertStringEndsWith('.pdf', $claim->proof_path);
        Storage::disk('local')->assertExists($claim->proof_path);
        Storage::disk('public')->assertMissing($claim->proof_path);
        Notification::assertSentTo($pro, RequestReceived::class);
    }

    public function test_invalid_company_number_and_wrong_file_types_are_refused(): void
    {
        $pro = User::factory()->pro()->create();
        $provider = Provider::factory()->create();

        $this->claim($pro, $provider, ['bce_number' => '0403.170.702'])->assertSessionHasErrors('bce_number');
        // An executable renamed to .pdf: the sniffed MIME type is not a PDF.
        $path = tempnam(sys_get_temp_dir(), 'proof');
        file_put_contents($path, "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00\xFF\xFF\x00\x00".str_repeat("\x00", 64).'This program cannot be run in DOS mode.');
        $this->claim($pro, $provider, ['proof' => new UploadedFile($path, 'preuve.pdf', 'application/pdf', null, true)])->assertSessionHasErrors('proof');
        $this->claim($pro, $provider, ['proof' => UploadedFile::fake()->create('preuve.docx', 10, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')])->assertSessionHasErrors('proof');
        $this->claim($pro, $provider, ['proof' => UploadedFile::fake()->create('huge.pdf', 6000, 'application/pdf')])->assertSessionHasErrors('proof');

        $this->assertDatabaseCount('claims', 0);
    }

    public function test_only_one_pending_claim_per_listing_and_user(): void
    {
        $pro = User::factory()->pro()->create();
        $provider = Provider::factory()->create();

        $this->claim($pro, $provider)->assertSessionHasNoErrors();
        $this->claim($pro, $provider)->assertSessionHasErrors('form');
        $this->actingAs($pro)->get('/fr/pro/'.$provider->slug.'/reclamer')->assertSee(__('pro.claim.pending_title'));

        $this->claim(User::factory()->pro()->create(), $provider)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('claims', 2);
    }

    public function test_hidden_and_removed_listings_cannot_be_claimed(): void
    {
        $pro = User::factory()->pro()->create();

        $this->actingAs($pro)->get('/fr/pro/'.Provider::factory()->hidden()->create()->slug.'/reclamer')->assertNotFound();
        $this->claim($pro, Provider::factory()->hidden()->create())->assertNotFound();
    }

    public function test_pro_adds_a_missing_business_as_a_hidden_listing_geocoded_on_the_postal_code(): void
    {
        $pro = User::factory()->pro()->create();
        PostalCode::factory()->create(['code' => '4000', 'name' => 'Liège', 'lat' => 50.60, 'lng' => 5.50, 'region' => 'wallonia']);
        PostalCode::factory()->create(['code' => '4000', 'name' => 'Rocourt', 'lat' => 50.68, 'lng' => 5.54, 'region' => 'wallonia']);
        $category = Category::factory()->create(['slug' => 'toiletteur-salon']);

        $this->actingAs($pro)->get('/fr/ajouter-mon-entreprise')->assertOk();
        $this->actingAs($pro)->post('/fr/ajouter-mon-entreprise', [
            'name' => 'Toilettage Câlin',
            'street' => 'Rue de la Paix',
            'house_number' => '12',
            'postal_code' => '4000',
            'phone' => '0478 12 34 56',
            'website' => 'https://calin.be',
            'categories' => [$category->slug],
            'bce_number' => '0876.543.270',
            'position' => 'Fondatrice',
            'proof' => $this->pdf(),
        ])->assertRedirect('/fr/mon-compte/espace-pro')->assertSessionHasNoErrors();

        $provider = Provider::query()->where('name', 'Toilettage Câlin')->sole();
        $this->assertSame(ProviderStatus::Hidden, $provider->status);
        $this->assertSame('Liège', $provider->city);
        $this->assertSame('+32478123456', $provider->phone);
        $this->assertEqualsWithDelta(50.64, (float) $provider->lat, 0.0001);
        $this->assertEqualsWithDelta(5.52, (float) $provider->lng, 0.0001);
        $this->assertNull($provider->claimed_by_user_id);
        $this->assertTrue($provider->categories->contains($category));
        $this->assertTrue($provider->claims()->sole()->creates_listing);

        $this->get('/fr/pro/'.$provider->slug)->assertNotFound();
    }

    public function test_new_listing_website_must_be_http(): void
    {
        $pro = User::factory()->pro()->create();

        $this->actingAs($pro)->post('/fr/ajouter-mon-entreprise', ['website' => 'javascript://alert(1)'])->assertSessionHasErrors('website');
        $this->actingAs($pro)->post('/fr/ajouter-mon-entreprise', ['website' => 'ftp://example.be'])->assertSessionHasErrors('website');
    }

    public function test_permanently_deleting_a_listing_deletes_the_proof_files(): void
    {
        $provider = Provider::factory()->create();
        Storage::disk('local')->put('proofs/claim.pdf', 'x');
        Storage::disk('local')->put('proofs/badge.pdf', 'x');
        Claim::factory()->for($provider)->create(['proof_path' => 'proofs/claim.pdf']);
        $badgeRequest = $provider->badgeRequests()->make(['regulatory_number' => 'ORD-1']);
        $badgeRequest->forceFill(['user_id' => User::factory()->pro()->create()->id, 'proof_path' => 'proofs/badge.pdf'])->save();

        $provider->forceDelete();

        Storage::disk('local')->assertMissing('proofs/claim.pdf');
        Storage::disk('local')->assertMissing('proofs/badge.pdf');
        $this->assertDatabaseCount('claims', 0);
    }
}
