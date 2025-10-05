/**
 * Pure Alpine.js User Mention System
 * Replaces the Livewire-based mention dropdown with client-side functionality
 */

// Global state for mention system
let mentionState = {
    insertingMention: false,
    atSymbolPosition: null,
    dropdownActive: false,
    mentionSelectionLock: false,
    mentionInputTimeout: null,
    dropdownComponent: null,
};

// Initialize the mention system
document.addEventListener("DOMContentLoaded", function () {
    initializeMentionSystem();
});

// Initialize mention system for all editors
function initializeMentionSystem() {
    // Wait for Alpine.js to be ready
    document.addEventListener("alpine:init", () => {
        // Create global mention dropdown component
        Alpine.data("userMentionDropdown", userMentionDropdown);

        // Initialize editors
        initializeAllEditors();
    });
}

// Legacy no-op: dropdown is provided by Blade component now
function addMentionDropdownToPage() {}

// User Mention Dropdown Alpine.js Component
function userMentionDropdown() {
    return {
        showDropdown: false,
        users: [],
        selectedIndex: 0,
        search: "",
        targetInputId: "",
        dropdownX: 0,
        dropdownY: 0,
        selectionLock: false,
        allUsers: [],
        keydownHandler: null,

        init() {
            // Load all users once on initialization
            this.loadAllUsers();

            // Listen for global events
            window.addEventListener("showMentionDropdown", (e) => {
                this.showDropdown = true;
                this.search = e.detail.searchTerm || "";
                this.targetInputId = e.detail.inputId || "";
                this.dropdownX = e.detail.x || 0;
                this.dropdownY = e.detail.y || 0;
                this.selectedIndex = 0;
                this.searchUsers();
                this.setupKeyboardNavigation();
            });

            window.addEventListener("hideMentionDropdown", () => {
                this.hideDropdown();
            });
        },

        async loadAllUsers() {
            try {
                const response = await fetch("/api/users/mention-search");
                if (response.ok) {
                    const data = await response.json();
                    this.allUsers = data.users || [];
                }
            } catch (error) {
                console.error("Failed to load users for mentions:", error);
                this.allUsers = [];
            }
        },

        searchUsers() {
            const cleanSearch = this.search.replace(/^@/, "").toLowerCase();

            // Start with @Everyone if search matches
            const users = [];
            if (cleanSearch === "" || "everyone".includes(cleanSearch)) {
                users.push({
                    id: "@Everyone",
                    username: "Everyone",
                    email: "Notify all users",
                    name: "Everyone",
                    avatar: null,
                    is_special: true,
                });
            }

            // Filter regular users
            const filteredUsers = this.allUsers.filter((user) => {
                const username = (user.username || "").toLowerCase();
                const email = (user.email || "").toLowerCase();
                const name = (user.name || "").toLowerCase();

                return (
                    username.includes(cleanSearch) ||
                    email.includes(cleanSearch) ||
                    name.includes(cleanSearch)
                );
            });

            // Limit to 10 results and add avatar URLs
            const regularUsers = filteredUsers.slice(0, 10).map((user) => ({
                ...user,
                avatar_url: user.avatar ? `/storage/${user.avatar}` : null,
                default_avatar: this.generateDefaultAvatar(user),
                is_special: false,
            }));

            this.users = [...users, ...regularUsers];
        },

        generateDefaultAvatar(user) {
            const name = user.name || user.username || "U";
            return `https://ui-avatars.com/api/?name=${encodeURIComponent(
                name
            )}&background=3b82f6&color=ffffff&size=32`;
        },

        setupKeyboardNavigation() {
            // Remove existing handler
            if (this.keydownHandler) {
                document.removeEventListener("keydown", this.keydownHandler);
            }

            this.keydownHandler = (e) => {
                if (this.selectionLock || !this.showDropdown) {
                    return;
                }

                switch (e.key) {
                    case "ArrowUp":
                        e.preventDefault();
                        this.navigateUp();
                        break;
                    case "ArrowDown":
                        e.preventDefault();
                        this.navigateDown();
                        break;
                    case "Enter":
                        e.preventDefault();
                        this.selectUser(this.selectedIndex);
                        break;
                    case "Escape":
                        e.preventDefault();
                        this.hideDropdown();
                        break;
                }
            };

            document.addEventListener("keydown", this.keydownHandler);
        },

        navigateUp() {
            this.selectedIndex =
                this.selectedIndex > 0
                    ? this.selectedIndex - 1
                    : this.users.length - 1;
            this.scrollToSelected();
        },

        navigateDown() {
            this.selectedIndex =
                this.selectedIndex < this.users.length - 1
                    ? this.selectedIndex + 1
                    : 0;
            this.scrollToSelected();
        },

        scrollToSelected() {
            this.$nextTick(() => {
                const list = this.$el.querySelector("#user-mention-list");
                const selectedItem = this.$el.querySelector(
                    `[data-index="${this.selectedIndex}"]`
                );
                if (!list || !selectedItem) return;

                const listRect = list.getBoundingClientRect();
                const itemRect = selectedItem.getBoundingClientRect();

                if (itemRect.top < listRect.top) {
                    // Scroll up
                    list.scrollTop -= listRect.top - itemRect.top + 8;
                } else if (itemRect.bottom > listRect.bottom) {
                    // Scroll down
                    list.scrollTop += itemRect.bottom - listRect.bottom + 8;
                }
            });
        },

        selectUser(index) {
            if (this.selectionLock || !this.users[index]) {
                return;
            }

            this.selectionLock = true;
            const user = this.users[index];

            // Hide dropdown immediately
            this.hideDropdown();

            // Dispatch selection event
            window.dispatchEvent(
                new CustomEvent("userSelected", {
                    detail: {
                        username: user.username,
                        userId: user.id,
                        inputId: this.targetInputId,
                    },
                })
            );

            // Reset lock after delay
            setTimeout(() => {
                this.selectionLock = false;
            }, 300);
        },

        hideDropdown() {
            this.showDropdown = false;
            this.users = [];
            this.search = "";
            this.selectedIndex = 0;

            // Remove keyboard handler
            if (this.keydownHandler) {
                document.removeEventListener("keydown", this.keydownHandler);
                this.keydownHandler = null;
            }
        },

        get dropdownStyle() {
            const isLargeScreen = window.innerWidth >= 1536;

            if (isLargeScreen) {
                return {
                    left: `${this.dropdownX}px`,
                    top: `${this.dropdownY}px`,
                    transform: "none",
                };
            } else {
                return {
                    left: "50%",
                    top: "50%",
                    transform: "translate(-50%, -50%)",
                };
            }
        },

        destroy() {
            if (this.keydownHandler) {
                document.removeEventListener("keydown", this.keydownHandler);
            }
        },
    };
}

