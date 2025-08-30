document.addEventListener("DOMContentLoaded", () => {
    // -----------------------------
    // Global Search Keyboard Shortcut + Custom Placeholder
    // -----------------------------
    const searchInput = document.querySelector(".fi-global-search input");

    // Set placeholder
    if (searchInput) {
        searchInput.placeholder = "CTRL + / to search";
    }

    // Keyboard shortcut: /
    document.addEventListener("keydown", function (e) {
        if (e.ctrlKey && e.key.toLowerCase() === "/") {
            e.preventDefault();
            const input = document.querySelector(".fi-global-search input");
            if (input) {
                input.focus();
            }
        }
    });
});
// -----------------------------
// Enable horizontal drag-scroll on Flowforge board
// -----------------------------
(function () {
    let isBound = false;
    function bind() {
        if (isBound) return;
        isBound = true;
        document.addEventListener("mousedown", function (e) {
            // Target the kanban board columns container directly
            const kanbanBoard = e.target.closest(
                ".ff-board__columns.kanban-board"
            );
            if (!kanbanBoard) return;

            // Don't interfere with card dragging or other interactive elements
            if (e.target.closest(".ff-card")) return;
            if (e.target.closest("button")) return;
            if (e.target.closest("input")) return;
            if (e.target.closest("select")) return;
            if (e.target.closest("textarea")) return;
            if (e.target.closest("[contenteditable]")) return;

            e.preventDefault(); // prevent text selection
            let isDown = true;
            const startX = e.pageX;
            const startScrollLeft = kanbanBoard.scrollLeft;
            kanbanBoard.classList.add("ff-drag-scrolling");

            const onMove = (ev) => {
                if (!isDown) return;
                kanbanBoard.scrollLeft = startScrollLeft - (ev.pageX - startX);
                ev.preventDefault();
            };

            const end = () => {
                isDown = false;
                kanbanBoard.classList.remove("ff-drag-scrolling");
                window.removeEventListener("mousemove", onMove);
                window.removeEventListener("mouseup", end);
                window.removeEventListener("mouseleave", end);
            };

            window.addEventListener("mousemove", onMove);
            window.addEventListener("mouseup", end);
            window.addEventListener("mouseleave", end);
        });
    }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bind);
    } else {
        bind();
    }
    document.addEventListener("livewire:navigated", function () {
        isBound = false;
        bind();
    });
})();

// -----------------------------
// Show a tiny "Copied" helper bubble after sharing
// -----------------------------
(function () {
    window.showCopiedBubble = function (anchor) {
        try {
            const rect = anchor.getBoundingClientRect();
            const bubble = document.createElement("div");
            // Get the language from document's lang attribute or default to 'en'
            const lang = document.documentElement.lang || "en";
            const messages = {
                en: "Copied!",
                ms: "Kopi!",
            };
            bubble.textContent = messages[lang] || messages["en"];
            bubble.style.position = "fixed";
            bubble.style.zIndex = "9999";
            bubble.style.top = rect.top - 40 + "px";
            bubble.style.left = "0px";
            bubble.style.background = "#00AE9F";
            bubble.style.color = "#fff";
            bubble.style.padding = "4px 8px";
            bubble.style.borderRadius = "6px";
            bubble.style.fontSize = "12px";
            bubble.style.opacity = "0";
            bubble.style.transition =
                "opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
            bubble.style.whiteSpace = "nowrap"; // Prevent text wrapping
            document.body.appendChild(bubble);

            // Center the bubble after it's rendered and we can measure its actual width
            requestAnimationFrame(() => {
                const bubbleRect = bubble.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                bubble.style.left = centerX - bubbleRect.width / 2 + "px";
            });

            // Smooth fade in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    bubble.style.opacity = "1";
                });
            });

            // Smooth fade out and remove
            setTimeout(() => {
                bubble.style.opacity = "0";
                setTimeout(() => bubble.remove(), 400);
            }, 1200);
        } catch (e) {
            // Silently fail if DOM not ready
        }
    };
})();

// -----------------------------
// Reusable Clipboard Utility
// -----------------------------
window.copyToClipboard = function (
    text,
    successMessage = "Copied to clipboard!"
) {
    if (!text) {
        console.error("No text provided to copy");
        return Promise.reject(new Error("No text provided"));
    }

    return navigator.clipboard
        .writeText(text)
        .then(function () {
            // Success - notification will be handled by PHP side
            console.log(successMessage, text);
            return text;
        })
        .catch(function (err) {
            console.error("Failed to copy to clipboard: ", err);

            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "-9999px";
            document.body.appendChild(textArea);
            textArea.select();

            try {
                document.execCommand("copy");
                document.body.removeChild(textArea);

                // Success - notification will be handled by PHP side
                console.log(successMessage + " (fallback):", text);
                return text;
            } catch (fallbackErr) {
                document.body.removeChild(textArea);
                console.error("Fallback copy also failed:", fallbackErr);
                throw fallbackErr;
            }
        });
};

// -----------------------------
// Livewire Event Handlers for Clipboard Operations
// -----------------------------
document.addEventListener("livewire:init", function () {
    // Generic copy event handler
    Livewire.on("copy-to-clipboard", function (data) {
        const { text, message } = data;
        copyToClipboard(text, message);
    });

    // Legacy event handlers for backward compatibility
    Livewire.on("copy-task-url", function (data) {
        copyToClipboard(data.url, "Task URL copied to clipboard!");
    });

    Livewire.on("copy-api-key", function (data) {
        copyToClipboard(data.apiKey, "API key copied to clipboard!");
    });
});

