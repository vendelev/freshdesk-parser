---
description: Правила оформления PHP кода
alwaysApply: true
---

# Правила оформления PHP кода

## Импорты классов

Все полные наименования классов должны быть заменены на использование оператора `use` в начале файла.

### Пример

Вместо:
```php
$response = new \App\Modules\Task\Domain\Response\TaskResponse();
```

Используйте:
```php
use App\Modules\Task\Domain\Response\TaskResponse;

// ...

$response = new TaskResponse();
```

### Исключения

- Не добавляйте оператор `use` для встроенных типов PHP (int, string, bool, array, etc.)
- Не добавляйте оператор `use` для классов в том же namespace

## Вызов PHP команд

Для выполнения PHP команд следует использовать команду `make php-run`.

### Пример

Вместо:
```bash
php artisan migrate
```

Используйте:
```bash
make php-run cmd="artisan migrate"
```

Это обеспечивает выполнение команд в правильной среде с нужными настройками.