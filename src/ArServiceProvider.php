<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 23.03.15
 * Time: 15:07
 */

namespace Heonozis\AR;

use Illuminate\Support\ServiceProvider;

class ArServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../db/migrations' => base_path('database/migrations'),
        ]);
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