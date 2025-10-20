# Calendar Implementation

## Overview

A lightweight custom calendar implementation for displaying Tasks and Meeting Links in a modal popup.

## Features

-   **Month View**: Full calendar grid showing current month
-   **Navigation**: Previous/Next month buttons + Today button
-   **Event Display**: Shows Tasks and Meetings on their respective dates
-   **Color Coding**:
    -   **Tasks**: Priority-based colors (High=Red, Medium=Yellow, Low=Green)
    -   **Meetings**: Blue with time display
-   **Interactive**:
    -   Click events to open in Filament edit pages
    -   "+X more" button for days with many events
    -   Popover showing all events for a day
-   **Dark Mode**: Full dark mode support
-   **Localization**: English and Malay translations

## Components

### Livewire Component

**File**: `app/Livewire/CalendarModal.php`

Handles:

-   Month navigation (previous/next/today)
-   Fetching tasks and meetings from database
-   Building calendar grid with weeks/days
-   Grouping events by date

### Blade View

**File**: `resources/views/livewire/calendar-modal.blade.php`

Features:

-   Responsive grid layout
-   Event cards with truncation
-   Alpine.js popover for overflow events
-   Loading states
-   Accessibility support

### Integration

**File**: `resources/views/components/global-modals.blade.php` (lines 875-877)

The calendar is embedded as a Livewire component inside the existing global modal system.

## Data Sources

### Tasks

-   **Model**: `App\Models\Task`
-   **Date Field**: `due_date`
-   **Color**: Based on `priority` (high/medium/low)
-   **Link**: Edit page route

### Meetings

-   **Model**: `App\Models\MeetingLink`
-   **Date Field**: `meeting_start_time`
-   **Duration**: `meeting_duration` (minutes)
-   **Color**: Blue
-   **Link**: Edit page route

## Translations

### English

File: `resources/lang/en/dashboard.php`

### Malay

File: `lang/ms/dashboard.php`

Keys:

-   `dashboard.calendar.today`
-   `dashboard.calendar.previous_month`
-   `dashboard.calendar.next_month`
-   `dashboard.calendar.tasks`
-   `dashboard.calendar.meetings`
-   `dashboard.calendar.loading`
-   `dashboard.calendar.days.*` (sun-sat)

## Performance Optimizations

1. **Query Scoping**: Only fetches events within visible calendar range
2. **Limit Display**: Shows max 3 events per day in grid
3. **Lazy Loading**: Modal content only loads when opened
4. **Eager Loading**: Groups events by date for efficient rendering

## Usage

The calendar modal opens via the existing global modal system:

```javascript
window.showGlobalModal("calendar");
```

Triggered from the Dashboard widget's calendar button.

## Customization

### Change Calendar Start Day

Edit line 51 in `CalendarModal.php`:

```php
$calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY); // Monday start
```

### Add More Event Types

1. Add model query in `CalendarModal::render()`
2. Add display logic in calendar grid section
3. Add to popover template

### Adjust Event Display Limit

Edit line 75 in `calendar-modal.blade.php`:

```blade
@foreach($day['tasks']->take(5) as $task) {{-- Show 5 instead of 3 --}}
```

## Browser Support

-   Chrome/Edge: ✅
-   Firefox: ✅
-   Safari: ✅
-   Mobile: ✅ (responsive design)

## Dependencies

-   **Livewire 3**: Component framework
-   **Alpine.js**: Client-side interactivity
-   **Carbon**: Date manipulation
-   **Tailwind CSS v3**: Styling
-   **Heroicons**: Icons
