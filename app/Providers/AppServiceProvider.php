<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share user data from session with all views
        View::composer('*', function ($view) {
            $userId = session('user_id');
            $user = null;
            
            if ($userId) {
                $user = DB::table('users')->where('id', $userId)->first();
            }
            
            $view->with('currentUser', $user);
        });
    }
}
