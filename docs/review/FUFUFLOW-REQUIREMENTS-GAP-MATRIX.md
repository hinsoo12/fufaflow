# FufaFlow — Requirements Gap Matrix

Companion to `FUFUFLOW-MASTER-PROMPT-AUDIT.md`. Each row is one gap in `docs/product/FUFUFLOW-MASTER-PROMPT.md` (V1). Section references (§n) point to V1. "Audit §n" points to the audit document.

**Severity**

| Level | Meaning |
|---|---|
| Critical | Would lead to the wrong product, a data leak, a legal blocker, or an unimplementable core. Resolve before coding. |
| High | Significant rework, production failure, or adoption failure if not addressed in the MVP. |
| Medium | Quality, operability or cost issue. Fixable later at moderate cost. |
| Low | Polish or clarity. |

**MVP? column**

| Value | Meaning |
|---|---|
| Pre-code | A decision, document or procurement task that must happen before feature coding |
| Decide (Dn) | Needs a product, business or legal decision. Dn refers to audit §13.4. |
| P0 | Build in the MVP |
| P1 | Build soon after launch |
| P2 | Build later |
| Remove | Do not build. Remove it from the spec. |

**Critical gaps (resolve first):**

1. Launch segment (beachhead)
2. Business-model knobs (renewal mode)
3. SaaS account domain
4. Renewal definition
5. Persisted subscription periods
6. Subscription state model
7. Customer-messaging channel fit
8. Template injection
9. Tenancy mechanism
10. Tenant-scoped validation rules
11. IDOR catalogue
12. Data-protection law
13. Data residency
14. Customer import
15. One-step payment collection
16. Backups

---

## 1. Product and business

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Product › Beachhead | §8 lists 19+ segments; §79 validates 6 in parallel | No launch segment. Prepaid B2C and invoiced B2B models are conflated. | Critical | Launch with membership and prepaid businesses: gyms, studios, training centres, coworking. B2B services follow in P1 on the same engine. | Decide (D1) |
| Product › Business-model knobs | §62–§63 "the same core engine must support both" | Nothing distinguishes prepaid from invoiced: renewal timing, payment terms, tax | Critical | `renewal_mode` manual (P0) or automatic (P1). Payment terms and tax in P1. | P0 / P1 |
| Product › Segments | Consultancies, software agencies, MSPs, internet resellers included | Project, time or usage billing is a different product | High | Remove from the initial target list | Remove |
| Product › Value proposition | Generic "manage customers, subscriptions, payments…" | Neither differentiated nor tested | High | Hypothesis: "Know who expires and who owes today, and collect it, from your phone". Validate it. | Pre-code |
| Product › Positioning lines | "Keep Business Going" vs "Keep customers, payments, and renewals flowing" | Two competing headlines | Low | Treat one as the tagline and one as the product description | Decide (D14) |
| Product › Pricing | §80 tiers with no prices | No willingness-to-pay data; SMS add-on economics unknown | High | Build a price hypothesis from interviews. Sell SMS as a metered add-on. | Decide (D4) |
| Product › Validation plan | §79 lists segments only | No interview guide, success criteria or pilot plan | High | 8–12 interviews, 5 real spreadsheets, 3–5 pilots. Target: first receipt in ≤ 10 minutes. | Pre-code |
| Product › ERP drift | §1 says "not an ERP", but §8, §29, §33, §58 and §81 widen the scope | No test for admitting features | High | Adopt the ERP-drift test: a feature must serve one of the seven core questions | Pre-code |
| Product › Alternatives | Not covered | Local gym software and pricing unknown | Medium | Desk research plus interview questions | Pre-code |

