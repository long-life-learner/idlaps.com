<?php

namespace Tests\Feature;

use App\Models\Event;
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['/event', 'INI RUN FEST 2026'])]
    #[TestWith(['/hasil', 'https://inirun.idlaps.com/'])]
    #[TestWith(['/berita', 'Cosmo Run 2026'])]
    #[TestWith(['/konsultasi', 'admin@idlaps.com'])]
    public function test_public_pages_render_useful_content_without_javascript(string $path, string $content): void
    {
        $this->seed(EventSeeder::class);
        $this->get($path)->assertOk()->assertSee($content);
    }

    public function test_event_page_links_to_the_correct_official_result(): void
    {
        $this->seed(EventSeeder::class);
        $this->get('/event/ini-run-fest-2026')
            ->assertOk()
            ->assertSee('AEON Mall Deltamas, Cikarang')
            ->assertSee('https://inirun.idlaps.com/');
    }

    public function test_event_without_results_does_not_invent_a_result_link(): void
    {
        $this->seed(EventSeeder::class);
        $this->get('/event/bukit-batu-2023')
            ->assertOk()
            ->assertSee('Tautan hasil event ini belum tersedia')
            ->assertDontSee('Lihat hasil lomba');
    }

    public function test_unknown_event_returns_not_found(): void
    {
        $this->get('/event/unknown-event')->assertNotFound();
    }

    public function test_staging_pages_are_not_indexable_by_default(): void
    {
        config(['idlaps.indexable' => false]);

        $this->get('/')->assertOk()->assertSee('content="noindex, nofollow"', false);
    }

    public function test_production_pages_can_be_indexed_when_enabled(): void
    {
        config(['idlaps.indexable' => true]);

        $this->get('/')->assertOk()->assertSee('content="index, follow"', false);
    }

    public function test_staging_host_stays_unindexed_when_production_indexing_is_enabled(): void
    {
        config(['idlaps.indexable' => true, 'app.url' => 'https://idlaps.com']);

        $this->get('https://staging.idlaps.com/')->assertOk()->assertSee('content="noindex, nofollow"', false);
        $this->get('https://idlaps.com/')->assertOk()->assertSee('content="index, follow"', false);
    }

    public function test_demo_remains_unindexed_and_labelled_even_when_production_is_indexable(): void
    {
        config(['idlaps.indexable' => true]);

        $this->get('/hasil/demo')->assertOk()
            ->assertSee('content="noindex, nofollow"', false)
            ->assertSee('Bukan hasil lomba resmi.');
    }

    public function test_event_title_and_serialized_data_are_escaped(): void
    {
        $event = Event::factory()->create(['name' => '<script>alert("unsafe")</script>', 'published' => true]);

        $this->get('/event')->assertOk()
            ->assertSee($event['name'])
            ->assertDontSee($event['name'], false);
    }
}
