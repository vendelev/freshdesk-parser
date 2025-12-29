# Технический план: Резервное копирование задач Freshdesk - ЗАДАЧА ДЛЯ РАЗРАБОТЧИКА

## Архитектурные решения

Создадим новый модуль **Backup** с минимальной архитектурой: получить JSON из API → сохранить в файл.

### Общая архитектура

| Слой | Компоненты | Назначение |
|------|-----------|-----------|
| **Domain** | Interfaces, Request/Response, Exceptions | Контракты и DTO |
| **Application** | UseCase | Бизнес-логика (координация) |
| **Infrastructure** | Adapters | Реализация интеграций |
| **Presentation** | Console Command, ServiceProvider, Config | Консольная команда |

## Модель предметной области

### Интерфейсы (контракты)

#### FreshdeskClientInterface
```php
public function getTickets(): array; // Возвращает массив задач из API
```

#### BackupStorageInterface
```php
public function save(array $data, string $filename): void; // Сохраняет JSON в файл
```

### DTO

#### BackupResponse (исходящие данные в консоль)
```
BackupResponse
├── status: string (success|error)
├── message: string
├── total_tickets: int|null
├── file_path: string|null
└── error_details: string|null
```

### Исключения

```
FreshdeskApiException (базовое)
├── FreshdeskApiConnectionException
├── FreshdeskApiRateLimitException
└── FreshdeskApiUnauthorizedException

BackupStorageException (базовое)
└── BackupStorageWriteException

BackupException (базовое)
└── BackupOperationFailedException
```

## Сценарии интеграции

```
Console Command (BackupTicketsCommand)
    ↓
UseCase (CreateBackupUseCase)
    ├→ FreshdeskClientInterface.getTickets()
    │   └→ FreshdeskHttpClientAdapter
    │       └→ Freshdesk API
    │
    └→ BackupStorageInterface.save()
        └→ FileBackupStorageAdapter
            └→ Файловая система
```

## Изменяемые файлы

### Создание новых файлов

```
backend/src/Backup/
├── Domain/
│   ├── FreshdeskClientInterface.php
│   ├── BackupStorageInterface.php
│   ├── Response/
│   │   └── BackupResponse.php
│   └── Exception/
│       ├── FreshdeskApiException.php
│       ├── FreshdeskApiConnectionException.php
│       ├── FreshdeskApiRateLimitException.php
│       ├── FreshdeskApiUnauthorizedException.php
│       ├── BackupStorageException.php
│       ├── BackupStorageWriteException.php
│       └── BackupOperationFailedException.php
│
├── Application/
│   └── UseCase/
│       └── CreateBackupUseCase.php
│
├── Infrastructure/
│   └── Adapter/
│       ├── FreshdeskHttpClientAdapter.php
│       └── FileBackupStorageAdapter.php
│
└── Presentation/
    ├── Console/
    │   └── BackupTicketsCommand.php
    ├── Config/
    │   ├── BackupServiceProvider.php
    │   └── freshdesk.php
    └── Listener/
        └── BackupCreatedListener.php (опционально)
```

### Модификация существующих файлов

```
backend/bootstrap/providers.php
├── Добавить BackupServiceProvider в массив providers

backend/.env.example
├── Добавить FRESHDESK_API_KEY
├── Добавить FRESHDESK_DOMAIN
├── Добавить BACKUP_STORAGE_PATH

backend/.env.testing
├── Добавить тестовые значения переменных окружения
```

## Последовательность действий

### Фаза 1: Domain слой

1. **Создать интерфейсы**:
   - `FreshdeskClientInterface.php` — получение задач из API
   - `BackupStorageInterface.php` — сохранение в файл

2. **Создать исключения**:
   - Иерархия из раздела "Исключения"

3. **Создать Response DTO**:
   - `BackupResponse.php` — результат выполнения бекапа

### Фаза 2: Application слой

4. **Создать UseCase**:
   - `CreateBackupUseCase.php`:
     - Инъектирует FreshdeskClientInterface и BackupStorageInterface
     - Вызывает `getTickets()` → получает `array $tickets`
     - Формирует JSON структуру с данными и метаданными:
       ```php
       [
           'meta' => [
               'created_at' => '2025-01-15T10:30:00Z',
               'total_tickets' => 1234,
           ],
           'tickets' => $tickets // Массив из API в неизменном виде
       ]
       ```
     - Вызывает `save($data, $filename)` для сохранения
     - Обрабатывает исключения и возвращает BackupResponse

### Фаза 3: Infrastructure слой

5. **Создать FreshdeskHttpClientAdapter**:
   - Реализует FreshdeskClientInterface
   - Инъектирует Guzzle Client через DI
   - `getTickets()` метод:
     - Получает API key и domain из конфигурации (через DI)
     - Реализует пагинацию (limit=100 на странице)
     - **Между каждым запросом добавляет задержку 1 сек** (sleep(1))
     - Retry logic с exponential backoff для обработки rate limiting
     - Преобразует HTTP ошибки в Domain исключения
     - Возвращает `array` всех задач

