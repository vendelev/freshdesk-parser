---
description: Repository Information Overview
alwaysApply: true
---

# Обзор репозитория

## Сводка

Учебный проект для создания парсера freshdesk используя Specification-Driven Development и AI, используя Laravel и полной контейнеризацией через Docker.

### Основные компоненты
- **Backend API**: REST сервис на Laravel с Clean Architecture и CQRS
- **Docker**: Multi-stage production builds и development окружение
- **Database**: SQLite с миграциями через Laravel
- **Specification**: OpenSpec для документирования архитектуры и требований

### Основные правила

- [PHP Lead Developer Role](php-role.md)
- [Архитектура проекта](architecture.md)
- [Тестирование](testing.md)
- [Workflow при добавлении новой feature](feature-workflow.md)

### Команды Makefile

Проект использует Makefile для упрощения выполнения частых задач разработки:

- `make install` - Собрать и запустить образы, composer install, создание тестовой БД.
- `make up` - Запуск контейнеров.
- `make down` - Остановить и удалить контейнеры.
- `make update` - Пересобрать и перезапустить образы, composer install.
- `make php-test` - Выполнить все PHP проверки.
- `make php-cli` - Bash PHP контейнера.
- `make php-log` - Логи PHP контейнера.
- `make php-run CMD="..."` - Выполнить команду в PHP контейнере. Например, `make openspec-run CMD="php artisan migrate"`
- `make openspec-run CMD="..."` - Выполнить команду в OpenSpec контейнере. Например, `make openspec-run CMD="openspec list"`

## OpenSpec

Проект использует OpenSpec для Specification-Driven Development:

- **Документация архитектуры**: Спецификации в `openspec/` описывают структуру и принципы проекта
- **Изменения и функции**: Все изменения проходят через процесс OpenSpec с proposal, design и tasks
- **Команда openspec-run**: Используется для выполнения команд в OpenSpec контейнере

---

## Проекты

### Backend (PHP/Laravel)

**Конфигурация**: `backend/composer.json`

- **Язык**: PHP 8.4.13
- **Фреймворк**: Laravel 12.43
- **Сборка**: Composer
- **Структура**: Модульный монолит с Clean Architecture и CQRS
- **Тесты**: Unit, Integration, E2E в `backend/tests/Suite/{ModuleName}/`
- **Статический анализ**: PHPStan, PHPmd, Rector

### База данных

- **СУБД**: SQLite
- **Управление схемой**: Laravel миграции в `backend/database/migrations/`
- **Конфигурация**: Backend переменные окружения
- **Тестовая БД**: Отдельная конфигурация для тестов

## Конфигурация и инструменты

### Контроль качества кода

- **PHP**: PHPStan (type checking), PHP_CodeSniffer (code style), Rector (refactoring)


### Переменные окружения

- **Backend**: `backend/.env.example` — шаблон с комментариями

### Структура файлов в backend

```text
backend/
├── src/
│   ├── Core/           (Общепроектный код)
│   └── {ModuleName}/   (Модули функциональности)
├── database/
│   └── migrations/     (Миграции БД)
├── tests/
│   ├── Suite/          (Все тесты приложения)
│   └── Stub/           (Фикстуры и тестовые данные)
└── composer.json       (Зависимости)
```