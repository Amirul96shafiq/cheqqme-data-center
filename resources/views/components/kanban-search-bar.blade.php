@props([
    'search' => null,
    'placeholder' => null,
    'clearLabel' => null,
    'wireModel' => 'search',
    'wireClear' => 'clearSearch'
])

<div class="-mb-8 px-4">
    <div class="flex items-center gap-2">
        <div class="relative">

           <!-- Search icon (prefix) -->
           <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
               <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 dark:text-gray-500" />
           </div>
           
           <!-- Search input -->
           <input
               type="text"
               wire:model.live.debounce.300ms="{{ $wireModel }}"
               placeholder="{{ $placeholder ?: __('action.search_placeholder') }}"
                class="w-56 py-3 pl-10 pr-12 text-sm bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-xl text-gray-600 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 focus:outline-none focus:border-primary-500 dark:focus:border-primary-500 transition-all duration-200 hover:bg-white/40 dark:hover:bg-gray-800/40 focus:bg-white/40 dark:focus:bg-gray-800/40 focus:ring-1 focus:ring-primary-500"
               autocomplete="off"
           />
          @if($search)

              <!-- Clear button -->
              <button
                  wire:click="{{ $wireClear }}"
                  class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200 focus:outline-none hover:bg-white/20 dark:hover:bg-gray-700/30 disabled:opacity-50 disabled:cursor-not-allowed"
                  type="button"
                  title="{{ $clearLabel ?: __('action.clear_search') }}"
                  wire:loading.attr="disabled"
                  wire:target="{{ $wireClear }}"
              >
                  <!-- Loading spinner -->
                  <div wire:loading wire:target="{{ $wireClear }}" class="w-4 h-4">
                      <x-icons.custom-icon name="refresh" class="w-4 h-4" />
                  </div>
                  
                  <!-- Clear icon (hidden when loading) -->
                  <div wire:loading.remove wire:target="{{ $wireClear }}">
                      <x-heroicon-o-x-mark class="w-4 h-4" />
                  </div>
              </button>
          @endif
        </div>
    </div>
</div>
