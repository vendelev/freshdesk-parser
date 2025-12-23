# Task 20: Создать Adapter для Freshdesk API (Ticket)

## Описание
Создать адаптер для взаимодействия с Freshdesk API. Adapter реализует anti-corruption layer между API и Application слоем.

## Файл
`backend/src/Ticket/Infrastructure/Adapter/FreshdeskTicketAdapter.php`

## Методы
```php
public function getTickets(int $page = 1): TicketListApiDto
public function getTicket(int $freshdeskId): TicketApiDto
```

## getTickets(int $page = 1): TicketListApiDto
- API: `GET /api/v2/tickets?page={page}&per_page=100`
- Возвращает пагинированный список tickets
- Query параметры: page, per_page (100)

## getTicket(int $freshdeskId): TicketApiDto
- API: `GET /api/v2/tickets/{id}`
- Возвращает полные данные одного ticket'а

## Требования
- Inject FreshdeskClient через конструктор (Dependency Injection)
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Throttling: sleep(1) после каждого запроса к API (соблюдение rate limits)
- Обработка 429 (Too Many Requests) ошибок - повторная попытка с exponential backoff
- Обработка других HTTP ошибок (выбрасывание исключения)
- Парсинг JSON ответа в DTO объекты

## Требование к Rate Limiting
Freshdesk API имеет rate limit 240 запросов в минуту. Использовать sleep(1) = 1 запрос в секунду = 60 в минуту (безопасно).

## Принятие
- [ ] Adapter создан
- [ ] Оба метода реализованы
- [ ] Throttling работает
- [ ] Обработка ошибок корректна
- [ ] PHPStan проходит без ошибок
- [ ] Интеграционные тесты покрывают основные сценарии
