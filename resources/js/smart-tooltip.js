// Smart Tooltip Positioning
document.addEventListener("alpine:init", () => {
    Alpine.data("tooltipSmartPositioning", () => ({
        positionTooltip(container) {
            const tooltip = container.querySelector(".tooltip");
            if (!tooltip) return;

            // Get original position and align from data attributes
            const originalPosition = tooltip.dataset.tooltipPosition || "top";
            const originalAlign = tooltip.dataset.tooltipAlign || "center";

            // Get container dimensions
            const containerRect = container.getBoundingClientRect();

            // Get viewport dimensions
            const viewport = {
                width: window.innerWidth,
                height: window.innerHeight,
                margin: 8,
            };

            let newPosition = originalPosition;
            let newAlign = originalAlign;

            // Temporarily show tooltip to get dimensions
            const wasVisible = tooltip.style.visibility !== "hidden";
            if (!wasVisible) {
                tooltip.style.visibility = "visible";
                tooltip.style.opacity = "0";
                tooltip.style.pointerEvents = "none";
            }

            const tooltipWidth = tooltip.offsetWidth;
            const tooltipHeight = tooltip.offsetHeight;

            // Hide tooltip again if it wasn't visible
            if (!wasVisible) {
                tooltip.style.visibility = "hidden";
                tooltip.style.opacity = "0";
            }

            // Smart positioning logic
            if (originalPosition === "top") {
                if (containerRect.top - tooltipHeight < viewport.margin) {
                    newPosition = "bottom";
                }
            } else if (originalPosition === "bottom") {
                if (
                    containerRect.bottom + tooltipHeight >
                    viewport.height - viewport.margin
                ) {
                    newPosition = "top";
                }
            } else if (originalPosition === "left") {
                if (containerRect.left - tooltipWidth < viewport.margin) {
                    newPosition = "right";
                }
            } else if (originalPosition === "right") {
                if (
                    containerRect.right + tooltipWidth >
                    viewport.width - viewport.margin
                ) {
                    newPosition = "left";
                }
            }

            // Adjust horizontal alignment if needed
            if (newPosition === "top" || newPosition === "bottom") {
                const centerX = containerRect.left + containerRect.width / 2;
                const tooltipLeft = centerX - tooltipWidth / 2;
                const tooltipRight = centerX + tooltipWidth / 2;

                if (tooltipLeft < viewport.margin) {
                    newAlign = "start";
                } else if (tooltipRight > viewport.width - viewport.margin) {
                    newAlign = "end";
                }
            }

            // Apply smart positioning classes
            tooltip.className = tooltip.className.replace(
                /tooltip-(top|bottom|left|right)/,
                ""
            );
            tooltip.className = tooltip.className.replace(
                /smart-(top|bottom|left|right|start|end|center)/g,
                ""
            );

            tooltip.classList.add(`smart-${newPosition}`);
            if (newPosition === "top" || newPosition === "bottom") {
                tooltip.classList.add(`smart-${newAlign}`);
            }
        },

        resetTooltip(container) {
            const tooltip = container.querySelector(".tooltip");
            if (!tooltip) return;

            // Reset to original position classes
            const originalPosition = tooltip.dataset.tooltipPosition || "top";
            const originalAlign = tooltip.dataset.tooltipAlign || "center";

            tooltip.className = tooltip.className.replace(
                /smart-(top|bottom|left|right|start|end|center)/g,
                ""
            );
            tooltip.classList.add(`tooltip-${originalPosition}`);
        },
    }));
});
