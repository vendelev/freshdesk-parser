# Руководство по выполнению AI-задач

Этот документ описывает стандартный workflow для выполнения задач в любой фазе разработки.

---

## Статусная модель и переходы

### Описание статусов

| Статус | Обозначение | Описание | Когда использовать |
|--------|-------------|---------|-------------------|
| **PENDING** | `[PENDING]` | Задача еще не начата | При первом создании списка задач или когда задача отложена |
| **IN_PROGRESS** | `[IN_PROGRESS]` | Задача сейчас выполняется | Когда ИИ начинает работать над задачей |
| **COMPLETED** | `[COMPLETED]` | Задача полностью выполнена | Когда задача 100% завершена и работает корректно |
| **BLOCKED** | `[BLOCKED]` | Задача заблокирована (опционально) | Когда есть dependency от другой задачи или ошибка, которую не удалось решить |

### Правила переходов между статусами

```
[PENDING] → [IN_PROGRESS] → [COMPLETED]
                    ↓
                [BLOCKED] → [PENDING] → [IN_PROGRESS] → [COMPLETED]
```

**Направление переходов**:
- ✅ **PENDING → IN_PROGRESS**: Когда ИИ начинает работать над задачей
- ✅ **IN_PROGRESS → COMPLETED**: Когда задача полностью готова и протестирована
- ✅ **IN_PROGRESS → BLOCKED**: Если во время выполнения обнаружена проблема, которая требует решения (например, отсутствует зависимость)
- ✅ **BLOCKED → PENDING**: Когда блокирующая проблема решена, задача переводится обратно в PENDING для переделки

### Критерии COMPLETED статуса

Задача может быть отмечена как `[COMPLETED]` только если:

1. **Функциональность реализована**: Код написан и соответствует требованиям задачи
2. **Код качественный**: Не содержит очевидных ошибок, соответствует стилю проекта
3. **Тесты написаны** (если требуются): Для тестируемых компонентов есть соответствующие тесты
4. **Нет зависимостей**: Задача не зависит от других незавершенных задач (или зависимости уже выполнены)
5. **Логирование/Документация**: PHPDoc комментарии где требуются

### Примеры переходов

**Пример 1 - Простая реализация**:
```
Task #1: Создать Entity для Ticket
[PENDING] → [IN_PROGRESS] (начали писать код)
         → [COMPLETED] (Entity написана, работает, есть тесты)
```

**Пример 2 - Заблокированная задача**:
```
Task #20: Создать Adapter для Freshdesk API
[PENDING] → [IN_PROGRESS] (начали писать)
         → [BLOCKED] (нужна HTTP клиент обертка - Task #21)
         (ИИ работает над Task #21)
Task #21: Создать HTTP Client обертка
[PENDING] → [IN_PROGRESS] → [COMPLETED]
         ↑ (Task #20 разблокирована)
Task #20: [BLOCKED] → [PENDING] → [IN_PROGRESS] → [COMPLETED]
```

### Формат изменения статуса в файле

Когда статус задачи меняется, обновляется первое упоминание в заголовке задачи:

```markdown
### 1. [PENDING] Создать Entity для Ticket   ← Изменять этот статус
```

При изменении:
```markdown
### 1. [COMPLETED] Создать Entity для Ticket   ← Обновлено на COMPLETED
```

---

## Workflow выполнения каждой задачи

### Обязательные шаги для каждой задачи

После завершения **каждой задачи** (перед отметкой как COMPLETED) необходимо выполнить:

#### 1️⃣ Написание тестов (если требуются)

**Когда писать тесты**:
- ✅ Все компоненты Application и Domain слоев **ОБЯЗАТЕЛЬНО** должны быть протестированы
- ✅ Infrastructure слой (Repository, Adapter) должен быть протестирован (Integration тесты с БД)
- ✅ Presentation слой (Command, Listener) должен быть протестирован (E2E тесты)
- ❌ Тесты для конфигурации и простых DTO можно опустить (если они не содержат логики)

