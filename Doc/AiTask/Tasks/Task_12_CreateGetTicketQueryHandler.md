# Task 12: Создать Query Handler для GetTicketQuery

## Описание
Создать обработчик Query, который получает полные данные одного ticket'а из Freshdesk API через Adapter.

## Файл
`backend/src/Ticket/Application/Query/GetTicketQueryHandler.php`

## Методы
```php
public function __invoke(GetTicketQuery $query): TicketApiDto
```

## Логика
1. Извлечь freshdeskId из query
2. Вызвать FreshdeskTicketAdapter::getTicket(freshdeskId)
3. Вернуть TicketApiDto

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
