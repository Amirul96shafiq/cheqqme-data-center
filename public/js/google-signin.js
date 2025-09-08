/**
 * Google Sign-in Popup Handler
 * Handles the popup window for Google OAuth authentication
 */

function openGoogleSignIn() {
    console.log("Opening Google Sign-in popup...");

    // Open Google OAuth in a popup window
    const popup = window.open(
        "/auth/google",
        "googleSignIn",
        "width=460,height=800,scrollbars=yes,resizable=yes,top=100,left=100"
    );

    if (!popup) {
        console.error("Failed to open popup window - popup blocked?");
        showGoogleSignInError(
            "Popup window was blocked. Please allow popups for this site."
        );
        return;
    }

    console.log("Popup opened successfully");

    // Listen for messages from the popup
    const messageListener = function (event) {
        console.log("Received message from popup:", event.data);
        console.log("Message origin:", event.origin);
        console.log("Current origin:", window.location.origin);

        // Verify origin for security
        if (event.origin !== window.location.origin) {
            console.warn("Message from unexpected origin:", event.origin);
            return;
        }

        if (event.data.type === "GOOGLE_SIGNIN_SUCCESS") {
            console.log("Google Sign-in successful!");
            console.log("Redirect URL:", event.data.redirect_url);

            // Close the popup
            popup.close();
            console.log("Popup closed");

            // Remove the event listener
            window.removeEventListener("message", messageListener);

            // Show success message briefly before redirect
            showGoogleSignInSuccess(event.data.message);

            // Redirect to dashboard after a short delay
            setTimeout(() => {
                console.log("Redirecting to:", event.data.redirect_url);
                window.location.href = event.data.redirect_url;
            }, 1000);
        } else if (event.data.type === "GOOGLE_SIGNIN_ERROR") {
            console.error("Google Sign-in error:", event.data.message);
            // Close the popup
            popup.close();

            // Remove the event listener
            window.removeEventListener("message", messageListener);

            // Show error message
            showGoogleSignInError(event.data.message);
        }
    };

    // Add event listener
    window.addEventListener("message", messageListener);

    // Check if popup was closed (either manually or by redirect)
    const checkClosed = setInterval(function () {
        if (popup.closed) {
            console.log("Popup was closed");
            clearInterval(checkClosed);
            window.removeEventListener("message", messageListener);

            // If popup closed without receiving a message, it might have redirected successfully
            // Check if we're still on the login page
            if (window.location.pathname.includes("/login")) {
                console.log(
                    "Popup closed without message, checking if redirect happened"
                );
                // The popup might have redirected the parent window directly
                // No action needed as the redirect should have already happened
            }
        }
    }, 1000);
}

function showGoogleSignInError(message) {
    // Create a temporary error message element
    const errorDiv = document.createElement("div");
    errorDiv.className =
        "fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50";
    errorDiv.textContent = message;

    // Add to page
    document.body.appendChild(errorDiv);

    // Remove after 5 seconds
    setTimeout(function () {
        errorDiv.remove();
    }, 5000);
}

function showGoogleSignInSuccess(message) {
    // Create a temporary success message element
    const successDiv = document.createElement("div");
    successDiv.className =
        "fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50";
    successDiv.textContent = message;

    // Add to page
    document.body.appendChild(successDiv);

    // Remove after 3 seconds
    setTimeout(function () {
        successDiv.remove();
    }, 3000);
}

// Make functions globally available
window.openGoogleSignIn = openGoogleSignIn;
window.showGoogleSignInError = showGoogleSignInError;
window.showGoogleSignInSuccess = showGoogleSignInSuccess;