// -----------------------------
// Apply cover image backgrounds to table rows
// -----------------------------
(function () {
    let periodicCheckInterval;

    function applyCoverImages() {
        // Find all table cells with cover image data
        const coverImageCells = document.querySelectorAll(
            "[data-cover-image-url]"
        );

        let appliedCount = 0;
        coverImageCells.forEach((cell) => {
            const coverImageUrl = cell.getAttribute("data-cover-image-url");
            if (coverImageUrl) {
                // Find the parent table row
                const tableRow = cell.closest("tr");
                if (tableRow) {
                    // Always reapply the background for cover image rows
                    const isDarkMode =
                        document.documentElement.classList.contains("dark") ||
                        document.body.classList.contains("dark");
                    const gradient = isDarkMode
                        ? `linear-gradient(to right, rgba(24,24,27,0.3) 0%, rgba(24,24,27,0.7) 20%, rgba(24,24,27,0.9) 70%, rgba(24,24,27,1) 100%)`
                        : `linear-gradient(to right, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.7) 20%, rgba(255,255,255,0.9) 70%, rgba(255,255,255,1) 100%)`;

                    // Force reapplication by clearing and re-setting
                    tableRow.style.backgroundImage = `${gradient}, url('${coverImageUrl}')`;
                    // tableRow.style.backgroundSize = "contain";
                    // tableRow.style.backgroundPosition = "center";
                    // tableRow.style.backgroundRepeat = "no-repeat";
                    tableRow.classList.add("cover-image-row");
                    appliedCount++;
                }
            }
        });
    }

    // Start periodic checking for cover images (fallback mechanism)
    function startPeriodicCheck() {
        if (periodicCheckInterval) {
            clearInterval(periodicCheckInterval);
        }

        periodicCheckInterval = setInterval(() => {
            const coverImageCells = document.querySelectorAll(
                "[data-cover-image-url]"
            );
            const rowsWithoutBackground = Array.from(coverImageCells).filter(
                (cell) => {
                    const tableRow = cell.closest("tr");
                    return tableRow && !tableRow.style.backgroundImage;
                }
            );

            if (rowsWithoutBackground.length > 0) {
                console.log(
                    `Found ${rowsWithoutBackground.length} rows without cover images, applying...`
                );
                applyCoverImages(); // Apply immediately without delay
            }
        }, 200); // Check every 200ms for faster response
    }

    // Apply on page load
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            applyCoverImages();
            startPeriodicCheck();
        });
    } else {
        applyCoverImages();
        startPeriodicCheck();
    }

    // Apply on all relevant Livewire events
    document.addEventListener("livewire:updated", (event) => {
        console.log("Livewire updated event triggered", event);
        applyCoverImages();
    });

    document.addEventListener("livewire:navigated", (event) => {
        console.log("Livewire navigated event triggered", event);
        applyCoverImages();
    });

    // Listen for theme changes (Filament light-switch plugin)
    document.addEventListener("theme-changed", (event) => {
        console.log("Theme changed event triggered", event.detail);
        // Reapply cover images immediately when theme changes
        applyCoverImages();
    });

    // Additional Livewire events that might trigger DOM updates
    document.addEventListener("livewire:loading", () => {
        console.log("Livewire loading event triggered");
    });

    // Listen for input changes that might trigger search
    document.addEventListener("input", (event) => {
        if (
            event.target.matches("input[wire\\:model*='search']") ||
            event.target.matches("input[wire\\:model*='tableSearch']") ||
            event.target.closest("[wire\\:model*='search']") ||
            event.target.closest("[wire\\:model*='tableSearch']")
        ) {
            console.log(
                "Search input detected, applying cover images immediately"
            );
            // Apply immediately without delay
            applyCoverImages();
        }
    });

    // Use MutationObserver as a fallback to catch any DOM changes
    const observer = new MutationObserver((mutations) => {
        let shouldApply = false;
        mutations.forEach((mutation) => {
            if (
                mutation.type === "childList" &&
                mutation.addedNodes.length > 0
            ) {
                // Check if any added nodes contain table rows with cover images
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (
                            node.matches &&
                            node.matches("[data-cover-image-url]")
                        ) {
                            shouldApply = true;
                        } else if (
                            node.querySelector &&
                            node.querySelector("[data-cover-image-url]")
                        ) {
                            shouldApply = true;
                        }
                        // Also check for table-related elements that might contain our target elements
                        if (
                            node.matches &&
                            (node.matches("table") ||
                                node.matches("tbody") ||
                                node.matches("tr"))
                        ) {
                            shouldApply = true;
                        }
                    }
                });
            }
            // Also monitor attribute changes that might affect our elements
            if (
                mutation.type === "attributes" &&
                mutation.target.matches &&
                (mutation.target.matches("[data-cover-image-url]") ||
                    mutation.target.closest("[data-cover-image-url]"))
            ) {
                shouldApply = true;
            }
        });

        if (shouldApply) {
            console.log("MutationObserver detected relevant DOM changes");
            applyCoverImages();
        }
    });

    // Start observing the document body for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ["data-cover-image-url", "wire:model"],
    });

    // Also observe the html element for theme class changes
    const htmlObserver = new MutationObserver((mutations) => {
        let themeChanged = false;
        mutations.forEach((mutation) => {
            if (
                mutation.type === "attributes" &&
                mutation.attributeName === "class"
            ) {
                themeChanged = true;
            }
        });

        if (themeChanged) {
            console.log("HTML class changed, checking for theme change");
            // Check if dark mode status changed and reapply if needed
            applyCoverImages();
        }
    });

    htmlObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });

    // Cleanup on page unload
    window.addEventListener("beforeunload", () => {
        if (periodicCheckInterval) {
            clearInterval(periodicCheckInterval);
        }
        observer.disconnect();
        htmlObserver.disconnect();
    });
})();
