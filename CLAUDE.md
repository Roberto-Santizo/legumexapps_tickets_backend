# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 13 / PHP 8.3 JSON API backend for a ticket system (Legumex). Stateless JWT auth via `tymon/jwt-auth`. All user-facing strings (validation messages, response messages, comments) are in **Spanish** — keep new ones in Spanish too.

## Commands

```bash
composer setup                 # first-time: install, .env, key, migrate, npm build
composer dev                   # serve + queue:listen + pail logs + vite (concurrently)
php artisan serve              # API only, http://127.0.0.1:8000
php artisan migrate --seed     # seeds initial admin from ADMIN_* env vars (idempotent)
php artisan jwt:secret         # populate JWT_SECRET (required, empty in .env.example)

composer test                  # config:clear + artisan test (PHPUnit 12, sqlite :memory:)
php artisan test --filter=NombreDelTest
php artisan test tests/Feature/ExampleTest.php

vendor/bin/pint                # code style (Laravel Pint)
```

Required env not in `.env.example`: `MSGRAPH_TENANT_ID`, `MSGRAPH_CLIENT_ID`, `MSGRAPH_CLIENT_SECRET`, `MSGRAPH_FROM` (read via `config/services.php` → `microsoft_graph`). `MicrosoftGraphMailService` throws in its constructor if any is missing, so `POST /api/tickets` and `PATCH /api/tickets/{id}/closed` fail without them.

API docs UI: `GET /api/documentation` (Swagger page reading `resources/api-docs/openapi.yaml`, currently `paths: {}`). Registered in `bootstrap/app.php` under the `web` middleware, not `api`.

## Architecture

### Request pipeline / response envelope

Every endpoint returns the same envelope via `App\Helpers\ResponseHandler`:

```json
{ "statusCode": 200, "message": "…", "data": … }
```

- `ResponseHandler::success($data, $message, $code)` — accepts models, arrays, or `JsonResource`; if `$data` is a paginated/array with a `data` key plus meta, meta is merged to top level.
- `ResponseHandler::error(\Throwable)` — status comes from `App\Errors\ApiException::getStatusCode()` (`BadRequestError` 400, `UnauthorizedError` 401, `NotFoundError` 404); any other throwable → 500.
- Controllers wrap the body in `try { … } catch (\Throwable $th) { return ResponseHandler::error($th); }`. `bootstrap/app.php` also registers a global renderer for `ApiException` so throwing from middleware/services works without the try/catch.
- JSON rendering forced for `api/*` requests (`shouldRenderJsonWhen`).

Pattern for a new resource: `routes/<resource>.php` (required from `routes/api.php`) → `FormRequest` in `app/Http/Requests` (Spanish `messages()`) → controller with a private `findXOrFail()` that throws `NotFoundError` → optional `JsonResource` in `app/Http/Resources`.

### Auth & authorization

- Guard `api` uses driver `jwt` (`config/auth.php`); routes use `jwt.auth` middleware (from the tymon package) and `admin` alias → `App\Http\Middleware\IsAdmin`.
- `User::getJWTCustomClaims()` embeds id/name/email/role in the token.
- Roles: `App\Enums\UserRole` (`admin`, `user`). Non-admins see only tickets they created or are assigned to; authorization checks are done inline in controllers comparing `auth()->user()->role` / ids, not via Policies/Gates.
- `POST /api/register` is admin-only; there is no public signup. Initial admin comes from `InitialUserSeeder` (`config('app.initial_admin')` ← `ADMIN_*` env).

### Services bound via interfaces

`bootstrap/providers.php` registers:
- `AuthProvider`: `AuthServiceInterface` → `Services\Auth\AuthService` (login/register).
- `ImageStorageProvider`: `ImageStorageServiceInterface` → `Services\Storage\ImageStorageService` (uuid filenames on the `public` disk, JPG/PNG/WEBP only, 5 MB cap; used by `TicketAttachmentController`).

Inject the interface in controller method signatures, not the concrete class.

### Email

`App\Services\MicrosoftGraphMailService` is a self-contained fluent client for Microsoft Graph `sendMail` (client-credentials token cached 50 min, large-attachment upload sessions). Not Laravel Mail — `MAIL_*` env is unused. Templates live in `resources/views/emails/` and embed `public/images/logo.jpeg` with `cid:logo-legumex`. Ticket creation notifies all admins; closing notifies the ticket creator.

### Domain model

- Models use PHP attributes `#[Fillable]` / `#[Hidden]` (Laravel 13 style) instead of `$fillable` properties.
- `Ticket` casts `status`/`priority` to `App\Enums\TicketStatus` / `TicketPriority`; relations `user`, `assignedTo`, `closedBy`, `category`, `comments`. `ticket_number` is client-supplied, unique, and `prohibited` on update. Only admins may set `status`/`priority` on update (see `TicketRequest::rules()`).
- Category model/table is spelled `TicketCategorie` / `ticket_categories` — keep that spelling when referencing it.

## Notes

- `app/Console/Commands/routes/api-docs.php` is a broken stub (invalid class name); ignore/remove rather than extend.
- No real tests yet — only the framework `ExampleTest`s.
