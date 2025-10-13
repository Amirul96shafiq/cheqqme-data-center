// Listen for copy-to-clipboard event
document.addEventListener("livewire:init", () => {
    Livewire.on("copy-to-clipboard", (event) => {
        const text = event.text;

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).catch((err) => {
                console.error("Failed to copy:", err);
            });
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


