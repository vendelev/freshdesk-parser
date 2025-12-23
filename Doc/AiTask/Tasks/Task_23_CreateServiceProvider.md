# Task 23: Создать Service Provider для Ticket модуля

## Описание
Создать Service Provider для регистрации всех зависимостей Ticket модуля в Laravel контейнере (Service Container).

## Файл
`backend/src/Ticket/Presentation/Config/TicketServiceProvider.php`

## Методы
```php
public function register(): void
public function boot(): void
```

## Регистрация в register()
### Binding интерфейсов к реализациям

```php
// Repository
$this->app->bind(TicketRepositoryInterface::class, DatabaseTicketRepository::class);

// Adapter (singleton)
$this->app->singleton(FreshdeskTicketAdapter::class, FreshdeskTicketAdapter::class);

// Query Handlers
$this->app->bind(GetTicketListQueryHandler::class, GetTicketListQueryHandler::class);
$this->app->bind(GetTicketQueryHandler::class, GetTicketQueryHandler::class);

// Command Handlers
$this->app->bind(SaveTicketCommandHandler::class, SaveTicketCommandHandler::class);

// UseCase
$this->app->bind(SyncTicketsUseCase::class, SyncTicketsUseCase::class);
```

## Регистрация команды в boot()
```php
if ($this->app->runningInConsole()) {
    $this->commands([
        SyncTicketsCommand::class,
    ]);
}
```

## Требования
- Extends Illuminate\Support\ServiceProvider
- Регистрирует все интерфейсы и их реализации
- Регистрирует консольные команды
- Использует bind() для обычных сервисов, singleton() для адаптеров
- PHPDoc комментарии
- Правильная иерархия зависимостей

## Принятие
- [ ] Provider создан
- [ ] Все интерфейсы зарегистрированы
- [ ] Консольная команда зарегистрирована
- [ ] Provider добавлен в config/app.php
- [ ] PHPStan проходит без ошибок
