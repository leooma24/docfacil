# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

DocFácil is a multi-tenant SaaS for medical/dental clinics in Mexico. Built with Laravel 12, Filament 3, Livewire 3, and Tailwind CSS v4. All user-facing text, URLs, and labels are in Spanish.

## Commands

```bash
composer run dev          # Serve + queue + logs + vite (all concurrent)
composer run test         # Clear config cache + run PHPUnit
php artisan test --filter="ConsultationTest"           # Single test file
php artisan test --filter="test_patient_list_page_loads"  # Single test method
npm run build             # Vite production build
php artisan migrate       # Run pending migrations
php artisan config:clear && php artisan view:clear      # Clear caches (do after .env or blade changes)
```

## Architecture

### Four Filament Panels

| Panel | URL | Provider | Resources in |
|---|---|---|---|
| Admin | `/admin` | `AdminPanelProvider` | `app/Filament/Resources/` |
| Doctor | `/doctor` | `DoctorPanelProvider` | `app/Filament/Doctor/Resources/` |
| Sales | `/ventas` | `SalesPanelProvider` | `app/Filament/Sales/Resources/` |
| Patient | `/paciente` | `PacientePanelProvider` | `app/Filament/Paciente/Resources/` |

The Doctor panel is the primary product. It has custom login/register pages, a `VerifyClinicPlan` middleware for plan gating, and the bulk of the resources, widgets, and custom pages.

### Multi-Tenancy via clinic_id

Every data model uses `BelongsToClinic` trait (`app/Models/Concerns/BelongsToClinic.php`) which applies a global `ClinicScope` — all queries are automatically filtered by `auth()->user()->clinic_id`. The trait also auto-fills `clinic_id` on create. Doctor panel resources additionally override `getEloquentQuery()` with explicit `where('clinic_id', ...)`.

### AI System (Currently Disabled)

All AI features are behind a kill switch: `AI_ENABLED=false` in `.env`. The central gatekeeper is `app/Services/AI.php` with `enabled()`, `dailyLimitReached()`, and `log()` methods. Seven AI service classes gate on `AI::enabled()` and return null when off. UI hides AI elements with `@if(config('services.ai.enabled'))`. The code stays intact for Phase 2 activation — just flip `AI_ENABLED=true` and `php artisan config:clear`.

Usage tracking goes to `ai_usage_logs` table via `AiUsageLog` model. Admin monitor at `/admin/ai-monitor`.

### Visual Design System (Doctor Panel)

List pages, Create pages, and Edit pages all use a glassmorphism hero banner with per-module gradient colors. This is DRY via:
- `HasListHero` trait + `list-with-hero.blade.php` view (lists with stats)
- `HasFormHero` trait + `create-with-hero.blade.php` / `edit-with-hero.blade.php` (forms)
- Shared partials in `resources/views/filament/doctor/partials/`

Each resource defines its own `getHeroConfig()` returning title, icon, gradient, accent color, and stats array. When adding a new resource, use these traits to maintain visual consistency.

### Scheduled Commands

Defined in `routes/console.php`, cron runs every minute on prod:
- `docfacil:send-trial-emails` — trial/beta expiry drips (daily 9am)
- `docfacil:send-engagement` — inactive clinic nudges (daily 10am)
- `docfacil:send-reminders` — WhatsApp appointment reminders (hourly)
- `docfacil:send-prospect-emails` — sales pipeline emails (hourly)

### Pricing

Plans: Free ($0), Básico ($499), Pro ($999), Clínica ($1,999). Annual = monthly × 10 (2 months free). Commission: 3× monthly price. Payout depends on billing cycle:
- **Monthly sale** → commission split 50/50 across first two payments (`payout_type='split'`).
- **Annual sale** → commission paid 100% in one lump sum (`payout_type='lump_sum'`).

All paid plans are commissionable. Source of truth: `Commission::monthlyPriceForPlan()` and `Commission::annualPriceForPlan()`. `Commission::generateForSale()` is idempotent — safe to call from both Stripe webhook retries and SPEI monthly renewals.

### Billing (Stripe + SPEI manual)

