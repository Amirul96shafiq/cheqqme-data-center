/**
 * Kanban Board Alpine.js Functions
 * Consolidated functions for search, filter, and drag & drop functionality
 */

// Icon helper function - returns Heroicon SVG markup
const getHeroicon = (name, classes = "w-6 h-6") => {
    const icons = {
        users: "M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0Z",
        calendar:
            "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
        "check-circle": "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        "magnifying-glass": "M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z",
        tag: "M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z",
    };

    const path = icons[name] || icons["magnifying-glass"];
    return `<svg class="${classes}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="${path}"/></svg>`;
};

// Global Kanban Filter Function (for search and filter components)
window.globalKanbanFilter = function () {
    return {
        // Search state
        globalSearch: "",

        // Filter state
        filterOpen: false,
        dropdownPosition: "bottom", // 'top' or 'bottom'
        assignedDropdownOpen: false,
        dueDateDropdownOpen: false,
        dueDateDropdownPosition: "bottom", // 'top' or 'bottom'
        priorityDropdownOpen: false,
        priorityDropdownPosition: "bottom", // 'top' or 'bottom'
        cardTypeDropdownOpen: false,
        assignedToFilter: [],
        users: {},
        // Due date filter state
        dueDatePreset: null, // 'today' | 'week' | 'month' | 'year' | null
        dueDateFrom: null, // 'YYYY-MM-DD' | null
        dueDateTo: null, // 'YYYY-MM-DD' | null
        // Priority filter state
        priorityFilter: [], // ['high', 'medium', 'low']
        // Card type filter state
        cardTypeFilter: "all", // 'all' | 'tasks' | 'issue_trackers'

        init(initialCardType = null) {
            // Get initial data from data attributes
            const element = this.$el;
            if (element) {
                this.globalSearch = element.dataset.initialSearch || "";
                this.assignedToFilter = JSON.parse(
                    element.dataset.initialAssignedTo || "[]"
                );
                this.users = JSON.parse(element.dataset.initialUsers || "{}");
                this.dueDatePreset =
                    element.dataset.initialDueDatePreset || null;
                this.dueDateFrom = element.dataset.initialDueDateFrom || null;
                this.dueDateTo = element.dataset.initialDueDateTo || null;
                this.priorityFilter = JSON.parse(
                    element.dataset.initialPriorityFilter || "[]"
                );

                // Use parameter if provided, otherwise fall back to data attribute
                const cardTypeFromParam =
                    initialCardType !== null &&
                    initialCardType !== undefined &&
                    initialCardType !== "null" &&
                    initialCardType !== ""
                        ? initialCardType
                        : null;
                const cardTypeFromData =
                    element.dataset.initialCardType || "all";
                const finalCardType = cardTypeFromParam || cardTypeFromData;

                this.cardTypeFilter =
                    finalCardType && finalCardType !== ""
                        ? finalCardType
                        : "all";
            }

            // Initialize global search state
            window.globalSearch = this.globalSearch;

            // Initialize global assigned filter state
            window.currentAssignedTo = this.assignedToFilter;

            // Initialize global due date filter state
            window.currentDueDateFilter = {
                preset: this.dueDatePreset,
                from: this.dueDateFrom,
                to: this.dueDateTo,
            };

            // Initialize global priority filter state
            window.currentPriorityFilter = this.priorityFilter;

            // Initialize global card type filter state
            window.currentCardTypeFilter = this.cardTypeFilter;

            // Trigger initial filtering based on URL parameters
            // Delay the initial filter dispatch to allow DOM to be fully ready
            setTimeout(() => {
                this.dispatchFilterEvent();
            }, 100);

            // Add resize handler to recalculate dropdown position when window is resized
            this.resizeHandler = () => {
                if (this.filterOpen) {
                    this.calculateDropdownPosition();
                }
                if (this.dueDateDropdownOpen) {
                    this.calculateDueDateDropdownPosition();
                }
                if (this.priorityDropdownOpen) {
                    this.calculatePriorityDropdownPosition();
                }
            };
            window.addEventListener("resize", this.resizeHandler);
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

        // Generic method to calculate dropdown position based on available space
        // Parameters:
        //   buttonRef: string - ref name of the button element
        //   dropdownRef: string - ref name of the dropdown element
        //   positionProperty: string - property name to set (e.g., 'dropdownPosition')
        //   defaultHeight: number - default height if dropdown not rendered yet
        calculateSmartDropdownPosition(
            buttonRef,
            dropdownRef,
            positionProperty,
            defaultHeight = 300
        ) {
            // Use double nextTick to ensure dropdown is fully rendered
            this.$nextTick(() => {
                this.$nextTick(() => {
                    const button = this.$refs[buttonRef];
                    const dropdown = this.$refs[dropdownRef];

                    if (!button || !dropdown) return;

                    const buttonRect = button.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;
                    const viewportWidth = window.innerWidth;

                    // Get actual dropdown height (or use approximate if not rendered yet)
                    let dropdownHeight = defaultHeight;
                    if (dropdown.offsetHeight > 0) {
                        dropdownHeight = dropdown.offsetHeight;
                    }

                    const spacing = 8; // Margin/spacing
                    const minSpaceRequired = dropdownHeight + spacing;

                    // Check available space
                    const spaceBelow = viewportHeight - buttonRect.bottom;
                    const spaceAbove = buttonRect.top;

                    // On mobile (sm breakpoint is 640px), always check both
                    // On desktop, check if dropdown would be cut off
                    const isMobile = viewportWidth < 640;

                    let position = "bottom";

                    if (isMobile) {
                        // On mobile, prefer bottom but use top if not enough space
                        if (
                            spaceBelow < minSpaceRequired &&
                            spaceAbove > spaceBelow
                        ) {
                            position = "top";
                        }
                    } else {
                        // On desktop, check if dropdown would be cut off at bottom
                        if (
                            spaceBelow < minSpaceRequired &&
                            spaceAbove >= minSpaceRequired
                        ) {
                            position = "top";
                        }
                    }

                    // Set the position property dynamically
                    this[positionProperty] = position;
                });
            });
        },

        // Calculate dropdown position based on available space
        calculateDropdownPosition() {
            this.calculateSmartDropdownPosition(
                "filterButton",
                "filterDropdown",
                "dropdownPosition",
                600
            );
        },

        // Generic method to toggle dropdown and calculate position
        // Parameters:
        //   openProperty: string - property name for open state (e.g., 'filterOpen')
        //   buttonRef: string - ref name of the button element
        //   dropdownRef: string - ref name of the dropdown element
        //   positionProperty: string - property name to set (e.g., 'dropdownPosition')
        //   defaultHeight: number - default height if dropdown not rendered yet
        toggleSmartDropdown(
            openProperty,
            buttonRef,
            dropdownRef,
            positionProperty,
            defaultHeight = 300
        ) {
            this[openProperty] = !this[openProperty];
            if (this[openProperty]) {
                this.calculateSmartDropdownPosition(
                    buttonRef,
                    dropdownRef,
                    positionProperty,
                    defaultHeight
                );
            }
        },

        // Calculate dropdown position based on available space
        calculateDropdownPosition() {
            this.calculateSmartDropdownPosition(
                "filterButton",
                "filterDropdown",
                "dropdownPosition",
                600
            );
        },

        // Toggle filter and calculate position
        toggleFilter() {
            this.toggleSmartDropdown(
                "filterOpen",
                "filterButton",
                "filterDropdown",
                "dropdownPosition",
                600
            );
        },

        // Calculate priority dropdown position based on available space
        calculatePriorityDropdownPosition() {
            this.calculateSmartDropdownPosition(
                "priorityButton",
                "priorityDropdown",
                "priorityDropdownPosition",
                200
            );
        },

        // Toggle priority dropdown and calculate position
        togglePriorityDropdown() {
            this.toggleSmartDropdown(
                "priorityDropdownOpen",
                "priorityButton",
                "priorityDropdown",
                "priorityDropdownPosition",
                200
            );
        },

        // Calculate due date dropdown position based on available space
        calculateDueDateDropdownPosition() {
            this.calculateSmartDropdownPosition(
                "dueDateButton",
                "dueDateDropdown",
                "dueDateDropdownPosition",
                400
            );
        },

        // Toggle due date dropdown and calculate position
        toggleDueDateDropdown() {
            this.toggleSmartDropdown(
                "dueDateDropdownOpen",
                "dueDateButton",
                "dueDateDropdown",
                "dueDateDropdownPosition",
                400
            );
        },

        toggleCardTypeDropdown() {
            this.cardTypeDropdownOpen = !this.cardTypeDropdownOpen;
        },

        // Filter methods
        handleAssignedFilterChange() {
            // Update global assigned filter state
            window.currentAssignedTo = this.assignedToFilter;

            // Trigger instant filtering
            this.dispatchFilterEvent();
        },

        handleDueDatePresetChange() {
            // Clear range when preset chosen
            if (this.dueDatePreset) {
                this.dueDateFrom = null;
                this.dueDateTo = null;
            }
            window.currentDueDateFilter = {
                preset: this.dueDatePreset,
                from: this.dueDateFrom,
                to: this.dueDateTo,
            };
            this.dispatchFilterEvent();
        },

        toggleDueDatePreset(value) {
            // If clicking the same preset again, deselect it
            if (this.dueDatePreset === value) {
                this.dueDatePreset = null;
            } else {
                this.dueDatePreset = value;
                // Clear custom range when selecting a preset
                this.dueDateFrom = null;
                this.dueDateTo = null;
            }
            window.currentDueDateFilter = {
                preset: this.dueDatePreset,
                from: this.dueDateFrom,
                to: this.dueDateTo,
            };
            this.dispatchFilterEvent();
        },

        handleDueDateRangeChange() {
            // Clear preset when range selected
            if (this.dueDateFrom || this.dueDateTo) {
                this.dueDatePreset = null;
            }
            window.currentDueDateFilter = {
                preset: this.dueDatePreset,
                from: this.dueDateFrom,
                to: this.dueDateTo,
            };
            this.dispatchFilterEvent();
        },

        clearAssignedFilter() {
            this.assignedToFilter = [];
            window.currentAssignedTo = [];
            this.dispatchFilterEvent();
        },

        clearDueDateFilter() {
            this.dueDatePreset = null;
            this.dueDateFrom = null;
            this.dueDateTo = null;
            window.currentDueDateFilter = {
                preset: null,
                from: null,
                to: null,
            };
            this.dispatchFilterEvent();
        },

        // Priority filter methods
        handlePriorityFilterChange() {
            window.currentPriorityFilter = this.priorityFilter;
            this.dispatchFilterEvent();
        },

        clearPriorityFilter() {
            this.priorityFilter = [];
            window.currentPriorityFilter = [];
            this.dispatchFilterEvent();
        },

        removePriority(priority) {
            this.priorityFilter = this.priorityFilter.filter(
                (p) => p !== priority
            );
            window.currentPriorityFilter = this.priorityFilter;
            this.dispatchFilterEvent();
        },

        getPriorityLabel(priority) {
            const labels = {
                high: "High",
                medium: "Medium",
                low: "Low",
            };
            return labels[priority] || priority;
        },

        // Card type filter methods
        navigateToCardType(value) {
            // Update URL without page refresh
            const url = new URL(window.location);
            if (value === "all") {
                url.searchParams.delete("type");
            } else {
                let typeParam = "task";
                if (value === "issue_trackers") {
                    typeParam = "issue";
                } else if (value === "wishlist_trackers") {
                    typeParam = "wishlist";
                }
                url.searchParams.set("type", typeParam);
            }

            // Update browser history
            window.history.pushState({}, "", url);

            // Update filter state
            this.cardTypeFilter = value;
            window.currentCardTypeFilter = value;

            // Dispatch filter event to update cards
            this.dispatchFilterEvent();
        },

        getCardTypeLabel(value) {
            const translations =
                window.kanbanTranslations?.cardTypeLabels || {};
            const labels = {
                all: translations.all || "All cards",
                tasks: translations.tasks || "Tasks",
                issue_trackers: translations.issue_trackers || "Issue trackers",
            };

            return labels[value] || labels.all;
        },

        clearFilters() {
            this.clearAssignedFilter();
            this.clearDueDateFilter();
            this.clearPriorityFilter();
            this.clearCardTypeFilter();
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

        getDateRangeText() {
            if (this.dueDateFrom && this.dueDateTo) {
                return `${this.dueDateFrom} - ${this.dueDateTo}`;
            } else if (this.dueDateFrom) {
                return `From ${this.dueDateFrom}`;
            } else if (this.dueDateTo) {
                return `Until ${this.dueDateTo}`;
            }
            return "";
        },

        getDueDateDisplayText() {
            if (this.dueDatePreset === "today") {
                return "Due today";
            } else if (this.dueDatePreset === "week") {
                return "Due this week";
            } else if (this.dueDatePreset === "month") {
                return "Due this month";
            } else if (this.dueDatePreset === "year") {
                return "Due this year";
            } else if (this.dueDateFrom || this.dueDateTo) {
                return this.getDateRangeText();
            }
            return "";
        },

        // Unified filter dispatch
        dispatchFilterEvent() {
            const eventData = {
                search: this.globalSearch,
                assignedTo: this.assignedToFilter,
                dueDate: {
                    preset: this.dueDatePreset,
                    from: this.dueDateFrom,
                    to: this.dueDateTo,
                },
                priority: this.priorityFilter,
                cardType: this.cardTypeFilter,
            };

            const event = new CustomEvent("action-board-unified-filter", {
                detail: eventData,
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
                const dueDate = e?.detail?.dueDate || {};
                const priority = e?.detail?.priority || [];
                const cardType = e?.detail?.cardType || "all";
                this.filterActive =
                    search.length > 0 ||
                    assignedTo.length > 0 ||
                    dueDate.preset ||
                    dueDate.from ||
                    dueDate.to ||
                    priority.length > 0 ||
                    cardType !== "all";
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

            // Show visual feedback - DISABLED
            // this.showDragFeedback(targetColumn, "success");

            // Check for empty columns immediately after drag
            setTimeout(() => {
                this.checkAndShowEmptyColumns();
            }, 50);

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
                        // Show drag feedback - DISABLED
                        // this.showDragFeedback(columnId, "completed");

                        // Refresh Livewire component to show empty columns
                        if (window.Livewire) {
                            window.Livewire.dispatch("refresh-kanban-board");
                        }

                        // Also manually check and show empty columns
                        setTimeout(() => {
                            this.checkAndShowEmptyColumns();
                        }, 100);
                    } else {
                        throw new Error(data.message || "Server update failed");
                    }
                })
                .catch((error) => {
                    console.error("Drag and drop sync failed:", error);
                    // Show drag feedback - DISABLED
                    // this.showDragFeedback(columnId, "error");
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

        // Drag feedback function - DISABLED
        /*
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
        */

        rollbackDragOperation(originalState) {
            // Implement rollback logic if needed
            console.log("Rolling back drag operation:", originalState);
        },

        checkAndShowEmptyColumns() {
            // Check all columns and show empty column component if no cards
            const columns = document.querySelectorAll(".ff-column");
            columns.forEach((column) => {
                const columnContent = column.querySelector(
                    ".ff-column__content"
                );
                const cards = columnContent.querySelectorAll(".ff-card");
                const emptyColumn =
                    columnContent.querySelector(".ff-empty-column");

                if (cards.length === 0 && !emptyColumn) {
                    // Column is empty but no empty column component - add it
                    this.addEmptyColumnComponent(columnContent, column);
                } else if (cards.length > 0 && emptyColumn) {
                    // Column has cards but still has empty column component - remove it
                    emptyColumn.remove();
                }
            });
        },

        addEmptyColumnComponent(columnContent, column) {
            // Get the column ID and config from the column element
            const columnId = column.getAttribute("data-column-id") || "unknown";

            // Create empty column component
            const emptyColumnDiv = document.createElement("div");
            emptyColumnDiv.className = "ff-empty-column";
            emptyColumnDiv.style.cssText =
                "transition: opacity 0.2s ease-out, transform 0.2s ease-out;";

            // Get the empty column text from the global translation
            const emptyColumnText =
                window.emptyColumnText || "No tasks in this column";

            emptyColumnDiv.innerHTML = `
                <svg class="ff-empty-column__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="ff-empty-column__text">
                    ${emptyColumnText}
                </p>
            `;

            // Add to column content
            columnContent.appendChild(emptyColumnDiv);
        },
    };
};

// Global Kanban Filter Event Listener (auto-initialized)
document.addEventListener("DOMContentLoaded", function () {
    // Global event listener for unified filter events (search + assigned to filter)
    document.addEventListener("action-board-unified-filter", function (e) {
        var search = e?.detail?.search || "";
        var assignedTo = e?.detail?.assignedTo || [];
        var dueDate = e?.detail?.dueDate || {
            preset: null,
            from: null,
            to: null,
        };
        var cardType = e?.detail?.cardType || "all";

        // Set global states for Alpine.js
        window.searchActive = search.length > 0;
        var priority = e?.detail?.priority || [];
        window.filterActive =
            assignedTo.length > 0 ||
            !!dueDate.preset ||
            !!dueDate.from ||
            !!dueDate.to ||
            priority.length > 0 ||
            cardType !== "all";

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
                    let matchesDueDate = true;
                    let matchesCardType = true;

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

                    // Check due date filter
                    const dueAttr = card.getAttribute("data-due-date");
                    if (
                        dueDate &&
                        (dueDate.preset || dueDate.from || dueDate.to)
                    ) {
                        // No due date on card => does not match any due-date filter
                        if (!dueAttr || !dueAttr.trim()) {
                            matchesDueDate = false;
                        } else {
                            // Parse as YYYY-MM-DD for comparison
                            const cardDate = new Date(dueAttr + "T00:00:00");
                            if (isNaN(cardDate.getTime())) {
                                matchesDueDate = false;
                            } else {
                                const today = new Date();
                                const startOfDay = new Date(
                                    today.getFullYear(),
                                    today.getMonth(),
                                    today.getDate()
                                );

                                function endOfWeek(d) {
                                    const day = d.getDay();
                                    const diff = 6 - day; // end on Saturday (6) to align with typical business week? Use Sunday->Saturday
                                    const e = new Date(d);
                                    e.setDate(d.getDate() + diff);
                                    e.setHours(23, 59, 59, 999);

                                    return e;
                                }

                                function endOfMonth(d) {
                                    const e = new Date(
                                        d.getFullYear(),
                                        d.getMonth() + 1,
                                        0
                                    );
                                    e.setHours(23, 59, 59, 999);

                                    return e;
                                }

                                function endOfYear(d) {
                                    const e = new Date(d.getFullYear(), 11, 31);
                                    e.setHours(23, 59, 59, 999);

                                    return e;
                                }

                                let rangeStart = null;
                                let rangeEnd = null;

                                if (dueDate.preset === "today") {
                                    rangeStart = new Date(startOfDay);
                                    rangeEnd = new Date(startOfDay);
                                    rangeEnd.setHours(23, 59, 59, 999);
                                } else if (dueDate.preset === "week") {
                                    rangeStart = new Date(startOfDay);
                                    rangeEnd = endOfWeek(startOfDay);
                                } else if (dueDate.preset === "month") {
                                    rangeStart = new Date(
                                        startOfDay.getFullYear(),
                                        startOfDay.getMonth(),
                                        1
                                    );
                                    rangeEnd = endOfMonth(startOfDay);
                                } else if (dueDate.preset === "year") {
                                    rangeStart = new Date(
                                        startOfDay.getFullYear(),
                                        0,
                                        1
                                    );
                                    rangeEnd = endOfYear(startOfDay);
                                }

                                // If manual from/to specified, override preset
                                if (dueDate.from) {
                                    const fromDate = new Date(
                                        dueDate.from + "T00:00:00"
                                    );
                                    if (!isNaN(fromDate.getTime())) {
                                        rangeStart = fromDate;
                                    }
                                }
                                if (dueDate.to) {
                                    const toDate = new Date(
                                        dueDate.to + "T23:59:59"
                                    );
                                    if (!isNaN(toDate.getTime())) {
                                        rangeEnd = toDate;
                                    }
                                }

                                // Validate range
                                if (!rangeStart && !rangeEnd) {
                                    // If neither specified (shouldn't happen), consider as no filter
                                    matchesDueDate = true;
                                } else {
                                    // Compare
                                    const afterStart = rangeStart
                                        ? cardDate >= rangeStart
                                        : true;
                                    const beforeEnd = rangeEnd
                                        ? cardDate <= rangeEnd
                                        : true;
                                    matchesDueDate = afterStart && beforeEnd;
                                }
                            }
                        }
                    }

                    // Check priority filter
                    let matchesPriority = true;
                    const priority = e?.detail?.priority || [];
                    if (priority.length > 0) {
                        matchesPriority = false;

                        // Look for priority badges on the card
                        const priorityBadges =
                            card.querySelectorAll(".ff-badge");
                        priorityBadges.forEach(function (badge) {
                            const badgeText = badge.textContent
                                .toLowerCase()
                                .trim();

                            // Check if badge contains priority indicators
                            if (
                                badgeText.includes("high") &&
                                priority.includes("high")
                            ) {
                                matchesPriority = true;
                            } else if (
                                badgeText.includes("medium") &&
                                priority.includes("medium")
                            ) {
                                matchesPriority = true;
                            } else if (
                                badgeText.includes("low") &&
                                priority.includes("low")
                            ) {
                                matchesPriority = true;
                            }
                        });
                    }

                    // Check card type filter
                    const cardTypeFilter = e?.detail?.cardType || "all";
                    if (cardTypeFilter !== "all") {
                        const cardTypeValue =
                            card.getAttribute("data-card-type") || "tasks";

                        if (cardTypeFilter === "tasks") {
                            // Show tasks (cards without tracking_token)
                            matchesCardType =
                                cardTypeValue !== "issue_trackers" &&
                                cardTypeValue !== "wishlist_trackers";
                        } else if (cardTypeFilter === "issue_trackers") {
                            // Show only issue trackers (cards with tracking_token or status 'issue_tracker')
                            matchesCardType =
                                cardTypeValue === "issue_trackers";
                        } else if (cardTypeFilter === "wishlist_trackers") {
                            // Show only wishlist trackers (cards with wishlist tracking_token or status 'wishlist')
                            matchesCardType =
                                cardTypeValue === "wishlist_trackers";
                        }
                    }

                    // Card must match ALL active filters
                    const matchesAllFilters =
                        matchesSearch &&
                        matchesAssignedFilter &&
                        matchesDueDate &&
                        matchesPriority &&
                        matchesCardType;

                    card.style.display = matchesAllFilters ? "" : "none";
                    if (matchesAllFilters) visible++;
                });

                // Update column badge count
                const countBadge = col.querySelector(".ff-column__count");

                const hasActiveFilters =
                    search.length > 0 ||
                    assignedTo.length > 0 ||
                    !!dueDate.preset ||
                    !!dueDate.from ||
                    !!dueDate.to ||
                    priority.length > 0 ||
                    cardType !== "all";

                if (countBadge) {
                    const currentText = (countBadge.textContent || "").trim();
                    if (
                        !countBadge.dataset.originalCount &&
                        currentText !== ""
                    ) {
                        countBadge.dataset.originalCount = currentText;
                    }

                    if (hasActiveFilters) {
                        countBadge.textContent = visible.toString();
                    } else {
                        const original = (
                            countBadge.dataset.originalCount ||
                            currentText ||
                            visible.toString()
                        ).trim();
                        countBadge.textContent = original;
                        countBadge.dataset.originalCount = original;
                    }
                }

                // Find create task button in this column
                const createButton = col.querySelector(
                    '.ff-create-button, [data-create-button], .create-task-button, button[title*="create"], button[title*="Create"], .add-task-btn, .create-button'
                );

                // Hide/show the entire column and create button based on whether it has visible cards
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

            const hasActiveFilters =
                search.length > 0 ||
                assignedTo.length > 0 ||
                !!dueDate.preset ||
                !!dueDate.from ||
                !!dueDate.to ||
                priority.length > 0 ||
                cardType !== "all";

            if (hasActiveFilters && totalVisibleCards === 0) {
                // Update no-results component content based on active filter
                const headingEl = noResultsComponent?.querySelector("h3");
                const descriptionEl = noResultsComponent?.querySelector("p");
                const iconEl = noResultsComponent?.querySelector(".w-14.h-14");

                if (headingEl && descriptionEl && iconEl) {
                    const translations =
                        window.kanbanTranslations?.noResults || {};

                    if (!!dueDate.preset || !!dueDate.from || !!dueDate.to) {
                        // Due date filter is active
                        headingEl.textContent =
                            translations.dueDate?.title ||
                            "No tasks found for selected date range";
                        descriptionEl.textContent =
                            translations.dueDate?.description ||
                            "Try adjusting your date filter or clear the filter to see all tasks.";
                        iconEl.setAttribute(
                            "class",
                            "w-14 h-14 mx-auto text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-500/20 rounded-full p-4"
                        );
                        iconEl.innerHTML = getHeroicon("calendar");
                    } else if (assignedTo.length > 0) {
                        // Assigned to filter is active
                        headingEl.textContent =
                            translations.assignedTo?.title ||
                            "No tasks found for selected users";
                        descriptionEl.textContent =
                            translations.assignedTo?.description ||
                            "Try adjusting your user filter or clear the filter to see all tasks.";
                        iconEl.setAttribute(
                            "class",
                            "w-14 h-14 mx-auto text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-500/20 rounded-full p-4"
                        );
                        iconEl.innerHTML = getHeroicon("users");
                    } else if (priority.length > 0) {
                        // Priority filter is active
                        headingEl.textContent =
                            translations.priority?.title ||
                            "No tasks found for selected priority";
                        descriptionEl.textContent =
                            translations.priority?.description ||
                            "Try adjusting your priority filter or clear the filter to see all tasks.";
                        iconEl.setAttribute(
                            "class",
                            "w-14 h-14 mx-auto text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-500/20 rounded-full p-4"
                        );
                        iconEl.innerHTML = getHeroicon("check-circle");
                    } else if (cardType !== "all") {
                        // Card type filter is active
                        headingEl.textContent =
                            translations.cardType?.title ||
                            "No tasks found for selected card type";
                        descriptionEl.textContent =
                            translations.cardType?.description ||
                            "Try adjusting your card type filter or clear the filter to see all tasks.";
                        iconEl.setAttribute(
                            "class",
                            "w-14 h-14 mx-auto text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-500/20 rounded-full p-4"
                        );
                        iconEl.innerHTML = getHeroicon("tag");
                    } else {
                        // Search filter is active
                        headingEl.textContent =
                            translations.search?.title || "No tasks found";
                        descriptionEl.textContent =
                            translations.search?.description ||
                            "Try adjusting your search terms or clear the search to see all tasks.";
                        iconEl.setAttribute(
                            "class",
                            "w-14 h-14 mx-auto text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-500/20 rounded-full p-4"
                        );
                        iconEl.innerHTML = getHeroicon("magnifying-glass");
                    }
                }

                if (noResultsComponent) {
                    noResultsComponent.classList.remove("hidden");
                }
                if (kanbanBoardContainer) {
                    kanbanBoardContainer.classList.add("hidden");
                }
            } else {
                if (noResultsComponent) {
                    noResultsComponent.classList.add("hidden");
                }
                if (kanbanBoardContainer) {
                    kanbanBoardContainer.classList.remove("hidden");
                }
            }
        }, 100);
    });
});

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
