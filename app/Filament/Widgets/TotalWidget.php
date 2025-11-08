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
use App\Filament\Widgets\StatWithCycleButton;
use App\Models\Task;
use App\Models\TrelloBoard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TotalWidget extends BaseWidget
{
    public bool $showIssueTrackers = false;

    protected function getColumns(): int
    {
        return 6;
    }

    public function toggleView(): void
    {
        $this->showIssueTrackers = ! $this->showIssueTrackers;
    }

    protected function getStats(): array
    {
        $totalIssueTrackers = Task::where(function ($query) {
            $query->where('status', 'issue_tracker')
                ->orWhereNotNull('tracking_token');
        })->count();

        if ($this->showIssueTrackers) {
            $issueTrackerCount = $this->getUserIssueTrackersCount();

            return [
                StatWithCycleButton::make(__('dashboard.your_issue_trackers.title'), $issueTrackerCount)
                    ->description(__('dashboard.your_issue_trackers.description', ['total' => $totalIssueTrackers]))
                    ->color('primary')
                    ->icon(ActionBoard::getNavigationIcon())
                    ->url(route('filament.admin.pages.action-board'))
                    ->extraAttributes([
                        'x-data' => '{ showButton: false }',
                        'x-on:mouseenter' => 'showButton = true',
                        'x-on:mouseleave' => 'showButton = false',
                        'class' => 'relative',
                    ]),

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

        return [
            StatWithCycleButton::make(__('dashboard.your_tasks.title'), $this->getUserTasksCount())
                ->description(__('dashboard.your_tasks.description', ['total' => Task::count()]))
                ->color('primary')
                ->icon(ActionBoard::getNavigationIcon())
                ->url(route('filament.admin.pages.action-board'))
                ->extraAttributes([
                    'x-data' => '{ showButton: false }',
                    'x-on:mouseenter' => 'showButton = true',
                    'x-on:mouseleave' => 'showButton = false',
                    'class' => 'relative',
                ]),

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
}
