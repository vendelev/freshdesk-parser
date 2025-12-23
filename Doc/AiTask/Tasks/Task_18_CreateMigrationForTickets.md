# Task 18: Создать миграцию для таблицы tickets

## Описание
Создать миграцию Laravel для создания таблицы tickets в БД со всеми необходимыми колонками и индексами.

## Файл
`backend/database/migrations/YYYY_MM_DD_create_tickets_table.php`

## Колонки
Все поля из Data Model (Concept.md):
- id (bigint primary key, auto increment)
- freshdesk_id (int, UNIQUE, not null)
- subject (string)
- description (text, nullable)
- description_text (text, nullable)
- type (string, nullable)
- status (int)
- priority (int)
- source (int)
- requester_id (int, nullable)
- responder_id (int, nullable)
- company_id (int, nullable)
- group_id (int, nullable)
- product_id (int, nullable)
- email (string, nullable)
- name (string, nullable)
- phone (string, nullable)
- custom_fields (json, nullable)
- tags (json, nullable)
- attachments (json, nullable)
- due_by (datetime, nullable)
- fr_due_by (datetime, nullable)
- created_at (datetime)
- updated_at (datetime)

## Индексы
- freshdesk_id (UNIQUE)
- updated_at (для синхронизации)

## Требования
- Использовать Laravel миграции (Schema::create)
- Правильные типы данных
- Nullable/not null как требуется
- Индексы для часто используемых полей

## Принятие
- [ ] Миграция создана
- [ ] Все колонки присутствуют
- [ ] Индексы добавлены
- [ ] PHPStan/PHP_CodeSniffer проходит без ошибок
