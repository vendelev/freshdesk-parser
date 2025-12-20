<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Presentation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Testing\PendingCommand;
use Parser\Task\Application\Service\LoadTicketDetailsService;
use Parser\Task\Domain\FreshdeskClientInterface;
use Tests\TestCase;

final class LoadTicketDetailsCommandTest extends TestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = $this->app->make('files');
    }

    public function testCommandExecutesSuccessfully(): void
    {
        $this->mock(FreshdeskClientInterface::class, function ($mock) {
            $mock->shouldReceive('getTicket')
                ->with(5510)
                ->once()
                ->andReturn([
                    'id' => 5510,
                    'subject' => 'Test ticket',
                    'description' => 'Test description',
                ]);

            $mock->shouldReceive('getTicket')
                ->with(5509)
                ->once()
                ->andReturn([
                    'id' => 5509,
                    'subject' => 'Test ticket 2',
                    'description' => 'Test description 2',
                ]);
        });

        $testDirectory = storage_path('freshdesk/test/12');
        $this->filesystem->makeDirectory($testDirectory, 0o755, true, true);

        $tasksJson = json_encode([
            ['id' => 5510, 'subject' => 'Test'],
            ['id' => 5509, 'subject' => 'Test 2'],
        ], JSON_UNESCAPED_UNICODE);

        $this->filesystem->put("{$testDirectory}/test_0001.json", $tasksJson);

        $result = $this->artisan('freshdesk:load-details');

        $result->assertExitCode(0);
        $this->assertTrue($this->filesystem->exists("{$testDirectory}/5510.json"));
        $this->assertTrue($this->filesystem->exists("{$testDirectory}/5509.json"));

        $this->filesystem->deleteDirectory(storage_path('freshdesk/test'));
    }

    public function testCommandHandlesApiErrors(): void
    {
        $this->mock(FreshdeskClientInterface::class, function ($mock) {
            $mock->shouldReceive('getTicket')
                ->with(9999)
                ->once()
                ->andThrow(new \Exception('Not found'));
        });

        $testDirectory = storage_path('freshdesk/test/12');
        $this->filesystem->makeDirectory($testDirectory, 0o755, true, true);

        $tasksJson = json_encode([
            ['id' => 9999, 'subject' => 'Test'],
        ], JSON_UNESCAPED_UNICODE);

        $this->filesystem->put("{$testDirectory}/test_0001.json", $tasksJson);

        $result = $this->artisan('freshdesk:load-details');

        $result->assertExitCode(0);
        $this->assertFalse($this->filesystem->exists("{$testDirectory}/9999.json"));

        $this->filesystem->deleteDirectory(storage_path('freshdesk/test'));
    }
}
