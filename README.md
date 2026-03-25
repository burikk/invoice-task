## Invoice Structure:

The invoice should contain the following fields:
* **Invoice ID**: Auto-generated during creation.
* **Invoice Status**: Possible states include `draft,` `sending,` and `sent-to-client`.
* **Customer Name** 
* **Customer Email** 
* **Invoice Product Lines**, each with:
  * **Product Name**
  * **Quantity**: Integer, must be positive. 
  * **Unit Price**: Integer, must be positive.
  * **Total Unit Price**: Calculated as Quantity x Unit Price. 
* **Total Price**: Sum of all Total Unit Prices.

## Required Endpoints:

1. **View Invoice**: Retrieve invoice data in the format above.
2. **Create Invoice**: Initialize a new invoice.
3. **Send Invoice**: Handle the sending of an invoice.

## Functional Requirements:

### Invoice Criteria:

* An invoice can only be created in `draft` status. 
* An invoice can be created with empty product lines. 
* An invoice can only be sent if it is in `draft` status. 
* An invoice can only be marked as `sent-to-client` if its current status is `sending`. 
* To be sent, an invoice must contain product lines with both quantity and unit price as positive integers greater than **zero**.

### Invoice Sending Workflow:

* **Send an email notification** to the customer using the `NotificationFacade`. 
  * The email's subject and message may be hardcoded or customized as needed. 
  * Change the **Invoice Status** to `sending` after sending the notification.

### Delivery:

* Upon successful delivery by the Dummy notification provider:
  * The **Notification Module** triggers a `ResourceDeliveredEvent` via webhook.
  * The **Invoice Module** listens for and captures this event.
  * The **Invoice Status** is updated from `sending` to `sent-to-client`.
  * **Note**: This transition requires that the invoice is currently in the `sending` status.

## Technical Requirements:

* **Preferred Approach**: Domain-Driven Design (DDD) is preferred for this project. If you have experience with DDD, please feel free to apply this methodology. However, if you are more comfortable with another approach, you may choose an alternative structure.
* **Alternative Submission**: If you have a different, comparable project or task that showcases your skills, you may submit that instead of creating this task.
* **Unit Tests**: Core invoice logic should be unit tested. Testing the returned values from endpoints is not required.
* **Documentation**: Candidates are encouraged to document their decisions and reasoning in comments or a README file, explaining why specific implementations or structures were chosen.

## Note on the Notification Module:

The Notification module included in this repository is a minimal, mock integration example. It is intentionally simple and should not be treated as a reference for DDD structure or for the expected invoice design.

## Setup Instructions:

* Start the project by running `./start.sh`.
* To access the container environment, use: `docker compose exec app bash`.

