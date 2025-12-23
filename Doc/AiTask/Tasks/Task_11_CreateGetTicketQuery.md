# Task 11: Создать Query для получения полных данных одной задачи

## Описание
Создать Query объект для передачи параметров получения полных данных одного ticket'а из Freshdesk API.

## Файл
`backend/src/Ticket/Application/Query/GetTicketQuery.php`

## Параметры
- freshdeskId: int (ID ticket'а в Freshdesk)

## Требования
- Типизированные свойства
- PHPDoc комментарии
- Private свойства с getter'ами
- Валидация freshdeskId > 0

## Принятие
- [ ] Query создан
- [ ] Все параметры типизированы
- [ ] PHPStan проходит без ошибок
- [ ] Валидация работает
