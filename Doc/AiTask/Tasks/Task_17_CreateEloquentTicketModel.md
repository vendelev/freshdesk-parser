# Task 17: Создать Eloquent Model для Ticket БД

## Описание
Создать Eloquent модель для взаимодействия с таблицей tickets в БД.

## Файл
`backend/src/Ticket/Infrastructure/Persistence/EloquentTicketModel.php`

## Таблица
- Название: `tickets`
- Схема: соответствует Data Model из Concept.md

## Поля (мутаторы)
- custom_fields: JSON массив
- tags: JSON массив
- attachments: JSON объект (метаданные)

## Требования
- Наследует Illuminate\Database\Eloquent\Model
- Определены fillable или guarded свойства
- Мутаторы для JSON полей (use Illuminate\Database\Eloquent\Casts\AsJson)
- PHPDoc комментарии для всех свойств
- Не содержит бизнес-логики (только преобразование данных)

## Принятие
- [ ] Model создана
- [ ] Все поля правильно определены
- [ ] Мутаторы JSON полей работают
- [ ] PHPStan проходит без ошибок
