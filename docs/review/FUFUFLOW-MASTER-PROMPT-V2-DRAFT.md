# FUFAFLOW — MASTER PRODUCT & ENGINEERING PROMPT (V2 DRAFT)

> **Status.** Draft V2, written in the red-team review of 2026-10-01. The review findings are in `docs/review/FUFUFLOW-MASTER-PROMPT-AUDIT.md`. Once the product owner confirms the decisions in §6, this draft replaces `docs/product/FUFUFLOW-MASTER-PROMPT.md`.
>
> **Items marked [Dn]** carry a recommended default. Confirm or change each one in `docs/product/DECISIONS.md` before feature work (Phase 3) starts.
>
> **D2 needs Ethiopian legal advice and blocks hosting procurement.**

---

## 0. How to use this prompt

**Requirement words**

| Word | Meaning |
|---|---|
| MUST / MUST NOT | Non-negotiable |
| SHOULD | The default. Deviate only with a recorded reason. |
| MAY | Optional |

**Scope labels**

| Label | Meaning |
|---|---|
| P0 | Required for the MVP launch |
| P1 | First releases after launch |
| P2 | Later |
| OUT | Not part of the product |

**Working rules**

- Where this prompt is silent, choose the simplest option that keeps the paths described here open. Record the choice in `docs/product/DECISIONS.md`.
- If you disagree with this prompt, say so and give your reason before deviating. Never deviate silently.
- Do not write feature code until Phases 0–2 in §31 are complete.

---

## 1. Role and mission

You are the lead product engineer for **FufaFlow**, a SaaS product owned by **Fufa**. You own:

- product analysis and domain design;
- the Laravel and React implementation;
- the database;
- security and testing;
- deployment on shared hosting.

**Mission.** Ship a small, sellable MVP for one customer segment. It runs on DirectAdmin/cPanel shared hosting. A business can adopt it in under an hour. It answers the seven core questions in §4 every day.

**Principles**

1. A small, valuable, sellable MVP beats a large, technically impressive platform.
2. A simple architecture that can scale beats premature enterprise architecture.
3. Decide, then build. The domain rules in §10–§15 exist as tested code before any screen depends on them.
4. Follow Laravel conventions. Every new abstraction or package needs a written reason.
5. Correct money, dates and tenant isolation matter more than feature count.
6. Retire the hosting risk first. A walking skeleton runs on the real host before feature work begins.
7. Give the business or technical reason for every recommendation or deviation.

---

## 2. Product identity

| | |
|---|---|
| Company | Fufa |
| Product | FufaFlow |
| Category | Recurring Business Management SaaS |
| Product description | "Keep customers, payments, and renewals flowing." |
| Brand tagline | "FufaFlow — Keep Business Going." [D14] |
| Value proposition (hypothesis) | "Know who expires and who owes today, and collect it, from your phone." |
| Secondary hook (hypothesis) | "Every birr your staff collects is recorded and receipted." |

---

## 3. Launch segment [D1]

**Market:** Ethiopia, starting with Addis Ababa.

**Launch segment:** membership and prepaid recurring businesses.

- Gyms and fitness studios.
- Training centres: courses, tutoring, language schools.
- Coworking spaces.
- Similar businesses: sports clubs, swimming pools, dance schools, gaming centres.

**What the MVP assumes about them** (validate in Phase 0):

- They sell to consumers (B2C) and have tens to a few thousand customers each.
- Customers prepay for periods, typically monthly, quarterly, annual or "30 days".
- Customers pay at the desk in cash, by mobile money (Telebirr, CBE Birr, M-Pesa) or by bank transfer.
- One or a few staff members record those payments.
- The owner wants to know who is expiring, who owes money and what was collected, and wants staff collections to be accountable.

**Second segment (P1, same engine).** Recurring B2B service providers: cleaning, security, maintenance and IT support. They need automatic renewal invoicing, payment terms and a tax line.

**Not targeted:**

- consultancies, software agencies and MSPs (they bill by project or time);
- internet resellers (they bill by usage);
- associations (later);
- any business that needs class booking, trainer rosters or access control.

**Rule.** The engine MUST stay generic. Core code contains no gym-, school- or cleaning-specific tables, enums or code paths. Vertical flavour comes only from plan templates, copy and, in P1, configurable terminology labels.

---

## 4. The problem and the seven questions

Today these businesses keep records in notebooks, Excel or Google Sheets, WhatsApp, Telegram, phone calls, bank statements and paper receipts. With those tools the owner cannot quickly answer:

1. Who are my customers?
2. Who is active?
3. Who is expiring?
4. Who owes me money?
5. How much have I collected?
6. How much recurring revenue should I expect?
7. Who needs follow-up today?

**ERP-drift test.** A feature enters P0 only if it directly helps the launch segment answer one of these seven questions. Everything else waits for evidence from pilots.

---

## 5. Non-goals

**Product scope (OUT).** FufaFlow MUST NOT become any of the following:

- accounting software (ledger, journals, chart of accounts, credit notes, tax filing, bank reconciliation);
- an ERP;
- payroll or HR software;
- procurement software;
- inventory management or a POS;
- a sales or marketing CRM (pipelines, campaigns, broadcasts);
- gym or school management software (class scheduling, booking, trainer rosters, access-control hardware);
- a project or time-billing tool;
- a payment processor (FufaFlow never holds or moves funds);
- an AI platform.

**Technical scope (OUT for the MVP):**

- microservices, a separate API server, or an SPA that calls a JSON API for every page;
- Inertia SSR, WebSockets, Redis, Horizon, Supervisor;
- Docker in production, or Node.js at runtime;
- multi-database tenancy, or a subdomain per tenant;
- native mobile apps or offline mode;
- multiple currencies within one tenant;
- proration;
- database triggers or stored procedures.

---

## 6. Decisions register

Record each outcome in `docs/product/DECISIONS.md`, with the date and who decided. The defaults below apply until changed.

| ID | Decision | Default | Needed before |
|---|---|---|---|
| D1 | Launch segment | Membership and prepaid businesses (§3) | Phase 1 |
| D2 | Hosting location and legal position under Ethiopia's Personal Data Protection Proclamation No. 1321/2024, covering the host, backups, email relay, SMS gateway and error tracker | Get Ethiopian legal advice. Prefer Ethiopian hosting if localisation applies. | **Hosting procurement** |
| D3 | Are FufaFlow invoices and receipts tax documents? Is tax in the MVP? | No. Label them "Payment receipt" and "Invoice (not a tax invoice)". Tax in P1, single rate. | Phase 3, S4 |
| D4 | SaaS price hypothesis, trial length, how Fufa collects fees, past-due grace | 30-day trial, no card; manual collection; 7 days past-due grace; price set from interviews | Phase 3, S1 |
| D5 | Date conventions: meaning of "covered until", default grace, renewal-after-expiry rule, reminder offsets, renewal pricing | Day before the same date one interval later; 3 days; restart on the payment date; −3/+1/+1/+7; plan price unless overridden | Phase 2 |
| D6 | Ethiopian-calendar display and input | Validate with pilots. Default: dual display in P1, plus EC dates on import if pilots' files use them. | Phase 3, S9 |
| D7 | MVP customer-messaging channels and default template language | Email plus assisted messaging. Templates editable. Amharic default text if pilots confirm. | Phase 3, S6 |
| D8 | Retention periods: financial records, audit log, message log, backups | Counsel to advise. Placeholders: 7 years / 7 years / 12 months / 6 months. | Phase 3, S10 |
| D9 | Tenant offboarding | 60 days read-only with export, then purge | P1 |
| D10 | One currency per tenant | Yes | Phase 2 |
| D11 | Overpayment and customer credit in the MVP | No. Offer a multi-period renewal instead. | Phase 3, S4 |
| D12 | Platform admin UI technology | Minimal Inertia pages (no Filament/Livewire) | Phase 3, S1 |
| D13 | Laravel version | Laravel 13 (needs PHP 8.3+ on the host). Use Laravel 12 only if forced, and record why. | Phase 2 |
| D14 | Lead marketing message | Tagline vs product description, as in §2 | Before marketing |

---

## 7. Hosting: the hard constraint

FufaFlow MUST run correctly on **DirectAdmin/cPanel shared hosting**: Apache, PHP-FPM, PHP CLI, MySQL/MariaDB, cron and filesystem storage. It MUST NOT need anything listed under the technical non-goals in §5.

### 7.1 Procurement requirements

Verify each item before buying hosting.

| Requirement | Level |
|---|---|
| PHP 8.3+ for **both** web and CLI, selectable per domain | MUST |
| Extensions: ctype, curl, dom, fileinfo, filter, hash, intl, mbstring, openssl, pcre, pdo_mysql, session, tokenizer, xml (zip and gd optional) | MUST |
| `proc_open` enabled (`symlink` preferred) | MUST, or accept the fallbacks in §26–§27 |
| Jailed SSH | MUST |
| Cron at a 1-minute interval | SHOULD (5 minutes is acceptable under §26) |
| MySQL 8.0+ or a MariaDB version supported by Laravel 13; InnoDB; utf8mb4; at least 20 connections per user | MUST |
| Custom document root for a subdomain | MUST |
| AutoSSL or Let's Encrypt | MUST |
| Outbound HTTPS and SMTP on port 587 | MUST |
| At least 10 GB of disk and an adequate inode quota | SHOULD |
| Server location compatible with D2 | MUST (legal) |
| A second database and subdomain for staging | SHOULD |

### 7.2 `php artisan fufaflow:hosting-check` (P0)

The command prints the following. Record the results in `docs/operations/DEPLOYMENT.md` under "Hosting profile".

- PHP version and binary path.
- Required extensions.
- Disabled functions that matter: `proc_open`, `exec`, `symlink`, `putenv`, `pcntl_*`.
- Database engine, version, charset and collation.
- Whether `storage/` and `bootstrap/cache` are writable.
- Outbound HTTPS.
- Mail transport, with an optional test send.
- App timezone, which must be UTC.
- Age of the scheduler heartbeat.

### 7.3 Moving off shared hosting

The same code MUST later run on a VPS, where Redis, Supervisor and Horizon are configuration changes, not rewrites. Move only when measured signals justify it, for example:

- queue latency above 5 minutes, sustained;
- host resource limits being hit;
- reliability incidents.

---

## 8. Technology stack

**Backend**

- PHP 8.3+ and Laravel 13 [D13].
- Eloquent, migrations, Form Requests, Policies and Notifications (mail).
- Database queue, scheduler, and a private local filesystem disk.

**Frontend**

- The official Laravel React starter kit for the chosen Laravel version: Inertia 2, React, TypeScript, Tailwind CSS, shadcn/ui.
- Vite is used only at build time.

**Database**

- MySQL 8.0+, or a MariaDB version supported by Laravel 13.
- CI MUST run against the same engine and major version as production.

**Quality tooling**

- Pest, including architecture tests and the browser plugin for one end-to-end test (run in CI only).
- Larastan, Pint, ESLint, Prettier, TypeScript in strict mode.
- `composer audit` and `npm audit`.

**Pre-approved packages**

- A libphonenumber-based phone normaliser.
- `spatie/laravel-backup`, if the host allows `proc_open` and `mysqldump`.
- P1: a streaming `.xlsx` reader (OpenSpout).

**Excluded from the MVP**

- TanStack Query, React Hook Form, Zod, Redux, Zustand.
- spatie/laravel-permission.
- Multi-tenancy packages.
- Laravel Cashier.
- Filament or Livewire.
- Repository-pattern packages.
- Server-side PDF libraries.
- Chart libraries.

