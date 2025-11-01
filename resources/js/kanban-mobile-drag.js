/**
 * Mobile Kanban Board Handler
 * Disables drag and drop on mobile, enables full scrolling
 */

(function () {
    "use strict";

    /**
     * Initialize mobile handlers
     */
    function initMobile() {
        // Only run on touch devices
        if (!("ontouchstart" in window || navigator.maxTouchPoints > 0)) {
            return;
        }

        // Wait for DOM to be ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", disableDragOnMobile);
        } else {
            disableDragOnMobile();
        }

        // Re-apply when new cards are added (Livewire updates)
        const observer = new MutationObserver(disableDragOnMobile);
        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    /**
     * Disable Alpine Sortable drag on all cards for mobile
     */
    function disableDragOnMobile() {
        const cards = document.querySelectorAll(".ff-card");

        cards.forEach(function (card) {
            // Remove Alpine Sortable attributes to disable drag
            if (card.hasAttribute("x-sortable-handle")) {
                card.removeAttribute("x-sortable-handle");
            }
            if (card.hasAttribute("x-sortable-item")) {
                card.removeAttribute("x-sortable-item");
            }
        });

        // Also disable on columns
        const columns = document.querySelectorAll(".ff-column__content");
        columns.forEach(function (column) {
            // Keep x-sortable on column for proper layout, but ensure cards can scroll
            column.style.touchAction = "pan-x pan-y"; // Allow all scrolling
        });
    }

    // Initialize on load
    initMobile();
})();
