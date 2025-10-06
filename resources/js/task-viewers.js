export function initTaskViewersBanner(root) {
    var container = root || document.getElementById("task-viewers-banner");
    if (!container) return;

    var taskId = container.getAttribute("data-task-id");
    if (!taskId) return;

    var avatars = container.querySelector("#task-viewers-avatars");
    var names = container.querySelector("#task-viewers-names");

    function othersOnly(list) {
        var currentUserId =
            window.currentUser && window.currentUser.id
                ? String(window.currentUser.id)
                : null;
        if (!Array.isArray(list)) return [];
        if (!currentUserId) return list;
        return list.filter(function (u) {
            return String(u.id) !== currentUserId;
        });
    }

    function render(users) {
        var viewers = othersOnly(users);
        if (!Array.isArray(viewers) || viewers.length === 0) {
            container.classList.add("hidden");
            return;
        }
        container.classList.remove("hidden");

        if (avatars) {
            avatars.innerHTML = "";
            viewers.slice(0, 5).forEach(function (u) {
                if (u.avatar) {
                    var img = document.createElement("img");
                    img.className = "h-6 w-6 rounded-full";
                    img.alt = u.name || "User";
                    img.src = u.avatar;
                    avatars.appendChild(img);
                } else {
                    var span = document.createElement("span");
                    span.className =
                        "flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-medium text-gray-200 bg-gray-950";
                    var initials = (u.name || "")
                        .trim()
                        .split(/\s+/)
                        .map(function (p) {
                            return p[0] ? p[0].toUpperCase() : "";
                        })
                        .slice(0, 2)
                        .join("");
                    span.textContent = initials || "?";
                    avatars.appendChild(span);
                }
            });
            if (viewers.length > 5) {
                var more = document.createElement("span");
                more.className = "ml-1 text-xs font-medium";
                more.textContent = "+" + (viewers.length - 5);
                avatars.appendChild(more);
            }
        }

        if (names) {
            var display = viewers.map(function (u) {
                return u.name || "User #" + (u.id || "?");
            });
            names.textContent = display.join(", ");
        }
    }

    var attempts = 0;
    var maxAttempts = 20;
    var retryDelayMs = 250;

    function start() {
        try {
            if (typeof window.Echo === "undefined") {
                if (attempts++ < maxAttempts)
                    return setTimeout(start, retryDelayMs);
                return;
            }

            var current = [];
            window.Echo.join("task-viewers." + taskId)
                .here(function (users) {
                    current = users;
                    render(current);
                })
                .joining(function (user) {
                    current = current.concat([user]);
                    render(current);
                })
                .leaving(function (user) {
                    current = current.filter(function (u) {
                        return String(u.id) !== String(user.id);
                    });
                    render(current);
                })
                .error(function () {
                    container.classList.add("hidden");
                });
        } catch (e) {
            // no-op
        }
    }

    if (
        document.readyState === "complete" ||
        document.readyState === "interactive"
    ) {
        start();
    } else {
        document.addEventListener("DOMContentLoaded", start);
    }
}

// Auto-init on pages that include the banner
if (typeof window !== "undefined") {
    window.initTaskViewersBanner = initTaskViewersBanner;
    document.addEventListener("DOMContentLoaded", function () {
        var el = document.getElementById("task-viewers-banner");
        if (el) initTaskViewersBanner(el);
    });
}

// Simple helper to init a board-wide viewers banner
export function initBoardViewersBanner(root) {
    var container = root || document.getElementById("board-viewers-banner");
    if (!container) return;

    var boardId = container.getAttribute("data-board-id") || "action-board";
    var avatars = container.querySelector("#task-viewers-avatars");
    var names = container.querySelector("#task-viewers-names");

    function othersOnly(list) {
        var currentUserId =
            window.currentUser && window.currentUser.id
                ? String(window.currentUser.id)
                : null;
        if (!Array.isArray(list)) return [];
        if (!currentUserId) return list;
        return list.filter(function (u) {
            return String(u.id) !== currentUserId;
        });
    }

    function render(users) {
        var viewers = othersOnly(users);
        if (!Array.isArray(viewers) || viewers.length === 0) {
            container.classList.add("hidden");
            return;
        }
        container.classList.remove("hidden");

        if (avatars) {
            avatars.innerHTML = "";
            viewers.slice(0, 5).forEach(function (u) {
                if (u.avatar) {
                    var img = document.createElement("img");
                    img.className = "h-6 w-6 rounded-full dark:ring-gray-900";
                    img.alt = u.name || "User";
                    img.src = u.avatar;
                    avatars.appendChild(img);
                } else {
                    var span = document.createElement("span");
                    span.className =
                        "flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-medium text-gray-200 bg-gray-950";
                    var initials = (u.name || "")
                        .trim()
                        .split(/\s+/)
                        .map(function (p) {
                            return p[0] ? p[0].toUpperCase() : "";
                        })
                        .slice(0, 2)
                        .join("");
                    span.textContent = initials || "?";
                    avatars.appendChild(span);
                }
            });
            if (viewers.length > 5) {
                var more = document.createElement("span");
                more.className = "ml-1 text-xs font-medium";
                more.textContent = "+" + (viewers.length - 5);
                avatars.appendChild(more);
            }
        }

        if (names) {
            var display = viewers.map(function (u) {
                return u.name || "User #" + (u.id || "?");
            });
            names.textContent = display.join(", ");
        }
    }

    var attempts = 0;
    var maxAttempts = 20;
    var retryDelayMs = 250;

    function start() {
        try {
            if (typeof window.Echo === "undefined") {
                if (attempts++ < maxAttempts)
                    return setTimeout(start, retryDelayMs);
                return;
            }

            var current = [];
            window.Echo.join("board-viewers." + boardId)
                .here(function (users) {
                    current = users;
                    render(current);
                })
                .joining(function (user) {
                    current = current.concat([user]);
                    render(current);
                })
                .leaving(function (user) {
                    current = current.filter(function (u) {
                        return String(u.id) !== String(user.id);
                    });
                    render(current);
                })
                .error(function () {
                    container.classList.add("hidden");
                });
        } catch (e) {
            // no-op
        }
    }

    if (
        document.readyState === "complete" ||
        document.readyState === "interactive"
    ) {
        start();
    } else {
        document.addEventListener("DOMContentLoaded", start);
    }
}

