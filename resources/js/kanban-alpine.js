/**
 * Kanban Board Alpine.js Functions
 * Consolidated functions for search, filter, and drag & drop functionality
 */

// Global Kanban Filter Function (for search and filter components)
window.globalKanbanFilter = function () {
    return {
        // Search state
        globalSearch: "",

        // Filter state
        filterOpen: false,
        assignedDropdownOpen: false,
        assignedToFilter: [],
        users: {},

        init() {
            // Initialize global search state
            window.globalSearch = this.globalSearch;

            // Initialize global assigned filter state
            window.currentAssignedTo = this.assignedToFilter;
        },

        // Search methods
        handleSearchInput() {
            // Update global search state
            window.globalSearch = this.globalSearch;

            // Trigger instant client-side filtering
            this.dispatchFilterEvent();
        },

        clearSearch() {
            this.globalSearch = "";
            window.globalSearch = "";
            this.dispatchFilterEvent();
        },

        // Filter methods
        handleAssignedFilterChange() {
            // Update global assigned filter state
            window.currentAssignedTo = this.assignedToFilter;

            // Trigger instant filtering
            this.dispatchFilterEvent();
        },

        clearAssignedFilter() {
            this.assignedToFilter = [];
            window.currentAssignedTo = [];
            this.dispatchFilterEvent();
        },

        removeAssignedUser(userId) {
            // Update Alpine.js state
            this.assignedToFilter = this.assignedToFilter.filter(
                (id) => id != userId
            );
            // Update global state
            window.currentAssignedTo = this.assignedToFilter;
            // Trigger instant filtering
            this.dispatchFilterEvent();
        },

        getUserById(userId) {
            return this.users[userId] || "Unknown User";
        },

        // Unified filter dispatch
        dispatchFilterEvent() {
            const event = new CustomEvent("action-board-unified-filter", {
                detail: {
                    search: this.globalSearch,
                    assignedTo: this.assignedToFilter,
                },
            });
            window.dispatchEvent(event);
            document.dispatchEvent(event);
        },
    };
};

// Column Drag and Drop Function
window.columnDragDrop = function (columnId) {
    return {
        filterActive: false,
        dragOperations: new Map(),

        init() {
            // Listen for filter events to disable drag and drop
            window.addEventListener("action-board-unified-filter", (e) => {
                const search = e?.detail?.search || "";
                const assignedTo = e?.detail?.assignedTo || [];
                this.filterActive = search.length > 0 || assignedTo.length > 0;
            });
        },

        handleDragEnd(event) {
            if (this.filterActive) {
                return; // Don't handle drag if filtering is active
            }

            const targetColumn = event.to.getAttribute("data-column-id");
            const cardIds = event.to.sortable.toArray();

            // Store original state for potential rollback
            const originalState = {
                columnId: columnId,
                cardIds: event.from.sortable.toArray(),
                timestamp: Date.now(),
            };

            // Optimistic UI update - cards are already moved visually by Alpine.js
            this.dragOperations.set(targetColumn, {
                originalState,
                newState: { columnId: targetColumn, cardIds },
                status: "pending",
            });

            // Show visual feedback
            this.showDragFeedback(targetColumn, "success");

            // Background server sync (non-blocking)
            this.syncWithServer(targetColumn, cardIds, originalState);
        },

        syncWithServer(columnId, cardIds, originalState) {
            // Use fetch API for non-blocking server sync with session auth
            fetch("/api/kanban/update-order", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
                body: JSON.stringify({
                    columnId: columnId,
                    cardIds: cardIds,
                }),
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(
                            `HTTP ${response.status}: ${response.statusText}`
                        );
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        this.dragOperations.set(columnId, {
                            ...this.dragOperations.get(columnId),
                            status: "completed",
                        });
                        this.showDragFeedback(columnId, "completed");
                    } else {
                        throw new Error(data.message || "Server update failed");
                    }
                })
                .catch((error) => {
                    console.error("Drag and drop sync failed:", error);
                    this.showDragFeedback(columnId, "error");
                    // Optional: Rollback UI changes
                    // this.rollbackDragOperation(originalState);
                })
                .finally(() => {
                    // Clean up after 3 seconds
                    setTimeout(() => {
                        this.dragOperations.delete(columnId);
                    }, 3000);
                });
        },

        showDragFeedback(columnId, type) {
            const column = document.querySelector(
                `[data-column-id="${columnId}"]`
            );
            if (!column) return;

            // Remove existing feedback classes
            column.classList.remove(
                "drag-success",
                "drag-error",
                "drag-pending"
            );

            // Add appropriate feedback class
            switch (type) {
                case "success":
                case "completed":
                    column.classList.add("drag-success");
                    break;
                case "error":
                    column.classList.add("drag-error");
                    break;
                case "pending":
                    column.classList.add("drag-pending");
                    break;
            }

            // Remove feedback after animation
            setTimeout(() => {
                column.classList.remove(
                    "drag-success",
                    "drag-error",
                    "drag-pending"
                );
            }, 1500);
        },

        rollbackDragOperation(originalState) {
            // Implement rollback logic if needed
            console.log("Rolling back drag operation:", originalState);
        },
    };
};

