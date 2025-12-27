<?php

declare(strict_types=1);

namespace Tests\Suite\Backup\Application\UseCase;

use Parser\Backup\Application\UseCase\CreateBackupUseCase;
use Parser\Backup\Domain\BackupStorageInterface;
use Parser\Backup\Domain\Exception\FreshdeskApiConnectionException;
use Parser\Backup\Domain\Exception\FreshdeskApiUnauthorizedException;
use Parser\Backup\Domain\Exception\BackupOperationFailedException;
use Parser\Backup\Domain\FreshdeskClientInterface;
use Tests\TestCase;

final class CreateBackupUseCaseTest extends TestCase
{
    /**
     * Успешное создание бекапа с одной страницей задач
     */
    public function testSuccessfulBackupCreationWithSinglePage(): void
    {
        // Подготовка моков
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
        $mockBackupStorage = $this->createMock(BackupStorageInterface::class);

        // Генератор с одной страницей задач (50 задач)
        $ticketsJson = json_encode(array_fill(0, 50, ['id' => 1, 'subject' => 'Test Ticket']));

        $generator = (static function () use ($ticketsJson) {
            yield $ticketsJson;
        })();

        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willReturn($generator);

        $mockBackupStorage
            ->expects(self::once())
            ->method('savePage')
            ->with($ticketsJson, self::anything(), 1);

        $mockBackupStorage
            ->expects(self::once())
            ->method('saveMetadata')
            ->with(
                self::callback(static fn(array $metadata): bool => isset($metadata['created_at']) &&
                    isset($metadata['total_tickets']) &&
                    $metadata['total_tickets'] === 50 &&
                    isset($metadata['total_pages'])),
                self::anything()
            );

        // Создание UseCase с моками
        $useCase = new CreateBackupUseCase($mockFreshdeskClient, $mockBackupStorage);

        // Выполнение
        $response = $useCase->execute();

        // Проверки
        self::assertSame('success', $response->status);
        self::assertSame('Бекап создан успешно', $response->message);
        self::assertSame(50, $response->totalTickets);
        self::assertStringStartsWith('backup_', $response->filePath ?? '');
        self::assertNull($response->errorDetails);
    }

    /**
     * Успешное создание бекапа с несколькими страницами задач
     */
    public function testSuccessfulBackupCreationWithMultiplePages(): void
    {
        // Подготовка моков
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
        $mockBackupStorage = $this->createMock(BackupStorageInterface::class);

        // Генератор с тремя страницами (100 + 100 + 50 = 250 задач)
        $page1 = json_encode(array_fill(0, 100, ['id' => 1, 'subject' => 'Test']));
        $page2 = json_encode(array_fill(0, 100, ['id' => 2, 'subject' => 'Test']));
        $page3 = json_encode(array_fill(0, 50, ['id' => 3, 'subject' => 'Test']));

        $generator = (static function () use ($page1, $page2, $page3) {
            yield $page1;
            yield $page2;
            yield $page3;
        })();

        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willReturn($generator);

        $mockBackupStorage
            ->expects(self::exactly(3))
            ->method('savePage');

        $mockBackupStorage
            ->expects(self::once())
            ->method('saveMetadata')
            ->with(
                self::callback(
                    static fn(array $metadata): bool =>
                        $metadata['total_tickets'] === 250 && $metadata['total_pages'] === 3
                ),
                self::anything()
            );

        // Создание UseCase с моками
        $useCase = new CreateBackupUseCase($mockFreshdeskClient, $mockBackupStorage);

        // Выполнение
        $response = $useCase->execute();

        // Проверки
        self::assertSame('success', $response->status);
        self::assertSame(250, $response->totalTickets);
    }

    /**
     * Ошибка подключения к API
     */
    public function testBackupFailureOnApiConnectionError(): void
    {
        // Подготовка моков
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
        $mockBackupStorage = $this->createMock(BackupStorageInterface::class);

        // Генератор выбрасывает исключение подключения
        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willThrowException(new FreshdeskApiConnectionException('Ошибка подключения'));

        $mockBackupStorage
            ->expects(self::never())
            ->method('savePage');

        $mockBackupStorage
            ->expects(self::never())
            ->method('saveMetadata');

        // Создание UseCase с моками
        $useCase = new CreateBackupUseCase($mockFreshdeskClient, $mockBackupStorage);

        // Выполнение
        $response = $useCase->execute();

        // Проверки
        self::assertSame('error', $response->status);
        self::assertSame('Ошибка подключения к Freshdesk API', $response->message);
        self::assertStringContainsString('Ошибка подключения', (string)$response->errorDetails);
    }

