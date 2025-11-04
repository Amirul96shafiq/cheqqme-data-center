<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100%;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Issue Status - {{ $task->tracking_token }} - {{ config('app.name') }}</title>

  {{-- Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

  @vite(['resources/css/app.css'])
</head>
<body class="antialiased font-sans bg-auto bg-no-repeat m-0 p-0" style="height: 100vh; margin: 0; padding: 0; background-image: url('{{ asset('images/issue-tracker-bg.png') }}'); background-position: top center; display: flex; flex-direction: column;">
    
  {{-- Content area --}}
  <div style="flex: 1; overflow-y: auto; min-height: 100vh;">
    {{-- Top spacer: 20% of viewport height --}}
    <div style="height: 20vh; flex-shrink-0; min-height: 20vh;"></div>
    <div class="flex items-center justify-center min-h-full py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-2xl w-full space-y-8">

        {{-- Header --}}
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            ISSUE TRACKER STATUS
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Tracking Code: <span class="font-mono font-semibold text-primary-500">{{ $task->tracking_token }}</span>
          </p>
        </div>

        {{-- Status Card --}}
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 space-y-6">
          
          {{-- Status Roadmap --}}
          <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <div class="text-center space-y-4">
              {{-- Heading --}}
              <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Roadmap</h2>
              
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
              <div class="flex items-center justify-center gap-2 flex-wrap">
                @foreach ($statusesToShow as $index => $status)
                  <div class="flex items-center {{ $index > 0 ? 'gap-2' : '' }}">
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
                      <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 {{ $arrowOpacity }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    @endif
                    {{-- Status badge --}}
                    @php
                      // Badge color: primary for current, gray for non-active
                      if ($status['isCurrent']) {
                        $badgeColor = 'bg-primary-500 text-primary-900';
                      } else {
                        $badgeColor = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
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
                        $badgeStyle = 'border border-gray-300 dark:border-gray-600 border-dashed';
                      }
                    @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $badgeColor }} {{ $badgeOpacity }} {{ $badgeStyle }}">
                      {{ $status['label'] }}
                    </span>
                  </div>
                @endforeach
              </div>
              
              {{-- Submitted on --}}
              <div class="pt-2">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Submitted on</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ $task->created_at->format('j/n/y') }} . {{ $task->created_at->format('h:i A') }}
                </p>
              </div>
            </div>
          </div>

          {{-- Reporter Information --}}
          <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Reporter Information</h3>
            <div class="space-y-2">
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Name</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->extra_information['reporter_name'] ?? 'N/A' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->extra_information['reporter_email'] ?? 'N/A' }}</p>
              </div>
            </div>
          </div>

          {{-- Project Information --}}
          @if($project)
          <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Project</h3>
            <p class="text-sm text-gray-900 dark:text-white">{{ $project->title }}</p>
          </div>
          @endif

          {{-- Issue Title --}}
          <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Issue Title</h3>
            <p class="text-sm text-gray-900 dark:text-white">{{ $task->title }}</p>
          </div>

          {{-- Description --}}
          <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Description</h3>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-md p-4">
              <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $task->description }}</p>
            </div>
          </div>

          {{-- Attachments --}}
          @if(!empty($task->attachments) && is_array($task->attachments))
          <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Attachments</h3>
            <div class="space-y-2">
              @foreach($task->attachments as $attachment)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-md">
                  <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ basename($attachment) }}</p>
                    </div>
                  </div>
                  <a href="{{ asset('storage/'.$attachment) }}" target="_blank" class="ml-3 text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium">
                    View
                  </a>
                </div>
              @endforeach
            </div>
          </div>
          @endif

          {{-- Actions --}}
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            @if($project)
            <a href="{{ route('issue-tracker.show', ['project' => $project->issue_tracker_code]) }}" 
               class="inline-flex items-center justify-center w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
              Submit Another Issue
            </a>
            @endif
          </div>

        </div>

        {{-- Footer --}}
        <div class="text-center">
          <span class="block mb-4 text-xs text-gray-500 dark:text-gray-400">Powered by:</span>
          <img src="{{ asset('logos/logo-dark-vertical.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-16 w-auto dark:hidden">
          <img src="{{ asset('logos/logo-dark-vertical.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-16 w-auto hidden dark:inline-block">
        </div>

      </div>
    </div>
  </div>

</body>
</html>

