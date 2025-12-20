<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Config;

use Illuminate\Support\ServiceProvider;
use Parser\Task\Application\Service\LoadTicketDetailsService;
use Parser\Task\Application\Service\ParseTasksService;
use Parser\Task\Application\Service\TaskStorageService;
use Parser\Task\Domain\FreshdeskClientInterface;
use Parser\Task\Infrastructure\Adapter\FreshdeskHttpAdapter;
use Parser\Task\Presentation\Console\LoadTicketDetailsCommand;
use Parser\Task\Presentation\Console\ParseFreshdeskCommand;

final class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');

        $this->app->when(FreshdeskHttpAdapter::class)
            ->needs('$apiKey')
            ->giveConfig('freshdesk.api_key');

        $this->app->when(FreshdeskHttpAdapter::class)
            ->needs('$domain')
            ->giveConfig('freshdesk.domain');

        $this->app->singleton(
            FreshdeskClientInterface::class,
            FreshdeskHttpAdapter::class,
        );

        $this->app->when(TaskStorageService::class)
            ->needs('$filesystem')
            ->give('files');

        $this->app->when(TaskStorageService::class)
            ->needs('$basePath')
            ->give('freshdesk');

        $this->app->singleton(TaskStorageService::class);

        $this->app->when(ParseTasksService::class)
            ->needs('$freshdeskClient')
            ->give(FreshdeskClientInterface::class);

        $this->app->when(ParseTasksService::class)
            ->needs('$storageService')
            ->give(TaskStorageService::class);

        $this->app->singleton(ParseTasksService::class);

        $this->app->when(LoadTicketDetailsService::class)
            ->needs('$freshdeskClient')
            ->give(FreshdeskClientInterface::class);

        $this->app->when(LoadTicketDetailsService::class)
            ->needs('$filesystem')
            ->give('files');

        $this->app->when(LoadTicketDetailsService::class)
            ->needs('$basePath')
            ->give('freshdesk');

        $this->app->singleton(LoadTicketDetailsService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ParseFreshdeskCommand::class,
                LoadTicketDetailsCommand::class,
            ]);
        }
    }
}
