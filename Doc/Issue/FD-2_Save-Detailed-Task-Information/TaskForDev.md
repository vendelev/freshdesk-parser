# Задача для разработчика: FD-2 Сохранение детальной информации о задачах

## Архитектурные решения

### Создаваемые компоненты по слоям

**Domain слой:**
- `TicketDetailRepositoryInterface` - контракт для сохранения детальной информации задач
- `FreshdeskDetailClientInterface` - контракт для получения детальной информации из Freshdesk API
- `SaveTicketDetailRequest` - DTO для запроса сохранения (ticket_id, опции)
- `SaveTicketDetailResponse` - DTO для ответа (статус, путь к файлу, метаданные)
- `TicketDetailMetadata` - ValueObject для метаданных сохранения
- Доменные исключения: `TicketDetailNotFoundException`, `TicketDetailSaveException`

**Application слой:**
- `SaveTicketDetailUseCase` - основной UseCase для сохранения детальной информации
- `SaveTicketDetailCommand` - CQRS команда для сохранения
- `SaveTicketDetailCommandHandler` - обработчик команды
- `TicketDetailFactory` - фабрика для создания DTO и ValueObject

**Infrastructure слой:**
- `FileTicketDetailRepository` - реализация сохранения в файловую систему
- `FreshdeskHttpDetailClientAdapter` - адаптер для получения данных из Freshdesk API
- `TicketDetailProgressInterface` - интерфейс для отображения прогресса (опционально, для консоли)

**Presentation слой:**
- `SaveTicketDetailsCommand` - консольная команда Laravel
- `TicketDetailServiceProvider` - провайдер для DI и конфигурации
- `freshdesk.php` - конфигурационный файл для переменных окружения

### Модульная структура

Создается новый модуль `TicketDetail` в `backend/src/TicketDetail/` со стандартной структурой Clean Architecture.

Модуль взаимодействует с модулем `Backup` через:
- Синхронный вызов Domain интерфейса `FreshdeskClientInterface` (расширенный методом `getTicketDetail`)
- Получение списка ticket IDs через Application слой Backup модуля

## Модель предметной области

### Основные интерфейсы

```php
interface TicketDetailRepositoryInterface
{
    public function save(int $ticketId, string $jsonData, TicketDetailMetadata $metadata): void;
    public function exists(int $ticketId): bool;
}

interface FreshdeskDetailClientInterface
{
    public function getTicketDetail(int $ticketId): string; // возвращает сырой JSON
}
```

### DTO и ValueObject

- `SaveTicketDetailRequest`: содержит `ticketId` (int), `forceOverwrite` (bool)
- `SaveTicketDetailResponse`: содержит `ticketId`, `status` ('success'|'partial'|'failed'), `filePath`, `savedAt`, `errorMessage`
- `TicketDetailMetadata`: immutable объект с `savedAt` (DateTime), `status`, `size` (bytes)

### Взаимосвязи

- UseCase получает Request, вызывает CommandHandler, возвращает Response
- CommandHandler использует FreshdeskDetailClientInterface для получения JSON и TicketDetailRepositoryInterface для сохранения
- Консольная команда преобразует аргументы в Request и выводит Response

## Сценарии интеграции

### Сценарий 1: Сохранение для одной задачи

1. Консольная команда получает `--ticket-id=123`
2. UseCase вызывает `FreshdeskDetailClientInterface::getTicketDetail(123)`
3. Адаптер делает HTTP запрос к `/api/v2/tickets/123` с include=conversations,attachments
4. UseCase сохраняет полученный JSON через `TicketDetailRepositoryInterface::save()`
5. Возвращается Response с успехом

### Сценарий 2: Сохранение для всех задач

1. Консольная команда получает `--all`
2. Получает список ticket IDs из Backup модуля (через его UseCase или Query)
3. Последовательно обрабатывает каждый ID по сценарию 1
4. При ошибке для одного ID продолжает с другими, логирует ошибки
5. Выводит итоговую статистику

### Сценарий 3: Обработка ошибок API

1. При rate limit: экспоненциальный backoff с повтором
2. При 404: выбрасывает `TicketDetailNotFoundException`
3. При частичном ответе: сохраняет доступные данные с статусом 'partial'

## Изменяемые файлы

### Новые файлы (модуль TicketDetail)

```
backend/src/TicketDetail/
├── Domain/
│   ├── TicketDetailRepositoryInterface.php
│   ├── FreshdeskDetailClientInterface.php
│   ├── Request/SaveTicketDetailRequest.php
│   ├── Response/SaveTicketDetailResponse.php
│   ├── ValueObject/TicketDetailMetadata.php
│   ├── Exception/TicketDetailNotFoundException.php
│   ├── Exception/TicketDetailSaveException.php
│   └── Doc/Diagram.md
├── Application/
│   ├── UseCase/SaveTicketDetailUseCase.php
│   ├── Command/SaveTicketDetailCommand.php
│   ├── Command/SaveTicketDetailCommandHandler.php
│   ├── Dto/SaveTicketDetailDto.php
│   └── Factory/TicketDetailFactory.php
├── Infrastructure/
│   ├── Repository/FileTicketDetailRepository.php
│   └── Adapter/FreshdeskHttpDetailClientAdapter.php
└── Presentation/
    ├── Console/SaveTicketDetailsCommand.php
    ├── Config/TicketDetailServiceProvider.php
    └── Config/freshdesk.php
```

