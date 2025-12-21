# Tasks: Парсинг одной задачи из Freshdesk API

## Backend

1. [ ] Создать UseCase `ParseSingleTaskFromFreshdesk` для получения одной задачи
2. [ ] Создать DTO `ParseSingleTaskRequest` для входных данных
3. [ ] Создать DTO `ParseSingleTaskResponse` для выходных данных
4. [ ] Добавить метод в `FreshdeskApiClient` для получения одной задачи по ID
5. [ ] Добавить метод в `FreshdeskTaskParser` для сохранения одной задачи
6. [ ] Создать консольную команду `ParseSingleTaskCommand`
7. [ ] Регистрировать новые компоненты в `TaskServiceProvider`

## Тестирование

1. [ ] Добавить unit тесты для `ParseSingleTaskFromFreshdesk`
2. [ ] Добавить интеграционные тесты для `FreshdeskApiClient`
3. [ ] Добавить E2E тесты для консольной команды

## Дополнительно

1. [ ] Обновить документацию
