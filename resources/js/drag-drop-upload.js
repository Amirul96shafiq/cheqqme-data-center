/**
 * Drag & Drop File Upload Handler
 * Enables drag-and-drop file uploads across the admin panel
 */

document.addEventListener("DOMContentLoaded", function () {
    console.log("Drag-drop-upload script loaded");

    const DragDropUpload = {
        overlay: null,
        dragCounter: 0,

        allowedTypes: [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "image/jpeg",
            "image/png",
            "image/gif",
            "text/plain",
            "text/csv",
        ],

        init() {
            this.createOverlay();
            this.bindEvents();
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
                background: rgba(59, 130, 246, 0.1);
                border: 3px dashed #3b82f6;
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: #3b82f6;
                font-weight: bold;
                backdrop-filter: blur(2px);
            `;
            this.overlay.innerHTML = "Drop file to upload document";
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
            console.log("File dropped:", file.name, file.type);

            if (!this.validateFile(file)) return;
            this.processFile(file);
        },

        validateFile(file) {
            if (!this.allowedTypes.includes(file.type)) {
                alert(
                    "File type not supported. Please upload PDF, Word, Excel, images, or text files."
                );
                return false;
            }
            return true;
        },

        processFile(file) {
            const fileData = {
                name: file.name,
                size: file.size,
                type: file.type,
                lastModified: file.lastModified,
            };

            const reader = new FileReader();
            reader.onload = (e) => {
                fileData.content = e.target.result;
                this.storeAndRedirect(fileData);
            };
            reader.readAsDataURL(file);
        },

        storeAndRedirect(fileData) {
            try {
                sessionStorage.setItem("draggedFile", JSON.stringify(fileData));
                console.log("File stored in sessionStorage, redirecting...");
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
