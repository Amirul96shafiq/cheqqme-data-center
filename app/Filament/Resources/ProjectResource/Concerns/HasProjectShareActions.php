<?php

namespace App\Filament\Resources\ProjectResource\Concerns;

use App\Filament\Resources\ProjectResource;
use App\Models\Task;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\Action;

trait HasProjectShareActions
{
    protected static function getIssueTrackerTextForCopy($record): string
    {
        $issueTrackerUrl = $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : 'TBD';
        $projectTitle = $record->title ?? 'TBD';

        return "Good day everyone ✨,\n\nHere's the issue tracker link for {$projectTitle} project.\n\n👉 {$issueTrackerUrl}\n\nPlease use this link to submit any issues or feedback related to this project.\n\nThank you! ☺️";
    }

    protected static function getAllIssueStatusLinksForCopy($record): string
    {
        $projectTitle = $record->title ?? 'TBD';
        $projectId = $record->id;

        $trackingTasks = Task::query()
            ->whereNotNull('tracking_token')
            ->with('updatedBy')
            ->where(function ($query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($trackingTasks->isEmpty()) {
            return "Hi team 👋,\n\nThere are currently no active issue tracking tokens for the {$projectTitle} project.\n\nWhen issues are submitted, their status links will appear here.\n\nThank you!";
        }

        $linksText = "Hi team 👋,\n\nHere are all the current issue status links for the {$projectTitle} project:\n\n";

        foreach ($trackingTasks as $task) {
            $issueTitle = $task->title ?: __('project.actions.issue_status_no_title');
            $statusLabel = static::formatIssueStatusLabel($task->status);
            $statusUrl = route('issue-tracker.status', ['token' => $task->tracking_token]);
            $submittedAt = $task->created_at?->format('j/n/y, h:i A');

            $linksText .= "🔹 {$issueTitle}\n";
            $linksText .= "   Status: {$statusLabel}\n";
            $linksText .= "   Link: {$statusUrl}\n";
            if ($submittedAt) {
                $linksText .= "   Submitted: {$submittedAt}\n";
            }
            $linksText .= "\n";
        }

        $linksText .= "Please use these links to check the latest status of each issue.\n\nThank you!";

        return $linksText;
    }

    protected static function formatIssueStatusLabel(?string $status): string
    {
        if (empty($status)) {
            return __('project.actions.issue_status_unknown');
        }

        $translationKey = "action.status.{$status}";
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    protected static function getWishlistTrackerTextForCopy($record): string
    {
        $wishlistTrackerUrl = $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : 'TBD';
        $projectTitle = $record->title ?? 'TBD';

        return "Good day everyone ✨,\n\nHere's the wishlist tracker link for {$projectTitle} project.\n\n👉 {$wishlistTrackerUrl}\n\nPlease use this link to submit any wishlist items or feature requests related to this project.\n\nThank you! ☺️";
    }

    protected static function getAllWishlistStatusLinksForCopy($record): string
    {
        $projectTitle = $record->title ?? 'TBD';
        $projectId = $record->id;

        $wishlistTasks = Task::wishlistTokens()
            ->with('updatedBy')
            ->where(function ($query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($wishlistTasks->isEmpty()) {
            return "Hi team 👋,\n\nThere are currently no active wishlist tracking tokens for the {$projectTitle} project.\n\nWhen wishlist items are submitted, their status links will appear here.\n\nThank you!";
        }

        $linksText = "Hi team 👋,\n\nHere are all the current wishlist status links for the {$projectTitle} project:\n\n";

        foreach ($wishlistTasks as $task) {
            $wishlistTitle = $task->title ?: __('project.actions.wishlist_status_no_title');
            $statusLabel = static::formatWishlistStatusLabel($task->status);
            $statusUrl = route('wishlist-tracker.status', ['token' => $task->tracking_token]);
            $submittedAt = $task->created_at?->format('j/n/y, h:i A');

            $linksText .= "🔹 {$wishlistTitle}\n";
            $linksText .= "   Status: {$statusLabel}\n";
            $linksText .= "   Link: {$statusUrl}\n";
            if ($submittedAt) {
                $linksText .= "   Submitted: {$submittedAt}\n";
            }
            $linksText .= "\n";
        }

        $linksText .= "Please use these links to check the latest status of each wishlist item.\n\nThank you!";

        return $linksText;
    }

    protected static function formatWishlistStatusLabel(?string $status): string
    {
        if (empty($status)) {
            return __('project.actions.wishlist_status_unknown');
        }

        $translationKey = "action.status.{$status}";
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Get the URL for editing a project record.
     * This method can be overridden in classes using this trait.
     */
    protected static function getProjectEditUrl($record): string
    {
        return ProjectResource::getUrl('edit', ['record' => $record->id]);
    }

    /**
     * Create a share action with modal form and copy functionality.
     */
    protected static function makeShareAction(
        string $name,
        string $label,
        string $modalHeading,
        string $modalDescription,
        string $formFieldName,
        string $formFieldLabel,
        callable $getTextCallback,
        string $color = 'primary',
        ?callable $getEditUrlCallback = null,
        ?string $editActionLabel = null,
        ?string $editActionIcon = null
    ): Action {
        $getEditUrlCallback = $getEditUrlCallback ?? fn ($record) => static::getProjectEditUrl($record);
        $editActionLabel = $editActionLabel ?? __('project.actions.edit_project');
        $editActionIcon = $editActionIcon ?? 'heroicon-o-pencil-square';

        return Tables\Actions\Action::make($name)
            ->label($label)
            ->icon('heroicon-o-share')
            ->color($color)
            ->modalWidth('2xl')
            ->modalHeading($modalHeading)
            ->modalDescription($modalDescription)
            ->form(function ($record) use ($getTextCallback, $formFieldName, $formFieldLabel) {
                $text = $getTextCallback($record);

                return [
                    Forms\Components\Textarea::make($formFieldName)
                        ->label($formFieldLabel)
                        ->default($text)
                        ->disabled()
                        ->rows(12)
                        ->extraInputAttributes([
                            'class' => 'font-mono text-sm !resize-none',
                            'style' => 'resize: none !important; max-height: none !important;',
                            'x-init' => '$el.style.resize = "none"',
                        ])
                        ->columnSpanFull(),
                ];
            })
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->extraModalFooterActions(function ($record, $livewire) use ($getTextCallback, $getEditUrlCallback, $editActionLabel, $editActionIcon, $color) {
                $actions = [];

                // Detect mobile device and hide copy button on mobile
                $userAgent = request()->userAgent() ?? '';
                $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

                // Only show copy button on desktop, hide on mobile so users can manually copy from preview
                if (! $isMobile) {
                    $actions[] = Tables\Actions\Action::make('copy_to_clipboard')
                        ->label(__('project.actions.copy_to_clipboard'))
                        ->icon('heroicon-o-clipboard-document')
                        ->color($color)
                        ->extraAttributes([
                            'x-data' => '{}',
                            'x-on:copy-success.window' => 'showCopiedBubble($el)',
                        ])
                        ->action(function () use ($record, $livewire, $getTextCallback) {
                            $text = $getTextCallback($record);

                            // Dispatch browser event with the text to copy and success callback
                            $livewire->dispatch('copy-to-clipboard-with-callback', text: $text);
                        });
                }

                $actions[] = Tables\Actions\Action::make('edit_project')
                    ->label($editActionLabel)
                    ->icon($editActionIcon)
                    ->color('gray')
                    ->url(fn ($record) => $getEditUrlCallback($record))
                    ->close();

                return $actions;
            });
    }

    /**
     * Get share issue tracker link action.
     */
    protected static function getShareIssueTrackerLinkAction(?callable $getEditUrlCallback = null): Action
    {
        return static::makeShareAction(
            'share_issue_tracker_link',
            __('project.actions.share_issue_tracker_link'),
            __('project.actions.share_issue_tracker_link'),
            __('project.actions.share_issue_tracker_link_description'),
            'issue_tracker_preview',
            __('project.actions.issue_tracker_preview'),
            fn ($record) => static::getIssueTrackerTextForCopy($record),
            'primary',
            $getEditUrlCallback
        )->visible(fn ($record) => ! $record->trashed() && $record->issue_tracker_code);
    }

    /**
     * Get share all issue status links action.
     */
    protected static function getShareAllIssueStatusLinksAction(?callable $getEditUrlCallback = null): Action
    {
        $getEditUrlCallback = $getEditUrlCallback ?? fn ($record) => static::getProjectEditUrl($record).'?activeRelationManager=0';

        return static::makeShareAction(
            'share_all_issue_status_link',
            __('project.actions.share_all_issue_status_link'),
            __('project.actions.share_all_issue_status_link'),
            __('project.actions.share_all_issue_status_link_description'),
            'all_issue_status_preview',
            __('project.actions.all_issue_status_preview'),
            fn ($record) => static::getAllIssueStatusLinksForCopy($record),
            'primary',
            $getEditUrlCallback,
            __('project.actions.view_tracking_tokens'),
            'heroicon-o-eye'
        )->visible(fn ($record) => ! $record->trashed() && $record->issue_tracker_code);
    }

    /**
     * Get share wishlist tracker link action.
     */
    protected static function getShareWishlistTrackerLinkAction(?callable $getEditUrlCallback = null): Action
    {
        return static::makeShareAction(
            'share_wishlist_tracker_link',
            __('project.actions.share_wishlist_tracker_link'),
            __('project.actions.share_wishlist_tracker_link'),
            __('project.actions.share_wishlist_tracker_link_description'),
            'wishlist_tracker_preview',
            __('project.actions.wishlist_tracker_preview'),
            fn ($record) => static::getWishlistTrackerTextForCopy($record),
            'success',
            $getEditUrlCallback
        )->visible(fn ($record) => ! $record->trashed() && $record->wishlist_tracker_code);
    }

    /**
     * Get share all wishlist status links action.
     */
    protected static function getShareAllWishlistStatusLinksAction(?callable $getEditUrlCallback = null): Action
    {
        $getEditUrlCallback = $getEditUrlCallback ?? fn ($record) => static::getProjectEditUrl($record).'?activeRelationManager=0';

        return static::makeShareAction(
            'share_all_wishlist_status_link',
            __('project.actions.share_all_wishlist_status_link'),
            __('project.actions.share_all_wishlist_status_link'),
            __('project.actions.share_all_wishlist_status_link_description'),
            'all_wishlist_status_preview',
            __('project.actions.all_wishlist_status_preview'),
            fn ($record) => static::getAllWishlistStatusLinksForCopy($record),
            'success',
            $getEditUrlCallback,
            __('project.actions.view_wishlists'),
            'heroicon-o-eye'
        )->visible(fn ($record) => ! $record->trashed() && $record->wishlist_tracker_code);
    }
}
