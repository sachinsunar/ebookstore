# 📚 Unicorn Book Shop — PHP/MySQL E-Commerce (eSewa Payments)

An e-commerce website for a Nepal-based bookshop built with **HTML**, **CSS**, **JavaScript**, **PHP**, **MySQL**, and **Bootstrap**. Payments are processed with **eSewa** (Nepal's digital wallet) using the official **ePay v2** API.

Owner / Developer: **Nena Maharjan** — nena123maharjan@gmail.com

---

## 🎯 Features

### User side
- Sign up / sign in with email verification
- Browse, basic & advanced search, category browsing, product details
- Cart & watchlist
- **eSewa payment** with full server-side verification
- Purchase history with payment status and retry for unpaid orders
- Chat/messaging with admin

### Admin panel
- User & product management
- Order/delivery status management
- Sales dashboard, customer messages

---

## 💳 eSewa Payment Integration

### Payment flow

```
Customer clicks Buy Now
   ↓
Server creates a PENDING order + unique transaction UUID
   ↓
Signed form (HMAC-SHA256) auto-submits to eSewa
   ↓
Customer pays at eSewa
   ↓
eSewa redirects back to success/failure URL
   ↓
Backend verifies: response signature → order ownership
→ merchant code → amount vs DB total → Status API = COMPLETE
   ↓
Order marked PAID (atomically), stock reduced, cart cleared
   ↓
Invoice page
```

The browser can never mark an order as paid. Every success redirect is verified server-to-server against eSewa's Transaction Status API before the order is finalized. Duplicate callbacks and retries are safe (atomic status transition + unique transaction UUID per attempt).

### Configuration

Credentials are stored **outside source code**, in this priority order:

1. Environment variables:
   ```bash
   ESEWA_MERCHANT_CODE=your_merchant_code
   ESEWA_SECRET_KEY=your_secret_key
   ESEWA_ENVIRONMENT=sandbox        # or "production"
   ESEWA_APP_BASE_URL=http://localhost/ebookstore/bookshop
   ```
2. `bookshop/payment/config.ini` — copy from `bookshop/payment/config.example.ini`:

   ```ini
   ESEWA_MERCHANT_CODE = your_merchant_code
   ESEWA_SECRET_KEY    = your_secret_key
   ESEWA_ENVIRONMENT   = sandbox
   ESEWA_APP_BASE_URL  = http://localhost/ebookstore/bookshop
   ```

`config.ini` is git-ignored. Never commit real credentials. The fallback defaults are eSewa's **official public sandbox test values** (`EPAYTEST`) — local development only!

### Endpoints used (official, from developer.esewa.com.np)

| Purpose | Sandbox | Production |
|---|---|---|
| Payment form | `https://rc-epay.esewa.com.np/api/epay/main/v2/form` | `https://epay.esewa.com.np/api/epay/main/v2/form` |
| Transaction status verification | `https://rc-epay.esewa.com.np/api/epay/transaction/status/` | `https://epay.esewa.com.np/api/epay/transaction/status/` |

### Testing in sandbox

Use eSewa's published test customer: **eSewa ID `9806800001`–`9806800005`, password `Nepal@123`, MPIN `1122`, token `123456`.**

To test callbacks on a phone/tunnel, expose your host and set `ESEWA_APP_BASE_URL` accordingly (e.g. `https://xxxx.in.ngrok.io/ebookstore/bookshop`). Do not disable SSL verification to make this work.

### Payment logs

Structured JSON logs (no secrets) are written to `bookshop/payment/logs/payments.log`: initiation, redirects, verification results, failures, duplicates.

---

## 🗄️ Database changes (vs original project)

Run once:

```bash
mysql -u root ebookstore < database/migration_esewa_payment.sql
```

Adds gateway-neutral columns to the existing `invoice` (order) table:

| Column | Type | Purpose |
|---|---|---|
| `transaction_uuid` | VARCHAR(64), UNIQUE | Per-attempt payment reference |
| `payment_method` | VARCHAR(30) | `esewa` (legacy rows marked `legacy`) |
| `payment_status` | VARCHAR(15) | `PENDING` / `PAID` / `FAILED` / `CANCELLED` |
| `transaction_code` | VARCHAR(50) | eSewa reference id (`ref_id`) |
| `paid_at` | DATETIME | Confirmation timestamp |

Delivery state remains untouched (`order_status`: 1=Waiting for accept, 2=Order Placed, 3=Delivered).

---

## 🔧 Getting started (XAMPP)

1. Place the project folder under `/opt/lampp/htdocs` (Linux) or `C:\xampp\htdocs` (Windows).
2. Start Apache + MySQL.
3. Create/import the database:
   - Import `bookshop.sql` (creates the `bookshop` schema; rename as needed)
   - Then run `database/migration_esewa_payment.sql`
4. Point `bookshop/Database.php` to your MySQL credentials/database.
5. Configure eSewa credentials (see Configuration above).
6. Open `http://localhost/<project-folder>/bookshop/home.php`

Admin panel: `/adminSignin.php` — simple **email + password** login
(default seeded accounts use password `admin123`; change them after first
login via `UPDATE admin SET password='...' WHERE email='...'`, or see
`database/migration_admin_password.sql`). A direct **Admin Login** link is
available on the customer sign-in page.

> **Email features** (admin login codes, registration, forgot password, category
> verification) use the centralized config in `bookshop/config.ini`
> (copy from `bookshop/config.example.ini`):
>
> - `MAIL_ENVIRONMENT = local` → no real emails; every message and its
>   verification code is appended to `bookshop/logs/mail_codes.log`. Perfect for development.
> - `MAIL_ENVIRONMENT = production` → sends through the configured SMTP server
>   (`MAIL_SMTP_USER`, `MAIL_SMTP_PASS`, ...). For Gmail, enable 2-Step
>   Verification and create an App Password at
>   https://myaccount.google.com/apppasswords — never use your normal password,
>   and never commit `config.ini`.
>
> Registration validates Nepali mobile numbers (98/97/96XXXXXXXX).

## 🧪 Verifying payments locally (quick check)

- Normal purchase: Buy Now → pay at eSewa sandbox → redirected back → invoice shows **PAID**, stock reduced, cart item removed.
- Cancel at eSewa → failure page → order stays unpaid, "Pay Now with eSewa" retry available.
- Tampering with the amount or replaying a success URL fails signature/status checks and never marks an order paid.

## 📜 License
MIT License.
