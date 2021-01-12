<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $models = ['User'];
        foreach ($models as $key => $model) {
            $this->app->bind("App\Services\\" . $model . "ServiceInterface", "App\Services\\" . $model . "Service");
            $this->app->bind("App\Repositories\\" . $model . "RepositoryInterface", "App\Repositories\\" . $model . "Repository");

        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