## 2. SaaS platform (FufaFlow's own business)

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| SaaS › Account model | §11 "manage FufaFlow plans, suspend tenants" | No tenant account domain at all | Critical | Tenant fields: stored `status` (active, suspended, cancelled), `trial_ends_at`, `paid_until`, `platform_plan`. Account phase (trialing, paid, past_due, lapsed) is derived from them. | P0 |
| SaaS › Vocabulary | Plan, Subscription, Trial and Feature used for both domains | Confusion and accidental coupling | High | Tenant-domain names are reserved. Platform concepts use a `Platform*` prefix or tenant account fields. | P0 |
| SaaS › Trial | Not defined | No length, limits or end behaviour | Medium | 30 days, no card, lower messaging caps | Decide (D4) / P0 |
| SaaS › Suspension | "Suspend tenants" | What suspension does is undefined | High | Read-only plus export. Customer messaging stops. Banner shown. Reason audited. | P0 |
| SaaS › Fee collection | Not defined | No way to record tenants paying Fufa | Medium | Admin records the payment, which extends `paid_until` (audited) | P0 |
| SaaS › Limits / entitlements | Tiers imply limits | No enforcement design | Medium | One `Entitlements` service returning unlimited in the MVP. Enforce after pricing is validated. | P0 (stub) / P1 |
| SaaS › Metered add-ons | §80: SMS, storage as revenue | No metering | Medium | Per-tenant SMS credits and usage log, built with SMS | P1 |
| SaaS › Automated SaaS billing | "Do not overbuild" | No future path named | Low | `App\Platform` (PlatformSubscription, PlatformInvoice). Option: dogfood on FufaFlow. | P2 |
| SaaS › Offboarding | Not defined | No export window or purge | Medium | Read-only for N days with export, then purge | Decide (D9) / P1 |

## 3. Domain model

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Domain › Renewal | §62 (payment triggers renewal) conflicts with §63 (renewal triggers billing); `auto_renew` undefined | The core engine is unspecified | Critical | A renewal is an action that appends period(s) plus an invoice in one transaction. `renewal_mode` manual (P0) or automatic (P1). | P0 |
| Domain › Billing cycles | Both "SubscriptionCycle" and "Billing Cycle" exist | Duplicated and undecided | Critical | `subscription_periods` table (sequence, `starts_on`, inclusive `ends_on`, price, `invoice_id`, source) | P0 |
| Domain › Price snapshot | Price on the plan only | Plan edits could alter history; no negotiated prices | High | Subscription holds `price_minor` plus `price_overridden`. Periods and invoices store actual amounts. Renewal uses the plan price unless overridden. | P0 |
| Domain › Plan intervals | 6 named intervals plus count plus "duration" | Duplicated; "duration" undefined | Medium | `interval_unit` (day, week, month, year) plus `interval_count`. `term_periods` for fixed terms. | P0 |
| Domain › 30-day plans | Not explicit | Common local practice (validate) | Medium | `day` unit with count 30 | P0 |
| Domain › Fixed-term plans | Only a "COMPLETED" state | No rule | Medium | `term_periods`; derived phase `completed`, which does not count as churn | P0 |
| Domain › Customer name | `first_name` / `last_name` | Fits neither Ethiopian names nor organisations | High | One `name` field | P0 |
| Domain › Customer status | `status` plus a lifecycle (§26) | Ambiguous meaning | Medium | Record status (active, archived). Membership phase is derived from subscriptions. | P0 |
| Domain › Joined date | Absent | Imports would distort growth metrics | Medium | `joined_on`, separate from `created_at` | P0 |
| Domain › Multiple subscriptions | §28 profile assumes one | Inconsistent with §64 | Medium | Allowed; the profile lists all | P0 |
| Domain › Plan change | Listed as an edge case only | No rule | Medium | Change at renewal. Immediate change = cancel plus new. No proration. | P0 |
| Domain › Redundant entities | PlanPrice, PlanFeature, Reminder, AttendanceRecord, NotificationTemplate | Undefined or duplicated | Medium | Drop or merge (audit §7.2) | Remove |
| Domain › User–tenant model | Role and Permission entities | No membership or invitation model | High | `memberships` (tenant, user, role, status) plus `invitations` | P0 |
| Domain › Numbering | Absent | Nothing to number documents with | High | `sequences` (tenant_id, name, next_value), row-locked | P0 |
| Domain › Usage | UsageEvent and AttendanceRecord | Duplicated; widens scope | Low | One informational `usage_events` table | P1 |
| Domain › Consumption plans | "Consume" (§1) | Undefined | Low | Visit-limited plans, only if validated | P2 |

