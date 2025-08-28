@props(['config', 'columnId', 'record'])
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
        @if(!empty($record['attributes']['featured_image']['value']))
            <div class="ff-card__featured-image -mx-4 -mt-4 mb-3">
                <img src="{{ $record['attributes']['featured_image']['value'] }}"
                     alt="Featured image"
                     class="w-full h-32 object-cover rounded-t-lg border-l border-r border-t border-gray-200 dark:border-gray-700">
            </div>
        @endif
        <h4 class="ff-card__title">{{ $record['title'] }}</h4>

        @if(!empty($record['description']))
            <p class="ff-card__description">{{ $record['description'] }}</p>
        @endif

        @php
            $attributes = collect($record['attributes'] ?? []);
            // Only show one assigned_to badge: prefer _self, else _username
            $assignedBadge = null;
            if (!empty($attributes['assigned_to_username_self']['value'])) {
                $assignedBadge = $attributes['assigned_to_username_self'];
            } elseif (!empty($attributes['assigned_to_username']['value'])) {
                $assignedBadge = $attributes['assigned_to_username'];
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
            // Remove both from the attributes list so they're not rendered twice
            $filtered = $attributes->except(['assigned_to_username_self', 'assigned_to_username', 'due_date_red', 'due_date_yellow', 'due_date_gray', 'due_date_green', 'featured_image', 'message_count']);
        @endphp
        @if($assignedBadge || $dueDateBadge || $messageCountBadge)
            <div class="flex justify-between mt-5 mb-1 gap-2">
                <div class="flex flex-col gap-1">
                    @if($assignedBadge)
                        <div class="w-fit">
                            <x-flowforge::card-badge
                                :label="$assignedBadge['label']"
                                :value="$assignedBadge['value']"
                                :color="$assignedBadge['color'] ?? 'default'"
                                :icon="$assignedBadge['icon'] ?? null"
                                :type="$assignedBadge['type'] ?? null"
                                :badge="$assignedBadge['badge'] ?? null"
                                :rounded="$assignedBadge['rounded'] ?? 'md'"
                                :size="$assignedBadge['size'] ?? 'md'"
                            />
                        </div>
                    @endif
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
                                :size="$messageCountBadge['size'] ?? 'md'"
                            />
                        </div>
                    @endif
                </div>
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

