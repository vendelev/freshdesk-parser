<?php

declare(strict_types=1);

namespace Parser\Backup\Presentation\Config;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Parser\Backup\Domain\BackupStorageInterface;
use Parser\Backup\Domain\FreshdeskClientInterface;
use Parser\Backup\Infrastructure\Adapter\FileBackupStorageAdapter;
use Parser\Backup\Infrastructure\Adapter\FreshdeskHttpClientAdapter;
use Parser\Backup\Presentation\Console\BackupTicketsCommand;

final class BackupServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');

        $this->app->bind(
            FreshdeskClientInterface::class,
            FreshdeskHttpClientAdapter::class,
        );

        $this->app->bind(
            BackupStorageInterface::class,
            FileBackupStorageAdapter::class,
        );

        $this->app->when(FreshdeskHttpClientAdapter::class)
            ->needs(Client::class)
            ->give(fn (): \GuzzleHttp\Client => new Client());

        $this->app->when(FreshdeskHttpClientAdapter::class)
            ->needs('$freshdeskApiKey')
            ->giveConfig('freshdesk.api_key');

        $this->app->when(FreshdeskHttpClientAdapter::class)
            ->needs('$freshdeskDomain')
            ->giveConfig('freshdesk.domain');

        $this->app->when(FileBackupStorageAdapter::class)
            ->needs('$backupStoragePath')
            ->giveConfig('freshdesk.backup_storage_path');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                BackupTicketsCommand::class,
            ]);
        }
    }
}
