<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Google Sign-in</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f8fafc;
        }
        .loading {
            text-align: center;
            color: #6b7280;
        }
        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading">
        <div class="spinner"></div>
        <p>Signing you in...</p>
    </div>

    <script>
        // Get the URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const code = urlParams.get('code');
        const error = urlParams.get('error');

        // Helper function to close popup with fallback message
        function closePopupWithFallback(message, isError = false) {
            window.close();
            
            setTimeout(() => {
                if (!window.closed) {
                    const icon = isError ? 'Authentication failed' : 'Successfully signed in! Redirecting...';
                    document.body.innerHTML = `<div class="loading"><p>${icon}</p><p>You can close this window.</p></div>`;
                }
            }, 500);
        }

        // Helper function to send error message to parent
        function sendErrorToParent(message) {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    type: 'GOOGLE_SIGNIN_ERROR',
                    message: message
                }, window.location.origin);
            }
        }

        if (error) {
            // Handle OAuth error from Google
            sendErrorToParent('Authentication was cancelled or failed.');
            closePopupWithFallback('Authentication was cancelled or failed.', true);
        } else if (code) {
            // Process OAuth code
            fetch('/auth/google/callback?' + window.location.search, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
            .then(async response => {
                if (!response.ok) {
                    // Try to parse error response
                    try {
                        const errorData = await response.json();
                        if (errorData.message) {
                            sendErrorToParent(errorData.message);
                            closePopupWithFallback(errorData.message, true);
                            return;
                        }
                    } catch (parseError) {
                        // Fallback if JSON parsing fails
                    }
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // SUCCESS: Redirect parent window and close popup
                    if (window.opener && !window.opener.closed) {
                        window.opener.location.href = data.redirect_url;
                    }
                    closePopupWithFallback('Successfully signed in!', false);
                } else {
                    // ERROR: Send error message to parent
                    sendErrorToParent(data.message);
                    closePopupWithFallback(data.message, true);
                }
            })
            .catch(error => {
                console.error('Error processing OAuth:', error);
                sendErrorToParent('Failed to authenticate with Google. Please try again.');
                closePopupWithFallback('Failed to authenticate with Google. Please try again.', true);
            });
        } else {
            // No code or error parameter - invalid response
            sendErrorToParent('Invalid authentication response.');
            closePopupWithFallback('Invalid authentication response.', true);
        }
    </script>
</body>
</html>