**Где размещать тесты**:
```
Компонент                           Путь теста
─────────────────────────────────────────────────────────────
Entity, ValueObject                 backend/tests/Suite/Ticket/Domain/
Service                             backend/tests/Suite/Ticket/Application/Service/
Query/QueryHandler                  backend/tests/Suite/Ticket/Application/Query/
Command/CommandHandler              backend/tests/Suite/Ticket/Application/Command/
UseCase                             backend/tests/Suite/Ticket/Application/UseCase/
Repository                          backend/tests/Suite/Ticket/Infrastructure/Persistence/
Adapter                             backend/tests/Suite/Ticket/Infrastructure/Adapter/
Console Command                     backend/tests/Suite/Ticket/Presentation/Console/
```

**Минимальное требование**:
- Для каждого public метода — хотя бы один позитивный тест
- Для методов с валидацией — тесты на invalid данные
- Для методов с зависимостями — используются Mock/Stub объекты

#### 2️⃣ Документирование (PHPDoc)

- Добавить PHPDoc блоки для всех public методов
- Документировать параметры (@param)
- Документировать возвращаемое значение (@return)
- Документировать возможные исключения (@throws)

Пример:
```php
/**
 * Трансформирует DTO из API в доменную Entity.
 *
 * @param TicketApiDto $dto DTO из Freshdesk API
 *
 * @return Ticket Трансформированная сущность
 *
 * @throws InvalidTicketData Если данные не соответствуют требованиям
 */
public function transformFromApi(TicketApiDto $dto): Ticket
{
    // ...
}
```

#### 3️⃣ Запуск фиксеров кода

**Затем** запустить автоматические фиксеры для исправления кода и стиля:

```bash
# 1. Rector - Автоматическое улучшение кода (рефакторинг, модернизация)
make php-run CMD="vendor/bin/rector process"

# 2. PHPCBF - Автоматическое исправление стиля кода (PSR-12)
make php-run CMD="vendor/bin/phpcbf"
```

#### 4️⃣ Запуск Code Quality & Verification команд

**После фиксеров** запустить проверки и тесты:

```bash
# 3. PHPStan - Проверка типов (0 ошибок)
make php-run CMD="vendor/bin/phpstan analyse"

# 4. PHP_CodeSniffer - Проверка кодстиля
make php-run CMD="vendor/bin/phpcs --colors"

# 5. PHPUnit - Запуск всех тестов Ticket модуля
make php-run CMD="vendor/bin/phpunit --colors --coverage-text"
```

**Критерии успеха**:
- ✅ Rector: Код рефакторен и изменен автоматически
- ✅ PHPCBF: Кодстиль автоматически исправлен до PSR-12
- ✅ PHPStan: **0 ошибок**
- ✅ PHP_CodeSniffer: **0 нарушений**
- ✅ PHPUnit: **Все тесты PASSED**, код coverage ≥ 80%

### Чеклист для каждой задачи

```markdown
### N. [IN_PROGRESS] Название задачи

- [ ] Код реализован согласно требованиям
- [ ] Написаны все необходимые тесты
- [ ] PHP_CodeSniffer: 0 нарушений
- [ ] PHPStan: 0 ошибок
- [ ] PHPUnit: все тесты passed
- [ ] PHPDoc комментарии добавлены
- [ ] Нет очевидных ошибок или TODO

→ Когда все пункты ✅ **меняем статус на [COMPLETED]**
```

### Пример полного цикла задачи

```
### 1. [PENDING] Создать Entity для Ticket
     ↓
### 1. [IN_PROGRESS] Создать Entity для Ticket
     (Пишем Ticket.php)
     ↓
   [Пишем Unit тесты] → backend/tests/Suite/Ticket/Domain/TicketTest.php
     ↓
   [Запускаем команды]
   $ make php-run CMD="vendor/bin/phpstan analyse"
   ✓ 0 ошибок
   $ make php-run CMD="vendor/bin/phpcs"
   ✓ 0 нарушений
   $ make php-run CMD="vendor/bin/phpunit"
   ✓ 5 tests passed
     ↓
   [Добавляем PHPDoc]
     ↓
### 1. [COMPLETED] Создать Entity для Ticket ✅
```
