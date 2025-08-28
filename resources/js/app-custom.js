document.addEventListener("DOMContentLoaded", () => {
    // -----------------------------
    // Global Search Keyboard Shortcut + Custom Placeholder
    // -----------------------------
    const searchInput = document.querySelector(".fi-global-search input");

    // Set placeholder
    if (searchInput) {
        searchInput.placeholder = "CTRL + / to search";
    }

    // Keyboard shortcut: /
    document.addEventListener("keydown", function (e) {
        if (e.ctrlKey && e.key.toLowerCase() === "/") {
            e.preventDefault();
            const input = document.querySelector(".fi-global-search input");
            if (input) {
                input.focus();
            }
        }
    });
});
// -----------------------------
// Enable horizontal drag-scroll on Flowforge board
// -----------------------------
(function () {
    let isBound = false;
    function bind() {
        if (isBound) return;
        isBound = true;
        document.addEventListener("mousedown", function (e) {
            // Target the kanban board columns container directly
            const kanbanBoard = e.target.closest(
                ".ff-board__columns.kanban-board"
            );
            if (!kanbanBoard) return;

            // Don't interfere with card dragging or other interactive elements
            if (e.target.closest(".ff-card")) return;
            if (e.target.closest("button")) return;
            if (e.target.closest("input")) return;
            if (e.target.closest("select")) return;
            if (e.target.closest("textarea")) return;
            if (e.target.closest("[contenteditable]")) return;

            e.preventDefault(); // prevent text selection
            let isDown = true;
            const startX = e.pageX;
            const startScrollLeft = kanbanBoard.scrollLeft;
            kanbanBoard.classList.add("ff-drag-scrolling");

            const onMove = (ev) => {
                if (!isDown) return;
                kanbanBoard.scrollLeft = startScrollLeft - (ev.pageX - startX);
                ev.preventDefault();
            };

            const end = () => {
                isDown = false;
                kanbanBoard.classList.remove("ff-drag-scrolling");
                window.removeEventListener("mousemove", onMove);
                window.removeEventListener("mouseup", end);
                window.removeEventListener("mouseleave", end);
            };

            window.addEventListener("mousemove", onMove);
            window.addEventListener("mouseup", end);
            window.addEventListener("mouseleave", end);
        });
    }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bind);
    } else {
        bind();
    }
    document.addEventListener("livewire:navigated", function () {
        isBound = false;
        bind();
    });
})();
