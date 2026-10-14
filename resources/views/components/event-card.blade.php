@props(['event'])
<article class="event-card">
    <a class="event-photo {{ !$event['image'] ? 'event-photo-graphic' : '' }}" href="{{ $event['url'] }}" aria-label="Lihat {{ $event['name'] }}">
        @if($event['image'])<img src="{{ $event['image'] }}" alt="Dokumentasi {{ $event['name'] }}" style="object-position: {{ $event['image_position'] ?? 'center' }}" loading="lazy" width="640" height="460">@else<span class="graphic-event-title">YOUR RACE.<br><em>IDLAPS.</em></span>@endif
        <span class="image-badge">{{ $event['sport'] }}</span><span class="card-arrow"><x-icon /></span>
    </a>
    <div class="event-card-meta"><span>{{ $event['date'] }}</span><span>{{ $event['status_label'] }}</span></div>
    <h3><a href="{{ $event['url'] }}">{{ $event['name'] }}</a></h3>
    <p class="location"><x-icon name="pin" /> {{ $event['location'] }}</p>
</article>
