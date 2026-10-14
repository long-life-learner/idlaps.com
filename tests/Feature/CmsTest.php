<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\EventSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function eventInput(array $overrides = []): array
    {
        return [...[
            'name' => 'Lampung Triathlon 2026', 'sport' => 'Triathlon', 'year' => 2026, 'event_date' => '2026-05-24',
            'location' => 'Bandar Lampung', 'description' => 'Dokumentasi timing event triathlon di Lampung.',
            'categories_text' => 'Sprint, Olympic', 'result_url' => 'https://triathlon.idlaps.com/',
            'published' => 0, 'featured' => 0, 'article_published' => 0, 'image_focus' => 50, 'action' => 'save',
        ], ...$overrides];
    }

    public function test_admin_can_create_an_event_with_photos_and_an_unpublished_article_draft(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin())->post('/admin/events', $this->eventInput([
            'photos' => [UploadedFile::fake()->image('race.jpg')], 'slug' => 'injected-slug',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $event = Event::sole();
        $this->assertSame('lampung-triathlon-2026', $event->slug);
        $this->assertSame(['Sprint', 'Olympic'], $event->categories);
        $this->assertFalse($event->published);
        $this->assertFalse($event->article_published);
        $this->assertStringContainsString('24 Mei 2026', $event->article_body);
        $this->assertStringContainsString($event->description, $event->article_body);
        $this->assertCount(1, $event->gallery);
        Storage::disk('local')->assertExists($event->gallery[0]['path']);
        $this->assertSame($event->gallery[0]['id'], $event->cover_id);
    }

    public function test_seed_import_preserves_edits_and_archived_events(): void
    {
        $this->seed(EventSeeder::class);
        $count = Event::count();
        $event = Event::firstWhere('slug', 'locomotive-run-2025');
        $this->assertSame('2025-04-27', $event->event_date->format('Y-m-d'));
        $event->update(['name' => 'Edited in CMS']);
        $event->delete();
        $this->seed(EventSeeder::class);
        $this->assertSame($count, Event::withTrashed()->count());
        $this->assertSame('Edited in CMS', $event->fresh()->name);
        $this->assertSoftDeleted($event);
        $this->assertNull(Event::firstWhere('slug', 'tkg-half-marathon-2024')->event_date);
    }

    public function test_drafts_are_hidden_from_every_public_surface(): void
    {
        $event = Event::factory()->create(['name' => 'Secret unpublished race', 'featured' => true, 'article_published' => true, 'article_body' => 'Private draft']);
        foreach (['/', '/event', '/hasil', '/berita'] as $path) {
            $this->get($path)->assertOk()->assertDontSee($event->name);
        }
        $this->get('/event/'.$event->slug)->assertNotFound();
        $this->get('/berita/'.$event->slug)->assertNotFound();
    }

    public function test_published_event_and_article_appear_publicly_with_escaped_text(): void
    {
        $event = Event::factory()->create(['published' => true, 'featured' => true, 'article_published' => true,
            'article_title' => 'Liputan race', 'article_body' => '<script>alert("unsafe")</script>']);
        $this->get('/')->assertOk()->assertSee($event->name);
        $this->get('/berita')->assertOk()->assertSee(route('news.show', $event->slug));
        $this->get('/berita/'.$event->slug)->assertOk()->assertSee($event->article_body)->assertDontSee($event->article_body, false);
    }

    public function test_draft_preview_requires_admin_and_remains_noindex(): void
    {
        $event = Event::factory()->create();
        $this->get(route('admin.events.preview', $event))->assertRedirect(route('login'));
        config(['idlaps.indexable' => true]);
        $this->actingAs($this->admin())->get(route('admin.events.preview', $event))->assertOk()
            ->assertSee($event->name)->assertSee('content="noindex, nofollow"', false);
    }

    public function test_update_publishes_content_without_changing_slug(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($this->admin())->put(route('admin.events.update', $event), $this->eventInput([
            'revision' => 1, 'published' => 1, 'featured' => 1, 'article_published' => 1,
            'article_title' => 'Cerita event', 'article_body' => 'Dokumentasi yang telah diperiksa.',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $event->refresh();
        $this->assertTrue($event->published);
        $this->assertTrue($event->article_published);
        $this->assertSame(2, $event->revision);
        $this->get('/event/'.$event->slug)->assertOk()->assertSee('Lampung Triathlon 2026');
    }

    public function test_stale_editor_cannot_overwrite_newer_changes(): void
    {
        $event = Event::factory()->create(['revision' => 3]);
        $this->actingAs($this->admin())->put(route('admin.events.update', $event), $this->eventInput(['revision' => 2]))
            ->assertSessionHasErrors('revision');
        $this->assertSame($event->name, $event->fresh()->name);
    }

    public function test_regeneration_replaces_article_with_a_draft(): void
    {
        $event = Event::factory()->create(['published' => true, 'article_published' => true, 'article_body' => 'Earlier article']);
        $this->actingAs($this->admin())->put(route('admin.events.update', $event), $this->eventInput([
            'revision' => 1, 'action' => 'generate', 'article_body' => 'Earlier article',
        ]))->assertSessionHasNoErrors();
        $this->assertFalse($event->fresh()->article_published);
        $this->assertStringContainsString('Lampung', $event->fresh()->article_body);
        $this->assertStringNotContainsString('Earlier article', $event->fresh()->article_body);
    }

    public function test_archive_hides_event_and_restore_returns_it_as_a_draft(): void
    {
        $event = Event::factory()->create(['published' => true, 'article_published' => true]);
        $this->actingAs($this->admin())->delete(route('admin.events.destroy', $event))->assertRedirect(route('admin.events.index'));
        $this->assertSoftDeleted($event);
        $this->get('/event/'.$event->slug)->assertNotFound();
        $this->post(route('admin.events.restore', $event->id))->assertRedirect(route('admin.events.edit', $event));
        $this->assertFalse($event->fresh()->published);
        $this->assertFalse($event->fresh()->article_published);
        $this->assertNull($event->fresh()->deleted_at);
    }

    #[TestWith(['result_url', 'javascript:alert(1)'])]
    #[TestWith(['legacy_article_url', 'data:text/html,bad'])]
    #[TestWith(['event_date', '2025-04-27'])]
    #[TestWith(['event_date', ['malformed']])]
    #[TestWith(['year', ['malformed']])]
    #[TestWith(['image_focus', 110])]
    #[TestWith(['cover_id', 'not-owned'])]
    #[TestWith(['sport', 'invalid'])]
    public function test_invalid_event_input_is_rejected(string $field, mixed $value): void
    {
        $this->actingAs($this->admin())->post('/admin/events', $this->eventInput([$field => $value]))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('events', 0);
    }

    public function test_article_requires_title_and_body_before_publishing(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($this->admin())->put(route('admin.events.update', $event), $this->eventInput(['revision' => 1, 'article_published' => 1]))
            ->assertSessionHasErrors(['article_title', 'article_body']);
        $this->assertFalse($event->fresh()->article_published);
    }

    public function test_unsafe_and_oversized_uploads_are_rejected(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin())->post('/admin/events', $this->eventInput(['photos' => [
            UploadedFile::fake()->createWithContent('payload.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->image('large.jpg')->size(8193),
        ]]))->assertSessionHasErrors(['photos.0', 'photos.1']);
        $this->assertDatabaseCount('events', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_media_is_served_only_when_owned_and_event_is_public_or_admin_is_logged_in(): void
    {
        Storage::fake('local');
        $event = Event::factory()->create();
        $path = 'event-photos/'.$event->id.'/photo.jpg';
        Storage::disk('local')->put($path, 'image-content');
        $event->update(['gallery' => [['id' => 'photo', 'path' => $path, 'image' => '/event-media/'.$event->id.'/photo.jpg', 'alt' => 'Race', 'caption' => 'Race']]]);
        $url = route('event-media', ['event' => $event, 'filename' => 'photo.jpg']);
        $this->get($url)->assertNotFound();
        $this->actingAs($this->admin())->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get(route('event-media', ['event' => $event, 'filename' => 'other.jpg']))->assertNotFound();
        Storage::disk('local')->assertExists($path);
    }

    public function test_cover_falls_back_when_removed_and_other_captions_remain_editable(): void
    {
        $gallery = [
            ['id' => 'one', 'image' => '/media/one.jpg', 'alt' => 'First', 'caption' => 'First'],
            ['id' => 'two', 'image' => '/media/two.jpg', 'alt' => 'Second', 'caption' => 'Second'],
        ];
        $event = Event::factory()->create(['gallery' => $gallery, 'cover_id' => 'one']);
        $this->actingAs($this->admin())->put(route('admin.events.update', $event), $this->eventInput([
            'revision' => 1, 'cover_id' => 'one', 'remove_photos' => ['one'], 'captions' => ['two' => 'Updated caption'], 'alt_texts' => ['two' => 'Updated alt'],
        ]))->assertSessionHasNoErrors();
        $event->refresh();
        $this->assertSame('two', $event->cover_id);
        $this->assertCount(1, $event->gallery);
        $this->assertSame('Updated caption', $event->gallery[0]['caption']);
        $this->assertSame('Updated alt', $event->gallery[0]['alt']);
    }

    public function test_editor_and_public_metadata_escape_untrusted_titles_and_descriptions(): void
    {
        $payload = '</title><script>alert("unsafe")</script>';
        $event = Event::factory()->create(['published' => true, 'article_published' => true,
            'name' => $payload, 'description' => $payload, 'article_title' => $payload, 'article_body' => 'Text']);
        $this->get('/event/'.$event->slug)->assertOk()->assertSee($payload)->assertDontSee($payload, false);
        $this->get('/berita/'.$event->slug)->assertOk()->assertSee($payload)->assertDontSee($payload, false);
        $this->actingAs($this->admin())->get(route('admin.events.edit', $event))->assertOk()->assertSee($payload)->assertDontSee($payload, false);
    }

    public function test_published_upload_is_public_and_removing_it_cleans_up_only_its_owned_file(): void
    {
        Storage::fake('local');
        $event = Event::factory()->create(['published' => true]);
        $path = 'event-photos/'.$event->id.'/photo.jpg';
        Storage::disk('local')->put($path, 'photo');
        Storage::disk('local')->put('unrelated.jpg', 'keep');
        $event->update(['gallery' => [['id' => 'photo', 'path' => $path, 'image' => '/event-media/'.$event->id.'/photo.jpg', 'alt' => 'Race', 'caption' => 'Race']]]);
        $this->get(route('event-media', ['event' => $event, 'filename' => 'photo.jpg']))->assertOk();
        $this->actingAs($this->admin())->put(route('admin.events.update', $event), $this->eventInput(['revision' => 1, 'remove_photos' => ['photo']]))->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($path);
        Storage::disk('local')->assertExists('unrelated.jpg');
        $this->assertSame([], $event->fresh()->gallery);
    }

    public function test_generated_title_fits_storage_limit_and_future_year_is_not_labelled_finished(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 14));
        $this->actingAs($this->admin())->post('/admin/events', $this->eventInput([
            'name' => str_repeat('A', 200), 'location' => str_repeat('B', 255), 'event_date' => null, 'year' => 2027,
        ]))->assertSessionHasNoErrors();
        $event = Event::sole();
        $this->assertLessThanOrEqual(255, mb_strlen($event->article_title));
        $this->assertSame('AKAN DATANG', $event->catalogueEntry()['status_label']);
    }

    public function test_dashboard_filters_and_editors_render(): void
    {
        $event = Event::factory()->create(['name' => 'Published event', 'published' => true]);
        Event::factory()->create(['name' => 'Draft event']);
        $this->actingAs($this->admin())->get('/admin?status=published&q=Published')->assertOk()->assertSee('Published event')->assertDontSee('Draft event');
        $this->get(route('admin.events.edit', $event))->assertOk()->assertSee('Foto & galeri');
        $this->get(route('admin.events.create'))->assertOk()->assertSee('Tambahkan event.');
    }
}
