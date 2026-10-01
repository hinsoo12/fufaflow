# FufaFlow Master Prompt — Red-Team Audit

| | |
|---|---|
| **Document reviewed** | `docs/product/FUFUFLOW-MASTER-PROMPT.md` (3,006 lines, commit `fa4c217`) |
| **Review date** | 2026-10-01 |
| **Review stance** | Product architecture, business analysis, Laravel, React/Inertia, SaaS, database, security, DevOps/hosting, QA, product management |
| **Companion documents** | `FUFUFLOW-REQUIREMENTS-GAP-MATRIX.md` (every gap, one row each) · `FUFUFLOW-MASTER-PROMPT-V2-DRAFT.md` (complete revised prompt) |
| **Verdict** | **NOT READY** for implementation as written. The V2 draft fixes most issues. The rest need product and legal decisions (§13.4). |

Section references such as "§17" point to sections of the original master prompt unless stated otherwise.

---

## 0. Repository inspection

Nothing in the repository was changed by this review apart from adding the three `docs/review/` files.

| Item inspected | Finding |
|---|---|
| Files in repository | Only `docs/product/FUFUFLOW-MASTER-PROMPT.md` |
| Git | Branch `main`, one commit (`fa4c217`, "pushing master prompt of fufa flow product", 2026-10-01). Remote `origin` → `github.com/hinsoo12/fufaflow`. Clean working tree. |
| `composer.json` | **Absent**: no Laravel application and no PHP dependencies. |
| Laravel version | None installed. Laravel 13 was released on 2026-03-17 and requires PHP ≥ 8.3, so the spec's claim (§3) is accurate. Bug fixes run to Q3 2027 and security fixes to Q1 2028, so an upgrade to Laravel 14 will fall within about a year of launch. |
| `package.json` / React / Inertia | **Absent** |
| `vite.config.*` | **Absent** |
| Migrations / routes / config | **Absent** |
| `.env.example` | **Absent** |
| `.gitignore` | **Absent**. It must exist before anyone creates a `.env`. The official starter kit provides one. |
| `CLAUDE.md` | **Absent** |
| Project `.claude/` directory | **Absent** |
| Deployment files (`.htaccess`, CI workflows, deploy scripts, Docker) | **Absent** |
| Other documentation | None |
| Naming | The file name says **FUFUFLOW**, but the product is **FufaFlow**. The review files keep the requested spelling for consistency. Rename everything to `FUFAFLOW-*` in one commit later. |

**Implications**

1. This is a greenfield project. No existing code constrains any decision, so Phase 0 ("inspect repository") in the spec takes minutes, not a phase.
2. Because nothing exists yet, the cheapest moment to settle the domain model (§7) and tenancy design (§10.1) is **now**.
3. The first engineering deliverable should be a **walking skeleton deployed to the real DirectAdmin/cPanel host**: starter kit, tenancy skeleton, cron heartbeat and deploy script. This retires the biggest technical risk (the hosting) before any feature work starts.

---

## 1. Executive summary

**What the spec gets right.** These choices should be kept:

- **Macro-architecture:** a modular monolith, Laravel + Inertia + React rather than a separate SPA/API, and a shared database with `tenant_id`.
- **Hosting awareness:** it takes shared hosting seriously, with no Redis, Supervisor, or Node at runtime, a database queue, and cron.
- **Non-goals:** an explicit list of things not to build (§10).
- **Core questions:** a clear statement of what the owner must be able to answer (§7, §86).
- **Engineering hygiene:** integer money, a tenant timezone, vertical slices, and a Definition of Done.

**What is wrong.** The document is a **requirements catalogue, not a decided specification**. Nearly every hard decision is handed to the implementer ("evaluate", "determine", "document the decision"). Where it does decide, it often contradicts itself. Building from it as written would produce a broad, generic, over-documented system whose core loop (*renew → collect → remind*) is still undefined. The eleven critical problems:

1. **No beachhead.** Two incompatible business models are treated as one: prepaid B2C memberships and invoiced B2B service contracts (C1).
2. **"Renewal" is never defined.** Billing cycle, SubscriptionCycle, renewal, `auto_renew` and next cycle overlap, so the engine has no specification (C2).
3. **State models are contradictory and mix lifecycle with payment status.** An invoice can be "PARTIALLY_PAID" and "OVERDUE" at once, and "PAID" appears as a subscription state (C3).
4. **FufaFlow's own SaaS domain is missing**, and its vocabulary (Plan, Subscription, Trial, Feature) collides with the core domain (C4).
5. **The messaging plan may not fit the market and creates an abuse risk.** Automation is email-only for Ethiopian end customers, and FufaFlow becomes a sender of tenant-written messages (C5).
6. **The tenant isolation mechanism is deferred.** Known Laravel leak paths (validation rules, query-builder reports, jobs, cache keys) are not addressed (C6).
7. **The hosting choice has not been checked against Ethiopia's data-protection law** (Proclamation 1321/2024), which reportedly includes data-localisation and cross-border-transfer rules (C7).
8. **The prepaid flow is forced through accounting steps**, and there is no import of existing customers with a "paid-through" date, so onboarding a real gym is impractical (C8).
9. **Financial integrity rules are missing**: no price snapshot, payments are editable, no document numbering, no double-submit protection (C9).
10. **Local conventions are absent despite the product being Ethiopia-first**: Ethiopian calendar, 30-day "months", patronymic names, phone formats, Ge'ez script (C10).
11. **Scope and process bloat**: 21 must-haves, about 25 documents before code, 14 domain modules, and three redundant frontend libraries (C11).

**What to do next (in order):**

1. Make decisions D1–D7 (§13.4), above all the beachhead, hosting location and legal position, and tax positioning.
2. Run 8–12 discovery interviews and collect 5 real customer spreadsheets (§13.5).
3. Adopt the V2 draft, edited with those decisions, as the master prompt.
4. Build the walking skeleton on the real host.
5. Build the P0 slices in the order given in the V2 draft (§31).

**Scores:** overall readiness **4/10**. Full scorecard in §14.

---

## 2. Critical problems

Each problem lists the evidence in the spec, the impact, the recommendation, and why.

### C1 — No beachhead; two business models are conflated

- **Evidence.** §8 lists 19+ segments across "membership" and "recurring service" businesses plus "future" ones. §79 validates six segments in parallel. §62–§63 say "the same core engine must support both" but never define what differs between them.
- **Impact.**
  - **Membership businesses** (gyms, studios, training centres, coworking) are B2C and prepaid. They have hundreds of customers, small tickets, and walk-in cash or mobile-money payments. Their pain is *knowing who has expired or owes, and collecting at the front desk*.
  - **Recurring-service businesses** (cleaning, security, maintenance, IT support) are B2B. They have few customers (often under 50) and large tickets. They often bill monthly on payment terms and are expected to issue formal, often VAT, invoices. Their pain is *receivables and contract invoicing*, which sits right next to accounting.
  - One MVP cannot excel at both. Trying produces a lowest-common-denominator UX and pulls invoicing toward accounting (see C9 and §12.1).
- **Recommendation.** Launch with **membership and prepaid businesses**: gyms and fitness studios, training centres, coworking. Keep the engine generic, with no vertical-specific tables. Write defaults, onboarding templates, acceptance criteria and pilots for this segment only. Serve B2B services through the same engine in P1 (automatic renewal invoicing, payment terms, tax line). **Decision D1.**
- **Why.** This segment has the highest frequency of the core pain (renewals every week), the simplest billing model ("pay → covered until a date"), and likely no fiscal-invoice expectation for individual customers (verify). Its incumbent tools are notebooks and Excel, and the owner usually decides alone, which shortens sales cycles.

### C2 — "Renewal" is undefined, so the engine is undefined

- **Evidence.**
  - §1 and §9 describe a flow "Subscription → Billing Cycle → Invoice → Payment → Renewal → Next Cycle".
  - §14 lists `SubscriptionCycle`, while "Billing Cycle" appears as a separate concept.
  - §17 has `auto_renew`, `next_renewal_date` and `current_period_end` with no semantics.
  - §21 describes a "renewal engine" that *detects* states but never says what a renewal *does*.
  - §62 makes payment trigger renewal ("Ahmed pays again → Subscription renewed"), but §63 makes renewal trigger billing ("Renew → Next billing cycle").
- **Impact.** Invoice timing, overdue and expiry rules, reminders and every metric depend on this definition. If it is left to the implementer, it will be decided implicitly in controller code, which is exactly what §17 forbids.
- **Recommendation.**
  1. Persist one row per covered period in `subscription_periods`.
  2. Make a **renewal** an *action* that appends one or more periods plus an invoice in a single transaction. It is not an entity and not a module.
  3. Add `renewal_mode`:
     - **manual** (P0, the prepaid default): nothing happens automatically. Staff renew when the customer pays. After the grace period the subscription is *expired*.
     - **automatic** (P1, for B2B): the system appends the next period and invoice before the period starts. It never auto-expires, and unpaid invoices become overdue.

  Full rules are in §7.4.

### C3 — State models mix lifecycle with payment status and contradict each other

- **Evidence.**
  - §17 lists TRIAL, ACTIVE, DUE, OVERDUE, PAUSED, CANCELLED, EXPIRED, COMPLETED.
  - §57 adds RENEWAL_DUE and **PAID** as subscription states.
  - §18 lists invoice states DRAFT, ISSUED, PARTIALLY_PAID, PAID, OVERDUE, VOID.
- **Impact.**
  - An invoice can be partially paid **and** overdue.
  - A subscription can be active **and** have an overdue invoice from last period.
  - A single status column cannot hold both conditions. The result is duplicated `if/else` logic and scheduler jobs that flip statuses and race against user actions. On shared hosting, every time-driven status flip also depends on cron being healthy.
- **Recommendation.** Adopt one principle: **store decisions, derive time.**
  - **Stored:** only states changed by a person or by money (subscription `active | paused | cancelled`; invoice `open | paid | void`; payment `recorded | void`).
  - **Derived:** everything that changes merely because time passes (scheduled, renewal due, in grace, expired, completed, overdue, partially paid), computed by one resolver with ordered rules.

  Correctness then no longer depends on cron. See §7.4–§7.5.

### C4 — FufaFlow's own SaaS domain is missing, and its vocabulary collides with the core domain

- **Evidence.**
  - §11: the platform admin can "manage FufaFlow plans" and "suspend tenants".
  - §80: tiers Starter/Business/Pro, plus add-ons (SMS, branches, storage).
  - §17: a `TRIAL` subscription state. §14: a `PlanFeature` entity.
  - Nothing defines tenant account status, trial, limits, or what suspension does.
- **Impact.** Developers will either reuse `Plan`, `Subscription` and `trial` for both domains, or bolt SaaS billing onto tenant tables later. Nobody knows what a suspended tenant can do, or whether its customers' reminders stop, which will turn into support incidents. Add-ons that cost money (SMS) cannot be sold without metering.
- **Recommendation.**
  - **Name the two domains now.** The **tenant domain** owns `Plan`, `Subscription`, `Invoice`, `Payment`. The **platform domain** uses `Tenant` account fields (`status`, `trial_ends_at`, `paid_until`, `platform_plan`) and, later, `Platform*` classes under `App\Platform`.
  - **MVP has no automated SaaS billing.** Fufa collects manually and an admin extends `paid_until`.
  - **Define suspension precisely:** read-only plus export, and all customer messaging stops.
  - **Put entitlements behind one service** that returns "unlimited" until pricing is validated.

  See §8.5.

### C5 — The messaging plan may not fit the market, and messaging is an abuse vector

- **Evidence.** §58 makes "Email notifications" a must-have, SMS a "should have", and Telegram/WhatsApp "later". The automation examples in §25 address end customers.
- **Impact.**
  - *Assumption to validate:* for B2C membership businesses in Ethiopia, phone calls, SMS and Telegram are the realistic ways to reach customers, and few gym members read email. If so, email-only automation means the headline promise ("keep renewals flowing") is barely felt at launch.
  - Any self-serve tenant can make FufaFlow send messages to arbitrary recipients with tenant-written text: spam or phishing that damages Fufa's sending reputation, plus SMS cost exposure.
- **Recommendation.**
  - **P0:**
    - the reminder engine (scheduling, deduplication, log) with an email channel;
    - **assisted messaging**: one tap from the Today list to call, SMS, WhatsApp or copy a pre-filled message;
    - tenant-editable message text, which lets owners write in Amharic without full UI localisation.
  - **P1:** one SMS gateway with per-tenant metering and caps. Start provider and sender-ID procurement now, because it has lead time.
  - **Guardrails:**
    - a verified owner email is required before any customer messaging;
    - per-tenant daily caps, with lower caps during trial;
    - sender shown as "Business via FufaFlow", with Reply-To set to the tenant.
  - **Cost note:** Ge'ez-script SMS uses UCS-2 encoding (70 characters per segment instead of 160), so Amharic SMS costs about 2–3× more per message. Price add-ons accordingly.

### C6 — Tenant isolation is deferred, and known leak paths are not addressed

- **Evidence.** §13 says "Evaluate whether Laravel global scopes, repositories/query objects, policies, service-layer checks, or a combination provides the safest design." §40 says "never trust IDs".
- **Impact.** This is the highest-severity risk class for a SaaS. Eloquent global scopes alone do **not** cover:
  - `exists` and `unique` validation rules, which use the query builder;
  - `DB::table()` report queries;
  - queued jobs and scheduled commands, which have no authenticated user;
  - cache keys;
  - file paths;
  - references to another customer *within the same tenant*.
- **Recommendation.** Decide now and enforce with tests (§10.1):
  - a fail-closed `BelongsToTenant` scope;
  - tenant-scoped validation-rule helpers;
  - composite `(tenant_id, id)` foreign keys on financial tables;
  - policies;
  - explicit `Tenancy::run()` and `Tenancy::bypass()`;
  - an automated isolation test harness.

### C7 — Hosting choice not checked against Ethiopian data-protection law

- **Evidence.** §2 and §6 choose DirectAdmin/cPanel shared hosting without saying where. §76 calls for off-site backups. Privacy law is not mentioned anywhere.
- **Facts.** Ethiopia's **Personal Data Protection Proclamation No. 1321/2024** was adopted in April 2024 and entered into force on 2024-07-24. The Ethiopian Communications Authority is the regulator. Secondary legal summaries report:
  - restrictions on transferring personal data to countries without adequate protection;
  - prior authorisation for cross-border transfer of *sensitive* data;
  - **data-localisation provisions**.
