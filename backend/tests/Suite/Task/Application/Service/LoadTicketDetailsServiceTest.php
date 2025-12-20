<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Application\Service;

use Illuminate\Filesystem\Filesystem;
use Parser\Task\Application\Service\LoadTicketDetailsService;
use Parser\Task\Domain\FreshdeskClientInterface;
use Tests\TestCase;

final class LoadTicketDetailsServiceTest extends TestCase
{
    private Filesystem $filesystem;

    private FreshdeskClientInterface $freshdeskClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = $this->app->make('files');
        $this->freshdeskClient = $this->mock(FreshdeskClientInterface::class);
    }

    public function testLoadAndSaveSuccessfully(): void
    {
        $this->freshdeskClient->shouldReceive('getTicket')
            ->with(123)
            ->once()
            ->andReturn(['id' => 123, 'subject' => 'Test ticket']);

        $testDirectory = storage_path('freshdesk/test/12');
        $this->filesystem->makeDirectory($testDirectory, 0o755, true, true);

        $tasksJson = json_encode([
            ['id' => 123, 'subject' => 'Test'],
        ], JSON_UNESCAPED_UNICODE);

        $this->filesystem->put("{$testDirectory}/test_0001.json", $tasksJson);

        $service = new LoadTicketDetailsService(
            $this->freshdeskClient,
            $this->filesystem,
            'freshdesk'
        );

        $results = $service->loadAndSave();

        $this->assertCount(1, $results);
        $this->assertEqual('success', $results[0]['status']);
        $this->assertEqual(123, $results[0]['ticket_id']);

        $this->assertTrue($this->filesystem->exists("{$testDirectory}/123.json"));

        $savedData = json_decode(
            $this->filesystem->get("{$testDirectory}/123.json"),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertEqual(123, $savedData['id']);
        $this->assertEqual('Test ticket', $savedData['subject']);

        $this->filesystem->deleteDirectory(storage_path('freshdesk/test'));
    }

    public function testLoadAndSaveWithErrors(): void
    {
        $this->freshdeskClient->shouldReceive('getTicket')
            ->with(456)
            ->once()
            ->andThrow(new \Exception('API error'));

        $testDirectory = storage_path('freshdesk/test/12');
        $this->filesystem->makeDirectory($testDirectory, 0o755, true, true);

        $tasksJson = json_encode([
            ['id' => 456, 'subject' => 'Test'],
        ], JSON_UNESCAPED_UNICODE);

        $this->filesystem->put("{$testDirectory}/test_0001.json", $tasksJson);

        $service = new LoadTicketDetailsService(
            $this->freshdeskClient,
            $this->filesystem,
            'freshdesk'
        );

        $results = $service->loadAndSave();

        $this->assertCount(1, $results);
        $this->assertEqual('failed', $results[0]['status']);
        $this->assertEqual(456, $results[0]['ticket_id']);
        $this->assertArrayHasKey('error', $results[0]);

        $this->assertFalse($this->filesystem->exists("{$testDirectory}/456.json"));

        $this->filesystem->deleteDirectory(storage_path('freshdesk/test'));
    }

    public function testLoadAndSaveWithoutJsonFiles(): void
    {
        $service = new LoadTicketDetailsService(
            $this->freshdeskClient,
            $this->filesystem,
            'freshdesk'
        );

        $results = $service->loadAndSave();

        $this->assertCount(0, $results);
    }
}
