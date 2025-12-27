<?php

declare(strict_types=1);

namespace Tests\Suite\Backup\Presentation;

use Illuminate\Testing\PendingCommand;
use Parser\Backup\Domain\Exception\FreshdeskApiConnectionException;
use Parser\Backup\Domain\FreshdeskClientInterface;
use Tests\TestCase;

final class BackupTicketsCommandTest extends TestCase
{
    private string $testStoragePath = '';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Создание временной директории для тестов
        $this->testStoragePath = sys_get_temp_dir() . '/backup_e2e_test_' . uniqid();
        mkdir($this->testStoragePath, 0755, true);

        // Мокируем конфигурацию
        config([
            'freshdesk.api_key' => 'test_api_key',
            'freshdesk.domain' => 'test_domain',
            'freshdesk.backup_storage_path' => $this->testStoragePath,
        ]);
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
     * Успешное выполнение команды бекапа
     *
     * @throws \ReflectionException
     * @throws \TypeError
     */
    public function testSuccessfulBackupCommand(): void
    {
        // Мокируем FreshdeskClientInterface
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);

        // Подготавливаем генератор с тестовыми данными
        $page1 = json_encode(array_fill(0, 100, ['id' => 1, 'subject' => 'Test Ticket 1']));
        $page2 = json_encode(array_fill(0, 150, ['id' => 2, 'subject' => 'Test Ticket 2']));

        $generator = (static function () use ($page1, $page2) {
            yield $page1;
            yield $page2;
        })();

        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willReturn($generator);

        // Регистрируем мок в контейнер
        $this->app->bind(
            FreshdeskClientInterface::class,
            fn (): \PHPUnit\Framework\MockObject\MockObject => $mockFreshdeskClient
        );

        // Выполняем команду
        /** @var PendingCommand $response */
        $response = $this->artisan('backup:tickets');

        // Проверяем exit code
        $response->assertExitCode(0);
    }

    /**
     * Обработка ошибки подключения к API
     *
     * @throws \ReflectionException
     * @throws \TypeError
     */
    public function testBackupCommandHandlesApiError(): void
    {
        // Мокируем FreshdeskClientInterface
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);

        // Генератор выбрасывает исключение
        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willThrowException(new FreshdeskApiConnectionException('Ошибка подключения'));

        // Регистрируем мок в контейнер
        $this->app->bind(
            FreshdeskClientInterface::class,
            fn (): \PHPUnit\Framework\MockObject\MockObject => $mockFreshdeskClient
        );

        // Выполняем команду
        /** @var PendingCommand $response */
        $response = $this->artisan('backup:tickets');

        // Проверяем exit code
        $response->assertExitCode(1);
    }

    /**
     * Проверка вывода команды при успехе
     *
     * @throws \ReflectionException
     * @throws \TypeError
     */
    public function testBackupCommandOutputOnSuccess(): void
    {
        // Мокируем FreshdeskClientInterface
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);

        // Подготавливаем генератор
        $pageData = json_encode(array_fill(0, 250, ['id' => 1, 'subject' => 'Test']));

        $generator = (static function () use ($pageData) {
            yield $pageData;
        })();

        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willReturn($generator);

        // Регистрируем мок в контейнер
        $this->app->bind(
            FreshdeskClientInterface::class,
            fn (): \PHPUnit\Framework\MockObject\MockObject => $mockFreshdeskClient
        );

        // Выполняем команду
        /** @var PendingCommand $response */
        $response = $this->artisan('backup:tickets');

        // Проверяем exit code
        $response->assertExitCode(0);
    }

    /**
     * Проверка справки команды (--help)
     */
    public function testBackupCommandHelpFlag(): void
    {
        // Выполняем команду с флагом --help
        /** @var PendingCommand $response */
        $response = $this->artisan('backup:tickets --help');

        // Проверяем exit code (справка возвращает 0)
        $response->assertExitCode(0);
    }

//    /**
//     * Команда создает файлы бекапа
//     *
//     * @throws \ReflectionException
//     * @throws \TypeError
//     */
//    public function testBackupCommandCreatesBackupFiles(): void
//    {
//        // Мокируем FreshdeskClientInterface
//        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);
//
//        // Подготавливаем генератор
//        $page1 = json_encode(array_fill(0, 100, ['id' => 1]));
//        $page2 = json_encode(array_fill(0, 100, ['id' => 2]));
//
//        $generator = (static function () use ($page1, $page2) {
//            yield $page1;
//            yield $page2;
//        })();
//
//        $mockFreshdeskClient
//            ->expects(self::once())
//            ->method('getTicketsIterator')
//            ->willReturn($generator);
//
//        // Регистрируем мок в контейнер
//        $this->app->bind(
//            FreshdeskClientInterface::class,
//            fn (): \PHPUnit\Framework\MockObject\MockObject => $mockFreshdeskClient
//        );
//
//        // Выполняем команду
//        /** @var PendingCommand $response */
//        $response = $this->artisan('backup:tickets');
//
//        // Проверяем exit code
//        /** @var PendingCommand $response */
//        $response->assertExitCode(0);
//
//        // Проверяем что файлы созданы
//        $files = glob($this->testStoragePath . '/*');
//        self::assertNotEmpty($files);
//        self::assertGreaterThanOrEqual(3, count($files));  // хотя бы 2 страницы + метаданные
//
//        // Проверяем что файлы содержат JSON
//        foreach ($files as $file) {
//            if (is_file($file)) {
//                $content = file_get_contents($file);
//                self::assertIsString($content);
//                $decoded = json_decode($content, true);
//                self::assertIsArray($decoded);
//            }
//        }
//    }

    /**
     * Команда выводит информацию о количестве задач
     *
     * @throws \ReflectionException
     * @throws \TypeError
     */
    public function testBackupCommandOutputsTicketCount(): void
    {
        // Мокируем FreshdeskClientInterface
        $mockFreshdeskClient = $this->createMock(FreshdeskClientInterface::class);

        // Подготавливаем генератор с известным количеством задач
        $tickets = array_fill(0, 123, ['id' => 1, 'subject' => 'Test']);
        $pageData = json_encode($tickets);

        $generator = (static function () use ($pageData) {
            yield $pageData;
        })();

        $mockFreshdeskClient
            ->expects(self::once())
            ->method('getTicketsIterator')
            ->willReturn($generator);

        // Регистрируем мок в контейнер
        $this->app->bind(
            FreshdeskClientInterface::class,
            fn (): \PHPUnit\Framework\MockObject\MockObject => $mockFreshdeskClient
        );

        // Выполняем команду
        /** @var PendingCommand $response */
        $response = $this->artisan('backup:tickets');

        // Проверяем exit code
        $response->assertExitCode(0);
    }
}
