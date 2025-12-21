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
