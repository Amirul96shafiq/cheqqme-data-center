import { initViewersBanners } from "./task-viewers.js";

// Initialize viewers banners for meeting link pages
initViewersBanners();

// Listen for copy-to-clipboard event
document.addEventListener("livewire:init", () => {
    Livewire.on("copy-to-clipboard", (event) => {
        // console.log("Copy event received:", event);
        const text = event.text;

        if (navigator.clipboard && text) {
            navigator.clipboard
                .writeText(text)
                .then(() => {
                    // console.log("Text copied successfully:", text);
                })
                .catch((err) => {
                    console.error("Failed to copy:", err);
                    // Fallback method
                    const textarea = document.createElement("textarea");
                    textarea.value = text;
                    textarea.style.position = "fixed";
                    textarea.style.opacity = "0";
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand("copy");
                    document.body.removeChild(textarea);
                    // console.log("Text copied using fallback method");
                });
        } else {
            console.error("No clipboard API or no text to copy");
        }
    });
});

// Warn before leaving page with unsaved meeting link
window.addEventListener("beforeunload", function (e) {
    const hasUnsavedMeeting = document.querySelector(
        '[wire\\:model="data.has_unsaved_meeting"]'
    );

    if (hasUnsavedMeeting && hasUnsavedMeeting.value === "1") {
        e.preventDefault();
        e.returnValue =
            "You have an unsaved meeting link. Are you sure you want to leave?";
        return e.returnValue;
    }
});
