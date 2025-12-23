# Task 10: Создать Query Handler для GetTicketListQuery

## Описание
Создать обработчик Query, который получает список tickets из Freshdesk API через Adapter.

## Файл
`backend/src/Ticket/Application/Query/GetTicketListQueryHandler.php`

## Методы
```php
public function __invoke(GetTicketListQuery $query): TicketListApiDto
```

## Логика
1. Извлечь page из query
2. Вызвать FreshdeskTicketAdapter::getTickets(page)
3. Вернуть TicketListApiDto

## Требования
- Inject FreshdeskTicketAdapter через конструктор (Dependency Injection)
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Обработка ошибок (HTTP ошибок из adapter'а)

## Принятие
- [ ] Handler создан
- [ ] Логика работает корректно
- [ ] PHPStan проходит без ошибок
- [ ] Функциональные тесты покрывают основные сценарии
