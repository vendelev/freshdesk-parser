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
        $this->app->singleton(
            TaskParserInterface::class,
            FreshdeskTaskParserAdapter::class,
        );

        $this->app->singleton(ParseTask::class);
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