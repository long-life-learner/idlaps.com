<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveEventRequest;
use App\Models\Event;
use App\Support\EventWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:200'], 'status' => ['nullable', Rule::in(['published', 'draft', 'archived'])]]);
        $query = ($filters['status'] ?? '') === 'archived' ? Event::onlyTrashed() : Event::query();
        if (($filters['status'] ?? '') === 'published') {
            $query->where('published', true);
        } elseif (($filters['status'] ?? '') === 'draft') {
            $query->where('published', false);
        }
        if ($filters['q'] ?? null) {
            $query->where('name', 'like', '%'.$filters['q'].'%');
        }

        return view('admin.events.index', [
            'events' => $query->orderByDesc('year')->orderByDesc('event_date')->orderByDesc('id')->paginate(15)->withQueryString(),
            'counts' => ['published' => Event::where('published', true)->count(), 'draft' => Event::where('published', false)->count(),
                'articles' => Event::where('published', true)->where('article_published', true)->count(), 'archived' => Event::onlyTrashed()->count()],
        ]);
    }

    public function create(): View
    {
        return view('admin.events.edit', ['event' => new Event(['year' => now()->year, 'sport' => 'Running', 'categories' => [], 'gallery' => [], 'image_focus' => 50])]);
    }

    public function store(SaveEventRequest $request, EventWriter $writer): RedirectResponse
    {
        $event = $writer->save(new Event, $request->validated());

        return to_route('admin.events.edit', $event)->with('status', 'Event disimpan. Draf artikel otomatis siap diperiksa di bagian Artikel.');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(SaveEventRequest $request, Event $event, EventWriter $writer): RedirectResponse
    {
        $writer->save($event, $request->validated());

        return to_route('admin.events.edit', $event)->with('status', $request->input('action') === 'generate'
            ? 'Draf artikel dibuat ulang. Periksa isinya sebelum menerbitkan.' : 'Perubahan event tersimpan.');
    }

    public function preview(Event $event): View
    {
        return view('event', ['event' => $event->catalogueEntry(), 'preview' => true]);
    }

    public function articlePreview(Event $event): View
    {
        return view('article', ['record' => $event, 'event' => $event->catalogueEntry(), 'preview' => true]);
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return to_route('admin.events.index')->with('status', 'Event dipindahkan ke arsip dan disembunyikan dari website.');
    }

    public function restore(int $id): RedirectResponse
    {
        $event = Event::onlyTrashed()->findOrFail($id);
        $event->published = false;
        $event->article_published = false;
        $event->revision++;
        $event->restore();

        return to_route('admin.events.edit', $event)->with('status', 'Event dipulihkan sebagai draf. Periksa sebelum diterbitkan kembali.');
    }
}
