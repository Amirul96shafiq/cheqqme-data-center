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
        // 'cloud' => [
        //     'viewBox' => '0 0 32 32',
        //     'content' => '<svg xmlns="http://www.w3.org/2000/svg">
        //                       <path fill="currentColor" d="M30 15.5a6.532 6.532 0 0 0-5.199-6.363a8.994 8.994 0 0 0-17.6 0A6.532 6.532 0 0 0 2 15.5a6.454 6.454 0 0 0 1.688 4.35A5.983 5.983 0 0 0 8 30h11a5.976 5.976 0 0 0 5.61-8.102A6.505 6.505 0 0 0 30 15.501ZM19 28H8a3.993 3.993 0 0 1-.673-7.93l.663-.112l.146-.656a5.496 5.496 0 0 1 10.73 0l.145.656l.663.113A3.993 3.993 0 0 1 19 28Zm4.5-8h-.055a5.956 5.956 0 0 0-2.796-1.756a7.495 7.495 0 0 0-14.299 0a5.988 5.988 0 0 0-1.031.407A4.445 4.445 0 0 1 4 15.5a4.517 4.517 0 0 1 4.144-4.481l.816-.064l.099-.812a6.994 6.994 0 0 1 13.883 0l.099.812l.815.064A4.497 4.497 0 0 1 23.5 20Z"/>
        //                   </svg>'
        // ],
        // 'rain' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>'
        // ],
        // 'snow' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>'
        // ],
        // 'thunderstorm' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M13 10l-4 6h4l-2 4"/>'
        // ],
        // 'fog' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18M3 20h18"/>'
        // ],
        // 'haze' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'mist' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'drizzle' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        // ],
        // 'sleet' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        // ],
        // 'tornado' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18M3 20h18"/>'
        // ],
        // 'hurricane' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18M3 20h18"/>'
        // ],
        // 'dust' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'sand' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'ash' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'squall' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'tropical-storm' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'freezing-rain' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        // ],
        // 'blizzard' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>'
        // ],
        // 'ice-pellets' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M8 19l2 2m0 0l2-2m-2 2V10"/>'
        // ],
        // 'sun' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>'
        // ],
        // 'moon' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>'
        // ],
        // 'partly-cloudy' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
        //     <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9"/>'
        // ],
        // 'overcast' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 19a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M3 12h18M3 16h18"/>'
        // ],
        // 'clear' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>'
        // ],
        'refresh' => [
            'viewBox' => '0 0 24 24',
            'content' => '<svg xmlns="http://www.w3.org/2000/svg">
                              <circle cx="18" cy="12" r="0" fill="currentColor">
                                  <animate attributeName="r" begin=".67" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/>
                              </circle>
                              <circle cx="12" cy="12" r="0" fill="currentColor">
                                  <animate attributeName="r" begin=".33" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/>
                              </circle>
                              <circle cx="6" cy="12" r="0" fill="currentColor">
                                  <animate attributeName="r" begin="0" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/>
                              </circle>
                          </svg>'
        ],
        'check' => [
            'viewBox' => '0 0 20 20',
            'content' => '<path fill="currentColor" fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>'
        ],
        'x-mark' => [
            'viewBox' => '0 0 20 20',
            'content' => '<path fill="currentColor" fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>'
        ],
        'warning' => [
            'viewBox' => '0 0 20 20',
            'content' => '<path fill="currentColor" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>'
        ],
        'sidebar-panel' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill-rule="evenodd" d="M6 5 a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2 V5H6Zm4 0v14h8a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-8ZM3 6a3 3 0 0 1 3-3 h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Z" clip-rule="evenodd"/>'
        ],
        'users' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" fill="none" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>'
        ],
        'map-pin' => [
            'viewBox' => '0 0 20 20',
            'content' => '<path fill="currentColor" fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>'
        ],
        'clock' => [
            'viewBox' => '0 0 20 20',
            'content' => '<path fill="currentColor" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>'
        ],
        'google' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>'
        ],
        'google-calendar' => [
            'viewBox' => '0 0 24 24',
            'content' => '<rect x="3" y="4" width="18" height="18" rx="2" fill="#4285F4"/><path d="M7 2v4M17 2v4" stroke="white" stroke-width="2" stroke-linecap="round"/><path d="M3 8h18" stroke="white" stroke-width="2"/><text x="12" y="17" font-size="8" font-weight="bold" fill="white" text-anchor="middle">31</text>'
        ],
        'video-camera' => [
            'viewBox' => '0 0 20 20',
            'content' => '<path fill="currentColor" d="M3.25 4A2.25 2.25 0 001 6.25v7.5A2.25 2.25 0 003.25 16h7.5A2.25 2.25 0 0013 13.75v-7.5A2.25 2.25 0 0010.75 4h-7.5zM19 4.75a.75.75 0 00-1.28-.53l-3 3a.75.75 0 00-.22.53v4.5c0 .199.079.39.22.53l3 3a.75.75 0 001.28-.53V4.75z"/>'
        ],
        'spotify' => [
            'viewBox' => '0 0 24 24',
            'content' => '<path fill="currentColor" d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>'
        ],
        // 'user' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>'
        // ],
        // 'settings' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
        //     <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
        // ],
        // 'rocket' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.58-5.84a14.927 14.927 0 015.84 2.58M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'
        // ],
        // 'table' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 0a2.25 2.25 0 00-2.25-2.25H5.625a2.25 2.25 0 00-2.25 2.25m0 0V12a2.25 2.25 0 002.25 2.25h12.75a2.25 2.25 0 002.25-2.25V5.625m-18.75 0h18.75"/>'
        // ],
        // 'chevron-right' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>'
        // ],
        // 'close' => [
        //     'viewBox' => '0 0 24 24',
        //     'content' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>'
        // ]
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
