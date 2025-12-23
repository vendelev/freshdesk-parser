# Task 21: Создать HTTP Client обертка для API

## Описание
Создать HTTP клиент для взаимодействия с Freshdesk API. Это низкоуровневая обертка над HTTP сессией.

## Файл
`backend/src/Core/Infrastructure/Http/FreshdeskClient.php`

## Методы
```php
public function request(string $method, string $endpoint, array $options = []): Response
```

## Параметры
- method: HTTP метод (GET, POST, PUT, DELETE, т.д.)
- endpoint: Путь API (например: `/api/v2/tickets/123`)
- options: Дополнительные опции для запроса (headers, query, body, т.д.)

## Configuration
- Base URL: из переменной окружения FRESHDESK_API_DOMAIN (например: https://digitalworlds.freshdesk.com)
- API ключ: из переменной окружения FRESHDESK_API_KEY
- Authentication: Basic Auth (username=API_KEY, password=X)

## Требования
- Использует HttpClient из Laravel (GuzzleHttp)
- Создает полные URL из base URL + endpoint
- Добавляет Basic Auth заголовок автоматически
- Типизация параметров и возвращаемых значений
- PHPDoc комментарии
- Обработка HTTP ошибок
- Возвращает типизированный Response объект (или выбрасывает исключение при ошибке)
- Использует конфиг из .env переменных

## Принятие
- [ ] Client создан
- [ ] Метод request работает корректно
- [ ] Authentication добавляется автоматически
- [ ] PHPStan проходит без ошибок
- [ ] Unit тесты покрывают основные сценарии
