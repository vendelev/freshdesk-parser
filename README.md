# Freshdesk Parser (учебный проект)

Laravel-приложение для парсинга задач (tickets) из Freshdesk API и сохранения оригинальных JSON-ответов в storage.

## Команды

Выполнение команд рекомендуется через контейнеры:

- Массовый парсинг (постранично):
  - `php artisan freshdesk:parse-tasks`
  - `php artisan freshdesk:parse-tasks --force`
  - Результат сохраняется в `backend/storage/freshdesk/freshdesk/tasks-{page}.json`

- Парсинг одной задачи по ID:
  - `php artisan freshdesk:parse-single-task 12345`
  - Результат сохраняется в `backend/storage/freshdesk/freshdesk/tasks/12345.json`

## Переменные окружения

См. [`backend/.env.example`](backend/.env.example).
