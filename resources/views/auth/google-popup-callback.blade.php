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
        const state = urlParams.get('state');

        // Debug logging
        console.log('Popup callback page loaded');
        console.log('Current URL:', window.location.href);
        console.log('Code parameter:', code);
        console.log('Error parameter:', error);
        console.log('State parameter:', state);
        console.log('All URL parameters:', Object.fromEntries(urlParams));

        if (error) {
            // Handle OAuth error
            console.log('OAuth error detected:', error);
            window.opener.postMessage({
                type: 'GOOGLE_SIGNIN_ERROR',
                message: 'Authentication was cancelled or failed.'
            }, window.location.origin);
            window.close();
        } else if (code) {
            // Make a request to our callback endpoint to process the OAuth code
            console.log('OAuth code found, making request to process it');
            fetch('/auth/google/callback?' + window.location.search, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Authentication response:', data);
                
                if (data.success) {
                    // SUCCESS: Immediately redirect parent window and close popup
                    console.log('Authentication successful, redirecting parent window');
                    
                    if (window.opener && !window.opener.closed) {
                        // Redirect the parent window immediately
                        window.opener.location.href = data.redirect_url;
                        console.log('Parent window redirected to:', data.redirect_url);
                    }
                    
                    // Close this popup window immediately
                    window.close();
                    
                    // Fallback: if window doesn't close, show success message
                    setTimeout(() => {
                        if (!window.closed) {
                            document.body.innerHTML = '<div class="loading"><p>Successfully signed in! Redirecting...</p><p>You can close this window.</p></div>';
                        }
                    }, 500);
                    
                } else {
                    // ERROR: Send error message to parent
                    console.log('Authentication failed, sending error message');
                    
                    if (window.opener && !window.opener.closed) {
                        window.opener.postMessage({
                            type: 'GOOGLE_SIGNIN_ERROR',
                            message: data.message
                        }, window.location.origin);
                    }
                    
                    // Close popup window
                    window.close();
                    
                    // Fallback: if window doesn't close, show error message
                    setTimeout(() => {
                        if (!window.closed) {
                            document.body.innerHTML = '<div class="loading"><p>Authentication failed: ' + data.message + '</p><p>You can close this window.</p></div>';
                        }
                    }, 500);
                }
            })
            .catch(error => {
                console.error('Error processing OAuth:', error);
                
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        type: 'GOOGLE_SIGNIN_ERROR',
                        message: 'Failed to authenticate with Google. Please try again.'
                    }, window.location.origin);
                }
                
                // Close popup window immediately
                window.close();
                
                // Fallback: if window doesn't close, show error message
                setTimeout(() => {
                    if (!window.closed) {
                        document.body.innerHTML = '<div class="loading"><p>Authentication failed. Please try again.</p><p>You can close this window.</p></div>';
                    }
                }, 500);
            });
        } else {
            // No code or error parameter - this should not happen in normal flow
            console.log('No OAuth code or error found in URL:', window.location.href);
            console.log('This indicates Google did not redirect properly');
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    type: 'GOOGLE_SIGNIN_ERROR',
                    message: 'Invalid authentication response.'
                }, window.location.origin);
            }
            
            // Close popup window immediately
            window.close();
            
            // Fallback: if window doesn't close, show error message
            setTimeout(() => {
                if (!window.closed) {
                    document.body.innerHTML = '<div class="loading"><p>Invalid authentication response.</p><p>You can close this window.</p></div>';
                }
            }, 500);
        }
    </script>
</body>
</html>
