/**
 * Drag-to-Scroll Utility
 *
 * A reusable utility that enables drag-to-scroll functionality on any scrollable element.
 * Automatically hides scrollbars while maintaining scroll functionality.
 *
 * Usage:
 * 1. Add 'data-drag-scroll' attribute to any scrollable element
 * 2. Call initDragToScroll() to initialize all elements with the attribute
 *
 * Example:
 * <div class="overflow-y-auto" data-drag-scroll>
 *     <!-- Your scrollable content -->
 * </div>
 *
 * Options:
 * - data-drag-scroll-speed: Set scroll speed multiplier (default: 2)
 *
 * Example with custom speed:
 * <div class="overflow-y-auto" data-drag-scroll data-drag-scroll-speed="3">
 *     <!-- Your scrollable content -->
 * </div>
 */

/**
 * Initialize drag-to-scroll functionality on a single element
 * @param {HTMLElement} element - The scrollable element to enable drag-to-scroll
 */
export function initDragToScrollElement(element) {
    if (!element) return;

    // Add CSS class for scrollbar hiding
    element.classList.add("drag-scroll-enabled");

    // Get scroll speed from data attribute or use default
    const scrollSpeed = parseInt(element.dataset.dragScrollSpeed) || 2;

    let isDown = false;
    let startX;
    let startY;
    let scrollLeft;
    let scrollTop;

    // Mouse down - start dragging
    element.addEventListener("mousedown", (e) => {
        // Don't interfere with clickable elements
        if (e.target.closest("button, a, input, select, textarea")) {
            return;
        }

        isDown = true;
        element.classList.add("drag-scroll-active");
        startX = e.pageX - element.offsetLeft;
        startY = e.pageY - element.offsetTop;
        scrollLeft = element.scrollLeft;
        scrollTop = element.scrollTop;
    });

    // Mouse leave - stop dragging
    element.addEventListener("mouseleave", () => {
        isDown = false;
        element.classList.remove("drag-scroll-active");
    });

    // Mouse up - stop dragging
    element.addEventListener("mouseup", () => {
        isDown = false;
        element.classList.remove("drag-scroll-active");
    });

    // Mouse move - perform dragging
    element.addEventListener("mousemove", (e) => {
        if (!isDown) return;
        e.preventDefault();

        const x = e.pageX - element.offsetLeft;
        const y = e.pageY - element.offsetTop;

        const walkX = (x - startX) * scrollSpeed;
        const walkY = (y - startY) * scrollSpeed;

        element.scrollLeft = scrollLeft - walkX;
        element.scrollTop = scrollTop - walkY;
    });
}

/**
 * Initialize drag-to-scroll on all elements with data-drag-scroll attribute
 * @param {HTMLElement|Document} container - Container to search for drag-scroll elements (defaults to document)
 */
export function initDragToScroll(container = document) {
    const elements = container.querySelectorAll("[data-drag-scroll]");
    elements.forEach((element) => {
        initDragToScrollElement(element);
    });
}

/**
 * Remove drag-to-scroll functionality from an element
 * @param {HTMLElement} element - The element to remove drag-to-scroll from
 */
export function removeDragToScroll(element) {
    if (!element) return;

    element.classList.remove("drag-scroll-enabled", "drag-scroll-active");

    // Clone and replace to remove all event listeners
    const newElement = element.cloneNode(true);
    element.parentNode.replaceChild(newElement, element);

    return newElement;
}

// Auto-initialize on DOM ready if not using as module
if (typeof document !== "undefined") {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            initDragToScroll();
        });
    } else {
        initDragToScroll();
    }
}

// Make functions available globally for non-module usage
if (typeof window !== "undefined") {
    window.initDragToScroll = initDragToScroll;
    window.initDragToScrollElement = initDragToScrollElement;
    window.removeDragToScroll = removeDragToScroll;
}
