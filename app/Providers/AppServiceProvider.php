<?php

namespace App\Providers;

use App\Models\AuthenticationLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

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
        //Schedule::command('authentication-log:purge')->at('22:49');
        Schedule::command('model:prune', ['--model' => AuthenticationLog::class])->at('23:19');
        Model::unguard();
    }
}
