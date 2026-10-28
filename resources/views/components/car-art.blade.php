@props(['type' => 'sedan', 'color' => '#1f6feb', 'label' => 'Hình minh họa xe'])
@php
    $gid = 'g'.substr(md5(uniqid('', true)), 0, 8);
    // Thân xe theo kiểu dáng (nhìn ngang, viewBox 400x190)
    $shapes = [
        'sedan' => [
            'body' => 'M22,128 Q20,108 46,102 L112,94 Q140,64 176,58 L248,58 Q284,60 314,92 L362,100 Q386,105 386,124 L386,138 Q386,146 377,146 L26,146 Q18,146 20,138 Z',
            'glass' => 'M128,94 Q152,70 180,66 L210,66 L210,94 Z M218,66 L246,66 Q272,68 296,92 L218,94 Z',
            'wheels' => [[98, 146, 25], [306, 146, 25]],
        ],
        'suv' => [
            'body' => 'M18,132 L20,98 Q22,84 44,82 L96,78 Q116,46 144,42 L292,42 Q322,44 338,78 L370,84 Q388,88 388,108 L388,140 Q388,148 379,148 L26,148 Q16,148 18,140 Z',
            'glass' => 'M110,80 Q126,52 148,50 L206,50 L206,80 Z M214,50 L288,50 Q310,52 322,78 L214,80 Z',
            'wheels' => [[100, 148, 28], [308, 148, 28]],
        ],
        'pickup' => [
            'body' => 'M14,134 L16,100 Q18,88 38,86 L98,82 Q116,50 140,46 L214,46 Q232,48 242,82 L384,84 Q390,84 390,92 L390,140 Q390,148 381,148 L22,148 Q12,148 14,140 Z',
            'glass' => 'M110,82 Q126,56 146,54 L176,54 L176,82 Z M184,54 L210,54 Q224,56 230,82 L184,82 Z',
            'wheels' => [[94, 148, 28], [318, 148, 28]],
        ],
        'hatch' => [
            'body' => 'M40,130 Q38,108 64,102 L104,96 Q132,60 168,56 L272,56 Q298,58 324,96 L348,104 Q364,110 364,128 L364,140 Q364,148 355,148 L46,148 Q36,148 38,140 Z',
            'glass' => 'M118,96 Q140,66 170,64 L214,64 L214,96 Z M222,64 L268,64 Q290,66 310,96 L222,96 Z',
            'wheels' => [[112, 148, 24], [298, 148, 24]],
        ],
        'ev' => [
            'body' => 'M24,128 Q22,104 52,98 L108,90 Q138,56 182,52 L244,52 Q286,54 318,90 L360,98 Q386,104 386,124 L386,138 Q386,146 377,146 L30,146 Q22,146 22,138 Z',
            'glass' => 'M124,90 Q150,62 186,60 L214,60 L214,90 Z M222,60 L242,60 Q276,62 302,90 L222,90 Z',
            'wheels' => [[100, 146, 25], [306, 146, 25]],
        ],
    ];
    $s = $shapes[$type] ?? $shapes['sedan'];
@endphp
<svg {{ $attributes->merge(['class' => 'car-art']) }} viewBox="0 0 400 190" role="img" aria-label="{{ $label }}" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="{{ $gid }}-shine" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity=".45"/>
            <stop offset=".45" stop-color="#fff" stop-opacity="0"/>
            <stop offset="1" stop-color="#000" stop-opacity=".28"/>
        </linearGradient>
        <linearGradient id="{{ $gid }}-glass" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#cfe3f5"/>
            <stop offset="1" stop-color="#5d7690"/>
        </linearGradient>
        <radialGradient id="{{ $gid }}-rim">
            <stop offset="0" stop-color="#e9edf1"/>
            <stop offset=".7" stop-color="#9aa3ad"/>
            <stop offset="1" stop-color="#5c646d"/>
        </radialGradient>
    </defs>
    <ellipse cx="204" cy="170" rx="186" ry="10" fill="#000" opacity=".16"/>
    <path d="{{ $s['body'] }}" fill="{{ $color }}" data-car-paint/>
    <path d="{{ $s['body'] }}" fill="url(#{{ $gid }}-shine)"/>
    <path d="{{ $s['glass'] }}" fill="url(#{{ $gid }}-glass)" opacity=".92"/>
    {{-- đường gân thân xe --}}
    <path d="M40,118 L372,118" stroke="#000" stroke-opacity=".12" stroke-width="2"/>
    {{-- đèn trước / sau --}}
    <path d="M362,104 L384,108 L384,116 L366,114 Z" fill="#fff6c9"/>
    <rect x="{{ $type === 'hatch' ? 38 : 20 }}" y="106" width="14" height="9" rx="3" fill="#d4232f"/>
    @if($type === 'ev')
        <rect x="300" y="100" width="72" height="3" rx="1.5" fill="#bff3ff" opacity=".9"/>
    @endif
    {{-- tay nắm cửa --}}
    <rect x="186" y="104" width="16" height="4" rx="2" fill="#000" opacity=".22"/>
    <rect x="252" y="104" width="16" height="4" rx="2" fill="#000" opacity=".22"/>
    @foreach($s['wheels'] as [$cx, $cy, $r])
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r + 5 }}" fill="#1c1f24" opacity=".25"/>
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="#1b1d21"/>
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r * .62 }}" fill="url(#{{ $gid }}-rim)"/>
        @for($k = 0; $k < 5; $k++)
            <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ round($cx + cos(deg2rad($k * 72 - 90)) * $r * .58, 1) }}" y2="{{ round($cy + sin(deg2rad($k * 72 - 90)) * $r * .58, 1) }}" stroke="#6b737c" stroke-width="3" stroke-linecap="round"/>
        @endfor
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="4" fill="#3a4047"/>
    @endforeach
</svg>
