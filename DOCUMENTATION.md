# Unicorn Book Shop — Full Project Documentation

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Project Structure](#3-project-structure)
4. [Architecture & Design Pattern](#4-architecture--design-pattern)
5. [Database Schema](#5-database-schema)
6. [Authentication System](#6-authentication-system)
7. [E-Commerce Features](#7-e-commerce-features)
8. [Payment Integration (eSewa)](#8-payment-integration-esewa)
9. [Admin Panel](#9-admin-panel)
10. [Search System](#10-search-system)
11. [Chat/Messaging System](#11-chatmessaging-system)
12. [Email System](#12-email-system)
13. [Frontend & JavaScript](#13-frontend--javascript)
14. [File Reference](#14-file-reference)
15. [API Endpoints (Process Files)](#15-api-endpoints-process-files)
16. [Configuration](#16-configuration)
17. [Setup & Installation](#17-setup--installation)
18. [Security Considerations](#18-security-considerations)
19. [Testing Guide](#19-testing-guide)
20. [Troubleshooting](#20-troubleshooting)

---

## 1. Project Overview

**Unicorn Book Shop** is a full-featured e-commerce web application for a Nepal-based online bookstore. It allows customers to browse, search, and purchase books online using **eSewa** (Nepal's leading digital wallet) for payment processing. The application includes a complete admin panel for managing products, users, orders, and sales.

**Owner / Developer:** Nena Maharjan — nena123maharjan@gmail.com

### Key Capabilities

- Customer registration with email verification
- Product browsing with category-based navigation
- Basic and advanced search functionality
- Shopping cart and watchlist (wishlist)
- eSewa payment gateway integration with server-side verification
- Purchase history with payment retry for unpaid orders
- Real-time chat between customers and admin
- Admin dashboard with sales analytics
- Full CRUD operations for products, categories, authors, and publishers
- User management with block/unblock functionality
- Order status tracking (Pending → Placed → Delivered)

---

## 2. Technology Stack

| Layer | Technology | Details |
|---|---|---|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) | Vanilla JS with XMLHttpRequest for AJAX |
| **CSS Framework** | Bootstrap 5 | Local copy (`bootstrap.css`, `bootstrap.bundle.js`) |
| **Icons** | Bootstrap Icons v1.9.1, Font Awesome v4.7.0 | CDN-loaded |
| **Animations** | jQuery FadeThis | Scroll-triggered fade-in animations |
| **Backend** | PHP 8.x | Procedural PHP, no framework |
| **Database** | MySQL / MariaDB | Via MySQLi extension |
| **Payment** | eSewa ePay v2 API | HMAC-SHA256 signed requests |
| **Email** | PHPMailer 5.x | SMTP transport, vendored locally |
| **Server** | Apache (XAMPP) | Local development environment |

### External Libraries (Vendored)

| Library | Files | Purpose |
|---|---|---|
| PHPMailer | `PHPMailer.php`, `SMTP.php`, `OAuth.php`, `POP3.php`, `Exception.php` | Email delivery (registration, password reset, category verification) |
| jQuery | `jquery.js` | DOM manipulation, plugin dependency |
| jQuery FadeThis | `jquery.fadethis.js` | Scroll animations on home page |
| Bootstrap 5 | `bootstrap.css`, `bootstrap.bundle.js` | UI framework |

---

## 3. Project Structure

```
ebookstore/
├── README.md                          # Project overview & quick start
├── DOCUMENTATION.md                   # This file — full documentation
├── setup.md                           # Step-by-step setup guide
├── .gitattributes
│
├── database/
│   └── ebookstore_setup.sql           # Complete DB setup (schema + seed data)
│
└── bookshop/                          # Application root
    │
    ├── ── Core / Config ──
    ├── Database.php                   # MySQLi connection wrapper
    ├── mail_config.php                # Email configuration & PHPMailer helper
    ├── config.ini                     # SMTP credentials (git-ignored)
    │
    ├── ── Shared Layout ──
    ├── header.php                     # Common navbar (customer-facing)
    ├── footer.php                     # Common footer (customer-facing)
    │
    ├── ── Customer Pages ──
    ├── home.php                       # Landing page (carousel, categories, products)
    ├── signUp.php                     # Customer registration form
    ├── signIn.php                     # Customer login form
    ├── aboutus.php                    # Static about page
    ├── userProfile.php                # Customer profile view/edit
    ├── singleProductView.php          # Product detail page
    ├── categoryView.php               # Products filtered by category
    ├── advancedSearch.php             # Advanced search form
    ├── cart.php                       # Shopping cart
    ├── watchlist.php                  # Watchlist (wishlist)
    ├── invoice.php                    # Order invoice view
    ├── purchasingHistory.php          # Purchase history listing
    │
    ├── ── Admin Pages ──
    ├── adminSignin.php                # Admin login form
    ├── adminPanel.php                 # Admin dashboard (analytics)
    ├── manageUsers.php                # User management (block/chat)
    ├── manageProducts.php             # Product management (CRUD)
    ├── addProduct.php                 # Add new product form
    ├── updateProduct.php              # Update existing product form
    ├── manageSales.php                # Sales/order management
    │
    ├── ── Backend Processors ──
    ├── process_register.php           # Handle customer registration
    ├── process_signIn.php             # Handle customer login
    ├── signOutProcess.php             # Handle customer logout
    ├── adminSignInProcess.php         # Handle admin login
    ├── adminSignOutProcess.php        # Handle admin logout
    ├── fotgotPasswordProcess.php      # Send password reset code
    ├── resetPasswordProcess.php       # Reset password with code
    ├── updateProfileProcess.php       # Update customer profile
    ├── addToCartProcess.php           # Add item to cart
    ├── cartQtyUpdateProcess.php       # Update cart item quantity
    ├── deleteFromCartProcess.php      # Remove item from cart
    ├── addToWatchlistProcess.php      # Toggle watchlist item
    ├── removeWatchlistProcess.php     # Remove from watchlist
    ├── basicSearchProcess.php         # Basic search AJAX handler
    ├── advancedSearchProcess.php      # Advanced search AJAX handler
    ├── saveFeedbackProcess.php        # Save product feedback
    ├── saveMesaageProcess.php         # Customer sends chat message
    ├── loadChatProcess.php            # Load customer chat history
    ├── sendAdminMsgProcess.php        # Admin sends chat reply
    ├── loadAdminMsgProcess.php        # Load admin chat view
    ├── userBlockProcess.php           # Toggle user block status
    ├── productBlockProcess.php        # Toggle product active status
    ├── adminStatusChangeProcess.php   # Advance order status
    ├── adminSalesViewProcess.php      # Load sales list (AJAX)
    ├── adminProfileUpdate.php         # Update admin profile
    ├── sendIdProcess.php              # Store product ID in session
    ├── addNewCategoryProcess.php      # Send category verification email
    ├── saveCategoryProcess.php        # Save verified new category
    ├── addPublisherProcess.php        # Add new publisher
    ├── addAuthorProcess.php           # Add new author
    │
    ├── ── Payment Subsystem ──
    ├── payment/
    │   ├── esewa_config.php           # eSewa config loader (env/INI)
    │   ├── esewa_lib.php              # eSewa helpers (UUID, HMAC, verify)
    │   ├── initiatePaymentProcess.php # Create PENDING order
    │   ├── esewa_redirect.php         # Signed form auto-submit to eSewa
    │   ├── payment_success.php        # eSewa callback (7-step verify)
    │   ├── payment_failure.php        # Payment failure handler
    │   ├── retryPaymentProcess.php    # Retry unpaid order
    │   ├── config.example.ini         # eSewa credentials template
    │   └── logs/                      # Payment JSON logs
    │
    ├── ── Third-Party Libraries ──
    ├── PHPMailer.php                  # PHPMailer main class
    ├── SMTP.php                       # SMTP transport
    ├── OAuth.php                      # OAuth2 auth
    ├── POP3.php                       # POP3 auth
    ├── Exception.php                  # Custom exception
    │
    ├── ── Frontend Assets ──
    ├── script.js                      # ALL JavaScript (AJAX functions)
    ├── style.css                      # Custom styles
    ├── bootstrap.css                  # Bootstrap CSS
    ├── bootstrap.bundle.js            # Bootstrap JS
    ├── jquery.js                      # jQuery
    ├── jquery.fadethis.js             # FadeThis plugin
    │
    ├── ── Static Resources ──
    ├── resource/
    │   ├── slider_images/             # Home page carousel images
    │   ├── product_img/               # Uploaded book cover images
    │   ├── profile_images/            # User profile pictures
    │   ├── new_user.svg               # Default user avatar
    │   └── join.jpeg                  # Sign-up page image
    │
    ├── ── Fonts ──
    ├── font/
    │   ├── HoneyScript-Light.ttf
    │   └── Quicksand-Medium.ttf
    │
    ├── ── Logs ──
    ├── logs/                          # Application logs (mail codes)
    │
    ├── ── Misc ──
    └── .gitignore
```

---

## 4. Architecture & Design Pattern

### Pattern: Procedural PHP with AJAX

This is a **procedural PHP web application** without any MVC framework, ORM, or routing layer.

**Key Characteristics:**

- **No router:** Each PHP file is directly accessible via URL. Files are named by convention.
- **No MVC separation:** Business logic, database queries, and HTML output coexist in the same files.
- **Single database abstraction:** `Database.php` wraps MySQLi with two static methods:
  - `Connection::select($query)` — returns result sets
  - `Connection::iud($query)` — executes insert/update/delete
- **AJAX-heavy frontend:** All user interactions (login, search, cart, watchlist, chat, admin CRUD) use vanilla JavaScript `XMLHttpRequest` calls to PHP backend files.
- **No templating engine:** PHP is embedded directly in HTML using `<?php ... ?>` blocks.
- **Session-based auth:** Two separate session keys — `$_SESSION["i"]` for customers, `$_SESSION["au"]` for admins.
- **Flat file structure:** All application files reside in a single `bookshop/` directory.

### Request Flow

```
Browser (HTML/CSS/JS)
    │
    ├──► Static files (Bootstrap, jQuery, images)
    │
    ├──► Page PHP files (home.php, cart.php, etc.)
    │       └── Include header.php + footer.php
    │       └── Query database via Database.php
    │       └── Render HTML with embedded PHP
    │
    └──► AJAX requests (XMLHttpRequest)
            └── Backend process files (*Process.php, process_*.php)
            └── Execute business logic
            └── Return plain text or HTML fragments
```

---

## 5. Database Schema

The database is named `ebookstore` and contains **23 tables**. The complete schema and seed data are in `database/ebookstore_setup.sql`.

### Entity Relationship Overview

```
province ──< district ──< city ──< user_has_address
                                     │
gender ──< user ────────────────────┘
    │
    ├──< cart ──> product ──< product_img
    ├──< watchlist ──> product
    ├──< recent ──> product
    ├──< feedback ──> product
    ├──< invoice ──> product
    ├──< invoice ──> order_status
    └──< chat ──> admin

category ──< product
author ──< author_has_publisher ──> publisher
                                     │
                                     └──< product

admin ──< product
admin ──< chat
```

### Table Reference

| Table | Purpose | Key Columns |
|---|---|---|
| `admin` | Admin accounts | `email` (PK), `fname`, `lname`, `password`, `vcode` |
| `user` | Customer accounts | `email` (PK), `fname`, `lname`, `password`, `mobile`, `gender_gender_id`, `status_status_id` |
| `gender` | Gender lookup | `gender_id` (PK), `gender_name` |
| `status` | Active/Inactive status | `status_id` (PK), `status` |
| `province` | Nepal provinces | `province_id` (PK), `province_name` |
| `district` | Nepal districts | `district_id` (PK), `district_name`, `province_province_id` (FK) |
| `city` | Nepal cities | `city_id` (PK), `city_name`, `district_district_id` (FK) |
| `user_has_address` | User addresses | `address_id` (PK), `user_email` (FK), `city_city_id` (FK), `line1`, `line2`, `postal_code` |
| `profile_img` | User profile images | `path` (PK), `user_email` (FK) |
| `category` | Book categories | `cat_id` (PK), `cat_name` |
| `author` | Book authors | `author_id` (PK), `author_name` |
| `publisher` | Publishers | `publisher_id` (PK), `publisher_name` |
| `author_has_publisher` | Author-publisher junction | `id` (PK), `author_author_id` (FK), `publisher_publisher_id` (FK) |
| `category_has_publisher` | Category-publisher junction | `category_cat_id` (FK), `publisher_publisher_id` (FK) |
| `product` | Books/products | `id` (PK), `title`, `price`, `qty`, `description`, `category_cat_id` (FK), `author_has_publisher_id` (FK), `status_status_id` (FK), `admin_email` (FK) |
| `product_img` | Product images | `img_path` (PK), `product_id` (FK) |
| `cart` | Shopping cart items | `cart_id` (PK), `user_email` (FK), `product_id` (FK), `qty` |
| `watchlist` | User wishlists | `w_id` (PK), `user_email` (FK), `product_id` (FK) |
| `recent` | Recently viewed/bookmarked | `r_id` (PK), `product_id` (FK), `user_email` (FK) |
| `feedback` | Product reviews | `feed_id` (PK), `product_id` (FK), `user_email` (FK), `type`, `feed` |
| `order_status` | Order status lookup | `status_id` (PK), `status` (Waiting for accept / Order Placed / Delivered) |
| `invoice` | Orders/invoices | `invoice_id` (PK), `order_id`, `transaction_uuid` (UNIQUE), `total`, `payment_method`, `payment_status` (PENDING/PAID/FAILED), `transaction_code`, `paid_at`, `qty`, `product_id` (FK), `user_email` (FK), `order_status_status_id` (FK) |
| `chat` | Customer-admin messages | `chat_id` (PK), `content`, `date_time`, `status` (1-4), `from` (FK→user), `to` (FK→admin) |

### Seed Data

The setup SQL includes:
- **12 books** (Nepali literature, educational)
- **7 authors** (Laxmi Prasad Devkota, B.P. Koirala, Parijat, etc.)
- **6 publishers** (Sajha Prakashan, Ratna Pustak Bhandar, etc.)
- **6 categories** (Novels, Short Stories, Educational, Language, Religion, Translations)
- **5 provinces**, **5 districts**, **8 cities** (Nepal geography)
- **1 admin account** (nena123maharjan@gmail.com / admin123)
- No customer accounts, orders, carts, or messages (fresh store)

---

## 6. Authentication System

### Customer Authentication

| Operation | File | Method |
|---|---|---|
| Registration form | `signUp.php` | HTML form |
| Registration handler | `process_register.php` | AJAX POST |
| Login form | `signIn.php` | HTML form |
| Login handler | `process_signIn.php` | AJAX POST |
| Logout handler | `signOutProcess.php` | AJAX GET |
| Forgot password | `fotgotPasswordProcess.php` | AJAX GET |
| Reset password | `resetPasswordProcess.php` | AJAX POST |

**Registration Flow:**
1. User fills form (first name, last name, email, mobile, password, gender)
2. AJAX POST to `process_register.php`
3. Server validates all fields
4. User inserted into `user` table with verification code
5. Welcome email sent via PHPMailer
6. Redirect to sign-in page

**Login Flow:**
1. User enters email + password (optionally checks "Remember Me")
2. AJAX POST to `process_signIn.php`
3. Server queries `user` table with matching credentials
4. On success: full user row stored in `$_SESSION["i"]`, first name stored in `$_COOKIE["name"]`
5. Redirect to home page
6. On failure: error message displayed

**Remember Me:** If checked, email and password are stored as browser cookies for 1 year.

**Session Guard:** Every customer-facing page checks `isset($_SESSION["i"])`. If not set, the user is redirected or shown an error.

### Admin Authentication

| Operation | File | Method |
|---|---|---|
| Login form | `adminSignin.php` | HTML form |
| Login handler | `adminSignInProcess.php` | AJAX POST |
| Logout handler | `adminSignOutProcess.php` | AJAX GET |

**Admin Login Flow:**
1. Admin enters email + password on `adminSignin.php`
2. AJAX POST to `adminSignInProcess.php`
3. Server queries `admin` table with matching credentials
4. On success: admin row stored in `$_SESSION["au"]`
5. Redirect to admin dashboard (`adminPanel.php`)

**Admin Session Guard:** Every admin page checks `isset($_SESSION["au"])`. If not set, redirect to `adminSignin.php`.

### Password Reset Flow
1. User clicks "Forgot Password" on sign-in page
2. AJAX GET to `fotgotPasswordProcess.php` with email
3. Server generates `uniqid()` verification code, stores in `user.verification_code`
4. Verification code sent to user's email
5. User enters code and new password
6. AJAX POST to `resetPasswordProcess.php`
7. Server verifies code and updates password

---

## 7. E-Commerce Features

### Product Catalog

- **12 pre-loaded books** across 6 categories
- Each product has: title, price, quantity (stock), description, category, author, publisher, images (up to 3)
- Products have an Active/Inactive status for admin-controlled visibility

### Product Browsing

- **Home page** (`home.php`): Image carousel, category grid, product cards grouped by category
- **Category view** (`categoryView.php`): Filtered product listing for a specific category
- **Single product view** (`singleProductView.php`): Full product detail with:
  - Image gallery (clickable thumbnails)
  - Price with display discount
  - Quantity selector
  - "Buy Now" and "Add to Cart" buttons
  - "Add to Watchlist" heart icon
  - Seller information
  - Related products
  - Customer feedback/reviews

### Shopping Cart

| Operation | File | Method |
|---|---|---|
| Add to cart | `addToCartProcess.php` | AJAX GET |
| Update quantity | `cartQtyUpdateProcess.php` | AJAX GET |
| Remove item | `deleteFromCartProcess.php` | AJAX GET |
| View cart | `cart.php` | Page |

**Cart Flow:**
1. User clicks "Add to Cart" on product page → AJAX call adds item to `cart` table
2. If item already exists, quantity is incremented
3. Cart page shows all items with images, quantity editors, delivery fees
4. Summary panel shows subtotal, delivery, and grand total
5. User can modify quantities or remove items

### Watchlist (Wishlist)

| Operation | File | Method |
|---|---|---|
| Toggle watchlist | `addToWatchlistProcess.php` | AJAX GET |
| Remove from watchlist | `removeWatchlistProcess.php` | AJAX GET |
| View watchlist | `watchlist.php` | Page |

**Watchlist Flow:**
1. Click heart icon on product → toggles watchlist status
2. Heart icon changes color to indicate status
3. Watchlist page shows saved products with Buy/Add-to-Cart/Remove actions
4. Removing from watchlist adds product to `recent` table

### Purchase Flow

1. User clicks "Buy Now" on single product view
2. AJAX POST to `payment/initiatePaymentProcess.php` with product ID and quantity
3. Server computes total (price × qty + delivery fee + tax + service charge)
4. PENDING invoice row created with unique `transaction_uuid`
5. Signed HTML form auto-submits to eSewa payment gateway
6. User completes payment on eSewa
7. eSewa redirects back to success/failure URL
8. Server verifies payment (7-step verification chain)
9. On success: order marked PAID, stock decremented, cart item removed
10. Invoice page displayed

### Purchase History

- Lists all past orders with status badges
- Shows payment status (PENDING / PAID / FAILED)
- "Pay Now with eSewa" retry link for unpaid orders
- Feedback modal for purchased products

---

## 8. Payment Integration (eSewa)

### Overview

Payments are processed through **eSewa** (Nepal's digital wallet) using the official **ePay v2 API**. The implementation includes HMAC-SHA256 request signing, server-to-server verification, and structured payment logging.

### Payment Files

| File | Purpose |
|---|---|
| `payment/esewa_config.php` | Loads configuration from env vars or `config.ini` |
| `payment/esewa_lib.php` | Helper library (UUID, HMAC-SHA256, signature verification, logging) |
| `payment/initiatePaymentProcess.php` | Creates PENDING order, returns redirect URL |
| `payment/esewa_redirect.php` | Builds and auto-submits signed form to eSewa |
| `payment/payment_success.php` | Handles eSewa callback with 7-step verification |
| `payment/payment_failure.php` | Handles payment failure/cancellation |
| `payment/retryPaymentProcess.php` | Retries unpaid orders with new transaction UUID |
| `payment/config.example.ini` | Template for credentials |
| `payment/logs/payments.log` | Structured JSON payment logs |

### Payment Flow (Detailed)

```
1. Customer clicks "Buy Now"
        ↓
2. AJAX POST to initiatePaymentProcess.php
   - Validates product exists and has stock
   - Computes total server-side (never trusts browser):
     total = (price × qty) + delivery_fee + VAT(13%) + service_charge
   - Creates invoice row with payment_status = 'PENDING'
   - Generates unique transaction_uuid (UUID v4)
        ↓
3. Redirect to esewa_redirect.php
   - Builds HTML form with signed fields:
     - total_amount, transaction_uuid, product_code (signed with HMAC-SHA256)
   - Auto-submits form to eSewa gateway
        ↓
4. Customer pays at eSewa
   - Sandbox: https://rc-epay.esewa.com.np/api/epay/main/v2/form
   - Production: https://epay.esewa.com.np/api/epay/main/v2/form
        ↓
5. eSewa redirects back to:
   - Success: payment_success.php
   - Failure: payment_failure.php
        ↓
6. payment_success.php — 7-Step Verification:
   Step 1: Check user session
   Step 2: Verify response signature (HMAC-SHA256, timing-safe comparison)
   Step 3: Look up order by transaction_uuid
   Step 4: Verify order belongs to current user
   Step 5: Verify product_code matches
   Step 6: Verify amount matches server-computed total
   Step 7: Server-to-server Status API call to eSewa (final confirmation)
        ↓
7. Only if ALL 7 steps pass:
   - Order marked as PAID (atomic status transition)
   - Product stock decremented
   - Cart item removed (if exists)
   - Redirect to invoice page
```

### Security Features

- Amount is **always computed server-side** (never trusted from browser)
- HMAC-SHA256 request signing and response verification
- **Timing-safe signature comparison** (`hash_equals`)
- Server-to-server **Status API verification** as final confirmation
- **Atomic PENDING → PAID update** to prevent duplicate processing
- Replay and duplicate callback guards
- Unique `transaction_uuid` per payment attempt
- Structured payment logging (never logs secrets)

### Configuration Priority

Credentials are loaded in this order:
1. Environment variables (`ESEWA_MERCHANT_CODE`, `ESEWA_SECRET_KEY`, etc.)
2. `bookshop/payment/config.ini` (git-ignored)
3. Defaults: eSewa's official sandbox test values (`EPAYTEST`)

### Sandbox Testing

| Setting | Value |
|---|---|
| Merchant code | `EPAYTEST` |
| Secret key | `8gBm/:&EnhH.1/q` |
| Form URL | `https://rc-epay.esewa.com.np/api/epay/main/v2/form` |
| Test customer eSewa ID | `9806800001` – `9806800005` |
| Test password | `Nepal@123` |
| Test MPIN | `1122` |
| Test token | `123456` |

### eSewa API Endpoints

| Purpose | Sandbox | Production |
|---|---|---|
| Payment form | `https://rc-epay.esewa.com.np/api/epay/main/v2/form` | `https://epay.esewa.com.np/api/epay/main/v2/form` |
| Transaction status | `https://rc-epay.esewa.com.np/api/epay/transaction/status/` | `https://epay.esewa.com.np/api/epay/transaction/status/` |

---

## 9. Admin Panel

### Access

Admin login: `/adminSignin.php` — email + password authentication.
Default credentials: `nena123maharjan@gmail.com` / `admin123`

### Dashboard (`adminPanel.php`)

Displays 6 metric cards:
- **Daily Earnings** — Total revenue for today
- **Monthly Earnings** — Total revenue for current month
- **Today's Sellings** — Number of orders today
- **Monthly Sellings** — Number of orders this month
- **Total Sellings** — All-time order count
- **Total Engagements** — Total registered users

Also shows the **"Mostly Sold Book"** of the day with image, quantity, and total amount.

### Manage Users (`manageUsers.php`)

- Paginated list (20 per page) of all registered users
- Expandable rows showing: profile image, name, email, mobile, registration date
- **Actions:**
  - Block/Unblock user (toggles `status_status_id` between 1 and 2)
  - Message user (opens in-page chat modal)

### Manage Products (`manageProducts.php`)

- Paginated list (10 per page) of all products
- Expandable rows with product image and actions
- **Actions:**
  - View product details (modal)
  - Update product (redirects to `updateProduct.php`)
  - Block/Unblock product (toggles Active/Inactive status)
- **Add New Book** button → `addProduct.php` form
- **Category Management** section:
  - Lists all categories
  - "Add new Category" requires email verification before saving
  - Inline Publisher and Author addition

### Manage Sales (`manageSales.php`)

- Filterable by order status: All, Pending, Placed, Delivered
- Sales list loaded via AJAX
- Expandable rows showing:
  - Product details: ID, name, price, quantity, ordered qty, amount
  - Customer details: email, name, mobile, full address
- **"Change Status"** button advances order:
  - Pending (1) → Placed (2) → Delivered (3)

### Admin Profile

- Editable first/last name via modal on any admin page
- Updated via `adminProfileUpdate.php`

---

## 10. Search System

### Basic Search

| Component | File |
|---|---|
| Search bar | `header.php` (navbar) |
| Category filter | `header.php` (dropdown) |
| Search handler | `basicSearchProcess.php` |
| Category view | `categoryView.php` |

**How it works:**
1. User enters keyword and/or selects category in the navbar
2. AJAX POST to `basicSearchProcess.php` with search text, category, and page number
3. Server queries products with `LIKE` matching on title and description
4. Returns paginated HTML product cards
5. Results replace `#basicSearchResult` div on the page

### Advanced Search

| Component | File |
|---|---|
| Search form | `advancedSearch.php` |
| Search handler | `advancedSearchProcess.php` |

**Filters available:**
- Keyword (title/description)
- Category
- Publisher
- Author

**How it works:**
1. User fills in one or more filters on `advancedSearch.php`
2. AJAX POST to `advancedSearchProcess.php` with all filters and page number
3. Server builds dynamic SQL query based on provided filters
4. Returns paginated HTML product cards
5. Results replace `#view_area` div

---

## 11. Chat/Messaging System

A real-time-like chat system between customers and the admin, using AJAX polling.

### Chat Status Codes

| Status | Meaning |
|---|---|
| 1 | Customer sent (unread by admin) |
| 2 | Customer sent (read by admin) |
| 3 | Admin replied (unread by customer) |
| 4 | Admin replied (read by customer) |

### Customer Side

| Operation | File |
|---|---|
| Send message | `saveMesaageProcess.php` |
| Load messages | `loadChatProcess.php` |

1. Customer clicks chat icon in header → opens modal
2. `loadChatProcess.php` loads all messages between customer and admin
3. Marks status-3 messages as status-4 (read)
4. Customer types and sends message
5. `saveMesaageProcess.php` saves with status 1

### Admin Side

| Operation | File |
|---|---|
| Send reply | `sendAdminMsgProcess.php` |
| Load messages | `loadAdminMsgProcess.php` |

1. Admin clicks "Message" on a user in `manageUsers.php`
2. Opens per-user chat modal
3. `loadAdminMsgProcess.php` loads conversation, marks status-1 as status-2
4. Admin types and sends reply
5. `sendAdminMsgProcess.php` saves with status 3

---

## 12. Email System

### Configuration

Email settings are centralized in `mail_config.php`. Two modes:

| Mode | Behavior |
|---|---|
| `local` (default) | No emails sent; codes written to `bookshop/logs/mail_codes.log` |
| `production` | Emails sent via SMTP (PHPMailer) |

### Email Use Cases

| Use Case | Trigger | Content |
|---|---|---|
| Welcome email | Customer registration | Account confirmation |
| Password reset | Forgot password | Verification code |
| Category verification | Admin adds new category | Verification code |

### Production Configuration

Copy `bookshop/config.example.ini` to `bookshop/config.ini`:

```ini
MAIL_ENVIRONMENT = production
MAIL_SMTP_USER   = yourgmail@gmail.com
MAIL_SMTP_PASS   = sixteen-char-gmail-app-password
MAIL_FROM_EMAIL  = yourgmail@gmail.com
```

---

## 13. Frontend & JavaScript

### Single JavaScript File

All frontend interactivity is in `script.js` (~1048 lines) using **vanilla JavaScript with XMLHttpRequest** (no jQuery AJAX).

### Key JavaScript Functions

| Function | Purpose |
|---|---|
| `register()` | Submit registration form via AJAX |
| `signIn()` | Submit login form via AJAX |
| `signout()` | Logout via AJAX GET |
| `forgotPassword()` | Request password reset code |
| `resetPassword()` | Submit new password with verification code |
| `basicSearch(page)` | Basic search with pagination |
| `advancedSearch(page)` | Advanced search with filters and pagination |
| `searchCatogory()` | Redirect to category view |
| `addToCart(id)` | Add product to cart |
| `changeQTY(id, allqty)` | Update cart item quantity |
| `deleteFromCart(id)` | Remove item from cart |
| `addToWatchlist(id)` | Toggle watchlist item |
| `removeFromWatchlist(id)` | Remove from watchlist |
| `buyNow(id)` | Initiate eSewa payment |
| `loadMainImg(id)` | Swap main product image |
| `qty_inc(qty)` / `qty_dec()` | Client-side quantity controls |
| `addProduct()` | Submit add product form (with images) |
| `updateProduct()` | Submit update product form |
| `sendid(id)` | Store product ID and redirect to update page |
| `blockUser(email)` | Toggle user block status |
| `blockProduct(id)` | Toggle product active status |
| `orderStatusChange(status, invoice_id)` | Advance order status |
| `loadSales()` | Load filtered sales list via AJAX |
| `addFeedback(id)` / `saveFeedback(id)` | Submit product feedback |
| `updateProfile()` | Submit profile update |
| `saveMessage()` / `loadChat()` | Customer chat functions |
| `sendAdminMsg(email)` / `loadAdminChat(email)` | Admin chat functions |
| `printInvoice()` | Print-friendly invoice view |
| `viewMsgModal(email)` | Open admin chat modal for specific user |
| `addNewCategory()` / `verifyCategory()` / `saveCategory()` | Three-step category creation with email verification |
| `addPublisher()` / `addAuthor()` | Add new publisher/author |
| `adminSignIn()` | Submit admin login form |

### UI Components

| Component | Library |
|---|---|
| Navbar | Bootstrap 5 (custom) |
| Image carousel | Bootstrap Carousel |
| Modals (chat, feedback, category, admin login) | Bootstrap Modal |
| Dropdowns (categories, user menu) | Bootstrap Dropdown |
| Collapse (product details, user details) | Bootstrap Collapse |
| Pagination | Bootstrap Pagination |
| Scroll animations | jQuery FadeThis |
| Cart icon badge | Custom (Bootstrap Icons) |

---

## 14. File Reference

### Core Files

| File | Lines | Purpose |
|---|---|---|
| `Database.php` | ~30 | MySQLi connection singleton with `select()` and `iud()` |
| `mail_config.php` | ~80 | Email configuration and PHPMailer delivery |
| `script.js` | ~1048 | All frontend JavaScript logic |

### Page Files

| File | Purpose |
|---|---|
| `home.php` | Landing page with carousel, categories, product listings |
| `header.php` | Shared navbar with search, category dropdown, user menu, cart, chat |
| `footer.php` | Shared footer with social links, contact info |
| `signUp.php` | Customer registration form |
| `signIn.php` | Customer login with remember-me and forgot-password |
| `aboutus.php` | Static about page |
| `userProfile.php` | Customer profile view/edit |
| `singleProductView.php` | Product detail page with gallery, buy/cart/watchlist actions |
| `categoryView.php` | Category-filtered product listing |
| `advancedSearch.php` | Advanced search form |
| `cart.php` | Shopping cart with quantity editors and summary |
| `watchlist.php` | Watchlist with buy/cart/remove actions |
| `invoice.php` | Order invoice view with print support |
| `purchasingHistory.php` | Past orders with status and retry |
| `adminSignin.php` | Admin login form |
| `adminPanel.php` | Admin dashboard with analytics |
| `manageUsers.php` | User management with block/chat |
| `manageProducts.php` | Product management with CRUD |
| `addProduct.php` | Add new product form |
| `updateProduct.php` | Update product form |
| `manageSales.php` | Sales/order management |

### Process Files (AJAX Handlers)

| File | Purpose |
|---|---|
| `process_register.php` | Handle customer registration |
| `process_signIn.php` | Handle customer login |
| `signOutProcess.php` | Handle customer logout |
| `adminSignInProcess.php` | Handle admin login |
| `adminSignOutProcess.php` | Handle admin logout |
| `fotgotPasswordProcess.php` | Send password reset code |
| `resetPasswordProcess.php` | Reset password with code |
| `updateProfileProcess.php` | Update customer profile |
| `addToCartProcess.php` | Add item to cart |
| `cartQtyUpdateProcess.php` | Update cart quantity |
| `deleteFromCartProcess.php` | Remove from cart |
| `addToWatchlistProcess.php` | Toggle watchlist item |
| `removeWatchlistProcess.php` | Remove from watchlist |
| `basicSearchProcess.php` | Basic search AJAX handler |
| `advancedSearchProcess.php` | Advanced search AJAX handler |
| `saveFeedbackProcess.php` | Save product feedback |
| `saveMesaageProcess.php` | Customer sends chat message |
| `loadChatProcess.php` | Load customer chat |
| `sendAdminMsgProcess.php` | Admin sends reply |
| `loadAdminMsgProcess.php` | Load admin chat |
| `userBlockProcess.php` | Toggle user block |
| `productBlockProcess.php` | Toggle product active |
| `adminStatusChangeProcess.php` | Advance order status |
| `adminSalesViewProcess.php` | Load sales list |
| `adminProfileUpdate.php` | Update admin profile |
| `sendIdProcess.php` | Store product ID in session |
| `addNewCategoryProcess.php` | Send category verification email |
| `saveCategoryProcess.php` | Save verified category |
| `addPublisherProcess.php` | Add new publisher |
| `addAuthorProcess.php` | Add new author |

---

## 15. API Endpoints (Process Files)

All backend process files accept POST or GET requests and return plain text or HTML.

### Authentication Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `process_register.php` | POST | fname, lname, email, mobile, password, gender | Success/error message |
| `process_signIn.php` | POST | email, password | "student" (success) / error |
| `signOutProcess.php` | GET | — | Success/error |
| `adminSignInProcess.php` | POST | email, password | "admin" (success) / error |
| `adminSignOutProcess.php` | GET | — | Redirect |
| `fotgotPasswordProcess.php` | GET | email | "ok" / error |
| `resetPasswordProcess.php` | POST | email, code, new_password | Success/error |

### Product Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `basicSearchProcess.php` | POST | text, category, page | HTML product cards |
| `advancedSearchProcess.php` | POST | keyword, category, publisher, author, page | HTML product cards |
| `addProductProcess.php` | POST | title, price, qty, description, category, author, publisher, images[], delivery_fee_colombo, delivery_fee_other | Success/error |
| `updateProductProcess.php` | POST | id, title, qty, description, images[], delivery_fee_colombo, delivery_fee_other | Success/error |
| `productBlockProcess.php` | GET | id | Success/error |
| `sendIdProcess.php` | GET | id | Redirect |

### Cart Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `addToCartProcess.php` | GET | id | Success/error |
| `cartQtyUpdateProcess.php` | GET | id, qty | Success/error |
| `deleteFromCartProcess.php` | GET | id | Success/error |

### Watchlist Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `addToWatchlistProcess.php` | GET | id | Success/error |
| `removeWatchlistProcess.php` | GET | id | Success/error |

### Payment Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `payment/initiatePaymentProcess.php` | POST | product_id, qty | JSON {redirect_url} |
| `payment/retryPaymentProcess.php` | POST | invoice_id | Redirect |
| `payment/payment_success.php` | GET | refId, amt, pid, scd, txnuuid, signature | Redirect |
| `payment/payment_failure.php` | GET | — | Failure page |

### Admin Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `adminStatusChangeProcess.php` | POST | invoice_id, status | Success/error |
| `adminSalesViewProcess.php` | POST | status, page | HTML sales list |
| `adminProfileUpdate.php` | POST | fname, lname | Success/error |
| `userBlockProcess.php` | GET | email | Success/error |
| `addNewCategoryProcess.php` | POST | cat_name | "ok" / error |
| `saveCategoryProcess.php` | POST | cat_name, code | Success/error |
| `addPublisherProcess.php` | POST | publisher_name | Success/error |
| `addAuthorProcess.php` | POST | author_name | Success/error |

### Chat Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `saveMesaageProcess.php` | POST | to, msg | Success/error |
| `loadChatProcess.php` | POST | email | HTML messages |
| `sendAdminMsgProcess.php` | POST | to, msg | Success/error |
| `loadAdminMsgProcess.php` | POST | email | HTML messages |

### Profile Endpoints

| Endpoint | Method | Parameters | Returns |
|---|---|---|---|
| `updateProfileProcess.php` | POST | fname, lname, mobile, password, address, profile_img | Success/error |
| `saveFeedbackProcess.php` | POST | product_id, type, feed | Success/error |

---

## 16. Configuration

### Database Configuration (`Database.php`)

```php
private static $server   = "localhost";
private static $user     = "root";
private static $password = "";            // default XAMPP = empty
private static $db       = "ebookstore";
```

### Email Configuration (`config.ini`)

```ini
MAIL_ENVIRONMENT = local           # or "production"
MAIL_SMTP_HOST   = smtp.gmail.com
MAIL_SMTP_PORT   = 587
MAIL_SMTP_USER   = yourgmail@gmail.com
MAIL_SMTP_PASS   = your-app-password
MAIL_FROM_EMAIL  = yourgmail@gmail.com
MAIL_FROM_NAME   = Unicorn Book Shop
```

### eSewa Payment Configuration (`payment/config.ini`)

```ini
ESEWA_MERCHANT_CODE = EPAYTEST
ESEWA_SECRET_KEY    = 8gBm/:&EnhH.1/q
ESEWA_ENVIRONMENT   = sandbox          # or "production"
ESEWA_APP_BASE_URL  = http://localhost/ebookstore/bookshop
```

**Configuration priority:** Environment variables → `config.ini` → hardcoded defaults.

---

## 17. Setup & Installation

### Prerequisites

- XAMPP with PHP 8.x, Apache, and MariaDB
- A web browser

### Quick Setup

1. **Clone the project** into XAMPP's `htdocs`:
   ```bash
   cd /opt/lampp/htdocs
   git clone <repo-url> ebookstore
   ```

2. **Start Apache + MySQL** via XAMPP control panel.

3. **Import the database:**
   ```bash
   /opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/ebookstore/database/ebookstore_setup.sql
   ```
   Or use phpMyAdmin → Import tab.

4. **Verify database credentials** in `bookshop/Database.php`.

5. **Open the site:**
   | Page | URL |
   |---|---|
   | Home | `http://localhost/ebookstore/bookshop/home.php` |
   | Customer login | `http://localhost/ebookstore/bookshop/signIn.php` |
   | Admin login | `http://localhost/ebookstore/bookshop/adminSignin.php` |

6. **Admin credentials:** `nena123maharjan@gmail.com` / `admin123`

### Log Directory Permissions (Linux)

```bash
sudo chown -R daemon:daemon /opt/lampp/htdocs/ebookstore/bookshop/payment/logs /opt/lampp/htdocs/ebookstore/bookshop/logs
```

---

## 18. Security Considerations

### Known Security Issues (Educational Note)

| Issue | Location | Risk |
|---|---|---|
| **Plaintext passwords** | `user` table, `admin` table | Passwords stored without hashing |
| **SQL string concatenation** | `Database.php`, all process files | Vulnerable to SQL injection |
| **No CSRF tokens** | All forms and AJAX calls | Cross-site request forgery possible |
| **Plaintext password in cookies** | `process_signIn.php` (Remember Me) | Password exposed in browser storage |
| **No input sanitization** | Most process files | XSS and injection risks |
| **No HTTPS enforcement** | Application-wide | Data transmitted in plaintext |

### Payment Security (Well-Implemented)

The payment subsystem has strong security:
- Server-side amount computation (never trusted from browser)
- HMAC-SHA256 request signing and response verification
- Timing-safe signature comparison (`hash_equals`)
- Server-to-server Status API verification
- Atomic order status transitions
- Replay and duplicate callback guards

---

## 19. Testing Guide

### Quick Smoke Test

1. Open `home.php` — books should be listed
2. Sign up a customer (any Nepali mobile like `98XXXXXXXX`)
3. Open a book → "Buy Now" → pay at eSewa sandbox → invoice shows **PAID**
4. Cancel at eSewa → order stays unpaid → retry available
5. Log in as admin → manage products/orders at `adminPanel.php`

### eSewa Sandbox Test Accounts

| eSewa ID | Password | MPIN | Token |
|---|---|---|---|
| 9806800001 | Nepal@123 | 1122 | 123456 |
| 9806800002 | Nepal@123 | 1122 | 123456 |
| 9806800003 | Nepal@123 | 1122 | 123456 |
| 9806800004 | Nepal@123 | 1122 | 123456 |
| 9806800005 | Nepal@123 | 1122 | 123456 |

### Payment Test Scenarios

| Scenario | Expected Result |
|---|---|
| Normal purchase → pay at eSewa | Invoice shows PAID, stock reduced, cart cleared |
| Cancel at eSewa | Order stays unpaid, retry link available |
| Tamper with amount | Signature/status check fails, order not marked paid |
| Replay success URL | Duplicate callback guard prevents double processing |

---

## 20. Troubleshooting

| Problem | Solution |
|---|---|
| Blank page / data missing | Check `Database.php` credentials + MySQL is running |
| `Unknown database 'ebookstore'` | Re-run the database import |
| PHP errors about permissions (Linux) | Fix log folder permissions (see Section 17) |
| Payment redirects fail on phone | Set `ESEWA_APP_BASE_URL` to tunnel URL (ngrok) |
| Admin can't log in | Verify `password` column is populated in `admin` table |
| Emails not sending | Check `config.ini` settings; default is local mode (logs only) |
| Chat messages not loading | Verify session is active; check AJAX calls in browser console |
| Images not displaying | Ensure `resource/` directory permissions are correct |

---

## License

MIT License.
