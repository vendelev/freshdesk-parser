# Task 16: Создать Factory для создания Entity из DTO

## Описание
Создать фабрику для создания Ticket Entity из TicketApiDto.

## Файл
`backend/src/Ticket/Application/Factory/TicketFactory.php`

## Методы
```php
public static function createFromApiDto(TicketApiDto $apiDto): Ticket
```

## Логика
1. Получить TicketApiDto
2. Создать ValueObjects для статуса, приоритета, источника
3. Создать Entity Ticket со всеми полями из DTO
4. Обработать ошибки валидации

## Требования
- Static метод для удобства использования
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Обработка ошибок (InvalidTicketData)
- Может быть покрыт unit тестами

## Принятие
- [ ] Factory создана
- [ ] Метод работает корректно
- [ ] PHPStan проходит без ошибок
- [ ] Unit тесты покрывают основные сценарии
