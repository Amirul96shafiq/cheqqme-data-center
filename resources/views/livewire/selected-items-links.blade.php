<div>
    <!-- This component displays selected projects, documents, and important URLs with clickable links in accordions -->
    <div class="space-y-4" 
         x-data="{ 
            accordionStates: {
                projects: true,
                documents: true,
                urls: true
            },
            toggleAccordion(section, event) {
                event.preventDefault();
                event.stopPropagation();
                this.accordionStates[section] = !this.accordionStates[section];
                return false;
            }
         }"
         wire:ignore.self>
        <!-- Projects -->
        @if(!empty($selectedProjects))
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <div @click="toggleAccordion('projects', $event)" 
                     @click.stop
                     @click.prevent
                     class="w-full px-4 py-3 text-left bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors rounded-t-lg border-b border-gray-200 dark:border-gray-700 cursor-pointer select-none">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('task.form.selected_projects') }}</h4>
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-500 transition-transform" x-bind:class="accordionStates.projects ? 'rotate-180' : ''" />
                    </div>
                </div>
                <!-- Projects list -->
                <div x-show="accordionStates.projects" x-transition class="p-4 space-y-2">
                    @foreach(Project::whereIn('id', $selectedProjects)->withTrashed()->get() as $project)
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $project->title }}
                                @if($project->deleted_at)
                                    <span class="text-red-500 text-xs">(deleted)</span>
                                @endif
                            </span>
                            <!-- Projects actions -->
                            @if($project->project_url)
                                <a href="{{ $project->project_url }}" 
                                   target="_blank" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-600 border border-primary-600 rounded-md hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                    <x-heroicon-o-link class="w-3 h-3" />
                                    {{ __('task.form.open_project_url') }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Documents -->
        @if(!empty($selectedDocuments))
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <div @click="toggleAccordion('documents', $event)" 
                     @click.stop
                     @click.prevent
                     class="w-full px-4 py-3 text-left bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors rounded-t-lg border-b border-gray-200 dark:border-gray-700 cursor-pointer select-none">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('task.form.selected_documents') }}</h4>
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-500 transition-transform" x-bind:class="accordionStates.documents ? 'rotate-180' : ''" />
                    </div>
                </div>
                <!-- Documents list -->
                <div x-show="accordionStates.documents" x-transition class="p-4 space-y-2">
                    @foreach(Document::whereIn('id', $selectedDocuments)->withTrashed()->get() as $document)
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $document->title }}
                                @if($document->deleted_at)
                                    <span class="text-red-500 text-xs">(deleted)</span>
                                @endif
                            </span>
                            <div class="flex gap-2">
                                <!-- Documents actions -->
                                @if($document->url)
                                    <a href="{{ $document->url }}" 
                                       target="_blank" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-600 border border-primary-600 rounded-md hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-link class="w-3 h-3" />
                                        {{ __('task.form.open_document_url') }}
                                    </a>
                                @endif
                                @if($document->file_path)
                                    <a href="{{ asset('storage/' . $document->file_path) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-600 border border-primary-600 rounded-md hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <x-heroicon-o-document-arrow-down class="w-3 h-3" />
                                        {{ __('task.form.download_document_file') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Important URLs -->
        @if(!empty($selectedUrls))
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <div @click="toggleAccordion('urls', $event)" 
                     @click.stop
                     @click.prevent
                     class="w-full px-4 py-3 text-left bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors rounded-t-lg border-b border-gray-200 dark:border-gray-700 cursor-pointer select-none">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('task.form.selected_important_urls') }}</h4>
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-500 transition-transform" x-bind:class="accordionStates.urls ? 'rotate-180' : ''" />
                    </div>
                </div>
                <!-- Important URLs list -->
                <div x-show="accordionStates.urls" x-transition class="p-4 space-y-2">
                    @foreach(ImportantUrl::whereIn('id', $selectedUrls)->withTrashed()->get() as $url)
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $url->title }}
                                @if($url->deleted_at)
                                    <span class="text-red-500 text-xs">(deleted)</span>
                                @endif
                            </span>
                            <!-- Important URLs actions -->
                            @if($url->url)
                                <a href="{{ $url->url }}" 
                                   target="_blank" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-600 border border-primary-600 rounded-md hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                    <x-heroicon-o-link class="w-3 h-3" />
                                    {{ __('task.form.open_important_url') }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
