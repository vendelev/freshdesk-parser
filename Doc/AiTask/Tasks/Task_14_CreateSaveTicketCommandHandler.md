# Task 14: Создать Command Handler для SaveTicketCommand

## Описание
Создать обработчик Command, который сохраняет/обновляет ticket в БД через Repository.

## Файл
`backend/src/Ticket/Application/Command/SaveTicketCommandHandler.php`

## Методы
```php
public function __invoke(SaveTicketCommand $command): void
```

## Логика
1. Извлечь ticket из command
2. Вызвать TicketRepositoryInterface::save(ticket)
3. Логировать сохранение (информационное сообщение)

## Требования
- Inject TicketRepositoryInterface через конструктор (Dependency Injection)
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Логирование через Psr\Log\LoggerInterface
- Обработка ошибок (NotFoundException, DatabaseException)

## Принятие
- [ ] Handler создан
- [ ] Логика работает корректно
- [ ] PHPStan проходит без ошибок
- [ ] Функциональные тесты покрывают основные сценарии
