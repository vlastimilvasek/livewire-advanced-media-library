<?php

namespace VlastimilVasek\LivewireAdvancedMediaLibrary;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use VlastimilVasek\LivewireAdvancedMediaLibrary\Console\InstallCommand;
use VlastimilVasek\LivewireAdvancedMediaLibrary\Livewire\AdvancedMediaLibrary;

class LivewireAdvancedMediaLibraryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-advanced-media-library');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'livewire-advanced-media-library');

        $this->publishes([
            __DIR__.'/../config/livewire-advanced-media-library.php' => config_path('livewire-advanced-media-library.php'),
        ], 'livewire-advanced-media-library-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/livewire-advanced-media-library'),
        ], 'livewire-advanced-media-library-views');

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/livewire-advanced-media-library'),
        ], 'livewire-advanced-media-library-translations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }

        Livewire::component('advanced-media-library', AdvancedMediaLibrary::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/livewire-advanced-media-library.php',
            'livewire-advanced-media-library'
        );
    }
}
