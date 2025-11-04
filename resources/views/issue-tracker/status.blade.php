<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100vh; max-height: 100vh; overflow: hidden; margin: 0; padding: 0; box-sizing: border-box;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Issue Status - {{ $task->tracking_token }} - {{ config('app.name') }}</title>

  {{-- Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

  @vite(['resources/css/app.css', 'resources/js/custom-notifications.js'])

  {{-- Alpine.js --}}
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased font-sans bg-auto bg-no-repeat m-0 p-0" style="height: 100vh; margin: 0; padding: 0; background-image: url('{{ asset('images/issue-tracker-bg.png') }}'); background-position: top center; display: flex; flex-direction: column;">
  
  {{-- Loading Transition --}}
  <x-auth-loading />
    
  {{-- Content area --}}
  <div style="flex: 1; overflow-y: auto; min-height: 100vh;">

    <div class="flex items-center justify-center min-h-full py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">

        {{-- Header --}}
        <div class="text-center">
          <img src="{{ asset('logos/logo-light.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-32 w-auto mb-8">
          <h1 class="text-3xl font-bold text-gray-900">
            Issue Tracker Status
          </h1>
          <p class="mt-2 text-sm text-gray-600">
            Tracking Code: <span class="inline-block px-3 py-1.5 bg-white rounded-full font-mono font-semibold text-primary-500">{{ $task->tracking_token }}</span>
          </p>
        </div>

        {{-- Status Card --}}
        <div class="bg-white shadow-lg rounded-lg p-6 space-y-6">
          
          {{-- Status Roadmap --}}
          <div class="border-b border-gray-200 pb-4">
            <div class="text-center space-y-4">
              
              {{-- Heading --}}
              <h2 class="text-sm font-medium text-gray-500">Status Roadmap</h2>
              
              {{-- Roadmap badges --}}
              @php
                $statusOrder = [
                  'issue_tracker' => 1,
                  'todo' => 2,
                  'in_progress' => 3,
                  'toreview' => 4,
                  'completed' => 5,
                  'archived' => 6,
                ];
                
                $statusLabels = [
                  'issue_tracker' => 'Issue Tracker',
                  'todo' => 'To Do',
                  'in_progress' => 'In Progress',
                  'toreview' => 'To Review',
                  'completed' => 'Completed',
                  'archived' => 'Archived',
                ];
                
                $currentStatusOrder = $statusOrder[$task->status] ?? 1;
                
                // Show all statuses with their type (previous, current, upcoming)
                $statusesToShow = [];
                foreach ($statusOrder as $status => $order) {
                  $statusType = 'upcoming';
                  if ($order < $currentStatusOrder) {
                    $statusType = 'previous';
                  } elseif ($order === $currentStatusOrder) {
                    $statusType = 'current';
                  }
                  
                  $statusesToShow[] = [
                    'key' => $status,
                    'label' => $statusLabels[$status],
                    'type' => $statusType,
                    'isCurrent' => $order === $currentStatusOrder,
                  ];
                }
              @endphp
              <div class="relative w-full overflow-x-auto scroll-smooth" id="status-roadmap-container" style="scrollbar-width: none; -ms-overflow-style: none;">
                <div id="status-roadmap" class="flex items-center gap-2 flex-nowrap">
                  @foreach ($statusesToShow as $index => $status)
                    <div class="flex items-center flex-shrink-0 {{ $index > 0 ? 'gap-2' : '' }}"@if($status['isCurrent']) id="current-status"@endif>
                      @if ($index > 0)

                        {{-- Connector arrow --}}
                        @php

                          // Arrow opacity based on status type
                          if ($status['type'] === 'current') {
                            $arrowOpacity = '';
                          } elseif ($status['type'] === 'previous') {
                            $arrowOpacity = 'opacity-50';
                          } else {
                            $arrowOpacity = 'opacity-30';
                          }

                        @endphp

                        {{-- Heroicon: arrow-long-right --}}
                        <svg class="w-4 h-4 text-gray-300 {{ $arrowOpacity }} flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H3m15 0l-3.75-3.75M18 12l-3.75 3.75" />
                        </svg>
                      @endif

                      {{-- Status badge --}}
                      @php

                        // Badge color: primary for current, gray for non-active
                        if ($status['isCurrent']) {
                          $badgeColor = 'bg-primary-500 text-primary-900';
                          $badgeFontSize = 'text-sm';
                          $badgeFontWeight = 'font-bold';
                        } else {
                          $badgeColor = 'bg-gray-100 text-gray-800';
                          $badgeFontSize = 'text-xs';
                          $badgeFontWeight = 'font-semibold';
                        }
                        
                        // Badge opacity and styling based on status type
                        if ($status['type'] === 'current') {
                          $badgeOpacity = '';
                          $badgeStyle = '';
                        } elseif ($status['type'] === 'previous') {
                          $badgeOpacity = 'opacity-50';
                          $badgeStyle = '';
                        } else {

                          // Upcoming statuses: more faded with border
                          $badgeOpacity = 'opacity-30';
                          $badgeStyle = 'border border-gray-300 border-dashed';

                        }

                      @endphp

                      <span class="inline-flex items-center px-3 py-1.5 rounded-full {{ $badgeFontSize }} {{ $badgeFontWeight }} flex-shrink-0 {{ $badgeColor }} {{ $badgeOpacity }} {{ $badgeStyle }}">
                        {{ $status['label'] }}
                      </span>
                    </div>
                  @endforeach
                </div>
              </div>
              
              <script>
                (function() {
                  'use strict';
                  
                  let isScrolling = false;
                  
                  function scrollToCurrentStatus() {
                    const container = document.getElementById('status-roadmap-container');
                    const currentStatus = document.getElementById('current-status');
                    
                    if (!container || !currentStatus || isScrolling) {
                      return;
                    }
                    
                    isScrolling = true;
                    
                    // Force layout recalculation
                    void container.offsetWidth;
                    void currentStatus.offsetWidth;
                    
                    // Use requestAnimationFrame to ensure layout is stable
                    requestAnimationFrame(function() {
                      requestAnimationFrame(function() {
                        const containerWidth = container.clientWidth;
                        const containerScrollWidth = container.scrollWidth;
                        const statusOffsetLeft = currentStatus.offsetLeft;
                        const statusWidth = currentStatus.offsetWidth;
                        const statusCenter = statusOffsetLeft + (statusWidth / 2);
                        
                        // Calculate scroll position to center the status
                        const scrollLeft = statusCenter - (containerWidth / 2);
                        const maxScroll = Math.max(0, containerScrollWidth - containerWidth);
                        const finalScrollLeft = Math.max(0, Math.min(scrollLeft, maxScroll));
                        
                        // Smooth scroll to center
                        container.scrollTo({
                          left: finalScrollLeft,
                          behavior: 'smooth'
                        });
                        
                        // Reset flag after scroll completes
                        setTimeout(function() {
                          isScrolling = false;
                        }, 600);
                      });
                    });

                  }
                  
                  // Initialize when DOM is ready
                  function init() {
                    scrollToCurrentStatus();

                    // Retry once after a delay to ensure layout is complete
                    setTimeout(scrollToCurrentStatus, 300);

                  }
                  
                  if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                  } else {
                    init();
                  }
                  
                  // Re-scroll on window resize
                  let resizeTimer;
                  window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(scrollToCurrentStatus, 200);
                  });
                  
                  // Watch for DOM changes (in case status changes)
                  const observer = new MutationObserver(function() {
                    if (!isScrolling) {
                      setTimeout(scrollToCurrentStatus, 100);
                    }
                  });
                  
                  setTimeout(function() {
                    const roadmap = document.getElementById('status-roadmap');
                    if (roadmap) {
                      observer.observe(roadmap, {
                        attributes: true,
                        attributeFilter: ['id'],
                        childList: true,
                        subtree: true
                      });
                    }
                  }, 500);
                })();
              </script>
              
              <style>
                
                /* Hide scrollbar for all browsers */
                #status-roadmap-container::-webkit-scrollbar {
                  display: none;
                }
                
                #status-roadmap-container {
                  -ms-overflow-style: none;
                  scrollbar-width: none;
                  -webkit-overflow-scrolling: touch;
                }
              </style>
              
              {{-- Submitted on --}}
              <div class="pt-2">
                <h2 class="text-sm font-medium text-gray-500">Submitted on</h2>
                @php
                  $submittedOn = null;
                  if (!empty($task->extra_information) && is_array($task->extra_information)) {
                    foreach ($task->extra_information as $item) {
                      if (is_array($item)) {
                        $title = $item['title'] ?? '';
                        $value = $item['value'] ?? '';
                        
                        if (stripos($title, 'Submitted on') !== false) {
                          $cleanValue = strip_tags($value);
                          $cleanValue = html_entity_decode($cleanValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                          $submittedOn = trim($cleanValue);
                          break;
                        }
                      }
                    }
                  }
                  
                  // Fallback to created_at if not found in extra_information
                  if (!$submittedOn) {
                    $submittedOn = $task->created_at->format('j/n/y').', '.$task->created_at->format('h:i A');
                  }
                @endphp
                <p class="text-sm font-medium text-gray-900">
                  {{ $submittedOn }}
                </p>
              </div>
              
            </div>
          </div>

          {{-- Reporter Information --}}
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Reporter Information</h3>
            <div class="space-y-2">
              @php
                $reporterName = 'N/A';
                $reporterEmail = 'N/A';
                
                if (!empty($task->extra_information) && is_array($task->extra_information)) {
                  foreach ($task->extra_information as $item) {
                    if (is_array($item)) {
                      $title = $item['title'] ?? '';
                      $value = $item['value'] ?? '';
                      
                      // Strip HTML tags from value (RichEditor stores HTML)
                      $cleanValue = strip_tags($value);
                      $cleanValue = html_entity_decode($cleanValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                      $cleanValue = trim($cleanValue);
                      
                      if (stripos($title, 'Reporter Name') !== false || stripos($title, 'name') !== false) {
                        $reporterName = $cleanValue ?: 'N/A';
                      }
                      if (stripos($title, 'Reporter Email') !== false || stripos($title, 'email') !== false) {
                        $reporterEmail = $cleanValue ?: 'N/A';
                      }
                    }
                  }
                }
              @endphp
              <div>
                <p class="text-xs text-gray-500">Name</p>
                <p class="text-sm font-medium text-gray-900">{{ $reporterName }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Email</p>
                <p class="text-sm font-medium text-gray-900">{{ $reporterEmail }}</p>
              </div>
            </div>
          </div>

          {{-- Project Information --}}
          @if($project)
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Project</h3>
            <p class="text-sm text-gray-900">{{ $project->title }}</p>
          </div>
          @endif

          {{-- Issue Title --}}
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Issue Title</h3>
            <p class="text-sm text-gray-900">{{ $task->title }}</p>
          </div>

          {{-- Description --}}
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Description</h3>
            <div class="bg-gray-50 rounded-md p-4">
              <div class="text-sm text-gray-900 prose prose-sm max-w-none">
                @php
                  // Check if description contains HTML tags
                  $description = $task->description ?? '';
                  if (!empty($description) && !preg_match('/<[^>]+>/', $description)) {
                    // Plain text - convert \n to <br> and preserve spaces
                    $description = nl2br(e($description));
                  }
                @endphp
                {!! $description !!}
              </div>
            </div>
          </div>

          {{-- Attachments --}}
          @if(!empty($task->attachments) && is_array($task->attachments))
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Attachments</h3>
            <div class="space-y-2">
              @foreach($task->attachments as $attachment)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                  <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">{{ basename($attachment) }}</p>
                    </div>
                  </div>
                  <a href="{{ asset('storage/'.$attachment) }}" target="_blank" class="ml-3 text-primary-600 hover:text-primary-800 text-sm font-medium">
                    View
                  </a>
                </div>
              @endforeach
            </div>
          </div>
          @endif

          {{-- Actions --}}
          <div class="pt-4 border-t border-gray-200">
            @if($project)
            <a href="{{ route('issue-tracker.show', ['project' => $project->issue_tracker_code]) }}" 
               class="inline-flex items-center justify-center w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-900 bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
              Submit Another Issue
            </a>
            @endif

            <button id="bookmark-btn" type="button"
              class="mt-3 inline-flex items-center justify-center w-full py-2 px-4 rounded-md text-sm font-medium border border-gray-300 text-gray-800 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
              Bookmark this page
            </button>
          </div>

        </div>

        {{-- Footer --}}
        <div class="text-center">
          <span class="block mb-4 text-xs text-gray-500">Powered by:</span>
          <img src="{{ asset('logos/logo-dark-vertical.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-16 w-auto">
        </div>

      </div>
    </div>

  </div>

  <script>
    (function () {
      'use strict';

      async function copyToClipboard(text) {
        try {
          await navigator.clipboard?.writeText(text);
          return true;
        } catch (_) {
          try {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            ta.remove();
            return true;
          } catch (_) {
            return false;
          }
        }
      }

      function attemptNativeBookmark(title, url) {
        if (window.sidebar && typeof window.sidebar.addPanel === 'function') {
          try { window.sidebar.addPanel(title, url, ''); return true; } catch (_) {}
        }
        if (window.external && 'AddFavorite' in window.external) {
          try { window.external.AddFavorite(url, title); return true; } catch (_) {}
        }
        return false;
      }

      function platformShortcut() {
        const isMac = navigator.platform?.toUpperCase().includes('MAC');
        return isMac ? 'Cmd + D' : 'Ctrl + D';
      }

      function showNotification(message, type) {
        if (typeof window.showNotification === 'function') {
          window.showNotification(type, message);
        } else if (typeof window.showSuccessNotification === 'function' && type === 'success') {
          window.showSuccessNotification(message);
        } else if (typeof window.showInfoNotification === 'function' && type === 'info') {
          window.showInfoNotification(message);
        } else {
          console.log(message);
        }
      }

      function onBookmarkClick() {
        const url = window.location.href;
        const title = document.title;

        const usedNative = attemptNativeBookmark(title, url);
        copyToClipboard(url);

        if (usedNative) {
          showNotification('Bookmark dialog opened. URL copied to clipboard.', 'success');
          return;
        }

        showNotification('Press ' + platformShortcut() + ' to bookmark. URL copied.', 'info');
      }

      function init() {
        const btn = document.getElementById('bookmark-btn');
        if (btn) {
          btn.addEventListener('click', onBookmarkClick);
        }
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }
    })();
  </script>

</body>
</html>