## 4. Subscription lifecycle

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Lifecycle › State model | TRIAL / ACTIVE / DUE / OVERDUE / PAUSED / CANCELLED / EXPIRED / COMPLETED, plus RENEWAL_DUE and PAID (§57) | Mixes lifecycle with payment status; contradictory | Critical | Stored: active, paused, cancelled. Derived: scheduled, renewal_due, grace, expired, completed. "Overdue" is a separate flag. | P0 |
| Lifecycle › Cron dependence | Scheduler runs "subscription transitions" (§24) | Status correctness depends on cron | High | Time-based states are derived, so there is no transition job | P0 |
| Lifecycle › Grace | Defined on both plan and subscription | Precedence undefined | Medium | Tenant-level `grace_days` (default 3). Plan override in P1. | P0 / Decide (D5) |
| Lifecycle › Renewal after expiry | "Define behaviour" | No rule | High | Default: new period starts on the renewal date. Tenant option: continue from the last end. Anchor resets. | P0 / Decide (D5) |
| Lifecycle › Multi-period renewal | Absent | Advance payments would force credit logic | Medium | Renew × N with one invoice | P0 |
| Lifecycle › Pause / resume | Fields only | No rules | Medium | MVP: "Extend" (adjustment period). P1: pause, with an adjustment period on resume. | P0 / P1 |
| Lifecycle › Cancellation | `cancelled_at` | Immediate vs end-of-period undefined | Medium | Both: cancel now, or set `cancel_on` = period end | P0 |
| Lifecycle › Backdated / future start | Listed as edge cases | No rules | Medium | Backdating needs manager+ and warns if the period has already ended. Future start is derived as `scheduled`. | P0 |
| Lifecycle › Customer trials | TRIAL state | Conflated with the SaaS trial | Low | No customer trials; use a free plan or an extension | Remove |
| Lifecycle › "Covered until" | Not defined | Off-by-one ambiguity | Medium | Inclusive end = the day before the same date one interval later | Decide (D5) |

## 5. Billing and invoices

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Billing › Invoice states | DRAFT / ISSUED / PARTIALLY_PAID / PAID / OVERDUE / VOID | "Partially paid" and "overdue" can both be true | High | Stored: open, paid, void. Derived: partially_paid, overdue. | P0 |
| Billing › Creation timing | A separate "Generate invoice" step | Extra click; timing undefined | High | Invoices are created inside start, renew and charge actions. P1: automatic lead days. | P0 |
| Billing › Drafts | DRAFT state | Review step nobody needs | Low | Remove | Remove |
| Billing › Invoice without subscription | Unclear | Needed for one-off charges and opening balances | Medium | Nullable `subscription_id`; `kind` = standard or opening_balance | P0 |
| Billing › Numbering | Not specified | Collisions; numbers nobody can quote | High | Per-tenant sequences: C-, INV-, RCT- | P0 |
| Billing › Discounts | "Discount" | No rules | Medium | Fixed amount per invoice, manager+. Percentages converted once. | P0 |
| Billing › Tax | "Tax if enabled" | No rules; customers may expect fiscal invoices | High | No tax in the MVP (`tax_minor` = 0). Single rate in P1. | Decide (D3) / P1 |
| Billing › Fiscal status | Not considered | Documents may be mistaken for tax invoices | High | Label as payment record and receipt until legal advice says otherwise | Decide (D3) |
| Billing › Write-off | Absent | Uncollectible debt lingers | Low | Void with reason "uncollectible" | P0 |
| Billing › Refunds | Not explicit | Undefined | Medium | MVP: void only. Refunds and credit in P1 if pilots need them. | P1 |
| Billing › Credit notes / ledgers | Absent | Risk of drifting into accounting | Medium | Never build | Remove |

## 6. Payments

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Payments › Immutability | "Payment edited" is an audited action | Payments can change silently | High | Payments are immutable. Corrections: void with reason, then re-record. | P0 |
| Payments › Allocation | Schema "must not prevent" it | No MVP rules | Medium | Allocations must equal the payment amount, same customer only, oldest invoice first by default | P0 |
| Payments › Overpayment | "Future" | Undefined | Medium | Blocked in the MVP (offer a multi-period renewal instead). Credit in P1. | P0 / P1 |
| Payments › Double submit | Absent | Duplicate payments | High | Client idempotency key, unique per tenant. Submit button disabled while processing. | P0 |
| Payments › Duplicate reference | Absent | The same transfer recorded twice | Medium | Warn when the same method and reference already exist | P0 |
| Payments › Methods | Fixed list described as "configurable" | Data shape undefined | Low | Tenant table (name plus `kind`), seeded per country | P0 |
| Payments › Receipts | "Should have" | Proof of payment and cash control are missing | High | Print view, receipt number, balance after payment | P0 |
| Payments › Credit given by staff | Not considered | Staff can extend credit | Medium | Starting or renewing with zero payment requires manager+ | P0 |
| Payments › Online integrations | "Later" | Per-merchant credentials not considered | Low | Gateway accounts, payment intents, webhooks unique by event ID | P2 |
| Payments › Webhooks / reconciliation | Listed as edge cases | Premature | Low | Record the principle only | P2 |

