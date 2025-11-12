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
<body class="antialiased font-sans bg-auto bg-no-repeat m-0 p-0"
      style="height: 100vh; margin: 0; padding: 0; background-image: url('{{ asset('images/issue-tracker-bg.png') }}'); background-position: top center; display: flex; flex-direction: column;"
      x-data="{
        showTrackingTokensModal: false,
        loadingTokens: false,
        trackingTokensData: null,
        tokensError: null,
        async fetchTrackingTokens() {
          this.loadingTokens = true;
          this.tokensError = null;
          try {
            const response = await fetch('/api/issue-trk/{{ $project ? $project->issue_tracker_code : '' }}/tokens');
            if (!response.ok) {
              throw new Error('Failed to fetch tracking tokens');
            }
            this.trackingTokensData = await response.json();
          } catch (error) {
            this.tokensError = error.message;
            console.error('Error fetching tracking tokens:', error);
          } finally {
            this.loadingTokens = false;
          }
        }
      }">
  
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
                <div id="status-roadmap" class="flex items-center flex-nowrap">
                  @foreach ($statusesToShow as $index => $status)
                    @if ($index > 0)
                      {{-- Connector line --}}
                      @php

                        // Line opacity based on previous status type (line connects from previous to current)
                        $previousStatus = $statusesToShow[$index - 1];
                        if ($previousStatus['type'] === 'current' || $status['type'] === 'current') {
                          $lineOpacity = '';
                        } elseif ($previousStatus['type'] === 'previous') {
                          $lineOpacity = 'opacity-50';
                        } else {
                          $lineOpacity = 'opacity-30';
                        }

                      @endphp

                      {{-- Connection line --}}
                      <div class="h-[1px] flex-1 min-w-[2rem] bg-gray-300 {{ $lineOpacity }} flex-shrink-0"></div>
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

                    <span @if($status['isCurrent']) id="current-status-badge"@endif class="inline-flex items-center px-3 py-1.5 rounded-full {{ $badgeFontSize }} {{ $badgeFontWeight }} flex-shrink-0 {{ $badgeColor }} {{ $badgeOpacity }} {{ $badgeStyle }}">
                      {{ $status['label'] }}
                    </span>
                  @endforeach
                </div>
              </div>
              
              <script>
                (function() {
                  'use strict';
                  
                  let isScrolling = false;
                  
                  function scrollToCurrentStatus() {
                    const container = document.getElementById('status-roadmap-container');
                    const currentStatusBadge = document.getElementById('current-status-badge');
                    
                    if (!container || !currentStatusBadge || isScrolling) {
                      return;
                    }
                    
                    isScrolling = true;
                    
                    // Force layout recalculation
                    void container.offsetWidth;
                    void currentStatusBadge.offsetWidth;
                    
                    // Use requestAnimationFrame to ensure layout is stable
                    requestAnimationFrame(function() {
                      requestAnimationFrame(function() {
                        const containerWidth = container.clientWidth;
                        const containerScrollWidth = container.scrollWidth;
                        const badgeOffsetLeft = currentStatusBadge.offsetLeft;
                        const badgeWidth = currentStatusBadge.offsetWidth;
                        const badgeCenter = badgeOffsetLeft + (badgeWidth / 2);
                        
                        // Calculate scroll position to center the badge (not including arrow)
                        const scrollLeft = badgeCenter - (containerWidth / 2);
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

          {{-- First Attachment Image Preview --}}
          @php
            $firstImageAttachment = null;
            if (!empty($task->attachments) && is_array($task->attachments)) {
              $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
              foreach ($task->attachments as $attachment) {
                if (is_string($attachment)) {
                  $extension = strtolower(pathinfo($attachment, PATHINFO_EXTENSION));
                  if (in_array($extension, $imageExtensions)) {
                    $firstImageAttachment = $attachment;
                    break;
                  }
                }
              }
            }
          @endphp
          @if($firstImageAttachment)
          <div>
            <div class="rounded-lg overflow-hidden">
              <img src="{{ asset('storage/'.$firstImageAttachment) }}" 
                   alt="Attachment preview" 
                   class="w-full h-auto max-h-32 object-cover bg-gray-50">
            </div>
          </div>
          @endif

          {{-- Reporter Information --}}
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-3">Reporter Information</h3>
            <div class="space-y-2">
              @php
                $reporterName = 'N/A';
                $reporterEmail = 'N/A';
                $reporterWhatsApp = 'N/A';
                $communicationPreference = 'N/A';
                
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
                      if (stripos($title, 'Reporter WhatsApp') !== false || stripos($title, 'whatsapp') !== false) {
                        $reporterWhatsApp = $cleanValue ?: 'N/A';
                      }
                      if (stripos($title, 'Communication Preference') !== false || stripos($title, 'preference') !== false) {
                        $communicationPreference = $cleanValue ?: 'N/A';
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
                <p class="text-xs text-gray-500">Preferred Communication Method</p>
                <p class="text-sm font-medium text-gray-900">{{ $communicationPreference }}</p>
              </div>
              @if($communicationPreference === 'Email' && $reporterEmail !== 'N/A')
              <div>
                <p class="text-xs text-gray-500">Email</p>
                <p class="text-sm font-medium text-gray-900">{{ $reporterEmail }}</p>
              </div>
              @endif
              @if($communicationPreference === 'WhatsApp' && $reporterWhatsApp !== 'N/A')
              <div>
                <p class="text-xs text-gray-500">WhatsApp Number</p>
                <p class="text-sm font-medium text-gray-900">{{ $reporterWhatsApp }}</p>
              </div>
              @endif
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
                    @svg('heroicon-o-document', 'h-5 w-5 text-gray-400 flex-shrink-0')
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

    {{-- Floating Action Button --}}
    <button type="button"
            @click="showTrackingTokensModal = true; fetchTrackingTokens()"
            class="fixed top-6 right-6 z-40 inline-flex items-center justify-center w-12 h-12 bg-primary-500 hover:bg-primary-600 text-primary-900 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            title="View All Tracking Tokens">
      <x-heroicon-m-inbox class="h-6 w-6" />
    </button>

  </div>

  {{-- Tracking Tokens Slide Panel --}}
  <div x-show="showTrackingTokensModal"
       x-cloak
       @keydown.escape.window="showTrackingTokensModal = false"
       class="fixed inset-0 z-50"
       style="display: none;">
       
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
         x-show="showTrackingTokensModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="showTrackingTokensModal = false"></div>

    {{-- Slide Panel --}}
    <div class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl ring-1 ring-black/5 transform transition-transform duration-300 ease-out flex flex-col"
         x-show="showTrackingTokensModal"
         x-transition:enter="translate-x-full"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="translate-x-full"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">

      {{-- Header --}}
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <x-heroicon-o-inbox class="h-6 w-6 text-gray-600" />
          </div>
          <div>
            <h2 class="text-base font-semibold leading-6 text-gray-900">
              Tracking Tokens
            </h2>
            <p class="text-sm text-gray-600 mt-1">
              <span x-text="trackingTokensData?.project?.title || 'Loading...'" class="font-medium"></span>
              <span class="text-xs text-gray-500 ml-1" x-text="'(' + (trackingTokensData?.project?.code || '') + ')'"></span>
            </p>
          </div>
        </div>
        <button type="button"
                @click="showTrackingTokensModal = false"
                class="text-gray-400 hover:text-gray-600 transition-colors duration-200 p-1 rounded-md hover:bg-gray-50 flex-shrink-0">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      {{-- Content --}}
      <div class="flex-1 overflow-y-auto min-h-0">

        {{-- Loading State --}}
        <div x-show="loadingTokens" class="flex flex-col items-center justify-center py-12 px-6 space-y-4">

          {{-- Loading Spinner --}}
          <div class="relative">
            <x-icons.custom-icon name="refresh" class="w-8 h-8" color="text-primary-500" />
          </div>

          {{-- Loading Text --}}
          <div class="text-center space-y-1">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ __('auth.loading') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Loading tracking tokens...
            </p>
          </div>
        </div>

        {{-- Tokens List --}}
        <div x-show="!loadingTokens && trackingTokensData?.tracking_tokens?.length > 0" class="divide-y divide-gray-200">
          <template x-for="token in trackingTokensData.tracking_tokens" :key="token.token">
            <div class="group relative px-6 py-8 hover:bg-gray-50 transition-colors"
                 :class="{ 'bg-blue-50/50': token.token === '{{ $task->tracking_token }}' }">
              <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2 mb-2">

                    {{-- Tracking Token --}}
                    <code class="text-xs font-mono text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full"
                          :class="{ 'text-blue-700 bg-blue-100': token.token === '{{ $task->tracking_token }}' }"
                          x-text="token.token"></code>

                    {{-- Status Badge --}}
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize bg-primary-50 text-primary-700"
                          x-text="token.status.replace('_', ' ')"></span>

                    {{-- Current Badge --}}
                    <span x-show="token.token === '{{ $task->tracking_token }}'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      Current
                    </span>

                  </div>

                  {{-- Created At --}}
                  <div class="flex items-center mb-4">
                    <span class="text-xs text-gray-500" x-text="token.created_at"></span>
                  </div>

                  {{-- Title --}}
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900"
                       :class="{ 'text-blue-900': token.token === '{{ $task->tracking_token }}' }"
                       x-text="token.title"></p>

                    {{-- View Button --}}
                    <a :href="token.url"
                       target="_blank"
                       class="text-xs text-primary-500 hover:text-primary-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                      View →
                    </a>

                  </div>
                  
              </div>
            </div>
          </template>
        </div>

        {{-- Empty State --}}
        <div x-show="!loadingTokens && (!trackingTokensData?.tracking_tokens || trackingTokensData.tracking_tokens.length === 0)" class="flex flex-col items-center justify-center py-12 px-6 text-center">
          <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="text-sm font-medium text-gray-900 mb-1">No tracking tokens found</h3>
          <p class="text-sm text-gray-500">No issues have been submitted for this project yet.</p>
        </div>

        {{-- Error State --}}
        <div x-show="tokensError" class="flex flex-col items-center justify-center py-12 px-6 text-center">
          <svg class="h-12 w-12 text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <h3 class="text-sm font-medium text-gray-900 mb-1">Error loading tracking tokens</h3>
          <p class="text-sm text-red-500" x-text="tokensError"></p>
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

