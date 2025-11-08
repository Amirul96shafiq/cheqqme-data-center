@props(['config', 'columnId', 'record'])
@php
    // Normalize due date for client-side filtering
    $normalizedDueDate = null;
    try {
        if (!empty($record['due_date'])) {
            $normalizedDueDate = \Carbon\Carbon::parse($record['due_date'])->format('Y-m-d');
        } else {
            // Fallback: parse from formatted badge values if raw due_date is not present
            $attr = $record['attributes'] ?? [];
            foreach (['due_date_red', 'due_date_yellow', 'due_date_gray', 'due_date_green'] as $key) {
                $val = $attr[$key]['value'] ?? null;
                if (!empty($val)) {
                    try {
                        // Badges use j/n/y (e.g., 5/10/25)
                        $normalizedDueDate = \Carbon\Carbon::createFromFormat('j/n/y', $val)->format('Y-m-d');
                        break;
                    } catch (\Throwable $e) {
                        // Ignore parse errors and continue
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        $normalizedDueDate = null;
    }
@endphp

@php
    // Determine card type: issue_trackers if task has tracking_token OR status is 'issue_tracker'
    $cardType = 'tasks'; // Default to tasks
    
    // Check status first (most reliable, always in record)
    $status = $record['status'] ?? null;
    if ($status === 'issue_tracker') {
        $cardType = 'issue_trackers';
    } else {
        // Check for tracking_token if status is not 'issue_tracker'
        $trackingToken = $record['tracking_token'] 
            ?? $record['attributes']['tracking_token']['value'] 
            ?? null;
        
        // Only query database if tracking_token not found and we have an ID
        if (empty($trackingToken) && isset($record['id'])) {
            try {
                $task = \App\Models\Task::select('tracking_token')->find($record['id']);
                $trackingToken = $task->tracking_token ?? null;
            } catch (\Throwable $e) {
                // Ignore errors, default to tasks
            }
        }
        
        // Set to issue_trackers if tracking_token exists
        if (!empty($trackingToken) && trim($trackingToken) !== '') {
            $cardType = 'issue_trackers';
        }
    }
@endphp

{{-- Card container with interactive/non-interactive classes and sortable attributes --}}
<div
    @class([
        'ff-card kanban-card group',
        'ff-card--interactive' => $this->editAction() &&  ($this->editAction)(['record' => $record['id']])->isVisible(),
        'ff-card--non-interactive' => !$this->editAction()
    ])
    x-data="{ 
        filterActive: false,
        showFeaturedImages: {{ session('action_board_show_featured_images', true) ? 'true' : 'false' }}
    }"
    x-init="
        // Listen for filter events to disable drag and drop
        window.addEventListener('action-board-unified-filter', (e) => {
            const search = e?.detail?.search || '';
            const assignedTo = e?.detail?.assignedTo || [];
            const dueDate = e?.detail?.dueDate || { preset: null, from: null, to: null };
            const cardType = e?.detail?.cardType || 'all';
            filterActive = search.length > 0 || assignedTo.length > 0 || !!dueDate.preset || !!dueDate.from || !!dueDate.to || cardType !== 'all';
        });
        
        // Listen for featured images visibility changes
        window.addEventListener('featured-images-visibility-changed', (e) => {
            showFeaturedImages = e?.detail?.visible ?? true;
        });
    "
    x-sortable-handle
    x-sortable-item="{{ $record['id'] }}"
    x-bind:class="filterActive ? 'drag-disabled' : ''"
    x-on:dragstart="filterActive && $event.preventDefault()"
    x-on:drag="filterActive && $event.preventDefault()"
    x-on:dragenter="filterActive && $event.preventDefault()"
    x-on:dragover="filterActive && $event.preventDefault()"
    x-on:dragleave="filterActive && $event.preventDefault()"
    x-on:dragend="filterActive && $event.preventDefault()"
    x-on:drop="filterActive && $event.preventDefault()"
    @if(!empty($normalizedDueDate)) data-due-date="{{ $normalizedDueDate }}" @endif
    data-task-id="{{ $record['id'] }}"
    @if($this->editAction() &&  ($this->editAction)(['record' => $record['id']])->isVisible())
        wire:click="mountAction('edit', {record: '{{ $record['id'] }}'})"
    @endif
    data-card-type="{{ $cardType }}"
    @if(method_exists($this, 'isTaskHighlighted') && $this->isTaskHighlighted((object) ['id' => $record['id']])) data-highlighted="true" @endif
>
    <div class="ff-card__content">
        
        {{-- Featured image section --}}
        @if(!empty($record['attributes']['featured_image']['value']))
            <div class="ff-card__featured-image -mx-4 -mt-4 mb-3 relative" x-show="showFeaturedImages" x-cloak>
                
                {{-- Share button for cards with featured image --}}
                <button
                    class="ff-card__share-btn absolute top-[15px] right-[15px] z-20 bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-800 rounded-md px-2 py-[3px] shadow-md hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700 md:opacity-0 md:group-hover:opacity-100"
                    onclick="event.stopPropagation(); event.stopImmediatePropagation(); if (!this.dataset.touched) { shareTaskUrl(event, '{{ $record['id'] }}'); showCopiedBubble(this); }"
                    ontouchstart="event.stopPropagation(); event.stopImmediatePropagation(); this.dataset.touched = '1';"
                    ontouchend="event.stopPropagation(); event.stopImmediatePropagation(); this.dataset.touched = '1'; shareTaskUrlMobile(event, '{{ $record['id'] }}', this); setTimeout(() => delete this.dataset.touched, 300);"
                    title="Share Task"
                    type="button"
                >
                    @svg('heroicon-o-share', 'w-3 h-3 text-gray-600 dark:text-gray-300')
                </button>

                {{-- Featured image --}}
                <div class="block cursor-pointer"
                     onclick="window.location.href = '{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $record['id']]) }}'">
                    <img src="{{ $record['attributes']['featured_image']['value'] }}"
                         alt="Featured image"
                         class="w-full h-24 object-cover rounded-t-lg border-l border-r border-t border-gray-200 dark:border-gray-700 hover:opacity-90 transition-opacity"
                         loading="lazy"
                         decoding="async"
                         fetchpriority="low"
                         sizes="(max-width: 768px) 100vw, 280px">
                </div>

            </div>
        @endif

        {{-- If no featured image OR featured images are hidden, show card title and share button section --}}
        @if(empty($record['attributes']['featured_image']['value']))
            <div class="flex justify-between items-center mb-2">
                
                {{-- Card title --}}
                <h4 class="ff-card__title m-0">{{ Str::limit($record['title'], 60) }}</h4>

                {{-- Share button --}}
                <button
                    class="ff-card__badge inline-flex items-center px-2 py-[3px] rounded-md bg-white/90 dark:bg-gray-800/90 hover:bg-white  dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 hover:border-gray-300 text-xs cursor-pointer md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200 touch-manipulation"
                    onclick="event.stopPropagation(); event.stopImmediatePropagation(); if (!this.dataset.touched) { shareTaskUrl(event, '{{ $record['id'] }}'); showCopiedBubble(this); }"
                    ontouchstart="event.stopPropagation(); event.stopImmediatePropagation(); this.dataset.touched = '1';"
                    ontouchend="event.stopPropagation(); event.stopImmediatePropagation(); this.dataset.touched = '1'; shareTaskUrlMobile(event, '{{ $record['id'] }}', this); setTimeout(() => delete this.dataset.touched, 300);"
                    title="Share Task"
                    type="button"
                >
                    @svg('heroicon-o-share', 'w-3 h-3 text-gray-600 dark:text-gray-300')
                </button>

            </div>
        @else
            {{-- Show title/share button when featured images are hidden --}}
            <div class="flex justify-between items-center mb-2" x-show="!showFeaturedImages" x-cloak>
                
                {{-- Card title --}}
                <h4 class="ff-card__title m-0">{{ Str::limit($record['title'], 60) }}</h4>

                {{-- Share button --}}
                <button
                    class="ff-card__badge inline-flex items-center px-2 py-[3px] rounded-md bg-white/90 dark:bg-gray-800/90 hover:bg-white  dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 hover:border-gray-300 text-xs cursor-pointer md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-200 touch-manipulation"
                    onclick="event.stopPropagation(); event.stopImmediatePropagation(); if (!this.dataset.touched) { shareTaskUrl(event, '{{ $record['id'] }}'); showCopiedBubble(this); }"
                    ontouchstart="event.stopPropagation(); event.stopImmediatePropagation(); this.dataset.touched = '1';"
                    ontouchend="event.stopPropagation(); event.stopImmediatePropagation(); this.dataset.touched = '1'; shareTaskUrlMobile(event, '{{ $record['id'] }}', this); setTimeout(() => delete this.dataset.touched, 300);"
                    title="Share Task"
                    type="button"
                >
                    @svg('heroicon-o-share', 'w-3 h-3 text-gray-600 dark:text-gray-300')
                </button>

            </div>

            {{-- Card title (shown when featured image is visible) --}}
            <h4 class="ff-card__title" x-show="showFeaturedImages">{{ Str::limit($record['title'], 60) }}</h4>

        @endif

        {{-- Card description --}}
        @if(!empty($record['description']))
            <p class="ff-card__description">{{ $record['description'] }}</p>
        @endif

        {{-- Process and filter special badges --}}
        @php
            $attributes = collect($record['attributes'] ?? []);

            // Only show one assigned_to badge: prefer _self, else _username
            $assignedBadge = null;
            if (!empty($attributes['assigned_to_username_self']['value'])) {
                $assignedBadge = $attributes['assigned_to_username_self'];
            } elseif (!empty($attributes['assigned_to_username']['value'])) {
                $assignedBadge = $attributes['assigned_to_username'];
            }
            
            // Handle extra assigned users badge
            $extraAssignedBadge = null;
            if (!empty($attributes['assigned_to_extra_count_self']['value'])) {
                $extraAssignedBadge = $attributes['assigned_to_extra_count_self'];
            } elseif (!empty($attributes['assigned_to_extra_count']['value'])) {
                $extraAssignedBadge = $attributes['assigned_to_extra_count'];
            }
            $dueDateBadge = null;
            if (!empty($attributes['due_date_red']['value'])) {
                $dueDateBadge = $attributes['due_date_red'];
            } elseif (!empty($attributes['due_date_yellow']['value'])) {
                $dueDateBadge = $attributes['due_date_yellow'];
            } elseif (!empty($attributes['due_date_gray']['value'])) {
                $dueDateBadge = $attributes['due_date_gray'];
            } elseif (!empty($attributes['due_date_green']['value'])) {
                $dueDateBadge = $attributes['due_date_green'];
            }

            // Handle message count badge
            $messageCountBadge = null;
            if (!empty($attributes['message_count']['value']) && $attributes['message_count']['value'] > 0) {
                $messageCountBadge = $attributes['message_count'];
            }

            // Handle attachment count badge
            $attachmentCountBadge = null;
            if (!empty($attributes['attachment_count']['value']) && $attributes['attachment_count']['value'] > 0) {
                $attachmentCountBadge = $attributes['attachment_count'];
            }

            // Handle resource count badge
            $resourceCountBadge = null;
            if (!empty($attributes['resource_count']['value']) && $attributes['resource_count']['value'] > 0) {
                $resourceCountBadge = $attributes['resource_count'];
            }

            // Handle priority badge - only one will have a value based on task priority
            $priorityBadge = null;
            if (!empty($attributes['priority_low']['value'])) {
                $priorityBadge = $attributes['priority_low'];
            } elseif (!empty($attributes['priority_medium']['value'])) {
                $priorityBadge = $attributes['priority_medium'];
            } elseif (!empty($attributes['priority_high']['value'])) {
                $priorityBadge = $attributes['priority_high'];
            }

            // Remove from the attributes list so they're not rendered twice
            $filtered = $attributes->except(['assigned_to_username_self', 'assigned_to_username', 'assigned_to_full_username', 'all_assigned_usernames', 'assigned_to_extra_count_self', 'assigned_to_extra_count', 'due_date_red', 'due_date_yellow', 'due_date_gray', 'due_date_green', 'featured_image', 'message_count', 'attachment_count', 'resource_count', 'priority_low', 'priority_medium', 'priority_high']);

        @endphp
        
        {{-- Special badges layout: assigned, message/attachment/resource counts, priority, and due date --}}
        @if($assignedBadge || $extraAssignedBadge || $dueDateBadge || $messageCountBadge || $attachmentCountBadge || $resourceCountBadge || $priorityBadge)
            <div class="flex justify-between mt-5 mb-1 gap-2">
                
                {{-- Left side: assigned badge and counts --}}
                <div class="flex flex-col gap-1">
                    
                    {{-- Assigned badge --}}
                    @if($assignedBadge)
                        @php
                            
                            // Get assigned user IDs for filtering
                            $assignedUserIds = [];
                            
                            // Try to get from raw record data first
                            if (isset($record['assigned_to']) && !empty($record['assigned_to'])) {
                                $rawAssignedTo = $record['assigned_to'];
                                if (is_string($rawAssignedTo)) {
                                    $rawAssignedTo = json_decode($rawAssignedTo, true) ?? [];
                                }
                                if (is_array($rawAssignedTo)) {
                                    $assignedUserIds = array_map('intval', $rawAssignedTo);
                                }
                            }
                            
                            // Fall back to usernames from attributes if no raw data
                            if (empty($assignedUserIds)) {
                                $allUsernames = [];
                                $assignedAttributes = [
                                    'assigned_to_username_self',
                                    'assigned_to_username', 
                                    'assigned_to_full_username',
                                    'all_assigned_usernames'
                                ];
                                
                                foreach ($assignedAttributes as $attr) {
                                    $value = $attributes[$attr]['value'] ?? '';
                                    if (!empty($value)) {
                                        if (strpos($value, ', ') !== false) {
                                            $allUsernames = array_merge($allUsernames, explode(', ', $value));
                                        } else {
                                            $allUsernames[] = $value;
                                        }
                                    }
                                }
                                
                                $allUsernames = array_unique(array_filter(array_map('trim', $allUsernames)));
                                
                                if (!empty($allUsernames)) {
                                    $assignedUserIds = \App\Models\User::withTrashed()
                                        ->whereIn('name', $allUsernames)
                                        ->pluck('id')->toArray();
                                }
                            }

                        @endphp
                        
                        {{-- Assigned badge --}}
                        <div class="w-fit flex gap-1 items-center" 
                             data-assigned-user-ids="{{ implode(',', $assignedUserIds) }}">
                            @php
                                // Get the main assigned user from the Task model's first_assigned_user attribute
                                // This ensures we get the correct user that matches the badge display
                                $mainAssignedUser = null;
                                if (!empty($record['id'])) {
                                    $task = \App\Models\Task::withTrashed()->find($record['id']);
                                    if ($task) {
                                        $mainAssignedUser = $task->first_assigned_user;
                                    }
                                }
                                
                                // Fallback to array lookup if Task model lookup fails
                                if (!$mainAssignedUser && !empty($assignedUserIds)) {
                                    $firstAssignedUserId = $assignedUserIds[0];
                                    $mainAssignedUser = \App\Models\User::withTrashed()->find($firstAssignedUserId);
                                }
                            @endphp

                            @if($mainAssignedUser)
                                <x-clickable-avatar-wrapper :user="$mainAssignedUser">
                                    <x-flowforge::card-badge
                                        :label="$assignedBadge['label']"
                                        :value="$assignedBadge['value']"
                                        :color="$assignedBadge['color'] ?? 'default'"
                                        :icon="$assignedBadge['icon'] ?? null"
                                        :type="$assignedBadge['type'] ?? null"
                                        :badge="$assignedBadge['badge'] ?? null"
                                        :rounded="$assignedBadge['rounded'] ?? 'md'"
                                        :size="$assignedBadge['size'] ?? 'md'"
                                        :tooltip="$attributes['assigned_to_full_username']['value'] ?? null"
                                    />
                                </x-clickable-avatar-wrapper>
                            @else
                                <x-flowforge::card-badge
                                    :label="$assignedBadge['label']"
                                    :value="$assignedBadge['value']"
                                    :color="$assignedBadge['color'] ?? 'default'"
                                    :icon="$assignedBadge['icon'] ?? null"
                                    :type="$assignedBadge['type'] ?? null"
                                    :badge="$assignedBadge['badge'] ?? null"
                                    :rounded="$assignedBadge['rounded'] ?? 'md'"
                                    :size="$assignedBadge['size'] ?? 'md'"
                                    :tooltip="$attributes['assigned_to_full_username']['value'] ?? null"
                                />
                            @endif
                            @if($extraAssignedBadge)
                                <x-flowforge::card-badge
                                    :label="$extraAssignedBadge['label']"
                                    :value="$extraAssignedBadge['value']"
                                    :color="$extraAssignedBadge['color'] ?? 'default'"
                                    :icon="$extraAssignedBadge['icon'] ?? null"
                                    :type="$extraAssignedBadge['type'] ?? null"
                                    :badge="$extraAssignedBadge['badge'] ?? null"
                                    :rounded="$extraAssignedBadge['rounded'] ?? 'md'"
                                    :size="$extraAssignedBadge['size'] ?? 'md'"
                                    :tooltip="$attributes['all_assigned_usernames']['value'] ?? null"
                                />
                            @endif
                        </div>

                    @endif

                    {{-- Message, attachment, and resource count badges --}}
                    @if($messageCountBadge || $attachmentCountBadge || $resourceCountBadge)
                        <div class="flex gap-1">
                            @if($messageCountBadge)
                                <div class="w-fit">
                                    <x-flowforge::card-badge
                                        :label="$messageCountBadge['label']"
                                        :value="$messageCountBadge['value']"
                                        :color="$messageCountBadge['color'] ?? 'default'"
                                        :icon="$messageCountBadge['icon'] ?? null"
                                        :type="$messageCountBadge['type'] ?? null"
                                        :badge="$messageCountBadge['badge'] ?? null"
                                        :rounded="$messageCountBadge['rounded'] ?? 'md'"
                                        :size="$messageCountBadge['size'] ?? 'sm'"
                                    />
                                </div>
                            @endif
                            @if($attachmentCountBadge)
                                <div class="w-fit">
                                    <x-flowforge::card-badge
                                        :label="$attachmentCountBadge['label']"
                                        :value="$attachmentCountBadge['value']"
                                        :color="$attachmentCountBadge['color'] ?? 'default'"
                                        :icon="$attachmentCountBadge['icon'] ?? null"
                                        :type="$attachmentCountBadge['type'] ?? null"
                                        :badge="$attachmentCountBadge['badge'] ?? null"
                                        :rounded="$attachmentCountBadge['rounded'] ?? 'md'"
                                        :size="$attachmentCountBadge['size'] ?? 'sm'"
                                    />
                                </div>
                            @endif
                            @if($resourceCountBadge)
                                <div class="w-fit">
                                    <x-flowforge::card-badge
                                        :label="$resourceCountBadge['label']"
                                        :value="$resourceCountBadge['value']"
                                        :color="$resourceCountBadge['color'] ?? 'default'"
                                        :icon="$resourceCountBadge['icon'] ?? null"
                                        :type="$resourceCountBadge['type'] ?? null"
                                        :badge="$resourceCountBadge['badge'] ?? null"
                                        :rounded="$resourceCountBadge['rounded'] ?? 'md'"
                                        :size="$resourceCountBadge['size'] ?? 'sm'"
                                    />
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- Right side: due date and priority badges --}}
                <div class="flex flex-col gap-1.5 items-end">
                    @if($dueDateBadge)
                        <x-flowforge::card-badge
                            :label="$dueDateBadge['label']"
                            :value="$dueDateBadge['value']"
                            :color="$dueDateBadge['color'] ?? 'default'"
                            :icon="$dueDateBadge['icon'] ?? null"
                            :type="$dueDateBadge['type'] ?? null"
                            :badge="$dueDateBadge['badge'] ?? null"
                            :rounded="$dueDateBadge['rounded'] ?? 'md'"
                            :size="$dueDateBadge['size'] ?? 'md'"
                        />
                    @endif
                    @if($priorityBadge)
                        <x-flowforge::card-badge
                            :label="$priorityBadge['label']"
                            :value="$priorityBadge['value']"
                            :color="$priorityBadge['color'] ?? 'default'"
                            :icon="$priorityBadge['icon'] ?? null"
                            :type="$priorityBadge['type'] ?? null"
                            :badge="$priorityBadge['badge'] ?? null"
                            :rounded="$priorityBadge['rounded'] ?? 'md'"
                            :size="$priorityBadge['size'] ?? 'sm'"
                        />
                    @endif
                </div>

            </div>
        @endif
        
        {{-- Remaining attributes section --}}
        @if($filtered->filter(fn($attribute) => !empty($attribute['value']))->isNotEmpty())
            <div class="ff-card__attributes">
                @foreach($filtered as $attribute => $data)
                    @if(isset($data) && !empty($data['value']))
                        <x-flowforge::card-badge
                            :label="$data['label']"
                            :value="$data['value']"
                            :color="$data['color'] ?? 'default'"
                            :icon="$data['icon'] ?? null"
                            :type="$data['type'] ?? null"
                            :badge="$data['badge'] ?? null"
                            :rounded="$data['rounded'] ?? 'md'"
                            :size="$data['size'] ?? 'md'"
                        />
                    @endif
                @endforeach
            </div>
        @endif

    </div>
</div>

