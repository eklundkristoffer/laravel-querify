<?php

namespace Eklundkristoffer\Querify;

use Illuminate\Support\ServiceProvider;

class QuerifyServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources' => resource_path('querify'),
        ], 'laravel-querify');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        require __DIR__.'/macros.php';
    }
}
