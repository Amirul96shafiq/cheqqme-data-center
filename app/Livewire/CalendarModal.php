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

    public function getPreviousMonthNameProperty(): string
    {
        $previousMonth = Carbon::create($this->year, $this->month, 1)->subMonth();

        return $previousMonth->format('F');
    }

    public function getNextMonthNameProperty(): string
    {
        $nextMonth = Carbon::create($this->year, $this->month, 1)->addMonth();

        return $nextMonth->format('F');
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

    public function getTaskClasses(Task $task, bool $isAssigned): string
    {
        $priorityColors = [
            'high' => [
                'assigned' => 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50',
                'unassigned' => 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50 text-red-600 dark:text-red-400',
            ],
            'medium' => [
                'assigned' => 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:hover:bg-yellow-900/50',
                'unassigned' => 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50 text-yellow-600 dark:text-yellow-400',
            ],
            'low' => [
                'assigned' => 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50',
                'unassigned' => 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50 text-green-600 dark:text-green-400',
            ],
        ];

        $priority = $task->priority ?? 'low';
        $assignmentKey = $isAssigned ? 'assigned' : 'unassigned';

        return $priorityColors[$priority][$assignmentKey] ?? $priorityColors['low'][$assignmentKey];
    }

    public function getPriorityDotClass(Task $task): string
    {
        return match ($task->priority ?? 'low') {
            'high' => 'bg-red-500',
            'medium' => 'bg-yellow-500',
            default => 'bg-green-500',
        };
    }

    public function getMeetingClasses(MeetingLink $meeting, bool $isInvited): string
    {
        if ($isInvited) {
            return 'bg-teal-100 text-teal-700 hover:bg-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:hover:bg-teal-900/50';
        }

        return 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50 text-teal-600 dark:text-teal-400';
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
                ->orderByRaw("CASE 
                    WHEN priority = 'high' THEN 1 
                    WHEN priority = 'medium' THEN 2 
                    WHEN priority = 'low' THEN 3 
                    ELSE 4 
                END")
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
