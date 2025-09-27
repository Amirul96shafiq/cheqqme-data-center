# Online Status System Documentation

## Overview

The online status system provides real-time status indicators for users throughout the application. It supports both static display and interactive status changes with real-time updates via Laravel Presence Channels.

## Architecture

### Core Components

1. **StatusConfig** (`app/Services/OnlineStatus/StatusConfig.php`)

    - Centralized configuration for all status-related data
    - Single source of truth for colors, labels, sizes, and validation
    - Provides JavaScript configuration for frontend

2. **HasOnlineStatus Trait** (`app/View/Components/Traits/HasOnlineStatus.php`)

    - Shared functionality for status indicator components
    - Provides common methods for size classes, status classes, and data attributes
    - Ensures consistency across all status components

3. **StatusDisplay** (`app/Services/OnlineStatus/StatusDisplay.php`)

    - UI-related functionality for status display
    - Delegates to StatusConfig for consistency
    - Provides form options for Filament

4. **PresenceStatusManager** (`app/Services/OnlineStatus/PresenceStatusManager.php`)

    - Backend real-time status management
    - Handles status changes and broadcasting
    - Integrates with Laravel Presence Channels

5. **PresenceStatusManager (JS)** (`resources/js/presence-status.js`)
    - Frontend real-time status management
    - Handles WebSocket connections and UI updates
    - Provides error handling and fallback mechanisms

### Components

#### OnlineStatusIndicator

-   **Purpose**: Static status display with tooltip
-   **Usage**: `<x-online-status-indicator :user="$user" size="md" />`
-   **Features**: Tooltip support, consistent styling, real-time updates

#### InteractiveOnlineStatusIndicator

-   **Purpose**: Clickable status indicator with dropdown
-   **Usage**: `<x-interactive-online-status-indicator :user="$user" size="xl" />`
-   **Features**: Status change dropdown, real-time updates, current user only

## Configuration

### Status Types

```php
// Available statuses
StatusConfig::ONLINE          // 'online' - User is actively online
StatusConfig::AWAY            // 'away' - User is away but may respond
StatusConfig::DO_NOT_DISTURB  // 'dnd' - User does not want to be disturbed
StatusConfig::INVISIBLE       // 'invisible' - User appears offline to others
```

### Size Options

```php
// Available sizes
StatusConfig::SIZE_XS  // 'xs' - Extra small (w-3 h-3)
StatusConfig::SIZE_SM  // 'sm' - Small (w-4 h-4) - Default
StatusConfig::SIZE_MD  // 'md' - Medium (w-5 h-5)
StatusConfig::SIZE_LG  // 'lg' - Large (w-6 h-6)
StatusConfig::SIZE_XL  // 'xl' - Extra large (w-8 h-8)
```

### Status Configuration

Each status includes:

-   **label**: Display name
-   **color**: Tailwind CSS color class
-   **filament_color**: Filament color for forms
-   **icon**: Heroicon name
-   **description**: Tooltip text

## Usage Examples

### Basic Status Indicator

```blade
<!-- Static indicator with tooltip -->
<x-online-status-indicator :user="$user" size="md" />

<!-- Static indicator without tooltip -->
<x-online-status-indicator :user="$user" size="sm" :show-tooltip="false" />
```

### Interactive Status Indicator

```blade
<!-- Interactive indicator (current user only) -->
<x-interactive-online-status-indicator :user="$user" size="xl" />

<!-- Interactive indicator without tooltip -->
<x-interactive-online-status-indicator :user="$user" size="lg" :show-tooltip="false" />
```

### In Filament Resources

```php
// In UserResource.php
Tables\Columns\Layout\Stack::make([
    Tables\Columns\TextColumn::make('name'),
    Tables\Columns\Layout\View::make('filament.resources.user-resource.avatar-column'),
])
```

### Custom Implementation

```php
// In a custom component
use App\View\Components\Traits\HasOnlineStatus;

class CustomStatusComponent extends Component
{
    use HasOnlineStatus;

    public function __construct(User $user, string $size = null)
    {
        $this->user = $user;
        $this->size = $size ?? StatusConfig::getDefaultSize();
    }
}
```

## Real-time Updates

### How It Works

