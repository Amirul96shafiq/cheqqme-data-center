# Google Meet Date/Time Configuration

## Overview

The Google Meet integration now supports configurable meeting date/time and duration when creating meeting links. This gives you full control over when meetings are scheduled instead of defaulting to 1 hour from now.

## How It Works

### 1. Default Behavior (Previous)

-   **Start Time**: Automatically set to 1 hour from now
-   **End Time**: Automatically set to 2 hours from now (1-hour meeting)
-   **No configuration options**

### 2. New Configurable Behavior

-   **Meeting Start**: DateTimePicker field to select exact date and time
-   **Duration**: Select dropdown with preset durations (15 min to 4 hours)
-   **Auto-calculated End Time**: Start time + duration

## Form Fields

When creating a Meeting Link with Google Meet platform:

1. **Meeting Title** (required)

    - The title/summary of the meeting in Google Calendar

2. **Platform** (required)

    - Select "Google Meet" to enable datetime fields

3. **Meeting Start** (required when Google Meet is selected)

    - DateTimePicker: "M d, Y - h:i A" format
    - Default: 1 hour from now (rounded to hour)
    - Minimum: Current time
    - Example: "Oct 14, 2025 - 02:00 PM"

4. **Duration** (required when Google Meet is selected)

    - Dropdown options:
        - 15 minutes
        - 30 minutes
        - 45 minutes
        - 1 hour (default)
        - 1.5 hours
        - 2 hours
        - 3 hours
        - 4 hours

5. **Meeting URL** (auto-generated)
    - Disabled field that shows the generated Google Meet link
    - Has "Copy" and "Regenerate" actions

## Google Calendar Event Details

When you generate a Google Meet link, it creates a Google Calendar event with:

-   **Summary**: Meeting title
-   **Start**: Selected meeting start date/time
-   **End**: Calculated (start + duration)
-   **Time Zone**: Your app's configured timezone
-   **Conference**: Google Meet link automatically added
-   **Attendees**: Empty by default (can be added in Google Calendar)
-   **Reminders**: Uses Google Calendar default settings

## Database Fields

### New Fields Added to `meeting_links` Table:

```php
$table->dateTime('meeting_start_time')->nullable();
$table->integer('meeting_duration')->default(60)->comment('Duration in minutes');
```

### Model Casts:

```php
protected $casts = [
    // ... other casts
    'meeting_start_time' => 'datetime',
];
```

## Usage Example

### Creating a Meeting for Tomorrow at 2 PM (1 hour duration):

1. Go to **Admin Panel → Meeting Links → Create**
2. Enter **Meeting Title**: "Client Review Meeting"
3. Select **Platform**: "Google Meet"
4. Set **Meeting Start**: "Oct 15, 2025 - 02:00 PM"
5. Select **Duration**: "1 hour"
6. Click **"Generate Google Meet URL"**
7. The system will:
    - Create a Google Calendar event from 2:00 PM to 3:00 PM
    - Generate a Google Meet link
    - Display the link in the form
8. Click **Save** to store the meeting link

## Code Flow

### 1. Form Input Collection

```php
$title = $get('title') ?: 'Meeting';
$startTime = $get('meeting_start_time');
$duration = (int) $get('meeting_duration') ?: 60;
```

### 2. Time Calculation

```php
// Calculate end time
$endTime = $startTime
    ? \Carbon\Carbon::parse($startTime)->addMinutes($duration)->toIso8601String()
    : null;

$startTime = $startTime
    ? \Carbon\Carbon::parse($startTime)->toIso8601String()
    : null;
```

### 3. Google Meet Link Generation

```php
$result = $googleMeetService->generateMeetLink($title, $startTime, $endTime);
```

### 4. Google Calendar Event Creation

```php
// In GoogleMeetService
$event = new Event([
    'summary' => $title,
    'start' => new EventDateTime([
        'dateTime' => $startDateTime, // ISO8601 format
        'timeZone' => config('app.timezone'),
    ]),
    'end' => new EventDateTime([
        'dateTime' => $endDateTime, // ISO8601 format
        'timeZone' => config('app.timezone'),
    ]),
    'conferenceData' => new ConferenceData([
        // Google Meet configuration
    ]),
]);
```

## Time Format Details

### ISO8601 Format

The Google Calendar API requires ISO8601 datetime format:

-   Example: `2025-10-15T14:00:00+08:00`
-   Generated using: `Carbon::parse($startTime)->toIso8601String()`

### Display Format

The DateTimePicker shows user-friendly format:

-   Format: `M d, Y - h:i A`
-   Example: "Oct 15, 2025 - 02:00 PM"

## Regenerate Feature

The "Regenerate" action also uses the configured datetime:

1. Click **Regenerate** button (visible when meeting URL exists)
2. Confirm the action
3. The system:
    - Deletes the old Google Calendar event (if possible)
    - Creates a new event with the **same start time and duration**
    - Generates a new Google Meet link
    - Updates the form fields

## Timezone Handling

-   All times are stored in the database as UTC
-   Display uses the app's configured timezone (`config('app.timezone')`)
-   Google Calendar events use the app's timezone
-   Users see times in their local timezone in the DateTimePicker

## Validation Rules

-   **meeting_start_time**:

    -   Required when platform is "Google Meet"
    -   Must be present or future datetime
    -   Minimum: Current time

-   **meeting_duration**:
    -   Required when platform is "Google Meet"
    -   Must be one of the preset values (15, 30, 45, 60, 90, 120, 180, 240)
    -   Default: 60 minutes

## Benefits

1. **Flexibility**: Schedule meetings for any future date/time
2. **Precision**: Exact datetime selection instead of "1 hour from now"
3. **Duration Control**: Choose appropriate meeting length
4. **Calendar Integration**: Creates properly scheduled Google Calendar events
5. **User Experience**: Clear, intuitive datetime picker interface

## Troubleshooting

### Meeting appears at wrong time in Google Calendar

-   Check your app's timezone setting in `config/app.php`
-   Verify the DateTimePicker is showing the correct timezone
-   Ensure your Google Calendar timezone matches your app timezone

### Cannot select past dates

-   This is by design - use `minDate(now())` validation
-   Only future meetings can be scheduled

### Duration not calculating correctly

-   Verify the duration value is in minutes (not hours)
-   Check that the end time calculation adds minutes: `->addMinutes($duration)`

## Related Files

-   **Form**: `app/Filament/Resources/MeetingLinkResource.php`
-   **Service**: `app/Services/GoogleMeetService.php`
-   **Model**: `app/Models/MeetingLink.php`
-   **Migration**: `database/migrations/2025_10_14_013520_add_meeting_datetime_fields_to_meeting_links_table.php`
