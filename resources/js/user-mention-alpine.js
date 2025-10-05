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
        // Note: The userMentionDropdown component is now defined in the Blade component
        // to avoid duplication. This file is kept for any global mention utilities.

        // Initialize editors
        initializeAllEditors();
    });
}

// Legacy no-op: dropdown is provided by Blade component now
function addMentionDropdownToPage() {}

// User Mention Dropdown Alpine.js Component - DEPRECATED
// This component has been moved to the Blade component to avoid duplication
// Keeping this file for reference but the component is now defined in the Blade template

// Legacy no-op: component is now provided by Blade component
function userMentionDropdown() {
    console.warn(
        "userMentionDropdown from JS file is deprecated. Use the Blade component instead."
    );
    return {};
}

// Initialize all editors (Trix, ProseMirror, etc.)
function initializeAllEditors() {
    // Wait a bit for DOM to be fully ready
    setTimeout(() => {
        initializeTrixEditors();
        initializeProseMirrorEditors();
        initializeContentEditableElements();
    }, 100);
}

// Initialize Trix editors
function initializeTrixEditors() {
    const trixEditors = document.querySelectorAll("trix-editor");
    trixEditors.forEach((editor) => {
        if (!editor.hasAttribute("data-mention-initialized")) {
            editor.setAttribute("data-mention-initialized", "true");
            setupTrixMentionHandling(editor);
        }
    });
}

// Initialize ProseMirror editors
function initializeProseMirrorEditors() {
    const proseMirrorEditors = document.querySelectorAll(".ProseMirror");
    proseMirrorEditors.forEach((editor) => {
        if (!editor.hasAttribute("data-mention-initialized")) {
            editor.setAttribute("data-mention-initialized", "true");
            setupProseMirrorMentionHandling(editor);
        }
    });
}

// Initialize contenteditable elements
function initializeContentEditableElements() {
    const contentEditableElements = document.querySelectorAll(
        '[contenteditable="true"]'
    );
    contentEditableElements.forEach((element) => {
        if (
            !element.hasAttribute("data-mention-initialized") &&
            !element.closest("trix-editor") &&
            !element.classList.contains("ProseMirror")
        ) {
            element.setAttribute("data-mention-initialized", "true");
            setupContentEditableMentionHandling(element);
        }
    });
}

// Setup mention handling for Trix editors
function setupTrixMentionHandling(trixEditor) {
    // Trix-specific mention handling
    trixEditor.addEventListener("trix-change", (e) => {
        handleTrixMentionInput(e, trixEditor);
    });

    trixEditor.addEventListener("keydown", (e) => {
        handleMentionKeydown(e, trixEditor);
    });
}

// Setup mention handling for ProseMirror editors
function setupProseMirrorMentionHandling(proseMirrorEditor) {
    // ProseMirror-specific mention handling
    proseMirrorEditor.addEventListener("input", (e) => {
        handleProseMirrorMentionInput(e, proseMirrorEditor);
    });

    proseMirrorEditor.addEventListener("keydown", (e) => {
        handleMentionKeydown(e, proseMirrorEditor);
    });
}

// Setup mention handling for contenteditable elements
function setupContentEditableMentionHandling(element) {
    // Contenteditable-specific mention handling
    element.addEventListener("input", (e) => {
        handleContentEditableMentionInput(e, element);
    });

    element.addEventListener("keydown", (e) => {
        handleMentionKeydown(e, element);
    });
}

// Handle mention input for Trix editors
function handleTrixMentionInput(e, trixEditor) {
    if (mentionState.insertingMention) return;

    const document = trixEditor.editor.getDocument();
    const range = trixEditor.editor.getSelectedRange();
    const text = document.toString();
    const cursorPosition = range[0];
    const beforeCursor = text.substring(0, cursorPosition);

    const atIndex = beforeCursor.lastIndexOf("@");
    if (atIndex !== -1) {
        const afterAt = beforeCursor.substring(atIndex + 1);
        if (!afterAt.includes(" ") && !afterAt.includes("\n")) {
            const searchTerm = afterAt;
            updateTrixMentionDropdown(trixEditor, searchTerm);
        } else {
            hideMentionDropdown();
        }
    } else {
        hideMentionDropdown();
    }
}

