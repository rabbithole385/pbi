# PBI Group — online banking platform

A complete, self-contained internet banking platform in plain PHP. No framework, no Composer,
no build step. Upload it, configure database or use the pre-built SQLite, and you have a working bank.

Currency defaults to **US Dollar (USD, $)** and can be changed at install time or later
under **Settings → Identity**.

---

## Installing

### On cPanel / shared hosting (Truehost and similar)

1. Upload the whole folder to `public_html` (or a subfolder — the app is path-aware and
   works fine at `example.com/bank/`).
2. Set folder permissions to `755`. The app needs to write `config.php`, `storage/` and `uploads/`.
3. Visit `https://yourdomain.com/install.php`.
4. Pick a database:
   - **SQLite** — nothing to set up. Best for demos and small deployments.
   - **MySQL** — create an empty database and user in cPanel first, then enter the credentials.
5. Name the bank, set the currency, create the administrator.
6. Leave *demo customers* ticked for a walkthrough; untick it for a clean start.
7. **Delete `install.php` from the server.** The installer warns you about this too.

### Requirements

PHP 7.4 or newer with PDO. That is genuinely all. `pdo_sqlite` for the SQLite route,
`pdo_mysql` for MySQL, `openssl` if you want SMTP over TLS.

---

## Signing in

| Door | URL |
|---|---|
| Public site | `/index.php` |
| Customers | `/login.php` |
| Staff | `/admin/login.php` |

If you loaded the demo data, three customers exist with password `password123`
and transfer PIN `1234`:

- `claire@example.com` — well funded, plenty of history
- `daniel@example.com` — funded, active
- `ruth@example.com` — low balance, useful for testing limits and refusals

The demo data also leaves one deposit and one loan application waiting in the
approvals queue so the admin side is not empty on first look.

---

## What the customer can do

- **Overview** — engraved balance panel, money in/out, held funds, recent activity
- **Send money** — internal (instant), domestic, and international wire with SWIFT/IBAN
- **Add funds** — log a deposit against bank/wire/cash/cheque/crypto, attach proof
- **Withdraw** — request a payout to a bank account or wallet
- **Transactions** — search, filter by direction/status/date, per-page totals
- **Statements** — any date range, opening/closing balance, running column, print or PDF
- **Loans** — live quote calculator, application, repayment from balance
- **Cards** — request virtual or physical Visa/Mastercard, freeze, reveal details, spend limit
- **Beneficiaries** — saved payees that fill the transfer form
- **Inbox** — every notice the bank sends
- **Profile** — personal details and KYC document upload
- **Security** — password, four-digit transfer PIN, two-step sign-in, sign-in history

## What the administrator can do

- **Dashboard** — funds held, money in/out today, loan book, fees, 14-day flow chart, queue
- **Customers** — search and filter, open new accounts with an optional opening balance
- **Customer control panel** — this is the heart of it:
  - Credit, debit, or **set the balance to an exact figure**
  - **Backdate** any entry so the history reads correctly
  - Choose the description, category and channel that appear on the statement
  - Optionally email the customer about the entry
  - Allow an overdraft when you mean to
  - Edit every field of the customer record
  - Suspend, activate, verify KYC, hold transfers with a reason shown to the customer
  - Reset password and transfer PIN
  - Set per-customer transfer authorisation codes
  - **View as customer** — open the app through their eyes
  - Delete the account and everything attached to it
- **Approvals** — transfers, deposits and withdrawals in one tabbed queue, approve or
  decline with a note; declining releases the held funds automatically
- **Loans** — review applications, **change the terms before approving**, disburse on
  approval, post repayments, mark settled or defaulted
- **Cards** — approve, decline, freeze, set spending limits
- **KYC review** — inspect uploaded documents against the account details
- **Ledger** — bank-wide with filters, plus edit, reverse or delete any single entry
  (the balance is recalculated correctly in each case)
- **Messaging** — write to one customer or broadcast to everyone, inbox and/or email
- **Settings** — everything below
- **Audit log** — who did what, when, from which IP