## 7. Money

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Money › Representation | Integer minor units recommended | Currency exponent undefined | High | Exponent registry (UGX and RWF have 0 decimals) | P0 |
| Money › Input parsing | Not covered | Floating-point bugs in JavaScript | High | Parse decimal strings on the server; never `parseFloat × 100` | P0 |
| Money › Rounding | "Document rounding rules" | No rule given | Medium | Round half away from zero to the minor unit, once per computed amount | P0 |
| Money › Currency per tenant | Plans carry their own currency | Reports would mix currencies | Medium | One currency per tenant, fixed after the first financial record | P0 / Decide (D10) |
| Money › Display | Not covered | ETB symbol undecided | Low | Intl / NumberFormatter; choose "ETB" or "Br" | P0 |

## 8. Date and time

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Time › Storage | "Consistent timestamps" | No rules | High | UTC timestamps (`*_at`), DATE business dates (`*_on`), no database time functions | P0 |
| Time › "Today" | "Use the tenant timezone" | Nothing enforces it | High | `TenantClock::today()`. An architecture test forbids `now()` / `today()` in domain code. | P0 |
| Time › Month-end / leap years | "Define behaviour" | No rules | High | Anchor-day clamping, with examples in DOMAIN-MODEL | P0 |
| Time › Ethiopian calendar | Absent | A likely local expectation (validate) | High | Store Gregorian. Ethiopian-calendar display in P1 per D6; convert EC dates on import if needed. | Decide (D6) |
| Time › Reminder timing | Configurable windows incl. 30 and 14 days | Meaningless for short plans; risk of night-time sends | Medium | Short defaults; skip offsets longer than the interval; tenant-local send window; skip stale reminders | P0 |
| Time › Timezone boundary | Listed | No rule | Medium | Form default dates come from the tenant clock | P0 |

## 9. Follow-up and notifications

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Follow-up › Today screen | A metrics dashboard | No action list | High | Today: renewal due, grace, recently expired, overdue, with one-tap actions | P0 |
| Notifications › Channel fit | Email-only automation in the MVP | Email may not reach Ethiopian end customers (validate) | Critical | P0: email plus assisted messaging. P1: SMS. | P0 / P1 |
| Notifications › Assisted messaging | Absent | Free way to reach customers is missed | High | `tel:`, `sms:`, wa.me, Telegram share and copy, each logged as "prepared" | P0 |
| Notifications › Template injection | Template variables listed | Rendering tenant text with Blade would allow code execution | Critical | Whitelisted `{{variable}}` substitution only, with escaping | P0 |
| Notifications › Template language | English locale | Customers may need Amharic | High | Tenant-editable template text from P0 | P0 |
| Notifications › Idempotency | "Idempotent" | No mechanism | High | `message_log` with `UNIQUE dedupe_key`, inserted before sending | P0 |
| Notifications › Retries / visibility | "Retryable, logged" | No policy | Medium | 3 tries, backoff 1 / 5 / 15 minutes; failed status shown on the timeline | P0 |
| Notifications › Opt-out | Absent | No consent handling | Medium | Opt-out flag in P0; unsubscribe link in P1 | P0 / P1 |
| Notifications › Abuse | Absent | Spam sent through tenants; SMS cost | High | Verified email, daily caps, trial caps, "Business via FufaFlow" sender, SPF/DKIM/DMARC | P0 |
| Notifications › SMS | "Should have" | Provider choice, sender-ID lead time, UCS-2 cost | High | One provider in P1. Start procurement now. Meter usage per tenant. | P1 |
| Notifications › Telegram / WhatsApp | "Later" | Telegram bots need opt-in; WhatsApp API cost | Low | P2. A Telegram share link is already part of P0 assisted messaging. | P2 |
| Notifications › Framework | Custom Service → Channel → Provider layer | Duplicates Laravel | Medium | Laravel Notifications plus a custom `SmsChannel` (P1) | Remove |
| Notifications › Owner digest | Absent | No daily habit loop | Low | Daily digest email | P1 |

