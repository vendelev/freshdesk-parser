<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Illuminate\Console\Command;
use Parser\Task\Application\UseCase\ParseTask;
use Parser\Task\Domain\Request\ParseTaskRequest;

/**
 * @final
 */
final class ParseTaskCommand extends Command
{
    protected $signature = 'task:parse';
    protected $description = 'Parse tasks from Freshdesk';

    public function handle(ParseTask $parseTask): void
    {
        $request = new ParseTaskRequest();
        $parseTask->run($request);
        
        $this->info('ok');
    }
}