---

## Approval flow

Nothing has to settle instantly. Under **Settings → Approvals** you decide what waits
for a human:

| Setting | Effect when on |
|---|---|
| Deposits | Logged deposits sit pending until confirmed against your records |
| Withdrawals | Payouts are held until released |
| Outward transfers | Domestic and wire transfers wait for approval |
| Internal transfers | Even customer-to-customer moves wait |

When something is pending, the debit is recorded but the money is **ring-fenced rather
than deducted**: the customer sees a ledger balance and a lower available balance, and
cannot spend the same funds twice. Declining releases the hold immediately.

## Payment authorisation

Also under Settings, choose what a customer must clear before a transfer goes through:

- **Transfer PIN** — the four digits they set under Security
- **Emailed one-time code** — sent per transfer, valid ten minutes
- **Authorisation codes** — COT, IMF, tax clearance, anti-terrorist certificate, or any
  sequence you define. Set `code_sequence` to control the order, then set each customer's
  actual codes on their profile page. The customer is walked through them one screen at a
  time, with a running summary of the payment beside each step.

---

## Email

Works out of the box with PHP's `mail()`. For anything real, switch to SMTP under
**Settings → Outgoing email** and use the **Send test** button at the bottom of the page
to confirm it before you rely on it.

The SMTP client is written from scratch over `fsockopen` — no PHPMailer, no Composer.
It supports STARTTLS, implicit SSL, and AUTH LOGIN.

Emails sent automatically: email confirmation, welcome, password reset and change,
sign-in codes, transfer authorisation codes, deposit/withdrawal/transfer submitted and
decided, loan received/approved/declined, card approved/declined, KYC decided, admin
credits and debits, and staff alerts for anything landing in the approvals queue.

---

## Layout

```
config.php            written by the installer (never commit this)
install.php           delete after setup
favicon.ico
index.php             marketing landing page
about.php  careers.php  contact.php  business.php  help.php  legal.php
login / register / verify / forgot / reset / logout
inc/
  bootstrap.php       session, includes, maintenance mode
  db.php              PDO connection and the 15-table schema
  helpers.php         settings, money, CSRF, flash, auth guards
  bank.php            the ledger engine — all money moves through post_transaction()
  mailer.php          dependency-free SMTP and the branded email shell
  layout.php          page shells, navigation, icons
  authside.php        the panel on sign-in screens
inc/site.php          header, footer and meta for the public website
user/                 the twelve customer pages
admin/                the twelve staff pages
assets/css/app.css    the whole design system, app and website
assets/js/app.js      confirmations, clipboard, loan calculator — no libraries
assets/img/           logo, favicons, social card, photography, empty-state art
storage/              SQLite database, blocked from the web
uploads/              KYC documents and payment proofs, PHP execution disabled
```

### The ledger rule

Every movement of money — customer transfer, admin credit, loan disbursement, fee,
repayment, reversal — goes through `post_transaction()` in `inc/bank.php`. Balances are
never written directly anywhere else. If you extend the app, keep it that way and the
books will stay straight.

---

## Security notes

- Passwords and PINs hashed with `password_hash()`
- CSRF token on every state-changing form
- Prepared statements everywhere, no string-built SQL with user input
- Sign-in rate limiting: six failures per IP per fifteen minutes
- Thirty-minute idle session timeout
- Separate staff sign-in door, admin-role check on every admin page
- `inc/` and `storage/` blocked by `.htaccess`, PHP execution disabled in `uploads/`
- Every staff action written to the audit log

On nginx there is no `.htaccess`, so add the equivalent denies for `/inc/`, `/storage/`
and `/config.php` to your server block.

---

---

## The public website

Six pages sit in front of the app, sharing one header and footer from `inc/site.php`:

