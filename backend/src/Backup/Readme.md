# Модуль Backup

Модуль отвечает за организацию резервного копирования списка всех задач (tickets) из Freshdesk в локальное хранилище. Обеспечивает защиту от потери данных и создание полного снимка состояния всех задач.

## 1. Архитектура и структура модуля

Модуль реализует **Clean Architecture** с четкой разделением на слои в соответствии с правилами проекта.

### Диаграмма архитектуры

```
┌─────────────────────────────────────────────────────────────┐
│ PRESENTATION (Точка входа и выхода)                        │
│ ┌──────────────────────────────────────────────────────┐    │
│ │ BackupTicketsCommand (Console)                       │    │
│ │ - Инициирует процесс создания бекапа                │    │
│ │ - Выводит результат в консоль                       │    │
│ └──────────────────────────────────────────────────────┘    │
└──────────────────────────────────┬──────────────────────────┘
                                   │
┌──────────────────────────────────▼──────────────────────────┐
│ APPLICATION (Бизнес-логика)                                │
│ ┌──────────────────────────────────────────────────────┐    │
│ │ CreateBackupUseCase                                  │    │
│ │ - Координирует получение задач из API               │    │
│ │ - Формирует структуру бекапа с метаданными          │    │
│ │ - Координирует сохранение в файловую систему        │    │
│ │ - Обрабатывает ошибки и возвращает результат       │    │
│ └──────────────────────────────────────────────────────┘    │
└──┬──────────────────────────────────────────────────────┬───┘
   │                                                      │
   ▼                                                      ▼
┌──────────────────────────────┐    ┌───────────────────────────┐
│ INFRASTRUCTURE               │    │ INFRASTRUCTURE            │
│ (Получение данных)          │    │ (Сохранение данных)       │
│ ┌──────────────────────────┐ │    │ ┌─────────────────────┐   │
│ │ FreshdeskHttpClient      │ │    │ │ FileBackupStorage   │   │
│ │ Adapter                  │ │    │ │ Adapter             │   │
│ │ - Подключение к API      │ │    │ │ - Запись JSON в    │   │
│ │ - Получение задач       │ │    │ │   файловую систему  │   │
│ │ - Обработка пагинации   │ │    │ │ - Создание директ. │   │
│ │ - Обработка ошибок      │ │    │ │ - Обработка ошибок │   │
│ │ - Получение деталей     │ │    │ │ - Сохранение       │   │
│ │   задач (getTicketDetail)│ │    │ │   метаданных       │   │
│ └──────────────────────────┘ │    │ └─────────────────────┘   │
└──────────────────────────────┘    └───────────────────────────┘
           │                                    │
           ▼                                    ▼
    Freshdesk API                        Файловая система
           │                                    │
           └── ИСПОЛЬЗУЕТСЯ МОДУЛЕМ TICKETDETAIL ─┘
```

### Структура каталогов

```
backend/src/Backup/
├── Domain/
│   ├── FreshdeskClientInterface.php          # Контракт для получения задач
│   ├── BackupStorageInterface.php            # Контракт для сохранения
│   ├── Response/
│   │   └── BackupResponse.php                # DTO результата операции
│   └── Exception/
│       ├── FreshdeskApiException.php         # Базовое исключение API
│       ├── FreshdeskApiConnectionException.php
│       ├── FreshdeskApiRateLimitException.php
│       ├── FreshdeskApiUnauthorizedException.php
│       ├── BackupStorageException.php        # Базовое исключение хранилища
│       ├── BackupStorageWriteException.php
│       └── BackupOperationFailedException.php
├── Application/
│   └── UseCase/
│       └── CreateBackupUseCase.php           # Основная бизнес-логика
├── Infrastructure/
│   └── Adapter/
│       ├── FreshdeskHttpClientAdapter.php    # Реализация получения из API
│       └── FileBackupStorageAdapter.php      # Реализация сохранения в файл
└── Presentation/
    ├── Console/
    │   └── BackupTicketsCommand.php          # Консольная команда
    └── Config/
        ├── BackupServiceProvider.php         # Регистрация в DI контейнере
        └── freshdesk.php                     # Конфигурация модуля
```