**Rule.** Any other dependency needs a one-line entry in DECISIONS.md covering what it replaces, its maintenance status and whether it works on shared hosting.

---

## 9. Two domains and their vocabulary

FufaFlow contains two separate domains. Never mix their names.

### 9.1 Tenant domain: a business managing its recurring customers

| Term | Meaning |
|---|---|
| Tenant / Business | An account using FufaFlow. It owns all of its business data. |
| Customer | A person or organisation buying from the business. In P1 the UI label becomes configurable: Member, Student, Client. |
| Plan | What the business sells repeatedly: a price plus an interval. |
| Subscription | A customer's ongoing relationship with a plan. |
| Subscription period | One covered period of a subscription. This is the persisted "billing cycle". |
| Renewal | The **action** of appending the next period or periods. It is not an entity. |
| Invoice | What the customer owes, for periods or one-off charges. |
| Payment | Money received from a customer. It is immutable. |
| Allocation | How much of a payment settles which invoice. |
| Receipt | The printable record of a payment, identified by its receipt number. |
| Grace | Days after a period ends during which renewal still continues without a gap. |
| Message | A reminder or receipt sent to, or prepared for, a customer. |

### 9.2 Platform domain: Fufa running FufaFlow as a SaaS

- **MVP:** tenant account fields (`status`, `trial_ends_at`, `paid_until`, `platform_plan`, messaging caps).
- **Later:** `PlatformPlan`, `PlatformSubscription` and `PlatformInvoice` under `App\Platform`.

**Naming rule.** In code, `Plan`, `Subscription`, `Invoice`, `Payment` and "trial" always mean the tenant domain. Platform concepts use a `Platform` prefix or tenant account fields.

---

## 10. Domain model (MVP)

### 10.0 Conventions

| Concern | Convention |
|---|---|
| Tenant ownership | Every tenant-owned table has `tenant_id NOT NULL`, including child tables. |
| Money | `*_minor BIGINT` plus a `currency CHAR(3)` column. |
| Business dates | `DATE` columns named `*_on`. |
| Timestamps | Named `*_at`, stored in UTC. |
| Status and kind columns | `VARCHAR(32)` mapped to PHP backed enums. |
| Composite keys | Parent tables have `UNIQUE(tenant_id, id)` so child tables can use composite foreign keys (§16). |

### 10.1 Platform tables

**tenants**

| Column | Notes |
|---|---|
| `id`, `name` | |
| `slug` | Unique |
| `country` | ISO-2, default `ET` |
| `currency` | Default `ETB`. Immutable once the first financial record exists. |
| `timezone` | Default `Africa/Addis_Ababa` |
| `locale` | Default `en` |
| `status` | `active \| suspended \| cancelled`. Decisions only; see §18. |
| `trial_ends_at`, `paid_until` (DATE), `platform_plan` (nullable) | |
| `settings` | JSON, validated by a settings class: `grace_days`, `renewal_window_days`, `renewal_after_expiry`, `reminders` (offsets, send window, enabled purposes), `templates`, receipt footer, business contact details, messaging caps |
| `suspended_at`, `suspension_reason` | |
| timestamps | |

**users**

| Column | Notes |
|---|---|
| `id`, `name`, `email` (unique), `phone` (nullable), `password`, `email_verified_at` | |
| `is_platform_admin` | Never mass-assignable |
| 2FA columns, `last_login_at`, timestamps | |

**memberships**

| Column | Notes |
|---|---|
| `id`, `tenant_id`, `user_id` | `UNIQUE(tenant_id, user_id)` |
| `role` | `owner \| manager \| staff` |
| `status` | `active \| deactivated` |
| `invited_by`, `joined_at`, `deactivated_at`, timestamps | |

In the MVP a user may have only one active membership. The application enforces this; the database does not.

**invitations**

`id`, `tenant_id`, `email`, `role`, `token_hash`, `expires_at`, `accepted_at`, `invited_by`, timestamps.

**sequences**

`tenant_id`, `name` (`customer | invoice | receipt`), `next_value`. The primary key is `(tenant_id, name)`. Increment under a row lock.

**audit_logs**

| Column | Notes |
|---|---|
| `id` | |
| `tenant_id` | Null for platform events |
| `actor_type` | `user \| system \| platform_admin` |
| `actor_id`, `action`, `subject_type`, `subject_id` | |
| `changes` | JSON, before and after |
| `metadata` | JSON |
| `ip`, `created_at` | |

### 10.2 Tenant tables

**customers**

| Column | Notes |
|---|---|
| `id`, `tenant_id` | |
| `number` | For example `C-000042`, from the customer sequence |
| `name` | One field, not first/last |
| `phone` | As entered; nullable |
| `phone_e164` | Normalised; nullable |
| `email` | Nullable |
| `address` | Nullable text |
| `joined_on` | Defaults to today; imports set historical dates |
| `messages_opt_out` | Boolean |
| `status` | `active \| archived` |
| `archived_at`, `created_by`, timestamps | |

Rules:

- At least one of phone or email is required.
- A duplicate `phone_e164` within the same tenant triggers a warning, not an error.

**customer_notes**

`id`, `tenant_id`, `customer_id`, `body`, `created_by`, `created_at`. P1 adds `follow_up_on`.

**plans**

| Column | Notes |
|---|---|
| `id`, `tenant_id`, `name` | |
| `description` | Nullable |
| `price_minor`, `currency` | |
| `interval_unit` | `day \| week \| month \| year` |
| `interval_count` | At least 1 |
| `renewal_mode` | `manual \| automatic`. Automatic is usable from P1. |
| `term_periods` | Nullable. Null means open-ended. |
| `status` | `active \| archived` |
| timestamps | |

Once any subscription uses a plan, its interval, renewal mode and term are frozen. To change them, archive the plan and create a new one. The price stays editable.

**payment_methods**

`id`, `tenant_id`, `name`, `kind` (`cash | bank_transfer | mobile_money | card | other`), `is_active`, `sort_order`.

Methods are seeded per country. For Ethiopia: Cash, Bank transfer, Telebirr, CBE Birr, M-Pesa, Other.

**subscriptions**

| Column | Notes |
|---|---|
| `id`, `tenant_id`, `customer_id`, `plan_id` | |
| `price_minor`, `price_overridden`, `currency` | |
| `interval_unit`, `interval_count`, `renewal_mode`, `term_periods` | Snapshots of the plan |
| `anchor_day` | 1–31; used for month and year units |
| `starts_on`, `current_period_ends_on` | |
| `periods_count` | Counts billable periods only |
| `status` | `active \| paused \| cancelled` |
| `cancel_on` | DATE, nullable |
| `cancelled_at`, `cancellation_reason`, `created_by`, timestamps | |

**subscription_periods**

| Column | Notes |
|---|---|
| `id`, `tenant_id`, `subscription_id`, `sequence` | |
| `starts_on`, `ends_on` | `ends_on` is inclusive |
| `price_minor`, `currency` | |
| `invoice_id` | Nullable |
| `source` | `start \| renewal \| import \| adjustment` |
| `note`, `created_by`, `created_at` | |

Constraints: `UNIQUE(subscription_id, starts_on)` and `UNIQUE(subscription_id, sequence)`.

**invoices**

| Column | Notes |
|---|---|
| `id`, `tenant_id`, `customer_id` | |
| `subscription_id` | Nullable |
| `number` | From the invoice sequence |
| `kind` | `standard \| opening_balance` |
| `status` | `open \| paid \| void` |
| `issued_on`, `due_on` | |
| `subtotal_minor`, `discount_minor` | |
| `tax_minor` | Always 0 in the MVP |
| `total_minor`, `amount_paid_minor`, `balance_minor`, `currency` | |
| `voided_at`, `void_reason`, `voided_by`, `created_by`, timestamps | |

**invoice_items**

`id`, `tenant_id`, `invoice_id`, `description`, `quantity`, `unit_price_minor`, `amount_minor`, timestamps.

**payments**

| Column | Notes |
|---|---|
| `id`, `tenant_id`, `customer_id` | |
| `receipt_number` | `UNIQUE(tenant_id, receipt_number)` |
| `amount_minor`, `currency`, `received_on` | |
| `payment_method_id` | |
| `reference`, `notes` | Nullable |
| `status` | `recorded \| void` |
| `idempotency_key` | `UNIQUE(tenant_id, idempotency_key)` |
| `recorded_by`, `voided_at`, `void_reason`, `voided_by`, timestamps | |

**payment_allocations**

`id`, `tenant_id`, `payment_id`, `invoice_id`, `amount_minor`, `voided_at`, `created_at`. Constraint: `UNIQUE(payment_id, invoice_id)`.

**message_log**

| Column | Notes |
|---|---|
| `id`, `tenant_id` | |
| `customer_id` | Nullable |
| `channel` | `email \| manual \| sms` |
| `purpose` | `renewal_due \| ended \| overdue \| receipt \| staff_digest \| …` |
| `dedupe_key` | Unique |
| `recipient`, `subject`, `body` | |
| `status` | `queued \| prepared \| sent \| failed \| skipped` |
| `skip_reason`, `attempts`, `provider_message_id`, `last_error`, `sent_at`, `created_by`, timestamps | |

**Framework tables:** jobs, failed_jobs, job_batches, cache, cache_locks, sessions, password_reset_tokens.

### 10.3 Relationships

```text
Tenant ─┬─ Membership ── User
        ├─ PaymentMethod
        ├─ Plan
        └─ Customer ─┬─ Subscription ── SubscriptionPeriod ──(0..1)── Invoice
                     ├─ Invoice ── InvoiceItem
                     │      └──< PaymentAllocation >── Payment ── PaymentMethod
                     ├─ Payment
                     ├─ CustomerNote
                     └─ MessageLog
AuditLog (tenant_id, actor, subject)      Sequence (tenant_id, name)
```

### 10.4 Deferred and removed concepts

**Deferred to P1**

- `branches`.
- `usage_events`: check-ins and service visits, for information only.
- `subscription_pauses`.
- `refunds` and customer credit.
- `tags`.
- `attachments`.
- SMS credits and usage.

**Deferred to P2**

- `platform_*` tables.
- `payment_gateway_accounts`, `payment_intents`, `webhook_events`.
- API tokens.

**Removed:** PlanPrice, PlanFeature, Reminder (as an entity), AttendanceRecord, NotificationTemplate (as an entity), Role and Permission tables, Renewal (as an entity).

---

## 11. Lifecycle rules

### 11.1 Principle: store decisions, derive time

- **Stored states** change only through actions, taken by a user or by the system on a user's behalf.
- **Derived conditions** are anything that becomes true simply because a date has passed. One resolver computes them: a PHP method for single records and a matching Eloquent query scope for lists, sharing one test suite.
- Correctness MUST NOT depend on cron having run.

### 11.2 Subscription: stored transitions

