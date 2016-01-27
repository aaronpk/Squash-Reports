<?php
namespace App\Providers;

use DB;
use Illuminate\Support\ServiceProvider;
use Log;

class SQLLoggerProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      if(env('DB_QUERYLOG') == 'true') {
        DB::listen(function($query) {
            Log::info($query->sql . "\n" . json_encode($query->bindings));
        });
      }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}
