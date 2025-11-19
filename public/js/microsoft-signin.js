/**
 * Microsoft Sign-in Popup Handler
 * Handles the popup window for Microsoft OAuth authentication
 */

function openMicrosoftSignIn() {
    console.log("Microsoft signin: openMicrosoftSignIn called");

    // Open Microsoft OAuth in a popup window
    const popupUrl = "/auth/microsoft";
    console.log("Microsoft signin: Opening popup to:", popupUrl);
    const popup = window.open(
        "/auth/microsoft",
        "microsoftSignIn",
        "width=460,height=800,scrollbars=yes,resizable=yes,top=" +
            Math.max(0, (screen.height - 800) / 2) +
            ",left=" +
            Math.max(0, (screen.width - 460) / 2)
    );

    console.log("Microsoft signin: Popup object:", popup);

    if (!popup) {
        console.error("Microsoft signin: Popup blocked or failed to open!");
        showMicrosoftSignInError(
            "Popup window was blocked. Please allow popups for this site."
        );
        return;
    }

    console.log(
        "Microsoft signin: Popup opened successfully, setting up listeners..."
    );

    // Listen for messages from the popup
    const messageListener = function (event) {
        // Verify origin for security
        if (event.origin !== window.location.origin) {
            return;
        }

        if (event.data.type === "MICROSOFT_SIGNIN_SUCCESS") {
            // Clear timeout and cleanup
            if (messageListener.timeout) {
                clearTimeout(messageListener.timeout);
            }

            window.removeEventListener("message", messageListener);
            showMicrosoftSignInSuccess(event.data.message);

            setTimeout(() => {
                window.location.href = event.data.redirect_url;
            }, 1000);
        } else if (event.data.type === "MICROSOFT_SIGNIN_ERROR") {
            // Clear timeout and cleanup
            if (messageListener.timeout) {
                clearTimeout(messageListener.timeout);
            }

            window.removeEventListener("message", messageListener);
            showMicrosoftSignInError(event.data.message);
        }
    };

    // Add event listener
    window.addEventListener("message", messageListener);

    // Set a timeout to clean up if no response is received
    const timeout = setTimeout(function () {
        window.removeEventListener("message", messageListener);
        showMicrosoftSignInError("Sign-in timed out. Please try again.");
    }, 300000); // 5 minutes timeout

    // Store timeout reference for cleanup
    messageListener.timeout = timeout;
}

function showMicrosoftSignInError(message) {
    // Use custom notification system if available, fallback to basic notification
    if (typeof showErrorNotification === "function") {
        showErrorNotification(message);
    } else if (typeof showNotification === "function") {
        showNotification("error", message);
    } else {
        // Fallback to basic notification
        showBasicNotification(message, "error");
    }
}

function showMicrosoftSignInSuccess(message) {
    // Use custom notification system if available, fallback to basic notification
    if (typeof showSuccessNotification === "function") {
        showSuccessNotification(message);
    } else if (typeof showNotification === "function") {
        showNotification("success", message);
    } else {
        // Fallback to basic notification
        showBasicNotification(message, "success");
    }
}

function showNotification(message, type) {
    const notification = document.createElement("div");
    const bgColor = type === "error" ? "bg-red-500" : "bg-green-500";
    const duration = type === "error" ? 5000 : 3000;

    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => notification.remove(), duration);
}

// Show basic notification
function showBasicNotification(message, type) {
    const notification = document.createElement("div");
    const bgColor = type === "error" ? "bg-red-500" : "bg-green-500";
    const duration = type === "error" ? 5000 : 3000;

    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => notification.remove(), duration);
}

// Make functions globally available
window.openMicrosoftSignIn = openMicrosoftSignIn;
window.showMicrosoftSignInError = showMicrosoftSignInError;
window.showMicrosoftSignInSuccess = showMicrosoftSignInSuccess;
