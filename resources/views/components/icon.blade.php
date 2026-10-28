@props(['name'])
@php
    $paths = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'cart' => '<path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/><circle cx="10" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'heart' => '<path d="M12 20s-7-4.4-9-9.2C1.6 7.3 4 4 7.4 4c2 0 3.5 1.1 4.6 2.6C13.1 5.1 14.6 4 16.6 4 20 4 22.4 7.3 21 10.8 19 15.6 12 20 12 20Z"/>',
        'moon' => '<path d="M20 14.5A8.5 8.5 0 1 1 9.5 4 7 7 0 0 0 20 14.5Z"/>',
        'compare' => '<path d="M8 3v18M16 3v18M3 8h5M16 16h5M3 16h5M16 8h5"/>',
        'phone' => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
        'shield' => '<path d="M12 3 4 6v6c0 5 3.4 8.4 8 9 4.6-.6 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'truck' => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.2L3 17.8V21h3.2l6.3-6.3a4 4 0 0 0 5.2-5.4l-2.6 2.6-2.4-.6-.6-2.4 2.6-2.6Z"/>',
        'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M17 6l3 3M15 8l2 2"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'car' => '<path d="M5 16h14M4 16l1.6-5.2A2 2 0 0 1 7.5 9.4h9a2 2 0 0 1 1.9 1.4L20 16v3h-3v-2H7v2H4v-3Z"/><circle cx="7.5" cy="13.5" r=".8"/><circle cx="16.5" cy="13.5" r=".8"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8L3 12Z"/><circle cx="7.5" cy="7.5" r="1.3"/>',
        'file' => '<path d="M6 3h8l5 5v13H6z"/><path d="M14 3v5h5"/>',
        'logout' => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>',
        'filter' => '<path d="M3 5h18l-7 8v6l-4 2v-8L3 5Z"/>',
        'home' => '<path d="M3 11 12 4l9 7v9h-6v-6H9v6H3z"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