// Handle mention input for ProseMirror editors
function handleProseMirrorMentionInput(e, proseMirrorEditor) {
    if (mentionState.insertingMention) return;

    const text = proseMirrorEditor.textContent || proseMirrorEditor.innerText;
    const selection = window.getSelection();
    const cursorPosition = selection.anchorOffset;
    const beforeCursor = text.substring(0, cursorPosition);

    const atIndex = beforeCursor.lastIndexOf("@");
    if (atIndex !== -1) {
        const afterAt = beforeCursor.substring(atIndex + 1);
        if (!afterAt.includes(" ") && !afterAt.includes("\n")) {
            const searchTerm = afterAt;
            updateProseMirrorMentionDropdown(proseMirrorEditor, searchTerm);
        } else {
            hideMentionDropdown();
        }
    } else {
        hideMentionDropdown();
    }
}

// Handle mention input for contenteditable elements
function handleContentEditableMentionInput(e, element) {
    if (mentionState.insertingMention) return;

    const text = element.textContent || element.innerText;
    const selection = window.getSelection();
    const cursorPosition = selection.anchorOffset;
    const beforeCursor = text.substring(0, cursorPosition);

    const atIndex = beforeCursor.lastIndexOf("@");
    if (atIndex !== -1) {
        const afterAt = beforeCursor.substring(atIndex + 1);
        if (!afterAt.includes(" ") && !afterAt.includes("\n")) {
            const searchTerm = afterAt;
            updateContentEditableMentionDropdown(element, searchTerm);
        } else {
            hideMentionDropdown();
        }
    } else {
        hideMentionDropdown();
    }
}

// Handle mention keydown events
function handleMentionKeydown(e, editor) {
    if (!mentionState.dropdownActive) return;

    switch (e.key) {
        case "ArrowUp":
        case "ArrowDown":
        case "Enter":
        case "Escape":
            e.preventDefault();
            break;
    }
}

// Update mention dropdown for Trix editor
function updateTrixMentionDropdown(trixEditor, searchTerm) {
    const inputId = getInputIdFromEditor(trixEditor);
    const composerPosition = getComposerBottomLeftPosition(trixEditor);
    const position = composerPosition || {
        left: trixEditor.getBoundingClientRect().left,
        top: trixEditor.getBoundingClientRect().bottom + 5,
    };

    window.dispatchEvent(
        new CustomEvent("showMentionDropdown", {
            detail: {
                inputId: inputId,
                searchTerm: searchTerm,
                x: position.left,
                y: position.top,
            },
        })
    );
}

// Update mention dropdown for ProseMirror editor
function updateProseMirrorMentionDropdown(proseMirrorEditor, searchTerm) {
    const inputId = getInputIdFromEditor(proseMirrorEditor);
    const position = {
        left: proseMirrorEditor.getBoundingClientRect().left,
        top: proseMirrorEditor.getBoundingClientRect().bottom + 5,
    };

    window.dispatchEvent(
        new CustomEvent("showMentionDropdown", {
            detail: {
                inputId: inputId,
                searchTerm: searchTerm,
                x: position.left,
                y: position.top,
            },
        })
    );
}

// Update mention dropdown for contenteditable element
function updateContentEditableMentionDropdown(element, searchTerm) {
    const inputId = getInputIdFromEditor(element);
    const position = {
        left: element.getBoundingClientRect().left,
        top: element.getBoundingClientRect().bottom + 5,
    };

    window.dispatchEvent(
        new CustomEvent("showMentionDropdown", {
            detail: {
                inputId: inputId,
                searchTerm: searchTerm,
                x: position.left,
                y: position.top,
            },
        })
    );
}

