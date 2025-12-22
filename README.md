# Freshdesk Parser

Freshdesk Parser - это приложение для парсинга задач из Freshdesk API и сохранения их в локальной базе данных для дальнейшей обработки и анализа.

## Возможности

- Получение списка задач из Freshdesk API
- Получение отдельной задачи по ID из Freshdesk API
- Сохранение полученных данных в формате JSON
- Обработка ошибок API (401, 404, 429)

## Установка

1. Склонируйте репозиторий
2. Установите зависимости: `composer install`
3. Настройте переменные окружения в `.env` файле

## Использование

### Получение всех задач

```bash
php artisan freshdesk:parse-tasks
```

### Получение задачи по ID

```bash
php artisan freshdesk:get-task {id}
```

Где `{id}` - это ID задачи в Freshdesk.

Пример:
```bash
php artisan freshdesk:get-task 12345
```

## Конфигурация

Для работы приложения необходимо настроить следующие переменные окружения:

- `FRESHDESK_API_KEY` - API ключ Freshdesk
- `FRESHDESK_DOMAIN` - Домен вашей компании в Freshdesk (например, для https://yourcompany.freshdesk.com используйте "yourcompany")

## Хранение данных

Полученные данные сохраняются в директории `backend/storage/freshdesk/tasks/` в формате JSON.