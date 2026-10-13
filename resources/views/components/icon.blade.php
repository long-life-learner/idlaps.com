@props(['name' => 'arrow-up-right'])
<svg {{ $attributes->merge(['class' => 'icon']) }} width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('arrow-right')<path d="M4 12h16M13 5l7 7-7 7"/>@break
        @case('arrow-down')<path d="M12 4v16M5 13l7 7 7-7"/>@break
        @case('pin')<path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
        @case('clock')<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6M12 2v3M19 5l2 2"/>@break
        @case('check')<path d="m5 12 4 4L19 6"/>@break
        @case('mail')<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>@break
        @case('calendar')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>@break
        @case('globe')<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/>@break
        @case('chevron')<path d="m9 5 7 7-7 7"/>@break
        @case('users')<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-3-5"/>@break
        @case('award')<circle cx="12" cy="8" r="5"/><path d="m8 12-2 9 6-3 6 3-2-9"/>@break
        @case('flag')<path d="M5 21V3M5 4c5-4 9 4 14 0v10c-5 4-9-4-14 0"/>@break
        @default<path d="M6 18 18 6M6 6h12v12"/>
    @endswitch
</svg>
