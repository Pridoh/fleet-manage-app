<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Excel as ExcelClass;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Excel facade alias
        $this->app->bind('excel', function($app) {
            return new ExcelClass($app['phpexcel'], $app['excel.writer'], $app['excel.reader']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Gate untuk mengontrol akses ke menu pemesanan kendaraan
        Gate::define('access-vehicle-requests', function ($user) {
            return $user->role !== 'approver';
        });
        
        // Gate untuk mengontrol akses ke menu log sistem
        Gate::define('access-system-logs', function ($user) {
            return $user->isAdmin();
        });
        
        // Gate untuk mengontrol akses admin
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });
        
        // Gate untuk mengontrol akses ke fitur monitoring kendaraan
        Gate::define('access-vehicle-monitoring', function ($user) {
            return $user->isAdmin();
        });
    }
}
