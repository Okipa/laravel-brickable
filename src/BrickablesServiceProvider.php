<?php

namespace Okipa\LaravelBrickables;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BrickablesServiceProvider extends ServiceProvider
{
    public function boot(Filesystem $filesystem): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-brickables');
        $this->publishes([
            __DIR__ . '/../config/brickables.php' => config_path('brickables.php'),
        ], 'config');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-brickables'),
        ], 'views');
        $this->publishes([
            __DIR__
            . '/../database/migrations/create_bricks_table.php.stub' => $this->getMigrationFileName($filesystem),
        ], 'migrations');
        $this->declareBladeDirectives();
    }

    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_bricks_table.php');
            })
            ->push($this->app->databasePath("/migrations/{$timestamp}_create_bricks_table.php"))
            ->first();
    }

    protected function declareBladeDirectives(): void
    {
        Blade::directive('brickablesCss', function () {
            return "<?php echo view('laravel-brickables::resources.css')->toHtml(); ?>";
        });
        Blade::directive('brickablesJs', function () {
            return "<?php echo view('laravel-brickables::resources.js')->toHtml(); ?>";
        });
        Blade::directive('brickableResourcesCompute', function () {
            return "<?php echo view('laravel-brickables::resources.compute')->toHtml(); ?>";
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/brickables.php', 'brickables');
        $this->app->bind('Brickables', Brickables::class);
    }
}
