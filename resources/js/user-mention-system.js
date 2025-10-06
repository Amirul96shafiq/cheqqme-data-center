/**
 * User Mention System - Clean and Consolidated Version
 * Handles all user mention functionality across different editor types
 */

class UserMentionSystem {
    constructor() {
        this.state = {
            insertingMention: false,
            atSymbolPosition: null,
            dropdownActive: false,
            mentionSelectionLock: false,
            mentionInputTimeout: null,
        };

        this.init();
    }

    init() {
        // Wait for DOM and Alpine.js to be ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () =>
                this.initializeSystem()
            );
        } else {
            this.initializeSystem();
        }
    }

    initializeSystem() {
        // Wait for Alpine.js
        if (typeof Alpine === "undefined") {
            document.addEventListener("alpine:init", () => this.setupSystem());
        } else {
            this.setupSystem();
        }
    }

    setupSystem() {
        this.setupGlobalEventListeners();
        this.initializeAllEditors();
        this.setupUserSelectedListener();
    }

    setupGlobalEventListeners() {
        // Listen for Livewire updates to re-initialize editors
        document.addEventListener("livewire:update", () => {
            setTimeout(() => this.initializeAllEditors(), 500);
        });

        document.addEventListener("livewire:navigated", () => {
            setTimeout(() => this.initializeAllEditors(), 500);
        });

        // Listen for refresh events
        document.addEventListener("refreshTaskComments", () => {
            setTimeout(() => this.initializeAllEditors(), 600);
        });

        // Watch for new forms being added to DOM
        this.setupMutationObserver();
    }

    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (
                    mutation.type === "childList" &&
                    mutation.addedNodes.length > 0
                ) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            this.checkForNewEditors(node);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    checkForNewEditors(node) {
        const hasEditForm =
            node.querySelector?.('.edit-form[data-edit-form="true"]') ||
            node.querySelector?.(".fi-form:not([data-composer] .fi-form)");
        const hasReplyForm = node.querySelector?.(
            '.reply-form[data-reply-form="true"]'
        );

        if (
            hasEditForm ||
            hasReplyForm ||
            node.classList?.contains("edit-form") ||
            node.classList?.contains("reply-form") ||
            (node.classList?.contains("fi-form") &&
                !node.closest("[data-composer]"))
        ) {
            // Initialize immediately for reply forms, with delay for edit forms
            if (hasReplyForm || node.classList?.contains("reply-form")) {
                this.initializeAllEditors();
                requestAnimationFrame(() => this.initializeAllEditors());
            } else {
                setTimeout(() => this.initializeAllEditors(), 1500);
            }
        }
    }

    initializeAllEditors() {
        this.initializeTrixEditors();
        this.initializeProseMirrorEditors();
        this.initializeContentEditableElements();
    }

    initializeTrixEditors() {
        const trixEditors = document.querySelectorAll(
            "trix-editor:not([data-mention-initialized])"
        );
        trixEditors.forEach((editor) => {
            editor.setAttribute("data-mention-initialized", "true");
            this.setupTrixMentionHandling(editor);
        });
    }

    initializeProseMirrorEditors() {
        const proseMirrorEditors = document.querySelectorAll(
            ".ProseMirror:not([data-mention-initialized])"
        );
        proseMirrorEditors.forEach((editor) => {
            editor.setAttribute("data-mention-initialized", "true");
            this.setupProseMirrorMentionHandling(editor);
        });
    }

    initializeContentEditableElements() {
        const contentEditableElements = document.querySelectorAll(
            '[contenteditable="true"]:not([data-mention-initialized])'
        );
        contentEditableElements.forEach((element) => {
            if (
                !element.closest("trix-editor") &&
                !element.classList.contains("ProseMirror")
            ) {
                element.setAttribute("data-mention-initialized", "true");
                this.setupContentEditableMentionHandling(element);
            }
        });
    }

    setupTrixMentionHandling(trixEditor) {
        trixEditor.addEventListener("trix-change", (e) => {
            if (!this.state.insertingMention) {
                this.handleTrixMentionDetection(trixEditor);
            }
        });

        trixEditor.addEventListener("trix-selection-change", (e) => {
            if (this.state.dropdownActive && !this.state.insertingMention) {
                this.handleTrixMentionDetection(trixEditor);
            }
        });

        trixEditor.addEventListener("keydown", (e) => {
            this.handleMentionKeydown(e, trixEditor);
        });
    }

    setupProseMirrorMentionHandling(proseMirrorEditor) {
        proseMirrorEditor.addEventListener("input", (e) => {
            if (!this.state.insertingMention) {
                this.handleProseMirrorMentionDetection(proseMirrorEditor);
            }
        });

        proseMirrorEditor.addEventListener("keydown", (e) => {
            this.handleMentionKeydown(e, proseMirrorEditor);
        });
    }

    setupContentEditableMentionHandling(element) {
        element.addEventListener("input", (e) => {
            if (!this.state.insertingMention) {
                this.handleContentEditableMentionDetection(element);
            }
        });

        element.addEventListener("keydown", (e) => {
            this.handleMentionKeydown(e, element);
        });
    }

    handleTrixMentionDetection(trixEditor) {
        if (!trixEditor.editor) return;

        const document = trixEditor.editor.getDocument();
        const range = trixEditor.editor.getSelectedRange();
        const text = document.toString();
        const cursorPosition = range[0];
        const beforeCursor = text.substring(0, cursorPosition);

        this.processMentionDetection(trixEditor, beforeCursor, cursorPosition);
    }

    handleProseMirrorMentionDetection(proseMirrorEditor) {
        const text = proseMirrorEditor.textContent || "";
        const selection = window.getSelection();
        const cursorPosition = selection.anchorOffset;
        const beforeCursor = text.substring(0, cursorPosition);

        this.processMentionDetection(
            proseMirrorEditor,
            beforeCursor,
            cursorPosition
        );
    }

    handleContentEditableMentionDetection(element) {
        const text = element.textContent || "";
        const selection = window.getSelection();
        const cursorPosition = selection.anchorOffset;
        const beforeCursor = text.substring(0, cursorPosition);

        this.processMentionDetection(element, beforeCursor, cursorPosition);
    }

    processMentionDetection(editor, beforeCursor, cursorPosition) {
        // Check for @ mention pattern
        let atMatch = beforeCursor.match(/(?:^|\s)@([^\s\n]*)$/);

        if (!atMatch && beforeCursor.endsWith("@")) {
            atMatch = ["@", ""]; // New @ symbol with empty search term
        }

        if (!atMatch) {
            if (this.state.dropdownActive) {
                this.hideMentionDropdown();
            }
            return;
        }

        const searchTerm = atMatch[1] || "";
        const atIndex = beforeCursor.lastIndexOf("@");

        // Detect multiple trailing @ symbols
        const trailingAtsMatch = beforeCursor.match(/@+$/);
        const hasExtraAt = trailingAtsMatch
            ? trailingAtsMatch[0].length > 1
            : false;

        if (!this.state.dropdownActive) {
            this.showMentionDropdown(editor, searchTerm, atIndex, hasExtraAt);
        } else {
            this.updateMentionDropdown(editor, searchTerm, hasExtraAt);
        }
    }

    showMentionDropdown(editor, searchTerm, atIndex, hasExtraAt = false) {
        try {
            const position = this.getDropdownPosition(editor, atIndex);
            if (position) {
                this.state.atSymbolPosition = position;
                this.state.dropdownActive = true;

                const inputId = this.getInputIdFromEditor(editor);

                window.dispatchEvent(
                    new CustomEvent("showMentionDropdown", {
                        detail: {
                            inputId: inputId,
                            searchTerm: searchTerm,
                            x: position.left,
                            y: position.top,
                            hasExtraAt: hasExtraAt,
                        },
                    })
                );
            }
        } catch (error) {
            console.warn("Failed to show mention dropdown:", error);
        }
    }

    updateMentionDropdown(editor, searchTerm, hasExtraAt = false) {
        const inputId = this.getInputIdFromEditor(editor);
        const position =
            this.state.atSymbolPosition || this.getDropdownPosition(editor);

        window.dispatchEvent(
            new CustomEvent("showMentionDropdown", {
                detail: {
                    inputId: inputId,
                    searchTerm: searchTerm,
                    x: position.left,
                    y: position.top,
                    hasExtraAt: hasExtraAt,
                },
            })
        );
    }

    getDropdownPosition(editor, atIndex = null) {
        // Try to get composer-based position first
        const composerPosition = this.getComposerBottomLeftPosition(editor);
        if (composerPosition) {
            return composerPosition;
        }

        // Fallback to editor position
        const editorRect = editor.getBoundingClientRect();
        return {
            left: editorRect.left,
            top: editorRect.bottom + 5,
        };
    }

    getComposerBottomLeftPosition(editor) {
        try {
            const composerForm =
                editor.closest("form") ||
                editor.closest(".comment-composer") ||
                editor.closest("[data-composer]");

            if (!composerForm) {
                return null;
            }

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

    getInputIdFromEditor(editor) {
        if (editor.closest("[data-composer]")) {
            return "composerData.newComment";
        } else if (editor.closest('.edit-form[data-edit-form="true"]')) {
            return "editData.editingText";
        } else if (
            editor.closest('.edit-reply-form[data-edit-reply-form="true"]')
        ) {
            return "editReplyData.editingReplyText";
        } else if (editor.closest('.reply-form[data-reply-form="true"]')) {
            return "replyData.replyText";
        } else {
            // Fallback: try to determine by form class
            const editForm = editor.closest(".edit-form");
            const editReplyForm = editor.closest(".edit-reply-form");
            const replyForm = editor.closest(".reply-form");

            if (editForm) {
                return "editData.editingText";
            } else if (editReplyForm) {
                return "editReplyData.editingReplyText";
            } else if (replyForm) {
                return "replyData.replyText";
            } else {
                return "composerData.newComment"; // Default fallback
            }
        }
    }

    handleMentionKeydown(e, editor) {
        // Handle Shift+Enter to prevent bold formatting issues
        if (e.key === "Enter" && e.shiftKey) {
            setTimeout(() => {
                if (editor.classList.contains("ProseMirror")) {
                    // For ProseMirror, rely on PHP sanitization
                } else if (editor.tagName === "TRIX-EDITOR" && editor.editor) {
                    try {
                        if (
                            typeof editor.editor.deactivateAttribute ===
                            "function"
                        ) {
                            editor.editor.deactivateAttribute("bold");
                        }
                    } catch (error) {
                        console.error(
                            "Error deactivating bold after Shift+Enter:",
                            error
                        );
                    }
                }
            }, 10);
            return;
        }

        // Check if dropdown is visible before handling navigation keys
        const dropdown = document.querySelector(".user-mention-dropdown");
        const isDropdownVisible = dropdown !== null;

        if (!isDropdownVisible) {
            return;
        }

        if (e.key === "Escape") {
            e.preventDefault();
            this.hideMentionDropdown();
        }
    }

    hideMentionDropdown() {
        this.state.dropdownActive = false;
        this.state.atSymbolPosition = null;
        window.dispatchEvent(new CustomEvent("hideMentionDropdown"));
    }

    setupUserSelectedListener() {
        // Remove any existing listeners first to avoid duplicates
        if (window.userSelectedListenerSetup) {
            return;
        }

        window.userSelectedListenerSetup = true;

        // Use both old and new Livewire event listener syntax for compatibility
        if (
            typeof Livewire !== "undefined" &&
            typeof Livewire.on === "function"
        ) {
            Livewire.on("userSelected", (event) => {
                const data = event.detail ? event.detail : event;
                this.handleUserSelected(data);
            });
        }

        // Listen for custom events on document (fallback)
        document.addEventListener("livewire:userSelected", (event) => {
            this.handleUserSelected(event.detail);
        });

        // Listen for the new instant custom event
        const userSelectedHandler = (event) => {
            this.handleUserSelected(event.detail);
        };

        window.addEventListener("userSelected", userSelectedHandler);
        window.userSelectedHandler = userSelectedHandler;
    }

    handleUserSelected(data) {
        // Prevent multiple mentions from being processed
        if (this.state.mentionSelectionLock) {
            return;
        }

        // Validate data
        if (!data || !data.username) {
            return;
        }

        // Lock mention selection
        this.state.mentionSelectionLock = true;

        // Reset dropdown state when user selects
        this.state.dropdownActive = false;
        this.state.atSymbolPosition = null;

        // Find the currently active editor
        const activeEditor = this.findActiveEditor(data.inputId);

        if (activeEditor && data.username) {
            // Check if this username is already properly inserted to prevent duplicates
            const currentText = this.getEditorText(activeEditor);
            const fullMention = `@${data.username} `;

            if (currentText.includes(fullMention)) {
                this.releaseSelectionLock();
                return;
            }

            // Insert the mention
            this.insertMention(activeEditor, data.username);

            // Notify server of selected user id
            if (typeof data.userId !== "undefined") {
                window.dispatchEvent(
                    new CustomEvent("userSelected", {
                        detail: {
                            userId: data.userId,
                            username: data.username,
                            inputId: data.inputId,
                        },
                    })
                );
            }
        }

        this.releaseSelectionLock();
    }

    findActiveEditor(inputId) {
        let activeEditor = null;

        // Use inputId to determine which editor to prioritize
        if (inputId === "editData.editingText") {
            const editForm = document.querySelector(
                '.edit-form[data-edit-form="true"]'
            );
            if (editForm) {
                activeEditor =
                    editForm.querySelector("trix-editor") ||
                    editForm.querySelector(".ProseMirror") ||
                    editForm.querySelector('[contenteditable="true"]');
            }
        } else if (inputId === "editReplyData.editingReplyText") {
            const editReplyForm = document.querySelector(
                '.edit-reply-form[data-edit-reply-form="true"]'
            );
            if (editReplyForm) {
                activeEditor =
                    editReplyForm.querySelector("trix-editor") ||
                    editReplyForm.querySelector(".ProseMirror") ||
                    editReplyForm.querySelector('[contenteditable="true"]');
            }
        } else if (inputId === "replyData.replyText") {
            const replyForm = document.querySelector(
                '.reply-form[data-reply-form="true"]'
            );
            if (replyForm) {
                activeEditor =
                    replyForm.querySelector("trix-editor") ||
                    replyForm.querySelector(".ProseMirror") ||
                    replyForm.querySelector('[contenteditable="true"]');
            }
        }

        // Fallback logic
        if (!activeEditor) {
            activeEditor =
                document.querySelector("trix-editor:focus") ||
                document.querySelector(".ProseMirror:focus") ||
                document.querySelector('[contenteditable="true"]:focus') ||
                document.querySelector("[data-composer] trix-editor") ||
                document.querySelector("[data-composer] .ProseMirror") ||
                document.querySelector(
                    '[data-composer] [contenteditable="true"]'
                );
        }

        return activeEditor;
    }

    getEditorText(editor) {
        if (editor.tagName === "TRIX-EDITOR") {
            return editor.editor?.getDocument()?.toString() || "";
        } else if (editor.classList.contains("ProseMirror")) {
            return editor.textContent || "";
        } else if (editor.contentEditable === "true") {
            return editor.textContent || "";
        }
        return "";
    }

    insertMention(editor, username) {
        if (!username || username === "undefined") {
            console.log("❌ Invalid username for insertion:", username);
            return;
        }

        // Prevent duplicate insertions
        if (this.state.insertingMention) {
            console.log("🚫 Insertion blocked - already inserting mention");
            return;
        }

        this.state.insertingMention = true;

        try {
            if (editor.tagName === "TRIX-EDITOR") {
                this.insertTrixMention(editor, username);
            } else if (editor.classList.contains("ProseMirror")) {
                this.insertProseMirrorMention(editor, username);
            } else {
                this.insertContentEditableMention(editor, username);
            }
        } finally {
            setTimeout(() => {
                this.state.insertingMention = false;
            }, 500);
        }
    }

    insertTrixMention(trixEditor, username) {
        if (!trixEditor.editor) return;

        try {
            const document = trixEditor.editor.getDocument();
            const range = trixEditor.editor.getSelectedRange();
            const text = document.toString();
            const cursorPosition = range[0];
            const beforeCursor = text.substring(0, cursorPosition);

            const atIndex = beforeCursor.lastIndexOf("@");
            if (atIndex !== -1) {
                const textFromAt = text.substring(atIndex);
                const spaceIndex = textFromAt.indexOf(" ");
                const newlineIndex = textFromAt.indexOf("\n");

                let endIndex = textFromAt.length;
                if (spaceIndex !== -1)
                    endIndex = Math.min(endIndex, spaceIndex);
                if (newlineIndex !== -1)
                    endIndex = Math.min(endIndex, newlineIndex);

                const startPosition = atIndex;
                const endPosition = atIndex + endIndex;
                trixEditor.editor.setSelectedRange([
                    startPosition,
                    endPosition,
                ]);

                const mentionText = `@${username} `;
                trixEditor.editor.insertString(mentionText);

                trixEditor.focus();
            }
        } catch (error) {
            console.error("Error inserting mention in Trix editor:", error);
        }
    }

    insertProseMirrorMention(proseMirrorEditor, username) {
        try {
            const text = proseMirrorEditor.textContent || "";
            const atIndex = text.lastIndexOf("@");

            if (atIndex !== -1) {
                const textFromAt = text.substring(atIndex);
                const spaceIndex = textFromAt.indexOf(" ");
                const newlineIndex = textFromAt.indexOf("\n");

                let endIndex = textFromAt.length;
                if (spaceIndex !== -1)
                    endIndex = Math.min(endIndex, spaceIndex);
                if (newlineIndex !== -1)
                    endIndex = Math.min(endIndex, newlineIndex);

                const beforeAt = text.substring(0, atIndex);
                const afterPartial = text.substring(atIndex + endIndex);
                const newText = beforeAt + `@${username} ` + afterPartial;

                // Replace the content safely
                const range = document.createRange();
                range.selectNodeContents(proseMirrorEditor);
                range.deleteContents();
                const temp = document.createElement("div");
                temp.innerHTML = newText;
                while (temp.firstChild) {
                    proseMirrorEditor.appendChild(temp.firstChild);
                }

                proseMirrorEditor.focus();
                setTimeout(() => {
                    proseMirrorEditor.dispatchEvent(
                        new Event("input", { bubbles: true })
                    );
                }, 10);
            }
        } catch (error) {
            console.error("Error inserting mention in ProseMirror:", error);
        }
    }

    insertContentEditableMention(element, username) {
        try {
            const text = element.textContent || "";
            const atIndex = text.lastIndexOf("@");

            if (atIndex !== -1) {
                const textFromAt = text.substring(atIndex);
                const spaceIndex = textFromAt.indexOf(" ");
                const newlineIndex = textFromAt.indexOf("\n");

                let endIndex = textFromAt.length;
                if (spaceIndex !== -1)
                    endIndex = Math.min(endIndex, spaceIndex);
                if (newlineIndex !== -1)
                    endIndex = Math.min(endIndex, newlineIndex);

                const beforeAt = text.substring(0, atIndex);
                const afterPartial = text.substring(atIndex + endIndex);
                const newText = beforeAt + `@${username} ` + afterPartial;

                element.textContent = newText;
                element.focus();

                setTimeout(() => {
                    element.dispatchEvent(
                        new Event("input", { bubbles: true })
                    );
                }, 10);
            }
        } catch (error) {
            console.error("Error inserting mention in contenteditable:", error);
        }
    }

    releaseSelectionLock() {
        setTimeout(() => {
            this.state.mentionSelectionLock = false;
        }, 500);
    }
}

// Initialize the system
new UserMentionSystem();
