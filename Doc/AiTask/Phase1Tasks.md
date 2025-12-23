# Phase 1: MVP — Список задач

## Статус: Начало

**Цель Phase 1**: Реализовать базовую синхронизацию задач (tickets) из Freshdesk в SQLite через консольную команду.

---

## Основные правила

- [Обзор проекта](../project-info.md)
- [PHP Lead Developer Role](../Rule/DeveloperRole.md)
- [Архитектура проекта](../Rule/Architecture.md)
- [Тестирование](../Rule/Testing.md)
- [Workflow при добавлении новой feature](../Rule/FeatureWorkflow.md)
- [Подсказки при работе с PHP кодом](../Rule/CodeHints.md)

## Стандарт выполнения задач

**ОБЯЗАТЕЛЬНО** Для всех задач в этой фазе используется единый workflow, описанный в документе **[TaskExecutionGuide.md](../Rule/TaskExecutionGuide.md)**.

**Включает**:
- Статусная модель и правила переходов между статусами
- Обязательные шаги для каждой задачи (тесты, фиксеры кода, проверки, документация)
- Контрольный список и примеры полного цикла задачи

**ОБЯЗАТЕЛЬНО** Перед тем как отметить задачу как выполненную, нужно убедиться, что все обязательные шаги выполнены.

---

## Domain Layer (Ticket Module)

