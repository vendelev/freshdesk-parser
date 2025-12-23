# Task 4: Создать DTO для входящих данных Ticket

## Описание
Создать объекты для передачи данных Ticket из Application слоя наружу (в Presentation слой).

## Файлы
- `backend/src/Ticket/Domain/Response/TicketResponse.php` (для выхода из UseCase)
- `backend/src/Ticket/Domain/Response/TicketListResponse.php` (список tickets)

## TicketResponse
Содержит полные данные одного ticket'а:
- Все поля Entity Ticket
- Преобразованные в удобный для API формат

## TicketListResponse
Содержит:
- Список TicketResponse объектов
- Pagination информация (page, total, per_page)

## Требования
- Типизация всех свойств
- PHPDoc комментарии
- Неизменяемость (private свойства, access через getter'ы)
- Структура должна совпадать с API ответами

## Принятие
- [ ] Оба DTO созданы
- [ ] Все поля типизированы
- [ ] PHPStan проходит без ошибок
- [ ] Структура логична и удобна для использования
