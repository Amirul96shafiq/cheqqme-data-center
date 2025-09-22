<?php

return [
    'title' => 'Sign in',

    'actions' => [
        'login' => 'Login',
        'forgotPassword' => 'Forgot Password?',
        'googleSignin' => 'Sign in with Google',
        'microsoftSignin' => 'Sign in with Microsoft',
    ],

    'form' => [
        'email' => 'Username / Email',
        'password' => 'Password',
        'remember' => 'Remember Me',
        'or' => 'Or',
    ],

    'errors' => [
        'googleAccountNotFound' => 'Failed to login. The selected Google Account does not exist in the system.',
        'googleAuthenticationFailed' => 'Failed to authenticate with Google. Please try again.',
        'googleAccountConnected' => 'Google account connected successfully!',
        'googleSigninSuccess' => 'Successfully signed in with Google!',
    ],
];
