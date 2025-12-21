# Design: Парсинг задач из Freshdesk

## Архитектурные решения

### Модуль Task

Создается новый модуль Task, следуя стандартной структуре проекта:

```
Task/
├── Application/
│   ├── UseCase/
│   │   └── ParseTasksFromFreshdesk.php
│   └── Service/
│       └── FreshdeskTaskParser.php
├── Domain/
│   ├── Request/
│   │   └── ParseTasksRequest.php
│   ├── Response/
│   │   └── ParseTasksResponse.php
│   └── Exception/
│       └── FreshdeskApiException.php
├── Infrastructure/
│   └── Adapter/
│       └── FreshdeskApiClient.php
└── Presentation/
    └── Console/
        └── ParseTasksCommand.php
```

### Поток выполнения

1. Пользователь запускает команду Artisan `php artisan freshdesk:parse-tasks`
2. Команда создает `ParseTasksRequest` и вызывает `ParseTasksFromFreshdesk` UseCase
3. UseCase использует `FreshdeskTaskParser` для загрузки данных
4. `FreshdeskTaskParser` использует `FreshdeskApiClient` для выполнения HTTP запросов
5. Данные сохраняются в файлы в формате `tasks-{номер страницы}.json`
6. Результат возвращается через `ParseTasksResponse`

### Обработка ошибок

- При ошибках API выбрасывается `FreshdeskApiException`
- При ошибках файловой системы выбрасываются стандартные исключения PHP
- Все ошибки логируются и передаются пользователю через команду

### Конфигурация

Параметры Freshdesk API (домен и ключ) хранятся в конфигурационном файле и передаются через контейнер зависимостей.