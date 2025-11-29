<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\MeetingLink;
use App\Models\Task;
use App\Models\User;
use App\Services\PublicHolidayService;
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
        $this->typeFilter = ['task', 'meeting', 'event', 'holiday', 'birthday']; // Default: show all
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
        $this->typeFilter = ['task', 'meeting', 'event', 'holiday', 'birthday'];
    }

    public function getTaskStatusUrl(Task $task): ?string
    {
        if (! $task->tracking_token) {
            return null;
        }

        // Check if it's a wishlist token
        if (str_starts_with($task->tracking_token, 'CHEQQ-WSH-')) {
            return route('wishlist-tracker.status', ['token' => $task->tracking_token]);
        }

        // Default to issue tracker
        return route('issue-tracker.status', ['token' => $task->tracking_token]);
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

    public function formatDateWithTranslation(Carbon $date): string
    {
        // Get the day name translation using full day names
        $dayKey = strtolower($date->format('D')); // e.g., 'wed'
        $dayName = __("calendar.calendar.days_full.{$dayKey}");

        // Format: "Wednesday, 1/10/25" -> "Rabu, 1/10/25" (in Malay)
        return $dayName.', '.$date->format('j/n/y');
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

    public function getEventClasses(Event $event, bool $isInvited): string
    {
        if ($isInvited) {
            return 'bg-teal-100 text-teal-700 hover:bg-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:hover:bg-teal-900/50';
        }

        return 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800/50 text-teal-600 dark:text-teal-400';
    }

    public function getBirthdayClasses(object $birthday): string
    {
        if ($birthday->is_current_user) {
            return 'bg-pink-100 text-pink-700 hover:bg-pink-200 dark:bg-pink-900/30 dark:text-pink-400 dark:hover:bg-pink-900/50';
        }

        return 'bg-orange-100 text-orange-700 hover:bg-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-900/50';
    }

    /**
     * Get user's country for holiday display
     * Reads from user settings, defaults to Malaysia (MY)
     */
    public function getUserCountry(): string
    {
        $user = auth()->user();

        // Priority 1: User's explicitly set country from settings
        if ($user && $user->country) {
            $userCountry = $user->country;

            // Only support Malaysia, Indonesia, Singapore, Philippines, Japan, and Korea
            // Thailand removed as Google Calendar doesn't have holiday calendar for it
            $supportedCountries = ['MY', 'ID', 'SG', 'PH', 'JP', 'KR'];

            if (in_array($userCountry, $supportedCountries)) {
                return $userCountry;
            }

            // For unsupported countries, default to Malaysia
            return 'MY';
        }

        // Priority 2: Default to Malaysia for all users without country info
        return \App\Helpers\TimezoneHelper::getDefaultCountry(); // Returns "MY"
    }

    /**
     * Get holidays for the current month based on user's country
     */
    public function getHolidaysForMonth(): \Illuminate\Support\Collection
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // Get calendar grid start and end (include previous and next month days)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        return app(PublicHolidayService::class)
            ->getHolidaysForCountry($this->getUserCountry(), $calendarStart, $calendarEnd);
    }

    /**
     * Get birthdays for the current month
     */
    public function getBirthdaysForMonth(): \Illuminate\Support\Collection
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // Get calendar grid start and end (include previous and next month days)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Get all users with birthdays in the current month
        $users = User::query()
            ->whereNotNull('date_of_birth')
            ->get()
            ->filter(function ($user) use ($calendarStart, $calendarEnd) {
                $birthday = Carbon::parse($user->date_of_birth);

                // Create birthday for current year
                $currentYearBirthday = $birthday->copy()->year($this->year);

                // Check if birthday falls within calendar range
                return $currentYearBirthday->between($calendarStart, $calendarEnd);
            })
            ->map(function ($user) {
                $birthday = Carbon::parse($user->date_of_birth);
                $currentYearBirthday = $birthday->copy()->year($this->year);

                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'short_name' => $user->short_name,
                    'date' => $currentYearBirthday,
                    'age' => $this->year - $birthday->year,
                    'is_current_user' => $user->id === auth()->id(),
                ];
            });

        return $users;
    }

    /**
     * Get country display information for the user
     */
    public function getCountryDisplayInfo(): array
    {
        $user = auth()->user();
        $selectedCountry = $this->getUserCountry();
        $countryName = app(PublicHolidayService::class)->getCountryName($selectedCountry);

        $detectionMethod = 'default';
        $detectionMessage = __('calendar.holidays.using_default_country', ['country' => $countryName]);

        if ($user->country) {
            $detectionMethod = 'user_setting';
            $detectionMessage = __('calendar.holidays.using_user_country', ['country' => $countryName]);
        }

        return [
            'country_code' => $selectedCountry,
            'country_name' => $countryName,
            'method' => $detectionMethod,
            'message' => $detectionMessage,
        ];
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
                ->map(function ($task) {
                    // Add status URL to task object
                    $task->status_url = $this->getTaskStatusUrl($task);

                    return $task;
                })
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

        // Fetch events in the calendar range (if event filter is enabled)
        $events = in_array('event', $this->typeFilter)
            ? Event::query()
                ->with('meetingLink')
                ->whereNotNull('start_datetime')
                ->whereBetween('start_datetime', [$calendarStart, $calendarEnd])
                ->visibleToUser()
                ->orderBy('start_datetime')
                ->get()
                ->groupBy(fn ($event) => Carbon::parse($event->start_datetime)->format('Y-m-d'))
            : collect();

        // Get holidays for the current month (if holiday filter is enabled)
        $holidays = in_array('holiday', $this->typeFilter)
            ? $this->getHolidaysForMonth()
                ->groupBy(fn ($holiday) => $holiday->date->format('Y-m-d'))
            : collect();

        // Get birthdays for the current month (if birthday filter is enabled)
        $birthdays = in_array('birthday', $this->typeFilter)
            ? $this->getBirthdaysForMonth()
                ->groupBy(fn ($birthday) => $birthday->date->format('Y-m-d'))
            : collect();

        // Get country display info
        $countryInfo = $this->getCountryDisplayInfo();

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
                    'events' => $events->get($dateKey, collect()),
                    'holidays' => $holidays->get($dateKey, collect()),
                    'birthdays' => $birthdays->get($dateKey, collect()),
                ];

                $currentDate->addDay();
            }
            $weeks[] = $week;
        }

        return view('livewire.calendar-modal', [
            'weeks' => $weeks,
            'monthName' => Carbon::create($this->year, $this->month, 1)->format('F Y'),
            'countryInfo' => $countryInfo,
            'priorityTranslations' => [
                'high' => __('calendar.priority.high'),
                'medium' => __('calendar.priority.medium'),
                'low' => __('calendar.priority.low'),
            ],
        ]);
    }
}
