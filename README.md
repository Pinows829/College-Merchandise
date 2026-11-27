# CTUKolek!

A PHP and MySQL college merchandise storefront with student accounts, inventory-aware carts, checkout, order tracking, and an admin dashboard.

## Laravel version

A Laravel 12 conversion is available in [`laravel-app/`](laravel-app/README.md). The original PHP pages remain in this folder. The Laravel version uses the existing `merch_db` database and has its own setup and administrator instructions.

To preview the Laravel version with XAMPP PHP, run this from `laravel-app/`:

```bash
/Applications/XAMPP/bin/php -S 127.0.0.1:8083 -t public
```

Then open <http://127.0.0.1:8083>.

## Run locally with XAMPP

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open `http://localhost/Untitled/College-Merchandise/` (adjust the URL if you put this folder elsewhere under `htdocs`).
3. If you have not imported the database yet, open phpMyAdmin, import `merch_db.sql`, and then import `college_courses_migration.sql` to add the college course catalog.
4. If `merch_db` is already in use, import only `college_courses_migration.sql`; it adds the six colleges and course collections without deleting existing orders or accounts.
5. Register a student account from the website to place orders.

The local database connection uses XAMPP's default `root` account with a blank password. If your MySQL credentials differ, update the connection in `db.php`.

## Create an administrator

Register a normal account first. In phpMyAdmin, select `merch_db`, open the SQL tab, and run this query with that account's email:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

Log out and log back in. The admin account opens the dashboard and can manage products, stock, and order statuses. Keep administrator access limited to trusted staff.

## Included flows

- Browse products and college collections without signing in.
- Register and sign in as a student; add in-stock sizes to a session cart.
- Place an order using GCash reference entry or Cash on Pickup.
- Review personal order history and fulfillment status.
- View sales, pending orders, and low stock in the admin dashboard.
- Create and edit products, manage sizes and inventory, and update order status.
- Add course collections and assign products to a specific course or the whole college.

GCash is recorded as a payment method and reference number; this project does not connect to a payment gateway. Generated merchandise mockups are used for product imagery.

The supplied course catalog includes the CCICT programs confirmed in the provided list. BSCS and Data Science were left out because they were marked as unverified; you can add them later from **Admin → Courses** if they are confirmed.
