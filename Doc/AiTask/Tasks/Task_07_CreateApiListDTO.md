# Task 7: Создать DTO для листа tickets из API

## Описание
Создать объект для парсинга пагинированного JSON ответа Freshdesk API при запросе списка tickets.

## Файл
`backend/src/Ticket/Application/Dto/TicketListApiDto.php`

## Структура
Должен содержать:
- tickets: TicketApiDto[] (массив tickets)
- page: int (текущая страница)
- per_page: int (количество элементов на странице)
- total: int (общее количество tickets)
- total_pages: int (общее количество страниц)

Источник: [Freshdesk API v2 Tickets List](https://developers.freshdesk.com/api/#list_tickets)

## Требования
- Типизированные свойства
- PHPDoc комментарии
- Private свойства с getter'ами
- Может быть создан из ассоциативного массива
- Метод для получения списка TicketApiDto

## Принятие
- [ ] DTO создан
- [ ] Все поля покрыты
- [ ] PHPStan проходит без ошибок
- [ ] Соответствует структуре API ответа
