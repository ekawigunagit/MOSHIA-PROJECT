@props(['name' => 'arrow'])
<svg {{ $attributes->merge(['class' => 'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
    @case('arrow') <path d="M5 12h14m-5-5 5 5-5 5"/> @break
    @case('sun') <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/> @break
    @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
    @case('chip') <rect x="6" y="6" width="12" height="12" rx="3"/><path d="M10 2v4m4-4v4m-4 12v4m4-4v4M2 10h4m-4 4h4m12-4h4m-4 4h4"/><rect x="10" y="10" width="4" height="4" rx=".5" fill="currentColor"/> @break
    @case('bolt') <path d="m13 2-9 12h7l-1 8 10-13h-7l1-7Z" fill="currentColor" stroke="none"/> @break
    @case('chart') <path d="M10 3a9 9 0 1 0 11 11H10Z" fill="currentColor" stroke="none"/><path d="M14 2v8h8a9 9 0 0 0-8-8Z" fill="currentColor" opacity=".55" stroke="none"/> @break
    @case('link') <path d="m10 13 4-4m-6 6-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 3 1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0" transform="translate(1 1)"/> @break
    @case('grid') <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/> @break
@endswitch
</svg>
