<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('idlaps.events') as $entry) {
            if (Event::withTrashed()->where('slug', $entry['slug'])->exists()) {
                continue;
            }
            $gallery = $entry['gallery'] ?? ($entry['image'] ? [[
                'image' => $entry['image'], 'alt' => 'Dokumentasi '.$entry['name'], 'caption' => $entry['name'],
            ]] : []);
            $gallery = array_map(fn (array $photo): array => ['id' => (string) Str::uuid(), ...$photo], $gallery);
            $cover = collect($gallery)->firstWhere('image', $entry['image']);
            preg_match('/(\d+)%/', $entry['image_position'] ?? '', $focus);
            Event::create([
                'slug' => $entry['slug'], 'name' => $entry['name'], 'sport' => $entry['sport'],
                'year' => $entry['year'], 'event_date' => strlen($entry['date']) > 4
                    ? Carbon::createFromLocaleFormat('j F Y', 'id', $entry['date'])->format('Y-m-d') : null,
                'location' => $entry['location'], 'description' => $entry['description'],
                'categories' => $entry['categories'], 'gallery' => $gallery, 'cover_id' => $cover['id'] ?? null,
                'image_focus' => (int) ($focus[1] ?? 50), 'result_url' => $entry['result_url'],
                'legacy_article_url' => $entry['article_url'], 'featured' => $entry['featured'], 'published' => true,
            ]);
        }
    }
}
