# Task 15: Создать UseCase для синхронизации всех Tickets

## Описание
Создать основной usecase, который координирует процесс синхронизации всех tickets из Freshdesk.

## Файл
`backend/src/Ticket/Application/UseCase/SyncTicketsUseCase.php`

## Методы
```php
public function execute(): SyncTicketsResponse
```

## Алгоритм
1. Получить список всех ID через GetTicketListQuery с пагинацией (все страницы)
2. Для каждого ID получить полные данные через GetTicketQuery
3. Трансформировать в Entity через TicketTransformer
4. Сохранить через SaveTicketCommand
5. Логировать прогресс

## Требования
- Inject зависимостей через конструктор (Query handlers, Command handlers, Service, Logger)
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Логирование прогресса (начало, каждые N tickets, завершение)
- Обработка ошибок (пропуск ошибочных tickets, логирование)
- Return SyncTicketsResponse с количеством синхронизованных/ошибочных tickets

## SyncTicketsResponse
Должна содержать:
- successCount: int
- errorCount: int
- totalCount: int
- errors: array (список ошибок)

## Принятие
- [ ] UseCase создан
- [ ] Алгоритм реализован полностью
- [ ] PHPStan проходит без ошибок
- [ ] Функциональные тесты покрывают основные сценарии
