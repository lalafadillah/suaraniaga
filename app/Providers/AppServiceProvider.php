<?php

namespace App\Providers;

use App\Support\BladeJs;
use Illuminate\Support\Facades\Blade;
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
        // Blade mengompilasi tag komponen sebelum direksi lain, sehingga @js
        // di dalam atribut komponen tidak pernah terkompilasi. String ini
        // disiapkan lebih awal supaya tombol suara bekerja di semua tampilan.
        Blade::prepareStringsForCompilationUsing(
            fn (string $template): string => BladeJs::ganti($template)
        );
    }
}