| Page | What it is |
|---|---|
| `index.php` | Landing page — hero, product sections, security, testimonial, CTAs |
| `business.php` | Business banking: supplier payments, payroll, approvals, company cards |
| `help.php` | Help centre, grouped FAQ with a sticky contents rail |
| `about.php` | Story and principles |
| `careers.php` | Values, benefits, open roles and a working application form |
| `contact.php` | Contact details and a working enquiry form |
| `legal.php` | Terms, privacy, security and complaints — one file, four documents via `?doc=` |

**The forms actually work.** Contact submissions and job applications are written to an
`enquiries` table, emailed to every administrator and the support address, and acknowledged
to the sender. Staff read and reply to them under **Admin → Enquiries**, where each one can
be answered by email and marked new, answered or closed.

Editing the open roles is a one-line job: the `$roles` array at the top of `careers.php`.

### Motion and interaction

The marketing pages use a light behaviour layer, all of it dependency-free and all of it
disabled automatically under `prefers-reduced-motion`:

- Sections fade and rise into view on scroll via `IntersectionObserver`, staggered with a
  `--d` custom property on each element
- Figures marked `data-count` animate up once when scrolled into view
- The product showcase is a real tab set with `role="tablist"` and proper `aria-selected`
- The landing-page loan calculator is the same calculation the application form uses — the
  slider mirrors into the amount field, and the figures update as you drag

### Images

Everything in `assets/img/` ships with the script:

- `logo.svg`, `logo-light.svg` and the plain variants — the wordmark is **converted to
  outlines**, so the logo renders identically even if no webfont loads
- `mark.svg` — the engraved coin, used in the sidebar and on auth screens
- `favicon.svg`, `favicon-16/32.png`, `apple-touch-icon.png`, `icon-512.png`, `favicon.ico`
- `og-image.jpg` — 1200×630 social share card, wired into Open Graph and Twitter meta
- `empty-ledger.svg`, `empty-inbox.svg`, `empty-cards.svg`, `avatar-default.svg` — empty-state art
- Photography: `hero-payments`, `payments-portrait`, `lending`, `community`, `careers-banner`,
  each with a smaller `-sm` or square crop where a page needs one

To swap the photography, drop replacements in with the same filenames and keep roughly the
same aspect ratios. Nothing in the code needs touching.

## Making it yours

- **Name, currency, contact details** — Settings → Identity
- **Colours and type** — the tokens at the top of `assets/css/app.css`. The whole look
  comes from about twenty variables; change `--ink` and `--brass` and the app follows.
- **Navigation** — `nav_user()` and `nav_admin()` in `inc/layout.php`
- **New settings** — add the key to `default_settings()` in `inc/helpers.php` and a field
  in `admin/settings.php`; checkbox keys also go in the `$checkboxes` array on that page

## Known limits

- Broadcast messages are stored as a single shared row, so read state on them is global
  rather than per customer. Individual messages track read state properly.
- Amounts are stored as `DECIMAL(18,2)` on MySQL and `REAL` on SQLite. For a production
  bank at scale, move to integer minor units.
- Single currency per installation.

---

## Notices

Nothing is hardcoded into the footer or the legal pages. Two fields under
**Settings → Identity → Notices** control them, and both ship blank, so nothing renders
until you write something:

- **Footer note** — one line in the website footer and on the sign-in screen. Your licence
  or company registration details go here.
- **Legal page notice** — a short paragraph at the foot of the terms, privacy, security and
  complaints pages.

Leave them empty and the site simply shows nothing in those spots.

---

## Ready to run

This package ships already installed and **empty** — no demo customers, no transactions,
one administrator account.

| | |
|---|---|
| **Customer sign-in** | `/login.php` |
| **Staff sign-in** | `/admin/login.php` |
| **Email** | `admin@pbigroup.com` |
| **Password** | `BankDOM#2026` |

Upload and sign in at `/admin/login.php`.

### First three things to do

1. **Settings → Identity** — change the support email to your company address.
2. **Customers → your own record** — change the admin password.
3. **Settings → Outgoing email** — point it at real SMTP and use the test-send button.

### Starting over

To reset the database, delete `config.php` and `storage/pbigroup.sqlite`.
