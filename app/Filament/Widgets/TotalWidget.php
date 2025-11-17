<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ActionBoard;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ImportantUrlResource;
use App\Filament\Resources\MeetingLinkResource;
use App\Filament\Resources\PhoneNumberResource;
use App\Filament\Resources\TrelloBoardResource;
use App\Models\Client;
use App\Models\ImportantUrl;
use App\Models\MeetingLink;
use App\Models\PhoneNumber;
use App\Models\Task;
use App\Models\TrelloBoard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TotalWidget extends BaseWidget
{
    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 2,
        'xl' => 2,
        '2xl' => 2,
    ];

    public int $viewState = 0; // 0=tasks, 1=issues, 2=wishlists

    public function mount(): void
    {
        $this->viewState = $this->getStoredViewPreference();
    }

    protected function getColumns(): int
    {
        return 6;
    }

    public function toggleView(): void
    {
        $this->viewState = ($this->viewState + 1) % 3; // Cycle through 0, 1, 2

        $this->persistViewPreference();
    }

    protected function getStats(): array
    {
        $stats = $this->getSharedStats();

        array_unshift($stats, $this->getPrimaryStat());

        return $stats;
    }

    protected function getUserTasksCount(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            return 0;
        }

        // For SQLite, we need to use a different approach since JSON_CONTAINS is not available
        // We'll use whereRaw with SQLite's JSON functions
        // Only count tasks with status: todo, in_progress, to_review (exclude completed and archived)
        return Task::where(function ($query) use ($userId) {
            $query->whereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%"'.$userId.'"%'])
                ->orWhereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%'.$userId.'%']);
        })
            ->whereIn('status', ['todo', 'in_progress', 'to_review'])
            ->count();
    }

    protected function getUserIssueTrackersCount(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            return 0;
        }

        // Count issue trackers assigned to the user
        // Issue trackers are tasks with status 'issue_tracker' OR have a tracking_token
        return Task::where(function ($query) use ($userId) {
            $query->whereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%"'.$userId.'"%'])
                ->orWhereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%'.$userId.'%']);
        })
            ->where(function ($query) {
                $query->where('status', 'issue_tracker')
                    ->orWhereNotNull('tracking_token');
            })
            ->whereIn('status', ['todo', 'in_progress', 'to_review', 'issue_tracker'])
            ->count();
    }

    protected function getUserMeetingLinksCount(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            return 0;
        }

        return MeetingLink::where(function ($query) use ($userId) {
            $query->whereRaw('JSON_EXTRACT(user_ids, "$") LIKE ?', ['%"'.$userId.'"%'])
                ->orWhereRaw('JSON_EXTRACT(user_ids, "$") LIKE ?', ['%'.$userId.'%']);
        })->count();
    }

    protected function getUserWishlistCount(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            return 0;
        }

        // Count wishlist tasks assigned to the user
        return Task::where(function ($query) use ($userId) {
            $query->whereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%"'.$userId.'"%'])
                ->orWhereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%'.$userId.'%']);
        })
            ->where('tracking_token', 'LIKE', 'CHEQQ-WSH-%')
            ->count();
    }

    private function getSharedStats(): array
    {
        return [
            Stat::make(__('dashboard.your_meeting_links.title'), $this->getUserMeetingLinksCount())
                ->description(__('dashboard.your_meeting_links.description', ['total' => MeetingLink::count()]))
                ->color('primary')
                ->icon(MeetingLinkResource::getNavigationIcon())
                ->url(route('filament.admin.resources.meeting-links.index')),

            Stat::make(__('dashboard.total_trello_boards.title'), TrelloBoard::count())
                ->description(__('dashboard.actions.view_all_trello_boards'))
                ->color('primary')
                ->icon(TrelloBoardResource::getNavigationIcon())
                ->url(route('filament.admin.resources.trello-boards.index')),

            Stat::make(__('dashboard.total_clients.title'), Client::count())
                ->description(__('dashboard.actions.view_all_clients'))
                ->color('primary')
                ->icon(ClientResource::getNavigationIcon())
                ->url(route('filament.admin.resources.clients.index')),

            Stat::make(__('dashboard.total_important_urls.title'), ImportantUrl::count())
                ->description(__('dashboard.actions.view_all_important_urls'))
                ->color('primary')
                ->icon(ImportantUrlResource::getNavigationIcon())
                ->url(route('filament.admin.resources.important-urls.index')),

            Stat::make(__('dashboard.total_phone_numbers.title'), PhoneNumber::count())
                ->description(__('dashboard.actions.view_all_phone_numbers'))
                ->color('primary')
                ->icon(PhoneNumberResource::getNavigationIcon())
                ->url(route('filament.admin.resources.phone-numbers.index')),
        ];
    }

    private function getPrimaryStat(): Stat
    {
        switch ($this->viewState) {
            case 1: // Issues
                $issueTrackerCount = $this->getUserIssueTrackersCount();
                $totalIssueTrackers = $this->getTotalIssueTrackersCount();

                return StatWithCycleButton::make(__('dashboard.your_issue_trackers.title'), $issueTrackerCount)
                    ->description(__('dashboard.your_issue_trackers.description', ['total' => $totalIssueTrackers]))
                    ->color('primary')
                    ->icon(ActionBoard::getNavigationIcon())
                    ->url(route('filament.admin.pages.action-board', ['type' => 'issue']))
                    ->extraAttributes([
                        'class' => 'relative',
                    ]);

            case 2: // Wishlists
                $wishlistCount = $this->getUserWishlistCount();
                $totalWishlists = Task::wishlistTokens()->count();

                return StatWithCycleButton::make(__('dashboard.your_wishlist.title'), $wishlistCount)
                    ->description(__('dashboard.your_wishlist.description', ['total' => $totalWishlists]))
                    ->color('primary')
                    ->icon(ActionBoard::getNavigationIcon())
                    ->url(route('filament.admin.pages.action-board', ['type' => 'wishlist']))
                    ->extraAttributes([
                        'class' => 'relative',
                    ]);

            default: // Tasks (case 0)
                return StatWithCycleButton::make(__('dashboard.your_tasks.title'), $this->getUserTasksCount())
                    ->description(__('dashboard.your_tasks.description', ['total' => Task::count()]))
                    ->color('primary')
                    ->icon(ActionBoard::getNavigationIcon())
                    ->url(route('filament.admin.pages.action-board', ['type' => 'task']))
                    ->extraAttributes([
                        'class' => 'relative',
                    ]);
        }
    }

    private function getTotalIssueTrackersCount(): int
    {
        return Task::where(function ($query) {
            $query->where('status', 'issue_tracker')
                ->orWhereNotNull('tracking_token');
        })->count();
    }

    private function getStoredViewPreference(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            return 0;
        }

        return (int) session()->get($this->getSessionKey($userId), 0);
    }

    private function persistViewPreference(): void
    {
        $userId = Auth::id();

        if (! $userId) {
            return;
        }

        session()->put($this->getSessionKey($userId), $this->viewState);
    }

    private function getSessionKey(int $userId): string
    {
        return 'widgets.total.show_issue_trackers.user_'.$userId;
    }
}