## 2. Описание предметной области (Domain)

Domain слой определяет контракты и исключения для взаимодействия с модулем.

### Интерфейсы (контракты)

#### FreshdeskClientInterface

Контракт для получения задач из Freshdesk API в виде генератора.

```php
/**
 * @return Generator<int, string, null, null> Генератор сырых JSON строк с задачами
 * @throws FreshdeskApiConnectionException
 * @throws FreshdeskApiRateLimitException
 * @throws FreshdeskApiUnauthorizedException
 */
public function getTicketsIterator(): Generator;

/**
 * Получить детальную информацию о задаче по ID
 *
 * @param int $ticketId ID задачи
 * @return string Сырой JSON с детальной информацией задачи
 * @throws FreshdeskApiConnectionException
 * @throws FreshdeskApiRateLimitException
 * @throws FreshdeskApiUnauthorizedException
 */
public function getTicketDetail(int $ticketId): string;
```

**Ответственность**: 
- Предоставить генератор, который итеративно выдает JSON строки с задачами (по одной странице за раз)
- Получить детальную информацию о конкретной задаче с полным набором данных, включая conversations

#### BackupStorageInterface

Контракт для сохранения данных бекапа в хранилище.

```php
/**
 * @param string $ticketsJson Сырая JSON строка с задачами
 * @throws BackupStorageWriteException
 */
public function savePage(string $ticketsJson, string $backupId, int $pageNumber): void;

/**
 * @param array<string, mixed> $metadata
 * @throws BackupStorageWriteException
 */
public function saveMetadata(array $metadata, string $backupId): void;
```

**Ответственность**: Определить способ сохранения отдельных страниц бекапа и метаданных.

### DTO (Структуры данных)

#### BackupResponse

Результат выполнения операции создания бекапа.

```php
final readonly class BackupResponse {
    public function __construct(
        public string $status,                // 'success' или 'error'
        public string $message,               // Сообщение о результате
        public ?int $totalTickets = null,     // Количество задач (при успехе)
        public ?string $filePath = null,      // ID бекапа (при успехе)
        public ?string $errorDetails = null,  // Детали ошибки (при ошибке)
    ) {}
}
```

### Исключения

Модуль определяет иерархию исключений для различных типов ошибок:

```
Exception
├── FreshdeskApiException (базовое для всех API ошибок)
│   ├── FreshdeskApiConnectionException    # Ошибка подключения
│   ├── FreshdeskApiRateLimitException     # Rate limiting (429)
│   └── FreshdeskApiUnauthorizedException  # Ошибка авторизации (401)
├── BackupStorageException (базовое для всех ошибок хранилища)
│   └── BackupStorageWriteException        # Ошибка записи файла
└── BackupOperationFailedException         # Общая ошибка операции
```

## 3. Описание реализации бизнес-логики (Application)

### CreateBackupUseCase

Основной класс, реализующий вариант использования "создание бекапа".

**Ответственность**: Координировать получение задач из API в виде генератора и сохранение страниц в хранилище с отслеживанием прогресса.

**Входные зависимости** (внедрение через конструктор):
- `FreshdeskClientInterface` — получение генератора задач
- `BackupStorageInterface` — сохранение страниц в хранилище

**Метод `execute(?BackupProgressInterface $progress = null): BackupResponse`**:

1. **Инициализация**:
   - Генерирование ID бекапа: `Y-m-d_His` формат (например, `2025-01-15_103000`)
   - Инициализация счетчика задач: `$totalTickets = 0`
   - Инициализация счетчика страниц: `$pageNumber = 1`

2. **Итеративное получение и сохранение страниц**:
   - Вызов `FreshdeskClientInterface::getTicketsIterator()` — получает генератор
   - Для каждой страницы (JSON строки из генератора):
     - Вызов `BackupStorageInterface::savePage($ticketsJson, $backupId, $pageNumber)` — сохраняет страницу
     - Декодирование JSON для подсчета задач на странице
     - Обновление общего счетчика: `$totalTickets += $ticketsCount`
     - Опционально: вызов `BackupProgressInterface::reportPageSaved()` для отчета о прогрессе
     - Инкремент номера страницы

