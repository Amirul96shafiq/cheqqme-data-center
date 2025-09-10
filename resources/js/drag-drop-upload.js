document.addEventListener("DOMContentLoaded", function () {
    console.log("Drag-drop-upload script loaded");

    // Create drag and drop overlay
    const overlay = document.createElement("div");
    overlay.id = "drag-drop-overlay";
    overlay.style.cssText = `
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
    overlay.innerHTML = "Drop file to upload document";
    document.body.appendChild(overlay);

    // Prevent default drag behaviors
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
        document.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Track drag state to prevent flickering
    let dragCounter = 0;

    // Highlight drop area when item is dragged over it
    document.addEventListener(
        "dragenter",
        function (e) {
            dragCounter++;
            if (dragCounter === 1) {
                overlay.style.display = "flex";
            }
        },
        false
    );

    document.addEventListener(
        "dragover",
        function (e) {
            e.preventDefault();
            overlay.style.display = "flex";
        },
        false
    );

    document.addEventListener(
        "dragleave",
        function (e) {
            dragCounter--;
            if (dragCounter === 0) {
                overlay.style.display = "none";
            }
        },
        false
    );

    document.addEventListener(
        "drop",
        function (e) {
            dragCounter = 0;
            overlay.style.display = "none";
        },
        false
    );

    // Handle dropped files
    document.addEventListener("drop", handleDrop, false);

    function handleDrop(e) {
        console.log("File dropped");
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            const file = files[0];
            console.log("File:", file.name, file.type);

            // Validate file type
            const allowedTypes = [
                "application/pdf",
                "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                "application/vnd.ms-excel",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "image/jpeg",
                "image/png",
                "image/gif",
                "text/plain",
            ];

            if (!allowedTypes.includes(file.type)) {
                alert(
                    "File type not supported. Please upload PDF, Word, Excel, images, or text files."
                );
                return;
            }

            // Store file data in sessionStorage
            const fileData = {
                name: file.name,
                size: file.size,
                type: file.type,
                lastModified: file.lastModified,
            };

            // Convert file to base64 for storage
            const reader = new FileReader();
            reader.onload = function (e) {
                fileData.content = e.target.result;
                sessionStorage.setItem("draggedFile", JSON.stringify(fileData));
                console.log("File stored in sessionStorage, redirecting...");

                // Redirect to document creation page
                window.location.href = "/admin/documents/create";
            };
            reader.readAsDataURL(file);
        }
    }
});