Two payment methods coexist in `/doctor/actualizar-plan`:
- **Stripe Checkout** — card payments, auto-renewal. Config in `config/services.php:stripe`. Webhook at `/billing/stripe/webhook` with signature verification and idempotency via `stripe_webhook_events` table.
- **Manual SPEI** — bank transfer, admin approves. Config in `config/services.php:spei` (CLABE, titular, admin_emails). Receipts stored on **private disk** (`storage/app/spei-receipts/`), served via signed route `/billing/spei-receipts/{id}` (auth + admin/owner check). Admin review at `/admin/spei-payments`. `SpeiReviewService::approve()` activates plan + generates commissions.

## Testing

**TDD is the methodology: the test comes first and must fail before the code exists.** A test written after the code only proves the code does what it already does. A green suite is evidence only about the paths the tests drive, on the engine they run on.

### Running it

PHP 8.4, not the system 8.5 (`openspout/openspout` pins `~8.4.0`), and `1024M` — Filament's views exhaust the 128M default and the run dies with an OOM that reads like a failing test:

```bash
/opt/homebrew/opt/php@8.4/bin/php -d memory_limit=1024M vendor/bin/phpunit
```

### Pick the layer

| What changed | Test it with |
|---|---|
| A Filament page or form | `Livewire::test(ThePage::class)` — fill it, `call('save')`, re-mount, assert |
| A route, middleware, or plan gate | `$this->actingAs($user)->get(...)` and assert the status |
| A model rule or calculation | A plain test on the model |
| A migration or column | A test that seeds the shape production has (see below) |

**The screen is tested against the screen.** Seeding the model directly with `Model::create([...])` proves nothing about the page that writes it. That is exactly how the anesthesia settings shipped rendering three fields, reporting "Configuración guardada", and saving none of them: the tests wrote the columns themselves and never touched the form.

### What the suite cannot see

- Tests use **SQLite in-memory** (`phpunit.xml`), prod is **MySQL**. SQLite ignores `varchar(N)` length and `decimal(M,D)` scale, so a green run says nothing about MySQL. Verify column names against the migration, not the model's `$fillable`.
- **The database starts empty.** Data-dependent failures — `->change()` dropping `nullable()`, a NULL reaching a `NOT NULL` column — pass on an empty table and break a populated one, on either engine. Seed a row, a NULL, and a long string.

Both are worked through in the `verifying-stack-behavior` skill.

### Existing coverage

- Multi-tenancy isolation: `tests/Feature/MultiTenancyTest.php`
- Doctor resource CRUD: `tests/Feature/DoctorResourcesTest.php` (37 tests)
- Consultation flow: `tests/Feature/ConsultationTest.php` (33 tests)

## Key Gotchas

- **Tailwind v4 on prod**: Some responsive utility classes don't compile on production. For critical UI (hero gradients, glassmorphism), use inline `style=""` attributes instead of Tailwind classes.
- **Filament Resource slugs are Spanish**: `'pacientes'`, `'citas'`, `'recetas'`, `'cobros'`, `'expediente-clinico'`, `'consentimientos'`, `'servicios'`, `'odontogramas'`.
- **EditOdontogram has a custom Livewire view** — don't add `HasFormHero` to it; it has its own interactive canvas editor.
- **Production deploy** is manual SSH: `ssh root@tu-app.co`, `cd /var/www/docfacil`, `git pull`, clear caches, run migrations.
- **Prod needs a queue worker running.** `QUEUE_CONNECTION=database`, and Filament's email-verification notification is queued — without a worker, new doctors register, land on "check your email", and the mail never sends. Managed by systemd: `docfacil-queue.service` (enabled, restarts on boot). Check with `systemctl status docfacil-queue`; log at `/var/log/docfacil-queue.log`. A stuck queue looks like rows piling up in the `jobs` table.
- **Scheduled commands** run from root's crontab, staggered 36s after the minute so the five Laravel projects on this box don't spike PHP at once: `* * * * * sleep 36; cd /var/www/docfacil && php artisan schedule:run`.
- **Filament caches its panel components on prod** (`bootstrap/cache/filament/`). Changes to a `*PanelProvider` — widgets, pages, resource registration — do NOT take effect with `view:clear` + `config:clear` alone. Run `php artisan filament:clear-cached-components && php artisan filament:cache-components` too, or the old panel definition keeps serving.
- **Commission model is the pricing source of truth** — update `Commission::monthlyPriceForPlan()` first, then propagate to landing, emails, docs, ClinicResource, and Upgrade page.
