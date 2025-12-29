<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain;

interface TicketListProviderInterface
{
    /**
     * @return iterable<int> Итератор ID задач
     */
    public function getTicketIds(): iterable;
}
