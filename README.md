# Expense Tracker API

A RESTful Expense Tracker API built with Laravel and PostgreSQL for managing personal expenses, organizing spending categories, setting monthly budgets, and generating expense summaries.

The project focuses on backend engineering fundamentals, including relational database design, authentication, request validation, authorization policies, dynamic query building, pagination, and structured API responses.

## Features

### Expense Management

- Create, retrieve, update, and delete expenses.
- Associate expenses with categories.
- Record expense descriptions, amounts, dates, and optional notes.
- Validate positive expense amounts and supported input formats.
- Filter expenses by category, date range, and amount range.
- Search expenses by description.
- Support configurable sorting and pagination.

### Category Management

- Create, retrieve, update, and delete categories.
- Store category names, descriptions, and colors.
- Enforce unique category names per user.
- Associate categories with expenses and budgets.
- Handle category deletion according to database relationship constraints.

### Budget Management

- Create and manage monthly budgets by category.
- Define spending limits for individual categories.
- Filter budgets by month and category.
- Enforce uniqueness for each user, category, and month combination.
- Prevent category deletion when existing budgets depend on that category.

### Expense Summary

- Retrieve expense totals for a selected period.
- Group spending by category or date.
- Filter summaries by month or custom date range.
- Retrieve category-level spending information and relevant budget data.

### Authentication and Security

- Token-based authentication using Laravel Sanctum.
- User registration, login, and logout.
- Password reset and email verification endpoints.
- Form Request validation for incoming data.
- Authorization policies for protected resources.
- User-scoped database queries to protect personal financial records.

### API Design

- Versioned REST API under `/api/v1`.
- UUID-based identifiers for application resources.
- Consistent JSON responses using Laravel API Resources.
- Optional query parameters for filtering and sorting.
- Paginated collection responses with metadata.

## Technology Stack

- **Language:** PHP 8.3+
- **Framework:** Laravel 13
- **Database:** PostgreSQL
- **Authentication:** Laravel Sanctum
- **ORM:** Eloquent
- **Testing:** Pest and PHPUnit
- **API Style:** REST
- **Database Identifiers:** UUID

## Architecture

The application uses Laravel's conventional architecture to separate HTTP handling, validation, authorization, database operations, and response formatting.

| Component       | Responsibility                                                 |
| --------------- | -------------------------------------------------------------- |
| Routes          | Define API endpoints and middleware                            |
| Form Requests   | Validate request bodies and query parameters                   |
| Policies        | Determine whether users may access or modify resources         |
| Controllers     | Coordinate requests, queries, and responses                    |
| Eloquent Models | Represent database records and relationships                   |
| API Resources   | Shape JSON responses consistently                              |
| Migrations      | Define database tables, indexes, foreign keys, and constraints |

The project also uses authenticated user relationships to scope expense, category, and budget operations to the appropriate user.

## Database Design

The primary application entities are:

| Entity     | Description                                        |
| ---------- | -------------------------------------------------- |
| Users      | Authenticated application users                    |
| Categories | User-defined expense categories                    |
| Expenses   | Individual expense records                         |
| Budgets    | Monthly spending limits associated with categories |

### Relationships

- A user has many categories.
- A user has many expenses.
- A user has many budgets.
- A category belongs to a user.
- A category has many expenses.
- A category has many budgets.
- An expense belongs to a user and may belong to a category.
- A budget belongs to a user and a category.

### Data Integrity

- UUID primary keys are used for categories, expenses, and budgets.
- Category names are unique per user.
- Budgets are unique per user, category, and month.
- Expense amounts must be greater than zero.
- Foreign keys maintain referential integrity.
- Deleting a category sets associated expense category references to `NULL`.
- Categories referenced by budgets cannot be deleted.
- Indexes support common filtering and retrieval patterns.

## API Endpoints

All application endpoints below are prefixed with `/api`, and the core resource endpoints use version `v1`.

### Authentication

| Method | Endpoint                               | Description                             |
| ------ | -------------------------------------- | --------------------------------------- |
| `POST` | `/api/register`                        | Register an account                     |
| `POST` | `/api/login`                           | Authenticate and obtain an access token |
| `POST` | `/api/logout`                          | Revoke the current access token         |
| `GET`  | `/api/user`                            | Retrieve the authenticated user         |
| `POST` | `/api/forgot-password`                 | Request a password reset                |
| `POST` | `/api/reset-password`                  | Reset a password                        |
| `GET`  | `/api/verify-email/{id}/{hash}`        | Verify an email address                 |
| `POST` | `/api/email/verification-notification` | Request another verification email      |

### Expenses

