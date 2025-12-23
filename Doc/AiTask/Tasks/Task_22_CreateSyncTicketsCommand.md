# Task 22: Создать Console Command для синхронизации tickets

## Описание
Создать консольную команду Laravel для запуска синхронизации tickets из Freshdesk.

## Файл
`backend/src/Ticket/Presentation/Console/SyncTicketsCommand.php`

## Сигнатура
```bash
php artisan freshdesk:sync
```

## Логика
1. Валидация наличия API ключа в .env (FRESHDESK_API_KEY)
2. Вывод информационного сообщения о начале синхронизации
3. Вызов SyncTicketsUseCase
4. Вывод прогресс-бара (используя Laravel ProgressBar)
5. Вывод результатов (количество синхронизованных, ошибочных tickets)
6. Обработка ошибок и их вывод

## Output примеры
```
Syncing Freshdesk tickets...
[=======>                         ] 100/200 (50%)
✓ Successfully synced: 200 tickets
✗ Errors: 0
Total time: 15 seconds
```

## Требования
- Extends Illuminate\Console\Command
- Inject SyncTicketsUseCase через конструктор (Dependency Injection)
- Использовать ProgressBar для вывода прогресса
- Типизированные параметры и возвращаемые значения
- PHPDoc комментарии
- Обработка ошибок (выводить в консоль)
- Exit code 0 при успехе, 1 при ошибке

## Принятие
- [ ] Command создана
- [ ] Синтаксис команды правильный
- [ ] Прогресс-бар показывается
- [ ] PHPStan проходит без ошибок
- [ ] Функциональные тесты покрывают основные сценарии
