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

## 🔧 Getting started (XAMPP)

See **[setup.md](setup.md)** for the full step-by-step guide. Short version:

1. Place the project folder inside `htdocs`.
2. Start Apache + MySQL.
3. Import the single database file:
   ```bash
   mysql -u root < database/ebookstore_setup.sql
   ```
   Creates the `ebookstore` DB, all tables (eSewa payment columns included),
   the Nepali catalog and one bootstrap admin — no customer/order data.
4. Check `bookshop/Database.php` credentials.
5. Open `http://localhost/<project-folder>/bookshop/home.php`

Admin panel: `/adminSignin.php` — simple **email + password** login.
The setup creates one admin (`nena123maharjan@gmail.com` / `admin123`);
change it after first login. A direct **Admin Login** link is available on
the customer sign-in page.

## 🧪 Verifying payments locally (quick check)

- Normal purchase: Buy Now → pay at eSewa sandbox → redirected back → invoice shows **PAID**, stock reduced, cart item removed.
- Cancel at eSewa → failure page → order stays unpaid, "Pay Now with eSewa" retry available.
- Tampering with the amount or replaying a success URL fails signature/status checks and never marks an order paid.

## 📜 License
MIT License.
