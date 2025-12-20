<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Config;

use Illuminate\Support\ServiceProvider;
use Parser\Task\Application\Service\TaskParser;
use Parser\Task\Application\UseCase\ParseTask;
use Parser\Task\Domain\TaskParserInterface;
use Parser\Task\Infrastructure\Adapter\FreshdeskTaskParserAdapter;

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
        $this->app->singleton(TaskParser::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Parser\Task\Presentation\Console\ParseTaskCommand::class,
            ]);
        }
    }
}