    /**
     * Ошибка авторизации
     */
    public function testBackupFailureOnUnauthorizedException(): void
    {
        // Подготовка моков
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
        $mockBackupStorage = $this->createMock(BackupStorageInterface::class);

        // Генератор выбрасывает исключение авторизации
        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willThrowException(new FreshdeskApiUnauthorizedException('Неверный API ключ'));

        // Создание UseCase с моками
        $useCase = new CreateBackupUseCase($mockFreshdeskClient, $mockBackupStorage);

        // Выполнение
        $response = $useCase->execute();

        // Проверки
        self::assertSame('error', $response->status);
        self::assertSame('Ошибка подключения к Freshdesk API', $response->message);
    }

    /**
     * Ошибка записи на диск при сохранении страницы
     */
    public function testBackupFailureOnStorageWriteError(): void
    {
        // Подготовка моков
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
        $mockBackupStorage = $this->createMock(BackupStorageInterface::class);

        // Генератор с одной страницей
        $ticketsJsonData = json_encode(array_fill(0, 50, ['id' => 1]));
        self::assertIsString($ticketsJsonData);
        $ticketsJson = $ticketsJsonData;

        $generator = (static function () use ($ticketsJson) {
            yield $ticketsJson;
        })();

        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willReturn($generator);

        // Storage выбрасывает исключение при попытке сохранить
        $mockBackupStorage
            ->expects(self::once())
            ->method('savePage')
            ->willThrowException(new BackupOperationFailedException('Ошибка записи'));

        // Создание UseCase с моками
        $useCase = new CreateBackupUseCase($mockFreshdeskClient, $mockBackupStorage);

        // Выполнение
        $response = $useCase->execute();

        // Проверки
        self::assertSame('error', $response->status);
        self::assertSame('Ошибка записи на диск', $response->message);
        self::assertStringContainsString('Ошибка записи', $response->errorDetails ?? '');
    }

    /**
     * Ошибка записи на диск при сохранении метаданных
     */
    public function testBackupFailureOnMetadataWriteError(): void
    {
        // Подготовка моков
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
        $mockBackupStorage = $this->createMock(BackupStorageInterface::class);

        // Генератор с одной страницей
        $ticketsJson = json_encode(array_fill(0, 50, ['id' => 1]));

        $generator = (static function () use ($ticketsJson) {
            yield $ticketsJson;
        })();

        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willReturn($generator);

        $mockBackupStorage
            ->expects(self::once())
            ->method('savePage');

        // Storage выбрасывает исключение при попытке сохранить метаданные
        $mockBackupStorage
            ->expects(self::once())
            ->method('saveMetadata')
            ->willThrowException(new BackupOperationFailedException('Ошибка записи метаданных'));

        // Создание UseCase с моками
        $useCase = new CreateBackupUseCase($mockFreshdeskClient, $mockBackupStorage);

        // Выполнение
        $response = $useCase->execute();

        // Проверки
        self::assertSame('error', $response->status);
        self::assertSame('Ошибка записи на диск', $response->message);
    }

    /**
     * Неизвестная ошибка
     */
    public function testBackupFailureOnUnknownException(): void
    {
        // Подготовка моков
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
        $mockBackupStorage = $this->createMock(BackupStorageInterface::class);

        // Генератор выбрасывает неожиданное исключение
        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willThrowException(new \RuntimeException('Неожиданная ошибка'));

        // Создание UseCase с моками
        $useCase = new CreateBackupUseCase($mockFreshdeskClient, $mockBackupStorage);

        // Выполнение
        $response = $useCase->execute();

        // Проверки
        self::assertSame('error', $response->status);
        self::assertSame('Неизвестная ошибка при создании бекапа', $response->message);
    }
}
