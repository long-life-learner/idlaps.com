<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class EventWriter
{
    public function __construct(private ArticleDraft $draft) {}

    public function save(Event $event, array $data): Event
    {
        $uploadedPaths = [];
        $removedPaths = [];
        try {
            $saved = DB::transaction(function () use ($event, $data, &$uploadedPaths, &$removedPaths): Event {
                if ($event->exists) {
                    $event = Event::query()->lockForUpdate()->findOrFail($event->id);
                    if ((int) $data['revision'] !== $event->revision) {
                        throw ValidationException::withMessages(['revision' => 'Event telah diubah di tab lain. Buka ulang editor sebelum menyimpan.']);
                    }
                }
                $isNew = ! $event->exists;
                $event->fill(collect($data)->only(['name', 'sport', 'year', 'event_date', 'location', 'description',
                    'result_url', 'legacy_article_url', 'article_title', 'article_body', 'published', 'featured', 'article_published', 'image_focus'])->all());
                $event->categories = array_values(array_filter(array_map('trim', explode(',', $data['categories_text'] ?? ''))));
                if ($isNew) {
                    $base = Str::slug($event->name) ?: 'event';
                    $slug = $base;
                    $number = 2;
                    while (Event::withTrashed()->where('slug', $slug)->exists()) {
                        $slug = $base.'-'.$number++;
                    }
                    $event->slug = $slug;
                    $event->gallery = [];
                }
                $event->save();
                $gallery = [];
                foreach ($event->gallery as $photo) {
                    if (in_array($photo['id'], $data['remove_photos'] ?? [], true)) {
                        if (isset($photo['path']) && str_starts_with($photo['path'], 'event-photos/'.$event->id.'/')) {
                            $removedPaths[] = $photo['path'];
                        }

                        continue;
                    }
                    $photo['caption'] = array_key_exists($photo['id'], $data['captions'] ?? []) ? ($data['captions'][$photo['id']] ?? '') : $photo['caption'];
                    $photo['alt'] = $data['alt_texts'][$photo['id']] ?? $photo['alt'];
                    $gallery[] = $photo;
                }
                foreach ($data['photos'] ?? [] as $file) {
                    $path = $file->store('event-photos/'.$event->id, 'local');
                    if (! $path) {
                        throw new \RuntimeException('Foto gagal disimpan.');
                    }
                    $uploadedPaths[] = $path;
                    $gallery[] = ['id' => (string) Str::uuid(), 'path' => $path,
                        'image' => route('event-media', ['event' => $event->id, 'filename' => basename($path)], false),
                        'alt' => 'Dokumentasi '.$event->name, 'caption' => $event->name];
                }
                $event->gallery = $gallery;
                $cover = $data['cover_id'] ?? $event->cover_id;
                $event->cover_id = collect($gallery)->contains('id', $cover) ? $cover : ($gallery[0]['id'] ?? null);
                if ($isNew || $data['action'] === 'generate') {
                    $event->fill($this->draft->generate($event));
                    $event->article_published = false;
                }
                $event->revision = $isNew ? 1 : $event->revision + 1;
                $event->save();

                return $event;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($uploadedPaths);
            throw $exception;
        }
        Storage::disk('local')->delete($removedPaths);

        return $saved;
    }
}
