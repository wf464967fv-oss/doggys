<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function pages(): array
    {
        $data = [];
        foreach (['fr', 'nl', 'en', 'de'] as $locale) {
            foreach (['notice', 'privacy', 'terms', 'cookies'] as $page) {
                $data["$locale $page"] = [$locale, $page];
            }
        }

        return $data;
    }

    #[DataProvider('pages')]
    public function test_legal_page_is_served_with_its_own_title_in_every_language(string $locale, string $page): void
    {
        app()->setLocale($locale);
        $title = __("legal.$page.meta_title");

        $this->get(localized_route("legal.$page", locale: $locale))
            ->assertOk()
            ->assertSee("<title>$title | Doggys</title>", false)
            ->assertSee(__('legal.toc'))
            ->assertSee('<html lang="'.$locale.'"', false);
    }

    #[DataProvider('pages')]
    public function test_placeholder_is_shown_when_identity_is_empty_and_hidden_once_configured(string $locale, string $page): void
    {
        app()->setLocale($locale);
        $url = localized_route("legal.$page", locale: $locale);
        $todo = __('legal.placeholder');

        config(['doggys.legal' => ['bce' => null, 'address' => null, 'email' => null]]);
        if ($page !== 'cookies' && $page !== 'terms') {
            $this->get($url)->assertSee($todo);
        }

        config(['doggys.legal' => ['bce' => '0123.456.789', 'address' => 'Rue Test 1, 4000 Liège', 'email' => 'legal@example.test']]);
        $response = $this->get($url)->assertOk()->assertDontSee($todo);
        if ($page === 'notice') {
            $response->assertSee('0123.456.789')->assertSee('legal@example.test');
        }
    }

    public function test_service_worker_file_is_versioned_and_excludes_private_areas(): void
    {
        $js = file_get_contents(public_path('sw.js'));

        $this->assertMatchesRegularExpression("/const VERSION = 'doggys-v\d+'/", $js);
        $this->assertStringContainsString('hors-ligne', $js);
        $this->assertStringContainsString("request.method !== 'GET'", $js);
        $this->assertStringContainsString('admin|api', $js);
    }

    #[DataProvider('locales')]
    public function test_offline_page_is_translated_and_not_indexed(string $locale): void
    {
        app()->setLocale($locale);

        $this->get(localized_route('offline', locale: $locale))
            ->assertOk()
            ->assertSee(__('ui.offline_title'))
            ->assertSee('noindex', false);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function locales(): array
    {
        return ['fr' => ['fr'], 'nl' => ['nl'], 'en' => ['en'], 'de' => ['de']];
    }

    public function test_service_worker_is_only_flagged_in_production(): void
    {
        $this->get('/fr/hors-ligne')->assertOk()->assertDontSee('data-sw', false);

        $this->app['env'] = 'production';
        $this->get('/fr/hors-ligne')->assertOk()->assertSee('data-sw', false);
    }

    public function test_view_routes_have_clean_canonical_and_reciprocal_hreflang(): void
    {
        foreach (['reviews.policy', 'legal.privacy'] as $name) {
            $html = $this->get(localized_route($name, locale: 'fr'))->assertOk()->getContent();

            $this->assertStringContainsString('<link rel="canonical" href="'.localized_route($name, locale: 'fr').'">', $html);
            foreach (['fr', 'nl', 'en', 'de'] as $locale) {
                $this->assertStringContainsString('hreflang="'.$locale.'" href="'.localized_route($name, locale: $locale).'"', $html);
            }
            $this->assertStringNotContainsString('?view=', $html);
        }
    }
}
