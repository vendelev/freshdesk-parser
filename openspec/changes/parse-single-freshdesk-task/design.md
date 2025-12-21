# Design: Парсинг одной задачи из Freshdesk API

## Архитектурные решения

### Расширение модуля Task

Добавление новой функциональности в существующий модуль Task:

```text
Task/
├── Application/
│   ├── UseCase/
│   │   ├── ParseTasksFromFreshdesk.php (существующий)
│   │   └── ParseSingleTaskFromFreshdesk.php (новый)
│   └── Service/
│       └── FreshdeskTaskParser.php (расширяемый)
├── Domain/
│   ├── TaskParserInterface.php
│   ├── Request/
│   │   ├── ParseTasksRequest.php (существующий)
│   │   └── ParseSingleTaskRequest.php (новый)
│   ├── Response/
│   │   ├── ParseTasksResponse.php (существующий)
│   │   └── ParseSingleTaskResponse.php (новый)
│   └── Exception/
│       └── FreshdeskApiException.php
├── Infrastructure/
│   └── Adapter/
│       └── FreshdeskApiClient.php (расширяемый)
├── Presentation/
│   ├── Console/
│   │   ├── ParseTasksCommand.php (существующий)
│   │   └── ParseSingleTaskCommand.php (новый)
│   └── Config/
│       ├── freshdesk.php
│       └── TaskServiceProvider.php (расширяемый)
└── Tests/
    └── Suite/
        └── Task/
            ├── Application/
            │   └── UseCase/
            │       └── ParseSingleTaskFromFreshdeskTest.php (новый)
            ├── Infrastructure/
            │   └── Adapter/
            │       └── FreshdeskApiClientTest.php (расширяемый)
            └── Presentation/
                └── Console/
                    └── ParseSingleTaskCommandTest.php (новый)
```

### Поток выполнения

1. Пользователь запускает команду Artisan `php artisan freshdesk:parse-task {task_id}`
2. Команда создает `ParseSingleTaskRequest` с ID задачи
3. Вызывается `ParseSingleTaskFromFreshdesk` UseCase
4. UseCase использует `FreshdeskTaskParser` для загрузки данных
5. `FreshdeskTaskParser` использует `FreshdeskApiClient` для выполнения HTTP запроса
6. Данные сохраняются в файл `backend/storage/freshdesk/freshdesk/tasks/{task_id}.json`
7. Результат возвращается через `ParseSingleTaskResponse`

### Сохранение данных

1. Директория: `backend/storage/freshdesk/freshdesk/tasks/`
2. Имя файла: `{task_id}.json`
3. Содержимое: Неизменённый JSON ответ от Freshdesk API
4. Директория создается автоматически, если её нет

### Обработка ошибок

- При ошибке 404 (задача не найдена): выбрасывается `FreshdeskApiException`
- При ошибке 401 (авторизация): выбрасывается `FreshdeskApiException`
- При ошибке 429 (лимит): система повторяет запрос после задержки
- При ошибках файловой системы: выбрасываются стандартные исключения PHP
- Все ошибки логируются

### Service Provider

Модуль регистрируется в Laravel через `TaskServiceProvider`, который:
- Регистрирует конфигурацию модуля
- Регистрирует зависимости через контейнер DI
- Регистрирует консольные команды и маршруты
