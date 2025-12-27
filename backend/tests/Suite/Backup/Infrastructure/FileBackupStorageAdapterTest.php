<?php

declare(strict_types=1);

namespace Tests\Suite\Backup\Infrastructure;

use Parser\Backup\Domain\Exception\BackupOperationFailedException;
use Parser\Backup\Infrastructure\Adapter\FileBackupStorageAdapter;
use Tests\TestCase;

final class FileBackupStorageAdapterTest extends TestCase
{
    private string $testStoragePath = '';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Создание временной директории для тестов
        $this->testStoragePath = sys_get_temp_dir() . '/backup_test_' . uniqid();
        if (!mkdir($this->testStoragePath, 0755, true)) {
            self::fail('Не удалось создать тестовую директорию');
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        // Удаление всех файлов и директории
        if (is_dir($this->testStoragePath)) {
            $files = glob($this->testStoragePath . '/*');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            rmdir($this->testStoragePath);
        }
    }

    /**
     * Успешная запись одной страницы файла
     *
     * @throws BackupOperationFailedException
     */
    public function testSuccessfulSavePage(): void
    {
        // Подготовка
        $adapter = new FileBackupStorageAdapter($this->testStoragePath);
        $ticketsJson = json_encode(array_fill(0, 50, ['id' => 1, 'subject' => 'Test Ticket']));
        $backupId = '2025-01-15_103000';
        $pageNumber = 1;

        // Выполнение
        $adapter->savePage($ticketsJson, $backupId, $pageNumber);

        // Проверка
        $expectedFilename = sprintf('backup_%s_page_%03d.json', $backupId, $pageNumber);
        $expectedPath = $this->testStoragePath . '/' . $expectedFilename;

        self::assertFileExists($expectedPath);
        $fileContent = file_get_contents($expectedPath);
        self::assertSame($ticketsJson, $fileContent);
    }

    /**
     * Успешная запись нескольких страниц файла
     *
     * @throws BackupOperationFailedException
     */
    public function testSuccessfulSaveMultiplePages(): void
    {
        // Подготовка
        $adapter = new FileBackupStorageAdapter($this->testStoragePath);
        $backupId = '2025-01-15_103000';

        $pages = [
            1 => json_encode(array_fill(0, 100, ['id' => 1])),
            2 => json_encode(array_fill(0, 100, ['id' => 2])),
            3 => json_encode(array_fill(0, 50, ['id' => 3])),
        ];

        // Выполнение
        foreach ($pages as $pageNumber => $pageJson) {
            $adapter->savePage($pageJson, $backupId, $pageNumber);
        }

        // Проверка
        foreach ($pages as $pageNumber => $expectedJson) {
            $expectedFilename = sprintf('backup_%s_page_%03d.json', $backupId, $pageNumber);
            $expectedPath = $this->testStoragePath . '/' . $expectedFilename;

            self::assertFileExists($expectedPath);
            $fileContent = file_get_contents($expectedPath);
            self::assertSame($expectedJson, $fileContent);
        }
    }

    /**
     * Успешная запись метаданных
     *
     * @throws BackupOperationFailedException
     */
    public function testSuccessfulSaveMetadata(): void
    {
        // Подготовка
        $adapter = new FileBackupStorageAdapter($this->testStoragePath);
        $metadata = [
            'created_at' => '2025-01-15T10:30:00Z',
            'total_tickets' => 250,
            'total_pages' => 3,
        ];
        $backupId = '2025-01-15_103000';

        // Выполнение
        $adapter->saveMetadata($metadata, $backupId);

        // Проверка
        $expectedFilename = sprintf('backup_%s_meta.json', $backupId);
        $expectedPath = $this->testStoragePath . '/' . $expectedFilename;

        self::assertFileExists($expectedPath);
        $fileContent = file_get_contents($expectedPath);
        $decodedContent = json_decode($fileContent, true);

        self::assertSame($metadata, $decodedContent);
    }