## 10. Multi-tenancy

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Tenancy › Mechanism | "Evaluate…" | Undecided | Critical | Fail-closed `BelongsToTenant` global scope, auto-fill of `tenant_id`, `Tenancy::run()` / `bypass()` | P0 |
| Tenancy › Validation rules | Not addressed | `exists` / `unique` rules bypass global scopes, enabling IDOR | Critical | `TenantRule` helpers. A CI grep forbids raw rule strings. | P0 |
| Tenancy › Query-builder reports | Not addressed | `DB::table` bypasses scopes | High | Allowed only in `App\Reports`, with an explicit tenant filter and two-tenant tests | P0 |
| Tenancy › Database-level guard | Generic "tenant-aware constraints" | No composite foreign keys | Medium | Composite `(tenant_id, x_id)` foreign keys on financial tables | P0 |
| Tenancy › Jobs and commands | "Tenant-aware" | No rules | High | Job middleware sets and clears tenant context. Scheduled loops run per tenant with try/catch. | P0 |
| Tenancy › Cache | Not addressed | Cross-tenant cache leaks | Medium | Prefix keys with `tenant:{id}:` | P0 |
| Tenancy › Resolution | Not addressed | Subdomains need wildcard SSL | High | Resolve the tenant from the session membership; no subdomains | P0 |
| Tenancy › Platform bypass | "Manage tenants" | No bypass design | High | Separate route group, 2FA, explicit bypass, read-only, audited | P0 |
| Tenancy › Isolation testing | One example scenario | Not systematic | High | Data-driven harness covering every tenant route | P0 |

## 11. Authorization

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Authz › Role matrix | Roles listed, permissions undefined | Implementers will guess | High | Fixed matrix in code for owner, manager and staff (audit §10.3) | P0 |
| Authz › Custom roles | "Permissions" | Over-engineering | Low | No permission tables | Remove |
| Authz › Revenue visibility | Not considered | Owners may want to hide revenue from staff | Medium | Staff see only their own collections | P0 |
| Authz › Last owner / deactivation | Not considered | Owner lockout; ex-staff keep access | Medium | Last-owner protection; deactivation ends sessions | P0 |
| Authz › Shared logins | Not considered | Shared logins defeat the audit trail | Medium | Per-staff accounts; validate how staff actually work | P0 |
| Authz › Branch permissions | Owner "manages branches" | Premature | Low | Build together with branches | P1 |

## 12. Security

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Security › IDOR catalogue | "Never trust IDs" | Specific attack vectors missing | Critical | Add the audit §10.2 checklist to ARCHITECTURE.md, with tests | P0 |
| Security › Mass assignment | Not covered | `tenant_id`, role, status and amounts are assignable | High | Explicit `$fillable`; actions set sensitive fields; strict mode outside production | P0 |
| Security › Inertia props | Not covered | Raw models end up in page HTML | High | Shape props with Resources or arrays | P0 |
| Security › Sort / filter input | Not covered | `orderBy` injection | Medium | Allow-lists | P0 |
| Security › CSV injection | Not covered | Formulas execute in Excel | Medium | Escape leading `=`, `+`, `-`, `@`, tab and CR | P0 |
| Security › Admin 2FA | Not covered | A compromised admin exposes the whole platform | High | Mandatory TOTP for platform admins | P0 |
| Security › Owner 2FA | Not covered | Cash and data protection | Low | Optional for owners | P1 |
| Security › Rate limits | "Rate limiting" | No numbers | Medium | Limits for login, registration, password reset, invites, export and messaging | P0 |
| Security › Headers | "Secure headers" | No specifics | Medium | CSP with Vite nonce, HSTS, frame deny, nosniff, Referrer-Policy | P0 |
| Security › Account enumeration | Not covered | Attackers can discover which emails exist | Low | Generic login and reset messages | P0 |
| Security › Logging | "Review sensitive logs" | No rule | Medium | Never log bodies or secrets; mask phones and emails | P0 |
| Security › APP_KEY | Not covered | Losing it breaks sessions and encrypted data | Medium | Keep a copy in a password manager | P0 |
| Security › File uploads | Generic list | Attack surface | Medium | No uploads in the MVP; strict rules in P1 | P1 |
| Security › Dependencies | Not covered | Vulnerable packages | Low | `composer audit` / `npm audit` in CI; minimal dependencies | P0 |

