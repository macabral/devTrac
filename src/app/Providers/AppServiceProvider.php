<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use ProtoneMedia\Splade\Components\Form\Input;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Input::defaultDateFormat('d/m/Y');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // verifica se a pasta 'public/uploads' existe
        $pasta = public_path('uploads');
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }
    }
}
