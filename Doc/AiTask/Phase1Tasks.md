# Phase 1: MVP — Список задач

## Статус: Начало

**Цель Phase 1**: Реализовать базовую синхронизацию задач (tickets) из Freshdesk в SQLite через консольную команду.

---

## Основные правила

- [Концепция проекта](Concept.md)
- [Обзор проекта](../ProjectInfo.md)
- [PHP Lead Developer Role](../Rule/DeveloperRole.md)
- [Архитектура проекта](../Rule/Architecture.md)
- [Тестирование](../Rule/Testing.md)
- [Workflow при добавлении новой feature](../Rule/FeatureWorkflow.md)
- [Подсказки при работе с PHP кодом](../Rule/CodeHints.md)

## Стандарт выполнения задач

**ОБЯЗАТЕЛЬНО** Для всех задач в этой фазе используется единый workflow, описанный в документе **[TaskExecutionGuide.md](../Rule/TaskExecutionGuide.md)**.
**ОБЯЗАТЕЛЬНО** Перед тем как отметить задачу как выполненную, нужно убедиться, что все обязательные шаги выполнены:

```markdown
- Код реализован согласно требованиям
- Написаны все необходимые тесты
- Добавлен PHPDoc
- Выполнен запуск Code Fixer
- Выполнен запуск Code Quality
```

---

## Domain Layer (Ticket Module)

### 1. Создать Entity для Ticket
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_01_CreateTicketEntity.md)

### 2. Создать ValueObjects для Ticket
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_02_CreateValueObjects.md)

### 3. Создать Domain интерфейсы для Ticket
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_03_CreateDomainInterfaces.md)

### 4. Создать DTO для входящих данных Ticket
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_04_CreateDTOResponse.md)

### 5. Создать Domain исключения для Ticket
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_05_CreateDomainExceptions.md)

---

## Application Layer (Ticket Module)

### 6. Создать DTO для парсинга API ответов
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_06_CreateApiDTO.md)

### 7. Создать DTO для листа tickets из API
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_07_CreateApiListDTO.md)

### 8. Создать Service для трансформации API данных в Domain Entity
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_08_CreateTicketTransformer.md)

### 9. Создать Query для получения списка ID задач
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_09_CreateGetTicketListQuery.md)

### 10. Создать Query Handler для GetTicketListQuery
**Статус**: [PENDING] | [Описание](./Tasks/Task_10_CreateGetTicketListQueryHandler.md)

### 11. Создать Query для получения полных данных одной задачи
**Статус**: [PENDING] | [Описание](./Tasks/Task_11_CreateGetTicketQuery.md)

### 12. Создать Query Handler для GetTicketQuery
**Статус**: [PENDING] | [Описание](./Tasks/Task_12_CreateGetTicketQueryHandler.md)

### 13. Создать Command для сохранения/обновления Ticket
**Статус**: [PENDING] | [Описание](./Tasks/Task_13_CreateSaveTicketCommand.md)

### 14. Создать Command Handler для SaveTicketCommand
**Статус**: [PENDING] | [Описание](./Tasks/Task_14_CreateSaveTicketCommandHandler.md)

### 15. Создать UseCase для синхронизации всех Tickets
**Статус**: [PENDING] | [Описание](./Tasks/Task_15_CreateSyncTicketsUseCase.md)

### 16. Создать Factory для создания Entity из DTO
**Статус**: [COMPLETED] | [Описание](./Tasks/Task_16_CreateTicketFactory.md)

---

## Infrastructure Layer (Ticket Module)

### 17. Создать Eloquent Model для Ticket БД
**Статус**: [PENDING] | [Описание](./Tasks/Task_17_CreateEloquentTicketModel.md)

### 18. Создать миграцию для таблицы tickets
**Статус**: [PENDING] | [Описание](./Tasks/Task_18_CreateMigrationForTickets.md)

### 19. Создать Repository реализацию для Ticket
**Статус**: [PENDING] | [Описание](./Tasks/Task_19_CreateDatabaseRepository.md)

### 20. Создать Adapter для Freshdesk API (Ticket)
**Статус**: [PENDING] | [Описание](./Tasks/Task_20_CreateFreshdeskAdapter.md)

### 21. Создать HTTP Client обертка для API
**Статус**: [PENDING] | [Описание](./Tasks/Task_21_CreateFreshdeskClient.md)

---

## Presentation Layer (Ticket Module)

### 22. Создать Console Command для синхронизации tickets
**Статус**: [PENDING] | [Описание](./Tasks/Task_22_CreateSyncTicketsCommand.md)

### 23. Создать Service Provider для Ticket модуля
**Статус**: [PENDING] | [Описание](./Tasks/Task_23_CreateServiceProvider.md)

---

## Database & Configuration

### 24. Добавить Freshdesk API ключ в .env
**Статус**: [PENDING] | [Описание](./Tasks/Task_24_AddEnvironmentVariables.md)

### 25. Создать/обновить конфиг базы данных для тестов
**Статус**: [PENDING] | [Описание](./Tasks/Task_25_ConfigureDatabaseForTests.md)

---

## Documentation

### 26. Написать README.md для Ticket модуля
**Статус**: [PENDING] | [Описание](./Tasks/Task_26_WriteTicketModuleREADME.md)

### 27. Обновить главный README.md проекта
**Статус**: [PENDING] | [Описание](./Tasks/Task_27_UpdateMainREADME.md)

---

## Критерии завершения Phase 1

✅ **Все 27 задач отмечены как [[COMPLETED]]**

✅ **Функциональность**:
- Консольная команда `php artisan freshdesk:sync` успешно синхронизирует tickets из Freshdesk
- Данные сохраняются в SQLite БД в соответствии со схемой
- Соблюдаются rate limits (1 сек между запросами)
- Обработка ошибок и 429 ответов

✅ **Качество кода**:
- PHPStan: 0 ошибок
- PHP_CodeSniffer: compliant
- Все тесты passing (Unit, Functional, Integration, E2E)

✅ **Архитектура**:
- Clean Architecture + CQRS + Modular Monolith соблюдены
- Все layer зависимости корректны
- Service Provider правильно регистрирует компоненты

✅ **Документация**:
- PHPDoc комментарии на всех методах
- README Ticket модуля
- Обновлен главный README