## 13. Privacy and compliance

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Privacy › Applicable law | Absent | Obligations under Proclamation 1321/2024 unknown | Critical | Legal review: processor vs controller, registration, DPO | Decide (D2) |
| Privacy › Data residency | Absent | Possible localisation rules affect host, backups and providers | Critical | Get legal advice before procuring hosting | Decide (D2) |
| Privacy › ToS / Privacy Policy / DPA | Absent | Needed before pilots | High | Draft with counsel | Pre-code |
| Privacy › Retention | Absent | Logs and backups kept forever | Medium | Retention schedule plus pruning jobs | Decide (D8) / P0 |
| Privacy › Data-subject rights | Absent | Erasure and access requests | Medium | Anonymise customer records; per-customer export | P1 |
| Privacy › Consent / marketing | Absent | Risk of broadcast misuse | Medium | Transactional messages only; no broadcasts | P0 |
| Privacy › Breach process | Absent | Notification duty unknown | Medium | Runbook that includes a legal step | P0 |
| Privacy › Sensitive data | "Don't collect unnecessary data" | Gyms may store health notes | Low | In-app guidance: no health or ID data in notes | P0 |

## 14. Laravel architecture

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Laravel › Module structure | 14 `Domain` modules | Ceremony; splits one transaction boundary | Medium | Standard folders plus Actions plus Billing calculators | P0 |
| Laravel › Business-logic home | "Clear domain/service boundary" | Pattern unspecified | High | Transactional Action classes plus pure calculators | P0 |
| Laravel › Repositories / interfaces | Offered as an option | Unnecessary | Medium | Use Eloquent and scopes. Only one interface: `SmsGateway` (P1). | Remove |
| Laravel › Events / listeners | "Where useful" | Hidden control flow | Low | Explicit calls; framework events only | P0 |
| Laravel › Enums | "Explicit enums / value objects" | No storage strategy | Low | Backed enums stored as VARCHAR | P0 |
| Laravel › Package policy | "No unnecessary packages" | No policy | Medium | Allow / deny list; each new dependency needs a DECISIONS entry | P0 |
| Laravel › Transactions | "Transactions" | Job dispatch timing undefined | Medium | `DB::transaction` in actions; queue `after_commit` | P0 |

## 15. React and Inertia

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Frontend › Foundation | Stack listed | Official starter kit not used | Medium | Official Laravel React starter kit | P0 |
| Frontend › TanStack Query | "Only where it adds value" | Redundant with Inertia | Medium | Remove | Remove |
| Frontend › React Hook Form + Zod | Listed | Duplicates server validation | Medium | Inertia forms plus Form Requests | Remove |
| Frontend › Navigation | 9 items | Too wide; no Today screen | Medium | Today, Customers, Payments, Plans, Reports, Settings | P0 |
| Frontend › Dates | Not covered | UTC parsing causes off-by-one-day errors | Medium | Keep dates as strings; format with Intl in the tenant timezone | P0 |
| Frontend › i18n and fonts | Translation files | No Ethiopic font; hard-coded strings | Medium | `t()` everywhere; bundle Noto Sans Ethiopic | P0 |
| Frontend › Mobile | "Mobile-friendly" | No concrete target | Medium | Key screens work at 360 px | P0 |
| Frontend › Charts | Implied | Bundle weight; low early value | Low | Numbers only in the MVP | P1 |
| Frontend › Error pages / 419 | Not covered | Broken experience when a session expires | Low | Inertia error pages; 419 reload prompt | P0 |

## 16. Database

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| DB › Index plan | "Indexes" | None specified | High | Composite indexes led by `tenant_id` (audit §8.3) | P0 |
| DB › Over-indexing | n/a | Risk of indexes nobody uses | Low | No standalone `tenant_id`, status or `created_at` indexes | P0 |
| DB › Deletion | Not specified | Soft deletes clash with unique constraints | Medium | Archive with `archived_at`; financial rows never deleted; anonymise on erasure | P0 |
| DB › Enum strategy | Not specified | MySQL ENUM changes need table alters | Low | VARCHAR plus PHP enums | P0 |
| DB › Charset | Not specified | MySQL/MariaDB differences; Ge'ez script | Medium | Explicit `utf8mb4` with `utf8mb4_unicode_ci` | P0 |
| DB › Triggers / procedures | Not specified | Shared hosts often lack the privileges | Low | Forbidden | P0 |
| DB › Engine parity | "Portable" | Local engine differs from production | Medium | CI runs the production engine and version | P0 |
| DB › Pagination | "Required" | Strategy unspecified | Low | Offset pagination for lists; cursor pagination for logs (P1) | P0 |

