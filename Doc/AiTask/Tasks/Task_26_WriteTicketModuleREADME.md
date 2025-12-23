# Task 26: Написать README.md для Ticket модуля

## Описание
Создать документацию для Ticket модуля, которая объясняет:
- Назначение модуля
- Архитектура модуля
- Примеры использования
- Структура директорий

## Файл
`backend/src/Ticket/README.md`

## Содержание

### 1. Введение
- Назначение модуля (синхронизация tickets из Freshdesk)
- Основные возможности

### 2. Архитектура
- Слои модуля (Domain, Application, Infrastructure, Presentation)
- Основные компоненты и их ответственность

### 3. Использование

#### UseCase: Синхронизация tickets
```php
$useCase = app(SyncTicketsUseCase::class);
$response = $useCase->execute();
echo "Synced: " . $response->getSuccessCount();
```

#### Console Command
```bash
php artisan freshdesk:sync
```

### 4. Структура директорий
```
Ticket/
├── Domain/             (Entity, ValueObjects, Interfaces)
├── Application/        (UseCase, Query, Command, Service, DTO, Factory)
├── Infrastructure/     (Repository, Adapter, HTTP Client)
├── Presentation/       (Console Command, Service Provider)
└── README.md           (этот файл)
```

### 5. Data Flow
1. Console Command вызывает SyncTicketsUseCase
2. UseCase получает список tickets через Query Handlers
3. Query Handlers вызывают Adapter к Freshdesk API
4. Данные трансформируются в Entity через Transformer и Factory
5. Entity сохраняются в БД через Repository (Command Handler)

### 6. Testing
- Unit тесты для Domain слоя
- Функциональные тесты для Application слоя
- Интеграционные тесты для Infrastructure слоя

## Требования
- Все разделы присутствуют
- Примеры кода корректны
- Диаграмма data flow
- Примеры использования

## Принятие
- [ ] README создан
- [ ] Все разделы присутствуют
- [ ] Примеры работают
- [ ] Диаграмма data flow добавлена
