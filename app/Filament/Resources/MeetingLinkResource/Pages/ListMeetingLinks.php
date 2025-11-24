<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Resources\MeetingLinkResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMeetingLinks extends ListRecords
{
    protected static string $resource = MeetingLinkResource::class;

    public function mount(): void
    {
        parent::mount();

        if (session('success')) {
            Notification::make()
                ->title(__('meetinglink.notifications.google_calendar_connected'))
                ->body(__('meetinglink.notifications.google_calendar_connected_body'))
                ->success()
                ->send();

            session()->forget('success');
        }
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('meetinglink.tabs.all')),
            'upcoming' => Tab::make(__('meetinglink.tabs.upcoming'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('meeting_start_time', '>', now()))
                ->badge(fn () => \App\Models\MeetingLink::where('meeting_start_time', '>', now())->count()),
            'today' => Tab::make(__('meetinglink.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('meeting_start_time', now()->toDateString()))
                ->badge(fn () => \App\Models\MeetingLink::whereDate('meeting_start_time', now()->toDateString())->count()),
            'this_week' => Tab::make(__('meetinglink.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('meeting_start_time', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]))
                ->badge(fn () => \App\Models\MeetingLink::whereBetween('meeting_start_time', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->count()),
            'this_month' => Tab::make(__('meetinglink.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('meeting_start_time', now()->month)
                    ->whereYear('meeting_start_time', now()->year))
                ->badge(fn () => \App\Models\MeetingLink::whereMonth('meeting_start_time', now()->month)
                    ->whereYear('meeting_start_time', now()->year)
                    ->count()),
            'past' => Tab::make(__('meetinglink.tabs.past'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('meeting_start_time', '<', now()))
                ->badge(fn () => \App\Models\MeetingLink::where('meeting_start_time', '<', now())->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('meetinglink.actions.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
