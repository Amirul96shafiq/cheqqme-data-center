# Drag-to-Scroll Utility

A reusable drag-to-scroll utility that enables intuitive drag-based scrolling on any scrollable element. The scrollbar is automatically hidden while maintaining full scroll functionality.

## Features

✅ **Easy to Use**: Just add a data attribute to any scrollable element  
✅ **Auto-hides Scrollbar**: Scrollbar is hidden automatically with cross-browser support  
✅ **Drag-to-Scroll**: Click and drag to scroll vertically or horizontally  
✅ **Configurable Speed**: Adjust scroll speed with data attributes  
✅ **Visual Feedback**: Cursor changes from `grab` to `grabbing` during drag  
✅ **Cross-browser Compatible**: Works on Chrome, Firefox, Safari, Edge, and IE 10+  
✅ **Multiple Scroll Methods**: Supports mouse wheel, keyboard, touch, and drag  
✅ **Smart Interaction**: Doesn't interfere with clickable elements (buttons, links, inputs)  
✅ **Performance Optimized**: Minimal overhead with efficient event handling

---

## Installation

### 1. Files Created

The utility consists of two files:

-   **JavaScript**: `resources/js/drag-to-scroll.js`
-   **CSS**: `resources/css/drag-to-scroll.css`

### 2. Vite Configuration

The files are already registered in `vite.config.js`:

```javascript
input: [
    // ... other files
    "resources/js/drag-to-scroll.js",
],
```

### 3. CSS Import

The CSS is automatically imported in `resources/css/app.css`:

```css
@import "drag-to-scroll.css";
```

---

## Basic Usage

### Simple Example

Add the `data-drag-scroll` attribute to any scrollable element:

```html
<div class="overflow-y-auto h-96" data-drag-scroll>
    <p>Your long scrollable content here...</p>
    <p>More content...</p>
    <p>Even more content...</p>
</div>
```

That's it! The element will now:

-   Hide its scrollbar automatically
-   Show a grab cursor on hover
-   Allow click-and-drag scrolling
-   Support all standard scroll methods (wheel, keyboard, touch)

---

## Advanced Usage

### Custom Scroll Speed

Adjust the scroll speed multiplier (default is `2`):

```html
<div class="overflow-y-auto h-96" data-drag-scroll data-drag-scroll-speed="3">
    <!-- Faster scrolling with speed multiplier of 3 -->
</div>

<div class="overflow-y-auto h-96" data-drag-scroll data-drag-scroll-speed="1">
    <!-- Slower scrolling with speed multiplier of 1 -->
</div>
```

### Horizontal Scrolling

Works with horizontal scrolling too:

```html
<div class="overflow-x-auto w-full" data-drag-scroll>
    <div class="flex gap-4 w-[2000px]">
        <!-- Wide horizontal content -->
    </div>
</div>
```

### Both Directions

Supports both vertical and horizontal scrolling simultaneously:

```html
<div class="overflow-auto h-96 w-96" data-drag-scroll>
    <div class="min-w-[2000px] min-h-[2000px]">
        <!-- Content that scrolls in both directions -->
    </div>
</div>
```

---

## Real-World Examples

### 1. Changelog Modal (Current Implementation)

```html
<div
    class="overflow-y-auto px-6 py-4"
    data-drag-scroll
    data-drag-scroll-speed="2"
>
    <!-- Commit list -->
    <template x-for="commit in commits">
        <div>{{ commit.message }}</div>
    </template>
</div>
```

### 2. Image Gallery

```html
<div class="overflow-x-auto flex gap-4" data-drag-scroll>
    <img src="photo1.jpg" class="h-64" />
    <img src="photo2.jpg" class="h-64" />
    <img src="photo3.jpg" class="h-64" />
</div>
```

### 3. Data Table

```html
<div
    class="overflow-auto max-h-96"
    data-drag-scroll
    data-drag-scroll-speed="1.5"
>
    <table class="w-full">
        <thead>
            <!-- headers -->
        </thead>
        <tbody>
            <!-- many rows -->
        </tbody>
    </table>
</div>
```

### 4. Chat Messages

```html
<div class="overflow-y-auto h-96 flex flex-col" data-drag-scroll>
    @foreach($messages as $message)
    <div class="message">{{ $message->text }}</div>
    @endforeach
</div>
```

### 5. Code Editor Preview

```html
<div
    class="overflow-auto h-screen bg-gray-900 text-white font-mono"
    data-drag-scroll
    data-drag-scroll-speed="2.5"
>
    <pre><code>{{ $codeContent }}</code></pre>
</div>
```

---

## JavaScript API

### Auto-initialization

The utility automatically initializes on DOM ready. All elements with `data-drag-scroll` will be processed automatically.

### Manual Initialization

You can manually initialize elements:

```javascript
// Initialize all elements with data-drag-scroll
window.initDragToScroll();

// Initialize within a specific container
const container = document.querySelector("#my-container");
window.initDragToScroll(container);

// Initialize a single element
const element = document.querySelector("#my-scrollable-div");
window.initDragToScrollElement(element);
```

