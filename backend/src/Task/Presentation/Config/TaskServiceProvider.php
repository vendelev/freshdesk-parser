<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Config;

use Illuminate\Support\ServiceProvider;
use Parser\Task\Application\UseCase\ParseTask;
use Parser\Task\Domain\TaskParserInterface;
use Parser\Task\Infrastructure\Adapter\FreshdeskTaskParserAdapter;
use Parser\Task\Presentation\Console\ParseTaskCommand;

/**
 * @final
 */
final class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');
        
        $this->app->singleton(
            TaskParserInterface::class,
            function ($app) {
                return new FreshdeskTaskParserAdapter(
                    config('freshdesk.api_key'),
                    config('freshdesk.domain')
                );
            },
        );

        $this->app->singleton(ParseTask::class);
        
        $this->app->when(ParseTask::class)
            ->needs('$freshdeskApiKey')
            ->giveConfig('freshdesk.api_key');
            
        $this->app->when(ParseTask::class)
            ->needs('$freshdeskDomain')
            ->giveConfig('freshdesk.domain');
            
        $this->app->when(FreshdeskTaskParserAdapter::class)
            ->needs('$freshdeskApiKey')
            ->giveConfig('freshdesk.api_key');
            
        $this->app->when(FreshdeskTaskParserAdapter::class)
            ->needs('$freshdeskDomain')
            ->giveConfig('freshdesk.domain');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ParseTaskCommand::class,
            ]);
        }
    }
}