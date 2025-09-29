// Language data for drag-drop functionality
const dragDropLang = {
    largeFileTitle:
        window.dragDropLang?.largeFileTitle || "Large File Detected",
    largeFileMessage:
        window.dragDropLang?.largeFileMessage ||
        "File :filename (:sizeMB) is too large for direct upload.",
    fileTooLarge: window.dragDropLang?.fileTooLarge || "File too large",
    unsupportedFileType:
        window.dragDropLang?.unsupportedFileType || "Unsupported file type",
};

// Handle drag-drop functionality
document.addEventListener("DOMContentLoaded", function () {
    const draggedFile = sessionStorage.getItem("draggedFile");
    if (!draggedFile) return;

    const fileData = JSON.parse(draggedFile);
    // console.log("Processing dragged file:", fileData.name);
    sessionStorage.removeItem("draggedFile");

    // Check if this is a large file
    if (fileData.isLargeFile) {
        // console.log("Large file detected, setting title and document type");
        initializeLargeFileAutoFill(fileData);

        // Retry after Livewire loads
        document.addEventListener("livewire:init", () => {
            setTimeout(() => initializeLargeFileAutoFill(fileData), 100);
        });
    } else {
        // Initialize form auto-fill for regular files
        initializeFormAutoFill(fileData);

        // Retry after Livewire loads
        document.addEventListener("livewire:init", () => {
            setTimeout(() => initializeFormAutoFill(fileData), 100);
        });
    }
});

function initializeFormAutoFill(fileData) {
    // Set title field
    setTitleField(fileData.name);

    // Set document type and file upload
    setDocumentTypeAndFile(fileData);
}

function initializeLargeFileAutoFill(fileData) {
    // Set title field
    setTitleField(fileData.name);

    // Set document type
    setDocumentType("internal");

    // Show notification
    showLargeFileMessage(fileData);
}

// Set title field
function setTitleField(fileName) {
    const titleSelectors = [
        'input[name="data[title]"]',
        'input[wire\\:model="data.title"]',
        'input[data-field="title"]',
        'input[placeholder*="title" i]',
        'input[placeholder*="document" i]',
        'input[placeholder*="name" i]',
        '.fi-input[data-field="title"]',
        'input[type="text"]:first-of-type',
    ];

    let titleField = findElement(titleSelectors);

    // Aggressive search if not found
    if (!titleField) {
        const forms = document.querySelectorAll("form");
        forms.forEach((form) => {
            const inputs = form.querySelectorAll('input[type="text"]');
            inputs.forEach((input) => {
                if (
                    input.name === "data[title]" ||
                    input.getAttribute("wire:model") === "data.title" ||
                    input.getAttribute("data-field") === "title" ||
                    (input.placeholder &&
                        input.placeholder.toLowerCase().includes("title"))
                ) {
                    titleField = input;
                }
            });
        });
    }

    // Set title field value
    if (titleField) {
        // console.log("Setting document title:", fileName);
        setFieldValue(titleField, fileName);
    } else {
        // console.log("Title field not found, retrying...");
        setTimeout(() => setTitleField(fileName), 500);
        setTimeout(() => setTitleField(fileName), 1500);
    }
}

// Set document type and file upload
function setDocumentTypeAndFile(fileData) {
    const typeSelectors = [
        'select[name="data[type]"]',
        'select[wire\\:model="data.type"]',
        'select[data-field="type"]',
        '[data-field="type"]',
        '[wire\\:model="data.type"]',
    ];

    const documentTypeField = findElement(typeSelectors);

    if (documentTypeField) {
        setFieldValue(documentTypeField, "internal");
    }

    // Proceed with file upload after a delay
    setTimeout(() => setFileUpload(fileData), 1000);
}

// Set document type
function setDocumentType(type) {
    const typeSelectors = [
        'select[name="data[type]"]',
        'select[wire\\:model="data.type"]',
        'select[data-field="type"]',
        '[data-field="type"]',
        '[wire\\:model="data.type"]',
    ];

    const documentTypeField = findElement(typeSelectors);

    if (documentTypeField) {
        setFieldValue(documentTypeField, type);
    }
}