## 17. Reports and metrics

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Metrics › Definitions | Three lists (§27, §55, §78), none defined | Cannot be calculated consistently | High | Metric dictionary (audit §7.10) | P0 / P1 |
| Metrics › "Expected revenue" | Dashboard tile | Undefined | Medium | Replace with "Invoices due" plus "Up for renewal" | P0 |
| Metrics › "Expired" count | Dashboard tile | Grows forever | Low | "Expired in the last 30 days, not renewed" | P0 |
| Metrics › MRR | KPI | Jargon for SMB owners; distorted by fixed terms | Low | Call it "Monthly run-rate" | P1 |
| Metrics › Renewal rate / churn | KPI | Needs period history | Medium | Define on persisted periods | P1 |
| Metrics › Lifetime / ARPU | KPI | Not enough history | Low | ARPU in P1 with labelled variants; lifetime in P2 | P2 |
| Reports › Scheduled reports | §24 | No user need | Low | Remove | Remove |
| Reports › MVP set | 9 reports | Too many | Medium | Collections, receivables (aging), renewals and expiries, subscriptions by plan | P0 |

## 18. Import and export

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Import › Customers | "Should have" | Without it onboarding is blocked | Critical | CSV including paid-through date and amount owed; preview before commit; one transaction; up to 5,000 rows | P0 |
| Import › Encoding / dates | Not covered | Amharic text breaks in CSV; DD/MM vs MM/DD ambiguity | Medium | UTF-8 check, BOM handling, explicit date-format choice, EC dates if D6 requires | P0 |
| Import › History | Not covered | Complexity | Low | Import opening balances only | Remove |
| Export › Core data | "Should have" | Trust and portability | High | CSV (UTF-8 with BOM): customers, subscriptions, invoices, payments | P0 |
| Export › Full tenant | Not covered | Needed for offboarding | Medium | Zip of all CSVs | P1 |
| Import › .xlsx | Not covered | Many users work in Excel | Low | Streaming .xlsx reader | P1 |

## 19. User experience

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| UX › Payment collection | Multi-step: invoice, then payment | Friction at the front desk | Critical | One "Collect payment" action: customer, subscription, invoice, payment and receipt together | P0 |
| UX › Onboarding | Not specified | No templates; no time target | High | ≤ 5 screens and ≤ 10 minutes to the first receipt; plan templates by business type | P0 |
| UX › Terminology | "Customer" everywhere | Gyms say "member", schools say "student" | Low | Configurable label | P1 |
| UX › Phone numbers | Not covered | Search, duplicate detection and SMS fail | High | Normalise to E.164, defaulting to the tenant's country | P0 |
| UX › Receipt printing | "Should have" | Thermal printers not considered | Low | A4 print in P0; 80 mm in P1 (validate) | P0 / P1 |
| UX › Customer profile | Single-plan view | Multiple subscriptions not shown | Medium | List all subscriptions plus a timeline | P0 |

## 20. Background jobs and scheduler

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Jobs › `proc_open` | Not considered | Scheduler `command()` and Composer fail without it | High | Check in `hosting-check`; schedule in-process; separate cron line for the worker | P0 |
| Jobs › Cron interval | "Supported interval" | Some hosts allow only 5 or 15 minutes | Medium | Catch-up design; schedule on boundaries the host supports | P0 |
| Jobs › Timeouts | Not considered | `pcntl` often missing, so job timeouts aren't enforced | Medium | Explicit HTTP and SMTP timeouts | P0 |
| Jobs › Worker command | "Cron-driven worker" | Command not specified | Medium | `queue:work --stop-when-empty --max-time=50` every minute | P0 |
| Jobs › Heartbeat | Not covered | Cron can fail silently | High | Heartbeat plus `/health` plus an external monitor | P0 |
| Jobs › Catch-up | Not covered | Reminders lost after an outage | Medium | Process everything due as of now; skip stale items | P0 |
| Jobs › Long-running work | Imports and reports queued | Shared-hosting limits | Medium | Run synchronously with caps in the MVP | P0 |

