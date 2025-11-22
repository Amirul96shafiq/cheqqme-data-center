// Auth Partial Navigation: replaces only the right-panel form content between auth pages
// Pages must include an element with id="auth-form-root" wrapping the form area.

(function () {
    if (typeof window === "undefined") return;

    // Global password toggle function for auth pages
    window.togglePassword = function (id) {
        var input = document.getElementById(id);
        if (!input) return;
        var eyeIcon = document.getElementById(id + "-eye");
        var eyeOffIcon = document.getElementById(id + "-eye-slash");

        input.type = input.type === "password" ? "text" : "password";
        var passwordVisible = input.type === "text";
        if (eyeIcon) eyeIcon.classList.toggle("hidden", passwordVisible);
        if (eyeOffIcon) eyeOffIcon.classList.toggle("hidden", !passwordVisible);
    };

    // Reinitialize remember me toggle after partial navigation
    function reinitializeRememberMeToggle() {
        // Simply re-run the original remember me toggle initialization
        if (window.RememberMeToggle) {
            new window.RememberMeToggle();
        } else {
            // Fallback: manually initialize if the class isn't available
            var checkbox = document.getElementById("remember");
            if (!checkbox) return;

            var toggleTrack = checkbox.nextElementSibling;
            var toggleThumb = toggleTrack?.nextElementSibling;

            if (!toggleTrack || !toggleThumb) return;

            function updateToggle() {
                if (checkbox.checked) {
                    toggleTrack.classList.remove(
                        "bg-gray-200",
                        "dark:bg-gray-600"
                    );
                    toggleTrack.classList.add("bg-primary-600");
                    toggleThumb.classList.remove("translate-x-0");
                    toggleThumb.classList.add("translate-x-5");
                } else {
                    toggleTrack.classList.remove("bg-primary-600");
                    toggleTrack.classList.add(
                        "bg-gray-200",
                        "dark:bg-gray-600"
                    );
                    toggleThumb.classList.remove("translate-x-5");
                    toggleThumb.classList.add("translate-x-0");
                }
            }

            // Initialize toggle state
            updateToggle();

            // Add event listener
            checkbox.addEventListener("change", updateToggle);
        }
    }

    var AUTH_PATHS_REGEX = /\/(login|forgot\-password|password\/reset)/;

    function getAuthFormRoot(doc) {
        return (doc || document).getElementById("auth-form-root");
    }

    function getRightPanelScroller() {
        return document.querySelector(".auth-frame .custom-scrollbar");
    }

    function sameOrigin(href) {
        try {
            var url = new URL(href, window.location.origin);
            return url.origin === window.location.origin;
        } catch (_) {
            return false;
        }
    }

    function shouldIntercept(href) {
        if (!href) return false;
        if (!sameOrigin(href)) return false;
        try {
            var url = new URL(href, window.location.href);
            return AUTH_PATHS_REGEX.test(url.pathname);
        } catch (_) {
            return false;
        }
    }

    async function loadAndSwap(href, replaceState) {
        var currentRoot = getAuthFormRoot(document);
        if (!currentRoot) {
            window.location.href = href;
            return;
        }

        // Start smooth fade-out
        currentRoot.classList.add("auth-form-fade-out");
        currentRoot.classList.add("pointer-events-none");

        try {
            var res = await fetch(href, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "text/html,application/xhtml+xml",
                },
                credentials: "same-origin",
            });

            if (!res.ok) throw new Error("Failed to fetch");
            var html = await res.text();
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, "text/html");
            var nextRoot = getAuthFormRoot(doc);
            if (!nextRoot) throw new Error("Target not found");

            // Update title
            if (doc.title) document.title = doc.title;

            // Prepare next content for fade-in
            nextRoot.classList.add("auth-form-fade-in", "pointer-events-none");

            // Swap node
            currentRoot.replaceWith(nextRoot);

            // Scroll top of right panel
            var scroller = getRightPanelScroller();
            if (scroller) scroller.scrollTop = 0;

            // Trigger fade-in and re-enable interactions after transition
            requestAnimationFrame(function () {
                // Allow layout to apply before removing fade-in class
                setTimeout(function () {
                    nextRoot.classList.remove("auth-form-fade-in");
                    nextRoot.classList.remove("pointer-events-none");

                    // Reinitialize remember me toggle if we're on login page
                    if (href.includes("/login")) {
                        reinitializeRememberMeToggle();
                    }
                }, 10);
            });

            // Update history
            if (replaceState) {
                window.history.replaceState({ authPartial: true }, "", href);
            } else {
                window.history.pushState({ authPartial: true }, "", href);
            }
        } catch (e) {
            // Fallback to full navigation
            window.location.href = href;
        } finally {
            var refreshedRoot = getAuthFormRoot(document);
            if (refreshedRoot) {
                // Ensure fade-out is cleared if still present
                refreshedRoot.classList.remove("auth-form-fade-out");
                // If we didn't swap (fallback), at least re-enable interactions
                refreshedRoot.classList.remove("pointer-events-none");
            }
        }
    }

    // Click interception for auth links inside the right panel
    document.addEventListener("click", function (e) {
        var anchor = e.target.closest("a");
        if (!anchor) return;

        // Only intercept left-click without modifier keys
        if (
            e.defaultPrevented ||
            e.button !== 0 ||
            e.metaKey ||
            e.ctrlKey ||
            e.shiftKey ||
            e.altKey
        )
            return;

        var href = anchor.getAttribute("href");
        if (!shouldIntercept(href)) return;

        // Ensure we are inside the auth layout
        if (!document.querySelector(".auth-frame")) return;

        e.preventDefault();
        loadAndSwap(href, false);
    });

    // Handle back/forward
    window.addEventListener("popstate", function () {
        if (!document.querySelector(".auth-frame")) return;
        loadAndSwap(window.location.href, true);
    });
})();
