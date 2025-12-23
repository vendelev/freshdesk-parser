# Task 1: Создать Entity для Ticket

## Описание
Определить структуру Entity соответствующую [Freshdesk API v2 Tickets](https://developers.freshdesk.com/api/#view_a_ticket)

## Файл
`backend/src/Ticket/Domain/Entity/Ticket.php`

## Поля
- freshdesk_id
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
- custom_fields
- tags
- attachments (metadata)
- due_by
- fr_due_by
- created_at
- updated_at

## Требования
- Immutable Entity (все поля private, доступ через getter'ы)
- Типизация всех параметров конструктора
- PHPDoc комментарии

## Принятие
- [ ] Entity создана
- [ ] Все поля имеют getter'ы
- [ ] Entity immutable
- [ ] PHPStan проходит без ошибок
