<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      Blade::directive('entrydate', function($entry) {
        return '<?php
          $date = new DateTime($entry->created_at);
          $date->setTimeZone(new DateTimeZone($entry->timezone));
          echo $date->format(\'M j g:ia\'); ?>';
      });

      Blade::directive('entrytime', function($entry) {
        return '<?php
          $date = new DateTime($entry->created_at);
          $date->setTimeZone(new DateTimeZone($entry->timezone));
          echo $date->format(\'g:ia\'); ?>';
      });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
