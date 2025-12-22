<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Config;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Parser\Task\Application\Service\FreshdeskTaskParser;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\TaskParserInterface;
use Parser\Task\Infrastructure\Adapter\FreshdeskApiClient;
use Parser\Task\Presentation\Console\GetTaskByIdCommand;
use Parser\Task\Presentation\Console\ParseTasksCommand;
use Illuminate\Support\ServiceProvider;

final class TaskServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');

        // Register dependencies
        $this->app->bind(FreshdeskApiClientInterface::class, FreshdeskApiClient::class);
        $this->app->when(FreshdeskApiClient::class)
            ->needs('$freshdeskDomain')
            ->giveConfig('freshdesk.domain');

        $this->app->when(FreshdeskApiClient::class)
            ->needs('$freshdeskApiKey')
            ->giveConfig('freshdesk.api_key');

        $this->app->bind(TaskParserInterface::class, FreshdeskTaskParser::class);
        $this->app->when(FreshdeskTaskParser::class)
            ->needs('$storagePath')
            ->giveConfig('freshdesk.storage_path');

        $this->app->bind(fn(): ClientInterface => new Client());

        // Register console commands
        $this->commands([
            ParseTasksCommand::class,
            GetTaskByIdCommand::class,
        ]);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__ . '/freshdesk.php' => config_path('freshdesk.php'),
            ], 'freshdesk-config');
        }
    }
}
