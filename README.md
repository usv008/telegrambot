# EcoPizza TelegramBot

Багатофункціональний Telegram-бот для сервісу доставки піци EcoPizza, побудований на Laravel 8.

## Про проект

Веб-додаток, що забезпечує роботу Telegram-бота з функціями:

- Каталог товарів, кошик та оформлення замовлень
- Інтеграція з платіжними системами (LiqPay, WayForPay)
- Ігри (Морський бій, Розіграш)
- Програма лояльності та кешбек
- Адмін-панель з управлінням користувачами, замовленнями, розсилками та статистикою
- Інтеграція з PrestaShop та Simpla CMS

## Технології

- **PHP** 7.4+
- **Laravel** 8.75
- **MySQL** — 6 підключень до баз даних
- **Laravel Mix** (webpack) — збірка фронтенду
- **php-telegram-bot** — бібліотека для роботи з Telegram Bot API
- **Yajra DataTables** — таблиці в адмін-панелі
- **Laravel Sanctum** — автентифікація API

## Встановлення

```bash
# Встановити PHP-залежності
composer install

# Скопіювати файл оточення та налаштувати
cp .env.example .env

# Згенерувати ключ додатку
php artisan key:generate

# Запустити міграції
php artisan migrate

# Встановити Node-залежності та зібрати фронтенд
npm install
npm run dev
```

## Запуск

```bash
# Локальний сервер розробки
php artisan serve

# Обробник черги (для асинхронних задач, наприклад масових розсилок)
php artisan queue:work
```

## Тестування

```bash
# Запустити всі тести
php vendor/bin/phpunit

# Запустити Unit-тести
php vendor/bin/phpunit --testsuite Unit

# Запустити Feature-тести
php vendor/bin/phpunit --testsuite Feature
```

## Збірка фронтенду

```bash
npm run dev          # збірка для розробки
npm run watch        # режим спостереження
npm run production   # продакшн-збірка
```

## Структура проекту

| Каталог | Опис |
|---|---|
| `app/Telegram/Commands/` | Команди Telegram-бота (Start, Menu, Order тощо) |
| `app/Http/Controllers/Telegram/` | Контролери логіки бота (меню, кошик, замовлення, ігри) |
| `app/Http/Controllers/` | Контролери адмін-панелі, оплати, API |
| `app/Models/` | Eloquent-моделі (150+), включаючи моделі PrestaShop та Simpla |
| `config/phptelegrambot.php` | Конфігурація Telegram-бота |
| `config/database.php` | Конфігурація 6 підключень до БД |
| `routes/web.php` | Маршрути (вебхуки, адмін-панель, платіжні колбеки) |
| `resources/views/` | Blade-шаблони (адмін-панель, авторизація) |
| `database/migrations/` | Міграції бази даних |

## Конфігурація

Усі секрети та налаштування зберігаються у файлі `.env`:

- Облікові дані для 6 баз даних MySQL
- Telegram Bot API токени (основний бот + Cherry бот)
- Ключі платіжних систем (LiqPay, WayForPay)
- API-ключ Google Maps
- URL-адреси колбеків

Дивіться `.env.example` для повного списку необхідних змінних.

## Ліцензія

Проект побудований на фреймворку Laravel, який є програмним забезпеченням з відкритим кодом під [ліцензією MIT](https://opensource.org/licenses/MIT).
