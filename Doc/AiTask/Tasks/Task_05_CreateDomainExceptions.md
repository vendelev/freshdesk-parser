# Task 5: Создать Domain исключения для Ticket

## Описание
Определить доменные исключения, которые выбрасываются при нарушении бизнес-правил Ticket.

## Файл
`backend/src/Ticket/Domain/Exception/`

## Исключения

### TicketNotFound
Выбрасывается когда ticket не найден в repository по ID.

Сигнатура:
```php
public function __construct(int $id, \Throwable $previous = null)
```

### InvalidTicketData
Выбрасывается когда данные ticket'а невалидны (например, неправильный статус, приоритет и т.д.).

Сигнатура:
```php
public function __construct(string $message, \Throwable $previous = null)
```

## Требования
- Все исключения расширяют базовый Exception класс (или DomainException если есть)
- Содержат PHPDoc комментарии
- Информативные сообщения об ошибках

## Принятие
- [ ] Оба исключения созданы
- [ ] Наследование от правильного базового класса
- [ ] PHPStan проходит без ошибок