- **Impact.** If localisation applies to FufaFlow (as processor of its tenants' customer data), then each of these could be non-compliant:
  - a foreign shared host;
  - foreign off-site backups;
  - foreign SMTP, SMS or error-tracking providers.

  That could force a change of host *after* launch.
- **Recommendation.** Get Ethiopian legal advice **before procuring hosting** (decision D2). The architecture itself is portable. What changes is procurement, the backup destination, third-party providers, the terms of service and the data processing agreement (DPA).

### C8 — The prepaid workflow goes through accounting steps; existing customers cannot be onboarded

- **Evidence.**
  - §62 gym flow: assign plan → subscription created → invoice generated → record payment → invoice PAID → subscription ACTIVE.
  - §44 and §74 end-to-end tests include "Generate invoice".
  - Import/export is only a "should have" (§58).
- **Impact.** At a real front desk, "the member pays 2,000 → membership extended → receipt" happens in one motion. A five-screen invoice-then-payment flow loses to a notebook. A gym with 400 members in Excel will not type them in by hand, and without an import that carries a *paid-through date* and an *amount owed*, onboarding fails.
- **Recommendation.**
  - **P0: a single "Collect payment" action** that creates or renews the period, invoice, payment, allocation and receipt in one transaction on one screen.
  - **P0: CSV import** of customers with their current plan, paid-through date and optional amount owed (opening balance).

### C9 — Financial integrity rules are missing

- **Evidence.**
  - §32 audits "Payment edited", which implies payments can be edited.
  - §16 puts a price on the plan, but nothing snapshots it on the subscription.
  - There is no invoice or receipt numbering.
  - There are no rules for concurrency or double-submission.
  - "Tax if enabled" (§18) has no rules.
- **Impact.**
  - Editing a plan's price rewrites history.
  - Editable payments allow silent cash manipulation, plausibly a core owner fear (validate).
  - A double-click creates duplicate payments.
  - Numbering collisions can occur.
- **Recommendation.**
  - Snapshot price and interval on the subscription.
  - Make payments and invoices immutable: corrections are a void with a reason, then re-recording.
  - Use per-tenant sequences for customer, invoice and receipt numbers.
  - Lock invoice rows during allocation.
  - Put an idempotency key on the payment form.
  - Defer tax to P1 as one tenant-level rate (decision D3).

### C10 — Ethiopia-first, but local conventions are absent

- **Evidence.** §34 covers currency, timezone and locale only.
- **What is missing:**
  - **The Ethiopian calendar.** It has 13 months, and many SMBs keep records in it (validate).
  - **The "a month is 30 days" pricing practice** (validate).
  - **Ethiopian naming.** A given name plus father's name, with no family surname, so `first_name` and `last_name` do not fit.
  - **Phone formats.** `09…`, `07…`, `+251…`, `251…` all need normalising for search, duplicate detection and SMS.
  - **Ge'ez script** in the UI, receipts and SMS.
- **Recommendation.**
  - Store dates in the Gregorian calendar.
  - Add a `day` interval unit so 30-day plans work.
  - Use a single `name` field.
  - Normalise phones to E.164.
  - Bundle an Ethiopic font.
  - Decide whether to display Ethiopian-calendar dates (decision D6; default: P1 dual display).

### C11 — Scope and process bloat

- **Evidence.**
  - §58: 21 must-haves plus 6 should-haves.
  - §54, §84, §85: about 25 documents plus 7 ADRs before coding, in two lists that don't match.
  - §12: 14 domain modules.
  - §4: TanStack Query, React Hook Form and Zod alongside Inertia.
- **Impact.** Months of documents and scaffolding pass before the first pilot, and a small team carries a large maintenance surface.
- **Recommendation.**
  - Condense to 6–7 documents plus short ADRs.
  - Use standard Laravel structure with Actions.
  - Remove the redundant libraries.
  - Ship the walking skeleton first.

---

## 3. Contradictions

| # | Statement A | Statement B | Resolution |
|---|---|---|---|
| 1 | §9: Usage/Event comes *after* Renewal | §1 and §86: Usage sits *between* Payment and Reminder | Usage is orthogonal to billing; it is not a step in the flow. It is informational only (P1). |
| 2 | §1: customers "consume" plans | No usage-based or session-pack pricing anywhere | Consumption pricing (class packs) is P2. In MVP, usage never affects billing. |
| 3 | §17 subscription states (DUE, OVERDUE, TRIAL, COMPLETED) | §57 examples (RENEWAL_DUE, **PAID**) | One stored lifecycle plus derived conditions (§7.4). PAID is an invoice condition. |
| 4 | §18: invoice PARTIALLY_PAID and OVERDUE are separate states | Reality: an invoice can be both | Store `open/paid/void`; derive `partially_paid` and `overdue`. |
| 5 | §17: `auto_renew` flag | §62: renewal happens when the customer pays | `renewal_mode = manual` (P0) or `automatic` (P1), with defined semantics. |
| 6 | §17 stores both `current_period_end` and `next_renewal_date` | One is derivable from the other | Store only `current_period_ends_on`; the next start is that date + 1 day. |
| 7 | §16: Plan has `grace period`; §17: Subscription has `grace_period` | Neither says which wins, and the tenant has no setting | MVP: one tenant-level `grace_days`. Plan-level override is P1. |
| 8 | §16: Plan has `price` | §14: `PlanPrice` entity | One price on the plan, snapshotted on the subscription. Drop `PlanPrice`. |
| 9 | §16: Plan has `currency` | §34: tenant default currency, and reports sum revenue | One currency per tenant in MVP; every money row still stores `currency`. |
| 10 | §16: intervals are weekly/monthly/quarterly/semiannual/annual/custom | §16: `billing interval` + `billing interval count` + `duration` | `interval_unit (day/week/month/year)` + `interval_count`. "Duration" becomes `term_periods` (fixed-term). |
| 11 | §11 and §13: owner manages branches; tenant owns branches; search filters by branch (§31) | §58: branch support is a "should have" | Branches are P1. No branch tables or columns in MVP. |
| 12 | §11: platform admin manages FufaFlow plans | §80: "do not overbuild SaaS billing"; no SaaS model exists | Minimal tenant account fields in P0; automated SaaS billing in P2 (§8.5). |
| 13 | §17: `TRIAL` as a customer subscription state | A SaaS trial is a tenant concept | No customer trials in MVP (use a free plan or extension). Trial belongs to the tenant account. |
| 14 | §14: `PlanFeature` | Never defined: marketing bullets or entitlements? | Drop it. FufaFlow entitlements live in the platform domain. |
| 15 | §11: customer portal includes "renewal" | §58: online payment integrations are "Later" while the portal is a "should have" | The portal's key action depends on a later feature. Move the portal to P2; share receipts via signed link in P1. |
| 16 | §23 and §24: queues and scheduler for "report generation" and "scheduled reports" | §30: on-request DB aggregates; no scheduled reports in MVP | Remove scheduled reports. Reports are computed on request. |
| 17 | §32: audit "Payment edited" | §32: "financial history must not silently change" | Payments are immutable: void with reason, then re-record. |
| 18 | §64: "duplicate webhook" edge case | No webhooks in MVP (§58) | Drop it from MVP edge cases; keep the principle (idempotent ingestion) for P2. |
| 19 | §22 and §53: custom `NotificationService → Channel → Provider` layer | §3 and §70: use Laravel notifications; "use Laravel's strengths" | Use Laravel Notifications plus custom channel classes. No parallel framework. |
| 20 | §4: TanStack Query, React Hook Form, Zod | §72: Inertia forms and props; "avoid unnecessary API abstractions" | Remove all three from the MVP stack (§8.2). |
| 21 | §12: 14 domain modules with "strong module boundaries" | §70: "avoid premature design patterns… unnecessary abstraction" | Standard Laravel structure with Actions. Subscriptions, Billing and Payments are one consistency boundary. |
| 22 | §39: "local/public storage where security permits" | §39: "sensitive files must not be publicly exposed by guessable URLs" | Private disk only. No tenant uploads in MVP. |
| 23 | §47: "storage link where needed" | §39: private files need no symlink, and shared hosts may forbid symlinks | No `storage:link` in MVP. |
| 24 | §47: "git pull, then composer install" on the server | §6: Composer and Node availability unknown | Build `vendor/` and assets in CI; deploy an artifact. |
| 25 | §27 dashboard metrics, §55 KPIs, §78 product metrics | Three different lists, none defined | One metric dictionary (§7.10); the dashboard becomes an action list. |
| 26 | §27: dashboard "Expired 49" | No time window, so the count grows forever | "Expired in the last 30 days, not renewed" (the win-back list). |
| 27 | §28: profile shows one "Current Plan / Subscription Status" | §64: multiple subscriptions per customer | The profile lists all subscriptions; the header shows the most relevant one. |
| 28 | §15: Customer has a `status` | Status is implied by subscriptions (§26 lifecycle) | Customer `status` = record state (active/archived). Membership state is derived. |
| 29 | §1: "do not become a generic ERP" | §8 targets consultancies, software agencies and MSPs; §81 lists vertical modules | Remove project/retainer segments from the initial target; vertical modules wait for validation. |
| 30 | §44 and §74: E2E flow includes "Generate invoice" | Prepaid reality is one-step collection | The E2E golden path uses "Collect payment". |
| 31 | §54 documentation tree | §84 required documents (different set) and §85 first-task list (a third set) | One condensed documentation set (§5, V2 §32). |
| 32 | Brand: "Keep Business Going" (§1) | Core promise: "Keep customers, payments, and renewals flowing" | Not a conflict, but pick which line leads on marketing and in-product copy. |
| 33 | §13: tenant owns "Reports" | Reports are computed, not stored | Remove from the ownership list. |
| 34 | §21: reminder windows of 30 and 14 days | Weekly and monthly plans (§16) | A 30-day reminder on a monthly plan fires right after payment. Skip offsets longer than the interval; default offsets are short (§7.9). |
| 35 | §31: search by "subscription number" | Customers never quote subscription numbers | Drop it; search by name, phone, customer number, invoice number, receipt number and payment reference. |

---

## 4. Missing requirements

Only requirements that can be justified are listed. Priorities follow the classification in §6.

| # | Requirement | Why it is needed | Priority |
|---|---|---|---|
| M1 | **Beachhead segment and ideal customer profile** | Drives defaults, onboarding, acceptance criteria and pilots (C1) | Pre-code (D1) |
| M2 | **Renewal semantics and `renewal_mode`** | Without it the engine is undefined (C2) | P0 |
| M3 | **Persisted subscription periods** | Idempotency, history, import of paid-through dates, renewal-rate metrics | P0 |
| M4 | **Price and interval snapshot on subscription**, with a `price_overridden` flag | Plan edits must never rewrite issued periods or invoices, and negotiated prices must survive plan price changes. Standard-priced subscriptions pick up a new plan price at their next renewal. | P0 |
| M5 | **One-step "Collect payment" flow** | Matches front-desk reality; cuts onboarding clicks by about half (§6.3) | P0 |
| M6 | **Per-tenant document numbering** (customer, invoice, receipt) | Receipts must carry a stable, human-quotable number; avoids race conditions | P0 |
| M7 | **Receipts** (print-friendly view) | Customers expect proof of payment; owners use receipts to control cash | P0 (moved up from "should have") |
| M8 | **Immutability: void instead of edit** for payments and invoices | Financial traceability; cash-control value proposition | P0 |
| M9 | **Double-submit and idempotency protection** on payment recording | Front-desk double-clicks on poor connections | P0 |
| M10 | **Opening balances and import of current customers** | Real onboarding from Excel (C8) | P0 |
| M11 | **CSV export** of customers, payments and invoices | Data ownership and trust; an exit path lowers adoption risk | P0 |
| M12 | **Today / follow-up screen** | Directly answers "who needs follow-up today?" (§86); the spec's navigation lacks it | P0 |
| M13 | **Assisted messaging** (tap to call/SMS/WhatsApp, copy text) | Reaches customers without integrations (C5) | P0 |
| M14 | **Customer notes** (timestamped, with author) | Records follow-up outcomes ("will pay Friday") | P0 |
| M15 | **Tenant account status, trial, suspension semantics** | SaaS operability (C4) | P0 (minimal) |
| M16 | **Tenant resolution strategy** (session, no subdomains) | Wildcard DNS and SSL are often unavailable on shared hosting | P0 |
| M17 | **User–tenant membership model** | Staff invitations; future multi-business owners; avoids a schema change later | P0 (one membership enforced in app) |
| M18 | **Staff deactivation** | Staff turnover is constant; access must end immediately while records keep their author | P0 |
| M19 | **Platform admin console** (minimal) | Support, suspension, trial extension, health visibility | P0 |
| M20 | **2FA for platform admins** | One compromised admin exposes every tenant | P0 |
| M21 | **Scheduler and queue health monitoring** (heartbeat, failed jobs) | Cron fails silently on shared hosting | P0 |
| M22 | **Hosting procurement checklist and `hosting-check` command** | The "may have" list must become verified requirements | Pre-code + P0 |
| M23 | **Build-artifact deployment and rollback** | No Composer or Node needed on the server; reliable rollback | P0 |
| M24 | **Encrypted backups, restore drill, per-tenant restore runbook** | A shared database makes per-tenant restore non-trivial | P0 (runbook), P1 (script) |
| M25 | **Data protection: residency, terms of service, privacy policy, DPA, retention** | Proclamation 1321/2024; FufaFlow processes its tenants' customer data | Pre-code (D2, D8) |
| M26 | **Messaging abuse controls** (verification, caps, sender identity) | Protects Fufa's domain reputation and SMS costs | P0 |
| M27 | **Opt-out for customer messages** | Respect and likely legal need; cheap | P0 (manual flag), P1 (unsubscribe link) |
| M28 | **Phone normalisation and duplicate warning** | Search, deduplication and SMS all depend on it | P0 |
| M29 | **Currency minor-unit registry** | UGX and RWF have 0 decimals; ETB has 2. Never hard-code ×100 | P0 |
| M30 | **Ethiopian calendar decision** | Possibly a key local usability factor | Decide (D6) |
| M31 | **`joined_on` separate from `created_at`** | Imports would otherwise inflate "customer growth" | P0 |
| M32 | **Plan change at renewal** | The most common upgrade/downgrade path; no proration needed | P0 |
| M33 | **Extend subscription** (adjustment period) | Covers gym closures, goodwill and informal freezes until pause is built | P0 |
| M34 | **Fixed-term plans** (`term_periods`) | Training courses end; they must not show as "expired" churn | P0 |
| M35 | **Messaging send window and tenant-local scheduling** | Don't send at 03:00; reminders run on the tenant's day | P0 |
| M36 | **Catch-up semantics for scheduled work** | Missed cron runs must not lose reminders | P0 |
| M37 | **Staging environment on the same host** | Test migrations and deploys on the real stack | P0 |
| M38 | **Error visibility** (error email digest or hosted tracker) | Shared-hosting logs are otherwise invisible | P0 |
| M39 | **Incident runbooks** (site down, cron stopped, mail failing, suspected leak) | Operability; breach notification may be legally required | P0 (docs) |
| M40 | **Terminology labels** ("member", "student", "client") | Makes a generic engine feel vertical at near-zero cost | P1 |
| M41 | **Owner daily digest email** | Builds a daily habit without opening the app | P1 |
| M42 | **Shareable signed receipt link** | Send a receipt by SMS or Telegram | P1 |
| M43 | **SMS gateway with credits and metering** | Market-fit channel (C5) | P1 |
| M44 | **Refunds and customer credit** | Rare in the beachhead (validate); void covers mistakes in MVP | P1 |
| M45 | **Single-rate tax** | Needed for the B2B segment | P1 (D3) |
| M46 | **Automatic renewal invoicing and payment terms** | B2B segment | P1 |
| M47 | **Branches** (with branch-scoped staff) | Chains exist but aren't the beachhead | P1 |
| M48 | **Check-ins / usage events** | Gym habit feature; informational | P1 |
| M49 | **Impersonation for support** (audited, visible banner) | Assisted onboarding and support | P1 |
| M50 | **Full tenant export and deletion process** | Offboarding; data rights | P1 |

---

## 5. Unnecessary requirements

These should be removed or postponed. Each entry says why and what replaces it.

| Item (spec §) | Why remove or postpone | Replacement |
|---|---|---|
| About 25 pre-code documents plus 7 ADRs (§54, §84, §85) | Weeks of writing before learning anything; the two lists conflict; personas and coding standards add little | PRODUCT-SPEC, DOMAIN-MODEL, ARCHITECTURE, DEPLOYMENT (runbook), BACKLOG, DECISIONS, short ADRs, CLAUDE.md |
| 14 `app/Domain/*` modules (§12) | Ceremony; splits one transactional boundary (subscriptions, billing, payments) | Standard Laravel folders plus `Actions/` and a small `Billing/` calculator namespace |
| Separate "Renewals" module (§12) | Renewal is an action on a subscription | `RenewSubscription` action plus a Today view |
| TanStack Query (§4) | Duplicates Inertia's server-state model; forces JSON endpoints | Inertia props, partial reloads, deferred props |
| React Hook Form + Zod (§4) | Duplicates Inertia `useForm` and Laravel validation in two languages that drift apart | Inertia forms plus Form Requests |
| Custom NotificationService/Provider framework (§22, §53) | Re-implements Laravel Notifications | Laravel Notifications plus a custom `SmsChannel` (P1) |
| Repository pattern and interfaces (implied by §13 "repositories/query objects") | Eloquent already is the data-access layer | Eloquent models, scopes, report query classes |
| `PlanPrice`, `PlanFeature`, `Reminder` (as an entity), `AttendanceRecord`, `File`, `NotificationTemplate` as a full entity (§14) | Undefined or duplicated concepts | Price on plan; message purpose plus message log; one `usage_events` table (P1); template text in tenant settings |
| Stored subscription states TRIAL, DUE, OVERDUE; stored invoice DRAFT, PARTIALLY_PAID, OVERDUE (§17, §18) | Contradictory; time-driven status flips depend on cron | Stored decisions plus derived conditions |
| Payment editing (§32) | Breaks traceability | Void plus re-record |
| Refunds in MVP | Rare for prepaid memberships (validate); adds credit-note logic | Void (mistakes) in P0; refunds and credit in P1 |
| Tax in MVP (§18) | Only needed by the B2B segment; risks pretending to issue fiscal invoices | Schema keeps `tax_minor = 0`; single rate in P1 (D3) |
| Proration and mid-cycle plan changes (§64) | Complex, error-prone, rarely needed by SMBs | Change at renewal; cancel and re-subscribe for immediate changes |
| Branch support in MVP (§11, §31, §33) | Adds a dimension to every query and permission | P1 with nullable `branch_id` added when built |
| Customer portal (§58, §59) | Its key action (online renewal) needs payment integration | Signed receipt links (P1); portal in P2 |
| Scheduled reports (§24) | No user need stated | On-request reports |
| API platform and OpenAPI (§37) | No consumer exists | None until a consumer exists (P2) |
| Webhook and duplicate-webhook design (§64) | No integrations in MVP | The principle only (idempotent ingestion) in the architecture doc |
| "Subscription number" search (§31) | Nobody quotes it | Customer, invoice and receipt numbers, and payment reference |
| 30-day and 14-day default reminder windows (§21) | Meaningless for weekly or monthly plans; spam risk | Defaults of −3 days, +1 day; bounded configuration |
| Customer `first_name`/`last_name` split (§15) | Doesn't fit Ethiopian names or organisations | One `name` field |
| Customer `tags` (§15) | Not needed to answer the core questions | P1 |
| Analytics such as Customer Lifetime, ARPU, Average Subscription Value (§78) | Can't be computed reliably early (little history, import bias) | P1/P2 with definitions (§7.10) |
| AI assistant, churn prediction (§58 "Later", §80 add-ons) | Unvalidated; signals scope drift | Remove from the roadmap until validated |
| Access-control hardware, advanced attendance, industry module tree (§58, §81) | Pulls toward gym or vertical-management software | Remove until a validated vertical need exists |
| Target segments: consulting companies, software agencies, MSPs, internet resellers (§8) | Project, time or usage billing; different products | Remove from the initial target list |
| Inertia SSR (§5) | Already excluded; keep it that way | none |

---

## 6. MVP scope changes

### 6.1 Feature classification

Legend:

- **P0**: required to launch the MVP for the beachhead.
- **P1**: soon after launch.
- **P2**: future.
- **REMOVE**: not part of the product (or not until a validated need appears).

| Feature | Spec placement | New class | Reason |
|---|---|---|---|
| Authentication (login, reset, verification) | Must | **P0** | Comes from the starter kit; near-zero cost |
| Multi-tenancy (shared DB, fail-closed) | Must | **P0** | Core of a SaaS |
| Business setup (name, currency, timezone, grace, templates) | Must | **P0** | One screen; prefilled defaults |
| Users and staff invitations | Must | **P0** | Front desks have several staff |
| Roles (Owner, Manager, Staff; fixed matrix) | Must | **P0** | Cash control; fixed roles only |
| Custom roles and permission editor | Implied by §36 | **REMOVE** (P2 at most) | Fixed roles cover SMBs |
| Customers (name, phone, email, notes, joined_on) | Must | **P0** | Core |
| Customer tags, custom fields | §15 | **P1 / P2** | Not core |
| Plans (unit + count, price, renewal mode, term) | Must | **P0** | Core |
| Subscriptions with periods | Must ("billing cycles") | **P0** | Core |
| Renewal (manual, multi-period, plan change at renewal) | Must | **P0** | Core |
| Automatic renewal invoicing (B2B) | Implied by `auto_renew` | **P1** | Second segment |
| Pause/resume | §17 | **P1** | "Extend" covers it in MVP |
| Extend (adjustment period) | Not present | **P0** | Cheap and covers many cases |
| Invoices (mostly invisible to prepaid users) | Must | **P0** | Needed for balances |
| Draft invoices | §18 | **REMOVE** | No review step needed |
| Discounts (fixed amount per invoice) | §18 | **P0** | Gyms negotiate |
| Coupons and promotions | Not present | **REMOVE** | Marketing tooling |
| Tax | §18 "if enabled" | **P1** (D3) | B2B need |
| Manual payments | Must | **P0** | Core |
| Configurable payment methods | §19 | **P0** | Banks and wallets vary |
| Payment allocation (oldest-first, same customer) | §20 | **P0** (schema + simple UI) | Partial payments; one payment covers several invoices |
| Overpayment and customer credit | §20 | **P1** | Block overpayment in MVP |
| Refunds | Not explicit | **P1** | Void covers mistakes |
| Payment reconciliation | Later | **P2** | Needs integrations |
| Receipts (print view) | Should | **P0** | Proof of payment; cash control |
| Receipt share link (signed) | Not present | **P1** | Delivery by SMS or Telegram |
| PDF generation on server | Not explicit | **REMOVE** (MVP) | Browser print-to-PDF is enough; Ge'ez fonts are hard in PHP PDF libraries |
| Outstanding balances and receivables | Must | **P0** | Core question: "who owes me?" |
| Today / follow-up screen (the dashboard) | Must (dashboard) | **P0** | Core question: "who needs follow-up today?" |
| Revenue tiles (collected, outstanding, overdue) | Must | **P0** | Owner value |
| Charts | Implied | **P1** | Numbers first |
| Reminder engine and email channel | Must | **P0** | Core promise |
| Assisted messaging (call, SMS, WhatsApp, copy) | Not present | **P0** | Market fit (C5) |
| Tenant-editable message templates | §22 | **P0** | Lets owners write Amharic messages |
| SMS gateway | Should | **P1** | Cost, metering, sender-ID lead time |
| Telegram bot | Later | **P2** | Bots can't message users first; needs an opt-in flow |
| WhatsApp Business API | Later | **P2** | Cost and approval; reach to validate |
| In-app notifications | §22 | **P2** | The Today screen covers it |
| Owner daily digest email | Not present | **P1** | Habit |
| Search (name, phone, numbers, reference) | Must | **P0** | Front-desk speed |
| Filters (status, plan, date) | Must | **P0** | Lists |
| Branch filter | §31 | **P1** | With branches |
| Audit log (writes) | Must | **P0** | Financial traceability |
| Audit log viewer (filters) | Implied | **P1** | Customer timeline covers P0 |
| Reports: collections, receivables (aging), renewals and expiries, subscriptions by plan | Must (basic reports) | **P0** | Answer the core questions |
| Reports: customer growth, renewal rate, churn, MRR | §30, §55 | **P1** | Definitions in §7.10 |
| Advanced analytics, churn prediction, AI | Later | **REMOVE** (until validated) | Drift |
| Import customers plus current subscription (CSV) | Should | **P0** | Onboarding (C8) |
| Import .xlsx | Not present | **P1** | Encoding pain with Amharic in CSV (validate) |
| Import historical payments and invoices | Not present | **REMOVE** (P2 at most) | Opening balance is enough |
| Export (customers, payments, invoices CSV) | Should | **P0** | Trust |
| Full tenant export (zip) | Not present | **P1** | Offboarding |
| Attendance / usage events (check-in) | Should | **P1** | Gym habit; informational |
| Session packs / visit-limited plans | Not present | **P2** | Validate demand |
| Access-control hardware | Later | **REMOVE** | Gym-management drift |
| Class scheduling and booking | Implied by "Class → Attendance" | **REMOVE** | Gym and school management drift |
| Branches | Should | **P1** | Not beachhead-critical |
| Customer portal | Should | **P2** | Depends on online payments |
| Online payment integrations (Telebirr, aggregators) | Later | **P2** | Per-merchant credentials, legal and commercial work |
| Webhooks | Later | **P2** | With integrations |
| Public REST API and OpenAPI | Later | **P2** | No consumer |
| Mobile app | Later | **P2** | Responsive web first |
| Industry-specific modules | Later | **REMOVE** (until validated) | Drift |
| Inventory / POS | Non-goal | **REMOVE** | ERP drift |
| File attachments (contracts, payment proofs) | §39 | **P1** | Payment reference covers P0; avoids an upload attack surface |
| Tenant logo on receipts | Not present | **P1** | Nice to have |
| Platform admin console (minimal) | §11 | **P0** | Operability |
| Impersonation | Not present | **P1** | Support |
| Automated SaaS billing for FufaFlow | §80 | **P2** | Manual collection first |
| Tenant trial and suspension | §11 | **P0** (fields + behaviour) | Operability |
| Entitlements and limits enforcement | §80 | **P1** (stub in P0) | Pricing not validated |
| English UI with i18n plumbing | §34 | **P0** | Cheap now, expensive to retrofit |
| Amharic UI | §34 future | **P1** (validate) | Staff may need it |
| Afaan Oromoo UI | §34 future | **P2** | After Amharic |
| Ethiopian-calendar display | Not present | **Decide** (D6; default P1) | Validate |
| Multi-currency within one tenant | Implied by plan currency | **REMOVE** (P2 at most) | Breaks reports |
| 2FA (platform admins) | Not present | **P0** | Blast radius |
| 2FA (owners, optional) | Not present | **P1** | Cash and data protection |
| Backups (own, encrypted) with restore drill | §76 | **P0** | Production requirement |
| Health checks and monitoring | §45 | **P0** | Cron visibility |
| Inertia SSR | Excluded | **REMOVE** | Shared hosting |

### 6.2 The resulting MVP

The MVP is a sellable product for one segment. A business can:

1. sign up and set up in one screen, with plan templates;
2. import its Excel member list (CSV) or add members;
3. collect a payment in one step (new member, renewal, partial or balance) and print a receipt;
4. open **Today** and see who renews soon, who is in grace, who expired recently, and who owes, then act with one tap (collect, call, SMS, copy message, note);
5. let automatic email reminders go to customers who have email;
6. see what was collected (by day, method and staff member) and what is outstanding;
7. trust that payments can't be silently changed, because every void needs a reason and is audited;
8. export its data at any time.

Everything else (P1/P2) follows real pilot feedback.

### 6.3 Onboarding path and click count (spec §25 question)

**Spec V1 path** (inferred from §62 and §44):

1. Sign up
2. Business setup
3. Plan form
4. Customer form
5. Customer profile
6. Subscription form
7. Generate invoice
8. Invoice page
9. Payment form, with allocation

That is **8–9 screens, about 20 clicks, about 25 fields**, with no import.

**V2 path:**

| Step | Screen | Required inputs | Clicks |
|---|---|---|---|
| 1 | Sign up | name, email, phone, password, business name, business type | 1 |
| 2 | Setup (prefilled: Ethiopia, ETB, Africa/Addis_Ababa, grace 3 days) and plan templates for the chosen business type (e.g., "Monthly ETB ___", "Quarterly ETB ___") | 1–3 prices | 1–2 |
| 3 | Today (empty state: "Add customer" or "Import from Excel") | none | 1 |
| 4 | New customer + collect payment (name, phone, plan, start date, amount, method, optional reference) | about 6 | 1–2 |
| 5 | Receipt (print or share) | none | 1 |

That is **5 screens, about 12 inputs, 5–7 clicks**, and **under 10 minutes to the first receipt** (target to test in usability sessions). Email verification shows as a banner and blocks only customer messaging, not use of the app.

### 6.4 Import/export decision (spec §24)

| Data | Decision | Rationale |
|---|---|---|
| Customers + current subscription (name, phone, email, plan, start date, **paid-through date**, amount owed, joined date) | **P0**, CSV (UTF-8), two-step (validate and preview, then commit), all-or-nothing transaction, up to 5,000 rows synchronous | Without it a business with hundreds of members cannot switch. The paid-through date creates a period with `source = import` and no invoice. Amount owed creates one "opening balance" invoice. |
| Plans | **No import**. Plans are created during setup from templates. | Businesses have few plans (usually under 10). |
| Historical invoices and payments | **Not imported** (P2 at most) | Low value, high mapping complexity; the opening balance captures what matters. |
| .xlsx | **P1** | Excel's default CSV encoding may break Amharic; validate with real files. Template instructions in P0 tell users to "Save as CSV UTF-8". |
| Export: customers, payments, invoices (CSV) | **P0**, owner only, audited, CSV-injection-safe | Trust and portability. |
| Full tenant export | **P1** | Offboarding and data-rights requests. |

---

## 7. Domain-model changes

### 7.1 Answers to the core domain questions

| Question | V1 spec | Recommendation |
|---|---|---|
| Which entity owns what? | §13 lists tenant ownership only | The tenant owns every business record. A Customer owns its Subscriptions, Invoices, Payments and Notes. A Subscription owns its Periods. An Invoice owns its Items. A Payment owns its Allocations. |
| Can a customer have multiple subscriptions? | Implied (§64); the profile assumes one (§28) | **Yes.** The profile lists all of them. |
| Can a subscription have multiple invoices? | Implied | **Yes**: one per renewal action. A multi-period renewal produces one invoice covering N periods. |
| Can an invoice have multiple payments? | Partial payments are mentioned | **Yes**, via allocations. |
| Can one payment cover multiple invoices? | "Future" (§20) | **Yes**, via allocations, in P0 for the same customer only. The default allocation is oldest invoice first. |
| Can a subscription change plan? | Edge case only | **At renewal (P0).** Mid-period: cancel and create a new subscription. No proration. |
| Can a customer have overlapping subscriptions? | Unstated | **Yes** for different plans. The UI warns when the same plan overlaps. |
| How are renewals represented? | Unclear; a "Renewals" module | As new `subscription_periods` rows (`source = renewal`) created by the `RenewSubscription` action, plus an audit entry. There is **no Renewal table**. |
| Is a renewal an event, a transaction, or a new cycle? | Ambiguous | A **transaction** (an action) that **creates new cycle(s)** and **emits an audit event**. |
| Should billing cycles be persisted? | "Evaluate SubscriptionCycle" | **Yes**, as `subscription_periods`. This gives idempotency (unique per subscription and start date), coverage history, imported paid-through dates, and renewal-rate and churn metrics. |
| When is an invoice created? | "Subscription created → invoice generated" | In the **same transaction** as the start or renewal action. In P1 automatic mode, *N* days before the next period starts. Never by a separate "Generate" click. |
| Can an invoice exist without a subscription? | Unclear | **Yes**: one-off charges (for example a registration fee) and opening balances (`subscription_id` null). |
| Can a payment exist without an invoice? | Unclear | **Not in MVP.** Every payment is fully allocated. P1 adds unapplied credit. |
| Manual payments | MVP | The only payment type in MVP. Immutable. |
| Overpayments | "Future" | **Blocked in MVP.** The UI offers a multi-period renewal instead. P1 adds credit balances. |
| Partial payments | Implied by PARTIALLY_PAID | **Allowed.** The invoice stays open, and the balance shows on the profile, the Today screen and receivables. Coverage is unaffected (the business decided to grant it). |

### 7.2 Entity decisions

| Spec entity | Decision | Notes |
|---|---|---|
| Tenant | **Keep (P0)** | Plus account fields: `status`, `trial_ends_at`, `paid_until`, `platform_plan`, `currency`, `timezone`, `locale`, `country`, `settings` (JSON) |
| User | **Keep (P0)** | `is_platform_admin` flag; 2FA required for admins |
| Role / Permission | **Replace** with a `role` column on Membership plus a code-defined ability matrix | No permission tables |
| (new) Membership | **Add (P0)** | `(tenant_id, user_id, role, status)`; one active membership per user enforced in the app for MVP |
| (new) Invitation | **Add (P0)** | Hashed token, expiry, role |
| Branch | **Defer (P1)** | No placeholder default branch |
| Customer | **Keep (P0)**, modified | Single `name`, `phone` and `phone_e164`, `email`, `joined_on`, `messages_opt_out`, `status` (active/archived), `number` |
| Plan | **Keep (P0)**, modified | `interval_unit`, `interval_count`, `price_minor`, `renewal_mode`, `term_periods` |
| PlanPrice | **Drop** | Price lives on the plan and is snapshotted on the subscription |
| PlanFeature | **Drop** | Undefined; SaaS entitlements live in the platform domain |
| Subscription | **Keep (P0)**, modified | Snapshots (price + `price_overridden`, interval, mode, term), `anchor_day`, `starts_on`, `current_period_ends_on`, `periods_count`, `status` (active/paused/cancelled), `cancel_on`, `cancelled_at`, `cancellation_reason`. Renewal price = current plan price unless `price_overridden`. |
| SubscriptionCycle / Billing Cycle | **Merge** into `subscription_periods` (P0) | `sequence`, `starts_on`, `ends_on` (inclusive), `price_minor`, `invoice_id` (nullable), `source` (start/renewal/import/adjustment) |
| Invoice | **Keep (P0)** | `status` open/paid/void; denormalised `amount_paid_minor` and `balance_minor`; `number`; `due_on` |
| InvoiceItem | **Keep (P0)** | Description, quantity, unit price, amount |
| Payment | **Keep (P0)** | Immutable; `receipt_number`; `idempotency_key`; `status` recorded/void |
| PaymentAllocation | **Keep (P0)** | `voided_at` instead of deletion |
| PaymentMethod | **Keep (P0)**, simplified | Tenant-defined name plus a platform `kind` enum (cash, bank_transfer, mobile_money, card, other) |
| Reminder | **Drop as an entity** | A reminder is a message *purpose*; eligibility is computed |
| Notification | **Replace** with `message_log` | Avoids a clash with Laravel's `notifications` table |
| NotificationTemplate | **Simplify** | Tenant settings hold the editable text per purpose; system defaults live in lang files |
| UsageEvent | **Defer (P1)** | One table, informational |
| AttendanceRecord | **Drop** | Merged into UsageEvent |
| CustomerNote | **Keep (P0)** | Body, author, timestamp. `follow_up_on` date in P1. |
| AuditLog | **Keep (P0)** | Explicit writes from actions |
| File | **Defer (P1)** | No uploads in MVP |
| (new) Sequence | **Add (P0)** | `(tenant_id, name) → next_value`, locked per increment |

### 7.3 MVP relationship sketch

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

### 7.4 Subscription lifecycle (deterministic)

**Principle: store decisions, derive time.** Stored states change only through an action taken by a user, or by the system on a user's behalf. Conditions that become true merely because a date has passed are computed by **one** resolver (a PHP method for single records, plus an equivalent query scope for lists). Correctness therefore never depends on cron having run.

**Stored `status` and its transitions:**

| From | Action | Guard | To | Side effects (same DB transaction) |
|---|---|---|---|---|
| none | `StartSubscription` | none | `active` | Period #1 (`source = start`), invoice for period #1 plus optional one-off lines, optional payment and receipt; audit |
| `active` | `RenewSubscription(n, plan?)` | manual mode; not cancelled | `active` | *n* periods appended (see renewal rules below), one invoice, optional payment; plan snapshot updated if the plan changed; audit |
| `active` | `ExtendSubscription(days, reason)` | manager+ | `active` | Adjustment period (price 0, no invoice) appended; anchor reset; audit |
| `active` | `CancelSubscription(now)` | manager+ | `cancelled` | `cancelled_at`, reason; the user decides whether to void open invoices; audit |
| `active` | `CancelSubscription(at_period_end)` | manager+ | `active` with `cancel_on = current_period_ends_on` | Audit. Derived as cancelled after `cancel_on`. |
| `active` | `PauseSubscription` (P1) | none | `paused` | Pause record |
| `paused` | `ResumeSubscription` (P1) | none | `active` | Adjustment period of the unused days |
| `cancelled` | none | terminal | none | Re-subscribing creates a new subscription |

**Derived phase.** Rules are evaluated in this order, first match wins. `today` is the tenant-local date and `G` is the tenant's grace days.

1. `cancelled`: `status = cancelled`, or `cancel_on` is set and `today > cancel_on`
2. `paused`: `status = paused`
3. `scheduled`: `today < starts_on`
4. `completed`: `term_periods` is set, `periods_count ≥ term_periods`, and `today > current_period_ends_on`
5. `expired`: manual mode and `today > current_period_ends_on + G`
6. `grace`: manual mode and `current_period_ends_on < today ≤ current_period_ends_on + G`
7. `active`: otherwise. Flag `renewal_due` when `0 ≤ current_period_ends_on − today ≤ renewal_window` (default 7 days).

`has_overdue_balance` is an independent flag: the customer or subscription has an open invoice with `due_on < today`. It never changes the lifecycle phase.

**Renewal rules (manual mode):**

- **R1. Renew on time** (phase is active, renewal_due or grace): the next period starts on `current_period_ends_on + 1 day`. The anchor is kept.
- **R2. Renew after expiry:** by default the new period starts **on the renewal date**, leaving a gap. A tenant setting can make it `continue_from_last_end` instead. The anchor resets to the new start day.
- **R3. Renew for N periods:** N consecutive periods and one invoice (one line, for example "Gold Monthly × 3: Oct 1 – Dec 31"). This is how advance payments are handled without customer credit.
- **R4. Renew with a different plan:** the subscription's plan and snapshot change. The new periods use the new plan.
- **R5. Idempotency:** `UNIQUE(subscription_id, starts_on)` on periods makes a double-submitted renewal fail safely.
- **R6. Renewal price:** the current plan price, unless `price_overridden` is set (a negotiated price), in which case the subscription's own price is used. Manager+ can edit the price on the renewal form, which sets the override. Periods and invoices always keep the amount actually charged.

**Automatic mode (P1):**

- An hourly job finds subscriptions where `current_period_ends_on − today ≤ invoice_lead_days` and no next period exists. It appends the next period and invoice with `due_on = period start + payment_terms_days`.
- The subscription never expires automatically. Unpaid invoices become overdue, and the owner decides whether to cancel.
- The job uses catch-up semantics: it processes everything due as of now. The unique key makes reruns safe.

**Scenario walkthrough (spec §5 scenarios):**

| Scenario | Behaviour |
|---|---|
| New subscription, start today | `StartSubscription`: phase active; period #1 covers today through the computed end; invoice due today; usually paid in the same action |
| First payment | Allocated to period #1's invoice, which becomes paid. The lifecycle is unaffected. |
| Active | Phase active |
| Upcoming renewal | `renewal_due` flag; listed on Today; reminder at −3 days by default |
| Payment due | Invoice open with `due_on = today` ("due today" on Today) |
| Partial payment | Invoice open, `amount_paid > 0`, so `partially_paid`; balance shown |
| Overdue | Open invoice with `due_on < today`, so `has_overdue_balance`; the lifecycle is unaffected |
| Expired | Phase expired once `today > ends_on + G` (manual mode); shown on the win-back list for 30 days |
| Renewed | R1 or R2 |
| Cancelled | Immediate or at period end; open invoices are not auto-voided (the user is prompted) |
| Paused / resumed | MVP: Extend. P1: pause and resume with an adjustment period. |
| Upgrade / downgrade | At renewal (R4); immediate change = cancel + new subscription, with any goodwill applied as a discount |
| Future start date | Phase scheduled; invoice due on the start date (or paid now); reminders suppressed until active |
| Backdated subscription | Allowed for manager+. Periods are computed from `starts_on`. If period #1 has already ended, the UI warns and offers to add periods up to today, or to use import semantics. |
| Renewal after expiry | R2; anchor reset |

**Interval and calendar rules.** These are owned by a pure `PeriodCalculator` class with no database access.

| Case | Rule | Example |
|---|---|---|
| Day interval (e.g. 30-day plan) | next start = start + n days | Jan 31 (non-leap year) + 30 days → next start Mar 2, so the period is Jan 31 – Mar 1 |
| Week interval | next start = start + 7n days | none |
| Month interval | next start = `(year, month + n, min(anchor_day, days_in_month))` | Anchor 15: Jan 15 – Feb 14, Feb 15 – Mar 14 |
| Month-end anchor | Clamp to the last day of month; the anchor stays 31 | Jan 31 → next start Feb 28 (or 29), so the period is Jan 31 – Feb 27; then Feb 28 – Mar 30, Mar 31 – Apr 29 |
| Quarterly / semiannual | Month interval with count 3 / 6 | Nov 30 → Feb 28/29 → May 30 |
| Annual | Month interval with count 12 | Feb 29 2028 → next start Feb 28 2029 (clamped); anchor 29 restored in 2032 |
| Period end | `ends_on = next_start − 1 day` (inclusive) | Display: "Covered until Feb 27" |
| Anchor reset | Extension, pause/resume, or renewal after expiry sets `anchor_day = day of the new start` | Prevents snapping back to an old anchor |
| Leap year | Covered by clamping | none |
| Timezone | All of the above are calendar dates in the tenant's timezone (§7.9) | none |

**Decision D5 (date conventions).** Confirm "covered until = the day before the same date next month" (recommended, deterministic). The alternative some gyms use is "the same date next month", where periods overlap by one day. Also confirm the default grace (3 days) and the default renewal-after-expiry rule (restart on the payment date).

### 7.5 Invoice, payment and message lifecycles

**Invoice.** Stored `open | paid | void`. Derived: `partially_paid` (open and `amount_paid > 0`) and `overdue` (open and `due_on < today`).

| From | Event | To | Rule |
|---|---|---|---|
| none | Created by start, renewal, ad-hoc charge or import (opening balance) | `open` (or `paid` if fully paid in the same transaction) | Number from the tenant sequence |
| `open` | Allocations reach the total | `paid` | Recomputed from the sum of allocations, never incremented |
| `paid` | A payment is voided, so allocations drop | `open` | none |
| `open` | `VoidInvoice(reason)` | `void` | Only if `amount_paid = 0` (void the payments first); manager+; audited. "Write-off" = void with reason "uncollectible". |
| `void` | none | terminal | The number is retained, never reused |

**Payment.** Stored `recorded | void`. Immutable once recorded.

- Recording requires that the allocations sum to the payment amount and target open invoices of the same customer. Invoice rows are locked with `SELECT … FOR UPDATE`.
- **Void** (manager+, reason required) marks the payment and its allocations voided, then recomputes the invoices. Mistakes are corrected by voiding and re-recording.
- The `idempotency_key` (generated by the client per form instance) is `UNIQUE` per tenant, so a double submit returns the first payment.
- A duplicate-reference **warning** (not a constraint) appears when the same method and reference already exist for the tenant.

**Message.** `queued → sent | failed | skipped`.

- `skipped` applies when the customer opted out, has no address, the tenant is suspended, or the condition is no longer true at send time.
- Retries: 3 attempts with backoff of 1, 5 and 15 minutes.
- `dedupe_key` is UNIQUE, which guarantees at most one message per (purpose, subject, date, offset). Delivery is at least once, because a crash between the provider call and the database update can, rarely, cause a resend.

### 7.6 Billing scope (spec §6)

| Capability | MVP handling |
|---|---|
| Invoice generation | Inside start, renewal and ad-hoc actions only |
| Invoice status | open / paid / void, plus derived partially-paid and overdue |
| Partial payment | Yes |
| Full payment | Yes |
| Outstanding balance | `balance_minor` (denormalised) summed per customer and tenant |
| Overdue balance | Open invoices with `due_on < today` |
| Payment allocation | Yes: oldest-first default, explicit choice possible, same customer only |
| Payment reference | Free text, indexed, duplicate warning |
| Receipt | Every payment has a `receipt_number` and a print view showing business, customer, amount, method, reference, what it paid for (periods covered), remaining balance, staff member and dates |
| Cancellation / void | Void for invoices and payments with a reason; audited; manager+ |
| Refund | **Not in MVP.** Void covers recording mistakes. If pilots show refunds actually happen, P1 adds `refunds` (a negative cash movement against a payment) and customer credit. |
| Credit notes, ledgers, journals, chart of accounts | **Never.** This is accounting software territory. |

### 7.7 Payments (spec §7)

| Topic | Decision |
|---|---|
| Method abstraction | Tenant-defined `payment_methods` (name plus `kind`). Seeded per country: for Ethiopia, Cash, Bank transfer, Telebirr, CBE Birr, M-Pesa, Other. Nothing provider-specific in domain logic. |
| Manual recording | The only mechanism in MVP |
| Telebirr / CBE Birr / bank transfer | Recorded manually with a reference. No API integration in MVP. |
| Future integrations (P2) | Online collection usually needs **per-business merchant accounts**, either with each provider or through a local aggregator (for example Chapa). Design later: a `payment_gateway_accounts` table per tenant, a `payment_intents` table, webhook ingestion keyed by provider event ID (unique), and a reconciliation job. Not now. |
| Webhooks, duplicate handling, idempotency | P2. The principle (unique provider event ID, process once, acknowledge fast) is recorded in ARCHITECTURE.md only. |
| Reconciliation | P2 |
| Allocation | P0, as above |
| Unnecessary complexity flagged | Designing gateway abstractions, webhook tables or reconciliation now; anything that holds funds (FufaFlow must never be a payment processor) |

### 7.8 Money rules (spec §16)

1. Amounts are integers in **minor units**: `BIGINT` (signed), with columns suffixed `_minor`. No floats or decimals anywhere in PHP or JavaScript arithmetic.
2. **Every monetary row stores `currency` (CHAR(3)).** One currency per tenant in MVP. The tenant's currency is fixed once the first financial record exists.
3. **The currency exponent comes from a registry** (ETB 2, USD 2, KES 2, TZS 2, GHS 2, AED 2, UGX 0, RWF 0). Never hard-code ÷100.
4. **Parse user input as a string on the server.** `"2,000.50"` becomes `200050` via regex. The frontend never runs `parseFloat(x) * 100`, because `0.29 * 100 = 28.999…`.
5. **Discounts:** a fixed amount per invoice in MVP. A percentage is converted to an amount once, rounding half away from zero to the minor unit.
6. **Tax (P1):** one tenant rate, inclusive or exclusive, computed on the subtotal after discount and rounded once per invoice.
7. **Totals:** `total = Σ line amounts − discount + tax`. Lines are already integers, so no intermediate rounding is needed.
8. **Allocation never exceeds the invoice balance.** A payment's allocations must equal its amount (MVP).
9. **Display** uses `Intl.NumberFormat` (JavaScript) or `NumberFormatter` (PHP `intl`) with the currency code. The ETB display style ("ETB 2,000.50" or "Br 2,000.50") is a copy decision.

### 7.9 Date and time rules (spec §17)

1. **The app timezone is UTC**, and the database session timezone is `+00:00`. No database time functions (`NOW()`) in queries; times come from PHP.
2. **Technical timestamps** (`*_at`) are stored in UTC and displayed in the tenant timezone.
3. **Business dates** (`*_on`: `starts_on`, `ends_on`, `due_on`, `received_on`, `joined_on`, `cancel_on`) are `DATE` columns meaning "a calendar date in the tenant timezone". They are never timezone-converted.
4. **"Today" always comes from `TenantClock::today($tenant)`.** Domain code must not call `now()` or `today()` directly (enforced by an architecture test). Tests freeze time.
5. **Default dates on forms are tenant-local.** For example, a payment recorded at 01:00 Addis time on Nov 1 is still 22:00 UTC on Oct 31, but `received_on` must be Nov 1.
6. **Periods are inclusive** `[starts_on, ends_on]`. The next start is `ends_on + 1`.
7. **Interval arithmetic** follows §7.4.
8. **Grace** is an integer number of days, a tenant setting (default 3, range 0–30). Derived phases use it as described in §7.4.
9. **Reminders:**
   - Offsets in days relative to `ends_on` (renewal) or `due_on` (overdue).
   - Defaults: renewal −3; post-expiry +1; overdue +1 and +7.
   - At most 3 offsets per purpose.
   - Offsets longer than the plan interval are skipped.
   - Sent only in a tenant-local window (default 09:00–19:00).
   - If the job runs late, a reminder is sent only if its condition still holds and it is at most 1 day late. Otherwise it is logged as `skipped (stale)`.
10. **DST:** Africa/Addis_Ababa has none. Because the logic is date-based, DST in future markets only affects the send-window hours, which Carbon handles.
11. **Ethiopian calendar** (D6): storage and arithmetic stay Gregorian. Ethiopian-calendar display and input are presentation concerns (default P1, behind a tenant setting). Whether "monthly" means a calendar month or a 30-day block is a *plan choice* (`month` versus `day × 30`), not a calendar feature.
12. **Changing a tenant's timezone** is allowed with a warning. Business dates are unaffected.

### 7.10 Metric definitions (spec §23)

All metrics are tenant-scoped and use tenant-local dates. W = window [start, end]. "Eligible" excludes voided records.

| Metric | Formula | Source | Window | Cancelled | Paused (P1) | Overdue | Reliability |
|---|---|---|---|---|---|---|---|
| **Collected** | Σ `amount_minor` of non-void payments with `received_on ∈ W` | payments | Any | n/a | n/a | n/a | **Reliable (P0)** |
| **Outstanding** | Σ `balance_minor` of open invoices | invoices | As of today | Included if invoices remain open | Included | Included | **Reliable (P0)** |
| **Overdue amount** | Σ `balance_minor` of open invoices with `due_on < today` | invoices | As of today | Included | Included | n/a | **Reliable (P0)** |
| **Active customers** | Distinct customers with ≥1 subscription in phase active or grace | subscriptions | As of today | Excluded | Excluded | Included | **Reliable (P0)** |
| **Renewals (count)** | Renewal actions (distinct invoices from periods with `source = renewal`) created in W | periods | W | n/a | n/a | n/a | **Reliable (P0)** |
| **New subscriptions** | Subscriptions with `starts_on ∈ W` (imports excluded via `source`) | subscriptions | W | Counted if started | Counted | n/a | **Reliable (P0)** |
| **MRR (run-rate)** | Σ over subscriptions in phase active (excluding grace, scheduled, paused, cancelled, completed, fixed-term) of `price_minor × monthly_factor`, where the factor is 1/count for month, 1/(12·count) for year, (52/12)/count for week, (365.25/12)/count for day; rounded once | subscriptions | As of today | Excluded | Excluded | **Included** (contracted value, not cash) | Reliable but **jargon**: label it "Monthly run-rate" (P1) |
| **Renewal rate** | Coverage-end events in W (the period's `ends_on ∈ W`, no later period already existing, excluding cancelled and fixed-term-final periods) that were followed by a `renewal` period starting `ends_on + 1`, divided by all such events. Measured once `today > W.end + G`. | periods | Month | Excluded from the denominator | Excluded | Not a factor | **Reliable only because periods are persisted (P1).** Tautological in automatic mode; use collection rate there. |
| **Churn (subscription)** | Subscriptions whose expiry (`ends_on + G + 1`) or cancellation date falls in W, divided by subscriptions active at W.start | subscriptions + periods | Month | **Counted as churn** | Excluded from both | Not a factor | Reliable (P1). Later reactivation counts as a "win-back" in its own month. |
| **Collection rate** | Σ non-void allocations (payment `received_on ≤ measurement date`) to invoices with `due_on ∈ W`, divided by Σ totals of non-void invoices with `due_on ∈ W` | allocations, invoices | Month, as of a date | Included | Included | That is the point | **Reliable (P1)** because payments are always allocated |
| **Overdue rate** | Overdue amount ÷ outstanding amount (as of a date); also customers with an overdue balance ÷ active customers | invoices | As of a date | Included | Included | n/a | Reliable (P1) |
| **Customer growth** | Customers with `joined_on ∈ W` (new); net = active at W.end − active at W.start | customers, subscriptions | Month | n/a | n/a | n/a | Reliable **only with `joined_on`** (imports set historical dates) (P1) |
| **ARPU** | Cash ARPU = Collected(W) ÷ distinct paying customers in W. Run-rate ARPU = MRR ÷ active customers. | payments, subscriptions | Month | n/a | n/a | n/a | Reliable (P1); the two variants must be labelled |
| **Customer lifetime** | none | none | none | none | none | none | **Not reliably calculable** for 12+ months (truncated history, import bias) → P2 |
| **"Expected revenue"** (§27) | Undefined | none | none | none | none | none | **Replace** with two figures: "Invoices due in W" and "Up for renewal in W" (count and value at current price) |

---

## 8. Architecture changes

### 8.1 Laravel: the simplest architecture that stays maintainable

**Answer.** Standard Laravel structure, with:

- thin controllers;
- Form Requests;
- one Policy per model;
- **Action classes** for business operations that touch more than one model;
- a few **pure calculator classes** for dates, status and money;
- Eloquent models with relationships and scopes;
- backed **Enums** for every status or kind;
- explicit audit writes.

Group code by feature *inside* Laravel's default folders.

```text
app/
  Actions/{Customers,Subscriptions,Billing,Payments,Imports,Tenancy}/   # one class per operation, wraps DB::transaction
  Billing/            # PeriodCalculator, SubscriptionPhase, Money, CurrencyRegistry (pure, unit-tested)
  Enums/
  Http/{Controllers,Requests,Middleware}/{Area}/
  Models/
  Policies/
  Tenancy/            # CurrentTenant, BelongsToTenant, TenantScope, Tenancy::run()/bypass(), TenantRule
  Notifications/ (+ Channels/ in P1)
  Jobs/  Console/Commands/  Reports/ (tenant-scoped query classes)
```

| Pattern | Verdict | Reason |
|---|---|---|
| Modular monolith | **Keep**, as *feature folders* rather than isolated modules | One deployable fits shared hosting. Subscriptions, Billing and Payments must change in **one** DB transaction, so physical module walls between them hurt. |
| `app/Domain/*` (14 modules) | **Remove** | Ceremony that hides Laravel conventions from new developers and AI agents |
| Controllers | **Keep, thin** | Authorise, validate, call one action, return an Inertia response |
| Form Requests | **Keep** | Single source of validation truth (with tenant-scoped rule helpers) |
| Policies | **Keep** | One per model; tenant membership plus a role-ability check |
| Actions | **Add** | The "one authoritative place" for each business rule (§43 of the spec); easy to test |
| Services | **Only** as pure calculators (`PeriodCalculator`, etc.) | Avoids "giant service classes" |
| Events / listeners | **Avoid for core flows** | Explicit calls read top to bottom. Use framework events only (e.g. login). |
| Jobs | **Only** for message sending (P0), automatic renewal (P1), large imports (P1) | Shared-hosting queue capacity is limited |
| Notifications | **Laravel Notifications** (mail in P0, custom SMS channel in P1) | Don't rebuild the framework |
| Eloquent | **Keep**; `Model::shouldBeStrict()` outside production | Catches lazy loading and silently discarded attributes |
| Enums | **PHP backed enums**, stored as VARCHAR | No magic strings; no MySQL ENUM migrations |
| DTOs | **Optional**, only for actions with more than 4 inputs (`final readonly` classes) | Not a layer |
| API Resources | **Use to shape Inertia props** (or explicit arrays) | Never pass raw models, which over-share data into page HTML |
| Repositories / interfaces | **Remove** | Eloquent is already the abstraction. The only justified interface is `SmsGateway` (P1), because providers vary and tests need a fake. |
| Packages | No `spatie/laravel-permission`, no multi-tenancy package, no Cashier (Stripe and Paddle can't serve Ethiopian businesses), no Filament/Livewire (a second UI stack) | About 150 lines of custom tenancy code is easier to audit and can fail closed |

### 8.2 React/Inertia

| Topic | Recommendation | Reason |
|---|---|---|
| Foundation | The **official Laravel React starter kit** (Inertia 2, React, TypeScript, Tailwind, shadcn/ui) for the chosen Laravel version | It is exactly the spec's stack, with auth screens included; saves weeks |
| **TanStack Query** | **Not necessary. Remove.** | Inertia *is* the server-state layer: page props, partial reloads, deferred props, polling and prefetch. TanStack adds a second cache and forces JSON endpoints, which is the "separate API per page" pattern the spec prohibits. Revisit only for a live, independently refreshing widget, and even then try Inertia polling first. |
| React Hook Form + Zod | **Remove** | Inertia `useForm`/`<Form>` handles state, submission, `processing` and server errors. Laravel validation is the single truth. |
| Global state (Redux, Zustand) | **None** | Page props plus local state are enough |
| Pages | `pages/{area}/{Index,Show,Create,Edit}.tsx` | Mirrors routes |
| Layouts | `AppLayout` (tenant), `PlatformLayout`, `AuthLayout`, `PrintLayout` (receipts) | none |
| Domain components | `MoneyInput`, `MoneyText`, `DateInput` (string `YYYY-MM-DD`), `CustomerPicker`, `PhaseBadge`, `DataTable` (server-driven), `EmptyState`, `ConfirmVoidDialog` | Reuse across pages |
| Tables, filters, pagination | Server-side (Laravel paginator); filters in the query string; `router.get(..., { preserveState, preserveScroll, replace })`; debounced search | Works with bookmarks and back navigation |
| Permissions | A `can` map in shared props, built from policies; the UI hides actions; the server enforces | none |
| Dates | Never `new Date('2026-10-01')` for business dates (UTC-parsing shifts). Treat them as strings and format with `Intl.DateTimeFormat` in the tenant timezone. | Avoids off-by-one-day bugs |
| Errors | An Inertia error page for 403/404/500/503; a "session expired, reload" prompt for 419 | none |
| Loading and empty states | Inertia progress bar, skeletons for deferred props, `processing` disables submit, a call-to-action on every empty list | Required by the spec's Definition of Done |
| Mobile | Today, search, customer profile, Collect payment and receipt usable at 360 px wide; tables collapse to cards | Front desks use phones |
| Performance | Lazy-loaded pages; **no chart library in MVP**; small bundle for low-bandwidth networks | Ethiopian mobile networks (validate) |
| i18n | All strings through `t()` from day one; Laravel lang JSON shared through props; Noto Sans Ethiopic bundled | Retrofitting i18n is expensive; Ge'ez rendering needs the font |
| Navigation | **Today, Customers, Payments, Plans, Reports, Settings** (6 items, down from 9) | Subscriptions, Billing and Renewals live inside the customer profile and Today screens |

### 8.3 Database design (spec §15)

**Conventions:**

- **Primary keys:** BIGINT auto-increment. Use ULID tokens only for externally shared documents (receipt links, P1).
- **`tenant_id NOT NULL`** on every tenant-owned table, plus `UNIQUE(tenant_id, id)` on parent tables to support **composite foreign keys** `(tenant_id, x_id) → x(tenant_id, id)` on financial tables (subscriptions, periods, invoices, items, payments, allocations). This makes the database itself reject cross-tenant references. It costs little now, and Laravel supports array foreign keys.
- **Money:** `BIGINT` `*_minor` plus `currency CHAR(3)`.
- **Business dates:** `DATE` columns named `*_on`. Timestamps are named `*_at` and stored in UTC.
- **Statuses:** `VARCHAR(32)` with PHP enums.
- **Charset:** `utf8mb4` with `utf8mb4_unicode_ci` set explicitly. This keeps behaviour portable between MySQL 8 and MariaDB and handles Ge'ez script.
- **JSON** only for tenant settings (validated by a settings class), never for data you query.
- **No triggers or stored procedures.** Shared-hosting database users often lack the privilege when binary logging is on, and they hide logic.
- **Deletion:**
  - Financial rows are never deleted (void instead).
  - Customers and plans are archived with `archived_at`, **not** SoftDeletes, which avoid unique-constraint conflicts and scope surprises.
  - Tenant purge is a scripted hard delete (P1).
  - A data-subject erasure anonymises PII but keeps financial rows.
- **Foreign keys:** `ON DELETE RESTRICT` everywhere. The purge script deletes children first.
- **Nullable fields:** only where business-optional (`email`, `reference`, `subscription_id` on ad-hoc invoices). A customer needs at least one of phone or email (validation rule).
- **Partial indexes:** none in MySQL. Rules such as "one active subscription per customer and plan" are enforced in the application.
- **Pagination:** offset-based for lists (SMB scale); cursor-based for logs (P1).

**Indexes that will matter as tenants grow:**

| Table | Index | Serves |
|---|---|---|
| customers | `UNIQUE(tenant_id, number)`, `(tenant_id, phone_e164)`, `(tenant_id, name)`, `(tenant_id, email)` | Number lookup, phone search and duplicate warning, prefix name search |
| subscriptions | `(tenant_id, status, current_period_ends_on)` | Today lists: renewal due, grace, expired (derived by date ranges) |
| subscriptions | `(tenant_id, customer_id)`, `(tenant_id, plan_id, status)` | Profile; per-plan counts |
| subscription_periods | `UNIQUE(subscription_id, starts_on)`, `UNIQUE(subscription_id, sequence)`, `(tenant_id, ends_on)` | Renewal idempotency; renewal-rate metrics |
| invoices | `UNIQUE(tenant_id, number)`, `(tenant_id, status, due_on)`, `(tenant_id, customer_id, status)` | Receivables, overdue, profile balance |
| payments | `UNIQUE(tenant_id, receipt_number)`, `UNIQUE(tenant_id, idempotency_key)`, `(tenant_id, received_on)`, `(tenant_id, recorded_by, received_on)`, `(tenant_id, reference)` | Collections, per-staff cash-up, duplicate-reference warning |
| payment_allocations | `UNIQUE(payment_id, invoice_id)`, `(invoice_id)` | Recomputing invoice paid amounts |
| message_log | `UNIQUE(dedupe_key)`, `(tenant_id, customer_id, created_at)`, `(status, created_at)` | Idempotency, timeline, retry and prune |
| audit_logs | `(tenant_id, created_at)`, `(tenant_id, subject_type, subject_id)` | Timeline, audit view |
| memberships | `UNIQUE(tenant_id, user_id)`, `(user_id)` | Tenant resolution |

**Indexes that would be unnecessary:**

- a standalone `tenant_id` index wherever a composite index starts with `tenant_id`;
- indexes on low-cardinality columns alone (`status`, booleans);
- a lone `created_at` on tenant tables (queries are always tenant-scoped);
- FULLTEXT indexes in MVP (prefix `LIKE` plus exact phone match is enough at SMB scale);
- speculative "dashboard" indexes before slow-query evidence;
- per-column foreign-key indexes duplicated by composite foreign-key indexes.

### 8.4 Notification architecture (spec §18)

| Concern | Design |
|---|---|
| Templates | System defaults in lang files (English, plus an Amharic default if D7 confirms it). Tenants can override the text per purpose (renewal due, expired, overdue, receipt). Rendering is **plain whitelisted `{{variable}}` substitution**, never `Blade::render()` on tenant input (that is server-side template injection, which here means code execution). Variables are HTML-escaped for email, with an SMS length and segment counter (UCS-2 aware). |
| Channels | P0: mail (Laravel Notifications), plus assisted manual messaging (`tel:`, `sms:`, `https://wa.me/<number>?text=`, copy to clipboard), each logged as "prepared" when used. P1: `SmsChannel` with an `SmsGateway` driver. P2: Telegram (needs an opt-in flow because bots can't message users first) and WhatsApp. |
| Retries | Queue `tries=3`, `backoff=[60,300,900]`; failures shown on the customer timeline and in the tenant's message log |
| Failures | `failed` status with error text; a platform alert when the failure rate is high |
| Duplicate sends | `INSERT` into `message_log` with `UNIQUE(dedupe_key)` *before* sending. Keys look like `renewal_due:{subscription_id}:{ends_on}:{offset}`, `overdue:{invoice_id}:{offset}`, `receipt:{payment_id}`. |
| Scheduling | An hourly job that only acts inside each tenant's local send window, with catch-up and staleness rules (§7.9) |
| Tenant-specific templates | Yes (text overrides), from P0 |
| Localisation | Message locale = tenant locale in MVP; per-customer language in P1 |
| Delivery logs | `message_log` (recipient, channel, purpose, rendered body, status, attempts, provider ID, error, timestamps), retained 12 months (D8) |
| Consent and opt-out | Customer `messages_opt_out` (staff-set in P0); unsubscribe link for email in P1; SMS STOP handling depends on the provider (P1) |
| Abuse controls | Verified owner email before any customer messaging; per-tenant daily caps (trial: low); no messaging from suspended tenants; sender "Business via FufaFlow"; SPF, DKIM and DMARC on Fufa's domain |
| MVP channels | **Email plus assisted messaging.** SMS in P1. |

### 8.5 SaaS platform layer (spec §10)

| Concept | Design now | Build when |
|---|---|---|
| Vocabulary | Tenant domain: Plan, Subscription, Invoice, Payment. Platform domain: `Tenant` account fields, later `PlatformPlan`, `PlatformSubscription`, `PlatformInvoice` in `App\Platform`. | Now (naming rule) |
| Tenant status | Same rule as subscriptions: **store decisions, derive time.** Stored `status` = `active / suspended / cancelled` (admin or owner decisions). Derived account phase = `trialing / paid / past_due / lapsed`, computed from `trial_ends_at`, `paid_until` and past-due grace. A lapsed account behaves like a suspended one. | P0 |
| Free trial | Yes. Length is decision D4 (default 30 days). No card needed (local payment reality). | P0 |
| Collection of Fufa's fees | Manual (bank transfer, Telebirr). The admin extends `paid_until`; audited. | P0 |
| Suspension behaviour | Read-only UI plus export; **all customer messaging stops**; banner; owners can still pay Fufa | P0 |
| Cancellation | Read-only for N days (D9), offered export, then purge | P1 (MVP: manual runbook) |
| Plan limits (customers, staff, branches) | `Entitlements` service with `allows()` and `limit()`; returns unlimited in MVP; checked at a few named enforcement points | Stub P0, enforcement P1 |
| Feature entitlements | Same service | P1 |
| Metered add-ons (SMS) | Per-tenant credit balance and usage log | P1, with SMS |
| Automated SaaS billing | `PlatformSubscription` and invoices. Option: dogfood by running Fufa's own billing as a FufaFlow tenant. | P2 |

### 8.6 Internationalisation and future markets (spec §27)

| Dimension | MVP design that keeps the path open |
|---|---|
| Currency | Per-tenant currency plus an exponent registry; `currency` on every money row |
| Timezone | Per-tenant IANA timezone; tenant clock; date-only business fields |
| Localisation | `t()` everywhere; lang files; tenant locale; Ethiopic font. Amharic in P1, Afaan Oromoo and Swahili later. |
| Calendar | Gregorian storage; display adapters (Ethiopian calendar, P1) |
| Payment methods | Seeded per country; tenant-editable; `kind` enum for reporting |
| Payment providers | Per-tenant gateway accounts (P2); no provider in the domain |
| Notification providers | `SmsGateway` interface with per-country drivers (P1+) |
| Tax | Single tenant rate (P1); a regional tax engine is never built in-house (integrate if ever needed) |
| Numbers, dates, phones | `Intl` formatting; libphonenumber normalisation to E.164 with the tenant's country as default region |
| Data residency | **Decide per country.** Several African markets have localisation or transfer rules, so design for **one deployment per jurisdiction** (config-driven, no hard-coded domains) rather than a multi-region database |

---

## 9. DirectAdmin/cPanel issues (spec §12, §19)

### 9.1 Assumptions that break on shared hosting

| # | Hidden assumption in V1 | Failure on shared hosting | Requirement or fallback |
|---|---|---|---|
| 1 | `php` in cron is PHP 8.3 | The CLI default is often an older PHP than the web PHP set in MultiPHP/PHP Selector, so artisan and cron fail or misbehave | Use the **absolute path** to the PHP 8.3 CLI binary (e.g. `/opt/alt/php83/usr/bin/php`, `/opt/cpanel/ea-php83/root/usr/bin/php`, `/usr/local/php83/bin/php`); verify inside a cron run |
| 2 | Process functions are available | `proc_open`, `exec` or `symlink` may be in `disable_functions`. Composer, scheduler `command()` entries (which spawn processes), `storage:link` and backup packages then fail. | Check in `hosting-check`. Fallbacks: in-process `Schedule::call`/`job`, a second cron line for the queue worker, no symlinks. |
| 3 | Cron runs every minute | Some hosts allow only 5 or 15 minutes | Design for "catch-up" jobs scheduled on boundaries compatible with the real interval; never `dailyAt('08:07')` |
| 4 | Long-running CLI processes are allowed | Resource limits (e.g. CloudLinux LVE: CPU, memory, process counts) kill long workers | `queue:work --stop-when-empty --max-time=50`; chunk work into units under 30 s |
| 5 | Job timeouts are enforced | `pcntl` is often missing in the CLI, so `--timeout` is ignored and a hung SMTP or HTTP call blocks the worker | Explicit socket and HTTP timeouts (10 s) on every outbound call |
| 6 | SSH is available | Without SSH, migrations need a web trigger, which is dangerous | **Require jailed SSH** in procurement |
| 7 | The document root can be `public/` | The primary domain's root is often fixed to `public_html` | Use a **subdomain** (e.g. `app.<domain>`) with a custom document root. Fallback: copy only `public/` into `public_html`, point `index.php` at the app outside the web root, and set the public path explicitly. Never put the project root in `public_html`. |
| 8 | Composer and Node exist on the server | Missing, outdated or memory-capped | **Build in CI**: `vendor/` (with the production PHP version) and `public/build`; deploy a zip artifact |
| 9 | MySQL behaves like local MySQL | Different engine or version (MariaDB vs MySQL), low `max_user_connections`, no SUPER privilege | CI runs against the **same engine and version**; no triggers; short requests; explicit `utf8mb4_unicode_ci` |
| 10 | Host SMTP is fine for email | Hourly send caps and poor deliverability | A **transactional SMTP relay** with SPF, DKIM and DMARC (provider subject to D2) |
| 11 | Disk is plentiful | Disk and **inode** quotas; file cache and file sessions create many files | `database` cache and sessions (Laravel defaults); daily logs kept 14 days; prune message and audit logs per retention; never upload `node_modules` |
| 12 | Symlinks work | `FollowSymLinks` may be disabled, breaking `storage:link` and the `current → release` switch | Avoid `storage:link` (no public tenant files); fallback deploy without symlinks (§9.3) |
| 13 | Host backups are enough | Short retention, no per-table restore, same failure domain | Own encrypted backups; restore drill (§11) |
| 14 | Outbound HTTPS is open | Some hosts firewall outbound traffic | Verify for the SMTP relay, SMS (P1), backup target and uptime pings |
| 15 | The server timezone matters | It doesn't if the app uses UTC and never calls database time functions | Rule in §7.9 |
| 16 | OPcache picks up new code | In-place overwrites can serve stale or mixed code | Release directories; deploy in maintenance mode |
| 17 | Hosting location is irrelevant | It may be legally constrained | **D2** (C7) |

### 9.2 Queue and scheduler realities (spec §19)

- **Queue:** `QUEUE_CONNECTION=database` with `after_commit=true`. Jobs: message sending (P0), automatic renewal (P1), large imports (P1).
- **Worker:** a scheduler entry `queue:work --stop-when-empty --max-time=50 --tries=3` every minute with `withoutOverlapping()` (the database cache store supports locks). If `proc_open` is disabled, use a second cron line that calls the worker directly.
- **Retry:** `tries=3`, `backoff=[60,300,900]`. `failed_jobs` is pruned after 30 days. Retries are documented in the runbook.
- **Idempotency:** guaranteed by unique keys (`message_log.dedupe_key`, `subscription_periods (subscription_id, starts_on)`, `payments.idempotency_key`), not by "remembering" state.
- **Long-running jobs:** none. Imports and exports in MVP are synchronous with row caps.
- **Scheduled "renewal checks":** there is **no status-transition job at all**, because phases are derived (C3). Cron only drives side effects: reminders, digests (P1), automatic renewal (P1), backups and pruning.
- **Heartbeat:** every minute the scheduler writes `scheduler_last_run_at`. `/health` reports its age, the queue depth and the failed-job count. An external uptime monitor alerts when the heartbeat is older than 15 minutes.

### 9.3 Deployment and rollback

```text
CI (GitHub Actions): composer install --no-dev -o (PHP 8.3, same extensions) → npm ci && npm run build → tests → zip artifact
Server:
  ~/fufaflow/releases/<timestamp>/   (code + vendor + public/build)
  ~/fufaflow/shared/.env, ~/fufaflow/shared/storage/
  ~/fufaflow/current -> releases/<timestamp>        (if symlinks allowed)
  docroot of app.<domain> -> ~/fufaflow/current/public
Deploy (SSH): upload/unzip → link shared .env/storage → php artisan down → mysqldump (pre-deploy backup)
  → php artisan migrate --force → php artisan optimize → switch `current` → php artisan up → smoke test /up and login
Rollback: switch `current` back (code); migrations are backward compatible (expand → migrate → contract in a later release);
  restoring the pre-deploy dump is the last resort. Never `migrate:rollback` in production by default.
Guards: DB::prohibitDestructiveCommands() in production; APP_DEBUG=false; .env never in Git.
No-symlink fallback: deploy into a fixed directory under maintenance mode; keep the previous artifact zip for rollback.
```

### 9.4 Other hosting items

- **`.env`** lives outside the web root (guaranteed by the `public/` docroot). Back up `APP_KEY` in a password manager, because losing it breaks sessions and any encrypted fields.
- **Storage:** a private disk only (recent Laravel versions default `local` to `storage/app/private`). No tenant uploads in MVP.
- **Logs:** `daily` with `LOG_DAILY_DAYS=14`, tenant and user IDs in the log context, PII masked.
- **Sessions:** `database`, secure cookies, `SameSite=Lax`, a 120-minute lifetime (consider a shorter one for shared front-desk devices).
- **Cache:** `database`; keys prefixed with the tenant ID.
- **Migrations:** run through SSH, with a backup first, backward compatible.
- **Staging:** a second subdomain and database on the same account, deployed first with the same script.

### 9.5 Hosting procurement checklist (turns the V1 "may have" list into requirements)

| Requirement | Status |
|---|---|
| PHP 8.3+ for **both web and CLI**, selectable per domain | Must |
| Extensions: ctype, curl, dom, fileinfo, filter, hash, intl, mbstring, openssl, pcre, pdo_mysql, session, tokenizer, xml (+ zip, gd optional) | Must |
| `proc_open` enabled (and ideally `symlink`) | Must (or accept the documented fallbacks) |
| Jailed SSH | Must |
| Cron at a 1-minute interval | Should (5 minutes acceptable with design rules) |
| MySQL 8.0+ or a MariaDB version supported by Laravel 13; InnoDB; utf8mb4; `max_user_connections` ≥ 20 | Must |
| Custom document root for a subdomain | Must |
| AutoSSL / Let's Encrypt | Must |
| Outbound HTTPS and SMTP (587) | Must |
| Disk ≥ 10 GB and adequate inode limit | Should |
| Server location compatible with D2 | **Must, legally** |
| A second database and subdomain for staging | Should |

A `php artisan fufaflow:hosting-check` command (P0) verifies each item that can be checked automatically.

---

## 10. Security issues

### 10.1 Multi-tenancy design and controls (spec §8)

| Question | Decision |
|---|---|
| Where `tenant_id` belongs | On **every** tenant-owned table, including child tables (items, allocations, periods), so every query can filter without joins and composite foreign keys can enforce ownership |
| Is a global scope appropriate? | **Yes**, as the default guard, but **fail closed**: if no tenant context is set, a tenant-owned query **throws** instead of returning every row. A `creating` hook fills `tenant_id` and rejects a mismatched one. |
| Where authorisation occurs | Route middleware (authenticated, tenant resolved, tenant not suspended for writes) → Policy (role ability; model loaded through the tenant scope, so a foreign ID returns 404) → Action invariants (for example, the invoice belongs to the same customer as the payment) |
| Accidental cross-tenant queries | (1) Fail-closed scope. (2) `TenantRule::exists()` and `TenantRule::unique()` helpers, because **Laravel's `exists`/`unique` rules use the query builder and ignore global scopes** (classic IDOR). (3) `DB::table()` allowed only in `App\Reports`, and every report test seeds two tenants. (4) Composite foreign keys on financial tables. (5) Architecture tests: every model under `App\Models` (except an allow-list) uses `BelongsToTenant`; no raw `exists:` strings in Form Requests (grep test). |
| Tenant identification | From the authenticated user's membership, stored as `current_tenant_id` in the session and re-validated each request. **No subdomains in MVP**: wildcard DNS and wildcard SSL are often unavailable on shared hosting. |
| Background jobs | Tenant-aware jobs carry `tenant_id`. Job middleware sets the context and **clears it in `finally`** (a cron-driven worker processes many tenants' jobs in one process). Note that Laravel restores serialised models *without* global scopes, so pass IDs and resolve them inside the tenant context. |
| Scheduled commands | Iterate tenants explicitly (`Tenancy::run($tenant, fn)` per tenant, try/catch per tenant so one failure doesn't stop the others; suspended tenants are skipped for messaging) |
| Files | Private disk, paths `tenants/{tenant_id}/…`, served only through a controller after a policy check; random file names; never accept a path from the request (prevents path traversal). No uploads in MVP. |
| Exports | Streamed CSV from tenant-scoped queries; owner only; audited; CSV-injection-safe (cells beginning `=`, `+`, `-`, `@`, tab or carriage return are prefixed with `'`. Note that this also affects `+251…` phone numbers, so export phones in national format or accept the prefix.) |
| Cache | Keys prefixed with `tenant:{id}:` (a shared cache key is a cross-tenant leak) |
| Platform admin bypass | Separate `/platform` route group, `EnsurePlatformAdmin` middleware plus mandatory 2FA; tenant data accessed only through explicit `Tenancy::bypass()` calls (grep-able, reviewed); **read-only by default**; impersonation (P1) audited, time-limited, with a visible banner. Never implement admin access as `Policy::before()` returning true. |

### 10.2 IDOR / broken object-level authorisation scenarios

| # | Attack | Does a global scope stop it? | Control |
|---|---|---|---|
| 1 | `GET /customers/{id}` with another tenant's ID | Yes (404) | Route model binding through the scope |
| 2 | Create a subscription with another tenant's `plan_id` | **No**: the `exists` rule bypasses scopes | `TenantRule::exists('plans')` plus a composite foreign key |
| 3 | Allocate a payment to an invoice of a *different customer in the same tenant* | **No** | Action invariant (`invoice.customer_id == payment.customer_id`) plus a test |
| 4 | Nested route `/customers/{a}/subscriptions/{b}` where `b` belongs to another customer | **No** | `scopeBindings()` |
| 5 | Report or dashboard via `DB::table` | **No** | Explicit `tenant_id` filter plus a two-tenant test |
| 6 | Cached dashboard without a tenant key | **No** | Tenant-prefixed keys |
| 7 | Queued job run without context | Throws (fail-closed) instead of leaking | Tenant-aware job middleware |
| 8 | Guessable receipt URL (P1) | n/a | Signed URL plus a ULID, expiring, revocable |
| 9 | `tenant_id`, `role`, `recorded_by`, `status` or amounts mass-assigned from the request | n/a | Explicit `$fillable`; actions set these fields |
| 10 | Inertia props over-sharing (full models with relations embedded in page HTML) | n/a | Resource or array prop shaping; review checklist |
| 11 | `?sort=` passed straight to `orderBy` | n/a | Allow-listed sort columns |
| 12 | Tenant template rendered by Blade | n/a | Plain substitution only |
| 13 | Platform routes reachable by a tenant owner | n/a | A route-group test asserting 403 for every `/platform/*` route |
| 14 | Invitation acceptance changing role or tenant | n/a | Role and tenant are taken from the server-side invitation record only |

### 10.3 Authorisation model (spec §9)

Fixed roles per membership plus a platform flag. The ability matrix is defined in code (`Role` enum → `Ability` set). There are no permission tables and no custom roles in MVP.

| Capability | Owner | Manager | Staff |
|---|---|---|---|
| View customers and profiles; search | ✓ | ✓ | ✓ |
| Create and edit customers; add notes | ✓ | ✓ | ✓ |
| Archive customers; import | ✓ | ✓ | — |
| Manage plans and prices; payment methods; message templates | ✓ | ✓ | — |
| Start or renew subscriptions; collect payments; print receipts | ✓ | ✓ | ✓ |
| Override price or apply a discount; backdate; extend | ✓ | ✓ | — (P1: tenant setting allowing staff discounts up to X%) |
| Cancel subscriptions | ✓ | ✓ | — |
| Void payments and invoices (reason required) | ✓ | ✓ | — |
| View revenue totals and reports | ✓ | ✓ | Own collections today only |
| Export data | ✓ | — (P1: grantable) | — |
| Invite or deactivate users; change roles | ✓ | — | — |
| Business settings; FufaFlow account and billing; close the account | ✓ (re-authentication for closing) | — | — |
| View the audit log | ✓ | — | — |
| Manage branches | P1 | P1 | — |

**Rules:**

- Starting or renewing a subscription with zero payment ("on credit") requires manager+. Staff can record partial payments.
- The last active owner cannot be demoted or deactivated.
- Deactivation ends the user's sessions immediately. Their records keep `recorded_by` (users are never hard-deleted).
- Every staff member has their own login. Shared logins defeat the cash-control value (validate actual practice).

### 10.4 Other security gaps

| Area | Missing in V1 | Requirement |
|---|---|---|
| Authentication | Account enumeration, admin 2FA | Generic reset and login messages; mandatory TOTP for platform admins; optional for owners (P1) |
| Rate limits | None specified | Login 5/min per email and IP; registration 3/hour per IP; password reset 3/hour; invitations 20/day per tenant; export and import 10/hour; messaging caps |
| Sessions | Shared front-desk devices | Regenerate on login; invalidate other sessions on password change; idle timeout; secure, HttpOnly, SameSite=Lax cookies |
| XSS | Templates and rich text | React escaping; ban `dangerouslySetInnerHTML` (lint); escape template variables in email |
| SQL injection | Raw fragments | Bindings only; ban interpolated `whereRaw`; allow-list sort and filter fields |
| CSRF | Covered by Laravel and Inertia | Handle 419 in the UI |
| Headers | "Secure headers" only | CSP (with a Vite nonce), HSTS, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy` via middleware (portable, unlike `.htaccess`) |
| File uploads | Generic | None in MVP. P1: size and MIME-by-content limits, private disk, random names, no SVG/HTML. |
| Secrets | `.env` only | `APP_KEY` escrow; SMTP and SMS credentials only in `.env`; rotation runbook |
| Sensitive logging | "Review logs" | Never log request bodies, passwords, tokens; mask phone and email in logs |
| Audit | Fields only | Include `ip`, the actor type (user, system, platform_admin), before/after for edits; platform-admin access to tenant data is audited |
| Backups | "Consider" | Encrypted (public-key, so the server can't decrypt old backups); access-controlled; restore drill |
| Data export | Not secured | Owner only, audited, rate-limited |
| Account deletion | Absent | Owner closes the account → read-only → export window → purge (D9); erasure of an individual customer = anonymise PII |
| Abuse | Absent | Messaging controls (§8.4); sign-up throttling; disposable-email blocking optional |
| Supply chain | Absent | `composer audit` and `npm audit` in CI; lockfiles committed; the minimal-dependency policy (§8.1) |

### 10.5 Privacy and compliance (human decisions needed)

These are not legal conclusions. Each needs a decision by Fufa with Ethiopian counsel.

| Topic | Question for a human decision |
|---|---|
| Applicable law | Ethiopia's Personal Data Protection Proclamation No. 1321/2024 (in force 2024-07-24; regulator: Ethiopian Communications Authority). Which obligations apply to Fufa as a **processor** for tenants, and to tenants as **controllers**? Is registration required? Is a DPO required? |
| Data residency | Do the localisation provisions require Ethiopian hosting for FufaFlow's data? Does that include backups, email relay, SMS gateway and error tracking? (**Blocks hosting procurement; D2.**) |
| Terms of Service and Privacy Policy | Required before the first pilot. Who owns customer data (the tenant), Fufa's processing role, acceptable use (no spam), suspension and termination, liability. |
| DPA with tenants | A processor agreement as part of the terms |
| Data minimisation | MVP collects name, phone, email (optional), address (optional) and notes. No ID numbers, no health data (gyms may want medical notes: **disallow** in free-text guidance), no photos. |
| Retention | Financial records (years?), audit logs (years?), message logs (12 months?), data of closed tenants (export window, e.g. 60 days, then purge), backups (rotation, so deleted data ages out) (**D8, D9**) |
| Customer rights | Access and export per customer (P1), erasure by anonymisation (P1), correction (P0 via edit) |
| Consent for communications | Transactional reminders versus marketing. MVP sends only transactional messages tied to an existing subscription, with opt-out. Marketing broadcasts are **out of scope**. |
| Breach notification | What must be reported, to whom, and how fast? Runbook required. |
| Fiscal documents | Can FufaFlow "invoices" and "receipts" be treated as tax documents? Default: **no**. Label them as payment records and receipts, not tax invoices, until advised otherwise (**D3**). |

---

## 11. Operational issues (spec §22)

| Capability | V1 | Gap | Recommendation | Priority |
|---|---|---|---|---|
| Onboarding | Sign-up only | No assisted path | Self-serve sign-up plus an "assisted onboarding" playbook (Fufa staff import the spreadsheet); validate how many pilots need it | P0 |
| Tenant setup | "Business setup" | No defaults or templates | Setup screen with country defaults and plan templates per business type | P0 |
| Support | "Support businesses" | No tooling | Admin console (tenant lookup, status, counts, owner contact); in-app support contact (phone, Telegram, email); impersonation in P1 | P0/P1 |
| Tenant suspension | "Suspend tenants" | No behaviour defined | Read-only plus export, messaging stopped, banner, audited reason | P0 |
| Tenant deletion | Absent | No process | Runbook in P0; scripted purge plus export in P1 | P0/P1 |
| Backups | "Document" | No encryption, location or drill | Nightly `mysqldump --single-transaction` → gzip → public-key encryption → off-server copy (location per D2); retention 7 daily, 4 weekly, 6 monthly; **monthly restore drill on staging** | P0 |
| Restore | "Restore process" | Per-tenant restore is non-trivial in a shared database | Runbook: restore to a scratch database and extract one tenant's rows by `tenant_id` (script in P1) | P0/P1 |
| Incident handling | Absent | none | Runbooks: site down, cron stopped, queue backlog, email failing, database full or quota, suspected data leak (with breach-notification step), bad deploy | P0 (docs) |
| Failed jobs | "Tracking" | No alerting | Daily alert if `failed_jobs > 0`; documented retry and forget commands | P0 |
| Email failure | Absent | none | Message log status, tenant-visible failures, platform alert on failure rate; relay provider dashboard | P0 |
| Notification delivery problems | Absent | none | Same as above; SMS provider status in P1 | P0/P1 |
| Billing failures (SaaS) | Absent | none | `past_due` banner, then suspension after grace (D4); manual follow-up by Fufa | P0 |
| Monitoring | "Health endpoint, logs" | No external watcher | External uptime monitor on `/up` and `/health` (heartbeat age, queue depth, failed jobs, last backup) | P0 |
| Error visibility | "Error logging" | Nobody reads shared-hosting logs | Daily error digest email to Fufa ops, or a hosted error tracker after the D2 check (PII scrubbed) | P0 |
| Data fixes | Absent | Manual SQL in production | Audited artisan commands only | P0 |
| Release management | Absent | none | Changelog; deploy window at night Addis time; staging first | P0 |
| Framework upkeep | Absent | Laravel 13 security support ends Q1 2028 | Annual upgrade slot in the roadmap | P1 |

---

## 12. Business risks (spec §3, §26)

### 12.1 Does the spec describe one product or slowly become an ERP?

The **core** (§7, §10, §86) describes one focused product. The **edges** pull it toward several others:

| Pull toward | Spec items doing the pulling |
|---|---|
| **Accounting package** | Tax engine (§18), drafts, credit and overpayment handling, reconciliation (§20, §58), B2B contract invoicing segments (§8), "Billing" as a top-level navigation item |
| **CRM** | Customer lifecycle/win-back/churned stages (§26), tags (§15), notes as a CRM, communication hub with 4–5 channels (§22) |
| **Gym-management platform** | Attendance and check-in (§29), "Class → Attendance" (training), access-control hardware (§58), advanced attendance |
| **PSA / services ERP** | Consulting companies, software agencies, MSPs (§8); contracts module and documents (§39, §81) |
| **Generic SMB platform** | Branches with cross-branch views (§33), API platform, mobile app, AI assistant, churn prediction (§58), industry module tree (§81), add-on revenue lines (§80) |

**The ERP-drift test** (adopted in V2): a feature enters the MVP only if it directly helps the beachhead segment answer one of the seven core questions:

1. Who are my customers?
2. Who is active?
3. Who is expiring?
4. Who owes me money?
5. How much have I collected?
6. How much recurring revenue should I expect?
7. Who needs follow-up today?

Everything else waits for evidence from pilots.

### 12.2 Positioning (spec §26)

None of the answers below is a proven advantage. Each is a hypothesis to validate.

- **Why would a gym use FufaFlow instead of a gym-specific system?**
  - Gyms that need class booking, trainer scheduling or turnstiles should *not*; they are not the target.
  - Small and medium gyms on notebooks or Excel might, for:
    - local pricing in ETB;
    - recording Telebirr, CBE Birr and bank payments;
    - Amharic customer messages;
    - working on a phone;
    - setup in an hour via import;
    - accountable cash collection (receipts plus void audit).
  - **Unknown:** which local gym software exists, what it costs, and why gyms don't use it.
- **Why would a cleaning company use FufaFlow instead of accounting software?**
  - For invoicing and VAT, it shouldn't; accounting software does that.
  - FufaFlow's only credible angle is the *recurring schedule, automatic invoices and collections follow-up* layered beside their accounting. For firms with under 30 clients that value is thin. This is why B2B services are not the beachhead.
- **Why would a training centre use FufaFlow instead of Excel?**
  - Excel is free, flexible and familiar.
  - FufaFlow has to win on:
    - automatic daily lists (expiring, owing);
    - reminders without manual effort;
    - instant receipts;
    - multi-user access with accountability;
    - phone access.
  - **Unknown:** whether that is worth a monthly fee, and at what price.
- **Strongest initial value proposition (hypothesis):** *"Know who expires and who owes today, and collect it, from your phone."* Secondary hook: *"Every birr your staff collects is recorded and receipted."*

### 12.3 Risk register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Building for everyone, resonating with no one | High | High | D1 beachhead; pilots with 3–5 businesses in one segment |
| Reminders don't reach customers (email) | Medium–High | High | Assisted messaging in P0; SMS in P1; validate channels in interviews |
| Hosting choice illegal or changed later | Medium | High | D2 legal advice before procurement |
| Shared hosting can't run cron or queue reliably | Medium | Medium | `hosting-check`, walking skeleton in week 1, derived states (no cron dependency), heartbeat alerts |
| Cross-tenant data leak | Low–Medium | Critical | §10.1 controls plus isolation test harness |
| Onboarding too slow (no import) | High (V1) | High | P0 import plus assisted onboarding |
| Price point too low to sustain SMS and support | Medium | Medium | Validate willingness to pay; SMS as a metered add-on |
| Staff resist accountability (shared logins) | Medium | Medium | Cheap or free staff seats; validate practice |
| Tax or fiscal expectations from B2B customers | Medium (if B2B) | Medium | D3: not a tax invoice; B2B in P1 |
| Ethiopian-calendar expectation unmet | Medium | Medium | D6: validate early; dual display in P1 |
| Over-documentation delays launch | High (V1) | Medium | Condensed docs; skeleton first |
| Single developer as bus factor | Unknown | High | CLAUDE.md, ADRs, tests, runbooks |

---

## 13. Recommendations

### 13.1 BUILD (definitely, P0)

1. Walking skeleton on the real host: starter kit, fail-closed tenancy, cron heartbeat, CI artifact deploy, `hosting-check`.
2. Sign-up, business setup with templates, staff invitations, three fixed roles, platform admin console (minimal), admin 2FA, tenant status and suspension.
3. Customers (single name, normalised phone, `joined_on`, opt-out, notes, archive).
4. Plans (interval unit and count, renewal mode, fixed term).
5. Subscriptions with persisted periods, snapshot pricing, derived phases, renew (multi-period, plan change), extend, cancel.
6. Invoices (open/paid/void), payment methods, payments (immutable, idempotent, allocations), void flows, receipts (print view), per-tenant numbering.
7. **Collect payment**: a one-step flow for new and existing customers.
8. **Today** screen: renewal due, grace, expired in the last 30 days, overdue, starting today; one-tap actions; money tiles for owners and managers.
9. Reminder engine with email and assisted messaging, editable templates, deduplication, send window, caps.
10. Reports: collections (by day, method and staff), receivables (aging), renewals and expiries, subscriptions by plan.
11. CSV import (customers with current subscription and opening balance) and CSV export.
12. Audit log writes; customer timeline.
13. Backups with a restore drill, monitoring and runbooks, staging.
14. Test suite: date-math tables, phase resolver, money parsing, isolation harness, idempotency, one end-to-end golden path.

### 13.2 POSTPONE

- **P1:**
  - SMS gateway plus credits
  - owner daily digest
  - signed receipt links
  - Ethiopian-calendar display (if D6 says so)
  - Amharic UI (validate)
  - pause/resume
  - automatic renewal invoicing and payment terms (B2B)
  - single-rate tax
  - refunds and customer credit
  - branches
  - check-ins/usage events
  - tags
  - follow-up dates on notes
  - `.xlsx` import
  - full tenant export and purge
  - impersonation
  - entitlement enforcement
  - charts and P1 metrics (run-rate, renewal rate, churn, collection rate)
  - audit viewer
  - owner 2FA
  - file attachments
  - terminology labels
  - per-customer message language
- **P2:**
  - customer portal
  - online payments and webhooks
  - Telegram bot
  - WhatsApp
  - public API and OpenAPI
  - mobile app
  - automated SaaS billing
  - session-pack plans
  - multi-currency within a tenant
  - customer lifetime metrics
  - Afaan Oromoo UI

### 13.3 REMOVE

- TanStack Query, React Hook Form, Zod (MVP stack)
- the 14-module Domain tree and the Renewals module
- the repository pattern and interfaces (except `SmsGateway`)
- the custom notification framework
- `PlanPrice`, `PlanFeature`, `Reminder` and `AttendanceRecord` entities
- stored time-driven statuses (DUE, OVERDUE, PARTIALLY_PAID, scheduled, expired)
- draft invoices
- payment editing
- proration
- scheduled reports
- subscription-number search
- the `first_name`/`last_name` split
- 30-day and 14-day default reminders
- server-side PDF generation (MVP)
- historical payment import
- custom roles
- coupons
- access-control hardware
- class scheduling
- inventory/POS
- AI assistant and churn prediction (until validated)
- industry module tree (until validated)
- consulting, agency, MSP and internet-reseller segments (initial target)
- subdomain-per-tenant (MVP)
- Inertia SSR
- the 25-document pre-code suite

### 13.4 DECIDE (product, business and legal decisions before coding)

| ID | Decision | Recommended default | Blocks |
|---|---|---|---|
| D1 | Beachhead segment | Membership/prepaid: gyms and fitness studios, training centres, coworking (Addis Ababa) | Defaults, templates, pilots |
| D2 | Hosting location and legal position under Proclamation 1321/2024, including backups, email relay, SMS and error tracking | Get Ethiopian legal advice; prefer an Ethiopian host if localisation applies | **Hosting procurement** |
| D3 | Are FufaFlow documents tax invoices? Tax in MVP? | No; "payment record / receipt"; tax in P1 (single rate) | Invoice and receipt design |
| D4 | SaaS pricing hypothesis, trial length, how Fufa collects fees, past-due grace | 30-day trial; manual collection; 7-day past-due grace; price from interviews | Tenant status logic |
| D5 | Date conventions: "covered until" definition, default grace, renewal-after-expiry rule, reminder defaults | Day before the same date next month; 3 days; restart on payment date; −3/+1/+1/+7 | PeriodCalculator, reminders |
| D6 | Ethiopian-calendar display in MVP | Validate; default P1 dual display | UI dates |
| D7 | MVP customer-messaging channels and default template language | Email plus assisted messaging; templates editable; Amharic default text if pilots confirm | Notification work |
| D8 | Data retention periods (financial, audit, messages, backups) | Counsel to advise; placeholders 7y / 7y / 12m / 6m | Pruning jobs |
| D9 | Tenant offboarding (export window, purge timing) | 60 days read-only, then purge | Platform ops |
| D10 | Single currency per tenant | Yes | Schema |
| D11 | Overpayment and credit in MVP | No (multi-period renewal instead) | Payment UI |
| D12 | Platform admin UI technology | Minimal Inertia pages (no Filament) | Admin console |
| D13 | Laravel 13 vs 12 | Laravel 13 (pick a host with PHP 8.3 or later) | Scaffold |
| D14 | Primary message: "Keep Business Going" vs "Keep customers, payments, and renewals flowing" | Tagline vs product description; confirm usage | Copy |

### 13.5 VALIDATE (with real businesses before or while implementing)

Run 8–12 interviews in the chosen segment, plus 3–5 pilots. Collect **5 real spreadsheets or notebooks** of member records.

1. Rank the pains: renewal tracking vs collecting debts vs staff cash control vs reminding customers.
2. **Channel reach:** how they contact customers today (calls, SMS, Telegram, WhatsApp, email), and what share of customers have email.
3. Do they record dates in the **Ethiopian calendar**? Does "monthly" mean a calendar month or 30 days?
4. Typical plan structures: monthly, quarterly, annual; registration fees; family or couple plans; session packs; freezes.
5. How often they see advance payments, partial payments, refunds and freezes.
6. Do staff collect cash? Do they share logins or devices? Is the owner present?
7. Devices (phone or desktop), internet reliability, printers (A4 or thermal).
8. Do B2B customers expect formal invoices? (Only if B2B is in scope.)
9. Willingness to pay (ETB per month) and preferred way to pay Fufa.
10. Number of branches and staff.
11. Whether the UI needs to be in Amharic for staff.
12. Usability test: first receipt in under 10 minutes, unassisted.

**Immediate next steps:**

1. Decide D1–D7, starting with legal advice on D2.
2. Run interviews and collect spreadsheets, in parallel with step 3.
3. Procure hosting against §9.5 and run `hosting-check`.
4. Edit the V2 draft with the decisions and adopt it as the master prompt.
5. Build the walking skeleton.
6. Build the P0 slices in V2 order.

---

## 14. Final verdict

### 14.1 Scorecard

| Dimension | Score | Explanation (every score is below 8) |
|---|---|---|
| Business clarity | **5** | The problem and the core questions are clear and well put. But there is no beachhead, two business models are conflated, and the positioning lines compete. |
| MVP focus | **4** | The non-goals are good. Against that: 21 must-haves plus "should haves" that creep in, about 25 documents before code, and scope features (branches, portal, audit UI, many reports) that don't serve the first sale. |
| Domain completeness | **4** | The entities are listed, but renewal is undefined, the state models contradict each other, cycles are duplicated, and there is no price snapshot, numbering, opening balance or immutability rule. |
| Architecture quality | **6** | The macro choices are right (monolith, Inertia, shared DB). The tenancy mechanism is deferred, the 14 modules are over-modularised, and the notification layer duplicates Laravel. |
| Laravel fit | **7** | Good instincts (Form Requests, Policies, migrations, no repositories), contradicted by the Domain tree and the custom notification framework. |
| React/Inertia fit | **6** | Inertia is the right call, undermined by three redundant libraries, nine navigation items, and no prop-shaping or date-handling rules. |
| Database quality | **5** | Principles only: no index plan, composite tenant constraints, numbering, deletion or enum strategy, and no MySQL/MariaDB portability rules. |
| Security | **6** | A broad checklist and a correct "never trust IDs" instinct. It misses validation-rule IDOR, template injection, CSV injection, prop exposure, messaging abuse and admin 2FA. |
| Multi-tenancy | **5** | The right model, but the mechanism is deferred, with no fail-closed rule, no job/command/cache/file rules, no tenant resolution and no membership model. |
| Shared-hosting compatibility | **6** | Unusually aware of the constraints. But the "may have" list is never turned into requirements, and it misses the CLI PHP path, `proc_open`, cron granularity, `pcntl`, SMTP limits, inodes, artifact deploys and legal location. |
| Operational readiness | **4** | Backups and logs are mentioned. There is no monitoring of cron or queue, no admin tooling spec, no tenant lifecycle, no runbooks and no staging. |
| Testability | **6** | Good test lists, but no clock control, no date-math tables, no systematic isolation harness, and a domain that isn't deterministic enough to test against. |
| Internationalisation readiness | **6** | Currency, timezone and locale awareness are there. Missing: currency exponents, Ethiopian calendar, phone normalisation, name model, Ethiopic fonts, SMS encoding, data residency per market. |
| Commercial readiness | **3** | No pricing hypothesis, no trial or SaaS account model, no onboarding or sales motion, and a validation plan that only lists segments. |
| **Overall readiness** | **4** | Strong intent, but key decisions are missing or contradictory. Building now would bake ambiguity into the schema. |

### 14.2 Verdict

> **Is the specification ready for implementation?**
>
> **NOT READY**

The current master prompt should not be handed to an implementer. The defects are fixable mostly by *subtraction and decision* rather than by adding features. The V2 draft (`FUFUFLOW-MASTER-PROMPT-V2-DRAFT.md`) resolves the domain, architecture, tenancy, hosting and scope issues, with explicit defaults. Once decisions D1–D7 are confirmed (D2 needs legal input) and the first interviews have run, the V2 prompt can serve as the basis for implementation.

---

### Sources consulted for external facts

- Laravel 13 release date, PHP 8.3 minimum and support window: [Laravel News: Laravel 13](https://laravel-news.com/laravel-13), [Laravel PHP compatibility matrix](https://itmarkerz.co.in/blog/laravel-php-compatibility-matrix)
- Ethiopia Personal Data Protection Proclamation No. 1321/2024: [Ministry of Justice text](https://justice.gov.et/en/law/personal-data-protection-proclamation/), [ECA PDF](https://eca.et/wp-content/uploads/2024/10/personal_data_protection_proclamation_No_1321_2024.pdf), [Digital Policy Alert: data localisation](https://digitalpolicyalert.org/change/12100-data-localisation-requirements-in-personal-data-protection-proclamation-proclamation-no-13212024), [Digital Policy Alert: data transfer](https://digitalpolicyalert.org/event/24914-adopted-personal-data-protection-proclamation-proclamation-no-13212024-including-data-transfer-regulation), [Michalsons overview](https://www.michalsons.com/blog/the-ethiopian-data-protection-law/75488)

These sources inform the legal questions only. They are not legal advice.