// Show large file message
function showLargeFileMessage(fileData) {
    // Check if notification already exists to prevent duplicates
    const existingNotification = document.querySelector(
        ".large-file-notification"
    );
    if (existingNotification) {
        return; // Notification already exists, don't create another
    }

    // Create a notification for large files using language data
    const fileSizeMB = (fileData.size / 1024 / 1024).toFixed(1);
    const message = dragDropLang.largeFileMessage
        .replace(":sizeMB", fileSizeMB + "MB")
        .replace(":filename", fileData.name);

    // Try to find a notification area or create one
    let notificationArea =
        document.querySelector(".fi-notifications") ||
        document.querySelector(".notifications") ||
        document.querySelector(".fi-form");

    // Create notification area if not found
    if (notificationArea) {
        const notification = document.createElement("div");
        notification.className =
            "large-file-notification fi-no-notification w-full overflow-hidden transition-all duration-500 ease-in-out max-w-full rounded-xl bg-white shadow-lg ring-1 dark:bg-gray-900 ring-teal-600/20 dark:ring-teal-400/30 fi-color-teal fi-status-teal"; // Add class for duplicate detection
        notification.style.cssText = `
            margin: 10px 0;
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        `;
        notification.innerHTML = `
            <div class="flex w-full gap-3 p-4 bg-teal-50 dark:bg-teal-400/10">
                <div class="mt-0.5 grid flex-1">
                    <h3 class="fi-no-notification-title text-sm font-medium text-gray-950 dark:text-white">
                        ${dragDropLang.largeFileTitle}
                    </h3>
                    <div class="fi-no-notification-body overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400 mt-1">
                        ${message}
                    </div>
                </div>
            </div>
        `;

        // Insert at the top of the form
        notificationArea.insertBefore(
            notification,
            notificationArea.firstChild
        );

        // Trigger fade-in animation
        requestAnimationFrame(() => {
            notification.style.opacity = "1";
            notification.style.transform = "translateY(0)";
        });

        // Auto-remove after 10 seconds with fade-out animation
        setTimeout(() => {
            if (notification.parentNode) {
                // Start fade-out animation
                notification.style.opacity = "0";
                notification.style.transform = "translateY(-20px)";

                // Remove from DOM after animation completes
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 500); // Match the transition duration
            }
        }, 10000);
    }

    // console.log("Large file message:", message);
}

// Set file upload
function setFileUpload(fileData) {
    const file = createFileFromBase64(fileData);
    const fileInput = findFileInput();

    if (fileInput) {
        // console.log("Starting file upload:", fileData.name);
        setFileInputValue(fileInput, file);
    } else {
        // console.log("File input not found");
    }
}

// Find element
function findElement(selectors) {
    for (const selector of selectors) {
        const element = document.querySelector(selector);
        if (element) return element;
    }
    return null;
}

// Find file input
function findFileInput() {
    const fileSelectors = [
        'input[type="file"]',
        '[wire\\:model="data.file_path"]',
        '[data-field="file_path"]',
        'input[name="data[file_path]"]',
        '.fi-fo-file-upload input[type="file"]',
        '[data-component="file-upload"] input[type="file"]',
        'input[accept*="pdf"]',
        'input[accept*="image"]',
        'input[accept*="word"]',
    ];

    return findElement(fileSelectors);
}

// Set field value
function setFieldValue(field, value) {
    // Set value using multiple approaches
    field.value = value;
    field.setAttribute("value", value);

    // Trigger events for Filament/Livewire
    ["input", "change", "blur"].forEach((eventType) => {
        field.dispatchEvent(new Event(eventType, { bubbles: true }));
    });

    if (window.Livewire) {
        field.dispatchEvent(new Event("livewire:update", { bubbles: true }));
    }

    // Trigger focus/blur cycle for better compatibility
    field.focus();
    field.blur();
}

// Create file from base64
function createFileFromBase64(fileData) {
    const byteCharacters = atob(fileData.content.split(",")[1]);
    const byteNumbers = new Array(byteCharacters.length);

    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }

    const byteArray = new Uint8Array(byteNumbers);
    return new File([byteArray], fileData.name, { type: fileData.type });
}

// Set file input value
function setFileInputValue(fileInput, file) {
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;

    // Trigger events for Filament/Livewire
    ["change", "input"].forEach((eventType) => {
        fileInput.dispatchEvent(new Event(eventType, { bubbles: true }));
    });

    // Verify file was set
    setTimeout(() => {
        if (fileInput.files.length > 0) {
            // console.log("File uploaded successfully:", fileInput.files[0].name);
        } else {
            // console.log("File upload failed");
        }
    }, 100);
}