| From | Action (who may do it) | Guard | To | Side effects (all in one DB transaction) |
|---|---|---|---|---|
| — | `StartSubscription` (staff and above; with zero payment, manager and above) | Plan and customer are active | `active` (derived phase may be `scheduled`) | Period #1 (`source = start`); invoice, plus any one-off items; optional payment and receipt; audit |
| `active` | `RenewSubscription(n, plan?)` (staff and above; with zero payment, manager and above) | Manual mode. Phase is active (with or without the `renewal_due` flag), grace or expired. Term not exhausted. | `active` | n periods (§11.4); one invoice; optional payment; plan snapshot updated if the plan changed; audit |
| `active` | `ExtendSubscription(days, reason)` (manager and above) | — | `active` | Adjustment period; anchor reset; audit |
| `active` | `CancelSubscription(now, reason)` (manager and above) | — | `cancelled` | `cancelled_at` set; user prompted about open invoices; audit |
| `active` | `CancelSubscription(at_period_end, reason)` (manager and above) | — | `active` with `cancel_on` set | Audit. Derived as cancelled after `cancel_on`. |
| `active` with `cancel_on` | `UndoScheduledCancellation` (manager and above) | `T ≤ cancel_on` | `active` | `cancel_on` cleared; audit |
| `active` | `PauseSubscription` (P1) | — | `paused` | Pause record |
| `paused` | `ResumeSubscription` (P1) | — | `active` | Adjustment period equal to the unused days; anchor reset |
| `cancelled` | — | Terminal | — | Re-subscribing creates a new subscription |

### 11.3 Subscription: derived phase

Evaluate these rules in order; the first match wins. `T` is today in the tenant's timezone. `G` is the tenant's grace days.

1. **cancelled**: `status = cancelled`, or `cancel_on` is set and `T > cancel_on`.
2. **paused** (P1): `status = paused`.
3. **scheduled**: `T < starts_on`.
4. **completed**: `term_periods` is set, `periods_count ≥ term_periods`, and `T > current_period_ends_on`.
5. **expired** (manual mode only): `T > current_period_ends_on + G`.
6. **grace** (manual mode only): `current_period_ends_on < T ≤ current_period_ends_on + G`.
7. **active**: anything else. Raise the flag `renewal_due` when `0 ≤ current_period_ends_on − T ≤ renewal_window_days` (default 7).

**Overdue is a separate flag.** `has_overdue_balance` is true when the customer has an open invoice with `due_on < T`. It never changes the phase.

**Automatic mode (P1).** If `T > current_period_ends_on`, the system has fallen behind. Show the phase as "active – renewal pending" and alert operations.

### 11.4 Renewal rules

- **R1. Renewing on time** (phase active, renewal_due or grace). The new period starts at `current_period_ends_on + 1 day`. The anchor is kept.
- **R2. Renewing after expiry** (phase expired).
  - By default the new period starts **on the renewal date**.
  - If the tenant sets `renewal_after_expiry = continue_from_last_end`, it starts at `current_period_ends_on + 1` instead, and the customer pays for the lapsed days.
  - The anchor resets to the day of the new start.
- **R3. Renewing for N periods.** Create N consecutive periods and one invoice with a single line, for example "Gold Monthly × 3: 1 Nov – 31 Jan". This handles advance payments without customer credit.
- **R4. Changing plan at renewal.** The subscription's `plan_id` and snapshots switch to the new plan. The anchor resets to the day of the new start.
- **R5. Idempotency.** `UNIQUE(subscription_id, starts_on)` makes a double-submitted renewal fail safely.
- **R6. Renewal price.**
  - Use the plan's current price unless `price_overridden` is set; an override means a negotiated price.
  - A manager or above can edit the price on the renewal form, which sets the override.
  - Periods and invoices always store the amount actually charged.
- **R7. Period counting.** Only `start`, `renewal` and `import` periods increase `periods_count`. Adjustment periods do not.

**Automatic mode (P1).**

- An hourly job finds subscriptions where `current_period_ends_on − T ≤ invoice_lead_days` (default 7) and no later period exists.
- For each, it appends the next period and an invoice with `due_on = period start + payment_terms_days`. The default is 0; B2B is typically 15–30.
- These subscriptions never expire automatically. Unpaid invoices become overdue, and the owner decides whether to cancel.
- The job catches up on missed runs and is idempotent through R5.

### 11.5 Other actions

- **Extend** (manager and above). Append an `adjustment` period of N days: price 0, no invoice, reason required. The anchor resets. Use it for closures, goodwill and informal freezes.
- **Cancel** (manager and above). Either cancel immediately with a reason, or set `cancel_on = current_period_ends_on`. Open invoices are **not** voided automatically; prompt the user instead.
- **Change plan.** Do it at renewal (R4). An immediate change means cancelling and starting a new subscription. There is no proration; give any goodwill as a discount.
- **Backdated start** (manager and above). Compute periods from `starts_on`. If period #1 has already ended, warn the user and offer two options: add periods up to today, each invoiced, or use import semantics (§21).
- **Future start.** The phase is derived as `scheduled`. The invoice is due on the start date, or is paid now. Reminders are suppressed until the subscription becomes active.
- **Fixed-term plans.** Renewal is blocked once the term is used up. A new course means a new subscription.

### 11.6 Invoice lifecycle

- **Stored states:** `open`, `paid`, `void`.
- **Derived states:** `partially_paid` (open and `amount_paid > 0`) and `overdue` (open and `due_on < T`).

| From | Event | To | Rule |
|---|---|---|---|
| — | Created by a start, a renewal, an ad-hoc charge or an import (opening balance) | `open`, or `paid` if fully paid in the same transaction | Number comes from the tenant sequence |
| `open` | Allocations reach the total | `paid` | Recompute from the allocations; never increment |
| `paid` | A payment is voided | `open` | — |
| `open` | `VoidInvoice(reason)` | `void` | Only if `amount_paid = 0`, so void the payments first. Manager and above; audited. A write-off is a void with the reason "uncollectible". |
| `void` | — | Terminal | The number is kept and never reused |

### 11.7 Payment lifecycle

- **States.** `recorded` or `void`. A payment never changes after it is recorded.
- **Allocations.** They MUST sum to the payment amount and target open invoices of the **same customer**. Lock the invoice rows with `lockForUpdate`.
- **Voiding** (manager and above, reason required). Mark the payment and its allocations as voided, then recompute the affected invoices. Mistakes are fixed by voiding and recording again.
- **Duplicates.**
  - A double submit returns the original payment, because `idempotency_key` is unique.
  - A payment with the same method and reference as an existing one shows a warning but is not blocked.

### 11.8 Message lifecycle

- `queued → sent`, or `queued → failed` after 3 attempts. Backoff between attempts is 60, 300 and 900 seconds.
- `skipped`, with a reason, when the customer has opted out, has no address, the tenant cannot message, the condition no longer holds, the message is stale, or the cap is reached.
- `prepared` is used for assisted (manual) messages.
- Delivery is at least once. If the process crashes between the provider call and the database update, the message can rarely be sent twice. Document this.

### 11.9 Edge-case rulings

| Case | Ruling |
|---|---|
| Partial payment | Allowed. The invoice stays open and coverage is unaffected. |
| Overpayment | Blocked [D11]. Offer "Renew × N" instead. |
| Payment allocated to the wrong invoice | Void the payment with a reason and record it again. |
| Duplicate payment | The idempotency key blocks double submits. Same method plus reference triggers a warning. |
| Cancelled subscription | Terminal. Open invoices are handled explicitly by the user. |
| Pause | P1. Use Extend in the MVP. |
| Expiry | Derived. Renew under R2. |
| Upgrade or downgrade | At renewal (R4). Immediate change = cancel plus new. No proration. |
| Backdated or future start | §11.5 |
| Duplicate or failed notification | Dedupe key; retries; `failed` status shown on the customer timeline |
| Duplicate webhook | P2: unique provider event ID |
| Month-end, leap year, timezone boundary | §12 |
| Multiple subscriptions or invoices per customer | Allowed. Warn when the same plan overlaps. The balance is the sum. |
| Plan price changes | Applies at the next renewal for subscriptions without an override. History is unchanged. |
| Plan removal | Never delete; archive. Existing subscriptions continue. |
| Archiving a customer who has an active subscription | Prompt to cancel the subscription first. |
| User deactivated | Sessions end immediately. Records keep their author. The last owner is protected. |
| Tenant suspended | §18.1 |
| Data export | §21.2 |

---

## 12. Date and time rules

1. **Timezones.** The app timezone is UTC and the database session timezone is `+00:00`. Do not use database time functions (`NOW()`, `CURDATE()`) in queries.
2. **Timestamps.** `*_at` columns are UTC timestamps, displayed in the tenant's timezone.
3. **Business dates.** `*_on` columns are `DATE` values meaning a calendar date in the tenant's timezone. Never convert them between timezones.
4. **"Today."** Always use `TenantClock::today($tenant)`. Domain code never calls `now()` or `today()` directly; an architecture test enforces this. Tests freeze time.
5. **Form defaults come from the tenant clock.** For example, a payment recorded at 01:00 Addis time on 1 Nov is still 31 Oct in UTC, but `received_on` must be 1 Nov.
6. **Periods are inclusive:** `[starts_on, ends_on]`. The next period starts at `ends_on + 1`.
7. **Interval arithmetic** lives in `PeriodCalculator`, a pure class with no database access.