### Изменяемые файлы (расширение Backup модуля)

```
backend/src/Backup/Domain/FreshdeskClientInterface.php  // добавить метод getTicketDetail
backend/src/Backup/Infrastructure/Adapter/FreshdeskHttpClientAdapter.php  // реализовать getTicketDetail
```

## Последовательность действий

1. **Расширить Backup модуль:**
   - Добавить метод `getTicketDetail(int $ticketId): string` в `FreshdeskClientInterface`
   - Реализовать метод в `FreshdeskHttpClientAdapter` с поддержкой пагинации conversations

2. **Создать Domain слой TicketDetail:**
   - Интерфейсы `TicketDetailRepositoryInterface`, `FreshdeskDetailClientInterface`
   - DTO `SaveTicketDetailRequest`, `SaveTicketDetailResponse`
   - ValueObject `TicketDetailMetadata`
   - Исключения `TicketDetailNotFoundException`, `TicketDetailSaveException`

3. **Создать Application слой:**
   - `SaveTicketDetailCommand` и `SaveTicketDetailCommandHandler`
   - `SaveTicketDetailUseCase` координирующий работу
   - `TicketDetailFactory` для создания объектов

4. **Создать Infrastructure слой:**
   - `FileTicketDetailRepository` для сохранения JSON в `storage/backups/detail/{id}.json`
   - `FreshdeskHttpDetailClientAdapter` реализующий `FreshdeskDetailClientInterface`

5. **Создать Presentation слой:**
   - `SaveTicketDetailsCommand` с опциями `--ticket-id`, `--all`, `--force`
   - `TicketDetailServiceProvider` с регистрацией зависимостей
   - Конфигурационный файл `freshdesk.php`

6. **Интеграция с Backup модулем:**
   - В `TicketDetailServiceProvider` зарегистрировать `FreshdeskDetailClientInterface` как алиас к `FreshdeskClientInterface` из Backup

## Зависимости

### Что нужно выполнить до

- Реализация FD-1 Backup Module (для FreshdeskClientInterface и получения списка задач)

## Диаграммы или псевдокод

### Поток данных

```
Консольная команда
    ↓ (аргументы)
SaveTicketDetailUseCase
    ↓ (Request)
SaveTicketDetailCommandHandler
    ├── FreshdeskDetailClientInterface::getTicketDetail() → сырой JSON
    └── TicketDetailRepositoryInterface::save() → файл
    ↓ (Response)
SaveTicketDetailUseCase
    ↓ (Response)
Консольная команда → вывод в консоль
```

### Псевдокод UseCase

```php
class SaveTicketDetailUseCase
{
    public function execute(SaveTicketDetailRequest $request): SaveTicketDetailResponse
    {
        try {
            $jsonData = $this->freshdeskClient->getTicketDetail($request->ticketId);
            $metadata = new TicketDetailMetadata(now(), 'success', strlen($jsonData));
            
            $this->repository->save($request->ticketId, $jsonData, $metadata);
            
            return new SaveTicketDetailResponse(
                $request->ticketId,
                'success',
                "storage/backups/detail/{$request->ticketId}.json",
                $metadata->savedAt,
                null
            );
        } catch (Exception $e) {
            // логирование и возврат failed response
        }
    }
}
```

## Миграции и конфигурация

- **Миграции:** Не требуются (сохранение в файлы)
- **Конфигурация:** Добавить в `backend/.env.example` переменные для Freshdesk (уже есть из FD-1)
- **ServiceProvider:** Зарегистрировать в `bootstrap/providers.php`

## Риски и альтернативы

### Риск 1: Rate limiting Freshdesk API

**Описание:** Детальное получение требует больше запросов.

**Альтернатива:** Реализовать очередь с отложенной обработкой вместо синхронной.

### Риск 2: Большие объемы данных

**Описание:** Conversations могут быть очень большими.

**Альтернатива:** Сохранять conversations отдельно от основной информации задачи.

### Риск 3: Изменение API Freshdesk

**Описание:** Структура ответа может измениться.

**Альтернатива:** Добавить валидацию структуры JSON перед сохранением.

### Риск 4: Недостаток прав доступа

**Описание:** API ключ без доступа к conversations.

**Альтернатива:** Сохранять только доступные поля, логировать предупреждения.

## Чек-лист архитектурного соответствия

- [ ] Соблюден принцип CQRS (команды для изменения, запросы для чтения)
- [ ] Clean Architecture: зависимости направлены inward (Domain не зависит от внешних слоев)
- [ ] Модульный монолит: четкие границы модуля TicketDetail
- [ ] Интерфейсы для инверсии зависимостей (Repository, Client)
- [ ] Типизация: все публичные методы используют DTO
- [ ] Валидация: бизнес-правила проверяются в Application слое
- [ ] Обработка ошибок: доменные исключения в Domain
- [ ] Тестируемость: зависимости инжектируются через конструктор
- [ ] Соответствие правилам [Architecture.md](../../../../Rule/Architecture.md)
