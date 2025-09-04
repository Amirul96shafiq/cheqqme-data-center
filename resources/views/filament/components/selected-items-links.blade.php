<!-- resources/views/filament/components/selected-items-links.blade.php -->
<!-- This component displays selected projects, documents, and important URLs with clickable links -->
@php
    use App\Models\Project;
    use App\Models\Document;
    use App\Models\ImportantUrl;
@endphp
<!-- Start of the selected items links component -->
<div class="space-y-4">
    <!-- Projects -->
    @if(!empty($selectedProjects))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="flex items-center justify-center w-5 h-5 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <x-heroicon-o-briefcase class="w-3 h-3 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('task.form.selected_projects') }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ count($selectedProjects) }} {{ __('task.form.item') }}</p>
                </div>
            </div>
            <!-- Projects list -->
            <div class="space-y-2">
                @foreach(Project::whereIn('id', $selectedProjects)->withTrashed()->get() as $project)
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <x-heroicon-o-folder class="w-3 h-3 text-gray-400" />
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate block">
                                    {{ Str::limit($project->title, 100) }}
                                </span>
                                @if($project->deleted_at)
                                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">Deleted</span>
                                @endif
                            </div>
                        </div>
                        <!-- Projects actions -->
                        <div class="flex gap-1">
                            <a href="{{ route('filament.admin.resources.projects.edit', $project) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <x-heroicon-o-pencil class="w-3 h-3" />
                                <span class="hidden sm:inline">Edit</span>
                            </a>
                            @if($project->project_url)
                                <a href="{{ $project->project_url }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-600 rounded-md hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
                                    <span class="hidden sm:inline">Open</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documents -->
    @if(!empty($selectedDocuments))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="flex items-center justify-center w-5 h-5 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <x-heroicon-o-document-text class="w-3 h-3 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('task.form.selected_documents') }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ count($selectedDocuments) }} {{ __('task.form.item') }}</p>
                </div>
            </div>
            <!-- Documents list -->
            <div class="space-y-2">
                @foreach(Document::whereIn('id', $selectedDocuments)->withTrashed()->get() as $document)
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <x-heroicon-o-document class="w-3 h-3 text-gray-400" />
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate block">
                                    {{ Str::limit($document->title, 100) }}
                                </span>
                                @if($document->deleted_at)
                                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">Deleted</span>
                                @endif
                            </div>
                        </div>
                        <!-- Documents actions -->
                        <div class="flex gap-1">
                            <a href="{{ route('filament.admin.resources.documents.edit', $document) }}"
                              target="_blank"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <x-heroicon-o-pencil class="w-3 h-3" />
                                <span class="hidden sm:inline">Edit</span>
                            </a>
                            @if($document->url)
                                <a href="{{ $document->url }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-600 rounded-md hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
                                    <span class="hidden sm:inline">View</span>
                                </a>
                            @endif
                            @if($document->file_path)
                                <a href="{{ asset('storage/' . $document->file_path) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-600 rounded-md hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                                    <x-heroicon-o-arrow-down-tray class="w-3 h-3" />
                                    <span class="hidden sm:inline">Download</span>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="flex items-center justify-center w-5 h-5 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <x-heroicon-o-link class="w-3 h-3 text-gray-600 dark:text-gray-400" />
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('task.form.selected_important_urls') }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ count($selectedUrls) }} {{ __('task.form.item') }}</p>
                </div>
            </div>
            <!-- Important URLs list -->
            <div class="space-y-2">
                @foreach(ImportantUrl::whereIn('id', $selectedUrls)->withTrashed()->get() as $url)
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <x-heroicon-o-globe-alt class="w-3 h-3 text-gray-400" />
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate block">
                                    {{ Str::limit($url->title, 100) }}
                                </span>
                                @if($url->url)
                                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ parse_url($url->url, PHP_URL_HOST) }}
                                    </span>
                                @endif
                                @if($url->deleted_at)
                                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">Deleted</span>
                                @endif
                            </div>
                        </div>
                        <!-- Important URLs actions -->
                        <div class="flex gap-1">
                            <a href="{{ route('filament.admin.resources.important-urls.edit', $url) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <x-heroicon-o-pencil class="w-3 h-3" />
                                <span class="hidden sm:inline">Edit</span>
                            </a>
                            @if($url->url)
                                <a href="{{ $url->url }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-600 rounded-md hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
                                    <span class="hidden sm:inline">Visit</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
