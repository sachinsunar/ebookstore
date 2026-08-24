# 🛠️ Unicorn Book Shop — Setup Guide

Follow these steps once and the project will run end-to-end (products, cart,
**eSewa payments**, admin panel) on a fresh machine.

---

## 1. Requirements

| Software | Version | Notes |
|---|---|---|
| [XAMPP](https://www.apachefriends.org/) | PHP **8.x** | Includes Apache + MariaDB |
| A web browser | any | |

No Composer, Node.js, or other frameworks are needed.

---

## 2. Get the code into the web root

Clone or copy the project so it lives **inside** your XAMPP `htdocs` folder:

```bash
# Linux (/opt/lampp)
cd /opt/lampp/htdocs
git clone <your-repo-url> ebookstore
```

```powershell
# Windows (C:\xampp)
cd C:\xampp\htdocs
git clone <your-repo-url> ebookstore
```

Resulting structure:

```
htdocs/
└── ebookstore/
    ├── bookshop/              ← the application
    ├── database/
    │   └── ebookstore_setup.sql   ← the ONLY database file you need
    └── setup.md
```

> If you keep the project somewhere else, create a link into htdocs instead:
> `sudo ln -s /path/to/ebookstore /opt/lampp/htdocs/ebookstore` (Linux).

## 3. Start Apache & MySQL

Open the XAMPP Control Panel (or run `sudo /opt/lampp/lampp start`) and start
both **Apache** and **MySQL**.

## 4. Create the database (one command / one import)

The single file `database/ebookstore_setup.sql` creates everything:

- the `ebookstore` database and all 23 tables
- eSewa payment columns (`transaction_uuid`, `payment_method`,
  `payment_status`, `transaction_code`, `paid_at`) already merged in
- `admin.password` column already merged in
- Nepali catalog data: books (Muna Madan, …), authors, publishers,
  categories, provinces/districts/cities
- **one bootstrap admin account**
- **no customer accounts, orders, carts or messages** (fresh store)

### Option A — command line

```bash
# Linux
/opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/ebookstore/database/ebookstore_setup.sql

# Windows
C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\ebookstore\database\ebookstore_setup.sql
```

### Option B — phpMyAdmin

1. Open http://localhost/phpmyadmin
2. **Import** tab → choose `database/ebookstore_setup.sql` → **Go**

The file is safe to re-import — it recreates tables from scratch.

## 5. Point the app to your database

Open `bookshop/Database.php` and match your MySQL credentials:

```php
private static $server   = "localhost";
private static $user     = "root";
private static $password = "";            // default XAMPP = empty
private static $db       = "ebookstore";  // must match the imported DB
```

## 6. Open the site

| Page | URL |
|---|---|
| Home | http://localhost/ebookstore/bookshop/home.php |
| Customer sign-in / sign-up | http://localhost/ebookstore/bookshop/signIn.php |
| Admin login | http://localhost/ebookstore/bookshop/adminSignin.php |

## 7. Log in as admin

Admin login is plain **email + password** (no email verification):

```
email:    nena123maharjan@gmail.com
password: admin123
```

Change it right after first login:

```sql
UPDATE ebookstore.admin SET password='your-new-password'
WHERE email='nena123maharjan@gmail.com';
```

A customer test account can be created from the Sign Up page.

---

## 8. Payments (eSewa)

Works out of the box with eSewa's **official public sandbox**:

| Setting | Value |
|---|---|
| Merchant code | `EPAYTEST` |
| Secret key | `8gBm/:&EnhH.1/q` |
| Form URL | `https://rc-epay.esewa.com.np/api/epay/main/v2/form` |
| Test customer | eSewa ID `9806800001`–`9806800005`, password `Nepal@123`, MPIN `1122`, token `123456` |

These sandbox defaults are built in, so you can immediately:
**Buy Now → pay at eSewa sandbox → verified server-side → invoice shows PAID.**

### Real merchant credentials (production later)

Never put credentials in source code. Copy the template and fill yours in:

```bash
cd bookshop/payment
cp config.example.ini config.ini
```

```ini
ESEWA_MERCHANT_CODE = your-real-merchant-code
ESEWA_SECRET_KEY    = your-real-secret-key
ESEWA_ENVIRONMENT   = production        ; switches all URLs automatically
ESEWA_APP_BASE_URL  = https://yourdomain.com/bookshop
```

Every payment is verified server-to-server against eSewa's Transaction
Status API before an order is marked PAID — the browser can never fake a
successful payment. Payment logs: `bookshop/payment/logs/payments.log`.

## 9. Emails (verification codes)

Emails use one central config. Default mode is **local**: nothing is sent;
every message and its verification code is written to
`bookshop/logs/mail_codes.log`. This is enough for registration, forgot
password and category verification during development.

For real emails later:

```bash
cd bookshop
cp config.example.ini config.ini
```

```ini
MAIL_ENVIRONMENT = production
MAIL_SMTP_USER   = yourgmail@gmail.com
MAIL_SMTP_PASS   = sixteen-char-gmail-app-password   ; myaccount.google.com/apppasswords
MAIL_FROM_EMAIL  = yourgmail@gmail.com
```

## 10. Quick smoke test

1. Open **home.php** — books should be listed.
2. Sign up a customer (any Nepali mobile like `98XXXXXXXX`).
3. Open a book → **Buy Now** → pay at the eSewa sandbox with the test
   customer above → invoice page shows **PAID**, stock decreases.
4. Cancel at eSewa instead → order stays unpaid; **Pay Now with eSewa**
   retry appears in *Purchase History*.
5. Log in as admin → manage products/orders at `/adminPanel.php`.

## 11. Troubleshooting

| Problem | Fix |
|---|---|
| Blank page / data missing | Check `Database.php` credentials + that MySQL is running |
| `Unknown database 'ebookstore'` | Re-run the import from step 4 |
| Pages show PHP errors about permissions (Linux) | `sudo chown -R daemon:daemon /opt/lampp/htdocs/ebookstore/bookshop/payment/logs /opt/lampp/htdocs/ebookstore/bookshop/logs` or make those folders world-writable |
| Payment redirects fail on phone testing | Set `ESEWA_APP_BASE_URL` to your tunnel URL (e.g. ngrok) in `bookshop/payment/config.ini` |
| Admin can't log in | Password column empty? Run the UPDATE from step 7 |

---

That's it — clone → import one SQL file → open the browser. 🚀
