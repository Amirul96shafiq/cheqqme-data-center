<?php

namespace App\Livewire;

use App\Models\MeetingLink;
use App\Models\Task;
use Carbon\Carbon;
use Livewire\Component;

class CalendarModal extends Component
{
    public int $year;

    public int $month;

    public function mount(): void
    {
        $now = now();
        $this->year = $now->year;
        $this->month = $now->month;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
    }

    public function today(): void
    {
        $now = now();
        $this->year = $now->year;
        $this->month = $now->month;
    }

    public function render()
    {
        // Get start and end dates for the calendar view
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // Get calendar grid start (include previous month days)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Fetch tasks with due dates in the calendar range
        $tasks = Task::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$calendarStart, $calendarEnd])
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn ($task) => Carbon::parse($task->due_date)->format('Y-m-d'));

        // Fetch meetings in the calendar range
        $meetings = MeetingLink::query()
            ->whereNotNull('meeting_start_time')
            ->whereBetween('meeting_start_time', [$calendarStart, $calendarEnd])
            ->orderBy('meeting_start_time')
            ->get()
            ->groupBy(fn ($meeting) => Carbon::parse($meeting->meeting_start_time)->format('Y-m-d'));

        // Build calendar grid
        $weeks = [];
        $currentDate = $calendarStart->copy();

        while ($currentDate <= $calendarEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $currentDate->format('Y-m-d');

                $week[] = [
                    'date' => $currentDate->copy(),
                    'is_current_month' => $currentDate->month === $this->month,
                    'is_today' => $currentDate->isToday(),
                    'tasks' => $tasks->get($dateKey, collect()),
                    'meetings' => $meetings->get($dateKey, collect()),
                ];

                $currentDate->addDay();
            }
            $weeks[] = $week;
        }

        return view('livewire.calendar-modal', [
            'weeks' => $weeks,
            'monthName' => Carbon::create($this->year, $this->month, 1)->format('F Y'),
        ]);
    }
}