### Dynamic Content

For dynamically added content (e.g., Alpine.js, Livewire):

```javascript
// Alpine.js example
this.$nextTick(() => {
    window.initDragToScroll(this.$el);
});

// Livewire example
Livewire.hook("message.processed", () => {
    window.initDragToScroll();
});

// After AJAX content load
fetch("/api/data")
    .then((response) => response.text())
    .then((html) => {
        container.innerHTML = html;
        window.initDragToScroll(container);
    });
```

### Remove Drag-to-Scroll

To remove the functionality from an element:

```javascript
const element = document.querySelector("#my-element");
window.removeDragToScroll(element);
```

---

## CSS Classes Applied

The utility automatically adds these classes:

| Class                 | When Applied      | Purpose                                             |
| --------------------- | ----------------- | --------------------------------------------------- |
| `drag-scroll-enabled` | On initialization | Hides scrollbar, adds grab cursor                   |
| `drag-scroll-active`  | During dragging   | Changes to grabbing cursor, prevents text selection |

---

## Browser Support

✅ **Chrome**: Full support  
✅ **Firefox**: Full support  
✅ **Safari**: Full support  
✅ **Edge**: Full support  
✅ **Internet Explorer 10+**: Full support

---

## Interaction with Clickable Elements

The utility intelligently avoids interfering with interactive elements:

```html
<div class="overflow-y-auto" data-drag-scroll>
    <button>I'm still clickable!</button>
    <a href="#">Links work normally</a>
    <input type="text" placeholder="Form inputs work" />
    <select>
        <option>Dropdowns work</option>
    </select>

    <!-- Only non-interactive areas enable drag-to-scroll -->
    <p>Drag here to scroll</p>
</div>
```

---

## Performance Tips

1. **Avoid excessive event listeners**: The utility uses event delegation and is optimized for performance
2. **Use appropriate scroll speeds**: Higher speeds (3-4) work well for large content areas
3. **Consider content size**: For very large datasets, consider virtual scrolling libraries
4. **Smooth scrolling**: The utility includes smooth scrolling when not actively dragging

---

## Accessibility

The drag-to-scroll utility maintains full accessibility:

-   ✅ Keyboard navigation still works (arrow keys, Page Up/Down, Home/End)
-   ✅ Screen readers can detect scrollable content
-   ✅ Mouse wheel scrolling is preserved
-   ✅ Touch scrolling on mobile devices
-   ✅ Focus management for form elements

---

## Troubleshooting

### Scrollbar still visible

Make sure the CSS is properly imported in `resources/css/app.css`:

```css
@import "drag-to-scroll.css";
```

### Drag not working

1. Check that `data-drag-scroll` attribute is present
2. Verify the element has `overflow-auto`, `overflow-y-auto`, or `overflow-x-auto`
3. Ensure content is actually scrollable (larger than container)
4. Check browser console for JavaScript errors

### Buttons not clickable

The utility automatically excludes `<button>`, `<a>`, `<input>`, `<select>`, and `<textarea>` elements. If you have custom interactive elements, they may start drag instead of click.

### Conflicts with other libraries

If using other drag libraries, you may need to add specific exclusions:

```javascript
// Exclude elements with specific classes
if (e.target.closest("button, a, input, select, textarea, .no-drag")) {
    return;
}
```

---

## Example: Complete Modal with Drag-to-Scroll

```html
<div class="modal fixed inset-0 flex items-center justify-center">
    <div
        class="modal-content bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col"
    >
        <!-- Header (fixed) -->
        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-bold">Modal Title</h2>
        </div>

        <!-- Scrollable Content -->
        <div
            class="overflow-y-auto px-6 py-4"
            data-drag-scroll
            data-drag-scroll-speed="2"
        >
            <p>Long content that needs scrolling...</p>
            <p>More content...</p>
            <button type="button">Interactive Button</button>
            <p>Even more content...</p>
        </div>

        <!-- Footer (fixed) -->
        <div class="px-6 py-4 border-t">
            <button type="button">Close</button>
        </div>
    </div>
</div>
```

---

## Migration from Inline Implementation

If you have inline drag-to-scroll code, here's how to migrate:

**Before:**

```javascript
// Inline implementation
initDragToScroll() {
    const container = this.$el.querySelector('.scroll-container');
    // ... lots of event listener code
}
```

**After:**

```html
<!-- Just add the data attribute -->
<div class="scroll-container overflow-y-auto" data-drag-scroll>
    <!-- content -->
</div>
```

```javascript
// Initialize after dynamic content loads
this.$nextTick(() => {
    window.initDragToScroll(this.$el);
});
```

---

## Support

For issues or questions:

1. Check the console for JavaScript errors
2. Verify CSS is properly compiled (`npm run build` or `npm run dev`)
3. Test with a simple example first
4. Review the browser's developer tools for applied classes

---

## License

This utility is part of the CheQQme Data Center project and follows the same license.
