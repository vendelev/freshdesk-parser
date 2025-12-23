# Task 24: Добавить Freshdesk API ключ в .env

## Описание
Добавить переменные окружения для конфигурации Freshdesk API в файлы .env.example и .env.

## Файлы
- `backend/.env.example` (шаблон с комментариями)
- `backend/.env` (реальная конфигурация)

## Переменные

### FRESHDESK_API_KEY
- Описание: API ключ для аутентификации в Freshdesk
- Формат: строка (токен)
- Пример: `your-api-key-here`
- Обязательна: Да

### FRESHDESK_API_DOMAIN
- Описание: Домен Freshdesk инстанции
- Формат: https://domain.freshdesk.com
- Пример: `digitalworlds.freshdesk.com`
- Обязательна: Да

## Требования в .env.example
```env
# Freshdesk Configuration
FRESHDESK_API_KEY=your-api-key-here
FRESHDESK_API_DOMAIN=digitalworlds.freshdesk.com
```

## Требования в .env (для разработки)
```env
# Freshdesk Configuration
FRESHDESK_API_KEY=
FRESHDESK_API_DOMAIN=digitalworlds.freshdesk.com
```

## Требования
- Переменные добавлены в оба файла
- Комментарии в .env.example поясняют назначение
- Безопасность: Реальные ключи не коммитятся в .env (оставить пусто)

## Принятие
- [ ] Переменные добавлены в .env.example
- [ ] Переменные добавлены в .env
- [ ] Комментарии присутствуют
