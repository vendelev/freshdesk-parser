# Task 19: Создать Repository реализацию для Ticket

## Описание
Создать реализацию TicketRepositoryInterface которая работает с БД через Eloquent модель.

## Файл
`backend/src/Ticket/Infrastructure/Persistence/DatabaseTicketRepository.php`

## Методы
```php
public function save(Ticket $ticket): void
public function findById(int $id): ?Ticket
public function findByFreshdeskId(int $freshdeskId): ?Ticket
public function delete(int $id): void
```

## Логика преобразования
- Entity → EloquentModel (при сохранении)
- EloquentModel → Entity (при получении)

## Требования
- Реализует TicketRepositoryInterface
- Inject EloquentTicketModel через конструктор (Dependency Injection)
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Обработка ошибок (ModelNotFoundException)
- Преобразование между Entity и Eloquent моделью
- Use паттерны для правильного наполнения Entity
- Обновление существующих tickets (проверка по freshdesk_id)

## Принятие
- [ ] Repository создан
- [ ] Все методы реализованы
- [ ] Преобразование работает корректно
- [ ] PHPStan проходит без ошибок
- [ ] Интеграционные тесты покрывают основные сценарии
