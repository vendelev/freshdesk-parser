# Task 2: Создать ValueObjects для Ticket

## Описание
Создать неизменяемые объекты для представления специальных полей Ticket с встроенной валидацией.

## Файлы
- `backend/src/Ticket/Domain/ValueObject/TicketId.php` (freshdesk_id)
- `backend/src/Ticket/Domain/ValueObject/TicketStatus.php`
- `backend/src/Ticket/Domain/ValueObject/TicketPriority.php`
- `backend/src/Ticket/Domain/ValueObject/TicketSource.php`

## TicketStatus
Допустимые значения статусов:
- 2: Open
- 3: Pending
- 4: Resolved
- 5: Closed
- 6: Waiting on Customer
- 7: Waiting on Third Party

## TicketPriority
Допустимые значения приоритетов:
- 1: Low
- 2: Medium
- 3: High
- 4: Urgent

## TicketSource
Допустимые источники:
- 1: Email
- 2: Portal
- 3: Phone
- 7: Chat
- и т.д.

## Требования
- Валидация значений в конструкторе
- Immutable (все поля private)
- Методы для получения значения (getValue/value)
- PHPDoc комментарии
- Выбрасывание исключения при невалидном значении

## Принятие
- [ ] Все ValueObjects созданы
- [ ] Валидация работает корректно
- [ ] PHPStan проходит без ошибок
- [ ] Unit тесты для каждого ValueObject
