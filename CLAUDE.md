# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

EcoPizza TelegramBot — a Laravel 8 (PHP 7.4+) web application that powers a multi-function Telegram bot for a pizza delivery service. It includes an admin dashboard, ordering system, games (Sea Battle, Raffle), payment processing (LiqPay, WayForPay), and integrates with PrestaShop and Simpla CMS.

## Common Commands

```bash
# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Start local dev server
php artisan serve

# Build frontend assets
npm run dev            # development build
npm run watch          # watch mode
npm run production     # production build

# Run tests
php vendor/bin/phpunit
php vendor/bin/phpunit --testsuite Unit
php vendor/bin/phpunit --testsuite Feature

# Queue worker (for async jobs like mass messaging)
php artisan queue:work

# Laravel REPL
php artisan tinker
```

## Architecture

### Multi-Database Design

The app connects to **6 MySQL databases** configured in `config/database.php`. Each model declares its connection via `protected $connection`:

| Connection | Purpose |
|---|---|
| `mysql_bot` | Primary bot data (default) |
| `mysql_ecopizza` | Pizza shop main data (BotUser, etc.) |
| `mysql_ecopizza_bot` | EcoPizza bot database |
| `mysql_cms` | Simpla CMS tables |
| `mysql_ecopizza_vps` | EcoPizza VPS database |
| `mysql_ecopizza_stage` | PrestaShop staging database |

### Telegram Bot

- **Webhook-based** (not polling). Webhook entry: `TelegramController::webhook()` via `POST /{PHP_TELEGRAM_BOT_API_KEY}`.
- Bot commands live in `app/Telegram/Commands/` (StartCommand, MenuCommand, OrderCommand, etc.).
- Telegram-specific HTTP controllers live in `app/Http/Controllers/Telegram/` — the main one is `BotMenuNewController` which handles menu navigation, cart, and order flow.
- A second bot (Cherry Bot) is handled by `TelegramBotCherryController`.
- Config: `config/phptelegrambot.php`.

### RBAC (Role-Based Access Control)

- Custom implementation via `Role`, `Permission`, `UsersRoles`, `UsersPermissions` models.
- `HasRolesAndPermissions` trait on the `User` model.
- `RoleMiddleware` protects admin routes: `middleware(['auth', 'role:,admin_panel'])`.

### Admin Dashboard

All admin routes are under `/admin` in `routes/web.php`, protected by auth + role middleware. Uses Yajra DataTables for data rendering.

### Payment Callbacks

Payment webhooks use obfuscated URLs (hashed paths) defined in `routes/web.php`:
- LiqPay: `BotPaymentLiqPayController`
- WayForPay: `BotPaymentWayForPayController`
- PrestaShop: `BotPaymentPrestaShopController`

### External System Models

- **PrestaShop** models (`PrestaShop_*.php`) — 50+ models mapping PrestaShop database tables.
- **Simpla CMS** models (`Simpla_*.php`) — 30+ models mapping Simpla CMS tables.

### Key Entry Points

| Path | Description |
|---|---|
| `routes/web.php` | All route definitions (~290 lines) |
| `app/Http/Controllers/TelegramController.php` | Main webhook handler |
| `app/Http/Controllers/Telegram/BotMenuNewController.php` | Core bot menu/cart/order logic |
| `config/phptelegrambot.php` | Bot configuration |
| `config/database.php` | Database connections |

### Frontend

Laravel Mix (webpack) compiles `resources/js/app.js` → `public/js` and `resources/css/app.css` → `public/css`. Configuration in `webpack.mix.js`.

## Environment

All secrets (API keys, DB credentials, Telegram tokens, payment keys, Google Maps key) are in `.env`. Reference `.env.example` for required variables. The app requires credentials for 6 databases plus multiple external service API keys.

## Code Style

StyleCI is configured (`.styleci.yml`) with Laravel preset.