// Initialize all editors with mention functionality
function initializeAllEditors() {
    // Use MutationObserver to watch for new editors
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    // Check for editors in the added node
                    const editors = node.querySelectorAll
                        ? node.querySelectorAll(
                              'trix-editor, .ProseMirror, [contenteditable="true"], [role="textbox"]'
                          )
                        : [];

                    editors.forEach((editor) => {
                        if (!editor.dataset.mentionsInitialized) {
                            initializeEditor(editor);
                        }
                    });

                    // If the node itself is an editor
                    if (
                        node.tagName === "TRIX-EDITOR" ||
                        node.contentEditable === "true" ||
                        node.getAttribute("role") === "textbox"
                    ) {
                        if (!node.dataset.mentionsInitialized) {
                            initializeEditor(node);
                        }
                    }
                }
            });
        });
    });

    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });

    // Initialize existing editors
    const existingEditors = document.querySelectorAll(
        'trix-editor, .ProseMirror, [contenteditable="true"], [role="textbox"]'
    );
    existingEditors.forEach((editor) => {
        if (!editor.dataset.mentionsInitialized) {
            initializeEditor(editor);
        }
    });
}

// Initialize a single editor
function initializeEditor(editor) {
    console.log("🚀 Initializing editor for mentions:", {
        tagName: editor.tagName,
        id: editor.id,
        className: editor.className,
    });

    editor.dataset.mentionsInitialized = "true";

    // Add event listeners
    editor.addEventListener("input", (e) => {
        handleMentionInput(e, editor);
    });

    editor.addEventListener("keydown", (e) => {
        handleMentionKeydown(e, editor);
    });

    // Listen for user selection events
    editor.addEventListener("userSelected", (e) => {
        insertMention(editor, e.detail.username, e.detail.userId);
    });
}

