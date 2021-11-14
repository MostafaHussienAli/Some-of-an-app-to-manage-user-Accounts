<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ExportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Sheet::macro('freezePane', function (Sheet $sheet, $pane) {
            $sheet->getDelegate()->getActiveSheet()->freezePane($pane);
        });
    }
}
