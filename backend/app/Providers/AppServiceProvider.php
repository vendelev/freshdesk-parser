<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Parser\Ticket\Application\Command\DownloadTicketsCommand;
use Parser\Ticket\Application\Service\FileStorage;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileStorage::class, function () {
            return new FileStorage();
        });
    }

    public function boot(): void
    {
        $this->commands([
            DownloadTicketsCommand::class,
        ]);
    }
}