// Handle mention input
function handleMentionInput(e, editor) {
    if (mentionState.insertingMention) {
        return;
    }

    // Clear any existing timeout
    if (mentionState.mentionInputTimeout) {
        clearTimeout(mentionState.mentionInputTimeout);
    }

    // Debounce the input handling
    mentionState.mentionInputTimeout = setTimeout(() => {
        handleMentionInputDebounced(e, editor);
    }, 10);
}

// Handle mention input debounced
function handleMentionInputDebounced(e, editor) {
    const text = editor.textContent || "";
    const cursorPosition = getCursorPosition(editor);
    const beforeCursor = text.substring(0, cursorPosition);

    // Check for @ pattern
    let atMatch = beforeCursor.match(/(?:^|\s)@(\w*)$/);

    // If no match and cursor is right after @, check for @ at end
    if (!atMatch && beforeCursor.endsWith("@")) {
        atMatch = beforeCursor.match(/(?:^|\s)@$/);
        if (atMatch) {
            atMatch = ["@", ""]; // Simulate match with empty search term
        }
    }

    // If no match, hide dropdown
    if (!atMatch) {
        if (mentionState.dropdownActive) {
            mentionState.dropdownActive = false;
            mentionState.atSymbolPosition = null;
            window.dispatchEvent(new CustomEvent("hideMentionDropdown"));
        }
        return;
    }

    const searchTerm = atMatch[1] || "";
    const atIndex = beforeCursor.lastIndexOf("@");

    // Always calculate position from the @ symbol (not from current cursor)
    // For Trix, we need to account for the fact that text might have been formatted
    const backChars = beforeCursor.length - atIndex; // Distance from @ to current cursor

    // Position calculation for composer-based positioning

    if (!mentionState.dropdownActive) {
        // Show new dropdown - position at bottom-left of composer
        const composerPosition = getComposerBottomLeftPosition(editor);

        if (composerPosition) {
            mentionState.atSymbolPosition = composerPosition;
            mentionState.dropdownActive = true;

            const inputId = getInputIdFromEditor(editor);

            window.dispatchEvent(
                new CustomEvent("showMentionDropdown", {
                    detail: {
                        inputId: inputId,
                        searchTerm: searchTerm,
                        x: composerPosition.left,
                        y: composerPosition.top,
                    },
                })
            );
        }
    } else {
        // Update existing dropdown: use fixed position at bottom-left of composer
        const composerPosition = getComposerBottomLeftPosition(editor);
        if (composerPosition) {
            mentionState.atSymbolPosition = composerPosition;

            window.dispatchEvent(
                new CustomEvent("showMentionDropdown", {
                    detail: {
                        inputId: getInputIdFromEditor(editor),
                        searchTerm: searchTerm,
                        x: composerPosition.left,
                        y: composerPosition.top,
                    },
                })
            );
        }
    }
}

// Handle mention keydown
function handleMentionKeydown(e, editor) {
    // Handle Shift+Enter to prevent bold formatting issues
    if (e.key === "Enter" && e.shiftKey) {
        e.preventDefault();
        return;
    }
}

// Get cursor position in editor
function getCursorPosition(editor) {
    if (editor.tagName === "TRIX-EDITOR") {
        return editor.editor.getSelectedRange()[0];
    } else if (editor.contentEditable === "true") {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            return range.startOffset;
        }
    }
    return 0;
}

