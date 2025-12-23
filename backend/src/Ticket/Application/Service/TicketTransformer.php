<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Service;

use DateTimeImmutable;
use Parser\Ticket\Application\Dto\TicketApiDto;
use Parser\Ticket\Domain\Entity\Ticket;

/**
 * Сервис для трансформации API данных в доменную сущность задачи.
 */
final readonly class TicketTransformer
{
    /**
     * Преобразует DTO из Freshdesk API в доменную сущность задачи.
     *
     * @param TicketApiDto $dto DTO с данными задачи из Freshdesk API
     *
     * @return Ticket Доменная сущность задачи
     *
     * @throws \DateMalformedStringException Если формат даты некорректен
     */
    public function transformFromApi(TicketApiDto $dto): Ticket
    {
        return new Ticket(
            freshdeskId: $dto->id,
            subject: $dto->subject,
            description: $dto->description,
            descriptionText: $dto->descriptionText,
            type: $dto->type,
            status: $dto->status,
            priority: $dto->priority,
            source: $dto->source,
            requesterId: $dto->requesterId,
            responderId: $dto->responderId,
            companyId: $dto->companyId,
            groupId: $dto->groupId,
            productId: $dto->productId,
            email: $dto->email,
            name: $dto->name,
            phone: $dto->phone,
            facebookId: $dto->facebookId,
            twitterId: $dto->twitterId,
            ccEmails: $dto->ccEmails,
            toEmails: $dto->toEmails,
            fwdEmails: $dto->fwdEmails,
            replyCcEmails: $dto->replyCcEmails,
            emailConfigId: $dto->emailConfigId,
            isEscalated: $dto->isEscalated,
            spam: $dto->spam,
            deleted: $dto->deleted,
            customFields: $dto->customFields,
            tags: $dto->tags,
            attachments: $dto->attachments,
            dueBy: $dto->dueBy ? new DateTimeImmutable($dto->dueBy) : null,
            frDueBy: $dto->frDueBy ? new DateTimeImmutable($dto->frDueBy) : null,
            isOverdue: $dto->isOverdue,
            frEscalated: $dto->frEscalated,
            slaPolicyId: $dto->slaPolicyId,
            createdAt: new DateTimeImmutable($dto->createdAt),
            updatedAt: new DateTimeImmutable($dto->updatedAt),
            syncedAt: new DateTimeImmutable(),
        );
    }
}