3. **Сохранение метаданных**:
   ```php
   $metadata = [
       'created_at' => '2025-01-15T10:30:00Z',    // ISO 8601 формат
       'total_tickets' => 1234,                    // Общее количество задач
       'total_pages' => 12,                        // Количество страниц
   ]
   ```
   - Вызов `BackupStorageInterface::saveMetadata($metadata, $backupId)` — сохраняет метаданные

4. **Возврат результата**:
   - При успехе: `BackupResponse` со статусом `success`, количеством задач и ID бекапа
   - При ошибке API: перехвата `FreshdeskApiException`, возврат ошибки с описанием
   - При ошибке хранилища: перехват `BackupOperationFailedException`, возврат ошибки
   - При прочих ошибках: перехват `\Throwable`, возврат общей ошибки

**Обработка ошибок**:
- Исключения API преобразуются в сообщение "Ошибка подключения к Freshdesk API"
- Исключения хранилища преобразуются в сообщение "Ошибка записи на диск"
- Прочие исключения преобразуются в "Неизвестная ошибка при создании бекапа"

## 4. Документация API интерфейсов (Presentation)

### BackupTicketsCommand

Консольная команда для инициирования процесса создания бекапа всех задач Freshdesk.

**Имя команды**: `backup:tickets`

**Синтаксис**:
```bash
php artisan backup:tickets
php artisan backup:tickets --help
```

**Параметры**:
- `--help` — встроенный флаг Laravel для вывода справки по команде

**Процесс выполнения** (метод `handle()`):

1. Вывод в консоль: "Начало создания бекапа..."
2. Создание экземпляра `ConsoleBackupProgress` для отслеживания прогресса
3. Вызов `CreateBackupUseCase::execute($progress)` с передачей слушателя прогресса
4. Проверка статуса в `BackupResponse`:

   **При успехе**:
   ```
   Начало создания бекапа...
     → Страница 1: 100 задач сохранено
     → Страница 2: 100 задач сохранено
     → Страница 3: 34 задачи сохранено

   ✓ Бекап создан успешно
     Задач: 234
     Путь: backup_2025-01-15_103000
   ```

   **При ошибке**:
   ```
   Начало создания бекапа...
   
   ✗ Ошибка подключения к Freshdesk API
     Подробности: Не удалось подключиться к Freshdesk API
   ```

5. Возврат exit code:
   - `self::SUCCESS` (0) — успешное выполнение
   - `self::FAILURE` (1) — ошибка при выполнении

**Пример использования**:
```bash
# Выполнить бекап
make php-run CMD="php artisan backup:tickets"

# Получить справку
make php-run CMD="php artisan backup:tickets --help"
```

## 5. Интеграция с внешними системами (Infrastructure)

### FreshdeskHttpClientAdapter

Адаптер для получения задач из Freshdesk API через HTTP.

**Реализует**: `FreshdeskClientInterface`

**Внедряемые зависимости**:
- Guzzle `Client` — HTTP клиент для запросов
- Конфигурация (API ключ, домен) — через DI контейнер

**Метод `getTicketsIterator(): Generator`**:

1. **Инициализация**:
   - Получение API ключа и домена из конфигурации
   - Инициализация счетчика попыток и задержки

2. **Получение первой страницы**:
   - GET запрос: `https://{domain}.freshdesk.com/api/v2/tickets?page=1&per_page=100`
   - Заголовки: `Authorization: Basic {base64(api_key:)}`

3. **Итеративное получение всех страниц**:
   - Проверка наличия следующей страницы (`meta.next_page`)
   - **Задержка 1 сек между запросами** (`sleep(1)`)
   - Повторение запроса для каждой страницы

4. **Обработка ошибок**:
   - HTTP 401 → `FreshdeskApiUnauthorizedException` (неверный API ключ)
   - HTTP 429 → `FreshdeskApiRateLimitException` (лимит превышен)
   - Ошибки подключения → `FreshdeskApiConnectionException`