// Get caret coordinates at specific index
function getCaretCoordinatesAtIndex(editor, index) {
    try {
        if (editor.tagName === "TRIX-EDITOR") {
            const range = [index, index];
            editor.editor.setSelectedRange(range);
            const rect =
                editor.editor.getClientRectAtPosition(index) ||
                editor.getBoundingClientRect();
            return {
                left: rect.left,
                top: rect.bottom + 5,
            };
        } else if (editor.contentEditable === "true") {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0).cloneRange();
                // Insert a temporary marker span at the desired index to get stable coords
                const marker = document.createElement("span");
                marker.textContent = "\u200b";
                const child = editor.firstChild;
                if (
                    child &&
                    child.nodeType === Node.TEXT_NODE &&
                    index <= child.length
                ) {
                    range.setStart(child, index);
                    range.setEnd(child, index);
                } else {
                    range.setStart(editor, editor.childNodes.length);
                    range.setEnd(editor, editor.childNodes.length);
                }
                range.insertNode(marker);
                const rect = marker.getBoundingClientRect();
                marker.remove();
                return {
                    left: rect.left,
                    top: rect.bottom + 5,
                };
            }
        }
    } catch (error) {
        console.warn("Could not get caret coordinates:", error);
    }
    return null;
}

// Compute coordinates for the original '@' by stepping back from current caret
function getCaretCoordinatesForAt(editor, backChars) {
    try {
        if (editor.tagName === "TRIX-EDITOR") {
            const sel = editor.editor.getSelectedRange();
            const currentPos = sel ? sel[0] : 0;
            const atPos = Math.max(currentPos - backChars, 0);

            // Store original selection
            const originalRange = sel ? [...sel] : [currentPos, currentPos];

            // For Trix, try multiple approaches to get stable positioning
            let rect;
            try {
                // First try: get position at the @ character
                editor.editor.setSelectedRange([atPos, atPos]);
                rect = editor.editor.getClientRectAtPosition(atPos);

                // If that failed, try getting position at current cursor
                if (!rect) {
                    editor.editor.setSelectedRange([currentPos, currentPos]);
                    rect = editor.editor.getClientRectAtPosition(currentPos);
                }

                // If still no rect, try getting the rect at the beginning of the current line
                if (!rect) {
                    // Try to find the beginning of the current line by searching backwards for newline
                    let lineStart = atPos;
                    const text = editor.editor.getDocument().toString();
                    for (let i = atPos - 1; i >= 0; i--) {
                        if (text[i] === "\n") {
                            lineStart = i + 1;
                            break;
                        }
                    }
                    editor.editor.setSelectedRange([lineStart, lineStart]);
                    rect = editor.editor.getClientRectAtPosition(lineStart);
                }
            } catch (e) {
                console.warn("Trix position calculation failed:", e);
            }

            // Always restore original selection
            editor.editor.setSelectedRange(originalRange);

            // If we still couldn't get the rect, use editor bounds as fallback
            if (!rect) {
                const editorRect = editor.getBoundingClientRect();
                return { left: editorRect.left, top: editorRect.bottom + 5 };
            }

            // For Trix, use a consistent Y offset
            const adjustedTop = rect.bottom + 3;

            console.log(
                `Trix position: atPos=${atPos}, currentPos=${currentPos}, backChars=${backChars}, rect=`,
                rect
            );

            return { left: rect.left, top: adjustedTop };
        } else if (editor.contentEditable === "true") {
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0) return null;
            const current = selection.getRangeAt(0).cloneRange();
            const range = selection.getRangeAt(0).cloneRange();
            const node = range.startContainer;
            let offset = range.startOffset - backChars;
            if (node.nodeType !== Node.TEXT_NODE) {
                // try text node
                if (node.childNodes.length) {
                    return getCaretCoordinatesAtIndex(editor, 0);
                }
            }
            offset = Math.max(offset, 0);
            range.setStart(node, offset);
            range.setEnd(node, offset);
            const marker = document.createElement("span");
            marker.textContent = "\u200b";
            range.insertNode(marker);
            const rect = marker.getBoundingClientRect();
            marker.remove();
            // restore
            selection.removeAllRanges();
            selection.addRange(current);
            return { left: rect.left, top: rect.bottom + 5 };
        }
    } catch (e) {
        console.warn("getCaretCoordinatesForAt failed", e);
    }
    return null;
}

