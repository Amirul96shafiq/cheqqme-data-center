/**
 * Drag & Drop File Upload Handler
 * Enables drag-and-drop file uploads across the admin panel
 */

document.addEventListener("DOMContentLoaded", function () {
    // console.log("Drag-drop-upload script loaded");

    const DragDropUpload = {
        overlay: null,
        dragCounter: 0,

        allowedTypes: [
            "application/pdf",
            "image/jpeg",
            "image/png",
            "application/msword", // doc
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document", // docx
            "application/vnd.ms-excel", // xls
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", // xlsx
            "text/csv", // csv
            "application/vnd.ms-powerpoint", // ppt
            "application/vnd.openxmlformats-officedocument.presentationml.presentation", // pptx
            "video/mp4", // mp4
        ],

        init() {
            // Check if we're on the Action Board page - if so, disable global drag-drop
            if (this.isActionBoardPage()) {
                const message =
                    window.dragDropLang?.actionBoardDetected ||
                    "Action Board page detected, disabling global drag-drop to allow card drag-drop";
                // console.log(message);
                return;
            }

            // Check if page has Filament upload fields - if so, disable global drag-drop
            if (this.hasFilamentUploadFields()) {
                const message =
                    window.dragDropLang?.filamentUploadDetected ||
                    "Filament upload fields detected, using native drag-drop instead";
                // console.log(message);
                return;
            }

            this.createOverlay();
            this.bindEvents();
        },

        isActionBoardPage() {
            // Check if current URL matches Action Board page
            const currentPath = window.location.pathname;
            return currentPath === "/admin/action-board";
        },

        hasFilamentUploadFields() {
            // Check for various Filament file upload field selectors
            const uploadSelectors = [
                'input[type="file"]', // Standard file inputs
                ".fi-fo-file-upload", // Filament file upload component
                '.fi-fo-file-upload input[type="file"]', // File input within Filament upload
                '[wire\\:model*="file"]', // Livewire file model bindings
                '[wire\\:model*="upload"]', // Livewire upload model bindings
                '.fi-input[data-field*="file"]', // Filament input with file field
                '.fi-input[data-field*="upload"]', // Filament input with upload field
                'input[name*="file"]', // Inputs with "file" in name
                'input[name*="upload"]', // Inputs with "upload" in name
            ];

            for (const selector of uploadSelectors) {
                const elements = document.querySelectorAll(selector);
                if (elements.length > 0) {
                    // console.log(
                    //     `Found ${elements.length} Filament upload field(s) with selector: ${selector}`
                    // );
                    return true;
                }
            }

            return false;
        },

        createOverlay() {
            this.overlay = document.createElement("div");
            this.overlay.id = "drag-drop-overlay";
            this.overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #fbb43e99;
                border: 5px dashed rgb(78 29 4);
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: rgb(78 29 4);
                font-weight: bold;
                backdrop-filter: blur(10px);
                flex-direction: column;
            `;
            this.overlay.innerHTML = `
                <svg class="w-12 h-12 mb-4 animate-bounce" style="animation-duration: 2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"></path>
                </svg>
                <div style="text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; margin-bottom: 8px;">${
                        window.dragDropLang?.drop_file_to_upload_document ||
                        "Drop file to upload document"
                    }</div>
                    <div style="font-size: 16px; font-weight: semibold; opacity: 0.8;">${
                        window.dragDropLang
                            ?.drop_file_to_upload_document_helper ||
                        "Automatically upload below 5MB files. If it's larger than 5MB, upload the file manually."
                    }</div>
                </div>
            `;
            document.body.appendChild(this.overlay);
        },

        bindEvents() {
            // Prevent default drag behaviors
            ["dragenter", "dragover", "dragleave", "drop"].forEach(
                (eventName) => {
                    document.addEventListener(
                        eventName,
                        this.preventDefaults.bind(this),
                        false
                    );
                }
            );

            // Handle drag events
            document.addEventListener(
                "dragenter",
                this.handleDragEnter.bind(this),
                false
            );
            document.addEventListener(
                "dragover",
                this.handleDragOver.bind(this),
                false
            );
            document.addEventListener(
                "dragleave",
                this.handleDragLeave.bind(this),
                false
            );
            document.addEventListener(
                "drop",
                this.handleDrop.bind(this),
                false
            );
        },

        preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        },

        handleDragEnter(e) {
            this.dragCounter++;
            if (this.dragCounter === 1) {
                this.overlay.style.display = "flex";
            }
        },

        handleDragOver(e) {
            e.preventDefault();
            this.overlay.style.display = "flex";
        },

        handleDragLeave(e) {
            this.dragCounter--;
            if (this.dragCounter === 0) {
                this.overlay.style.display = "none";
            }
        },

        handleDrop(e) {
            this.dragCounter = 0;
            this.overlay.style.display = "none";

            const files = e.dataTransfer.files;
            if (files.length === 0) return;

            const file = files[0];
            // console.log("File dropped:", file.name, file.type);

            if (!this.validateFile(file)) return;
            this.processFile(file);
        },

        validateFile(file) {
            if (!this.allowedTypes.includes(file.type)) {
                alert(
                    window.dragDropLang?.unsupportedFileType ||
                        "File type not supported. Please upload PDF, Word, Excel, PowerPoint, images, videos, or CSV files."
                );
                return false;
            }
            return true;
        },

        processFile(file) {
            // Check file size limit (20MB as per DocumentResource)
            const maxSize = 20 * 1024 * 1024; // 20MB in bytes
            if (file.size > maxSize) {
                const fileSizeMB = (file.size / 1024 / 1024).toFixed(1);
                const message =
                    window.dragDropLang?.fileTooLarge?.replace(
                        ":sizeMB",
                        fileSizeMB
                    ) ||
                    `File size exceeds 20MB limit. Your file is ${fileSizeMB}MB.`;
                alert(message);
                return;
            }

            const fileData = {
                name: file.name,
                size: file.size,
                type: file.type,
                lastModified: file.lastModified,
            };

            // Simple size-based logic
            if (file.size <= 5 * 1024 * 1024) {
                // 5MB or less
                // console.log("Small file detected, using auto-upload approach");
                // For small files, use base64 approach
                const reader = new FileReader();
                reader.onload = (e) => {
                    fileData.content = e.target.result;
                    this.storeAndRedirect(fileData);
                };
                reader.readAsDataURL(file);
            } else {
                // console.log(
                //     "Large file detected, using metadata-only approach"
                // );
                this.handleLargeFile(fileData);
            }
        },

        handleLargeFile(fileData) {
            // For large files, we'll redirect without storing the file content
            // The user will need to manually upload the file on the create page
            // console.log("Redirecting for large file upload:", fileData.name);

            // Store only metadata, not the file content
            const metadata = {
                name: fileData.name,
                size: fileData.size,
                type: fileData.type,
                lastModified: fileData.lastModified,
                isLargeFile: true,
            };

            try {
                sessionStorage.setItem("draggedFile", JSON.stringify(metadata));
                // console.log("Large file metadata stored, redirecting...");
                window.location.href = "/admin/documents/create";
            } catch (error) {
                console.error("Error storing large file metadata:", error);
                alert(
                    window.dragDropLang?.fileTooLarge?.replace(
                        ":sizeMB",
                        (fileData.size / 1024 / 1024).toFixed(1)
                    ) ||
                        "File too large for drag-and-drop. Please use the upload form directly."
                );
            }
        },

        storeAndRedirect(fileData) {
            try {
                sessionStorage.setItem("draggedFile", JSON.stringify(fileData));
                // console.log("File stored in sessionStorage, redirecting...");
                window.location.href = "/admin/documents/create";
            } catch (error) {
                console.error("Error storing file:", error);
                alert("Error processing file. Please try again.");
            }
        },
    };

    // Initialize drag and drop functionality
    DragDropUpload.init();
});
