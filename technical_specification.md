# Техническое задание для консольной команды Freshdesk Parser

## Общее описание

Разработать консольную команду на PHP для Laravel, которая будет скачивать список всех задач из Freshdesk API и сохранять их в формате JSON.

## Функциональные требования

1. Скачивание списка всех задач из Freshdesk API постранично
2. Сохранение каждого ответа API в неизмененном виде в формате JSON в папку `tickets/list/`
3. Получение детального описания каждой задачи
4. Сохранение детальной информации в папку `tickets/detail/`
5. Соблюдение задержки в 1 секунду между запросами
6. Использование API ключа и домена Freshdesk из переменных окружения
7. Реализация в виде Artisan команды с названием `freshdesk:download:tickets`

## Нефункциональные требования

1. Использование HTTP клиента GuzzleHttp
2. Обработка ошибок при работе с API
3. Создание необходимых директорий при их отсутствии
4. Сохранение всех доступных данных по задачам без фильтрации

## Переменные окружения

- `FRESHDESK_API_KEY` - API ключ для доступа к Freshdesk API
- `FRESHDESK_DOMAIN` - Домен Freshdesk (например, "yourcompany.freshdesk.com")

## Структура проекта

```
backend/
├── src/
│   └── Ticket/
│       └── Application/
│           └── Command/
│               └── DownloadTicketsCommand.php
└── storage/
    └── tickets/
        ├── list/
        └── detail/
```

## Технические детали

### Freshdesk API

- Endpoint для списка задач: `https://{domain}.freshdesk.com/api/v2/tickets`
- Endpoint для детальной информации: `https://{domain}.freshdesk.com/api/v2/tickets/{id}`
- Аутентификация: Basic Auth с API ключом в качестве username
- Постраничная навигация: параметр `page` и `per_page` (по умолчанию 100)

### Реализация

1. Создать Artisan команду `freshdesk:download:tickets`
2. Реализовать Freshdesk API клиент
3. Реализовать постраничную загрузку задач
4. Реализовать сохранение JSON файлов
5. Добавить задержку в 1 секунду между запросами

## Команда запуска

```bash
php artisan freshdesk:download:tickets