# Documentation — Invoice Module

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Domain-Driven Design (DDD)](#domain-driven-design-ddd)
3. [Module & Layer Structure](#module--layer-structure)
4. [Domain Layer](#domain-layer)
5. [Application Layer](#application-layer)
6. [Infrastructure Layer](#infrastructure-layer)
7. [Presentation Layer](#presentation-layer)
8. [CQRS](#cqrs)
9. [Invoice Lifecycle](#invoice-lifecycle)
10. [Cross-Module Communication](#cross-module-communication)
11. [Exception Handling](#exception-handling)
12. [Testing Strategy](#testing-strategy)
13. [Key Design Decisions](#key-design-decisions)

---

## Architecture Overview

**Clean Architecture**, **DDD**, and **CQRS**.

```
┌──────────────────────────────────────────────────┐
│  Frameworks & Drivers (Laravel, DB facade)       │
│  ┌────────────────────────────────────────────┐  │
│  │  Interface Adapters                        │  │
│  │  (Controllers, Repository Implementations) │  │
│  │  ┌──────────────────────────────────────┐  │  │
│  │  │  Use Cases                           │  │  │
│  │  │  (Handlers, Commands, Queries)       │  │  │
│  │  │  ┌────────────────────────────────┐  │  │  │
│  │  │  │  Entities                      │  │  │  │
│  │  │  │  (Domain Entities, VOs, Enums) │  │  │  │
│  │  │  └────────────────────────────────┘  │  │  │
│  │  └──────────────────────────────────────┘  │  │
│  └────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────┘

Dependencies point inward. The Domain layer has zero framework dependencies.
```

| Clean Architecture Layer | Project Layer | Examples |
|---|---|---|
| **Entities** | `Domain/` | `Invoice`, `InvoiceProductLine`, `Price`, `Quantity`, `StatusEnum` |
| **Use Cases** | `Application/` | `CreateInvoiceHandler`, `SendInvoiceHandler`, `ViewInvoiceHandler` |
| **Interface Adapters** | `Presentation/`, `Infrastructure/` | Controllers, Form Requests, Repository implementations |
| **Frameworks & Drivers** | Laravel | `DB` facade, service providers, routing |

```
src/Modules/
├── Invoices/          # Core bounded context — invoice management
├── Notifications/     # Supporting module — notification delivery (provided mock)
└── Shared/            # Shared Kernel — Bus, Exceptions, Value Objects
```

> **Note:** The level of architectural rigor was chosen to demonstrate knowledge of these patterns, not because the task's complexity strictly demands it.

---

## Domain-Driven Design (DDD)

### Strategic Design

| Concept | Implementation |
|---|---|
| **Bounded Context** | `Invoices` — core context with its own domain model |
| **Supporting Module** | `Notifications` — delivers notifications via public API |
| **Shared Kernel** | `Shared/` — Bus interfaces, base exceptions, UUID trait |
| **Anti-Corruption Layer** | `NotificationFacadeInterface` — Invoice depends on Notification's API, not internals |
| **Published Language** | `NotifyData` DTO, `WebhookDeliveredEvent` — cross-module contracts |

### Tactical Design

| Concept | Implementation |
|---|---|
| **Aggregate Root** | `Invoice` — owns `InvoiceProductLine` child entities |
| **Entity** | `Invoice`, `InvoiceProductLine` — objects with identity and lifecycle |
| **Value Object** | `InvoiceId`, `ProductLineId`, `CustomerName`, `CustomerEmail`, `Price`, `Quantity`, `ProductLineName` |
| **Repository** | `InvoiceWriteRepositoryInterface`, `InvoiceReadRepositoryInterface` |
| **Domain Event** | `WebhookDeliveredEvent` |
| **Factory Method** | `Invoice::create()`, `Invoice::reconstitute()`, `InvoiceProductLine::create()`, `InvoiceProductLine::reconstitute()` |

---

## Module & Layer Structure

```
Invoices/
├── Domain/              # Pure business logic, zero framework dependencies
│   ├── Entities/        # Invoice (Aggregate Root), InvoiceProductLine (Child Entity)
│   ├── Enums/           # StatusEnum
│   ├── Exceptions/      # Domain-specific exceptions
│   ├── Repository/      # Repository interfaces (ports)
│   └── ValueObjects/    # Immutable value types
├── Application/         # Use cases, orchestration
│   ├── Exceptions/      # Application-level exceptions
│   └── UseCase/         # Commands, Handlers, Listeners, Queries
├── Infrastructure/      # Framework-dependent implementations
│   ├── Eloquent/        # ORM Models (if used, but in this case we use DB facade)
│   └── Providers/       # Service providers
│   └── QueryBuilder/    # DB facade repositories
└── Presentation/        # HTTP layer
    ├── Http/            # Controllers, Requests
    ├── ViewModels/      # Read-optimized DTOs
    └── routes.php
```

---

## Domain Layer

### `Invoice` — Aggregate Root

- **Private constructor** with named factories: `create()` (new invoices, always `draft`) and `reconstitute()` (rebuild from DB without re-validating).
- **`addProductLine()`** — only allowed in `draft` status.
- **`send()`** — `draft` → `sending`. Guards: must be `draft`, must have ≥ 1 product line.
- **`markAsDelivered()`** — `sending` → `sent-to-client`. Guard: must be `sending`.
- **Timestamps** — `createdAt` set once, `updatedAt` refreshed on each mutation.

### `InvoiceProductLine` — Child Entity

Modeled as an **entity** (not VO) because it has its own identity (`ProductLineId`) — two lines with identical data are distinct items. No repository of its own; persisted through the `Invoice` aggregate root.

### Value Objects

All immutable (`readonly`), private constructors, `create()` factories. Validate invariants at construction time:

| Value Object | Invariant |
|---|---|
| `InvoiceId`, `ProductLineId` | Valid UUID |
| `CustomerName`, `ProductLineName` | Non-empty string |
| `CustomerEmail` | Non-empty, valid email format |
| `Price`, `Quantity` | Positive integer (> 0) |

By enforcing constraints at the VO level, it's **impossible** to construct an `InvoiceProductLine` with invalid values. `Invoice::send()` does not re-validate — the type system guarantees correctness.

### Domain Exceptions

Each business rule has a dedicated exception extending `DomainException`, mapped globally to `422 Unprocessable Entity`.

---

## Application Layer

Uses a **Command/Query Bus** to decouple Presentation from Application:

| Handler | Responsibility |
|---|---|
| `CreateInvoiceHandler` | Creates domain entity, adds product lines, persists |
| `SendInvoiceHandler` | Loads invoice, calls `send()`, persists, dispatches notification |
| `ViewInvoiceHandler` | Loads view from read repository, throws 404 if not found |
| `InvoiceDeliveredListener` | Listens for `WebhookDeliveredEvent`, calls `markAsDelivered()`, persists |

`InvoiceNotFoundException` extends `ApplicationException` (not `DomainException`) — maps to `404`, because "not found" is an application concern, not a domain rule violation.

---

## Infrastructure Layer

Two repositories using Laravel's `DB` facade (Query Builder), not Eloquent ORM:

- **Write** (`QueryBuilderInvoiceWriteRepository`) — upsert pattern with `DB::transaction()`. Product lines are replaced entirely on each save.
- **Read** (`QueryBuilderInvoiceReadRepository`) — returns `InvoiceView`/`InvoiceProductLineView` DTOs with computed fields.

**Why `DB` facade?** Simpler mapping, no ORM leakage, no `$fillable`/model concerns. Domain entities are explicitly constructed via `reconstitute()`.


---

## Presentation Layer

| Controller | Route | Description |
|---|---|---|
| `CreateInvoiceController` | `POST /api/invoices` | Validates request, dispatches command |
| `ViewInvoiceController` | `GET /api/invoices/{id}` | Dispatches query, returns JSON |
| `SendInvoiceController` | `POST /api/invoices/{id}/send` | Dispatches command |

`CreateInvoiceRequest` validates at the presentation layer (first line of defense). Domain VOs provide a second line (defense in depth). Routes use `->whereUuid('id')` to reject non-UUID parameters.

---

## CQRS

```
Write: Controller → CommandBus → Handler → Domain Entity → WriteRepository (DB::transaction)
Read:  Controller → QueryBus   → Handler → ReadRepository → ViewModel (computed fields)
```

Separate repositories allow the read side to return flat DTOs with computed `totalUnitPrice` and `totalPrice`, while the write side works with full domain entities.

---

## Invoice Lifecycle

```
     create() ──▶ DRAFT ──send()──▶ SENDING ──markAsDelivered()──▶ SENT-TO-CLIENT
                          Guards:              Guard:
                          - draft status       - sending status
                          - ≥ 1 product line   Trigger: WebhookDeliveredEvent
```

State transitions are **enforced by the domain entity**. Each throws a domain exception if preconditions are violated.

---

## Cross-Module Communication

1. **`NotificationFacadeInterface`** — Invoice depends on Notification's public API interface, not internals.
2. **`WebhookDeliveredEvent`** — Notification dispatches; Invoice listens via `InvoiceDeliveredListener`.
3. **`NotifyData` DTO** — cross-module data transfer.

---

## Exception Handling

```
DomainException      → 422 Unprocessable Entity
ApplicationException → 404 Not Found
```

Registered in `bootstrap/app.php` via `withExceptions()`. Consistent JSON envelope: `{"error": "message"}`.

---

## Testing Strategy

**Unit tests** — pure PHP, no framework. Cover domain entities, value objects, state transitions, invariants.

**Feature tests** — Laravel HTTP tests with `RefreshDatabase`. Seed via `DB::table()`, assert HTTP status + JSON structure + database state. Mock external dependencies where needed. Use Data Providers for parametric scenarios.

```
tests/Unit/Modules/Invoices/Domain/Entities/    # InvoiceTest, InvoiceProductLineTest
tests/Unit/Modules/Invoices/Domain/ValueObjects/ # All 7 VO tests
tests/Feature/Invoice/Http/                      # Create, View, Send controller tests
tests/Feature/Notification/Http/                 # Webhook controller test
```

---

## Key Design Decisions

| Decision | Reasoning |
|---|---|
| **InvoiceProductLine as Entity** | Has identity — two identical lines are distinct. Future-proof for per-line operations. |
| **Private constructors + factories** | `create()` for new, `reconstitute()` for DB hydration. Always valid state. |
| **`reconstitute()` skips validation** | Persisted data was validated at creation. Re-validating breaks historical data if rules change. |
| **Separate read/write repositories** | CQRS: read returns ViewModels, write works with domain entities. |
| **`DB` facade over Eloquent** | No ORM coupling, simpler mapping, no model concerns in domain. |
| **`DB::transaction()` on save** | Atomic persistence of aggregate (invoice + product lines). |
| **VO-level validation** | Makes illegal states unrepresentable at the type level. |
| **Dedicated exception per rule** | Self-documenting, testable, maps to HTTP status codes. |
| **View Models compute totals** | Presentation concern, not domain state. Computed on read side. |
| **Event-driven cross-module** | No direct coupling between Invoice and Notification modules. |