1. **Status Change**: User selects new status from dropdown
2. **API Call**: Frontend sends request to `/api/user/status`
3. **Backend Update**: PresenceStatusManager updates database and broadcasts event
4. **Real-time Broadcast**: `UserOnlineStatusChanged` event sent to all users
5. **Frontend Update**: PresenceStatusManager receives event and updates UI

### Broadcasting

-   **Channel**: `online-users` (Presence Channel)
-   **Event**: `user.status.changed`
-   **Data**: User information, new status, previous status

### Error Handling

-   **Network Errors**: Graceful fallback with user notification
-   **Validation Errors**: Clear error messages
-   **Connection Issues**: Automatic reconnection attempts
-   **Missing Dependencies**: Fallback to static display

## API Endpoints

### Update Status

```
POST /api/user/status
Content-Type: application/json
X-CSRF-TOKEN: {token}

{
    "status": "online|away|dnd|invisible"
}
```

### Get Current Status

```
GET /api/user/status
```

### Get Multiple User Statuses

```
POST /api/user/statuses
Content-Type: application/json

{
    "user_ids": [1, 2, 3]
}
```

## Data Attributes

All status indicators include these data attributes:

```html
<div
    data-user-id="123"
    data-current-status="online"
    data-is-current-user="true"
    data-tooltip-text="Online"
    class="online-status-indicator ..."
></div>
```

## Styling

### CSS Classes

Status indicators use consistent Tailwind CSS classes:

```css
/* Base classes */
.online-status-indicator {
    @apply rounded-full border-2 border-white dark:border-gray-900;
}

/* Status colors */
.bg-teal-500    /* Online */
/* Online */
/* Online */
/* Online */
.bg-primary-500 /* Away */
.bg-red-500     /* Do Not Disturb */
.bg-gray-400; /* Invisible */
```

### Size Classes

```css
.w-3.h-3  /* xs */
/* xs */
/* xs */
/* xs */
.w-4.h-4  /* sm - default */
.w-5.h-5  /* md */
.w-6.h-6  /* lg */
.w-8.h-8; /* xl */
```

## Best Practices

### Component Usage

1. **Use appropriate size**: Choose size based on context (avatar, list, etc.)
2. **Enable tooltips**: Always show tooltips for better UX
3. **Interactive for current user**: Use interactive component only for current user
4. **Consistent placement**: Place indicators consistently (bottom-right of avatars)

### Performance

1. **Lazy loading**: Status indicators are lightweight and can be loaded on demand
2. **Real-time updates**: Only update when status actually changes
3. **Error handling**: Always provide fallback for failed updates
4. **Caching**: Status configuration is cached and shared

### Accessibility

1. **Tooltips**: Provide status information via tooltips
2. **Keyboard navigation**: Interactive components support keyboard navigation
3. **Screen readers**: Status information is available to screen readers
4. **Color contrast**: Status colors meet accessibility standards

## Troubleshooting

### Common Issues

1. **Status not updating**: Check WebSocket connection and broadcasting configuration
2. **Wrong colors**: Verify StatusConfig configuration and Tailwind CSS classes
3. **Tooltip not showing**: Ensure tooltip component is properly included
4. **Interactive not working**: Check if user is current user and JavaScript is loaded

### Debug Mode

Enable debug logging by setting:

```javascript
window.presenceStatusManager.debug = true;
```

### Fallback Mode

If real-time updates fail, the system falls back to:

1. Static status display
2. Manual refresh required
3. Error notifications to user

## Migration Guide

### From Old System

1. **Update component calls**: Use new component names and parameters
2. **Update configuration**: Use StatusConfig instead of StatusManager
3. **Update JavaScript**: Use new PresenceStatusManager
4. **Test real-time**: Verify WebSocket connections work

### Breaking Changes

1. **Component parameters**: Size parameter now uses StatusConfig constants
2. **Configuration methods**: Use StatusConfig instead of StatusManager
3. **JavaScript API**: New error handling and validation
4. **Data attributes**: Standardized attribute names

## Future Enhancements

1. **Status history**: Track status changes over time
2. **Custom statuses**: Allow custom status types
3. **Status scheduling**: Schedule status changes
4. **Team status**: Show team/group status
5. **Status analytics**: Track status usage patterns