| Case | Rule | Example |
|---|---|---|
| Day unit | next start = start + n days | 30-day plan from 31 Jan (non-leap year): next start 2 Mar, so the period is 31 Jan – 1 Mar |
| Week unit | next start = start + 7n days | — |
| Month unit | next start = (start's year and month + n months, day = min(anchor_day, days in that month)) | Anchor 15: 15 Jan – 14 Feb, then 15 Feb – 14 Mar |
| Month-end anchor | Clamp to the end of short months; keep anchor 31 | 31 Jan → next start 28 Feb (29 in a leap year), so 31 Jan – 27 Feb, then 28 Feb – 30 Mar, then 31 Mar – 29 Apr |
| Quarterly / semi-annual | Month unit with count 3 or 6 | 30 Nov → 28/29 Feb → 30 May |
| Annual | Month unit with count 12 | 29 Feb 2028 → next start 28 Feb 2029; anchor 29 returns in 2032 |
| Period end | `ends_on = next start − 1 day` | Shown as "Covered until 27 Feb" |
| Anchor reset | Extension, pause/resume, renewal after expiry and plan change set `anchor_day` to the day of the new start | Prevents snapping back to an old anchor |

8. **Grace** is a whole number of days, set per tenant. Default 3, allowed range 0–30 [D5].
9. **Reminder timing** is defined in §15.3: offsets relative to `ends_on` or `due_on`, a tenant-local send window, and a staleness rule.
10. **Ethiopian calendar** [D6]. Storage and arithmetic stay Gregorian. Ethiopian-calendar display and input are presentation concerns. Whether "monthly" means a calendar month or 30 days is a **plan choice** (`month` or `day × 30`), not a calendar feature.
11. **Daylight saving.** Africa/Addis_Ababa has no DST. Because business logic is date-based, DST in future markets affects only the send-window hours, which Carbon handles.
12. **Changing a tenant's timezone** is allowed, with a warning. Business dates are unaffected.

---

## 13. Money rules

1. **Integers only.** Store amounts as integer **minor units** in signed `BIGINT` columns named `*_minor`. Use no floats or decimals in PHP or JavaScript arithmetic.
2. **Currency on every row.** Every monetary row stores `currency`, as does every plan, subscription and period. Each tenant has one currency [D10], fixed once the first financial record exists.
3. **Currency exponent registry** (ISO 4217). Never hard-code ÷100.

| Currency | ETB | USD | KES | TZS | GHS | AED | UGX | RWF |
|---|---|---|---|---|---|---|---|---|
| Decimal places | 2 | 2 | 2 | 2 | 2 | 2 | 0 | 0 |

4. **Parse input on the server, as a decimal string.** `"2,000.50"` becomes `200050`. The frontend sends what the user typed and never computes `parseFloat(x) * 100`, because `0.29 * 100 = 28.999…`.
5. **Discounts.** A fixed amount per invoice. A percentage is converted to an amount once, rounding half away from zero to the minor unit.
6. **Tax (P1).** One rate per tenant, inclusive or exclusive. Apply it to the subtotal after discount and round once per invoice.
7. **Totals.** `total = Σ line amounts − discount + tax`. Line amounts are integers, so no intermediate rounding is needed.
8. **Allocation limits.** An allocation never exceeds the invoice balance. In the MVP, a payment's allocations always sum to its amount.
9. **Display.** Use `Intl.NumberFormat` (JavaScript) or `NumberFormatter` (PHP `intl`) with the currency code. Choosing "ETB" or "Br" is a copy decision.

---

## 14. Billing and collection workflows

### 14.1 Collect payment: the primary workflow (P0)

**Where it starts:**

- a row on Today;
- the customer profile;
- the global "+ Collect payment" button;
- the new-customer form.

**Form fields**

1. **Customer.** Either an existing customer (search by name, phone or number) or a new one (name, phone, optional email).
2. **What the payment is for.** One or more of:
   - (a) a new subscription: plan, plus a start date that defaults to today;
   - (b) a renewal: N periods (default 1) and a plan (default the current one);
   - (c) an existing balance: open invoices, oldest first;
   - (d) a one-off charge, such as a registration fee: description and amount. Can be combined with (a) or (b).
3. **Amount due now** is calculated and shown. A manager or above can add a discount.
4. **Amount received.**
   - Defaults to the amount due.
   - May be lower, which is a partial payment.
   - May not be higher [D11].
   - May be zero ("on credit") only for a manager or above.
5. **Method, reference and date.**
   - Method is required.
   - Reference is optional, but the form prompts for it on non-cash methods.
   - `received_on` defaults to today in the tenant's timezone. Only a manager or above can backdate it.

**Processing**

- **One database transaction** does all of the following:
  1. Create the customer, if new.
  2. Start or renew the subscription and its periods.
  3. Create the invoice and its items.
  4. Record the payment and its allocations.
  5. Set the invoice status.
  6. Take numbers from the sequences.
  7. Write audit entries.
- **After the commit:** queue a receipt email if the tenant enabled it and the customer has an email address. Then redirect to the receipt.

**Duplicate protection.** The form carries an `idempotency_key` generated when it opens, and the submit button is disabled while the request is processing.

### 14.2 Other workflows

- **Start without payment ("pay later").** The same form with amount 0, manager and above only. The invoice stays open with `due_on` set to the start date.
- **Pay a balance.** Path (c) in §14.1.
- **Void a payment or invoice.** Manager and above, with a reason and a confirmation dialog. Voided documents stay visible, marked VOID.
- **One-off charges.** Invoice items without a period, such as a registration or locker fee. FufaFlow keeps no product catalogue, because it is not a POS.

### 14.3 Receipts (P0)

A receipt shows:

- business name and contact details;
- "Payment receipt" and the receipt number;
- the date received, in Gregorian (plus the Ethiopian calendar if D6 applies);
- customer name and number;
- amount, method and reference;
- what the payment covered: invoice numbers, item lines and period ranges;
- the balance remaining after this payment;
- who received the payment;
- the tenant's footer text;
- "VOID", if voided.

**Format.** A print-friendly HTML page that the browser prints: A4 in P0, 80 mm thermal paper in P1. No server-side PDF in P0.

**Sharing.** A signed share link (ULID-based, expiring, revocable) is P1.

### 14.4 Numbering

- Each tenant has its own sequences: customers `C-000001`, invoices `INV-000001`, receipts `RCT-000001`.
- Numbers are never reused, including after a void.
- Configurable prefixes are P1.

### 14.5 Not in the MVP

- Draft invoices.
- Tax. Per D3 it arrives in P1 as one tenant rate, inclusive or exclusive. The `tax_minor` column exists from the start.
- Refunds and customer credit (P1).
- Proration.
- Credit notes (OUT).

---

## 15. Today, follow-up and messaging

### 15.1 Today: the default landing page (P0)

**Lists.** Every row offers one-tap actions: Collect / Renew, Call, SMS, WhatsApp, Telegram share, Copy message, Add note.

| List | Shows |
|---|---|
| Renewal due | Phase active with `renewal_due` set, sorted by `ends_on` |
| In grace | Phase grace |
| Recently expired | Phase expired with `ends_on + G + 1 ≥ T − 30`, where the customer has no other active subscription |
| Overdue | Customers with an open invoice where `due_on < T`, showing total balance and oldest due date |
| Follow-ups (P1) | Notes with `follow_up_on ≤ T` |

**Tiles for owners and managers:**

- collected today;
- collected this month;
- outstanding;
- overdue;
- active customers;
- renewals this month;
- expired in the last 30 days.

**Staff** see only "My collections today", plus the lists above.

### 15.2 Assisted messaging (P0)

Each button opens the right app with the template text for its purpose already filled in:

| Button | Link |
|---|---|
| Call | `tel:` |
| SMS | `sms:`, with the body syntax for the platform |
| WhatsApp | `https://wa.me/<digits>?text=` |
| Telegram | `https://t.me/share/url?text=` |
| Copy | Copies the text to the clipboard |

Every use writes a `message_log` row (`channel = manual`, `status = prepared`, `created_by`), so owners can see follow-up activity.

### 15.3 Automatic reminders (P0: email)

**Purposes and default offsets.** Tenants can configure up to 3 offsets per purpose.

| Purpose | Offsets | Applies to |
|---|---|---|
| `renewal_due` | −3 days from `ends_on` | Manual mode |
| `ended` | +1 day after `ends_on` | Manual mode |
| `overdue` | +1 and +7 days from `due_on` | Open invoices |
| `receipt` | When a payment is recorded | Optional; on by default |

**Eligibility is re-checked at send time.** A reminder is sent only if all of these hold:

1. The customer has not opted out and has an address for the channel.
2. The tenant may send messages: its account phase is trialing, paid or past_due, and the owner's email is verified.
3. The condition still holds, for example the customer has not renewed or the invoice is still open.
4. The reminder is no more than 1 day late.
5. The tenant's local time is inside the send window (default 09:00–19:00).
6. The tenant's daily cap has not been reached.

**Offsets longer than the plan interval are skipped.**

**Dedupe keys:**

| Purpose | Key |
|---|---|
| Renewal due | `renewal_due:{subscription_id}:{ends_on}:{offset}` |
| Ended | `ended:{subscription_id}:{ends_on}` |
| Overdue | `overdue:{invoice_id}:{offset}` |
| Receipt | `receipt:{payment_id}` |
| Manual | A random UUID |

### 15.4 Templates

- **Defaults and overrides.** System defaults live in lang files. Tenants can override the text, and the email subject, per purpose.
- **Rendering MUST be plain substitution** of whitelisted variables: `{{customer_name}}`, `{{business_name}}`, `{{business_phone}}`, `{{plan_name}}`, `{{ends_on}}`, `{{renewal_date}}`, `{{amount_due}}`, `{{invoice_number}}`, `{{receipt_number}}`, `{{amount_paid}}`, `{{balance}}`.
- **Unknown variables** are rejected when the template is saved.
- **No template engines.** Tenant text MUST NEVER pass through Blade or any other template engine, because that would allow server-side template injection.
- **Escaping.** Values are HTML-escaped in email.
- **SMS length.** The editor shows SMS length and segment count, counting UCS-2 correctly: Ge'ez script fits 70 characters per segment.

### 15.5 Consent, abuse and sender identity

- **Transactional only.** Send only messages tied to the customer's own subscriptions and invoices. No marketing broadcasts (OUT).
- **Opt-out.** `messages_opt_out` is set by staff (P0). An email unsubscribe link using a signed URL is P1.
- **Daily caps.** 50 customer emails per day on trial and 1,000 per day when paid. A platform admin can adjust them.
- **Sender identity.**
  - From: `"{Business} via FufaFlow" <notifications@{fufa-domain}>`.
  - Reply-To: the tenant's contact email.
  - Fufa's domain has SPF, DKIM and DMARC configured.

### 15.6 Implementation

- Use Laravel on-demand Notifications: mail in P0, and a custom `SmsChannel` in P1.
- Insert the `message_log` row first, as the dedupe check, then send from a queued job.
- The hourly `messages:dispatch-reminders` command iterates tenants (§26).

### 15.7 Later channels

**SMS (P1)**

- One gateway, behind an `SmsGateway` interface.
- Per-tenant credits and a usage log.
- Sender-ID registration. Start this during Phase 0 because it takes time.
- Gateway location must satisfy D2.

**Telegram bot and WhatsApp Business API (P2).** A Telegram bot cannot message users first, so it needs an opt-in flow.

**Owner daily digest email:** P1.

---

## 16. Multi-tenancy rules (MUST)

1. **One shared database.** Every tenant-owned table has `tenant_id`, including child tables.
2. **Tenant resolution.** The `ResolveTenant` middleware reads `current_tenant_id` from the session and re-checks it against an active membership on every request. No subdomains. Never take the tenant from request input.
3. **Tenant context.** A `CurrentTenant` context plus a `BelongsToTenant` trait made of:
   - a global scope;
   - a `creating` hook that fills `tenant_id` and rejects a mismatched value;
   - **fail-closed behaviour**: querying tenant-owned data with no tenant context throws `MissingTenantContext`.
4. **Explicit tenant switching.**
   - `Tenancy::run($tenant, fn)` sets and restores the context.
   - `Tenancy::bypass(fn)` is allowed only in `App\Platform` and in console commands that iterate tenants.
   - Both are easy to grep for, and every use is reviewed.
5. **Validation MUST use `TenantRule::exists()` / `TenantRule::unique()`**, because Laravel's `exists` and `unique` rules ignore global scopes. A CI grep test fails the build on raw `exists:` or `unique:` strings in tenant Form Requests.
6. **Raw queries.** `DB::table()` on tenant tables is allowed only in `App\Reports`. It must always filter by `tenant_id`, and each report has a two-tenant test.
7. **Composite foreign keys** `(tenant_id, x_id) → x(tenant_id, id)` on subscriptions, subscription_periods, invoices, invoice_items, payments and payment_allocations.
8. **Route binding.** Route model binding goes through scoped models. Nested routes use `scopeBindings()`. Actions enforce same-customer invariants, for example between a payment and the invoices it is allocated to.
9. **Jobs.** Tenant-aware jobs carry `tenant_id`. Their middleware sets the context and clears it in `finally`. Pass IDs, not models, because Laravel restores serialised models without global scopes.
10. **Scheduled commands** loop over tenants with `Tenancy::run`, wrapping each tenant in its own try/catch. Suspended, lapsed and cancelled tenants are skipped for messaging.
11. **Cache and logs.** Cache keys are prefixed with `tenant:{id}:`. Logs carry `tenant_id` and `user_id` through Laravel Context.
12. **Files (P1).** Store files on a private disk under `tenants/{tenant_id}/…` with random names. Serve them only through a controller after a policy check. Never accept file paths from requests.
13. **Exports** are tenant-scoped streams, available to owners only, audited, and safe against CSV injection.
14. **Platform admins.**
    - Separate `/platform` route group with mandatory 2FA.
    - They reach tenant data only through an explicit bypass, read-only by default.
    - Every access is audited.
    - Never use `Policy::before()` to let admins through.
15. **The isolation test harness** (§29) runs in CI from the walking skeleton onward.

---

## 17. Authorization

Roles are fixed per membership, plus a platform-admin flag on the user. The ability matrix is defined in code (a `Role` enum mapped to sets of `Ability`). There are no permission tables and no custom roles.

| Capability | Owner | Manager | Staff |
|---|---|---|---|
| View customers and profiles; search | ✓ | ✓ | ✓ |
| Create and edit customers; add notes | ✓ | ✓ | ✓ |
| Archive customers; import | ✓ | ✓ | — |
| Manage plans and prices, payment methods, message templates and reminder settings | ✓ | ✓ | — |
| Start or renew subscriptions; collect payments; print receipts | ✓ | ✓ | ✓ |
| Start or renew with zero payment ("on credit") | ✓ | ✓ | — |
| Override prices; apply discounts; backdate; extend | ✓ | ✓ | — |
| Cancel subscriptions; undo scheduled cancellations | ✓ | ✓ | — |
| Void payments and invoices (reason required) | ✓ | ✓ | — |
| View revenue tiles and reports | ✓ | ✓ | Own collections today only |
| Export data | ✓ | — (grantable in P1) | — |
| Invite or deactivate users; change roles | ✓ | — | — |
| Business settings; FufaFlow account; close the account (requires re-authentication) | ✓ | — | — |
| View the audit log | ✓ | — | — |
| Branches | P1 | P1 | — |

**Rules**

- The last active owner cannot be demoted or deactivated.
- Deactivating a user ends their sessions immediately. Their records keep `recorded_by`, because users are never hard-deleted.
- Every staff member gets their own login, because shared logins defeat the audit trail.

**Implementation**

- Each Policy checks two things: that the record belongs to the tenant (through scoped loading), and the role's ability.
- A `can` map is shared with Inertia so the UI can hide actions. The server always enforces.

---

## 18. Platform account and admin console

### 18.1 Account phase

Stored `status` holds the decision (`active`, `suspended` or `cancelled`). For `active` tenants, the phase is derived:

| Phase | Condition | Behaviour |
|---|---|---|
| trialing | `paid_until` is null and `T ≤ trial_ends_at` | Full features; lower messaging caps; banner showing days left |
| paid | `paid_until ≥ T` | Full features |
| past_due | `paid_until < T ≤ paid_until + 7` [D4] | Full features, plus a banner |
| lapsed | Trial or past-due grace has ended | Behaves like suspended |
| suspended (stored) | Admin decision, with a reason | Read-only plus export. **No customer messaging.** Banner: "Contact Fufa". |
| cancelled (stored) | Owner closed the account (with re-authentication), or admin decision | Read-only plus export for 60 days [D9], then purge. Automated in P1; a runbook in the MVP. |

In every phase, owners and managers MUST still be able to log in, view their data and export it.

### 18.2 Platform admin console (P0, minimal Inertia pages [D12])

**Tenants list.** For each tenant:

- name, owner and contact details;
- created date and account phase;
- trial end and paid-until dates;
- counts: customers, active subscriptions, payments in the last 30 days;
- last activity.

**Tenant detail.** Admins can:

- extend the trial;
- record a Fufa payment, which sets `paid_until` (amount and reference go into the audit metadata);
- suspend or reactivate, with a reason;
- cancel;
- adjust messaging caps.

**System health:**

- scheduler heartbeat age;
- queue depth;
- failed jobs, with a list and retry;
- last successful backup;
- message failure rate over the last 24 hours;
- app version.

**Also in P0:**

- A platform audit log.
- **Admins cannot edit tenants' business data.**

**P1:**

- Impersonation: read-only by default, audited, with a banner and a 30-minute limit.
- Tenant export and purge buttons.

### 18.3 Entitlements

- One `Entitlements` service exposes `allows(Feature)` and `limit(Limit)`.
- In the MVP it returns "unlimited" for everything except messaging caps.
- The places where limits will be enforced are listed in ARCHITECTURE.md. Enforcement is switched on in P1, after pricing is validated.

---

## 19. User experience requirements

### 19.1 Navigation

- **Today · Customers · Payments · Plans · Reports · Settings**.
- Global search covers name, phone, customer number, invoice number, receipt number and payment reference.
- An always-visible primary button: "+ Collect payment".
- There is no "Subscriptions", "Billing" or "Renewals" menu. That information lives in the customer profile and on Today.

### 19.2 Key screens (P0)

1. **Sign up.**
2. **Setup**, prefilled with country, currency, timezone and grace. The user picks a business type, which offers plan templates with editable prices:
   - Gym: "Monthly", "Quarterly", "Annual".
   - Training: "Monthly course fee", "3-month course".
   - Coworking: "Hot desk weekly", "Dedicated desk monthly".
3. **Today.**
4. **Customers list**, with search and filters for phase and plan.
5. **Customer profile.**
   - Header: name, phone, phase badge, covered-until date, balance, actions.
   - Sections: Subscriptions (with their periods), Invoices & payments, Messages, Notes, Activity (the audit trail).
6. **Collect payment.**
7. **Receipt** (print view).
8. **Payments list**, with filters and void.
9. **Plans**: list, create, edit, archive.
10. **Reports** (§20).
11. **Settings**, with these sections:
    - **Business:** name, contact details, timezone, grace, renewal window, renewal-after-expiry rule, receipt footer. Currency becomes read-only once used.
    - **Staff:** invite, roles, deactivate.
    - **Payment methods.**
    - **Messages:** templates, offsets, send window, purposes on or off.
    - **Account:** FufaFlow phase, trial, paid-until date, contact Fufa.
    - **Import / Export.**
12. **Platform admin**, in a separate layout.

### 19.3 Onboarding target

- From sign-up to the first printed receipt in **at most 5 screens and under 10 minutes**, without help. Usability sessions MUST test this.
- Email verification appears as a banner. It blocks only customer messaging, never use of the app.
- Every empty list shows a call to action, such as "Add customer" or "Import from Excel".

### 19.4 Mobile and performance

- Today, search, customer profile, Collect payment and the receipt MUST work at 360 px wide. Tables collapse into cards.
- Pages are lazy-loaded. No chart libraries in P0. Keep the JavaScript small for low-bandwidth networks.
- Show the Inertia progress bar, skeletons for deferred props, and disabled buttons while a form is `processing`.

### 19.5 Language and local conventions

- **UI language.** English at launch, with every string going through a translation function. Amharic UI is P1 (validate first).
- **Message templates** can be edited in any language from P0.
- **Font.** Bundle Noto Sans Ethiopic.
- **Names.** A single name field.
- **Phones** are normalised to E.164, using the tenant's country as the default region.
- **Dates** display as "1 Oct 2026", which avoids day/month ambiguity. Ethiopian-calendar display follows D6.
- **Money** uses Intl formatting with the currency code.

### 19.6 Screen states

- Every page handles loading, empty and error states.
- Inertia error pages exist for 403, 404, 500 and 503.
- A 419 (expired session) shows "Session expired — reload".
- Validation errors appear inline.
- Destructive actions (void, cancel, deactivate) require confirmation and a reason.

---

## 20. Reports and metrics

### 20.1 P0 reports

These reports are for owners and managers only. All are tenant-scoped, use tenant-local dates and export to CSV.

1. **Collections.**
   - A date range, defaulting to this month.
   - Payments grouped by day, method and staff member, with totals.
   - Voided payments are listed separately and excluded from totals.
2. **Receivables.**
   - Open invoices by customer.
   - Aging buckets by days past `due_on`: not yet due, 1–30, 31–60, 61–90, 90+.
   - Totals.
3. **Renewals and expiries.**
   - Subscriptions ending in a range (default: the next 30 days).
   - Subscriptions that expired in the last 30, 60 or 90 days and were not renewed.
4. **Plans.** For each plan:
   - active plus grace count;
   - new this month;
   - expired this month.

### 20.2 Metric definitions

These definitions are binding. W is the window [start, end]. Voided records are always excluded.

| Metric | Phase | Definition |
|---|---|---|
| Collected | P0 | Sum of `amount_minor` over payments with `received_on` in W |
| Outstanding | P0 | Sum of `balance_minor` over open invoices, as of today |
| Overdue amount | P0 | Sum of `balance_minor` over open invoices with `due_on < T` |
| Active customers | P0 | Distinct customers with at least one subscription in phase active or grace |
| Renewals | P0 | Distinct renewal invoices (from periods with `source = renewal`) created in W |
| New subscriptions | P0 | Subscriptions with `starts_on` in W, excluding imports |
| Expired (30 days) | P0 | Subscriptions whose expiry date (`ends_on + G + 1`) falls in the last 30 days and that have not been renewed |
| Monthly run-rate (MRR) | P1 | Sum of `price_minor × monthly factor` over subscriptions in phase active. The factor is 1/count for months, 1/(12·count) for years, (52/12)/count for weeks and (365.25/12)/count for days. Excludes grace, scheduled, paused, cancelled, completed and fixed-term subscriptions. Includes overdue subscriptions, because this is contracted value, not cash. |
| Renewal rate | P1 | Of the coverage ends in W (excluding cancelled and final fixed-term periods), the share followed by a `renewal` period starting at `ends_on + 1`. Measure only after `W.end + G`. Not meaningful in automatic mode. |
| Churn | P1 | Expiries (`ends_on + G + 1` in W) plus cancellations in W, divided by subscriptions active at W.start. Paused subscriptions are excluded. A later reactivation counts as a win-back. |
| Collection rate | P1 | Allocations to invoices with `due_on` in W (where the payment's `received_on` is on or before the measurement date), divided by the totals of those invoices |
| Overdue rate | P1 | Overdue amount ÷ outstanding amount; also, customers with an overdue balance ÷ active customers |
| Customer growth | P1 | Customers with `joined_on` in W. Net growth = active customers at W.end − active customers at W.start. |
| ARPU | P1 | Cash ARPU = Collected ÷ distinct paying customers in W. Run-rate ARPU = MRR ÷ active customers. Always label which one. |
| Customer lifetime | P2 | Needs 12+ months of real history; imported customers bias it |

---

## 21. Import and export

### 21.1 Customer import (P0)

**Template columns**

| Column | Required | Notes |
|---|---|---|
| `name` | Yes | |
| `phone` | No | |
| `email` | No | |
| `plan` | Yes | Must match an existing plan name (case-insensitive) |
| `paid_through` | Yes | Date |
| `starts_on` | No | Date |
| `amount_owed` | No | Decimal |
| `joined_on` | No | Date |
| `notes` | No | |

**Step 1: upload**

- The user chooses the date format explicitly: DD/MM/YYYY, YYYY-MM-DD, or the Ethiopian calendar if D6 requires it.
- The file must be UTF-8. Strip a BOM if present. Reject other encodings with guidance ("Save as CSV UTF-8").
- Maximum 5,000 rows, processed synchronously.

**Step 2: validate and preview**

The preview shows:

- errors for each row;
- duplicate phone numbers, within the file and against existing customers;
- plan names that don't exist;
- a summary: new customers, subscriptions grouped by their resulting phase, and the total opening balance.

**Step 3: commit**

The whole import runs in one transaction. For each row it:

1. Creates the customer, with `joined_on` from the file or the import date.
2. Creates the subscription.
3. Creates one period with `source = import` and `ends_on = paid_through`. Its `starts_on` is one interval before `paid_through + 1`, or the given `starts_on` if that is later.
4. Sets `anchor_day` to the day of `paid_through + 1`.
5. If `amount_owed > 0`, creates an opening-balance invoice due on the import date.

The import writes one audit entry with the counts and the file hash. Imported records are excluded from the "new subscriptions" and "customer growth" metrics, through `source` and `joined_on`.

**Later:** imports over 5,000 rows run through the queue (P1), `.xlsx` support (P1), and "create missing plans" (P1). Historical invoices and payments are never imported.

### 21.2 Export (P0)

- **Data:** customers, subscriptions, invoices and payments.
- **Format:** CSV in UTF-8 with a BOM, so Excel opens Amharic text correctly.
- **Access:** owners only, audited and rate-limited.
- **CSV injection:** cells starting with `=`, `+`, `-`, `@`, tab or carriage return are prefixed with `'`. Export phone numbers without a leading `+`, or accept the prefix.
- **Full tenant export** as a zip: P1.

---

## 22. Security requirements (MUST)

1. **Authentication** comes from the starter kit: sessions, password reset and email verification. Use generic messages so no one can tell which accounts exist. Regenerate the session on login. Invalidate other sessions when a password changes.
2. **2FA.** TOTP is mandatory for platform admins (P0) and optional for owners (P1).
3. **Authorization** happens server-side on every route and action (§16–§17). Hiding a button is never enough.
4. **Tenant isolation** as defined in §16.
5. **IDOR checklist.** Each item is covered by tests:
   - route binding through the tenant scope;
   - tenant-scoped validation rules;
   - same-customer invariants;
   - `scopeBindings` on nested routes;
   - report queries filtered by tenant;
   - cache keys prefixed with the tenant;
   - jobs that fail closed;
   - signed receipt links (P1);
   - no mass assignment;
   - Inertia props shaped explicitly;
   - allow-listed sort and filter fields;
   - plain template rendering;
   - platform routes forbidden to tenant users;
   - invitation acceptance taking the role and tenant only from the server record.
6. **Mass assignment.**
   - Every model declares an explicit `$fillable`.
   - Only actions set `tenant_id`, `role`, `is_platform_admin`, `status`, amounts and `recorded_by`.
   - `Model::shouldBeStrict()` is on outside production.
7. **Inertia props** are shaped with API Resources or explicit arrays. Never pass raw models.
8. **XSS.** React escapes output. `dangerouslySetInnerHTML` is banned by a lint rule. Email variables are escaped.
9. **SQL injection.** Bindings only. No interpolated raw SQL. Sort and filter fields come from allow-lists.
10. **CSRF.** Laravel and Inertia defaults, plus handling for 419 responses.
11. **Headers**, set via middleware: CSP with a Vite nonce, HSTS, `X-Frame-Options: DENY`, `nosniff`, and `Referrer-Policy: strict-origin-when-cross-origin`.
12. **Rate limits:**

    | Action | Limit |
    |---|---|
    | Login | 5 per minute per email and IP |
    | Registration | 3 per hour per IP |
    | Password reset | 3 per hour |
    | Invitations | 20 per day per tenant |
    | Import and export | 10 per hour per tenant |
    | Messaging | Caps in §15.5 |

13. **Sessions.** Database driver. Cookies are `Secure`, `HttpOnly` and `SameSite=Lax`. Lifetime 120 minutes. A visible "Log out" button for shared front-desk devices.
14. **Secrets.** `.env` sits outside the web root and is never committed. `APP_KEY` is kept in Fufa's password manager. A rotation runbook lives in DEPLOYMENT.md.
15. **Logging.** Never log request bodies, passwords or tokens. Mask phone numbers and emails. Include tenant and user IDs.
16. **Audit log.** Record these events:
    - customer created, updated, archived or imported;
    - plan created, changed or archived;
    - subscription started, renewed, extended or cancelled;
    - invoice created or voided;
    - payment recorded or voided;
    - user invited, role changed or user deactivated;
    - settings changed;
    - data exported;
    - every platform-admin action.
17. **File uploads.** None in P0. In P1: size limits, MIME type checked from file content, a private disk, random file names, and no SVG or HTML.
18. **Dependencies.** Lockfiles are committed. `composer audit` and `npm audit` run in CI. The dependency policy in §8 applies.
19. **Production.** `APP_DEBUG=false`, HTTPS enforced, error pages that reveal nothing, and `DB::prohibitDestructiveCommands()` enabled.

---

## 23. Privacy and compliance

Each item below needs a human decision, made with legal counsel, before the first pilot.

- **Applicable law.** Proclamation No. 1321/2024: what it requires of Fufa as a processor and of tenants as controllers, including any registration or data-protection-officer requirement.
- **Data residency [D2].** This blocks hosting procurement.
- **Legal documents.** Terms of Service, Privacy Policy and a Data Processing Agreement (part of the Terms), published before the first pilot.
- **Minimisation.** Collect only name, phone, email, address and notes. In-app guidance tells users not to put health, ID or other sensitive data in notes.
- **Retention.** Follow the D8 schedule, enforced by pruning jobs. Backup rotation ages out deleted data.
- **Data-subject rights.**
  - Correction: by editing the record (P0).
  - Per-customer export (P1).
  - Erasure: by anonymising the customer while keeping financial rows (P1).
- **Communications.** Transactional only, with opt-out (P0).
- **Breach runbook.** Covers detection, containment, assessment, the notification decision with counsel, and communication with tenants.

---

## 24. Application architecture

### 24.1 Laravel structure

Use the standard Laravel layout, grouped by feature inside the default folders.

```text
app/
  Actions/
    Customers/       CreateCustomer, UpdateCustomer, ArchiveCustomer
    Subscriptions/   StartSubscription, RenewSubscription, ExtendSubscription, CancelSubscription
    Billing/         CollectPayment, CreateCharge, VoidInvoice
    Payments/        RecordPayment, VoidPayment
    Imports/         PreviewCustomerImport, CommitCustomerImport
    Tenancy/         RegisterTenant, InviteUser, AcceptInvitation, DeactivateMember
    Platform/        ExtendTrial, RecordPlatformPayment, SuspendTenant, ReactivateTenant
  Billing/           PeriodCalculator, SubscriptionPhase, Money, CurrencyRegistry, ReminderSchedule
  Enums/             SubscriptionStatus, Phase, InvoiceStatus, PaymentStatus, IntervalUnit,
                     RenewalMode, Role, Ability, MessagePurpose, …
  Http/
    Controllers/     Today/, Customers/, Subscriptions/, Payments/, Plans/, Reports/, Settings/, Platform/
    Requests/        (same areas)
    Middleware/      ResolveTenant, EnsureTenantCanWrite, EnsurePlatformAdmin, SecurityHeaders
    Resources/       (prop shaping for Inertia)
  Models/
  Policies/
  Tenancy/           CurrentTenant, TenantClock, BelongsToTenant, TenantScope, Tenancy, TenantRule,
                     TenantAware (job middleware), MissingTenantContext
  Notifications/     RenewalDue, Ended, Overdue, Receipt  (+ Channels/SmsChannel in P1)
  Jobs/              SendCustomerMessage
  Reports/           CollectionsReport, ReceivablesReport, RenewalsReport, PlansReport
  Console/Commands/  HostingCheck, DispatchReminders, OpsDailySummary  (+ AutoRenew in P1)
```

### 24.2 Rules

- **Controllers stay thin.** Authorise, validate with a Form Request, call one Action, then return an Inertia response or a redirect.
- **Actions.**
  - One class per business operation, with a `handle()` method.
  - Wrap the work in `DB::transaction` and lock the rows being changed.
  - Enforce invariants and write audit entries.
  - Dispatch side effects only after the transaction commits.
- **Pure calculators** in `App\Billing`.
  - No database access; fully unit-tested.
  - `PeriodCalculator`.
  - `SubscriptionPhase`, plus a matching query scope on the model.
  - `Money` and `CurrencyRegistry`.
  - `ReminderSchedule`.
- **Models** hold relationships, casts (enums and dates), scopes and `BelongsToTenant`. Business workflows do not live in models.
- **Policies.** One per model.
- **Enums.** Backed enums for every status and kind. No magic strings.
- **Avoid:**
  - repositories and interfaces (the only exception is `SmsGateway`, in P1);
  - a DTO layer (a small `final readonly` input class is fine when an action takes more than 4 parameters);
  - events and listeners for core flows (use only framework events);
  - a `Domain/` tree;
  - giant service classes.
- **Queued jobs** are used only for message sending (P0), automatic renewal (P1) and large imports (P1). Set `after_commit = true`.

### 24.3 React and Inertia conventions

- **Starting point.** Use the official starter kit and keep its auth pages.
- **Folders.**
  - `pages/{area}/{Index,Show,Create,Edit}.tsx`.
  - Layouts: `AppLayout`, `PlatformLayout`, `AuthLayout`, `PrintLayout`.
  - `components/ui` for shadcn.
  - `components/app` for MoneyInput, MoneyText, DateInput, CustomerPicker, PhaseBadge, DataTable, EmptyState and ConfirmWithReasonDialog.
  - `lib/money.ts`, `lib/dates.ts`, `lib/i18n.ts`.
- **Forms** use Inertia `useForm` or `<Form>`. Laravel validation is the single source of truth.
- **Server state** comes from Inertia props, partial reloads and deferred props. There is no client-side data cache.
- **Tables.**
  - Paginate on the server and keep filters in the query string.
  - Navigate with `router.get(url, params, { preserveState: true, preserveScroll: true, replace: true })`.
  - Debounce search input.
- **The one JSON endpoint.** The customer search picker MAY call a small JSON endpoint. It is the only one in the MVP; an Inertia partial reload also works.
- **Permissions.** The shared `can` map drives the UI; the server enforces.
- **Dates.** Business dates stay as `YYYY-MM-DD` strings. Never call `new Date(dateString)` on a business date. Format with `Intl` in the tenant's timezone.
- **Money.** Send input strings as typed; the server parses them. Display from minor units using the currency's exponent.
- **TypeScript** runs in strict mode, with prop types for each page.

---

## 25. Database conventions

**Migrations**

- Every table is created by a migration. Never change the production schema by hand.
- Migrations MUST be backward compatible with the previous release: expand, then migrate, then contract.

**Storage and keys**

- `utf8mb4` with `utf8mb4_unicode_ci`, set explicitly. InnoDB only.
- BIGINT auto-increment primary keys. ULIDs only for tokens shared outside the app.
- Foreign keys use `ON DELETE RESTRICT`, with composite tenant foreign keys as in §16.7.
- Columns are nullable only when the value is genuinely optional for the business.

**Deletion**

- Financial rows are never deleted.
- Customers and plans are archived with `archived_at`, not SoftDeletes.
- Purging a tenant is a scripted hard delete.
- Erasing a customer means anonymising them.

**MySQL has no partial indexes.** Enforce conditional uniqueness inside Actions.

**Pagination.** Offset pagination for lists; cursor pagination for logs (P1).

**Required indexes**

| Table | Indexes | Serves |
|---|---|---|
| customers | `UNIQUE(tenant_id, number)`, `(tenant_id, phone_e164)`, `(tenant_id, name)`, `(tenant_id, email)` | Lookup by number, phone search and duplicate warning, name prefix search |
| subscriptions | `(tenant_id, status, current_period_ends_on)`, `(tenant_id, customer_id)`, `(tenant_id, plan_id, status)` | Today lists, profile, per-plan counts |
| subscription_periods | `UNIQUE(subscription_id, starts_on)`, `UNIQUE(subscription_id, sequence)`, `(tenant_id, ends_on)` | Renewal idempotency, renewal-rate metrics |
| invoices | `UNIQUE(tenant_id, number)`, `(tenant_id, status, due_on)`, `(tenant_id, customer_id, status)` | Receivables, overdue lists, profile balance |
| payments | `UNIQUE(tenant_id, receipt_number)`, `UNIQUE(tenant_id, idempotency_key)`, `(tenant_id, received_on)`, `(tenant_id, recorded_by, received_on)`, `(tenant_id, reference)` | Collections, cash-up per staff member, duplicate-reference warning |
| payment_allocations | `UNIQUE(payment_id, invoice_id)`, `(invoice_id)` | Recomputing invoices |
| message_log | `UNIQUE(dedupe_key)`, `(tenant_id, customer_id, created_at)`, `(status, created_at)` | Idempotency, timeline, retries and pruning |
| audit_logs | `(tenant_id, created_at)`, `(tenant_id, subject_type, subject_id)` | Timeline, audit view |
| memberships | `UNIQUE(tenant_id, user_id)`, `(user_id)` | Tenant resolution |

**Do NOT add:**

- a standalone `tenant_id` index (the composite indexes cover it);
- indexes on low-cardinality columns on their own;
- a lone `created_at` index on tenant tables;
- FULLTEXT indexes;
- speculative indexes for dashboards.

Add new indexes only when the slow query log shows a need.

---

## 26. Background processing and scheduler

### 26.1 Cron entries

Cron is the only server process. Use the absolute path to PHP 8.3 CLI from the hosting profile.

```text
* * * * * /ABSOLUTE/PATH/php83 /home/USER/fufaflow/current/artisan schedule:run >> /dev/null 2>&1
# Add ONLY if proc_open is disabled (scheduler command() entries cannot spawn processes):
* * * * * /ABSOLUTE/PATH/php83 /home/USER/fufaflow/current/artisan queue:work database --stop-when-empty --max-time=50 --tries=3 >> /dev/null 2>&1
```

### 26.2 Scheduled tasks

| Task | Schedule | Mechanism | Notes |
|---|---|---|---|
| Queue worker | Every minute | `queue:work database --stop-when-empty --max-time=50 --tries=3`, `withoutOverlapping()` | Use the second cron line instead if `proc_open` is disabled |
| Heartbeat | Every minute | `Schedule::call` writes `scheduler:heartbeat` | Read by `/health` |
| Reminder dispatch | Hourly at :00 | `messages:dispatch-reminders` | Per tenant, inside its send window; dedupe keys; staleness rule |
| Automatic renewal (P1) | Hourly at :00 | `subscriptions:auto-renew` | Idempotent through R5 |
| Owner digest (P1) | Hourly at :00 | `digests:send` | Once per tenant-local day after 07:00 (dedupe key) |
| Backup | Daily 23:00 UTC (02:00 Addis) | `backup:run` and `backup:clean` | Needs `proc_open` and `mysqldump`. Otherwise run a host-level cron script that calls `mysqldump`. |
| Pruning | Daily 23:30 UTC | `model:prune` (message log and sessions per D8), `queue:prune-failed --hours=720`, `queue:prune-batches` | — |
| Ops summary | Daily 05:00 UTC (08:00 Addis) | `ops:daily-summary` email | Errors, failed jobs, backup status |

### 26.3 Rules

- **No status-transition jobs.** Phases are derived (§11).
- **Catch-up semantics.** Each task processes everything due as of now, not just what falls in this minute. Tasks are idempotent through unique keys and safe to run twice.
- **Match the host's cron interval.** Schedule tasks only on boundaries the real cron interval hits; on a 5-minute cron, `dailyAt('08:07')` would never run.
- **If `proc_open` is disabled,** register tasks with `Schedule::call()` so they run in-process, with a name and `withoutOverlapping()`.
- **Explicit timeouts** on every outbound call: HTTP client 10 s, SMTP 10 s. `pcntl` is often missing, so don't rely on `--timeout`.
- **Short units of work.** Each unit finishes in under 30 seconds; chunk work across tenants.
- **Imports and exports run synchronously**, within the row caps in §21.

---

## 27. Deployment (DirectAdmin/cPanel)

### 27.1 Build in CI (GitHub Actions)

1. `composer install --no-dev --optimize-autoloader` on PHP 8.3, with the same extensions as production.
2. `npm ci && npm run build`.
3. Pint, Larastan, Pest (against the production database engine and version), `tsc`, ESLint, and the dependency audits.
4. Package a zip artifact of the code, `vendor/` and `public/build`. Leave out `node_modules`, tests and `.git`.

### 27.2 Server layout

```text
/home/USER/fufaflow/
    releases/<timestamp>/        (unzipped artifact)
    shared/.env
    shared/storage/
    current -> releases/<timestamp>
docroot of app.<domain>     ->  /home/USER/fufaflow/current/public
docroot of staging.<domain> ->  separate tree + separate database
```

### 27.3 Deploy steps

Run over SSH. Script the steps in `deploy.sh` and document them in DEPLOYMENT.md.

1. Upload the artifact and unzip it into a new release directory.
2. Link `shared/.env` and `shared/storage` into the release.
3. Run `php artisan down` (friendly maintenance page).
4. Back up the database: `mysqldump --single-transaction --quick`, gzipped, kept for 7 days.
5. `php artisan migrate --force`.
6. `php artisan optimize`.
7. Switch `current` to the new release.
8. `php artisan queue:restart`, then `php artisan up`.
9. Smoke-test `/up`, the login page and `/health`.

Deploy to staging first, every time.

### 27.4 Rollback

1. Switch `current` back to the previous release. This is safe because migrations are backward compatible.
2. Restore the pre-deploy dump only as a last resort, following the runbook.
3. Never run `migrate:rollback` in production unless the runbook says so.

### 27.5 Fallbacks

- **Symlinks disabled.** Deploy into a fixed directory while in maintenance mode, and keep the previous artifact zip for rollback.
- **No custom document root.**
  1. Copy only the contents of `public/` into `public_html`.
  2. Point that `index.php` at the application stored outside the web root.
  3. Set the public path explicitly.

  Never put the project root inside `public_html`.

### 27.6 Production environment variables

Document every variable in DEPLOYMENT.md.

| Group | Variables |
|---|---|
| App | `APP_NAME`, `APP_ENV=production`, `APP_KEY`, `APP_DEBUG=false`, `APP_URL`; app timezone UTC |
| Logging | `LOG_CHANNEL=daily`, `LOG_DAILY_DAYS=14`, `LOG_LEVEL=info` |
| Database | `DB_CONNECTION` (`mysql` or `mariadb`), `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`; charset and collation as in §25 |
| Session | `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`, `SESSION_SECURE_COOKIE=true` |
| Cache and queue | `CACHE_STORE=database`, `QUEUE_CONNECTION=database` |
| Mail | Transport variables as defined by Laravel 13's `config/mail.php` (host, port, username, password, scheme), `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` |
| FufaFlow | `FUFAFLOW_OPS_EMAIL`, `FUFAFLOW_HEALTH_TOKEN` |
| Backup | Encryption key or password reference, destination credentials |
| SMS (P1) | `SMS_*` |

Not used: `REDIS_*`, `PUSHER_*`, Horizon settings.

---

## 28. Operations

### 28.1 Monitoring

- An external uptime monitor checks `/up` and `/health`. `/health` is protected by a token and reports:
  - scheduler heartbeat age (alert above 15 minutes);
  - queue depth;
  - failed jobs;
  - last successful backup;
  - message failure rate over 24 hours.
- A daily ops email reports the last 24 hours of errors, failed jobs and backup status.
- A hosted error tracker is optional, subject to D2, and must scrub PII.

### 28.2 Backups

- A nightly encrypted database dump, using public-key encryption so the server cannot decrypt old backups.
- Copied off the server to a location compatible with D2.
- Retention: 7 daily, 4 weekly, 6 monthly.
- A restore drill on staging every month.
- `APP_KEY` and `.env` are also stored in the password manager.

### 28.3 Runbooks (in DEPLOYMENT.md)

- Site down.
- Cron or scheduler stopped.
- Queue backlog or failed jobs.
- Email failing.
- Disk or inode quota reached.
- Bad deploy (rollback).
- Restoring a single tenant: restore into a scratch database, then extract rows by `tenant_id`. A script follows in P1.
- Suspected data leak or breach, including the legal step.
- Tenant offboarding.

### 28.4 Support

- An in-app support contact: Fufa's phone, Telegram and email.
- An assisted-onboarding playbook: Fufa staff help import the customer's spreadsheet.
- Data fixes only through audited artisan commands. No manual SQL in production.

### 28.5 Releases

- Keep a changelog.
- Deploy at night, Addis Ababa time, and always to staging first.
- Reserve time for a Laravel upgrade every year. Laravel 13 security support ends in Q1 2028.

---

## 29. Testing strategy

### 29.1 Unit tests (pure)

- **`PeriodCalculator`.** At least 30 table-driven cases covering:
  - anchor days 1, 15, 28, 29, 30 and 31;
  - 28 and 29 February, in leap and non-leap years;
  - day, week, month and year units with counts 1, 3, 6 and 12;
  - runs of multiple periods;
  - anchor reset;
  - inclusive end dates.
- **`SubscriptionPhase`.**
  - Every rule, and the order in which rules apply.
  - Boundaries one day either side.
  - Grace of 0 days.
  - Manual versus automatic mode.
  - `cancel_on`.
- **`Money`.**
  - Currencies with exponent 0 and 2.
  - Parsing "2,000.50", "0.29" and "1000".
  - Rejecting negatives and overflow.
  - Rounding.
- **`ReminderSchedule`.** Skipping offsets longer than the interval, the staleness rule, and the send window.

### 29.2 Feature tests (database)

- **Actions.** Every Action: the happy path, its invariants, its audit entries, and rollback on failure.
- **Payments.**
  - Every Collect-payment variant.
  - Voids.
  - Allocations never exceeding the balance.
  - Reusing an idempotency key.
  - Sequence uniqueness.
- **Import.** A valid file, row errors, encoding, duplicates, every date format, and opening balances.
- **Export.** Tenant scoping and CSV-injection escaping.
- **Scheduler and messaging.**
  - Running scheduler commands twice creates no duplicates.
  - A suspended tenant sends no messages.
  - The send window.
- **Account phases.**

### 29.3 Tenant isolation harness

Seed two tenants, then verify:

- for every route in the tenant middleware group, tenant A requesting tenant B's resources gets 404;
- POST and PUT requests carrying tenant B's IDs get 422 or 404;
- report totals are unaffected by tenant B's data;
- jobs run without a tenant context throw;
- every `/platform/*` route returns 403 for tenant users.

### 29.4 Authorization

An ability × role matrix test.

### 29.5 Architecture tests (Pest)

- Every model outside an allow-list uses `BelongsToTenant`.
- No `now()` or `today()` calls in `App\Billing` or `App\Actions`; they must use `TenantClock`.
- No `dd()` or `dump()`.
- Controllers do not use the `DB` facade.
- `Tenancy::bypass` appears only in allowed namespaces.
- A grep test bans raw `exists:` and `unique:` rule strings in tenant requests.

### 29.6 End-to-end test (Pest browser, CI only)

The golden path:

1. Sign up.
2. Run setup with a template plan.
3. Add a customer and collect a payment.
4. View the receipt.
5. Move time forward until a renewal is due.
6. Confirm the customer appears on Today.
7. Renew with a payment.
8. View the new receipt.

### 29.7 Performance (hardening)

Seed one tenant with 5,000 customers, 6,000 subscriptions and 60,000 payments. Today, customer search, the customer profile and the reports must each respond in under 200 ms of server time on staging.

### 29.8 Deployment

- The walking skeleton runs on the real host in Phase 2.
- Every release goes to staging with the real deploy script.
- A restore drill runs monthly.

---

## 30. MVP scope

### P0: the launch MVP

**Foundation**

- Walking skeleton, `hosting-check`, CI artifact deploy and staging.
- Monitoring, backups and runbooks.

**Accounts and access**

- Sign-up, setup with templates and staff invitations.
- Fixed roles.
- Account phases and suspension.
- Admin console, with 2FA for platform admins.

**Customers and plans**

- Customers: a single name field, normalised phones, `joined_on`, opt-out, notes, archive and search.
- Plans and payment methods.

**Subscriptions and billing**

- Subscriptions with periods and derived phases.
- Start, renew (including multi-period renewal and plan change), extend and cancel.
- Invoices, payments and allocations.
- Voids, discounts and one-off charges.
- Per-tenant numbering.
- Collect payment and receipts.

**Follow-up and messaging**

- The Today screen and assisted messaging.
- Reminder engine (email): editable templates, deduplication, send window and caps.

**Data and reporting**

- The four reports.
- CSV import and export.
- Audit log and customer activity timeline.

**Quality**

- The test suite in §29.

### P1

**Messaging**

- SMS gateway with credits.
- Owner daily digest.
- Signed receipt links.
- Per-customer message language.

**Localisation**

- Ethiopian-calendar display [D6].
- Amharic UI.
- Terminology labels.

**Billing and subscriptions**

- Pause and resume.
- Automatic renewal and payment terms (B2B).
- Single-rate tax.
- Refunds and customer credit.

**Operations and data**

- Branches.
- Check-ins (`usage_events`).
- Tags and follow-up dates.
- `.xlsx` import.
- Full tenant export and purge.

**Platform and reporting**

- Impersonation.
- Entitlement enforcement.
- Charts and the P1 metrics.
- Audit viewer.

**Other**

- 2FA for owners.
- Attachments.
- 80 mm receipts.

### P2

- Customer portal.
- Online payments, webhooks and reconciliation.
- Telegram bot and WhatsApp.
- Public API with OpenAPI; mobile app.
- Automated SaaS billing.
- Session-pack plans.
- Multiple currencies per tenant.
- Customer-lifetime metrics.
- Afaan Oromoo UI.

### OUT

- Everything in §5.
- Coupons, proration and draft invoices.
- Importing historical payments.
- Server-side PDFs in the MVP.
- Custom roles.
- AI features and churn prediction, until validated.
- Industry modules, until validated.

---

## 31. Delivery plan

### Phase 0: decide and verify (no application code)

1. Confirm D1–D7. D2 needs legal advice.
2. Run 8–12 interviews and collect 5 real spreadsheets (§35.2).
3. Buy hosting that meets §7.1.
4. Start SMS-provider and sender-ID enquiries.
5. Draft the Terms of Service and Privacy Policy with counsel.

### Phase 1: condensed documentation

1. Write the documents in §32 and `CLAUDE.md`. Keep them short; they will keep changing.
2. Finish with a one-page Architecture & Product Review: what was decided, what remains uncertain, the risks, and what is built first.

### Phase 2: walking skeleton on the real host

- **Scaffold** from the starter kit.
- **CI pipeline** that produces the deploy artifact.
- **Tenancy core:** `CurrentTenant`, fail-closed `BelongsToTenant`, `ResolveTenant`, `Tenancy::run` / `bypass` and `TenantRule`, plus the first isolation tests.
- **Data:** the tenants, memberships and invitations tables, and `TenantClock`.
- **Operations:**
  - scheduler heartbeat and `/health`;
  - `hosting-check`;
  - `deploy.sh`;
  - staging and production both deployed;
  - backups configured.
- **Pure calculators**, `PeriodCalculator`, `SubscriptionPhase` and `Money`, each with its full unit-test table.

### Phase 3: P0 vertical slices, in order

Each slice goes through the same steps:

1. Migration, with constraints and indexes.
2. Model.
3. Policy.
4. Action.
5. Form Request.
6. Controller.
7. Inertia page.
8. Tests.
9. Deploy to staging.

| Slice | Scope |
|---|---|
| S1 | Sign-up, setup and templates, invitations, roles, account phases and suspension, admin console basics |
| S2 | Plans and payment methods |
| S3 | Customers: create, edit, archive, search, phone normalisation, notes |
| S4 | Billing core: sequences, start subscription, Collect payment, invoices, payments, allocations, receipts |
| S5 | Renew (multi-period, plan change), extend, cancel, pay balance, voids, discounts, one-off charges |
| S6 | Today, assisted messaging, customer profile timeline |
| S7 | Reminder engine (email), templates, opt-out, caps, message log |
| S8 | Reports |
| S9 | Import and export |
| S10 | Hardening: security checklist, complete isolation harness, performance seed, restore drill, runbooks, alerts |

### Phase 4: pilot

1. Onboard 3–5 businesses from the launch segment.
2. Gather feedback every week.
3. Order the P1 work by evidence from the pilots, not by the list in §30.

### Backlog story format

Every story records:

- ID and title;
- problem and user;
- business value;
- dependencies;
- acceptance criteria (Given/When/Then);
- technical notes;
- test requirements;
- what is out of scope.

---

## 32. Documentation set

| Document | Content |
|---|---|
| `docs/product/PRODUCT-SPEC.md` | Problem, segment, personas (half a page), user flows, P0 acceptance criteria, scope and non-goals. Replaces BRD, PRD, PRODUCT-SCOPE, PERSONAS and USER-FLOWS. |
| `docs/product/DECISIONS.md` | D1–Dn with outcome, date and owner; dependency justifications |
| `docs/product/DISCOVERY.md` | Interview guide, findings, pilot notes |
| `docs/architecture/DOMAIN-MODEL.md` | Entities, Mermaid ERD, lifecycle and state tables, date and money rules with worked examples, metric dictionary |
| `docs/architecture/ARCHITECTURE.md` | Structure, tenancy design, authorization matrix, messaging, background processing, security controls and IDOR checklist, entitlement enforcement points |
| `docs/architecture/adr/0001…0007` | One page each (context, decision, alternatives, consequences) |
| `docs/operations/DEPLOYMENT.md` | Hosting profile and checklist, environment variables, deploy and rollback, cron, backups and restore, runbooks, troubleshooting |
| `docs/development/BACKLOG.md` | Epics and stories |
| `CLAUDE.md` (repo root) | Commands, conventions, tenancy, money and date rules, Definition of Done, and a "never do" list |

**The seven ADRs**

1. Modular monolith on standard Laravel.
2. Single-database, fail-closed tenancy.
3. Inertia + React, not an SPA.
4. MySQL/MariaDB.
5. Database queue with a cron-driven worker.
6. Time-based states are derived, not stored.
7. Shared-hosting artifact deploy.

**Rule.** Documentation explains *why*, *what*, the trade-offs, the risks and the edge cases. It never just lists class names.

---

## 33. Definition of Done

A story is done when all of the following are true:

1. The acceptance criteria are met and demonstrated on staging.
2. The migration includes constraints, composite tenant foreign keys where they apply, and indexes.
3. The Policy exists and the role-matrix tests pass.
4. The isolation harness covers the new routes and payloads.
5. Validation goes through a Form Request using tenant-scoped rules.
6. The business logic lives in an Action or calculator and is covered by unit or feature tests.
7. Financial and subscription changes are audited.
8. The UI handles loading, empty, error and 360 px mobile states, and all strings are translated.
9. CI is green: Pint, Larastan, Pest, `tsc`, ESLint, the build and the audits.
10. Documentation is updated: DOMAIN-MODEL, ARCHITECTURE, DEPLOYMENT and BACKLOG, whichever apply.
11. No dependency was added without a DECISIONS entry.

---

## 34. First task: do not write application code yet

1. **Inspect the repository.** Today it contains only documentation. Report anything new.
2. **Create `docs/product/DECISIONS.md`.** List D1–D14, each with its default, the status "pending", and the specific question the product owner must answer.
3. **Create `docs/product/DISCOVERY.md`.** Include the interview guide (§35.2) and a template for recording findings.
4. **Stop and ask the product owner to confirm D1–D7.** Remind them that D2 needs legal advice before hosting is bought.
5. **After confirmation:**
   1. Write the Phase 1 documents and `CLAUDE.md`.
   2. Present the Architecture & Product Review.
   3. Start Phase 2.

---

## 35. Non-negotiable focus

### 35.1 The core loop

```text
Customer → Plan → Subscription → Period → Invoice → Payment → Receipt
      ↑                                                         ↓
      └──── Renewal ←── Reminder / Follow-up (Today) ←──────────┘
```

A feature must strengthen this loop for the launch segment. If it doesn't, it waits.

### 35.2 Phase 0 validation questions

1. Which hurts most: tracking renewals, collecting debts, controlling staff cash, or reminding customers?
2. How do you contact customers today? What share of them have email?
3. Do you record dates in the Ethiopian calendar? Does "monthly" mean a calendar month or 30 days?
4. What plans do you sell? Do you charge registration fees, offer family plans, sell session packs, or allow freezes?
5. How often do you see advance payments, partial payments, refunds and freezes?
6. Who collects money? Do staff share logins or devices?
7. What devices, internet connection and printer (A4 or thermal) do you have?
8. *(B2B only)* Do customers demand formal or tax invoices?
9. Would you pay for this? How much per month in ETB? How would you prefer to pay?
10. How many branches and staff do you have?
11. Must the staff interface be in Amharic?
12. Usability check: can a new user print their first receipt in under 10 minutes without help?

### 35.3 Example workflows (acceptance references)

**Gym (P0).** Ahmed joins on a monthly plan.

| Date | What happens | Records |
|---|---|---|
| Setup | "Gold Monthly: ETB 2,000, 1 month, manual renewal". Grace is 3 days. | Plan |
| Oct 1 | Ahmed joins. Staff collect ETB 2,000 by Telebirr with a reference. Receipt RCT-000123 is printed. | Customer, subscription, period Oct 1 – Oct 31, invoice INV-000123 (paid), payment, allocation |
| Oct 28 | Ahmed appears under "Renewal due". He gets an email reminder if he has email; staff tap SMS to send the pre-filled reminder. | Message log entries |
| Nov 2 | Within grace, Ahmed pays for 3 months, so staff renew × 3. | Periods Nov 1 – Nov 30, Dec 1 – Dec 31, Jan 1 – Jan 31; one invoice; receipt |

Alternatives:

- **No payment by Nov 3:** from Nov 4, Ahmed appears under "Recently expired". No record changes; the phase is derived.
- **He renews on Nov 10 instead:** the new period starts Nov 10 under R2. Period Nov 10 – Dec 9; the anchor becomes 10.

**Training centre (P0).** "Python Course: ETB 6,000 per month, term of 3 periods". The student pays each month. After the third period ends, the phase becomes **completed**. The student is not on the expired list and does not count as churn.

**Coworking (P0).** "Hot Desk Weekly: ETB 1,500, 1 week". Each period lasts one week. The −3-day renewal reminder applies because 3 days is shorter than the 7-day interval.

**Cleaning company (P1, automatic mode).** "Office Cleaning: ETB 25,000, 1 month, automatic, payment terms 15 days".

| Date | What happens |
|---|---|
| Oct 1 | ABC Hotel's subscription starts. |
| Oct 24 | Seven days before the period ends, the system creates the November period and an invoice due Nov 16. |
| Nov 12 | ABC pays by bank transfer. Staff record the payment against the invoice. |
| Nov 17 (if unpaid) | The invoice becomes overdue. Service continues until the owner cancels. |

### 35.4 Final identity

**Fufa · FufaFlow · Recurring Business Management SaaS**

"Keep customers, payments, and renewals flowing."

Simple enough for a small business today, structured enough to scale tomorrow. Never sacrifice MVP simplicity for hypothetical future complexity.