// Get the bottom-left position of the composer form
function getComposerBottomLeftPosition(editor) {
    try {
        // Find the composer form container
        const composerForm =
            editor.closest("form") ||
            editor.closest(".comment-composer") ||
            editor.closest("[data-composer]");

        if (!composerForm) {
            // Fallback to editor itself
            const editorRect = editor.getBoundingClientRect();
            return {
                left: editorRect.left,
                top: editorRect.bottom + 5,
            };
        }

        // Get the editor's position instead of the entire form
        const editorRect = editor.getBoundingClientRect();
        return {
            left: editorRect.left,
            top: editorRect.bottom + 5,
        };
    } catch (error) {
        console.warn("Failed to get composer position:", error);
        return null;
    }
}

// Get input ID from editor context
function getInputIdFromEditor(editor) {
    // Try to find the closest form or input context
    const form = editor.closest("form");
    if (form) {
        const input = form.querySelector(
            'input[name*="comment"], input[name*="reply"], input[name*="edit"]'
        );
        if (input) {
            return input.name;
        }
    }

    // Fallback to editor ID or generate one
    return editor.id || "editor-" + Math.random().toString(36).substr(2, 9);
}

// Insert mention into editor
function insertMention(editor, username, userId) {
    if (mentionState.insertingMention) {
        return;
    }

    mentionState.insertingMention = true;

    try {
        if (editor.tagName === "TRIX-EDITOR") {
            // For Trix editor
            const text = editor.editor.getDocument().toString();
            const beforeCursor = text.substring(0, getCursorPosition(editor));
            const afterCursor = text.substring(getCursorPosition(editor));

            // Find the @ symbol and replace with mention
            const atIndex = beforeCursor.lastIndexOf("@");
            if (atIndex !== -1) {
                const beforeAt = beforeCursor.substring(0, atIndex);
                const mention = `@${username}`;

                const newText = beforeAt + mention + afterCursor;
                editor.editor.loadHTML(newText);

                // Set cursor position after mention
                const newPosition = beforeAt.length + mention.length;
                editor.editor.setSelectedRange([newPosition, newPosition]);
            }
        } else if (editor.contentEditable === "true") {
            // For contenteditable
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const text = editor.textContent || "";
                const beforeCursor = text.substring(0, range.startOffset);
                const afterCursor = text.substring(range.endOffset);

                const atIndex = beforeCursor.lastIndexOf("@");
                if (atIndex !== -1) {
                    const beforeAt = beforeCursor.substring(0, atIndex);
                    const mention = `@${username}`;

                    const newText = beforeAt + mention + afterCursor;
                    editor.textContent = newText;

                    // Set cursor position after mention
                    const newPosition = beforeAt.length + mention.length;
                    const newRange = document.createRange();
                    newRange.setStart(editor, newPosition);
                    newRange.setEnd(editor, newPosition);
                    selection.removeAllRanges();
                    selection.addRange(newRange);
                }
            }
        }

        // Trigger input event to update form state
        editor.dispatchEvent(new Event("input", { bubbles: true }));
        editor.dispatchEvent(new Event("change", { bubbles: true }));
    } catch (error) {
        console.error("Error inserting mention:", error);
    } finally {
        // Reset inserting flag after a delay
        setTimeout(() => {
            mentionState.insertingMention = false;
        }, 100);

        // Reset mention state
        mentionState.dropdownActive = false;
        mentionState.atSymbolPosition = null;
    }
}

// Export for global access
window.userMentionSystem = {
    initialize: initializeMentionSystem,
    insertMention: insertMention,
};
