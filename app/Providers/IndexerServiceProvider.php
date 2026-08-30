<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Storage\OriginalFileStorage;
use App\Services\Pruning\PruningService;
use App\Services\Versioning\VersionManager;
use App\Services\Scanner\IndexerService;
use App\Console\Commands\IndexScanCommand;

class IndexerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // bind storage
        $this->app->singleton(OriginalFileStorage::class, function ($app) {
            return new OriginalFileStorage();
        });

        $this->app->singleton(PruningService::class, function ($app) {
            return new PruningService();
        });

        $this->app->singleton(VersionManager::class, function ($app) {
            return new VersionManager($app->make(OriginalFileStorage::class), $app->make(PruningService::class));
        });

        $this->app->singleton(IndexerService::class, function ($app) {
            return new IndexerService($app->make(VersionManager::class));
        });

        // register command
        $this->commands([
            IndexScanCommand::class,
        ]);
    }

    public function boot()
    {
        // nothing for now
    }
}
