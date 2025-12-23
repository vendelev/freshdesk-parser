# Концепция проекта: Freshdesk Backup Parser

## Обзор

Проект "Freshdesk Parser" — это консольное приложение для создания резервной копии задач (tickets) и вложений (attachments) из учетной записи Freshdesk на домене **digitalworlds.freshdesk.com**.

## Цель проекта

Разработать приложение, которое:
- Синхронизирует задачи и вложения из Freshdesk в локальную базу данных
- Обеспечивает безопасное хранение резервных копий
- Предоставляет интерфейс управления только через консольные команды
- Сохраняет полную структуру данных Freshdesk API в БД

## Основные компоненты системы

### 1. Backend (консольное приложение)
- **Технология**: Laravel 12.43
- **Язык**: PHP 8.5
- **Архитектура**: Clean Architecture + CQRS + Modular Monolith
- **Хранилище**: SQLite с миграциями
- **Контейнеризация**: Docker (multi-stage production builds)
- **Интерфейс**: Консольные команды (Artisan commands)

### 2. Freshdesk API интеграция
- Подключение к Freshdesk API для получения:
  - Задач (tickets)
  - Вложений (attachments)
  - Метаданных
- Обработка аутентификации и авторизации
- Обработка лимитов и throttling API

### 3. Хранилище данных
- **SQLite база данных** для локального хранения
- Сохранение задач с полной информацией
- Привязка вложений к задачам
- История синхронизации

## Архитектура приложения

Приложение следует принципам:

### Clean Architecture
- **Presentation Layer**: Консольные команды (Artisan commands)
- **Application Layer**: UseCase, CQRS (Command/Query), Service
- **Domain Layer**: Entity, ValueObject, интерфейсы, исключения
- **Infrastructure Layer**: Repository, Adapter (для Freshdesk API)

### CQRS (Command Query Responsibility Segregation)
- **Command**: Операции записи (создание, обновление резервных копий)
- **Query**: Операции чтения (получение задач, вложений)

### Modular Monolith
Модули по функциональности:
- **Ticket Module**: Синхронизация и хранение задач (tickets)
  - Adapter: FreshdeskTicketAdapter (работает с двумя API endpoints)
  - Repository: TicketRepository (сохранение в БД)
- **Attachment Module**: Управление вложениями
  - Adapter: FreshdeskAttachmentAdapter
  - Repository: AttachmentRepository
- **Sync Module**: История и статус синхронизации (опционально)

## Основные функции

### 1. Синхронизация задач

Процесс синхронизации состоит из двух этапов:

**Этап 1: Получение списка задач (постраничная загрузка)**
- API запрос: `GET /api/v2/tickets?page=1&per_page=100`
- Получение краткой информации по всем задачам с пагинацией
- Сохранение ID задач для дальнейшей загрузки полных данных
- Повторение для каждой страницы до конца списка

**Этап 2: Получение полных данных каждой задачи**
- API запрос: `GET /api/v2/tickets/{id}` для каждой задачи
- Получение полной структуры с custom_fields, attachments metadata и другими полями
- Парсинг JSON и сохранение в БД с полной структурой
- Обновление существующих задач или создание новых
- **Обязательно**: 1 секунда задержки между запросами (см. Rate Limiting)

**Оптимизация**:
- Отслеживание измененных задач (используя updated_at)
- Инкрементальная синхронизация - загрузка только новых или измененных задач
- Обработка ошибок и повторов при сбое
- Соблюдение rate limit через throttling (минимум 1 сек между запросами)

### 2. Управление вложениями
- Скачивание вложений из Freshdesk через `attachment_url` из API
- Локальное хранение файлов в `backend/storage/attachments/`
- Привязка вложений к соответствующим задачам
- Управление размером и хранилищем
- **Обязательно**: 1 секунда задержки между скачиванием вложений (throttling)

