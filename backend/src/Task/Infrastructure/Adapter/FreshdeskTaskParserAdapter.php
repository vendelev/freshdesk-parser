<?php

declare(strict_types=1);

namespace Parser\Task\Infrastructure\Adapter;

use Parser\Task\Domain\Request\GetTasksRequest;
use Parser\Task\Domain\TaskParserInterface;

/**
 * @final
 * @readonly
 */
final readonly class FreshdeskTaskParserAdapter implements TaskParserInterface
{
    public function __construct(
        private string $freshdeskApiKey,
        private string $freshdeskDomain,
    ) {
    }

    public function parse(string $data): array
    {
        // Адаптер для парсинга данных из Freshdesk
        return [];
    }
    
    /**
     * @return array<array<string, mixed>>
     */
    public function getTasks(GetTasksRequest $request): array
    {
        // Получение списка задач из Freshdesk
        $apiKey = $this->freshdeskApiKey;
        $domain = $this->freshdeskDomain;
        
        $url = "https://{$domain}.freshdesk.com/api/v2/tickets";
        
        // Формирование параметров запроса
        $params = [
            'per_page' => $request->perPage,
            'page' => $request->page,
        ];
        
        if ($request->updatedSince) {
            $params['updated_since'] = $request->updatedSince;
        }
        
        $url .= '?' . http_build_query($params);
        
        // Выполнение HTTP-запроса
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "$apiKey:X");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response === false) {
            throw new \RuntimeException("Failed to fetch tasks from Freshdesk. cURL error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new \RuntimeException("Failed to fetch tasks from Freshdesk. HTTP code: $httpCode. Response: $response");
        }
        
        $tasks = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode JSON response from Freshdesk: ' . json_last_error_msg());
        }
        
        return $tasks;
    }
}