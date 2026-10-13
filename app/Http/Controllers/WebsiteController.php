<?php

namespace App\Http\Controllers;

use App\Support\EventCatalogue;
use Illuminate\Contracts\View\View;

class WebsiteController extends Controller
{
    public function __construct(private EventCatalogue $catalogue) {}

    public function home(): View
    {
        return view('home', [
            'events' => $this->catalogue->all()->where('featured', true),
            'articles' => $this->catalogue->all()->whereNotNull('article_url'),
        ]);
    }

    public function events(): View
    {
        return view('events', ['events' => $this->catalogue->all()]);
    }

    public function event(string $slug): View
    {
        return view('event', ['event' => $this->catalogue->find($slug)]);
    }

    public function results(): View
    {
        return view('results', ['events' => $this->catalogue->all()->whereNotNull('result_url')->values()]);
    }

    public function demo(): View
    {
        return view('result-demo');
    }

    public function news(): View
    {
        return view('news', ['articles' => $this->catalogue->all()->whereNotNull('article_url')]);
    }
}
