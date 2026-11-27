# CTUKolek! Laravel app

This Laravel 12 version contains the student storefront, college and course filters, registration/login, session cart, checkout, order tracking, and admin product/course/order management. The original PHP version remains in the parent folder.

## Requirements

- PHP 8.2 or later with `mysqli`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, and `tokenizer` enabled.
- MySQL running in XAMPP, with the existing `merch_db` database and the college/course migration imported.
- Composer dependencies are included in `vendor/`.

## Run it

From this directory:

```bash
/Applications/XAMPP/bin/php artisan migrate --seed
/Applications/XAMPP/bin/php artisan serve --host=127.0.0.1 --port=8000
```

Then open <http://127.0.0.1:8000>. The `.env` file is configured for XAMPP's local `root` account with a blank password; adjust the DB settings if yours differ.

The migration checks for existing tables before creating them and adds no destructive table drops. The seeder upserts the six colleges and course names and does not create sample user accounts.

## Administrator account

Register a student account, then run this in phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

Log out and back in to see the admin navigation.

GCash is recorded as a payment method and reference number; payment processing is not connected to a gateway.
