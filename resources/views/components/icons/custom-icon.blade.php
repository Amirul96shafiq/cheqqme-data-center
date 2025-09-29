@props([
    'name',
    'class' => 'w-6 h-6',
    'color' => 'text-gray-500 dark:text-gray-400'
])

@php
    $icons = [
        'humidity' => [
            'viewBox' => '0 0 32 32',
            'content' => '<svg xmlns="http://www.w3.org/2000/svg">
                              <path fill="currentColor" d="M26 12a3.898 3.898 0 0 1-4-3.777a3.902 3.902 0 0 1 .653-2.064l2.517-3.745a1.038 1.038 0 0 1 1.66 0l2.485 3.696A3.97 3.97 0 0 1 30 8.223A3.898 3.898 0 0 1 26 12zm0-7.237l-1.656 2.463a1.89 1.89 0 0 0-.344.997a2.014 2.014 0 0 0 4 0a1.98 1.98 0 0 0-.375-1.047zM23.5 30h-15a6.496 6.496 0 0 1-1.3-12.862a8.994 8.994 0 0 1 17.6 0A6.496 6.496 0 0 1 23.5 30zM16 12a7 7 0 0 0-6.941 6.145l-.1.812l-.815.064A4.496 4.496 0 0 0 8.5 28h15a4.496 4.496 0 0 0 .356-8.979l-.815-.064l-.099-.812A7.002 7.002 0 0 0 16 12z"/>
                          </svg>'
        ],
        'wind' => [
            'viewBox' => '0 0 32 32',
            'content' => '<svg xmlns="http://www.w3.org/2000/svg">
                              <path fill="currentColor" d="M21 15H8v-2h13a3 3 0 1 0-3-3h-2a5 5 0 1 1 5 5zm2 13a5.006 5.006 0 0 1-5-5h2a3 3 0 1 0 3-3H4v-2h19a5 5 0 0 1 0 10z"/>
                          </svg>'
        ],
        'uv-index' => [
            'viewBox' => '0 0 32 32',
            'content' => '<svg xmlns="http://www.w3.org/2000/svg">
                              <path fill="currentColor" d="M13 30H9a2.003 2.003 0 0 1-2-2v-8h2v8h4v-8h2v8a2.003 2.003 0 0 1-2 2zm12-10h-1.75L21 29.031L18.792 20H17l2.5 10h3L25 20zM15 2h2v5h-2zm6.688 6.9l3.506-3.506l1.414 1.414l-3.506 3.506zM25 15h5v2h-5zM2 15h5v2H2zm3.395-8.192l1.414-1.414L10.315 8.9L8.9 10.314zM22 17h-2v-1a4 4 0 0 0-8 0v1h-2v-1a6 6 0 0 1 12 0Z"/>
                          </svg>'
        ],
        'sunset' => [
            'viewBox' => '0 0 32 32',
            'content' => '<svg xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor" d="M2 27.005h27.998v2H2zm14-7a4.005 4.005 0 0 1 4 4h2a6 6 0 0 0-12 0h2a4.005 4.005 0 0 1 4-4Zm9 2h5v2h-5zm-3.313-5.101l3.506-3.506l1.414 1.414l-3.506 3.506zM19.59 9.595L17 12.175v-8.17h-2v8.17l-2.59-2.58l-1.41 1.41l5 5l5-5l-1.41-1.41zM5.394 14.812l1.414-1.414l3.506 3.506l-1.415 1.414zM2 22.005h5v2H2z"/>
                        </svg>'
        ],
        // 'temperature' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'
        // ],
        // 'pressure' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12h18M3 6h18M3 18h18"/>'
        // ],
        // 'visibility' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
        //     <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
        // ],
        'cloud' => [
            'viewBox' => '0 0 32 32',
            'content' => '<svg xmlns="http://www.w3.org/2000/svg">
                              <path fill="currentColor" d="M30 15.5a6.532 6.532 0 0 0-5.199-6.363a8.994 8.994 0 0 0-17.6 0A6.532 6.532 0 0 0 2 15.5a6.454 6.454 0 0 0 1.688 4.35A5.983 5.983 0 0 0 8 30h11a5.976 5.976 0 0 0 5.61-8.102A6.505 6.505 0 0 0 30 15.501ZM19 28H8a3.993 3.993 0 0 1-.673-7.93l.663-.112l.146-.656a5.496 5.496 0 0 1 10.73 0l.145.656l.663.113A3.993 3.993 0 0 1 19 28Zm4.5-8h-.055a5.956 5.956 0 0 0-2.796-1.756a7.495 7.495 0 0 0-14.299 0a5.988 5.988 0 0 0-1.031.407A4.445 4.445 0 0 1 4 15.5a4.517 4.517 0 0 1 4.144-4.481l.816-.064l.099-.812a6.994 6.994 0 0 1 13.883 0l.099.812l.815.064A4.497 4.497 0 0 1 23.5 20Z"/>
                          </svg>'
        ],
        'rain' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>'
        ],
        'snow' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>'
        ],
        'thunderstorm' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M13 10l-4 6h4l-2 4"/>'
        ],
        'fog' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18M3 20h18"/>'
        ],
        'haze' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'mist' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'drizzle' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        ],
        'sleet' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        ],
        'tornado' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18M3 20h18"/>'
        ],
        'hurricane' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18M3 20h18"/>'
        ],
        'dust' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'sand' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'ash' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'squall' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'tropical-storm' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'freezing-rain' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        ],
        'blizzard' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>'
        ],
        'ice-pellets' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        ],
        'sun' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>'
        ],
        'moon' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>'
        ],
        'partly-cloudy' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9"/>'
        ],
        'overcast' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        ],
        'clear' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>'
        ]
    ];

    $iconData = $icons[$name] ?? null;
    $viewBox = $iconData['viewBox'] ?? '0 0 24 24';
    $content = $iconData['content'] ?? '';
@endphp

@if($iconData)
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }} {{ $color }}" viewBox="{{ $viewBox }}">
        {!! $content !!}
    </svg>
@else
    <!-- Fallback for unknown icon -->
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }} {{ $color }}" viewBox="0 0 24 24">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
    </svg>
@endif
