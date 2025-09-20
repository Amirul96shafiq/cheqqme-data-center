@props(['config', 'columnId', 'record'])
{{-- Card container with interactive/non-interactive classes and sortable attributes --}}
<div
    @class([
        'ff-card kanban-card',
        'ff-card--interactive' => $this->editAction() &&  ($this->editAction)(['record' => $record['id']])->isVisible(),
        'ff-card--non-interactive' => !$this->editAction()
    ])
    x-sortable-handle
    x-sortable-item="{{ $record['id'] }}"
    @if($this->editAction() &&  ($this->editAction)(['record' => $record['id']])->isVisible())
        wire:click="mountAction('edit', {record: '{{ $record['id'] }}'})"
    @endif
    @if(method_exists($this, 'isTaskHighlighted') && $this->isTaskHighlighted((object) ['id' => $record['id']])) data-highlighted="true" @endif
>
    <div class="ff-card__content">
        {{-- Featured image section --}}
        @if(!empty($record['attributes']['featured_image']['value']))
            <div class="ff-card__featured-image -mx-4 -mt-4 mb-3 relative">
                {{-- Share button for cards with featured image --}}
                <button
                    class="ff-card__share-btn absolute top-2 right-2 z-10 bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-800 rounded-md px-2 py-[3px] shadow-md hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700"
                    onclick="shareTaskUrl(event, '{{ $record['id'] }}'); showCopiedBubble(this);"
                    title="Share Task"
                >
                    @svg('heroicon-o-share', 'w-3 h-3 text-gray-600 dark:text-gray-300')
                </button>
                <div class="block cursor-pointer"
                     onclick="window.location.href = '{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $record['id']]) }}'">
                    <img src="{{ $record['attributes']['featured_image']['value'] }}"
                         alt="Featured image"
                         class="w-full h-32 object-cover rounded-t-lg border-l border-r border-t border-gray-200 dark:border-gray-700 hover:opacity-90 transition-opacity">
                </div>
            </div>
        @endif

        {{-- If no featured image, show card title and share button section --}}
        @if(empty($record['attributes']['featured_image']['value']))
            <div class="flex justify-between items-center mb-2">
                <h4 class="ff-card__title m-0">{{ Str::limit($record['title'], 60) }}</h4>
                <span
                    class="ff-card__badge inline-flex items-center px-2 py-[3px] rounded-md bg-white/90 dark:bg-gray-800/90 hover:bg-white  dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 hover:border-gray-300 text-xs cursor-pointer"
                    onclick="shareTaskUrl(event, '{{ $record['id'] }}'); showCopiedBubble(this);"
                    title="Share Task"
                >
                    @svg('heroicon-o-share', 'w-3 h-3 text-gray-600 dark:text-gray-300')
                </span>
            </div>
        @else
            {{-- Card title --}}
            <h4 class="ff-card__title">{{ Str::limit($record['title'], 60) }}</h4>
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
            // Remove from the attributes list so they're not rendered twice
            $filtered = $attributes->except(['assigned_to_username_self', 'assigned_to_username', 'assigned_to_full_username', 'all_assigned_usernames', 'assigned_to_extra_count_self', 'assigned_to_extra_count', 'due_date_red', 'due_date_yellow', 'due_date_gray', 'due_date_green', 'featured_image', 'message_count', 'attachment_count', 'resource_count']);
        @endphp
        
        {{-- Special badges layout: assigned, message/attachment/resource counts, and due date --}}
        @if($assignedBadge || $extraAssignedBadge || $dueDateBadge || $messageCountBadge || $attachmentCountBadge || $resourceCountBadge)
            <div class="flex justify-between mt-5 mb-1 gap-2">
                {{-- Left side: assigned badge and counts --}}
                <div class="flex flex-col gap-1">
                    {{-- Assigned badge --}}
                    @if($assignedBadge)
                        <div class="w-fit flex gap-1 items-center">
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
                {{-- Right side: due date badge --}}
                <div>
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

