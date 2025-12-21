# Change: [Описание изменения]

## Why

1-2 предложения о проблеме или возможности.

Пример:
> Необходимо добавить возможность экспорта отчетов в формате Excel, так как клиенты просят выгружать данные для анализа в сторонних системах. Текущий формат CSV не поддерживает сложные структуры данных и форматирование.

## What Changes

- [Перечислить основные изменения]
- [Отметить breaking changes как **BREAKING**]

Пример:
- Добавить методы `generateExcelReport()` в `ReportService`
- Создать DTO `ExportReportRequest` для параметров экспорта
- **BREAKING**: Изменить сигнатуру `generateReport()` (было `string`, станет `ExportResponse`)

## Impact

- **Affected specs**: [перечислить затронутые capabilities]
- **Affected code**: [ключевые файлы/модули]
- **Breaking Changes**: Опишите обратно несовместимые изменения (если есть)
- **Migration**: Опишите необходимость миграции данных или кода (если нужна)
- **Dependencies**: Новые внешние библиотеки (если требуются)
- **Performance**: Оцените влияние на производительность

Пример:
- **Affected specs**: `reporting`, `api`
- **Affected code**: `backend/src/Reporting/`, `backend/database/migrations/`
- **Breaking Changes**: Нет
- **Migration**: Требуется добавить миграцию для таблицы `report_exports`
- **Dependencies**: Добавлена библиотека `PhpOffice\PhpSpreadsheet`
- **Performance**: Увеличение памяти на 5-10MB при обработке больших отчетов