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

### Основные правила

- [PHP Lead Developer Role](php-role.md)
- [Архитектура проекта](architecture.md)
- [Тестирование](testing.md)
- [Workflow при добавлении новой feature](feature-workflow.md)

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