/**
 * Google Sign-in Popup Handler
 * Handles the popup window for Google OAuth authentication
 */

function openGoogleSignIn() {
    
    // Open Google OAuth in a popup window
    const popup = window.open(
        "/auth/google",
        "googleSignIn",
        "width=460,height=800,scrollbars=yes,resizable=yes,top=" +
            Math.max(0, (screen.height - 800) / 2) +
            ",left=" +
            Math.max(0, (screen.width - 460) / 2)
    );

    if (!popup) {
        showGoogleSignInError(
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

        if (event.data.type === "GOOGLE_SIGNIN_SUCCESS") {
            
            // Clear timeout and cleanup
            if (messageListener.timeout) {
                clearTimeout(messageListener.timeout);
            }

            window.removeEventListener("message", messageListener);
            showGoogleSignInSuccess(event.data.message);

            setTimeout(() => {
                window.location.href = event.data.redirect_url;
            }, 1000);
        } else if (event.data.type === "GOOGLE_SIGNIN_ERROR") {
            
            // Clear timeout and cleanup
            if (messageListener.timeout) {
                clearTimeout(messageListener.timeout);
            }

            window.removeEventListener("message", messageListener);
            showGoogleSignInError(event.data.message);
        }
    };

    // Add event listener
    window.addEventListener("message", messageListener);

    // Set a timeout to clean up if no response is received
    const timeout = setTimeout(function () {
        window.removeEventListener("message", messageListener);
        showGoogleSignInError("Sign-in timed out. Please try again.");
    }, 300000); // 5 minutes timeout

    // Store timeout reference for cleanup
    messageListener.timeout = timeout;
}

function showGoogleSignInError(message) {
    
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

function showGoogleSignInSuccess(message) {
    
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
window.openGoogleSignIn = openGoogleSignIn;
window.showGoogleSignInError = showGoogleSignInError;
window.showGoogleSignInSuccess = showGoogleSignInSuccess;
