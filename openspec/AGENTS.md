<!-- OPENSPEC:START -->
# OpenSpec Instructions

These instructions are for AI assistants working in this project.

Always open `@/openspec/AGENTS.md` when the request:
- Mentions planning or proposals (words like proposal, spec, change, plan)
- Introduces new capabilities, breaking changes, architecture shifts, or big performance/security work
- Sounds ambiguous and you need the authoritative spec before coding

Use `@/openspec/AGENTS.md` to learn:
- How to create and apply change proposals
- Spec format and conventions
- Project structure and guidelines

Keep this managed block so 'openspec update' can refresh the instructions.

<!-- OPENSPEC:END -->

# Инструкции для AI агентов

## Общая информация

Этот проект использует подход Specification-Driven Development, где все изменения в системе должны быть сначала описаны в виде спецификаций.

## Стандарты разработки

1. **Clean Architecture** - Следуйте принципам чистой архитектуры
2. **CQRS** - Используйте разделение команд и запросов
3. **Модульный монолит** - Код организован в независимые модули

## Работа с OpenSpec

1. Все изменения должны начинаться с создания спецификации в каталоге `changes/`
2. После утверждения изменения интегрируются в `specs/`
3. Следуйте структуре каталогов проекта при создании новых компонентов

## Структура модуля

Каждый модуль должен следовать структуре:

```
ModuleName/
├── Application/
├── Domain/
├── Infrastructure/
└── Presentation/
```

## Code Style

- Используйте строгую типизацию
- Следуйте PSR стандартам
- Все публичные методы должны иметь типизированные параметры и возвращаемые значения