<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Collection;

class EventCatalogue
{
    public function all(): Collection
    {
        return Event::query()->where('published', true)->orderByDesc('year')->orderByDesc('event_date')->orderByDesc('id')
            ->get()->map(fn (Event $event): array => $event->catalogueEntry());
    }

    public function find(string $slug): array
    {
        return Event::query()->where('published', true)->where('slug', $slug)->firstOrFail()->catalogueEntry();
    }
}
