# Freshdesk Parser Project Specification

## Project Overview

**Name**: Freshdesk Parser  
**Description**: Educational project for creating a Freshdesk parser using Specification-Driven Development and AI, built with Laravel and fully containerized with Docker.

This project implements a REST API service that parses data from Freshdesk, following Clean Architecture principles with CQRS pattern and Modular Monolith structure.

## Technical Stack

- **Language**: PHP 8.5
- **Framework**: Laravel 12.43
- **Database**: SQLite with Laravel migrations
- **Containerization**: Docker with multi-stage builds
- **Build Tool**: Composer

## Architecture Principles

### Design Patterns & Principles

1. **CQRS (Command Query Responsibility Segregation)**
2. **Clean Architecture**
   - Multi-layered architecture with clear dependencies
   - Domain-driven design
   - Framework-independent business logic
3. **Modular Monolith**
   - Independent modules
   - Modular APIs and contracts
   - Clear module boundaries

### Layer Dependencies

Each layer can only use lower layers:

| Слой | Может использовать                                                   |
|------|----------------------------------------------------------------------|
| **Presentation** | Infrastructure (только своего модуля), Application (только своего модуля), Domain (своего модуля и чужих)          |
| **Infrastructure** | Application (только своего модуля), Domain (своего и чужих модулей)                         |
| **Application** | Application (только своего модуля), Domain (предпочтительно только своего модуля) |
| **Domain** | Domain (своего и чужих модулей)                                      |

### Module Structure

Each module follows a standardized structure:

```
{ModuleName}/
├── Application/
│   ├── UseCase/     (User interaction scenarios)
│   ├── Command/     (CQRS commands for data modification)
│   ├── Query/      (CQRS queries for data retrieval)
│   ├── Service/     (Framework-independent logic implementations)
│   ├── Dto/        (Data Transfer Objects within Application layer)
│   └── Factory/     (Factory methods for DTOs, Responses, ValueObjects)
├── Domain/
│   ├── {InterfaceName}.php  (Contracts for dependency inversion)
│   ├── Request/     (Incoming data DTOs for Controller/Console)
│   ├── Response/    (Outgoing data DTOs from Controller)
│   ├── Entity/      (Database table structure descriptions)
│   ├── ValueObject/ (Specialized DTOs with built-in validation)
│   ├── Validation/  (Business requirement validation classes)
│   ├── Event/       (Notifications, Queries, Commands for inter-module communication)
│   └── Exception/   (Domain exceptions for Application layer errors)
├── Infrastructure/
│   ├── Repository/ (Simplified CQRS for database access)
│   └── Adapter/    (Domain interface implementations for external services)
└── Presentation/
    ├── Http/
    │   └── Controller/ (HTTP controllers for REST request handling)
    ├── Console/     (Cron scripts, daemons, console commands)
    ├── Listener/     (Subscribers to domain events)
    └── Config/      (Module configuration)
```

## Project Structure

```
.
├── backend/         (REST API implementation)
│   ├── src/
│   │   ├── Core/     (Application core - project-wide events, exceptions)
│   │   └── {ModuleName}/ (Functionality modules)
│   ├── database/
│   │   └── migrations/   (Database migrations)
│   ├── tests/
│   │   ├── Architecture/ (Architectural tests)
│   │   ├── Suite/       (Application test suite)
│   │   │   └── {ModuleName}/ (Module tests)
│   │   ├── Stub/        (Test fixtures and data)
│   │   └── TestCase.php (Framework wrapper abstract class)
├── docker/          (Docker container build files)
├── openspec/        (OpenSpec documentation)
└── docker-compose.yaml (Docker orchestration)
```

## Development Conventions

### Module Definition

A module is a self-contained part of the product responsible for specific functionality. It may contain public domain interfaces, entities, or events that define its usage boundaries (module API).

### UseCase Implementation

UseCase implements a specific user behavior scenario, coordinating Command, Query, and Service components.

### DTO (Data Transfer Object)

Typed data structures for transferring between layers and modules.

### Events

Specialized DTOs for informing other modules about events:
- **Notifications**: Past action completion messages (sync/async, multiple recipients)
- **Queries**: Synchronous data requests (single recipient)
- **Commands**: Synchronous action instructions (single recipient)

### Interface Creation Rules

Interfaces are mandatory in these cases:
1. Multiple implementations exist within a module
2. Dependency inversion is needed (Application uses Domain interface implemented by Infrastructure)
3. Inter-module functionality usage (always through Domain interface)

### Validation & Typing

#### Controller Level (Presentation)
Validates structural data characteristics:
- Field requirements (required/nullable)
- Field types (string, int, array, etc.)
- Basic formats (email, date, etc.)

#### UseCase Level (Application)
Validates business requirements:
- Logical constraints
- States
- Availability

### Class Responsibility Granularity

Keep methods in one class when:
- Constructor dependency is used in multiple methods for similar functionality
- Methods address one responsibility area

Separate into different classes when:
- Constructor dependency is used in 2 methods out of 5+
- Indicates responsibility separation

## Testing

### Test Types

1. **Unit Tests**
   - Location: `backend/tests/Suite/{ModuleName}/Application`, `backend/tests/Suite/{ModuleName}/Domain`
   - Covers: Business logic in Service, Validation classes, Domain objects and ValueObjects

2. **Functional Tests**
   - Location: `backend/tests/Suite/{ModuleName}/Application/UseCase`, `backend/tests/Suite/{ModuleName}/Application/Query`, `backend/tests/Suite/{ModuleName}/Application/Command`
   - Covers: Business logic in UseCase, Query, Command

3. **Integration Tests**
   - Location: `backend/tests/Suite/{ModuleName}/Infrastructure`
   - Covers: Adapter (external service interaction), anti-corruption layer between modules

4. **End-To-End (E2E) Tests**
   - Location: `backend/tests/Suite/{ModuleName}/Presentation`
   - Covers: HTTP controllers and routes, middleware, complete request/data lifecycle

### Quality Control Tools

- **PHPStan**: Static code analysis and type checking
- **PHP_CodeSniffer**: Code style compliance checking
- **Rector**: Automatic refactoring and code modernization
