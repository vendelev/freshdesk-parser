# Рекомендации по использованию переменных окружения

## Общие принципы

1. **Конфигурация через конфигурационные файлы**: Все параметры приложения должны быть доступны через систему конфигурации Laravel, используя функцию `config()`.

2. **Использование env() только в конфигурационных файлах**: Функция `env()` должна использоваться только в конфигурационных файлах в каталоге `config/` или в соответствующих файлах модулей.

3. **Избегайте прямого использования env() в бизнес-логике**: Никогда не используйте `env()` напрямую в классах Application, Domain или Infrastructure слоев.

## Правильное использование

### 1. Создание конфигурационного файла

Создайте конфигурационный файл в каталоге `Presentation/Config` вашего модуля:

```php
// freshdesk.php
<?php

declare(strict_types=1);

return [
    'api_key' => env('FRESHDESK_API_KEY'),
    'domain' => env('FRESHDESK_DOMAIN'),
];
```

### 2. Регистрация конфигурации в ServiceProvider

```php
// TaskServiceProvider.php
public function register(): void
{
    // Merge configuration
    $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');
    
    // ...
}
```

### 3. Использование контейнера зависимостей

Для передачи конфигурационных значений в классы используйте контейнер зависимостей:

```php
// TaskServiceProvider.php
public function register(): void
{
    // Merge configuration
    $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');
    
    $this->app->singleton(ParseTask::class);
    
    $this->app->when(ParseTask::class)
        ->needs('$freshdeskApiKey')
        ->giveConfig('freshdesk.api_key');
        
    $this->app->when(ParseTask::class)
        ->needs('$freshdeskDomain')
        ->giveConfig('freshdesk.domain');
}
```

### 4. Получение конфигурации в классах

В классах используйте внедрение зависимостей для получения значений:

```php
final readonly class ParseTask
{
    public function __construct(
        private TaskParserInterface $taskParser,
        private string $freshdeskApiKey,
        private string $freshdeskDomain,
    ) {
    }
    
    // ...
}
```

## Преимущества подхода

1. **Тестируемость**: Конфигурационные значения могут быть легко заменены в тестах.
2. **Гибкость**: Возможность переопределения конфигурации через файлы конфигурации.
3. **Читаемость**: Явное указание зависимостей от конфигурации.
4. **Соответствие принципам Clean Architecture**: Конфигурация инкапсулирована в Presentation слое.

## Переменные окружения в .env файле

Все переменные окружения должны быть определены в файле `.env.example` с примерами значений и комментариями:

```env
# Freshdesk API Configuration
FRESHDESK_API_KEY=your_freshdesk_api_key_here
FRESHDESK_DOMAIN=your_company_domain
