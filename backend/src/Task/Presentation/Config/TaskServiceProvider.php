<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Config;

use Illuminate\Support\ServiceProvider;
use Parser\Task\Application\UseCase\ParseTask;
use Parser\Task\Application\UseCase\ImportTasksFromJson;
use Parser\Task\Domain\TaskParserInterface;
use Parser\Task\Domain\JsonFileReaderInterface;
use Parser\Task\Domain\Validation\TaskValidator;
use Parser\Task\Domain\Validation\TaskRequiredFieldsValidator;
use Parser\Task\Domain\Validation\TaskDataFormatValidator;
use Parser\Task\Infrastructure\Adapter\FreshdeskTaskParserAdapter;
use Parser\Task\Infrastructure\Adapter\FileSystemJsonFileReaderAdapter;
use Parser\Task\Presentation\Console\ParseTaskCommand;
use Parser\Task\Presentation\Console\ImportJsonTaskCommand;

/**
 * @final
 */
final class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');
        
        // Register TaskParserInterface
        $this->app->singleton(
            TaskParserInterface::class,
            function ($app) {
                return new FreshdeskTaskParserAdapter(
                    config('freshdesk.api_key'),
                    config('freshdesk.domain')
                );
            },
        );
        
        // Register JsonFileReaderInterface
        $this->app->singleton(
            JsonFileReaderInterface::class,
            FileSystemJsonFileReaderAdapter::class,
        );

        // Register Validation classes
        $this->app->singleton(TaskRequiredFieldsValidator::class);
        $this->app->singleton(TaskDataFormatValidator::class);
        $this->app->singleton(TaskValidator::class);

        // Register UseCases
        $this->app->singleton(ParseTask::class);
        $this->app->singleton(ImportTasksFromJson::class);
        
        // Configure dependency injection for ParseTask
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
            
        // Configure dependency injection for ImportTasksFromJson
        $this->app->when(ImportTasksFromJson::class)
            ->needs('$jsonFileReader')
            ->give(JsonFileReaderInterface::class);
            
        $this->app->when(ImportTasksFromJson::class)
            ->needs('$taskParser')
            ->give(TaskParserInterface::class);
            
        $this->app->when(ImportTasksFromJson::class)
            ->needs('$taskValidator')
            ->give(TaskValidator::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ParseTaskCommand::class,
                ImportJsonTaskCommand::class,
            ]);
        }
    }
}