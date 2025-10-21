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

    public array $typeFilter = [];

    public function mount(): void
    {
        $now = now();
        $this->year = $now->year;
        $this->month = $now->month;
        $this->typeFilter = ['task', 'meeting']; // Default: show both
    }

    public function toggleTypeFilter(string $type): void
    {
        if (in_array($type, $this->typeFilter)) {
            $this->typeFilter = array_values(array_diff($this->typeFilter, [$type]));
        } else {
            $this->typeFilter[] = $type;
        }
    }

    public function clearTypeFilter(): void
    {
        $this->typeFilter = ['task', 'meeting'];
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();

        // Check if the new year is within allowed range
        $currentYear = now()->year;
        $minYear = $currentYear - 5;

        if ($date->year >= $minYear) {
            $this->year = $date->year;
            $this->month = $date->month;
        }
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();

        // Check if the new year is within allowed range
        $currentYear = now()->year;
        $maxYear = $currentYear + 5;

        if ($date->year <= $maxYear) {
            $this->year = $date->year;
            $this->month = $date->month;
        }
    }

    public function today(): void
    {
        $now = now();
        $this->year = $now->year;
        $this->month = $now->month;
    }

    public function getIsViewingTodayProperty(): bool
    {
        $now = now();

        return $this->year === $now->year && $this->month === $now->month;
    }

    public function goToMonth(int $month, int $year): void
    {
        // Validate year range: -5 to +5 years from current year
        $currentYear = now()->year;
        $minYear = $currentYear - 5;
        $maxYear = $currentYear + 5;

        // Clamp year to allowed range
        $validatedYear = max($minYear, min($maxYear, $year));

        // Validate month range
        $validatedMonth = max(1, min(12, $month));

        $this->month = $validatedMonth;
        $this->year = $validatedYear;
    }

    public function render()
    {
        // Get start and end dates for the calendar view
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // Get calendar grid start (include previous month days)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Fetch tasks with due dates in the calendar range (if task filter is enabled)
        $tasks = in_array('task', $this->typeFilter)
            ? Task::query()
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [$calendarStart, $calendarEnd])
                ->orderBy('due_date')
                ->get()
                ->groupBy(fn ($task) => Carbon::parse($task->due_date)->format('Y-m-d'))
            : collect();

        // Fetch meetings in the calendar range (if meeting filter is enabled)
        $meetings = in_array('meeting', $this->typeFilter)
            ? MeetingLink::query()
                ->whereNotNull('meeting_start_time')
                ->whereBetween('meeting_start_time', [$calendarStart, $calendarEnd])
                ->orderBy('meeting_start_time')
                ->get()
                ->groupBy(fn ($meeting) => Carbon::parse($meeting->meeting_start_time)->format('Y-m-d'))
            : collect();

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
