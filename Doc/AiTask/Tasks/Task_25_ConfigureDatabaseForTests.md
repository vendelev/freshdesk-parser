# Task 25: Создать/обновить конфиг базы данных для тестов

## Описание
Создать отдельную конфигурацию SQLite БД для тестов (изолировано от разработки).

## Файл
`backend/config/database.php`

## Структура
```php
'sqlite_test' => [
    'driver' => 'sqlite',
    'url' => env('DATABASE_URL'),
    'database' => env('DB_DATABASE_TEST', ':memory:'),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
],
```

## Требования
- Отдельное подключение `sqlite_test`
- По умолчанию использует `:memory:` (в памяти)
- Возможность переопределить через переменную `DB_DATABASE_TEST`
- Foreign keys включены для целостности данных

## Конфигурация в .env для тестов
```env
DB_CONNECTION_TEST=sqlite_test
DB_DATABASE_TEST=:memory:
```

## Как использовать в phpunit.xml
```xml
<env name="DB_CONNECTION" value="sqlite_test"/>
<env name="DB_DATABASE_TEST" value=":memory:"/>
```

## Требования
- Конфиг добавлен в database.php
- Отдельное подключение для тестов
- Foreign keys включены

## Принятие
- [ ] Конфиг добавлен
- [ ] Подключение работает
- [ ] PHPStan проходит без ошибок