6. **Создать FileBackupStorageAdapter**:
   - Реализует BackupStorageInterface
   - `save($data, $filename)` метод:
     - Получает путь к директории из конфигурации (через DI)
     - Конвертирует массив в JSON (json_encode с опциями для читаемости)
     - Проверяет наличие директории, создаёт если нужно
     - Сохраняет в файл `backup_YYYY-MM-DD_HHmmss.json`
     - Обрабатывает ошибки записи в BackupStorageWriteException

### Фаза 4: Presentation слой

7. **Создать Console Command**:
   - `BackupTicketsCommand.php`:
     - Имя: `backup:tickets`
     - Инъектирует CreateBackupUseCase
     - `handle()` метод:
       - Вызывает UseCase
       - Выводит результат в консоль:
         - При успехе: статус, количество задач, время создания, путь к файлу
         - При ошибке: понятное сообщение об ошибке
       - Exit code: 0 при успехе, 1 при ошибке
     - Поддерживает флаг `--help`

8. **Создать ServiceProvider**:
   - `BackupServiceProvider.php`:
     - Регистрирует конфигурацию через `mergeConfigFrom()`
     - Регистрирует интерфейсы и реализации через `bind()`:
       - `FreshdeskClientInterface` → `FreshdeskHttpClientAdapter`
       - `BackupStorageInterface` → `FileBackupStorageAdapter`

9. **Создать конфигурационный файл**:
   - `freshdesk.php` в Presentation/Config/:
     - Читает FRESHDESK_API_KEY, FRESHDESK_DOMAIN, BACKUP_STORAGE_PATH из env

### Фаза 5: Конфигурация

10. **Обновить конфигурационные файлы**:
    - Добавить BackupServiceProvider в `bootstrap/providers.php`
    - Добавить переменные окружения в `.env.example` и `.env.testing`

## Зависимости

### До реализации модуля

- ✅ Laravel проект инициализирован
- ✅ Composer зависимости установлены (Guzzle)
- ✅ Переменные окружения настроены

### После реализации модуля

- Domain события (опционально) для логирования
- Может быть использовано другими модулями через Domain интерфейсы

## Диаграмма потока данных

```
User: php artisan backup:tickets
        ▼
BackupTicketsCommand.handle()
        ▼
CreateBackupUseCase.execute()
        ├→ getTickets() [FreshdeskClientInterface]
        │   └→ Freshdesk API (с пагинацией + задержка 1 сек между запросами)
        │   └→ array $tickets
        │
        └→ Формирует структуру:
            {
              "meta": {
                "created_at": "2025-01-15T10:30:00Z",
                "total_tickets": 1234
              },
              "tickets": [...]
            }
        
        ├→ save($data, $filename) [BackupStorageInterface]
        │   └→ storage/backups/backup_2025-01-15_103000.json
        │
        └→ BackupResponse (успех/ошибка)
        
        ▼
Console output:
  ✓ Бекап создан успешно
  - Задач: 1234
  - Время: 2025-01-15 10:30:00
  - Путь: storage/backups/backup_2025-01-15_103000.json
```

## Конфигурация

### Переменные окружения (.env)

Добавить в `.env.example` и `.env.testing`:

```env
# Freshdesk API Configuration
FRESHDESK_API_KEY=your_freshdesk_api_key_here
FRESHDESK_DOMAIN=your_company_domain

# Backup Storage Configuration
BACKUP_STORAGE_PATH=storage/backups
```

### ServiceProvider регистрация

В `backend/bootstrap/providers.php` добавить провайдер:

```php
return [
    \Parser\Core\Presentation\Config\CoreServiceProvider::class,
    \Parser\Backup\Presentation\Config\BackupServiceProvider::class,
];
```

## Риски и альтернативы

### Риск 1: Rate Limiting Freshdesk API

**Решение**: Реализовать exponential backoff retry (макс 3 попытки) с задержками между запросами.

### Риск 2: Потеря соединения

**Решение**: Retry logic с обработкой ConnectionException.

### Риск 3: Недостаток прав доступа к API

**Решение**: Документировать требуемые права, выбросить понятную ошибку при первом запросе.

### Риск 4: Проблемы с дисковым пространством

**Решение**: На текущем этапе логировать размер файла, в будущем можно добавить проверку свободного места.

## Чек-лист архитектурного соответствия

- [x] Clean Architecture (разделение на слои, зависимости идут вверх)
- [x] Domain содержит только контракты и DTO
- [x] Application слой независим от Framework
- [x] Infrastructure реализует интерфейсы
- [x] Presentation содержит Console Command и конфигурацию
- [x] Все требования Spec.md покрыты
- [x] Простая и понятная реализация
- [x] JSON сохраняется в неизменном виде из API
- [x] Без лишней валидации

## Связанные документы

Для написания и запуска тестов см. **TaskForTest.md**
