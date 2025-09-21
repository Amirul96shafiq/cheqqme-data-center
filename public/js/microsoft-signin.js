/**
 * Microsoft Sign-in Popup Handler
 * Handles the popup window for Microsoft OAuth authentication
 */

function openMicrosoftSignIn() {
    // Open Microsoft OAuth in a popup window
    const popup = window.open(
        "/auth/microsoft",
        "microsoftSignIn",
        "width=460,height=800,scrollbars=yes,resizable=yes,top=" +
            Math.max(0, (screen.height - 800) / 2) +
            ",left=" +
            Math.max(0, (screen.width - 460) / 2)
    );

    if (!popup) {
        showMicrosoftSignInError(
            "Popup window was blocked. Please allow popups for this site."
        );
        return;
    }

    // Listen for messages from the popup
    const messageListener = function (event) {
        // Verify origin for security
        if (event.origin !== window.location.origin) {
            return;
        }

        if (event.data.type === "MICROSOFT_SIGNIN_SUCCESS") {
            // Close the popup and redirect
            popup.close();
            window.removeEventListener("message", messageListener);
            showMicrosoftSignInSuccess(event.data.message);

            setTimeout(() => {
                window.location.href = event.data.redirect_url;
            }, 1000);
        } else if (event.data.type === "MICROSOFT_SIGNIN_ERROR") {
            // Close the popup and show error
            popup.close();
            window.removeEventListener("message", messageListener);
            showMicrosoftSignInError(event.data.message);
        }
    };

    // Add event listener
    window.addEventListener("message", messageListener);

    // Check if popup was closed
    const checkClosed = setInterval(function () {
        if (popup.closed) {
            clearInterval(checkClosed);
            window.removeEventListener("message", messageListener);
        }
    }, 1000);
}

function showMicrosoftSignInError(message) {
    showNotification(message, "error");
}

function showMicrosoftSignInSuccess(message) {
    showNotification(message, "success");
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

// Make functions globally available
window.openMicrosoftSignIn = openMicrosoftSignIn;
window.showMicrosoftSignInError = showMicrosoftSignInError;
window.showMicrosoftSignInSuccess = showMicrosoftSignInSuccess;
