# Task 8: Создать Service для трансформации API данных в Domain Entity

## Описание
Создать сервис, который преобразует TicketApiDto в доменный Entity Ticket.

## Файл
`backend/src/Ticket/Application/Service/TicketTransformer.php`

## Методы
```php
public function transformFromApi(TicketApiDto $apiDto): Ticket
```

## Логика трансформации
1. Получить TicketApiDto
2. Создать ValueObjects для статуса, приоритета, источника
3. Создать Entity Ticket со всеми полями
4. Обработать ошибки валидации (выбросить InvalidTicketData если необходимо)

## Требования
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Обработка ошибок валидации
- Не использует Eloquent модели напрямую
- Может быть покрыт unit тестами

## Принятие
- [ ] Service создан
- [ ] Метод transformFromApi работает корректно
- [ ] PHPStan проходит без ошибок
- [ ] Unit тесты покрывают основные сценарии
