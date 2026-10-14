<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventMediaController extends Controller
{
    public function __invoke(Request $request, Event $event, string $filename): StreamedResponse
    {
        abort_unless($event->published || $request->user()?->can('manage-cms'), 404);
        $path = 'event-photos/'.$event->id.'/'.$filename;
        abort_unless(collect($event->gallery)->contains('path', $path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }
}
