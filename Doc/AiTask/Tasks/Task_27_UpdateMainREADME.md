# Task 27: Обновить главный README.md проекта

## Описание
Обновить корневой README.md проекта с инструкциями по запуску синхронизации tickets.

## Файл
`backend/README.md` или корневой `README.md`

## Добавить секции

### 1. Freshdesk Sync
Описать как запустить синхронизацию:

```markdown
## Freshdesk Sync

Синхронизация tickets из Freshdesk в локальную БД.

### Предусловия
- Установить зависимости: `make install`
- Установить Freshdesk API ключ в `.env`:
  ```
  FRESHDESK_API_KEY=your-api-key
  FRESHDESK_API_DOMAIN=digitalworlds.freshdesk.com
  ```

### Запуск синхронизации
```bash
php artisan freshdesk:sync
```

### Output
```
Syncing Freshdesk tickets...
[=======>                         ] 100/200 (50%)
✓ Successfully synced: 200 tickets
✗ Errors: 0
Total time: 15 seconds
```

### Примечания
- Rate limit: 1 запрос в секунду
- Первая синхронизация может занять время (зависит от количества tickets)
```

### 2. Troubleshooting
- Ошибка аутентификации: проверить FRESHDESK_API_KEY
- Timeout: увеличить timeout в php.ini
- Memory limit: увеличить memory_limit в php.ini

## Требования
- Инструкции полные и понятные
- Примеры команд корректны
- Примеры output актуальны

## Принятие
- [ ] README обновлен
- [ ] Инструкции добавлены
- [ ] Примеры корректны
