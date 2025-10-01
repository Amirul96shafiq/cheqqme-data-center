# Reusable Dropdown Components

This document explains how to use the new reusable dropdown components created for the kanban search filter.

## Components Created

### 1. `dropdown-panel.blade.php`

A reusable dropdown panel component that handles the visual styling and transitions.

**Props:**

-   `isOpen` - Boolean variable name for controlling panel visibility
-   `width` - CSS width class (default: 'w-64')
-   `position` - CSS position classes (default: 'top-full left-0')
-   `zIndex` - CSS z-index class (default: 'z-[60]')

### 2. `dropdown.blade.php`

A complete dropdown component that includes both the trigger button and the panel.

**Props:**

-   `isOpen` - Boolean variable name for controlling dropdown state
-   `placeholder` - Text shown when no option is selected
-   `selectedText` - Text to display for selected option
-   `buttonClass` - CSS classes for the trigger button
-   `panelWidth` - Width of the dropdown panel
-   `panelPosition` - Position of the dropdown panel
-   `zIndex` - Z-index for the dropdown panel
-   `clickOutside` - Whether to close on outside click (default: true)

## Usage Examples

### Basic Dropdown Panel

```blade
<x-dropdown-panel is-open="myDropdownOpen" width="w-80">
    <!-- Your dropdown content here -->
    <div class="p-4">
        <p>Dropdown content</p>
    </div>
</x-dropdown-panel>
```

### Complete Dropdown Component

```blade
<x-dropdown
    is-open="myDropdownOpen"
    placeholder="Select an option..."
    selected-text="Selected Option"
>
    <div class="p-4">
        <div class="space-y-2">
            <button class="w-full text-left px-3 py-2 hover:bg-gray-100">
                Option 1
            </button>
            <button class="w-full text-left px-3 py-2 hover:bg-gray-100">
                Option 2
            </button>
        </div>
    </div>
</x-dropdown>
```

### Custom Styling

```blade
<x-dropdown-panel
    is-open="customDropdownOpen"
    width="w-96"
    position="top-full right-0"
    z-index="z-[70]"
>
    <div class="p-6">
        <h3 class="font-semibold mb-4">Custom Dropdown</h3>
        <!-- Custom content -->
    </div>
</x-dropdown-panel>
```

## Implementation in Kanban Search Filter

The kanban search filter now uses these components for both the "Assigned To" and "Due Date" dropdowns, making the code more maintainable and reusable.

### Benefits

1. **Reusability** - Components can be used throughout the application
2. **Consistency** - Uniform styling and behavior across all dropdowns
3. **Maintainability** - Changes to dropdown behavior only need to be made in one place
4. **Flexibility** - Props allow customization for different use cases
5. **Clean Code** - Reduced duplication and improved readability

## Alpine.js Requirements

The dropdown components require Alpine.js and expect the following data structure:

```javascript
// Example Alpine.js data
{
    myDropdownOpen: false,
    // Your other data...
}
```

The components use Alpine.js for:

-   Toggle functionality (`@click` events)
-   Conditional visibility (`x-show`)
-   Transitions (`x-transition`)
-   Outside click detection (`@click.outside`)
