<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Config;

use Illuminate\Support\ServiceProvider;
use Parser\Task\Application\Service\ParseTasksService;
use Parser\Task\Application\Service\TaskStorageService;
use Parser\Task\Domain\FreshdeskClientInterface;
use Parser\Task\Infrastructure\Adapter\FreshdeskHttpAdapter;
use Parser\Task\Presentation\Console\ParseFreshdeskCommand;

final class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            FreshdeskClientInterface::class,
            static fn () => new FreshdeskHttpAdapter(
                apiKey: (string) config('freshdesk.api_key'),
                domain: (string) config('freshdesk.domain'),
            ),
        );

        $this->app->singleton(
            TaskStorageService::class,
            static fn ($app) => new TaskStorageService(
                filesystem: $app->make('files'),
                basePath: 'freshdesk',
            ),
        );

        $this->app->singleton(
            ParseTasksService::class,
            static fn ($app) => new ParseTasksService(
                freshdeskClient: $app->make(FreshdeskClientInterface::class),
                storageService: $app->make(TaskStorageService::class),
            ),
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ParseFreshdeskCommand::class,
            ]);
        }
    }
}