// Hide mention dropdown
function hideMentionDropdown() {
    window.dispatchEvent(new CustomEvent("hideMentionDropdown"));
}

// Get input ID from editor element
function getInputIdFromEditor(editor) {
    // Try to find associated input or form
    const form = editor.closest("form");
    if (form) {
        const input = form.querySelector(
            'input[name*="comment"], input[name*="message"], input[name*="content"]'
        );
        if (input) {
            return input.id || input.name;
        }
    }

    // Fallback to editor ID or generate one
    return editor.id || `editor-${Math.random().toString(36).substr(2, 9)}`;
}

// Get composer bottom left position for Trix editor
function getComposerBottomLeftPosition(trixEditor) {
    const rect = trixEditor.getBoundingClientRect();
    return {
        left: rect.left,
        top: rect.bottom + 5,
    };
}

// Listen for user selection events
window.addEventListener("userSelected", (e) => {
    const { username, userId, inputId } = e.detail;

    // Find the target editor
    const targetEditor =
        document.querySelector(`#${inputId}`) ||
        document.querySelector(`[data-input-id="${inputId}"]`) ||
        document.querySelector("trix-editor") ||
        document.querySelector(".ProseMirror") ||
        document.querySelector('[contenteditable="true"]');

    if (targetEditor) {
        insertMention(targetEditor, username);
    }
});

// Insert mention into editor
function insertMention(editor, username) {
    mentionState.insertingMention = true;

    try {
        if (editor.tagName === "TRIX-EDITOR") {
            insertTrixMention(editor, username);
        } else if (editor.classList.contains("ProseMirror")) {
            insertProseMirrorMention(editor, username);
        } else {
            insertContentEditableMention(editor, username);
        }
    } finally {
        setTimeout(() => {
            mentionState.insertingMention = false;
        }, 100);
    }
}

// Insert mention into Trix editor
function insertTrixMention(trixEditor, username) {
    if (!trixEditor.editor) return;

    const document = trixEditor.editor.getDocument();
    const range = trixEditor.editor.getSelectedRange();
    const text = document.toString();
    const cursorPosition = range[0];
    const beforeCursor = text.substring(0, cursorPosition);

    const atIndex = beforeCursor.lastIndexOf("@");
    if (atIndex !== -1) {
        const selectionRange = [atIndex, cursorPosition];
        trixEditor.editor.setSelectedRange(selectionRange);
        trixEditor.editor.insertString(`@${username} `);
    }
}

// Insert mention into ProseMirror editor
function insertProseMirrorMention(proseMirrorEditor, username) {
    const selection = window.getSelection();
    const text = proseMirrorEditor.textContent || proseMirrorEditor.innerText;
    const cursorPosition = selection.anchorOffset;
    const beforeCursor = text.substring(0, cursorPosition);

    const atIndex = beforeCursor.lastIndexOf("@");
    if (atIndex !== -1) {
        const range = document.createRange();
        range.setStart(proseMirrorEditor.firstChild, atIndex);
        range.setEnd(proseMirrorEditor.firstChild, cursorPosition);
        selection.removeAllRanges();
        selection.addRange(range);

        const mentionText = `@${username} `;
        document.execCommand("insertText", false, mentionText);
    }
}

// Insert mention into contenteditable element
function insertContentEditableMention(element, username) {
    const selection = window.getSelection();
    const text = element.textContent || element.innerText;
    const cursorPosition = selection.anchorOffset;
    const beforeCursor = text.substring(0, cursorPosition);

    const atIndex = beforeCursor.lastIndexOf("@");
    if (atIndex !== -1) {
        const range = document.createRange();
        range.setStart(element.firstChild, atIndex);
        range.setEnd(element.firstChild, cursorPosition);
        selection.removeAllRanges();
        selection.addRange(range);

        const mentionText = `@${username} `;
        document.execCommand("insertText", false, mentionText);
    }
}