### 3. Консольные команды
- `php artisan freshdesk:sync` - Синхронизация всех задач
- `php artisan freshdesk:sync {--ticket-id=}` - Синхронизация конкретной задачи
- `php artisan freshdesk:sync-attachments` - Загрузка вложений
- `php artisan freshdesk:clear` - Очистка локальных данных
- `php artisan freshdesk:status` - Статус синхронизации

## Технические характеристики

### Stack технологий
| Компонент | Технология | Версия |
|-----------|-----------|--------|
| **Язык** | PHP | 8.5 |
| **Фреймворк** | Laravel | 12.43 |
| **БД** | SQLite | - |
| **HTTP клиент** | Guzzle HTTP | 7.10 |
| **Логирование** | Monolog | 3.9 |
| **Тестирование** | PHPUnit | 12.5 |
| **Анализ кода** | PHPStan, PHPCodeSniffer | - |

### Зависимости проекта
- **guzzlehttp/guzzle** - HTTP клиент для работы с Freshdesk API
- **monolog/monolog** - Логирование операций синхронизации
- **larastan/larastan** - Статический анализ кода
- **phpunit/phpunit** - Unit и integration тесты

## Качество кода

### Проверки качества
- **PHPStan** - Type checking и статический анализ
- **PHPCodeSniffer** - Проверка кодстиля и PSR-12 стандартов
- **Rector** - Автоматическая рефакторизация
- **Dependency Analyser** - Анализ зависимостей

### Тестирование
- **Unit тесты**: Тестирование отдельных компонентов
- **Integration тесты**: Тестирование с БД
- **E2E тесты**: Полный жизненный цикл API запросов
- **Architecture тесты**: Проверка соблюдения архитектурных правил

## Data Model

