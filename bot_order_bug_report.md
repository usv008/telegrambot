# BUG REPORT: Duplicate entry for key 'simpla_id' при створенні замовлення через бота

## Контекст

Telegram-бот створює замовлення через PrestaShop WebService API (POST /api/orders), отримує id_order з відповіді, і зберігає його в таблицю `bot_orders_new.external_id`. При тестовому замовленні 31 січня 2026 PrestaShop успішно створив замовлення #66694, але бот не зміг зберегти запис в свою базу.

## Помилка

```
[2026-01-31 23:54:31] local.ERROR: Order creation error: SQLSTATE[23000]:
Integrity constraint violation: 1062 Duplicate entry '66694' for key 'simpla_id'
(SQL: insert into `bot_orders_new` (`external_id`, ...) values (66694, ...))
```

**Stack trace**: `BotMenuNewController.php:1335` -> `BotMenuNewController.php:1975`

## Причина

Таблиця `bot_orders_new` має UNIQUE індекс `simpla_id` на колонці `external_id`:

```sql
UNIQUE KEY `simpla_id` (`external_id`)
```

В таблиці вже існує запис з `external_id = 66694`:

| id    | external_id | user_id   | name     | created_at          |
|-------|-------------|-----------|----------|---------------------|
| 66410 | 66694       | 491798957 | Наталья  | 2026-01-23 10:46:17 |

Цей запис був створений 23 січня для іншого клієнта. Коли тестове замовлення 31 січня отримало від PrestaShop той самий `id_order = 66694`, виникла колізія.

**Імовірна причина колізії**: Раніше зі сторони PrestaShop були видалені замовлення (тестові або під час міграції з Simpla CMS), і auto_increment створив нові замовлення з тими ж ID, які вже записані в `bot_orders_new`.

## Наслідки помилки

Метод `createOrder()` (рядок 1335) кидає виняток при `$bot_order->save()`. Через відсутність try-catch:

1. **Замовлення в PrestaShop створене** (id=66694, статус "Awaiting check payment") - все ОК
2. **Запис в `bot_orders_new` НЕ створений** для нового замовлення
3. **`BotOrderContent` НЕ заповнений** (рядки 1337-1368 не виконались)
4. **Кешбек НЕ списаний** (рядки 1370-1372 не виконались)
5. **Виняток летить вверх до `testCreateOrder()`** (рядок 1975)
6. **`$order_id` не отримано** -> код на рядку 1977 (`if ($order_id && $order_id > 0)`) не виконується
7. **Статус замовлення НЕ оновлений** (рядки 1979-1982: current_state -> 24/15)
8. **Повідомлення користувачу НЕ відправлене** (рядки 1985-2010)
9. **Кошик бота НЕ очищений** (рядок 2017: `BotCartNew::where(...)->delete()`)

## Місце в коді

**Файл**: `app/Http/Controllers/Telegram/BotMenuNewController.php`

**Метод `createOrder()`** (рядки 1292-1374):
```php
// Рядок 1308: Створення замовлення в PrestaShop
$xml = $webService->add($opt);
$id_order = (int)$xml->order->id;  // Рядок 1310

// Рядок 1314-1335: Збереження в bot_orders_new
$bot_order = new BotOrdersNew;
$bot_order->external_id = $id_order;  // <-- ТУТ КОЛІЗІЯ
// ...
$bot_order->save();  // Рядок 1335 <-- ТУТ ВИНЯТОК

return $id_order;  // Рядок 1374 - НЕ досягається
```

**Метод `testCreateOrder()`** (рядок 1975):
```php
$order_id = self::createOrder($data_order);  // Виняток тут
// Все що нижче - не виконується:
if ($order_id && $order_id > 0) {
    // Оновлення статусу, відправка повідомлення, очистка кошика
}
```

## Структура таблиці `bot_orders_new`