5. **Retry логика** для rate limiting и временных ошибок:
   - Exponential backoff: 1 сек, 2 сек, 4 сек
   - Максимум 3 попытки

6. **Возврат**: Генератор JSON строк с задачами

**Метод `getTicketDetail(int $ticketId): string`**:

1. **Получение детальной информации**:
   - GET запрос: `https://{domain}.freshdesk.com/api/v2/tickets/{ticketId}`
   - Заголовки: `Authorization: Basic {base64(api_key:)}`

2. **Обработка conversations**:
   - Проверка наличия поля `conversations` в основном ответе
   - Если conversations есть, получение ВСЕХ conversations через пагинацию
   - Запросы: `https://{domain}.freshdesk.com/api/v2/tickets/{ticketId}/conversations?page=N&per_page=100`
   - Объединение всех страниц conversations в единый массив
   - Замена conversations в основном ответе полным массивом

3. **Retry логика** (аналогично getTicketsIterator):
   - Exponential backoff для rate limiting
   - Максимум 3 попытки

4. **Обработка ошибок**:
   - HTTP 404 → `FreshdeskApiConnectionException` (задача не найдена)
   - HTTP 401 → `FreshdeskApiUnauthorizedException` (неверный API ключ)
   - HTTP 429 → `FreshdeskApiRateLimitException` (лимит превышен)
   - Ошибки подключения → `FreshdeskApiConnectionException`

5. **Graceful degradation**:
   - При ошибках получения conversations продолжает с основными данными
   - Не блокирует сохранение основной информации задачи

6. **Возврат**: Полный JSON с детальной информацией задачи и всеми conversations

### FileBackupStorageAdapter

Адаптер для сохранения бекапа в локальную файловую систему.

**Реализует**: `BackupStorageInterface`

**Внедряемые зависимости**:
- Конфигурация (путь к директории хранилища) — через DI контейнер

**Метод `save(array $data, string $filename): void`**:

1. **Подготовка директории**:
   - Получение пути из конфигурации (по умолчанию: `storage/backups`)
   - Проверка существования директории
   - Создание директории если необходимо (рекурсивно)

2. **Сохранение файла**:
   - JSON кодирование данных с опциями:
     - `JSON_PRETTY_PRINT` — читаемый формат
     - `JSON_UNESCAPED_UNICODE` — сохранение символов Unicode
   - Запись в файл: `{path}/backup_YYYY-MM-DD_HHmmss.json`
   - Перезапись файла если существует (замена предыдущего бекапа)

3. **Обработка ошибок**:
   - Недостаточно прав → `BackupStorageWriteException`
   - Диск заполнен → `BackupStorageWriteException`
   - Другие ошибки записи → `BackupStorageWriteException`

## 6. Зависимости

### Внешние зависимости

- **Freshdesk API** (`https://developers.freshdesk.com/api/`) — источник данных о задачах
  - Требует валидный API ключ с правами на чтение
  - Версия API: v2
  -限制: Rate limit в зависимости от плана

### Внутренние зависимости (модули проекта)

- **Core** — нет прямой зависимости, но можно использовать общие исключения или утилиты
- **TicketDetail Module** — использует `FreshdeskClientInterface::getTicketDetail()` для получения детальной информации задач

### Интеграция с модулем TicketDetail

Модуль Backup предоставляет функциональность получения детальной информации о задачах для модуля TicketDetail:

- **Используемый интерфейс**: `FreshdeskClientInterface::getTicketDetail(int $ticketId): string`
- **Цель**: Получение полной информации о задаче, включая conversations с пагинацией
- **Механизм**: Модуль TicketDetail использует адаптер для делегирования вызовов к Backup модулю
- **Преимущества**: 
  - Повторное использование логики работы с Freshdesk API
  - Централизованная обработка ошибок и retry механизмов
  - Согласованность в работе с внешним API

### Внешние библиотеки PHP

- **Guzzle** (встроена в Laravel) — HTTP клиент для взаимодействия с Freshdesk API
- **Laravel** — фреймворк, обеспечивает DI контейнер, консольные команды, конфигурацию

### Переменные окружения

Модуль требует следующих переменных в файле `.env`:

