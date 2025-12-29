<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Presentation\Config;

use Illuminate\Support\ServiceProvider;
use Parser\Backup\Infrastructure\Adapter\FreshdeskHttpClientAdapter;
use Parser\TicketDetail\Application\UseCase\SaveAllTicketDetailsUseCase;
use Parser\TicketDetail\Domain\FreshdeskDetailClientInterface;
use Parser\TicketDetail\Domain\TicketDetailRepositoryInterface;
use Parser\TicketDetail\Domain\TicketListProviderInterface;
use Parser\TicketDetail\Infrastructure\Adapter\BackupTicketListProviderAdapter;
use Parser\TicketDetail\Infrastructure\Adapter\FreshdeskHttpDetailClientAdapter;
use Parser\TicketDetail\Infrastructure\Repository\FileTicketDetailRepository;
use Parser\TicketDetail\Presentation\Console\SaveTicketDetailsCommand;

final class TicketDetailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');

        // Register interfaces and implementations
        $this->app->bind(TicketDetailRepositoryInterface::class, FileTicketDetailRepository::class);

        $this->app->bind(fn($app): FreshdeskDetailClientInterface => new FreshdeskHttpDetailClientAdapter(
            $app->make(FreshdeskHttpClientAdapter::class)
        ));

        // Register new interfaces and implementations for --all functionality
        $this->app->bind(TicketListProviderInterface::class, BackupTicketListProviderAdapter::class);

        $this->app->when(BackupTicketListProviderAdapter::class)
            ->needs('$backupStoragePath')
            ->giveConfig('freshdesk.backup_storage_path');

        $this->app->bind(SaveAllTicketDetailsUseCase::class, SaveAllTicketDetailsUseCase::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SaveTicketDetailsCommand::class,
            ]);
        }
    }
}
