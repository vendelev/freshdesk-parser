---
description: Подсказки при работе с PHP кодом
alwaysApply: true
---

# Подсказки при работе с PHP кодом

## Импорты классов

Все полные наименования классов должны быть заменены на использование оператора `use` в начале файла.

### Пример

Вместо:
```php
$response = new \Parser\Task\Domain\Response\TaskResponse();
```

Используйте:
```php
use Parser\Task\Domain\Response\TaskResponse;

// ...

$response = new TaskResponse();
```

### Исключения

- Не добавляйте оператор `use` для встроенных типов PHP (int, string, bool, array, etc.)
- Не добавляйте оператор `use` для классов в том же namespace

## Регистрация интерфейсов и реализаций

Для связи интерфейсов и их реализаций в контейнере зависимостей Laravel используйте метод `bind`:

```php
$this->app->bind(Interface::class, Implementation::class);
```

Это позволяет легко заменять реализации в тестах и соблюдает принцип инверсии зависимостей.

## Комментарии в коде

Все комментарии в коде должны быть написаны на русском языке.

### Пример

```php
// Получить список задач из Freshdesk
$tasks = $this->freshdeskClient->getTasks();
```

### Исключения

- Комментарии в формате PHPDoc могут содержать английские термины, относящиеся к коду (например, @param, @return, @throws)
- Названия классов, методов и переменных в комментариях должны сохранять оригинальное написание

## Рекомендации по оформлению кода

1. **При написании тестов:**
    - Использовать статические вызовы методов PHPUnit, например (
      self::assertTrue вместо $this->assertTrue,
      self::assertFalse вместо $this->assertFalse,
      self::assertSame вместо $this->assertSame,
      self::assertNull вместо $this->assertNull,
      self::assertEquals вместо $this->assertEquals,
      )

2. **При написании кода:**
    - Следить за длиной строк (не более 120 символов)
    - Разбивать длинные строки на несколько строк для улучшения читаемости
    - Не переопределять методы без изменения логики
    - Всегда проверять форматирование кода после внесения изменений
    - Использовать типизированные константы для улучшения type safety

4. **При работе с Entity и DTO:**
    - Использовать публичные readonly свойства вместо геттеров (согласно возможностям PHP 8.5)
    - Добавлять типы массивов в PHPDoc комментариях для улучшения анализа кода
    - Для скалярных массивов в PHPDocs вместо `array<...>` указывать `list<...>`

5. **При работе с интерфейсами:**
    - Удалять лишние @return void из PHPDoc комментариев
    - Следить за корректностью описания методов

## Рекомендации по использованию переменных окружения

### Общие принципы

1. **Конфигурация через конфигурационные файлы**: Все параметры приложения должны быть доступны через систему конфигурации Laravel, используя функцию `config()`.

2. **Использование env() только в конфигурационных файлах**: Функция `env()` должна использоваться только в конфигурационных файлах в каталоге `config/` или в соответствующих файлах модулей.

3. **Избегайте прямого использования env() в бизнес-логике**: Никогда не используйте `env()` напрямую в классах Application, Domain или Infrastructure слоев.

### Правильное использование

#### 1. Создание конфигурационного файла

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

#### 2. Регистрация конфигурации в ServiceProvider

```php
// TaskServiceProvider.php
public function register(): void
{
    // Merge configuration
    $this->mergeConfigFrom(__DIR__ . '/freshdesk.php', 'freshdesk');
    
    // ...
}
```

#### 3. Использование контейнера зависимостей

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

#### 4. Получение конфигурации в классах

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

### Преимущества подхода

1. **Тестируемость**: Конфигурационные значения могут быть легко заменены в тестах.
2. **Гибкость**: Возможность переопределения конфигурации через файлы конфигурации.
3. **Читаемость**: Явное указание зависимостей от конфигурации.
4. **Соответствие принципам Clean Architecture**: Конфигурация инкапсулирована в Presentation слое.

### Переменные окружения в .env файле

Все переменные окружения должны быть определены в файле `.env.example` с примерами значений и комментариями:

```env
# Freshdesk API Configuration
FRESHDESK_API_KEY=your_freshdesk_api_key_here
FRESHDESK_DOMAIN=your_company_domain
