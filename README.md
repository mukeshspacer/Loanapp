# Loan Finance Management System

A complete PHP + MySQL + Bootstrap web app for running a loan/finance business:
members, loans, EMI collection, receipt/payment vouchers, cash book, ledgers,
reports, and settings.

## Requirements
- PHP 7.4+ (with PDO MySQL extension enabled)
- MySQL 5.7+ / MariaDB
- Any web server: XAMPP, WAMP, LAMP, or a shared hosting plan with PHP+MySQL

## Setup (local, using XAMPP/WAMP)

1. **Copy the folder** `loan-finance/` into your server's web root:
   - XAMPP: `C:/xampp/htdocs/loan-finance`
   - WAMP: `C:/wamp64/www/loan-finance`
   - Linux/LAMP: `/var/www/html/loan-finance`

2. **Create the database.**
   Open phpMyAdmin (or the MySQL CLI) and import `db/schema.sql`.
   This creates the `loan_finance` database with all tables and a default
   `settings` row.

3. **Set your real admin password.**
   The schema ships with a placeholder password hash. Visit:
   `http://localhost/loan-finance/backend/generate_hash.php`
   in your browser — it will print a real bcrypt hash for the password
   `admin123`. Copy the SQL `UPDATE` statement shown and run it in phpMyAdmin.
   (Change the password inside `generate_hash.php` first if you want a
   different one, then re-run.)

4. **Check your DB credentials.**
   Open `backend/db.php` and confirm `$DB_HOST`, `$DB_NAME`, `$DB_USER`,
   `$DB_PASS` match your MySQL setup (defaults are `localhost` / `root` /
   no password, which matches a fresh XAMPP install).

5. **Open the app.**
   Go to `http://localhost/loan-finance/` and log in with:
   - Username: `admin`
   - Password: `admin123` (or whatever you set in step 3)

## Folder Structure

```
loan-finance/
├── index.php              (Login page)
├── dashboard.php
├── members.php
├── loans.php
├── emi.php
├── receipt.php
├── payment.php
├── ledger.php
├── reports.php
├── settings.php
├── logout.php
├── includes/
│   ├── header.php         (shared <head>, opens layout)
│   ├── sidebar.php         (shared nav)
│   └── footer.php          (closes layout, shared scripts)
├── backend/
│   ├── db.php               (DB connection + helpers)
│   ├── login_process.php
│   ├── save_member.php
│   ├── save_loan.php
│   ├── collect_emi.php
│   ├── save_voucher.php
│   ├── save_settings.php
│   ├── add_user.php
│   ├── backup_db.php
│   ├── export_ledger.php
│   ├── print_voucher.php
│   ├── print_agreement.php
│   └── generate_hash.php   (run once to set a real admin password)
├── assets/
│   ├── css/style.css
│   ├── js/app.js
│   └── images/             (uploaded member photos & logo land here)
└── db/schema.sql
```

## How it works

- **Members** — add/edit/delete, auto-generated Member ID (MEM0001, ...),
  optional photo upload, search by name/mobile/ID.
- **Loans** — pick a member, enter amount/rate/tenure; EMI auto-calculates
  (flat interest method) but is editable. Saving a loan auto-generates its
  full month-by-month EMI schedule. Loan Number auto-generated (LN0001, ...).
  "Print Agreement" produces a printable loan agreement.
- **EMI Collection** — search by member name or loan number, see the full
  schedule, collect a payment against any pending installment. Each
  collection automatically creates a Receipt Voucher and marks the loan
  Closed once every installment is paid.
- **Receipt / Payment Vouchers** — manual entry forms with auto-numbering
  (RCT0001, PMT0001, ...) and a print-friendly voucher template.
- **Cash Book / Ledger** — filter by Today / This Month / Custom Date,
  running balance, export to CSV (opens in Excel) or a print-to-PDF view.
- **Reports** — Daily/Monthly Collection, Outstanding Loans, Overdue EMI,
  Profit & Loss, Member Statement, and the four ledger views.
- **Settings** — company name/logo, default interest rate, add system
  users, and a one-click database backup (requires `mysqldump` on the
  server).

## Notes / next steps you may want

- Passwords are hashed with PHP's `password_hash()` (bcrypt) — never store
  plain text passwords.
- The EMI calculation uses a simple flat-interest formula. If you need
  reducing-balance (diminishing) interest instead, that formula can be
  swapped into `loans.php`'s JS and `backend/save_loan.php`.
- `backend/backup_db.php` shells out to `mysqldump`; on shared hosting
  without CLI access, use phpMyAdmin's Export feature instead.
- For production, move `backend/db.php` credentials into environment
  variables rather than hardcoding them.