| Method      | Endpoint                     | Description              |
| ----------- | ---------------------------- | ------------------------ |
| `GET`       | `/api/v1/expenses`           | List and filter expenses |
| `POST`      | `/api/v1/expenses`           | Create an expense        |
| `GET`       | `/api/v1/expenses/{expense}` | Retrieve an expense      |
| `PUT/PATCH` | `/api/v1/expenses/{expense}` | Update an expense        |
| `DELETE`    | `/api/v1/expenses/{expense}` | Delete an expense        |

**Supported expense query parameters**

| Parameter     | Description                                       |
| ------------- | ------------------------------------------------- |
| `page`        | Page number                                       |
| `per_page`    | Page size, from 1 to 100                          |
| `search`      | Search expense descriptions                       |
| `category_id` | Filter by category UUID                           |
| `from`        | Filter by starting expense date                   |
| `to`          | Filter by ending expense date                     |
| `min_amount`  | Minimum expense amount                            |
| `max_amount`  | Maximum expense amount                            |
| `sort_by`     | Sort by `created_at`, `expense_date`, or `amount` |
| `sort_order`  | `asc` or `desc`                                   |

Example:

```http
GET /api/v1/expenses?search=lunch&category_id=UUID&min_amount=100&sort_by=expense_date&sort_order=desc
```

### Categories

| Method      | Endpoint                        | Description         |
| ----------- | ------------------------------- | ------------------- |
| `GET`       | `/api/v1/categories`            | List categories     |
| `POST`      | `/api/v1/categories`            | Create a category   |
| `GET`       | `/api/v1/categories/{category}` | Retrieve a category |
| `PUT/PATCH` | `/api/v1/categories/{category}` | Update a category   |
| `DELETE`    | `/api/v1/categories/{category}` | Delete a category   |

**Supported category query parameters**

- `search` — search category names.
- `sort_by` — `created_at` or `name`.
- `sort_order` — `asc` or `desc`.
- `per_page` — page size, from 1 to 100.

### Budgets

| Method      | Endpoint                   | Description       |
| ----------- | -------------------------- | ----------------- |
| `GET`       | `/api/v1/budgets`          | List budgets      |
| `POST`      | `/api/v1/budgets`          | Create a budget   |
| `GET`       | `/api/v1/budgets/{budget}` | Retrieve a budget |
| `PUT/PATCH` | `/api/v1/budgets/{budget}` | Update a budget   |
| `DELETE`    | `/api/v1/budgets/{budget}` | Delete a budget   |

**Supported budget query parameters**

- `month` — filter by month in `YYYY-MM` format.
- `category_id` — filter by category UUID.
- `page` — page number.
- `per_page` — page size, from 1 to 100.

### Expense Summary

| Method | Endpoint          | Description                   |
| ------ | ----------------- | ----------------------------- |
| `GET`  | `/api/v1/summary` | Retrieve expense summary data |

**Supported summary query parameters**

- `group_by` — group results by `category` or `date`.
- `month` — summarize a specific month in `YYYY-MM` format.
- `from` — start date for a custom range.
- `to` — end date for a custom range.

The `month` parameter cannot be combined with `from` or `to`. When using a custom range, the start date must not be later than the end date.

## Getting Started

### Prerequisites

- PHP 8.3 or later.
- Composer.
- PostgreSQL.
- Git.
- A compatible PHP environment, such as Laravel Herd.

### Installation

**1. Clone the repository**

```bash
git clone <repository-url>
cd expense-tracker-api
```

**2. Install dependencies**

```bash
composer install
```

**3. Configure environment variables**

Create your local environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Update the database settings in `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=expense_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Create the `expense_tracker` database in PostgreSQL before running the migrations.

**4. Run database migrations**

```bash
php artisan migrate
```

**5. Start the development server**

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

**6. Authenticate with the API**

Register an account using `POST /api/register`, then authenticate using `POST /api/login`. Use the returned access token as a bearer token for protected endpoints.

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

## Running Tests

Run the automated test suite with:

```bash
php artisan test
```

Use a dedicated test database and configure the testing environment before running tests. Avoid running destructive test operations against your development or production database.

## Learning Objectives

This project demonstrates practical experience with:

- Designing relational database schemas and Eloquent relationships.
- Building RESTful APIs with Laravel.
- Implementing authentication and authorization.
- Validating request payloads and optional query parameters.
- Building dynamic database queries.
- Implementing pagination, filtering, and sorting.
- Structuring JSON responses using API Resources.
- Enforcing database constraints and referential integrity.
- Aggregating financial data for summaries and budget-related reporting.
- Testing backend endpoints and validation behavior.

## Project Status

Completed learning project.

The API includes expense, category, budget, authentication, and summary functionality. Refer to the source code for the exact implemented behavior and available validations.

## License

This project is intended for educational and portfolio purposes. Add a project-specific license if you plan to distribute or reuse the code publicly.