```sql
CREATE TABLE `bot_orders_new` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `external_id` bigint(20) NOT NULL,
  -- ...інші поля...
  PRIMARY KEY (`id`),
  UNIQUE KEY `simpla_id` (`external_id`)  -- LEGACY назва індексу з часів Simpla CMS
) ENGINE=InnoDB AUTO_INCREMENT=66593 DEFAULT CHARSET=utf8mb4;
```

**Модель**: `app/Models/BotOrdersNew.php`
- Connection: `mysql_ecopizza`
- Table: `bot_orders_new`
- Timestamps: `false`

## Рекомендовані виправлення

### Варіант 1: Обгортка в try-catch з updateOrCreate (рекомендований)

В методі `createOrder()` замінити `$bot_order->save()` на логіку яка обробляє дублікати:

```php
// Замість:
$bot_order = new BotOrdersNew;
$bot_order->external_id = $id_order;
// ...
$bot_order->save();

// Зробити:
try {
    $bot_order = new BotOrdersNew;
    $bot_order->external_id = $id_order;
    // ...всі поля...
    $bot_order->save();
} catch (\Illuminate\Database\QueryException $e) {
    if ($e->getCode() == 23000) {
        // Duplicate key - оновити існуючий запис
        $bot_order = BotOrdersNew::where('external_id', $id_order)->first();
        $bot_order->user_id = $data['user_id'];
        $bot_order->name = $data['name'];
        // ...оновити всі поля...
        $bot_order->save();
        Log::warning("Order external_id={$id_order} already existed in bot_orders_new (id={$bot_order->id}), updated.");
    } else {
        throw $e;
    }
}
```

### Варіант 2: Перевірка перед вставкою

```php
$existing = BotOrdersNew::where('external_id', $id_order)->first();
if ($existing) {
    // Оновити існуючий запис новими даними
    $existing->update([...]);
    $bot_order = $existing;
    Log::warning("Duplicate external_id={$id_order}, updated existing record.");
} else {
    $bot_order = new BotOrdersNew;
    $bot_order->external_id = $id_order;
    // ...
    $bot_order->save();
}
```

### Варіант 3: Загальний try-catch для всього createOrder

Обгорнути весь блок після `$webService->add()` в try-catch, щоб навіть при помилці `$id_order` повертався до `testCreateOrder()`:

```php
$xml = $webService->add($opt);
$id_order = (int)$xml->order->id;

try {
    // ...збереження в bot_orders_new...
    // ...збереження BotOrderContent...
    // ...кешбек...
} catch (\Exception $e) {
    Log::error("Failed to save bot order data for PrestaShop order #{$id_order}: " . $e->getMessage());
}

return $id_order;  // Завжди повертати id, навіть якщо bot DB fails
```

### Додатково: Очистка старих записів

Перевірити та видалити/оновити записи в `bot_orders_new` де `external_id` посилається на неіснуючі або чужі замовлення PrestaShop. Це запобіжить майбутнім колізіям.

```sql
-- Знайти записи де external_id вже не відповідає тому самому клієнту
-- (потребує маппінгу bot user_id -> PrestaShop id_customer)
SELECT bon.id, bon.external_id, bon.user_id, bon.name, bon.created_at
FROM bot_orders_new bon
LEFT JOIN ps_orders po ON bon.external_id = po.id_order
WHERE po.id_order IS NULL OR po.date_add > bon.created_at + INTERVAL 1 DAY
ORDER BY bon.id DESC
LIMIT 20;
```

## Статус PrestaShop

Замовлення #66694 на стороні PrestaShop створене **коректно**:
- Товари в `order_detail`: Піца Селянська (подарунок, 219 грн) + Піца Royal Beef x2 (538 грн)
- `order_cart_rule`: CartRule #2051 застосований (знижка 219 грн на подарунок)
- `ep_ordergift`: подарунок #15 відмічений як "used"
- Проблема виключно на стороні бота
