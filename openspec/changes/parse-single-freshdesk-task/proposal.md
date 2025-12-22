# Change: Добавление функциональности парсинга отдельной задачи из Freshdesk

## Why

Необходимо добавить возможность парсинга отдельной задачи из Freshdesk API по её идентификатору. Это позволит выполнять целенаправленную обработку конкретных тикетов без необходимости парсинга всего списка задач, что повысит эффективность и снизит нагрузку на API.

Текущая система поддерживает только массовый парсинг всех задач, что не подходит для сценариев, когда требуется обработка одного или нескольких конкретных тикетов.

## What Changes

- Добавить новый use case `ParseSingleTaskFromFreshdesk` для парсинга отдельной задачи
- Создать новый DTO `ParseSingleTaskRequest` с параметром `taskId`
- Добавить новый DTO `ParseSingleTaskResponse` для ответа
- Создать новую консольную команду `freshdesk:parse-single-task {taskId}` для вызова функциональности
- Сохранять оригинальный JSON задачи в `backend/storage/freshdesk/freshdesk/tasks/{taskId}.json`
- Обновить `FreshdeskApiClientInterface` для поддержки метода получения одной задачи
- Реализовать метод в `FreshdeskApiClient` для запроса к API Freshdesk по ID задачи

### From
```
// Текущая функциональность только массового парсинга
$request = new ParseTasksRequest(startPage: 1, forceRestart: false);
$response = $parseTasksFromFreshdesk->execute($request);
```

### To
```
// Добавление функциональности парсинга отдельной задачи
$request = new ParseSingleTaskRequest(taskId: 12345);
$response = $parseSingleTaskFromFreshdesk->execute($request);
```

## Impact

- **Affected specs**: `freshdesk-parser`
- **Affected code**: `backend/src/Task/`
- **Breaking Changes**: Нет
- **Migration**: Нет
- **Dependencies**: Нет
- **Performance**: Минимальное влияние, запрос к API для одной задачи вместо постраничной загрузки
