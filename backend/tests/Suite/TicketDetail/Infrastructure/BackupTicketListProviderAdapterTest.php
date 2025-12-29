<?php

declare(strict_types=1);

namespace Tests\Suite\TicketDetail\Infrastructure;

use Parser\TicketDetail\Infrastructure\Adapter\BackupTicketListProviderAdapter;
use RuntimeException;
use Tests\TestCase;

final class BackupTicketListProviderAdapterTest extends TestCase
{
    private string $testBackupDir;

    #[\Override]
    protected function setUp(): void
    {
        // Создать временную директорию для тестов
        $this->testBackupDir = sys_get_temp_dir() . '/ticket_detail_test_' . uniqid();
        mkdir($this->testBackupDir, 0777, true);

        parent::setUp();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // Удалить тестовые файлы
        $this->removeDirectory($this->testBackupDir);
        parent::tearDown();
    }

    /**
     * Рекурсивно удалить директорию
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function testSuccessfullyScansBackupsAndReturnsTicketIds(): void
    {
        // Дано
        $this->createTestBackupFiles();

        $adapter = new BackupTicketListProviderAdapter($this->testBackupDir);

        // Когда
        $ticketIds = iterator_to_array($adapter->getTicketIds());

        // Тогда
        self::assertCount(3, $ticketIds);
        self::assertContains(1001, $ticketIds);
        self::assertContains(1002, $ticketIds);
        self::assertContains(1003, $ticketIds);
    }

    public function testHandlesEmptyDirectory(): void
    {
        // Дано
        $adapter = new BackupTicketListProviderAdapter($this->testBackupDir);

        // Когда
        $ticketIds = iterator_to_array($adapter->getTicketIds());

        // Тогда
        self::assertEmpty($ticketIds);
    }

    public function testSkipsCorruptedFiles(): void
    {
        // Дано
        // Создать корректный файл бекапа
        file_put_contents(
            $this->testBackupDir . '/backup_2025-12-27_170710_page_001.json',
            json_encode([
                ['id' => 1001, 'subject' => 'Test Ticket 1'],
                ['id' => 1002, 'subject' => 'Test Ticket 2'],
            ])
        );

        // Создать поврежденный файл бекапа
        file_put_contents(
            $this->testBackupDir . '/backup_2025-12-27_170710_page_002.json',
            'invalid json content'
        );

        $adapter = new BackupTicketListProviderAdapter($this->testBackupDir);

        // Когда
        $ticketIds = iterator_to_array($adapter->getTicketIds());

        // Тогда
        self::assertCount(2, $ticketIds);
        self::assertContains(1001, $ticketIds);
        self::assertContains(1002, $ticketIds);
    }

    public function testThrowsExceptionWhenDirectoryDoesNotExist(): void
    {
        // Дано
        $nonExistentDir = $this->testBackupDir . '/non_existent';
        $adapter = new BackupTicketListProviderAdapter($nonExistentDir);

        // Тогда
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Директория бекапов не существует');

        // Когда
        iterator_to_array($adapter->getTicketIds());
    }

    /**
     * Создать тестовые файлы бекапов
     */
    private function createTestBackupFiles(): void
    {
        // Создать файлы бекапов с тестовыми данными
        file_put_contents(
            $this->testBackupDir . '/backup_2025-12-27_170710_page_001.json',
            json_encode([
                ['id' => 1001, 'subject' => 'Test Ticket 1'],
                ['id' => 1002, 'subject' => 'Test Ticket 2'],
            ])
        );

        file_put_contents(
            $this->testBackupDir . '/backup_2025-12-27_170710_page_002.json',
            json_encode([
                ['id' => 1003, 'subject' => 'Test Ticket 3'],
            ])
        );

        // Создать файл с некорректной структурой (без ID)
        file_put_contents(
            $this->testBackupDir . '/backup_2025-12-27_170710_page_003.json',
            json_encode([
                ['subject' => 'Test Ticket No ID'],
            ])
        );
    }
}
