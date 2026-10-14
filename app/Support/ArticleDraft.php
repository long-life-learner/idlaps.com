<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Str;

class ArticleDraft
{
    /** @return array{article_title: string, article_body: string} */
    public function generate(Event $event): array
    {
        $entry = $event->catalogueEntry();
        $paragraphs = ["{$event->name} — {$event->location}, {$entry['date']}."];
        if ($event->description) {
            $paragraphs[] = $event->description;
        }
        if ($event->categories) {
            $paragraphs[] = 'Kategori event: '.implode(', ', $event->categories).'.';
        }
        if ($event->gallery) {
            $paragraphs[] = 'Dokumentasi event dapat dilihat melalui galeri pada halaman event ini.';
        }
        if ($event->result_url) {
            $paragraphs[] = 'Hasil lomba tersedia melalui tautan result pada halaman event.';
        }

        return ['article_title' => Str::limit($event->name.' di '.$event->location, 255, ''), 'article_body' => implode("\n\n", $paragraphs)];
    }
}
