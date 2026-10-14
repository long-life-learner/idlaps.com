<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['slug', 'name', 'sport', 'year', 'event_date', 'location', 'description', 'categories', 'gallery', 'cover_id', 'image_focus', 'result_url', 'legacy_article_url', 'article_title', 'article_body', 'article_published', 'published', 'featured', 'revision'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'event_date' => 'date', 'categories' => 'array', 'gallery' => 'array',
            'published' => 'boolean', 'article_published' => 'boolean', 'featured' => 'boolean',
        ];
    }

    public function catalogueEntry(): array
    {
        $cover = collect($this->gallery)->firstWhere('id', $this->cover_id) ?? ($this->gallery[0] ?? null);

        return [
            'slug' => $this->slug, 'name' => $this->name, 'sport' => $this->sport,
            'year' => (string) $this->year,
            'date' => $this->event_date?->locale('id')->translatedFormat('j F Y') ?? (string) $this->year,
            'location' => $this->location, 'description' => $this->description,
            'image' => $cover['image'] ?? null, 'image_position' => 'center '.$this->image_focus.'%',
            'categories' => $this->categories, 'gallery' => $this->gallery,
            'result_url' => $this->result_url,
            'article_title' => $this->article_published ? $this->article_title : $this->name,
            'article_url' => $this->article_published ? route('news.show', $this->slug) : $this->legacy_article_url,
            'featured' => $this->featured, 'url' => route('events.show', $this->slug),
            'status_label' => ($this->event_date?->isFuture() || $this->year > now()->year) ? 'AKAN DATANG' : 'EVENT SELESAI',
        ];
    }
}
