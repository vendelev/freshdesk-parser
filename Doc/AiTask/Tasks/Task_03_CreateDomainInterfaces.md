# Task 3: Создать Domain интерфейсы для Ticket

## Описание
Определить контракты (интерфейсы) для работы с Ticket, которые будут реализованы в Infrastructure слое.

## Файл
`backend/src/Ticket/Domain/TicketRepositoryInterface.php`

## Методы
```php
public function save(Ticket $ticket): void;
public function findById(int $id): ?Ticket;
public function findByFreshdeskId(int $freshdeskId): ?Ticket;
public function delete(int $id): void;
```

## Требования
- Интерфейс используется для инверсии зависимостей
- Параметры и возвращаемые значения типизированы
- PHPDoc комментарии для каждого метода
- Находится в Domain слое

## Принятие
- [ ] Интерфейс создан
- [ ] Все методы задокументированы
- [ ] PHPStan проходит без ошибок