```env
# API Freshdesk
FRESHDESK_API_KEY=your_freshdesk_api_key_here
FRESHDESK_DOMAIN=your_company_domain

# Хранилище бекапов
BACKUP_STORAGE_PATH=storage/backups
```

**Описание**:
- `FRESHDESK_API_KEY` — API ключ для авторизации в Freshdesk (✅ защищено в .env, не в логах)
- `FRESHDESK_DOMAIN` — поддомен Freshdesk для формирования URL API
- `BACKUP_STORAGE_PATH` — путь к директории для сохранения бекапов (относительно корня проекта)

## 8. Тестирование модуля

### Расположение тестов

Все тесты модуля находятся в `backend/tests/Suite/Backup/` согласно структуре:

```
backend/tests/Suite/Backup/
├── Application/
│   └── UseCase/
│       └── CreateBackupUseCaseTest.php          # Функциональные тесты Use Case
├── Infrastructure/
│   ├── FreshdeskHttpClientAdapterTest.php       # Integration тесты API адаптера
│   └── FileBackupStorageAdapterTest.php         # Integration тесты хранилища
└── Presentation/
    └── BackupTicketsCommandTest.php              # E2E тесты консольной команды
```

### Типы тестов

#### Unit/Functional тесты Application слоя

**Файл**: `tests/Suite/Backup/Application/UseCase/CreateBackupUseCaseTest.php`

Тестирование бизнес-логики UseCase с мокированными зависимостями:

- ✅ Успешное создание бекапа с корректным количеством задач
- ✅ Обработка ошибки подключения к API
- ✅ Обработка ошибки авторизации (неверный API ключ)
- ✅ Обработка ошибки записи на диск
- ✅ Корректное формирование структуры бекапа с метаданными

#### Integration тесты Infrastructure слоя

**Файл**: `tests/Suite/Backup/Infrastructure/FreshdeskHttpClientAdapterTest.php`

Тестирование взаимодействия с Freshdesk API (с mock HTTP клиентом):

- ✅ Успешное получение одной страницы задач
- ✅ Пагинация с несколькими страницами (проверка задержки 1 сек между запросами)
- ✅ Обработка rate limiting (HTTP 429) с retry логикой
- ✅ Обработка ошибок подключения
- ✅ Обработка ошибок авторизации (HTTP 401)

**Файл**: `tests/Suite/Backup/Infrastructure/FileBackupStorageAdapterTest.php`

Тестирование сохранения бекапа в файловую систему:

- ✅ Успешная запись файла с валидным JSON
- ✅ Перезапись существующего файла
- ✅ Автоматическое создание директории
- ✅ Обработка ошибок при записи (недостаточно прав, диск заполнен)

#### E2E тесты Presentation слоя

**Файл**: `tests/Suite/Backup/Presentation/BackupTicketsCommandTest.php`

Тестирование полного потока от консольной команды до результата:

- ✅ Успешное выполнение команды с корректным выводом
- ✅ Обработка ошибок при выполнении команды
- ✅ Вывод справки при флаге `--help`
- ✅ Проверка exit code (0 при успехе, 1 при ошибке)

### Запуск тестов

```bash
# Все тесты модуля Backup
make php-run CMD="php vendor/bin/phpunit tests/Suite/Backup"

# Отдельно по типам
make php-run CMD="php vendor/bin/phpunit tests/Suite/Backup/Application"
make php-run CMD="php vendor/bin/phpunit tests/Suite/Backup/Infrastructure"
make php-run CMD="php vendor/bin/phpunit tests/Suite/Backup/Presentation"
```

### Проверка качества кода

```bash
# PHPStan — статический анализ типов
make php-run CMD="php vendor/bin/phpstan analyse src/Backup"

# PHP CodeSniffer — проверка стиля кода
make php-run CMD="php vendor/bin/phpcs src/Backup"
```

## 9. Сценарии использования

### Сценарий 1: Регулярное резервное копирование

**Цель**: Администратор выполняет регулярное резервное копирование всех задач из Freshdesk.

