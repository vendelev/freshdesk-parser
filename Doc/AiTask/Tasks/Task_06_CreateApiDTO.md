# Task 6: Создать DTO для парсинга API ответов

## Описание
Создать объект для парсинга JSON ответа Freshdesk API на запрос одного ticket'а.

## Файл
`backend/src/Ticket/Application/Dto/TicketApiDto.php`

## Структура
Должен содержать все поля из Freshdesk API ответа:
- id
- subject
- description
- description_text
- type
- status
- priority
- source
- requester_id
- responder_id
- company_id
- group_id
- product_id
- email
- name
- phone
- custom_fields (JSON)
- tags (массив)
- attachments (JSON метаданные)
- due_by
- fr_due_by
- created_at
- updated_at

Источник: [Freshdesk API v2 Tickets](https://developers.freshdesk.com/api/#view_a_ticket)

## Требования
- Типизированные свойства
- PHPDoc комментарии
- Private свойства с getter'ами
- Может быть создан из ассоциативного массива (factory метод)

## Принятие
- [ ] DTO создан
- [ ] Все поля покрыты
- [ ] PHPStan проходит без ошибок
- [ ] Соответствует структуре API ответа
