# Design: Парсинг задач из Freshdesk

## Архитектурные решения

### Модуль Task

Создается новый модуль Task, следуя стандартной структуре проекта:

```text
Task/
├── Application/
│   ├── UseCase/
│   │   └── ParseTasksFromFreshdesk.php
│   └── Service/
│       └── FreshdeskTaskParser.php
├── Domain/
│   ├── TaskParserInterface.php
│   ├── Request/
│   │   └── ParseTasksRequest.php
│   ├── Response/
│   │   └── ParseTasksResponse.php
│   └── Exception/
│       └── FreshdeskApiException.php
├── Infrastructure/
│   └── Adapter/
│       └── FreshdeskApiClient.php
├── Presentation/
│   ├── Console/
│   │   └── ParseTasksCommand.php
│   └── Config/
│       └── freshdesk.php
│       └── TaskServiceProvider.php
└── Tests/
    └── Suite/
        └── Task/
            ├── Application/
            │   └── UseCase/
            │       └── ParseTasksFromFreshdeskTest.php
            └── Infrastructure/
                └── Adapter/
                    └── FreshdeskApiClientTest.php
            └── Presentation/
                └── Console/
                    └── ParseTasksCommandTest.php
```

### Поток выполнения

1. Пользователь запускает команду Artisan `php artisan freshdesk:parse-tasks`
2. Команда создает `ParseTasksRequest` и вызывает `ParseTasksFromFreshdesk` UseCase
3. UseCase использует `FreshdeskTaskParser` для загрузки данных
4. `FreshdeskTaskParser` использует `FreshdeskApiClient` для выполнения HTTP запросов
5. Данные сохраняются в файлы в формате `tasks-{номер страницы}.json`
6. Результат возвращается через `ParseTasksResponse`


### Продолжение после прерывания

1. Перед началом парсинга система проверяет наличие файлов tasks-{номер страницы}.json в директории backend/storage/freshdesk/
2. Определяется номер последнего файла
3. Парсинг начинается со страницы N+1, где N - номер последнего файла
4. Если файлов нет, парсинг начинается с первой страницы

### Перезапуск с первой страницы

1. Если команда запущена с параметром --force, все существующие файлы tasks-*.json удаляются
2. Парсинг начинается с первой страницы (page=1)
3. Если команда запущена без параметра --force, используется стандартный механизм продолжения

### Обработка ошибок

- При ошибках API выбрасывается `FreshdeskApiException`
- При ошибках файловой системы выбрасываются стандартные исключения PHP
- Все ошибки логируются и передаются пользователю через команду

### Конфигурация

Параметры Freshdesk API (домен и ключ) хранятся в конфигурационном файле `freshdesk.php` в каталоге `Presentation/Config` модуля и передаются через контейнер зависимостей в соответствии с правилами использования переменных окружения проекта.

### Service Provider

Модуль регистрируется в Laravel через `TaskServiceProvider`, который:
- Регистрирует конфигурацию модуля
- Регистрирует зависимости через контейнер DI
- Регистрирует консольные команды

### Тестирование

Модуль включает unit-тесты для UseCase и интеграционные тесты для адаптера Freshdesk API.