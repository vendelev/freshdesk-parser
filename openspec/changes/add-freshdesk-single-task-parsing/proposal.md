# Change: Добавление функциональности парсинга одной задачи из Freshdesk

## Why

Необходимо добавить возможность парсинга отдельной задачи из Freshdesk API по её ID для более точного анализа конкретных тикетов. Это позволит получать детальную информацию по отдельным задачам без необходимости загрузки всех задач системы.

Текущая система имеет функциональность постраничной загрузки задач, но не позволяет получить информацию по конкретной задаче по её ID.

## What Changes

Добавление новой функциональности для загрузки отдельной задачи из Freshdesk API по ID с сохранением оригинальных JSON данных.

- Добавить новый метод в FreshdeskApiClientInterface для получения задачи по ID
- Реализовать метод в FreshdeskApiClient для загрузки задачи по ID
- Добавить новый UseCase для обработки запроса на получение одной задачи
- Добавить новую консольную команду для запуска парсинга одной задачи
- **BREAKING**: Изменить структуру сохранения файлов - отдельные задачи будут сохраняться в подкаталоге tasks/{id}.json

## Impact

- **Affected specs**: `freshdesk-parser`
- **Affected code**: 
  - `backend/src/Task/Domain/FreshdeskApiClientInterface.php`
  - `backend/src/Task/Infrastructure/Adapter/FreshdeskApiClient.php`
  - `backend/src/Task/Application/UseCase/`
  - `backend/src/Task/Presentation/Console/ParseSingleTaskCommand.php`
- **Breaking Changes**: Изменение структуры хранения файлов - отдельные задачи будут сохраняться в подкаталоге tasks/{id}.json вместо tasks-{id}.json в корневом каталоге
- **Migration**: Не требуется
- **Dependencies**: Нет
- **Performance**: Минимальное влияние, ограничено задержками между запросами