Структура таблиц БД соответствует JSON структуре данных [Freshdesk API v2 (Tickets)](https://developers.freshdesk.com/api/#view_a_ticket).

### Основные таблицы

#### tickets (задачи)

Таблица сохраняет полную структуру объекта Ticket из [Freshdesk API v2](https://developers.freshdesk.com/api/#view_a_ticket):

```
- id (Primary Key - локальная БД, автоинкремент)
- freshdesk_id (INTEGER, UNIQUE) - ID задачи в Freshdesk системе

Основные поля:
- subject (TEXT)
- description (TEXT, nullable) - HTML версия описания
- description_text (TEXT, nullable) - текстовая версия описания (HTML удален)
- type (VARCHAR, nullable) - тип задачи (ticket, incident, problem, change_request)

Статусы и приоритеты:
- status (INTEGER) - 2:Open, 3:Pending, 4:Resolved, 5:Closed, 6:Waiting on Customer, 7:Waiting on Third Party
- priority (INTEGER) - 1:Low, 2:Medium, 3:High, 4:Urgent
- source (INTEGER) - 1:Email, 2:Portal, 3:Phone, 7:Chat, 9:Mobihelp, 10:Feedback Widget, 11:Outbound Email

Идентификаторы контактов и групп:
- requester_id (INTEGER) - контакт, создавший задачу
- responder_id (INTEGER, nullable) - агент, ответивший первым
- company_id (INTEGER, nullable) - компания контакта
- group_id (INTEGER, nullable) - группа агентов, обслуживающая задачу
- product_id (INTEGER, nullable) - связанный продукт

Контактная информация:
- email (VARCHAR, nullable) - email адрес requester
- name (VARCHAR, nullable) - имя requester
- phone (VARCHAR, nullable) - телефон requester
- facebook_id (VARCHAR, nullable) - Facebook ID requester
- twitter_id (VARCHAR, nullable) - Twitter ID requester

Email маршрутизация:
- cc_emails (JSON) - массив email на копии
- to_emails (JSON) - массив email получателей
- fwd_emails (JSON) - массив email при пересылке
- reply_cc_emails (JSON) - массив email для ответа на копию
- email_config_id (INTEGER, nullable) - конфиг входящей почты

Состояние:
- is_escalated (BOOLEAN) - задача эскалирована
- spam (BOOLEAN) - помечено как спам
- deleted (BOOLEAN) - помечено на удаление

Дополнительные данные:
- custom_fields (JSON) - пользовательские поля
- tags (JSON) - массив тегов
- attachments (JSON, nullable) - список вложений (metadata из API)

Сроки и SLA:
- due_by (DATETIME, nullable) - срок решения задачи
- fr_due_by (DATETIME, nullable) - первый ответ по SLA
- is_overdue (BOOLEAN) - задача просрочена (текущее время > due_by)
- fr_escalated (BOOLEAN, nullable) - эскалация первого ответа
- sla_policy_id (INTEGER, nullable) - применяемая SLA политика

Время:
- created_at (DATETIME) - создание задачи в Freshdesk
- updated_at (DATETIME) - последнее обновление в Freshdesk
- synced_at (DATETIME) - время последней синхронизации в нашу БД
```

#### attachments (вложения)

Таблица сохраняет данные о вложениях из [Freshdesk API v2 Conversations](https://developers.freshdesk.com/api/#list_all_conversations_of_a_ticket):

```
- id (PRIMARY KEY - локальная БД, автоинкремент)
- freshdesk_id (INTEGER, UNIQUE) - ID вложения в Freshdesk

Связи:
- ticket_id (INTEGER, Foreign Key -> tickets.id) - связь на задачу в локальной БД
- freshdesk_ticket_id (INTEGER) - ID задачи в Freshdesk

Метаданные файла:
- name (TEXT) - оригинальное имя файла
- file_type (TEXT) - MIME тип файла (например, "application/pdf", "image/jpeg")
- size (INTEGER) - размер в байтах

Источник:
- attachment_url (TEXT) - URL для скачивания из Freshdesk API
- created_at (DATETIME) - время добавления вложения в Freshdesk

Локальное хранилище:
- local_path (TEXT, nullable) - путь к локальному файлу
- downloaded_at (DATETIME, nullable) - время успешной загрузки локально
- download_status (VARCHAR) - статус загрузки (pending, success, failed)
```

#### sync_history (история синхронизации)

```
- id (Primary Key)
- entity_type (VARCHAR) - 'ticket', 'attachment'
- freshdesk_id (INTEGER)
- operation (VARCHAR) - 'create', 'update', 'delete'
- status (VARCHAR) - 'pending', 'success', 'failed'
- error_message (TEXT, nullable)
- started_at (DATETIME)
- completed_at (DATETIME, nullable)
```

## Workflow разработки

При добавлении новой функции следуйте процессу:

1. **Domain Layer** - определите сущности, value objects, интерфейсы, исключения
2. **Application Layer** - реализуйте UseCase, CQRS операции (Command/Query), Service
3. **Infrastructure Layer** - создайте Repository и Adapter для Freshdesk API
4. **Presentation Layer** - добавьте консольную команду (Artisan Command)
5. **Tests** - напишите Unit, Integration и функциональные тесты

## Архитектура работы с Freshdesk API

### Двухступенчатый процесс синхронизации

```
┌─────────────────┐
│ Artisan Command │
└────────┬────────┘
         │
    ┌────▼──────────────────────────┐
    │   SyncTicketsUseCase          │
    │  (Application слой)           │
    └────┬───────────────┬──────────┘
         │               │
    ┌────▼────────┐  ┌──▼──────────────────┐
    │  FreshDesk  │  │ TicketRepository    │
    │   Adapter   │  │ (Infrastructure)    │
    │ (Infrastructure) │                    │
    └────┬──────────┘  └──────────────────┘
         │
    ┌────▼──────────────────────────┐
    │  GET /api/v2/tickets?page=X   │ (1. Список ID)
    │  GET /api/v2/tickets/{id}     │ (2. Полные данные)
    └──────────────────────────────┘
```

### FreshdeskTicketAdapter

Интегрирует два типа запросов API:

**Метод `getTickets(int $page = 1): TicketListDto`**
- Запрос к `GET /api/v2/tickets?page={page}&per_page=100`
- Возвращает список ID и краткие данные (updated_at для инкрементальной загрузки)

**Метод `getTicket(int $freshdeskId): TicketDto`**
- Запрос к `GET /api/v2/tickets/{id}` или `GET /api/v2/tickets/{id}?include=conversations,stats`
- Возвращает полную структуру Ticket со всеми стандартными полями
- Дополнительно можно запросить:
  - `include=conversations` - получить conversations (обсуждения/комментарии)
  - `include=stats` - получить статистику ticket
  - `include=requester` - получить полные данные requester контакта

### UseCase - SyncTicketsUseCase

Координирует процесс синхронизации с соблюдением rate limits:

```php
1. Получить список всех ID через getTickets() с пагинацией
   - Для каждой страницы: запрос + 1 сек задержка (throttle)
2. Для каждого ID получить полные данные через getTicket()
   - После каждого запроса: 1 сек задержка
   - При 429 ошибке: прочитать Retry-After и ждать
3. Трансформировать DTO в Entity
4. Сохранить через TicketRepository::save()
5. Логировать прогресс в sync_history с информацией о Rate Limit headers
```

**Важно**: Adapter должен автоматически вызывать `throttle()` после каждого HTTP запроса для соблюдения лимитов API.

### TicketRepository

Отвечает за persisten layer:

```php
- save(Ticket $ticket): void  // INSERT или UPDATE
- findById(int $id): ?Ticket
- findByFreshdeskId(int $freshdeskId): ?Ticket
- delete(int $id): void
```

## Архитектура консольных команд

### Структура Artisan Commands

Все команды наследуют базовый класс Laravel Command и реализуют следующую структуру:

```php
namespace Parser\{Module}\Presentation\Console;

class {ModuleAction}Command extends Command
{
    protected $signature = 'freshdesk:{action} {options?}';
    protected $description = 'Описание команды';
    
    public function __construct(private UseCase $useCase) {}
    
    public function handle()
    {
        // 1. Парсинг аргументов команды
        // 2. Валидация входных данных
        // 3. Вызов UseCase
        // 4. Вывод результата в консоль
    }
}
```

### Примеры команд

**SyncTicketsCommand**:
- Получает параметры (диапазон ID, фильтры)
- Вызывает `SyncTicketsUseCase`
- Выводит прогресс и результаты

**SyncAttachmentsCommand**:
- Получает параметры (ticket_id, limit)
- Вызывает `SyncAttachmentsUseCase`
- Скачивает файлы и выводит статус

### Output & Feedback

- **Progress Bar**: Для долгих операций
- **Table Output**: Для вывода результатов
- **Success/Error Messages**: Для информирования пользователя
- **Logging**: Все операции логируются через Monolog

## DevOps и развертывание

### Docker
- **php-dev контейнер** - разработка и тестирование
- **Multi-stage builds** - оптимизированные production образы
- **docker-compose** - локальное окружение разработки

### Makefile команды
```bash
make install    # Полная установка и инициализация
make up         # Запуск контейнеров
make down       # Остановка контейнеров
make php-cli    # Bash в контейнере
make php-run    # Выполнить команду в контейнере
```

## Безопасность

- API ключ Freshdesk хранится в переменных окружения (.env)
- Вложения скачиваются через HTTPS
- Валидация входных данных на всех уровнях
- Статический анализ кода (PHPStan) для выявления потенциальных уязвимостей
- Периодическая проверка безопасности через `roave/security-advisories`

## Этапы разработки

### Phase 1: MVP (Minimum Viable Product)
- Базовая синхронизация задач через консольную команду
- Сохранение полной структуры Ticket из Freshdesk API в SQLite
- Консольная команда для запуска синхронизации
- Основные модули структуры (Parser, Sync)

### Phase 2: Расширение
- Синхронизация вложений
- Инкрементальная синхронизация (обновление только измененных задач)
- Улучшенные консольные команды с фильтрацией по статусу, приоритету
- Логирование и отчетность синхронизации

### Phase 3: Оптимизация
- Асинхронная обработка больших объемов данных (очередь)
- Кэширование метаданных
- Экспорт резервных копий в JSON/CSV форматы
- Восстановление из резервной копии

## Freshdesk API Reference

Все структуры данных и поля таблиц соответствуют официальной документации:
- **[Freshdesk API v2 Documentation](https://developers.freshdesk.com/api/)**

### API Endpoints для синхронизации

| Операция | Endpoint | Метод | Описание |
|----------|----------|-------|---------|
| Список задач (пагинированно) | `/api/v2/tickets` | GET | [List All Tickets](https://developers.freshdesk.com/api/#list_all_tickets) - краткая информация со статусом, приоритетом, ID и основными полями |
| Полные данные задачи | `/api/v2/tickets/{id}` | GET | [View a Ticket](https://developers.freshdesk.com/api/#view_a_ticket) - полная структура с custom_fields, metadata, истории и т.д. |
| Вложения задачи | `/api/v2/tickets/{id}/conversations` | GET | Получение вложений через conversations endpoint |
| Загрузка вложения | `{attachment_url}` | GET | Скачивание файла по URL из attachment metadata |

### Параметры API

- **Base URL**: `https://digitalworlds.freshdesk.com/api/v2/`
- **Аутентификация**: Basic Auth (API ключ : пусто)
- **Pagination**: `page` и `per_page` параметры (max 100 на странице)
- **Фильтры**: `order_by`, `order_type`, `updated_since` для инкрементальной синхронизации

### Rate Limiting и Throttling

**Ограничения Freshdesk API**:

| План | Запросов/минуту | List Tickets | Get Ticket |
|------|--------|--------|--------|
| Growth | 200 | 20 | ∞ |
| Pro | 400 | 100 | ∞ |
| Enterprise | 700 | 200 | ∞ |

**Стратегия throttling (обязательно реализовать)**:

1. **Задержка между запросами**: 
   - Минимум **1 секунда** между каждым HTTP запросом к API
   - Это обеспечивает макс 60 запросов/минуту, что безопасно для всех планов
   - Реализовать через `sleep(1)` после каждого запроса

2. **Обработка 429 (Too Many Requests)**:
   - При получении HTTP 429, нужно читать заголовок `Retry-After`
   - Ждать указанное время (обычно несколько секунд)
   - Повторить запрос

3. **Мониторинг лимитов**:
   - Проверять заголовки ответа:
     - `X-RateLimit-Total` - всего запросов в минуту
     - `X-RateLimit-Remaining` - осталось запросов
     - `X-RateLimit-Used-CurrentRequest` - запросов в текущем запросе
   - Логировать прогресс синхронизации

4. **Оптимизация**:
   - Использовать пагинацию (max 100 tickets/page)
   - Для получения данных одной задачи нет лимита на количество запросов
   - Запрашивать только нужные поля через `include` параметр

**Реализация в FreshdeskTicketAdapter**:

```php
private function throttle(): void
{
    // Задержка 1 сек между запросами
    usleep(1_000_000); // 1 000 000 микросекунд = 1 секунда
}

private function handleRateLimit(Response $response): void
{
    if ($response->getStatusCode() === 429) {
        $retryAfter = (int)($response->getHeaderLine('Retry-After') ?: 60);
        $this->logger->warning("Rate limit reached, waiting {$retryAfter} seconds");
        sleep($retryAfter);
    }
}
```

## Документирование

- **PHP Doc** - Документация функций и классов
- **Architecture.md** - Архитектурные решения
- **README.md** - Инструкции по установке и использованию
- **Concept.md** - Этот документ с описанием концепции
