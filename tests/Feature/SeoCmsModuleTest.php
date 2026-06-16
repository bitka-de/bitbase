<?php

namespace Tests\Feature;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoCmsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_tags_are_rendered_for_published_page(): void
    {
        $page = Page::query()->create([
            'title' => 'SEO Seite',
            'slug' => 'seo-seite',
            'status' => PageStatus::Published->value,
            'robots_index' => 'index',
            'robots_follow' => 'follow',
            'seo_title' => 'Mein SEO Titel',
            'meta_description' => 'Dies ist eine optimierte Description.',
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertSee('<title>Mein SEO Titel</title>', false);
        $response->assertSee('name="description"', false);
    }

    public function test_sitemap_contains_only_indexable_published_pages(): void
    {
        Page::query()->create([
            'title' => 'Live',
            'slug' => 'live',
            'status' => PageStatus::Published->value,
            'robots_index' => 'index',
            'robots_follow' => 'follow',
            'sitemap_include' => true,
        ]);

        Page::query()->create([
            'title' => 'Noindex',
            'slug' => 'noindex',
            'status' => PageStatus::Published->value,
            'robots_index' => 'noindex',
            'robots_follow' => 'follow',
            'sitemap_include' => true,
        ]);

        Page::query()->create([
            'title' => 'Draft',
            'slug' => 'draft',
            'status' => PageStatus::Draft->value,
            'robots_index' => 'index',
            'robots_follow' => 'follow',
            'sitemap_include' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('/live', false);
        $response->assertDontSee('/noindex', false);
        $response->assertDontSee('/draft', false);
    }

    public function test_slug_change_creates_redirect(): void
    {
        $page = Page::query()->create([
            'title' => 'Test',
            'slug' => 'alt-url',
            'status' => PageStatus::Published->value,
            'robots_index' => 'index',
            'robots_follow' => 'follow',
        ]);

        $page->update(['slug' => 'neu-url']);

        $this->get('/alt-url')->assertRedirect('/neu-url');
    }

    public function test_draft_pages_are_not_publicly_visible(): void
    {
        Page::query()->create([
            'title' => 'Entwurf',
            'slug' => 'entwurf',
            'status' => PageStatus::Draft->value,
            'robots_index' => 'noindex',
            'robots_follow' => 'nofollow',
        ]);

        $this->get('/entwurf')->assertNotFound();
    }
}
