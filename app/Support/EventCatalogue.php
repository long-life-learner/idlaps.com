<?php

namespace App\Support;

use Illuminate\Support\Collection;

class EventCatalogue
{
    public function all(): Collection
    {
        return collect(config('idlaps.events'))->map(fn (array $event): array => [
            ...$event,
            'url' => route('events.show', $event['slug']),
        ]);
    }

    public function find(string $slug): array
    {
        $event = $this->all()->firstWhere('slug', $slug);

        abort_unless($event, 404);

        return $event;
    }
}
