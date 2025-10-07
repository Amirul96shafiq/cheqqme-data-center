<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Spotify Sign-in</title>
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
            border-top: 3px solid #1db954;
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
        <p>Connecting to Spotify...</p>
        <div style="margin-top: 20px;">
            <button onclick="window.close()" style="padding: 8px 16px; background: #1db954; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Close Popup
            </button>
        </div>
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
                    const icon = isError ? 'Connection failed' : 'Successfully connected! Redirecting...';
                    document.body.innerHTML = `<div class="loading"><p>${icon}</p><p>You can close this window.</p></div>`;
                }
            }, 500);
        }

        // Helper function to send error message to parent
        function sendErrorToParent(message) {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    type: 'SPOTIFY_SIGNIN_ERROR',
                    message: message
                }, window.location.origin);
            }
        }

        if (error) {
            // Handle OAuth error from Spotify
            sendErrorToParent('Authentication was cancelled or failed.');
            closePopupWithFallback('Authentication was cancelled or failed.', true);
        } else if (code) {
            // Process OAuth code - the callback will redirect us to this popup callback view
            // with success/error data in session, so we'll handle it below
            console.log('OAuth code received, processing...');
        } else {
            // No code or error parameter - invalid response
            sendErrorToParent('Invalid authentication response.');
            closePopupWithFallback('Invalid authentication response.', true);
        }

        // Debug passed data
        console.log('OAuth data available:', {
            success: @json($oauth_success ?? false),
            message: @json($oauth_message ?? null),
            redirect_url: @json($oauth_redirect_url ?? null),
            error: @json($oauth_error ?? null)
        });

        // Handle OAuth data from successful callback
        @if(isset($oauth_success) && $oauth_success)
            console.log('Spotify OAuth success detected in popup');
            
            // Update the UI to show success
            document.querySelector('.loading').innerHTML = `
                <div style="color: #1db954; font-size: 24px; margin-bottom: 16px;">✅</div>
                <p style="color: #1db954; font-weight: bold;">Spotify Connected Successfully!</p>
                <p style="color: #666; font-size: 14px;">Check the console for debugging info</p>
                <div style="margin-top: 20px;">
                    <button onclick="window.close()" style="padding: 8px 16px; background: #1db954; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Close Popup
                    </button>
                </div>
            `;
            
            // Success case - redirect parent window directly
            if (window.opener && !window.opener.closed) {
                console.log('Redirecting parent window to profile page');
                window.opener.location.href = '{{ $oauth_redirect_url }}';
                
                // Close popup after redirect
                setTimeout(() => {
                    console.log('Closing popup after redirect');
                    window.close();
                }, 1000);
            } else {
                console.log('No opener window, closing popup directly');
                window.close();
            }
        @elseif(isset($oauth_error) && $oauth_error)
            console.log('Spotify OAuth error detected in popup');
            
            // Update the UI to show error
            document.querySelector('.loading').innerHTML = `
                <div style="color: #e74c3c; font-size: 24px; margin-bottom: 16px;">❌</div>
                <p style="color: #e74c3c; font-weight: bold;">Spotify Connection Failed</p>
                <p style="color: #666; font-size: 14px;">{{ $oauth_error }}</p>
                <p style="color: #666; font-size: 12px;">Check the console for debugging info</p>
                <div style="margin-top: 20px;">
                    <button onclick="window.close()" style="padding: 8px 16px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Close Popup
                    </button>
                </div>
            `;
            
            // Error case - send error message to parent
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    success: false,
                    message: '{{ $oauth_error }}'
                }, window.location.origin);
                
                // Give the parent window time to process the message before closing
                setTimeout(() => {
                    // closePopupWithFallback('{{ $oauth_error }}', true);
                    console.log('Auto-close disabled for debugging (error case)');
                }, 500);
            } else {
                closePopupWithFallback('{{ $oauth_error }}', true);
            }
        @endif
    </script>
</body>
</html>