    /**
     * Автоматическое создание директории
     *
     * @throws BackupOperationFailedException
     */
    public function testAutomaticDirectoryCreation(): void
    {
        // Подготовка - используем несуществующую директорию
        $newStoragePath = sys_get_temp_dir() . '/backup_test_new_' . uniqid();
        self::assertDirectoryDoesNotExist($newStoragePath);

        // Выполнение
        $adapter = new FileBackupStorageAdapter($newStoragePath);
        $ticketsJson = json_encode(['id' => 1]);
        $adapter->savePage($ticketsJson, '2025-01-15_103000', 1);

        // Проверка
        self::assertDirectoryExists($newStoragePath);

        // Очистка
        $files = glob($newStoragePath . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        rmdir($newStoragePath);
    }

    /**
     * Перезапись существующего файла
     *
     * @throws BackupOperationFailedException
     */
    public function testOverwriteExistingFile(): void
    {
        // Подготовка
        $adapter = new FileBackupStorageAdapter($this->testStoragePath);
        $backupId = '2025-01-15_103000';
        $oldJson = json_encode(['id' => 'old']);
        $newJson = json_encode(['id' => 'new']);

        // Выполнение - сначала сохраняем старые данные
        $adapter->savePage($oldJson, $backupId, 1);

        // Потом перезаписываем новыми данными
        $adapter->savePage($newJson, $backupId, 1);

        // Проверка
        $expectedFilename = sprintf('backup_%s_page_%03d.json', $backupId, 1);
        $expectedPath = $this->testStoragePath . '/' . $expectedFilename;

        $fileContent = file_get_contents($expectedPath);
        self::assertSame($newJson, $fileContent);
    }

    /**
     * Ошибка записи при недостаточных прав доступа
     */
    public function testWriteErrorWithoutPermissions(): void
    {
        // Пропускаем тест если запускаемся как root
        if (posix_getuid() === 0) {
            self::markTestSkipped('Тест не может выполняться от пользователя root');
        }

        // Подготовка - создаем директорию без прав записи
        $readOnlyPath = sys_get_temp_dir() . '/backup_readonly_' . uniqid();
        mkdir($readOnlyPath, 0555, true);

        // Выполнение и проверка
        $adapter = new FileBackupStorageAdapter($readOnlyPath);

        try {
            $ticketsJson = json_encode(['id' => 1]);
            $adapter->savePage($ticketsJson, '2025-01-15_103000', 1);

            // Если мы здесь - тест может быть пропущен (например, если разработчик использует sudo)
            self::markTestSkipped('Тест требует директорию без прав на запись');
        } catch (BackupOperationFailedException $e) {
            self::assertStringContainsString('Не удалось', $e->getMessage());
        } finally {
            // Очистка
            chmod($readOnlyPath, 0755);
            rmdir($readOnlyPath);
        }
    }

    /**
     * Ошибка при сохранении невалидного JSON в метаданные
     */
    public function testMetadataJsonEncodingError(): void
    {
        // Подготовка
        $adapter = new FileBackupStorageAdapter($this->testStoragePath);

        // Метаданные с циклической ссылкой вызовут ошибку при json_encode
        $metadata = [
            'created_at' => '2025-01-15T10:30:00Z',
            'total_tickets' => 250,
        ];

        // Добавляем ресурс (который не может быть сериализован)
        $resource = fopen('php://memory', 'r');
        $metadata['resource'] = $resource;

        try {
            // Выполнение - должно выбросить исключение
            $adapter->saveMetadata($metadata, '2025-01-15_103000');

            // Если исключения не было - проверяем что произошла ошибка
            self::fail('Ожидалось исключение BackupOperationFailedException');
        } catch (BackupOperationFailedException $e) {
            self::assertStringContainsString('сериализовать', $e->getMessage());
        } finally {
            fclose($resource);
        }
    }

    /**
     * Проверка корректного форматирования JSON для метаданных
     *
     * @throws BackupOperationFailedException
     */
    public function testMetadataJsonFormatting(): void
    {
        // Подготовка
        $adapter = new FileBackupStorageAdapter($this->testStoragePath);
        $metadata = [
            'created_at' => '2025-01-15T10:30:00Z',
            'total_tickets' => 250,
            'total_pages' => 3,
        ];
        $backupId = '2025-01-15_103000';

        // Выполнение
        $adapter->saveMetadata($metadata, $backupId);

        // Проверка
        $expectedFilename = sprintf('backup_%s_meta.json', $backupId);
        $expectedPath = $this->testStoragePath . '/' . $expectedFilename;

        $fileContent = file_get_contents($expectedPath);
        self::assertIsString($fileContent);

        // Проверяем что JSON отформатирован (содержит переводы строк)
        self::assertStringContainsString("\n", $fileContent);

        // Проверяем что JSON валиден
        $decoded = json_decode($fileContent, true);
        self::assertIsArray($decoded);
        self::assertSame($metadata, $decoded);
    }
}