### 1. [COMPLETED] Создать Entity для Ticket
- **Файл**: `backend/src/Ticket/Domain/Entity/Ticket.php`
- **Задача**: Определить структуру Entity соответствующую [Freshdesk API v2 Tickets](https://developers.freshdesk.com/api/#view_a_ticket)
- **Поля**: freshdesk_id, subject, description, description_text, type, status, priority, source, requester_id, responder_id, company_id, group_id, product_id, email, name, phone, custom_fields, tags, attachments (metadata), due_by, fr_due_by, created_at, updated_at
- **Требование**: Immutable Entity

### 2. [COMPLETED] Создать ValueObjects для Ticket
- **Файлы**:
  - `backend/src/Ticket/Domain/ValueObject/TicketId.php` (freshdesk_id)
  - `backend/src/Ticket/Domain/ValueObject/TicketStatus.php` (2:Open, 3:Pending, 4:Resolved, 5:Closed, 6:Waiting on Customer, 7:Waiting on Third Party)
  - `backend/src/Ticket/Domain/ValueObject/TicketPriority.php` (1:Low, 2:Medium, 3:High, 4:Urgent)
  - `backend/src/Ticket/Domain/ValueObject/TicketSource.php` (1:Email, 2:Portal, 3:Phone, 7:Chat и т.д.)
- **Требование**: Валидация и Immutable

### 3. [COMPLETED] Создать Domain интерфейсы для Ticket
- **Файл**: `backend/src/Ticket/Domain/TicketRepositoryInterface.php`
- **Методы**: save(Ticket): void, findById(int): ?Ticket, findByFreshdeskId(int): ?Ticket, delete(int): void

### 4. [COMPLETED] Создать DTO для входящих данных Ticket
- **Файлы**:
  - `backend/src/Ticket/Domain/Response/TicketResponse.php` (для выхода из UseCase)
  - `backend/src/Ticket/Domain/Response/TicketListResponse.php` (список tickets)

### 5. [COMPLETED] Создать Domain исключения для Ticket
- **Файл**: `backend/src/Ticket/Domain/Exception/`
- **Исключения**: TicketNotFound, InvalidTicketData

---

## Application Layer (Ticket Module)

### 6. [COMPLETED] Создать DTO для парсинга API ответов
- **Файл**: `backend/src/Ticket/Application/Dto/TicketApiDto.php`
- **Задача**: Структура для парсинга JSON из `GET /api/v2/tickets/{id}`
- **Требование**: Все поля из Freshdesk API

### 7. [COMPLETED] Создать DTO для листа tickets из API
- **Файл**: `backend/src/Ticket/Application/Dto/TicketListApiDto.php`
- **Задача**: Структура для парсинга JSON из `GET /api/v2/tickets?page=X` (пагинированная)

### 8. [COMPLETED] Создать Service для трансформации API данных в Domain Entity
- **Файл**: `backend/src/Ticket/Application/Service/TicketTransformer.php`
- **Методы**: transformFromApi(TicketApiDto): Ticket

### 9. [PENDING] Создать Query для получения списка ID задач
- **Файл**: `backend/src/Ticket/Application/Query/GetTicketListQuery.php`
- **Параметры**: page (int)

### 10. [PENDING] Создать Query Handler для GetTicketListQuery
- **Файл**: `backend/src/Ticket/Application/Query/GetTicketListQueryHandler.php`
- **Логика**: Вызов adapter.getTickets(page), возврат TicketListApiDto

### 11. [PENDING] Создать Query для получения полных данных одной задачи
- **Файл**: `backend/src/Ticket/Application/Query/GetTicketQuery.php`
- **Параметры**: freshdeskId (int)

### 12. [PENDING] Создать Query Handler для GetTicketQuery
- **Файл**: `backend/src/Ticket/Application/Query/GetTicketQueryHandler.php`
- **Логика**: Вызов adapter.getTicket(freshdeskId), возврат TicketApiDto

### 13. [PENDING] Создать Command для сохранения/обновления Ticket
- **Файл**: `backend/src/Ticket/Application/Command/SaveTicketCommand.php`
- **Параметры**: Ticket entity

### 14. [PENDING] Создать Command Handler для SaveTicketCommand
- **Файл**: `backend/src/Ticket/Application/Command/SaveTicketCommandHandler.php`
- **Логика**: Вызов repository.save(ticket), логирование

### 15. [PENDING] Создать UseCase для синхронизации всех Tickets
- **Файл**: `backend/src/Ticket/Application/UseCase/SyncTicketsUseCase.php`
- **Алгоритм**:
  1. Получить список всех ID через getTickets() с пагинацией (for all pages)
  2. Для каждого ID получить полные данные через getTicket()
  3. Трансформировать в Entity через TicketTransformer
  4. Сохранить через SaveTicketCommand
  5. Логировать прогресс

### 16. [COMPLETED] Создать Factory для создания Entity из DTO
- **Файл**: `backend/src/Ticket/Application/Factory/TicketFactory.php`
- **Методы**: createFromApiDto(TicketApiDto): Ticket

---

## Infrastructure Layer (Ticket Module)

### 17. [PENDING] Создать Eloquent Model для Ticket БД
- **Файл**: `backend/src/Ticket/Infrastructure/Persistence/EloquentTicketModel.php`
- **Таблица**: tickets (соответствует схеме из Concept.md)
- **Мутаторы**: custom_fields, tags, attachments (JSON)

### 18. [PENDING] Создать миграцию для таблицы tickets
- **Файл**: `backend/database/migrations/YYYY_MM_DD_create_tickets_table.php`
- **Колонки**: Все поля из Data Model (Concept.md)
- **Индексы**: freshdesk_id (UNIQUE), updated_at

### 19. [PENDING] Создать Repository реализацию для Ticket
- **Файл**: `backend/src/Ticket/Infrastructure/Persistence/DatabaseTicketRepository.php`
- **Методы**: save(Ticket), findById(int), findByFreshdeskId(int), delete(int)
- **Логика**: Преобразование Entity ↔ EloquentModel

### 20. [PENDING] Создать Adapter для Freshdesk API (Ticket)
- **Файл**: `backend/src/Ticket/Infrastructure/Adapter/FreshdeskTicketAdapter.php`
- **Методы**:
  - getTickets(int $page = 1): TicketListApiDto — `GET /api/v2/tickets?page={page}&per_page=100`
  - getTicket(int $freshdeskId): TicketApiDto — `GET /api/v2/tickets/{id}`
- **Требование**: Throttling (sleep(1) после каждого запроса), обработка 429 ошибок

### 21. [PENDING] Создать HTTP Client обертка для API
- **Файл**: `backend/src/Core/Infrastructure/Http/FreshdeskClient.php`
- **Методы**: request(method, endpoint, options): Response
- **Параметры**: Base URL, API ключ (из .env), Basic Auth

---

## Presentation Layer (Ticket Module)

### 22. [PENDING] Создать Console Command для синхронизации tickets
- **Файл**: `backend/src/Ticket/Presentation/Console/SyncTicketsCommand.php`
- **Сигнатура**: `php artisan freshdesk:sync`
- **Логика**:
  1. Валидация наличия API ключа в .env
  2. Вызов SyncTicketsUseCase
  3. Вывод прогресс-бара и результатов в консоль
  4. Обработка ошибок и их вывод
- **Output**: Success/Error сообщения, количество синхронизованных tickets

### 23. [PENDING] Создать Service Provider для Ticket модуля
- **Файл**: `backend/src/Ticket/Presentation/Config/TicketServiceProvider.php`
- **Регистрация**: Binding интерфейсов к реализациям в Service Container
  - TicketRepositoryInterface → DatabaseTicketRepository
  - FreshdeskTicketAdapter (singleton)
- **Регистрация команды**: SyncTicketsCommand

---

## Database & Configuration

### 24. [PENDING] Добавить Freshdesk API ключ в .env
- **Файл**: `backend/.env.example` и `backend/.env`
- **Переменная**: `FRESHDESK_API_KEY`, `FRESHDESK_API_DOMAIN` (digitalworlds.freshdesk.com)

### 25. [PENDING] Создать/обновить конфиг базы данных для тестов
- **Файл**: `backend/config/database.php`
- **Требование**: Отдельная SQLite БД для тестов

---

## Testing (Ticket Module)

### 26. [PENDING] Написать Unit тесты для Entity и ValueObjects
- **Путь**: `backend/tests/Suite/Ticket/Domain/`
- **Охват**: TicketId, TicketStatus, TicketPriority, TicketSource, Ticket Entity
- **Требование**: Проверка валидации и immutability

### 27. [COMPLETED] Написать Unit тесты для Service
- **Путь**: `backend/tests/Suite/Ticket/Application/Service/`
- **Охват**: TicketTransformer.transformFromApi()

### 28. [PENDING] Написать Functional тесты для Query Handlers
- **Путь**: `backend/tests/Suite/Ticket/Application/Query/`
- **Охват**: GetTicketListQueryHandler, GetTicketQueryHandler
- **Мок**: FreshdeskTicketAdapter

### 29. [PENDING] Написать Functional тесты для Command Handlers
- **Путь**: `backend/tests/Suite/Ticket/Application/Command/`
- **Охват**: SaveTicketCommandHandler
- **БД**: Тестовая SQLite

### 30. [PENDING] Написать Functional тесты для SyncTicketsUseCase
- **Путь**: `backend/tests/Suite/Ticket/Application/UseCase/`
- **Охват**: Полный цикл синхронизации
- **Мок**: FreshdeskTicketAdapter (тестовые данные)

### 31. [PENDING] Написать Integration тесты для Repository
- **Путь**: `backend/tests/Suite/Ticket/Infrastructure/Persistence/`
- **Охват**: save(), findById(), findByFreshdeskId(), delete()
- **БД**: Тестовая SQLite

### 32. [PENDING] Написать E2E тесты для SyncTicketsCommand
- **Путь**: `backend/tests/Suite/Ticket/Presentation/Console/`
- **Охват**: Полный жизненный цикл команды artisan freshdesk:sync
- **Проверка**: Exit code, консольный вывод, данные в БД

### 33. [PENDING] Написать Mock/Stub данных для тестов
- **Путь**: `backend/tests/Stub/`
- **Охват**: FreshdeskApiResponses, TicketFactory для тестов

---

## Code Quality & Verification

### 34. [PENDING] Запустить PHPStan на коде
- **Команда**: `make php-run CMD="vendor/bin/phpstan analyse"`
- **Цель**: 0 ошибок type checking

### 35. [PENDING] Запустить PHP_CodeSniffer на коде
- **Команда**: `make php-run CMD="vendor/bin/phpcs"`
- **Цель**: Соответствие стандартам из файла конфигурации

### 36. [PENDING] Запустить все тесты для Ticket модуля
- **Команда**: `make php-run CMD="vendor/bin/phpunit"`
- **Цель**: 75% тестов passed, хороший coverage

### 37. [PENDING] Проверить соблюдение архитектурных правил
- **Файл**: `backend/tests/Architecture/TicketArchitectureTest.php`
- **Проверки**:
  - Application НЕ использует Presentation/Infrastructure слои других модулей
  - Infrastructure НЕ использует Presentation слой
  - Все зависимости между слоями через интерфейсы

---

## Documentation

### 38. [PENDING] Написать PHPDoc комментарии для всех публичных методов
- **Охват**: Все классы, методы, параметры, return типы, @throws

### 39. [PENDING] Написать README.md для Ticket модуля
- **Содержание**: Описание модуля, примеры использования, структура

### 40. [PENDING] Обновить главный README.md проекта
- **Добавить**: Инструкции по запуску `php artisan freshdesk:sync`

---

## Дополнительное / Bugfixes

### 41. [PENDING] Проверить Docker setup и окружение
- **Задача**: Убедиться что все контейнеры поднимаются корректно
- **Команда**: `make install`, `make up`

### 42. [PENDING] Проверить подключение к Freshdesk API
- **Задача**: Тест реального API запроса (если доступен API ключ)
- **Fallback**: Используется мок-сервер если нет доступа

---

## Критерии завершения Phase 1

✅ **Все 42 задачи отмечены как [COMPLETED]**

✅ **Функциональность**:
- Консольная команда `php artisan freshdesk:sync` успешно синхронизирует tickets из Freshdesk
- Данные сохраняются в SQLite БД в соответствии со схемой
- Соблюдаются rate limits (1 сек между запросами)
- Обработка ошибок и 429 ответов

✅ **Качество кода**:
- PHPStan: 0 ошибок
- PHP_CodeSniffer: compliant
- Все тесты passing (Unit, Functional, Integration, E2E)

✅ **Архитектура**:
- Clean Architecture + CQRS + Modular Monolith соблюдены
- Все layer зависимости корректны
- Service Provider правильно регистрирует компоненты

✅ **Документация**:
- PHPDoc комментарии на всех методах
- README Ticket модуля
- Обновлен главный README