**Процесс**:
1. Администратор выполняет команду: `php artisan backup:tickets`
2. Система получает все задачи из Freshdesk API с обработкой пагинации
3. Система сохраняет полный снимок задач в JSON файл
4. Администратор видит сообщение об успехе с информацией о бекапе

**Результат**:
- Создан файл: `storage/backups/backup_2025-01-15_103000.json`
- Файл содержит метаданные и все задачи
- Предыдущий бекап заменен новым

**Примеры файлов**:
```json
{
  "meta": {
    "created_at": "2025-01-15T10:30:00Z",
    "total_tickets": 1234
  },
  "tickets": [
    {
      "id": 1,
      "subject": "Task 1",
      "status": "open",
      ...
    },
    ...
  ]
}
```

### Сценарий 2: Обработка ошибок при подключении к API

**Цель**: При недоступности Freshdesk API система корректно обработает ошибку.

**Процесс**:
1. Администратор выполняет команду: `php artisan backup:tickets`
2. Freshdesk API недоступен или требует авторизацию
3. Система выводит понятное сообщение об ошибке
4. Команда завершается с exit code 1
5. Предыдущий бекап остается нетронутым

**Результат**:
```
✗ Ошибка создания бекапа
- Описание: Не удалось подключиться к Freshdesk API
```

### Сценарий 3: Проверка справки команды

**Цель**: Пользователь получить информацию о синтаксисе команды.

**Процесс**:
1. Пользователь выполняет: `php artisan backup:tickets --help`
2. Система выводит справку с описанием синтаксиса
3. Команда завершается нормально

**Результат**:
```
Description:
  Create a backup of all Freshdesk tickets

Usage:
  backup:tickets

Options:
  -h, --help  Display this help message
```

### Сценарий 4: Защита от потери данных

**Цель**: При ошибке записи на диск система сохраняет целостность предыдущего бекапа.

**Процесс**:
1. Администратор выполняет: `php artisan backup:tickets`
2. Система успешно получает задачи из API
3. При записи на диск возникает ошибка (диск заполнен, недостаточно прав)
4. Система откатывает операцию
5. Система выводит сообщение об ошибке
6. Команда завершается с exit code 1
7. Предыдущий бекап остается в целости

**Результат**: Пользователь получает информацию об ошибке, данные в безопасности.

---

## Интеграция в проект

### Регистрация в bootstrap/providers.php

Модуль автоматически регистрируется при добавлении `BackupServiceProvider` в массив providers:

```php
return [
    \Parser\Core\Presentation\Config\CoreServiceProvider::class,
    \Parser\Backup\Presentation\Config\BackupServiceProvider::class,
];
```

### ServiceProvider (BackupServiceProvider)

Провайдер выполняет:
1. Загрузку конфигурации модуля
2. Регистрацию интерфейсов и реализаций в DI контейнере:
   - `FreshdeskClientInterface` → `FreshdeskHttpClientAdapter`
   - `BackupStorageInterface` → `FileBackupStorageAdapter`

---

## Дополнительная информация

### Обработка Rate Limiting

Freshdesk API имеет ограничения на количество запросов. Модуль реализует:
- Задержку 1 сек между каждым запросом при пагинации
- Exponential backoff (1, 2, 4 секунды) при получении HTTP 429
- Максимум 3 попытки повторения

### Безопасность

- ✅ API ключ хранится в переменной окружения `.env`
- ✅ API ключ НЕ логируется и НЕ попадает в вывод команды
- ✅ Бекап содержит только данные, доступные через API с текущим уровнем доступа

### Масштабируемость

- Система корректно обрабатывает большие объемы задач (1000+ задач)
- Пагинация обеспечивает получение всех задач
- Файловая система справляется с размерами бекапов (примерный размер JSON для 1000 задач: 50-100 МБ)

### Возможные улучшения (будущие версии)

- Сжатие бекапа (gzip)
- Ведение истории бекапов с версионированием
- Автоматическое выполнение по расписанию (cron)
- REST API для управления бекапами
- Восстановление данных из бекапа
- Синхронизация инкрементальных изменений
- Оптимизация получения детальной информации для множественных задач
- Кэширование результатов API запросов
- Асинхронная обработка больших объемов данных
