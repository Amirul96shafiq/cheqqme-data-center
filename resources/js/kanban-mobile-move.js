/**
 * Mobile Kanban Card Movement Handler
 * Handles long-press on mobile devices to show movement options modal
 */

(function () {
    "use strict";

    /**
     * Initialize mobile movement handlers
     */
    function initMobileMove() {
        // Only run on touch devices
        if (!("ontouchstart" in window || navigator.maxTouchPoints > 0)) {
            return;
        }

        // Wait for DOM to be ready
        if (document.readyState === "loading") {
            document.addEventListener(
                "DOMContentLoaded",
                attachLongPressHandlers
            );
        } else {
            attachLongPressHandlers();
        }

        // Re-apply when new cards are added (Livewire updates)
        const observer = new MutationObserver(attachLongPressHandlers);
        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    /**
     * Attach long-press handlers to all kanban cards
     */
    function attachLongPressHandlers() {
        const cards = document.querySelectorAll(".ff-card");

        cards.forEach(function (card) {
            // Skip if already has handler
            if (card.dataset.longPressAttached === "true") {
                return;
            }

            card.dataset.longPressAttached = "true";

            let longPressTimer = null;
            let hasMoved = false;
            let touchStartX = 0;
            let touchStartY = 0;
            let longPressActivated = false;

            // Prevent default click behavior during long press
            const preventClick = (e) => {
                e.preventDefault();
                e.stopPropagation();
            };

            // Start long press detection
            const handleTouchStart = (e) => {
                const touch = e.touches[0];
                touchStartX = touch.clientX;
                touchStartY = touch.clientY;
                hasMoved = false;
                longPressActivated = false;

                // Start timer for long press (500ms)
                longPressTimer = setTimeout(() => {
                    if (!hasMoved) {
                        longPressActivated = true;

                        // Show the movement modal
                        showMovementModal(card);

                        // Prevent any click events that might happen after touch ends
                        const preventClickHandler = (clickEvent) => {
                            clickEvent.preventDefault();
                            clickEvent.stopPropagation();
                            clickEvent.stopImmediatePropagation();
                        };

                        // Add click prevention with capture phase
                        card.addEventListener("click", preventClickHandler, {
                            capture: true,
                            once: true,
                        });

                        // Also prevent default on any following events
                        card.addEventListener("click", preventClick, {
                            once: true,
                        });
                    }
                }, 500);
            };

            // Track if user moved finger
            const handleTouchMove = (e) => {
                if (!longPressTimer) return;

                const touch = e.touches[0];
                const deltaX = Math.abs(touch.clientX - touchStartX);
                const deltaY = Math.abs(touch.clientY - touchStartY);

                // If moved more than 10px, cancel long press
                if (deltaX > 10 || deltaY > 10) {
                    hasMoved = true;
                    clearTimeout(longPressTimer);
                    longPressTimer = null;
                }
            };

            // Clean up on touch end
            const handleTouchEnd = (e) => {
                if (longPressTimer) {
                    clearTimeout(longPressTimer);
                    longPressTimer = null;
                }

                // If long press was activated, prevent the default click
                if (longPressActivated) {
                    e.preventDefault();
                    longPressActivated = false;
                }
            };

            // Attach event listeners
            card.addEventListener("touchstart", handleTouchStart, {
                passive: true,
            });
            card.addEventListener("touchmove", handleTouchMove, {
                passive: true,
            });
            card.addEventListener("touchend", handleTouchEnd, {
                passive: true,
            });
            card.addEventListener("touchcancel", handleTouchEnd, {
                passive: true,
            });
        });
    }

    /**
     * Show the movement modal for a card
     */
    function showMovementModal(card) {
        // Get the task ID from the card's data attribute (prefer data-task-id, fallback to x-sortable-item)
        let taskId = card.getAttribute("data-task-id");

        if (!taskId) {
            // Fallback to x-sortable-item attribute
            taskId = card.getAttribute("x-sortable-item");
        }

        if (!taskId) {
            console.warn("No task ID found on card", card);
            return;
        }

        const taskIdNum = parseInt(taskId, 10);

        if (isNaN(taskIdNum)) {
            console.warn("Invalid task ID:", taskId);
            return;
        }

        console.log(
            "Dispatching kanban-show-move-modal event with taskId:",
            taskIdNum
        );

        // Dispatch event to show modal (handled by Alpine.js)
        const event = new CustomEvent("kanban-show-move-modal", {
            detail: { taskId: taskIdNum },
            bubbles: true,
            cancelable: true,
        });

        // Dispatch on window for global handler (Alpine.js listens here)
        window.dispatchEvent(event);
    }

    // Initialize on load
    initMobileMove();
})();
