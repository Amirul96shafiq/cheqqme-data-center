<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    // If user is logged in, redirect to admin dashboard
    if (Auth::check()) {
        return redirect('/admin');
    }

    //If user not yet logged in, redirect to login page
    return redirect('/admin/login');
});
