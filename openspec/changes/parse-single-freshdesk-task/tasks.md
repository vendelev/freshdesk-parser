# Tasks: Добавление функциональности парсинга отдельной задачи из Freshdesk

## Backend

1. [ ] Создать новый use case `ParseSingleTaskFromFreshdesk` для парсинга отдельной задачи
2. [ ] Создать DTO `ParseSingleTaskRequest` с параметром `taskId`
3. [ ] Создать DTO `ParseSingleTaskResponse` для ответа
4. [ ] Создать новую консольную команду `freshdesk:parse-single-task {taskId}`
5. [ ] Сохранять оригинальный JSON задачи в `backend/storage/freshdesk/freshdesk/tasks/{taskId}.json`
6. [ ] Обновить `FreshdeskApiClientInterface` для поддержки метода получения одной задачи
7. [ ] Реализовать метод в `FreshdeskApiClient` для запроса к API Freshdesk по ID задачи

## Дополнительно

1. [ ] Добавить unit тесты для нового функционала
2. [ ] Обновить документацию API
