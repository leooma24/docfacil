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

Plans: Free ($0), Básico ($149), Pro ($299), Clínica ($499). Commission: 3× monthly price, split 50/50 across first two payments. All paid plans are commissionable. Source of truth: `Commission::monthlyPriceForPlan()`.

## Testing

- Tests use **SQLite in-memory** (`phpunit.xml`), prod uses **MySQL**
- SQLite is more permissive — queries with non-existent columns may pass locally but 500 on prod. Always verify column names against the migration, not the model's `$fillable`
- Multi-tenancy isolation is tested in `tests/Feature/MultiTenancyTest.php`
- Doctor resource CRUD is covered in `tests/Feature/DoctorResourcesTest.php` (37 tests)
- Consultation flow has 33 tests in `tests/Feature/ConsultationTest.php`

## Key Gotchas

- **Tailwind v4 on prod**: Some responsive utility classes don't compile on production. For critical UI (hero gradients, glassmorphism), use inline `style=""` attributes instead of Tailwind classes.
- **Filament Resource slugs are Spanish**: `'pacientes'`, `'citas'`, `'recetas'`, `'cobros'`, `'expediente-clinico'`, `'consentimientos'`, `'servicios'`, `'odontogramas'`.
- **EditOdontogram has a custom Livewire view** — don't add `HasFormHero` to it; it has its own interactive canvas editor.
- **Production deploy** is manual SSH: `ssh root@tu-app.co`, `cd /var/www/docfacil`, `git pull`, clear caches, run migrations.
- **Commission model is the pricing source of truth** — update `Commission::monthlyPriceForPlan()` first, then propagate to landing, emails, docs, ClinicResource, and Upgrade page.