if (typeof window !== "undefined") {
    window.initBoardViewersBanner = initBoardViewersBanner;
    document.addEventListener("DOMContentLoaded", function () {
        var el = document.getElementById("board-viewers-banner");
        if (el) initBoardViewersBanner(el);
    });
}

// Generic initializer for any .viewers-banner component
export function initViewersBanners() {
    var banners = document.querySelectorAll(".viewers-banner");
    if (!banners.length) return;

    banners.forEach(function (container) {
        var channel = container.getAttribute("data-channel");
        var id = container.getAttribute("data-id");

        var avatars = container.querySelector(".avatars");
        var names = container.querySelector(".names");

        function othersOnly(list) {
            var currentUserId =
                window.currentUser && window.currentUser.id
                    ? String(window.currentUser.id)
                    : null;
            if (!Array.isArray(list)) return [];
            if (!currentUserId) return list;
            return list.filter(function (u) {
                return String(u.id) !== currentUserId;
            });
        }

        function render(users) {
            var viewers = othersOnly(users);
            if (!Array.isArray(viewers) || viewers.length === 0) {
                container.classList.add("hidden");
                return;
            }
            container.classList.remove("hidden");

            if (avatars) {
                avatars.innerHTML = "";
                viewers.slice(0, 5).forEach(function (u) {
                    if (u.avatar) {
                        var img = document.createElement("img");
                        img.className =
                            "h-6 w-6 rounded-full ring-2 ring-white dark:ring-gray-900";
                        img.alt = u.name || "User";
                        img.src = u.avatar;
                        avatars.appendChild(img);
                    } else {
                        var span = document.createElement("span");
                        span.className =
                            "flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-medium text-gray-200 ring-2 ring-white bg-gray-950 dark:ring-gray-900";
                        var initials = (u.name || "")
                            .trim()
                            .split(/\s+/)
                            .map(function (p) {
                                return p[0] ? p[0].toUpperCase() : "";
                            })
                            .slice(0, 2)
                            .join("");
                        span.textContent = initials || "?";
                        avatars.appendChild(span);
                    }
                });
                if (viewers.length > 5) {
                    var more = document.createElement("span");
                    more.className = "ml-1 text-xs font-medium";
                    more.textContent = "+" + (viewers.length - 5);
                    avatars.appendChild(more);
                }
            }

            if (names) {
                var display = viewers.map(function (u) {
                    return u.name || "User #" + (u.id || "?");
                });
                names.textContent = display.join(", ");
            }
        }

        var attempts = 0;
        var maxAttempts = 20;
        var retryDelayMs = 250;

        function start() {
            try {
                if (typeof window.Echo === "undefined") {
                    if (attempts++ < maxAttempts)
                        return setTimeout(start, retryDelayMs);
                    return;
                }

                var topic = channel && id ? channel + "." + id : channel;
                if (!topic) return;

                var current = [];
                window.Echo.join(topic)
                    .here(function (users) {
                        current = users;
                        render(current);
                    })
                    .joining(function (user) {
                        current = current.concat([user]);
                        render(current);
                    })
                    .leaving(function (user) {
                        current = current.filter(function (u) {
                            return String(u.id) !== String(user.id);
                        });
                        render(current);
                    })
                    .error(function () {
                        container.classList.add("hidden");
                    });
            } catch (e) {
                // no-op
            }
        }

        if (
            document.readyState === "complete" ||
            document.readyState === "interactive"
        ) {
            start();
        } else {
            document.addEventListener("DOMContentLoaded", start);
        }
    });
}

if (typeof window !== "undefined") {
    window.initViewersBanners = initViewersBanners;
    document.addEventListener("DOMContentLoaded", initViewersBanners);
}