## 21. DirectAdmin/cPanel deployment

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Deploy › PHP CLI path | Mentioned briefly (§49) | CLI and web PHP versions often differ | High | Use the absolute PHP 8.3 CLI path in cron and docs | P0 |
| Deploy › Document root | Fallback left to "design" | No concrete pattern | High | Point a subdomain's docroot at `public/`; document the fallback | P0 |
| Deploy › Build location | Composer runs on the server | Composer may be absent | High | CI builds `vendor/` and assets; deploy an artifact | P0 |
| Deploy › SSH | "Limited SSH access" | Migrations need a shell | High | Require jailed SSH | Pre-code |
| Deploy › Rollback | Not covered | No safe rollback | High | Release directories, expand/contract migrations, pre-deploy dump | P0 |
| Deploy › Destructive commands | Not covered | `migrate:fresh` could run in production | Medium | `DB::prohibitDestructiveCommands()` | P0 |
| Deploy › `storage:link` | "Where needed" | Hosts may restrict symlinks | Low | Not needed in the MVP | Remove |
| Deploy › Staging | Not covered | Deploys go out untested | Medium | Staging subdomain and database on the same host | P0 |
| Deploy › Email | Environment variables only | Host send limits; poor deliverability | High | Transactional relay plus SPF/DKIM/DMARC | P0 |
| Deploy › Quotas | Not covered | Disk or inode exhaustion | Medium | Database cache and sessions; log rotation; pruning | P0 |
| Deploy › Hosting procurement | "May have" list | Capabilities never verified | High | Must-have checklist plus a `hosting-check` command | Pre-code / P0 |
| Deploy › VPS migration triggers | "Must remain possible" | No signals defined | Low | Define signals (queue latency, resource limits) | P1 |

## 22. Operations

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Ops › Admin console | Capabilities listed | No specification | High | Tenant list, status, trial and paid-until, health, failed jobs | P0 |
| Ops › Impersonation | Not covered | Support staff need it | Medium | Audited, visible banner, time-limited | P1 |
| Ops › Monitoring | Health endpoint | Nothing watches it | High | Uptime monitor on `/up` and `/health` | P0 |
| Ops › Error visibility | "Error logging" | Nobody reads the logs | Medium | Daily error digest, or an error tracker once D2 allows | P0 |
| Ops › Backups | "Document" | No encryption, location or restore drill | Critical | Nightly encrypted dumps, stored off-server per D2, with retention and a monthly restore drill | P0 |
| Ops › Per-tenant restore | Not covered | Hard in a shared database | Medium | Runbook in P0; script in P1 | P0 / P1 |
| Ops › Runbooks | Troubleshooting doc only | No incident procedures | Medium | Site down, cron, queue, mail, quota, data leak, bad deploy | P0 |
| Ops › Assisted onboarding | Not covered | SMBs need help importing | Medium | Onboarding playbook; validate demand | P0 |
| Ops › Data fixes | "Never modify schema manually" | Manual data edits in production | Low | Audited artisan commands only | P0 |
| Ops › Framework upgrades | Not covered | Laravel 13 security support ends Q1 2028 | Low | Schedule an annual upgrade | P1 |

## 23. Testing

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Tests › Clock control | Not covered | Date tests become flaky | High | `travelTo` plus `TenantClock`; architecture test | P0 |
| Tests › Date maths | "Test renewal calculations" | No cases listed | High | 30 or more table-driven cases | P0 |
| Tests › Tenant isolation | One scenario | Not systematic | High | Harness driven by the route list | P0 |
| Tests › Idempotency | "Notification idempotency" | Only partly covered | Medium | Scheduler run twice, double-submitted payment, double-submitted renewal | P0 |
| Tests › End-to-end | Full flow including "Generate invoice" | Tests an outdated flow | Low | Golden path through "Collect payment" (Pest browser, CI only) | P0 |
| Tests › Deployment | Detailed checklist (§75) | Real host only tested at the end | Medium | Walking skeleton on the host in week 1; deploy each release to staging | P0 |
| Tests › Performance | Not covered | Behaviour at scale unknown | Medium | Seed a large tenant; key queries under 200 ms | P0 |

## 24. Documentation and process

| Area | Current | Gap | Severity | Recommendation | MVP? |
|---|---|---|---|---|---|
| Docs › Pre-code set | About 25 documents plus 7 ADRs | Delays the first pilot | High | 7 documents, short ADRs and a CLAUDE.md | Pre-code |
| Docs › Conflicting lists | §54, §84 and §85 differ | Confusing | Low | One list | Pre-code |
| Docs › CLAUDE.md | Absent | AI coding agents get no project rules | Medium | Commands, conventions, tenancy/money/date rules, Definition of Done | Pre-code |
| Docs › Decisions log | Absent | Decisions scattered across documents | Medium | DECISIONS.md with D1–Dn | Pre-code |
| Process › Walking skeleton | Deployment tested only at the end (§75) | Hosting risk retired too late | High | Deploy a skeleton to the real host first | P0 |
| Process › File naming | "FUFUFLOW" | The product is FufaFlow | Low | Rename all files in one commit | Pre-code |
