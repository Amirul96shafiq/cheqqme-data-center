<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
  /**
   * Where to redirect users after login.
   */
  public const HOME = '/admin/dashboard';

  /**
   * Define your route model bindings, pattern filters, etc.
   */
public function boot(): void
  {
    parent::boot();

    // Optional: You can define route groups or bindings here if needed
  }
}