// Global Kanban Filter Event Listener (for main board page)
window.initKanbanFiltering = function () {
    // Global event listener for unified filter events (search + assigned to filter)
    document.addEventListener("action-board-unified-filter", function (e) {
        var search = e?.detail?.search || "";
        var assignedTo = e?.detail?.assignedTo || [];

        // Set global states for Alpine.js
        window.searchActive = search.length > 0;
        window.filterActive = assignedTo.length > 0;

        // Wait a bit for DOM to update, then filter cards
        setTimeout(function () {
            const columns = document.querySelectorAll(".ff-column");
            let totalVisibleCards = 0;

            columns.forEach(function (col) {
                const cards = col.querySelectorAll(".ff-card");
                let visible = 0;

                cards.forEach(function (card) {
                    let matchesSearch = true;
                    let matchesAssignedFilter = true;

                    // Check search filter
                    if (search.length > 0) {
                        const titleEl = card.querySelector(".ff-card__title");
                        const title = (
                            titleEl?.textContent || ""
                        ).toLowerCase();
                        matchesSearch = title.includes(search.toLowerCase());
                    }

                    // Check assigned to filter
                    if (assignedTo.length > 0) {
                        matchesAssignedFilter = false;
                        const assignedElements = card.querySelectorAll(
                            "[data-assigned-user-ids]"
                        );

                        if (assignedElements.length > 0) {
                            assignedElements.forEach(function (el) {
                                const userIds = el.getAttribute(
                                    "data-assigned-user-ids"
                                );

                                if (userIds && userIds.trim() !== "") {
                                    const cardUserIds = userIds.split(",");
                                    const hasMatch = cardUserIds.some(function (
                                        cardUserId
                                    ) {
                                        const cardUserIdStr = cardUserId.trim();
                                        const cardUserIdNum =
                                            parseInt(cardUserIdStr);
                                        return (
                                            assignedTo.includes(
                                                cardUserIdStr
                                            ) ||
                                            assignedTo.includes(
                                                cardUserIdNum.toString()
                                            )
                                        );
                                    });
                                    if (hasMatch) {
                                        matchesAssignedFilter = true;
                                    }
                                }
                            });
                        }
                    }

                    // Card must match BOTH search AND assigned filter (if either is active)
                    const matchesAllFilters =
                        matchesSearch && matchesAssignedFilter;

                    card.style.display = matchesAllFilters ? "" : "none";
                    if (matchesAllFilters) visible++;
                });

                // Find create task button in this column
                const createButton = col.querySelector(
                    '.ff-create-button, [data-create-button], .create-task-button, button[title*="create"], button[title*="Create"], .add-task-btn, .create-button'
                );

                // Hide/show the entire column and create button based on whether it has visible cards
                const hasActiveFilters =
                    search.length > 0 || assignedTo.length > 0;
                if (hasActiveFilters && visible === 0) {
                    col.style.display = "none";
                    // Hide create button when column is hidden
                    if (createButton) {
                        createButton.style.display = "none";
                    }
                } else {
                    col.style.display = "";

                    // Hide/show create task button based on filter state
                    if (createButton) {
                        if (hasActiveFilters) {
                            createButton.style.display = "none";
                        } else {
                            createButton.style.display = "";
                        }
                    }
                }

                totalVisibleCards += visible;
            });

            // Show/hide no-results component
            const noResultsComponent = document.getElementById(
                "no-results-component"
            );
            const kanbanBoardContainer = document.getElementById(
                "kanban-board-container"
            );

            const hasActiveFilters = search.length > 0 || assignedTo.length > 0;
            if (hasActiveFilters && totalVisibleCards === 0) {
                if (noResultsComponent)
                    noResultsComponent.classList.remove("hidden");
                if (kanbanBoardContainer)
                    kanbanBoardContainer.classList.add("hidden");
            } else {
                if (noResultsComponent)
                    noResultsComponent.classList.add("hidden");
                if (kanbanBoardContainer)
                    kanbanBoardContainer.classList.remove("hidden");
            }
        }, 100);
    });
};

// Helper function to show no results component
window.showNoResults = function () {
    const noResultsComponent = document.getElementById("no-results-component");
    const kanbanBoardContainer = document.getElementById(
        "kanban-board-container"
    );

    if (noResultsComponent) {
        noResultsComponent.classList.remove("hidden");
    }

    if (kanbanBoardContainer) {
        kanbanBoardContainer.classList.add("hidden");
    }
};
