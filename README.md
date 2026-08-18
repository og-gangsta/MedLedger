# MedLedger — Pharmacy Inventory Management

ICT243 Web Programming Assignment — minimalist web app for the **pharmacy
inventory** domain (staff log in, view a dashboard, and manage medicine
stock and categories).

## Tech stack

- PHP (PDO + MySQL)
- MySQL / MariaDB
- Plain HTML/CSS/JS (no framework) — responsive, mobile-first sidebar

## Setup (XAMPP / WAMP / MAMP / Laragon)

1. Copy the whole `pharmacy-inventory` folder into your server's web root
   (e.g. `htdocs/` for XAMPP).
2. Start Apache and MySQL.
3. Open **phpMyAdmin**, click *Import*, and import `sql/schema.sql`.
   This creates the `pharmacy_inventory` database, its tables, and seed data.
4. If your MySQL root user has a password, edit `includes/db.php` and set
   `$DB_PASS`.
5. Visit `http://localhost/pharmacy-inventory/` in your browser.
6. Log in with the seeded admin account:
   - **Username:** `admin`
   - **Password:** set your own before importing `schema.sql` — generate a
     hash with `php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"`
     and paste it into the `INSERT INTO users` line, or update the row
     directly in phpMyAdmin after import.

   The password is intentionally not stored in plain text anywhere in this
   repo, including this README — only the hash is seeded.

## Deliverables checklist (mapped to the assignment brief)

| # | Requirement | Where |
|---|---|---|
| a | Database + tables | `sql/schema.sql` — `users`, `categories`, `medicines` |
| b | Login page + Dashboard | `login.php`, `dashboard.php` |
| c | CRUD pages | `medicines.php` + `medicine_form.php` + `medicine_delete.php` (medicines); `categories.php` (categories) |
| d | Color scheme | `assets/css/style.css` — pine green / amber / clay apothecary palette |
| e | Responsive | CSS Grid + media queries in `style.css`; sidebar collapses to a top bar under 900px |
| f | Prepared statements | Every query that includes user input uses PDO with bound parameters (`?` placeholders), e.g. `login.php`, `medicine_form.php`, `medicines.php` search |

## Project structure

```
pharmacy-inventory/
├── index.php                 # redirects to login/dashboard
├── login.php / logout.php
├── dashboard.php              # stats + "needs attention" list
├── medicines.php               # list + search + delete trigger
├── medicine_form.php           # add / edit (create + update)
├── medicine_delete.php         # delete
├── categories.php              # full CRUD for categories
├── includes/
│   ├── db.php                 # PDO connection
│   ├── auth.php                # session guard + helpers
│   ├── header.php / footer.php # shared layout (sidebar/topbar)
├── assets/
│   ├── css/style.css
│   └── js/app.js
└── sql/schema.sql
```

## Notes for your presentation

- Passwords are hashed with `password_hash()` / verified with
  `password_verify()` — never stored in plain text.
- All INSERT/UPDATE/DELETE/SELECT-with-input queries use PDO prepared
  statements (`$pdo->prepare()` + `execute([...])`), which is what stops
  SQL injection even if someone types SQL into a form field.
- The dashboard flags **low stock** (quantity ≤ reorder level) and
  **expired/near-expiry** items automatically — this is the kind of
  business logic a real pharmacy inventory system needs beyond plain CRUD.
- You can extend this further (e.g. suppliers table, sales/dispensing log)
  if you want more depth — the schema's foreign key on `category_id`
  shows the marker how relational data is structured.
