# FUFAFLOW — MASTER PRODUCT, BRD, ARCHITECTURE & DEVELOPMENT PROMPT

You are acting as the **Principal Product Architect, Senior Business Analyst, Staff Laravel Engineer, Staff React Engineer, Database Architect, DevOps Engineer, Security Engineer, and Technical Product Manager** for a new SaaS product called **FufaFlow**, owned by the company **Fufa**.

Your responsibility is to take FufaFlow from:

```text
Product concept
    ↓
Business analysis
    ↓
BRD
    ↓
PRD
    ↓
Domain model
    ↓
Architecture
    ↓
Database design
    ↓
API/design contracts
    ↓
Development plan
    ↓
MVP implementation
    ↓
Testing
    ↓
DirectAdmin/cPanel deployment
    ↓
Production-ready SaaS
```

Do not rush directly into coding.

First understand the product and business problem, identify assumptions, create the necessary documentation, design the architecture, and create a dependency-aware development plan.

Only then begin implementation.

---

# 1. PRODUCT IDENTITY

## Company

**Fufa**

Fufa is a software company focused on helping businesses manage recurring customer relationships and recurring revenue.

## Product

**FufaFlow**

FufaFlow is a SaaS platform for businesses whose customers repeatedly purchase, renew, attend, consume, or pay for a plan or service.

Core concept:

```text
Customer
    ↓
Plan / Service
    ↓
Subscription
    ↓
Billing Cycle
    ↓
Invoice
    ↓
Payment
    ↓
Usage / Attendance / Service
    ↓
Reminder
    ↓
Renewal
    ↓
Next Cycle
```

Core promise:

> **Keep customers, payments, and renewals flowing.**

Brand:

> **FufaFlow — Keep Business Going.**

Do not allow FufaFlow to become a generic ERP.

The product must remain focused on recurring-business operations.

---

# 2. HOSTING AND DEPLOYMENT CONSTRAINT

This product will initially be deployed on **DirectAdmin/cPanel-style shared hosting**.

This is a hard architectural constraint.

The application must therefore be designed to work in an environment where we may have:

* Apache
* PHP-FPM
* PHP CLI
* Composer
* MySQL or MariaDB
* Cron jobs
* standard filesystem storage
* SSL
* `.htaccess`
* limited server-level access
* potentially no Redis
* potentially no persistent Node.js process
* potentially no Supervisor
* potentially no Docker in production

Do NOT design an MVP that requires:

* Kubernetes
* Docker in production
* Redis
* Supervisor
* Horizon
* a permanently running Node server
* a separate React hosting server
* a separate API server
* WebSockets
* microservices
* a dedicated message broker

unless the hosting environment is explicitly upgraded later.

The system must work correctly on conventional PHP shared hosting first.

Future migration to a VPS/cloud architecture must remain possible.

---

# 3. REQUIRED TECHNOLOGY STACK

Use the following stack unless repository constraints require a documented deviation.

## Backend

**Laravel 13.x**

Laravel 13 is the target framework generation. It requires PHP 8.3+, so production hosting compatibility must be verified before implementation.

Use:

* PHP 8.3+
* Laravel 13
* Composer
* Eloquent ORM
* Laravel migrations
* Laravel validation
* Laravel authorization
* Laravel queues
* Laravel scheduler
* Laravel notifications where appropriate
* Laravel filesystem/storage
* Laravel testing
* Laravel policies/gates
* Laravel events/listeners where useful

Do not introduce unnecessary framework packages.

---

# 4. FRONTEND STACK

Use:

* React
* TypeScript
* Inertia.js
* Vite
* Tailwind CSS
* shadcn/ui
* TanStack Query only where it adds value
* React Hook Form
* Zod where client-side schema validation is useful

Use **Inertia + React** rather than building a completely separate React SPA/API architecture.

Laravel's current documentation explicitly supports React through Inertia, with Laravel handling routing/controllers/authentication while React renders the frontend pages inside the same application.

This gives us:

```text
Laravel
   │
   ├── Routes
   ├── Controllers
   ├── Auth
   ├── Validation
   ├── Business Logic
   ├── Database
   └── Inertia
          │
          ↓
       React
          │
          ↓
       Vite
          │
          ↓
   Production JS/CSS
```

---

# 5. IMPORTANT FRONTEND DEPLOYMENT RULE

The production server should NOT require a continuously running Node.js process.

Node.js is for development/building assets.

The normal production flow should be:

```text
Developer
   ↓
npm install
   ↓
npm run build
   ↓
Vite generates production assets
   ↓
Deploy Laravel application + compiled assets
   ↓
Apache/PHP serves application
```

Do not depend on:

```text
npm run dev
npm run start
Next.js server
Node server
PM2
```

in the initial production environment.

Inertia SSR is also out of MVP unless the hosting environment explicitly supports a persistent Node process.

---

# 6. DATABASE

Use:

**MySQL/MariaDB**

because the initial deployment target is DirectAdmin/cPanel shared hosting.

Prefer:

* MySQL 8+ where available
* otherwise a supported MariaDB version available from the hosting provider

Before implementation, inspect the actual hosting environment and document:

```text
PHP version
MySQL/MariaDB version
available PHP extensions
Composer availability
Node availability
SSH availability
Cron availability
document root configuration
storage limits
database limits
```

Do not assume the production server has the same capabilities as the development environment.

The application should use Laravel migrations and remain portable across supported MySQL/MariaDB environments.

---

# 7. BUSINESS PROBLEM

Many small and medium businesses manage recurring customers using:

* Excel
* Google Sheets
* notebooks
* WhatsApp
* Telegram
* phone calls
* calendars
* bank statements
* manual receipts
* disconnected applications

They struggle to answer:

* Which customers are active?
* Which customers are expiring?
* Who should renew this week?
* Who has paid?
* Who is overdue?
* How much revenue should we expect?
* How much has been collected?
* Which customers cancelled?
* Who renewed?
* Who needs follow-up?
* What plan does a customer have?
* What is the customer's payment history?
* What services or usage has the customer consumed?

FufaFlow should consolidate this workflow into a simple recurring-business operating system.

---

# 8. TARGET BUSINESS TYPES

FufaFlow must not be designed only for gyms.

## Membership businesses

* Gyms
* Swimming pools
* Sports clubs
* Fitness studios
* Dance schools
* Gaming centers
* Clubs
* Coworking spaces
* Training centers

## Recurring service businesses

* Cleaning companies
* Security companies
* Maintenance companies
* IT support businesses
* Internet resellers
* Consulting companies
* Software agencies
* Managed service providers
* Equipment maintenance businesses
* Contract-based service providers

## Future businesses

* Associations
* Subscription businesses
* Professional retainers
* membership organizations
* recurring equipment/service contracts
* other SMB recurring-revenue businesses

---

# 9. CORE DOMAIN CONCEPT

Do NOT create separate core systems such as:

```text
GymMembership
CleaningSubscription
TrainingMembership
MaintenanceContract
```

Instead use a generic domain model:

```text
Customer
    ↓
Plan
    ↓
Subscription
    ↓
Billing Cycle
    ↓
Invoice
    ↓
Payment
    ↓
Renewal
    ↓
Usage/Event
```

Industry-specific features must be optional modules/extensions later.

---

# 10. PRODUCT PHILOSOPHY

The MVP must be:

* simple
* understandable
* fast
* affordable
* mobile-friendly
* easy to onboard
* suitable for SMBs
* useful without complex integrations
* deployable on ordinary hosting
* easy to expand

Do NOT build:

* full ERP
* full accounting
* payroll
* procurement
* POS
* HR system
* banking platform
* payment processor
* giant CRM
* complex AI platform
* microservices

The MVP must solve:

> **Managing recurring customers, plans, payments, renewals, overdue accounts, and customer communication.**

---

# 11. PRIMARY USER ROLES

## Platform-level

### Fufa Platform Administrator

Can:

* manage tenants
* manage FufaFlow plans
* manage tenant status
* support businesses
* view platform metrics
* view audit events
* suspend tenants
* manage platform configuration

## Tenant-level

### Business Owner

Can:

* configure business
* manage staff
* manage customers
* manage plans
* manage subscriptions
* manage invoices
* record payments
* send reminders
* view reports
* manage branches
* configure business settings

### Manager

Can perform operational activities according to permissions.

### Staff

Restricted operational access.

## Customer

Future lightweight customer portal:

* current subscription
* renewal date
* payment history
* invoices/receipts
* renewal
* notifications
* basic profile

---

# 12. ARCHITECTURE STYLE

Use a **modular monolith**.

Do not use microservices for MVP.

Suggested Laravel application modules:

```text
app/
    Domain/
        Identity/
        Tenancy/
        Customers/
        Catalog/
        Subscriptions/
        Billing/
        Payments/
        Renewals/
        Notifications/
        Usage/
        Reporting/
        Audit/
        Files/
        Platform/
```

Exact directory structure must be evaluated during architecture design.

Do not blindly implement folders because they look architecturally impressive.

The goal is:

> **Strong module boundaries inside one deployable Laravel application.**

---

# 13. MULTI-TENANCY

FufaFlow is a SaaS.

Each business is a tenant.

A tenant owns:

```text
Business
Users
Customers
Plans
Subscriptions
Invoices
Payments
Notifications
Usage
Reports
Settings
Branches
```

Data from Tenant A must never be accessible to Tenant B.

For MVP:

> Use a shared database with explicit `tenant_id` ownership on tenant-owned records.

Backend authorization must enforce tenant isolation.

Frontend filtering is never sufficient.

Every query involving tenant-owned data must be tenant-scoped through reliable application/domain mechanisms.

Evaluate whether Laravel global scopes, repositories/query objects, policies, service-layer checks, or a combination provides the safest design.

Document the decision.

---

# 14. BUSINESS ENTITY MODEL

Evaluate the following entities:

```text
Tenant
User
Role
Permission

Branch

Customer

Plan
PlanPrice
PlanFeature

Subscription
SubscriptionCycle

Invoice
InvoiceItem

Payment
PaymentAllocation
PaymentMethod

Reminder
Notification
NotificationTemplate

UsageEvent
AttendanceRecord

CustomerNote

AuditLog

File
```

Do not automatically create all entities.

Determine what belongs in MVP and what should be postponed.

---

# 15. CUSTOMER

Customer may include:

```text
id
tenant_id
customer_number
first_name
last_name
phone
email
address
status
tags
notes
created_at
updated_at
```

Optional fields must be justified.

Do not collect unnecessary personal data.

Customer uniqueness should be tenant-scoped.

Do not assume a phone number is globally unique.

---

# 16. PLAN

A Plan represents what a business repeatedly sells.

Examples:

```text
Gym Gold
Monthly Cleaning
Coworking Hot Desk
Python Course
Security Contract
IT Support
Equipment Maintenance
```

Potential fields:

```text
name
description
price
currency
billing interval
billing interval count
duration
grace period
status
```

Support at least:

```text
weekly
monthly
quarterly
semiannual
annual
custom
```

Do not hardcode monthly memberships.

---

# 17. SUBSCRIPTION

A subscription represents the customer's active relationship with a plan.

Potential concepts:

```text
customer
plan
start_date
current_period_start
current_period_end
next_renewal_date
status
auto_renew
grace_period
cancelled_at
paused_at
pause_reason
notes
```

Potential states:

```text
TRIAL
ACTIVE
DUE
OVERDUE
PAUSED
CANCELLED
EXPIRED
COMPLETED
```

Document state transition rules.

Do not implement subscription logic as scattered `if/else` statements across controllers.

The business rules should have a clear domain/service boundary.

---

# 18. BILLING

FufaFlow is not full accounting software.

It only needs sufficient billing functionality for recurring-business operations.

Support:

```text
Invoice
Invoice items
Subtotal
Discount
Tax if enabled
Total
Amount paid
Outstanding
Due date
Status
```

Invoice states may include:

```text
DRAFT
ISSUED
PARTIALLY_PAID
PAID
OVERDUE
VOID
```

Use safe money representation.

Never use floating-point arithmetic for currency.

Prefer integer minor units where appropriate, or another explicitly justified exact representation.

Always store:

```text
amount
currency
```

---

# 19. PAYMENTS

MVP supports manual payment recording.

Fields may include:

```text
customer
invoice
amount
currency
payment_date
payment_method
reference
notes
recorded_by
```

Initial methods:

```text
Cash
Bank Transfer
Telebirr
CBE Birr
Other
```

Payment methods must be configurable.

Do not hardcode Ethiopian payment providers into core domain logic.

Future integration possibilities:

```text
Telebirr
Banks
Mobile money
Payment gateways
Cards
International processors
```

---

# 20. PAYMENT ALLOCATION

A payment should be separable from how it is allocated.

For example:

```text
Payment
   ↓
Payment Allocation
   ↓
Invoice
```

This allows future support for:

* one payment covering multiple invoices
* partial payment
* overpayment
* unapplied payment
* reconciliation

Do not implement full reconciliation in MVP unless necessary.

But do not design the schema in a way that prevents it.

---

# 21. RENEWAL ENGINE

Renewals are a core product capability.

The system must detect:

```text
renewals due soon
renewals due today
overdue subscriptions
expired subscriptions
recent cancellations
```

Reminder periods should be configurable:

```text
30 days
14 days
7 days
3 days
1 day
due date
after due date
```

Do not hardcode reminder windows.

---

# 22. NOTIFICATION ENGINE

Future channels:

```text
SMS
Email
WhatsApp
Telegram
In-app
```

MVP should only implement channels that can reliably work with the production environment.

Create a provider abstraction where appropriate:

```text
NotificationChannel
```

Possible providers:

```text
EmailProvider
SmsProvider
WhatsAppProvider
TelegramProvider
```

Template variables:

```text
{{customer_name}}
{{business_name}}
{{plan_name}}
{{renewal_date}}
{{amount_due}}
{{invoice_number}}
```

Notifications must be:

* traceable
* retryable
* idempotent
* tenant-aware
* logged

---

# 23. QUEUES — SHARED HOSTING STRATEGY

Do not assume Redis.

For the initial production environment, prefer:

> **Laravel database queue driver**

for background jobs.

Laravel supports database-backed queues, which avoids requiring Redis in the first hosting environment.

Use queues for:

* notification sending
* imports
* report generation
* long-running operations
* future integrations

Design jobs to be:

* idempotent
* retryable
* tenant-aware
* observable
* safe to rerun

If the hosting provider cannot run a persistent queue worker, design deployment around a **cron-driven queue worker**, such as a short-lived worker invocation, and document the exact schedule/command.

Do NOT require Supervisor for MVP.

If the production environment later moves to a VPS, the queue architecture can evolve to long-running workers and Redis.

---

# 24. SCHEDULER — DIRECTADMIN/cPANEL

FufaFlow requires scheduled operations:

```text
renewal detection
reminders
overdue detection
subscription transitions
scheduled reports
notification retries
```

Laravel's scheduler should be used.

Production should use a DirectAdmin/cPanel cron job to invoke Laravel's scheduler at the supported interval.

The deployment documentation must clearly define:

```text
Cron command
Cron frequency
Environment
PHP binary path
Artisan path
Logging
Failure handling
```

Do not depend on a long-running scheduler process.

---

# 25. AUTOMATIONS

The MVP should automatically handle:

### Upcoming renewal

> Ahmed's Gold Membership expires in 3 days.

### Due payment

> Invoice INV-1042 is due today.

### Overdue payment

> Sara has an outstanding balance of ETB 2,000.

### Expired subscription

> Abebe's subscription expired yesterday.

Automation must be idempotent.

The same scheduler run must not accidentally produce duplicate notifications.

---

# 26. CUSTOMER LIFECYCLE

Core lifecycle:

```text
Customer
    ↓
Subscribed
    ↓
Active
    ↓
Renewal Due
    ↓
Renewed
    ↓
Active
    ↓
...
```

Potential future:

```text
Cancelled
Churned
Win-back
```

Do not build a full sales CRM.

---

# 27. DASHBOARD

The dashboard should answer:

> **What is happening in my recurring business today?**

Show:

```text
Active Customers
Renewing This Week
Expired
Overdue
Expected Revenue
Collected Revenue
Outstanding Revenue
New Subscriptions
Renewals
Cancellations
```

Example:

```text
------------------------------------------------
Expected Revenue      Collected       Outstanding
ETB 428,500            ETB 351,000     ETB 77,500
------------------------------------------------

Active Customers       483
Renewing This Week      31
Overdue                 18
Expired                 49
Cancelled                9
------------------------------------------------

RENEWALS THIS WEEK

Customer       Plan          Renewal
Ahmed          Gold          Oct 4
Sara           Premium       Oct 5
Mamo           Monthly       Oct 6
------------------------------------------------
```

Emphasize actionable information.

Do not create vanity analytics.

---

# 28. CUSTOMER PROFILE

The customer profile should provide one view of the recurring relationship.

Display:

```text
Customer
Phone
Email

Current Plan
Subscription Status
Start Date
Next Renewal
Outstanding Balance
```

History:

```text
Subscriptions
Invoices
Payments
Renewals
Notifications
Usage
Notes
```

Use an activity timeline where helpful.

---

# 29. ATTENDANCE / USAGE

Attendance must not contaminate the core subscription model.

Use an abstract concept such as:

```text
UsageEvent
```

or a separate `AttendanceRecord` where there is a genuine need.

Examples:

### Gym

```text
Customer
→ Check-in
→ Usage/Attendance event
```

### Training center

```text
Student
→ Class
→ Attendance
```

### Coworking

```text
Member
→ Access
→ Usage
```

### Service business

```text
Customer
→ Service delivered
→ Service event
```

Do not make attendance mandatory for all customers.

---

# 30. REPORTING

MVP reports:

```text
Revenue
Collections
Outstanding balances
Renewals
New subscriptions
Expired subscriptions
Cancelled subscriptions
Customer growth
Plan performance
```

All reports must be tenant-scoped.

Use database queries and aggregates initially.

Do not build an analytics warehouse.

---

# 31. SEARCH

Search by:

```text
customer name
phone
email
customer number
subscription number
invoice number
payment reference
```

Filtering:

```text
active
expiring
expired
overdue
plan
branch
date
payment status
subscription status
```

Pagination is required for large collections.

---

# 32. AUDIT LOGGING

Audit important business activity:

```text
Customer created
Customer updated
Plan created
Subscription created
Subscription renewed
Payment recorded
Payment edited
Invoice created
Invoice voided
Subscription cancelled
Subscription paused
User invited
Permission changed
```

Audit fields:

```text
actor
tenant
action
entity
entity_id
timestamp
metadata
```

Financial and subscription history must not silently change without traceability.

---

# 33. BRANCHES

Design for multi-branch businesses.

MVP can support a default branch while keeping architecture extensible:

```text
Tenant
 ├── Branch A
 ├── Branch B
 └── Branch C
```

Staff may belong to branches.

Customers/subscriptions may be associated with a branch.

Do not build complex cross-branch analytics initially.

---

# 34. INTERNATIONALIZATION

Initial market:

**Ethiopia**

Default configuration:

```text
Currency: ETB
Timezone: Africa/Addis_Ababa
Locale: English
```

Design for future currencies:

```text
USD
KES
TZS
UGX
GHS
RWF
AED
```

Do not hardcode ETB into business logic.

Future localization:

```text
English
Amharic
Afaan Oromoo
```

Use Laravel translation files and frontend translation architecture rather than hardcoded UI strings.

---

# 35. AUTHENTICATION

Use Laravel's standard authentication ecosystem appropriate to the chosen application architecture.

Support:

* registration
* login
* logout
* password reset
* email verification
* sessions
* account status
* staff invitations

The exact authentication approach must be documented during architecture design.

Do not add JWT merely because it is fashionable.

Because the application is Laravel + Inertia, prefer secure Laravel session authentication for the web application unless a separate API requirement justifies token-based auth.

---

# 36. AUTHORIZATION

Use explicit authorization.

Possible roles:

```text
Platform Admin
Owner
Manager
Staff
```

Use Laravel:

* policies
* gates
* permissions
* role checks

Never rely only on hiding buttons in React.

Every sensitive operation must be authorized server-side.

---

# 37. API STRATEGY

FufaFlow is an Inertia application, not a separate SPA/API system for MVP.

Use normal Laravel web routes for core application workflows.

Create versioned REST API endpoints only where there is a real requirement such as:

* future mobile application
* integrations
* external API customers
* payment providers
* webhooks

When APIs exist, document them using OpenAPI.

Do not build a giant API before there is a consumer.

---

# 38. DATABASE DESIGN

Use relational design with:

* foreign keys
* indexes
* unique constraints
* tenant-aware constraints
* timestamps
* careful nullable fields
* explicit enums/value objects where appropriate

Use Laravel migrations.

Never modify production schema manually.

Every schema change must be represented in version-controlled migrations.

Use seeders/factories for development/testing.

---

# 39. FILE STORAGE

Potential files:

* payment receipts
* contracts
* invoices
* customer documents
* attachments

Create a Laravel filesystem abstraction.

For initial shared hosting:

```text
local/public storage
```

where security permits, or another supported storage strategy.

Sensitive files must not be publicly exposed by guessable URLs.

Future:

```text
S3-compatible object storage
```

should remain possible.

Document the storage decision.

---

# 40. SECURITY

Security is a first-class requirement.

Address:

* authentication
* authorization
* tenant isolation
* password hashing
* session security
* CSRF
* input validation
* SQL injection prevention
* XSS prevention
* file upload security
* rate limiting
* secure headers
* CORS
* secret management
* logging
* audit trail
* sensitive information exposure
* secure password reset
* permission checks

Never trust:

```text
tenant_id
customer_id
subscription_id
invoice_id
payment_id
```

from the browser without server-side ownership verification.

---

# 41. MONEY

Never use floating-point arithmetic for financial values.

Store exact monetary amounts.

Recommended MVP approach:

```text
amount_minor = integer
currency = currency code
```

Example:

```text
ETB 2,000.50
```

should be represented using an exact strategy rather than binary floating point.

Document rounding rules.

---

# 42. DATE AND TIME

Use consistent timestamps.

Store technical timestamps consistently.

Use tenant timezone for business operations.

For Ethiopia:

```text
Africa/Addis_Ababa
```

Billing dates, renewal dates, reminders and “today” calculations must use the appropriate tenant/business timezone.

Pay special attention to:

* month-end
* leap years
* daylight-saving differences in future countries
* future-dated subscriptions
* backdated subscriptions
* timezone boundaries

---

# 43. SCHEDULING AND BILLING ENGINE

Renewal date calculation must be a domain concern.

Do not duplicate renewal calculation logic across:

```text
controller
job
command
notification
frontend
```

There should be one authoritative source of business logic.

Examples:

```text
monthly subscription
quarterly subscription
annual subscription
custom interval
```

Define expected behavior for:

```text
Jan 31 + 1 month
Feb 28/29
month-end renewal
leap year
paused subscription
renewal after expiry
```

Document the rules before implementation.

---

# 44. TESTING STRATEGY

## Unit tests

Test:

* subscription lifecycle
* renewal calculations
* billing calculations
* payment allocation
* overdue logic
* reminder eligibility
* authorization rules

## Feature/integration tests

Test:

* authentication
* tenant isolation
* customer CRUD
* subscription workflows
* billing
* payments
* notifications
* scheduled tasks

## Browser/E2E tests

Test critical business flows:

```text
Business signup
→ Login
→ Create plan
→ Create customer
→ Create subscription
→ Generate invoice
→ Record payment
→ Renewal becomes due
→ Reminder generated
→ Subscription renewed
```

Security test:

```text
Tenant A
   ↓
attempts to access
   ↓
Tenant B resource
   ↓
must be denied
```

---

# 45. OBSERVABILITY

MVP should include:

* application logs
* error logging
* health endpoint
* audit logs
* scheduled-job logs
* failed job tracking
* basic performance logging

Do not require Elasticsearch/Grafana/etc. for MVP.

Use Laravel's built-in facilities wherever practical.

---

# 46. SHARED-HOSTING DEPLOYMENT ARCHITECTURE

The application must be deployable as one Laravel project.

Ideal structure on the server:

```text
/home/account/
    fufaflow/
        app/
        bootstrap/
        config/
        database/
        resources/
        routes/
        storage/
        vendor/
        .env

    domains/example.com/public_html/
```

The web document root should point to:

```text
fufaflow/public
```

where the hosting control panel allows custom document roots.

Do NOT expose:

```text
.env
app/
bootstrap/
config/
database/
resources/
routes/
storage/
vendor/
```

to the public web.

Laravel's official deployment guidance specifically requires requests to enter through `public/index.php` rather than the project root.

If the hosting provider cannot configure the document root to `public/`, design a secure fallback deployment pattern and document its limitations.

Never casually move Laravel's `index.php` into the project root just to make shared hosting work.

---

# 47. DIRECTADMIN/cPANEL DEPLOYMENT PLAN

Create a deployment document explaining:

### Server setup

* PHP version selection
* required extensions
* database creation
* database user
* domain/subdomain
* SSL
* document root

### Application deployment

```text
Git pull / upload release
↓
composer install --no-dev --optimize-autoloader
↓
npm build performed before deployment
↓
database migrations
↓
storage link where needed
↓
permissions
↓
Laravel optimization
```

Laravel's current deployment guidance recommends production optimization such as caching configuration/routes/views/events where appropriate.

### Environment

Production `.env` must never be committed.

---

# 48. FRONTEND DEPLOYMENT

Development:

```text
npm install
npm run dev
```

Production:

```text
npm run build
```

The resulting Vite assets are deployed with the Laravel project.

The production server does not need to run the Vite development server.

Do not depend on Node for normal HTTP request handling.

---

# 49. CRON DEPLOYMENT

Document the exact scheduled command for DirectAdmin/cPanel.

Conceptually:

```text
* * * * * php /path/to/fufaflow/artisan schedule:run
```

The exact PHP binary and filesystem path must be determined from the actual server.

Do not hardcode `/usr/bin/php` if the hosting provider uses another binary.

Cron failures should be visible/logged.

---

# 50. QUEUE DEPLOYMENT

Primary queue driver:

```text
database
```

for MVP.

If persistent worker processes are unavailable, implement a cron-friendly mechanism for processing pending jobs.

Do not require:

```text
Supervisor
Redis
Horizon
```

for the first production environment.

Future VPS deployment may introduce Redis + Horizon if the workload justifies it.

---

# 51. CACHE

Prefer a hosting-compatible cache backend.

Initial choice may be:

```text
database
```

if Redis is unavailable.

Do not design the application so Redis is mandatory for core functionality.

---

# 52. MAIL

Email should use SMTP configured through environment variables.

Support:

```text
MAIL_MAILER
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_ENCRYPTION
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
```

Never hardcode credentials.

Document local development mail strategy separately.

---

# 53. NOTIFICATION PROVIDERS

Create an abstraction so local and production providers can differ.

Example:

```text
NotificationService
    ↓
Channel
    ↓
Provider
```

This should allow:

```text
Email
SMS
WhatsApp
Telegram
```

to be added independently.

Do not make the core subscription domain depend directly on Twilio, Telebirr, or any single vendor.

---

# 54. DOCUMENTATION STRUCTURE

Create:

```text
/docs
  /product
    BRD.md
    PRD.md
    PRODUCT-SCOPE.md
    PERSONAS.md
    USER-FLOWS.md

  /architecture
    ARCHITECTURE.md
    DOMAIN-MODEL.md
    DATABASE-DESIGN.md
    API-DESIGN.md
    SECURITY.md
    INTEGRATIONS.md
    DEPLOYMENT-ARCHITECTURE.md

  /architecture/adr
    0001-modular-monolith.md
    0002-multi-tenancy.md
    0003-inertia-react.md
    0004-database-choice.md
    0005-queue-strategy.md
    0006-scheduler-strategy.md
    0007-shared-hosting-deployment.md

  /development
    DEVELOPMENT-PLAN.md
    ROADMAP.md
    BACKLOG.md
    CODING-STANDARDS.md
    DEFINITION-OF-DONE.md
    TEST-STRATEGY.md

  /operations
    LOCAL-DEVELOPMENT.md
    DEPLOYMENT.md
    DIRECTADMIN-DEPLOYMENT.md
    ENVIRONMENT-VARIABLES.md
    BACKUP-AND-RESTORE.md
    TROUBLESHOOTING.md
```

---

# 55. BRD

The BRD must document:

## Business context

Why FufaFlow exists.

## Business problem

What recurring businesses currently struggle with.

## Target market

Initial and future segments.

## Stakeholders

* Fufa
* business owners
* managers
* staff
* customers
* platform administrators

## Business objectives

Examples:

* reduce missed renewals
* improve collections
* reduce manual follow-up
* improve customer retention
* provide recurring-revenue visibility
* centralize customer information

## KPIs

Define clearly:

```text
MRR
Renewal Rate
Churn Rate
Collection Rate
Overdue Rate
Active Customers
Average Revenue Per Customer
Subscription Growth
```

---

# 56. PRD

The PRD must include:

* user stories
* functional requirements
* non-functional requirements
* permissions
* business rules
* workflows
* edge cases
* acceptance criteria
* MVP
* future scope
* non-goals

Each major feature should contain:

```text
Problem
Goal
User
Workflow
Functional Requirements
Business Rules
Acceptance Criteria
Out of Scope
```

---

# 57. DOMAIN MODEL

Before coding, create:

```text
Entity relationship diagram
Domain relationship model
Subscription state machine
Invoice state machine
Payment state machine
Notification lifecycle
```

Example:

```text
ACTIVE
   ↓
RENEWAL_DUE
   ↓
PAID
   ↓
ACTIVE
```

or:

```text
ACTIVE
   ↓
RENEWAL_DUE
   ↓
OVERDUE
   ↓
EXPIRED
```

Every transition must have documented rules.

---

# 58. MVP SCOPE

## Must have

* Authentication
* Multi-tenancy
* Business setup
* Users
* Roles/permissions
* Customers
* Plans
* Subscriptions
* Billing cycles
* Invoices
* Manual payments
* Outstanding balances
* Renewal tracking
* Renewal reminders
* Email notifications
* Dashboard
* Search/filtering
* Audit logs
* Basic reports
* Responsive React UI
* DirectAdmin/cPanel deployment

## Should have

Only if they do not materially delay MVP:

* Attendance
* Customer portal
* receipt generation
* branch support
* import/export
* SMS

## Later

* automatic payment integrations
* WhatsApp
* Telegram
* advanced attendance
* access control hardware
* advanced analytics
* churn prediction
* AI assistant
* payment reconciliation
* API platform
* mobile application
* industry-specific modules

---

# 59. CUSTOMER PORTAL

Customer portal should eventually provide:

```text
Current subscription
Renewal date
Invoices
Payment history
Receipt
Renew
Profile
Notifications
```

Keep it lightweight.

Do not require a mobile app for customers.

---

# 60. PRODUCT UX

FufaFlow should feel understandable without formal training.

Prefer:

```text
Today
Upcoming
Overdue
Paid
Renew
```

over accounting jargon.

Primary navigation:

```text
Dashboard
Customers
Plans
Subscriptions
Billing
Payments
Renewals
Reports
Settings
```

Avoid excessive nested navigation.

---

# 61. BUSINESS CONCEPT SEPARATION

The domain must distinguish:

### Customer

Who buys.

### Plan

What the business sells.

### Subscription

The ongoing customer relationship.

### Billing cycle

The period being billed.

### Invoice

What the customer owes.

### Payment

What the customer paid.

### Renewal

Continuation of the subscription.

### Usage/Event

What the customer consumed, attended, or received.

Do not collapse these into one model.

---

# 62. EXAMPLE GYM WORKFLOW

```text
1. Create Gold Monthly
2. Create Ahmed
3. Assign Gold Monthly
4. Subscription created
5. Invoice generated
6. Record ETB 2,000 payment
7. Invoice becomes PAID
8. Subscription becomes ACTIVE
9. Three days before expiry:
   reminder generated
10. Ahmed pays again
11. Subscription renewed
12. Next cycle created
```

---

# 63. EXAMPLE CLEANING BUSINESS WORKFLOW

```text
1. Create Monthly Cleaning
2. Create ABC Hotel
3. Create subscription
4. Generate invoice
5. Record bank transfer
6. Service event recorded
7. Reminder before renewal
8. Renew
9. Next billing cycle
```

The same core engine must support both.

---

# 64. EDGE CASES

Document expected behavior for:

* partial payments
* overpayments
* wrong invoice allocation
* cancelled subscriptions
* paused subscriptions
* expired subscriptions
* renewal after expiry
* plan changes
* upgrades
* downgrades
* extending subscription
* backdated subscription
* future-dated subscription
* duplicate payment
* duplicate webhook
* duplicate notification
* failed notification
* month-end renewals
* leap years
* timezone boundaries
* multiple subscriptions per customer
* multiple invoices
* user deactivation
* tenant suspension
* data export

Do not necessarily implement every case in MVP.

But design the model intentionally.

---

# 65. DEVELOPMENT PROCESS

## Phase 0 — Inspect repository

Before changing code:

* inspect repository
* inspect framework version
* inspect package.json
* inspect composer.json
* inspect existing Laravel code
* inspect routes
* inspect migrations
* inspect `.env.example`
* inspect Vite configuration
* inspect existing React/TypeScript
* inspect Docker configuration if present
* inspect deployment files
* inspect documentation

Do not overwrite or delete existing work blindly.

---

# 66. PHASE 1 — PRODUCT DISCOVERY

Create:

```text
BRD
PRD
Personas
User Journeys
Business Rules
MVP Scope
Non-goals
Assumptions
Risks
```

---

# 67. PHASE 2 — ARCHITECTURE

Create:

```text
System architecture
Laravel module architecture
React/Inertia architecture
Database ERD
Domain model
Subscription state machine
Billing state machine
Payment state machine
Multi-tenancy design
Authorization design
Notification architecture
Queue strategy
Scheduler strategy
File storage strategy
Deployment architecture
Security architecture
```

---

# 68. PHASE 3 — DEVELOPMENT PLAN

Create a dependency-aware backlog.

Example:

```text
EPIC-001 Foundation
EPIC-002 Authentication
EPIC-003 Tenancy
EPIC-004 Customers
EPIC-005 Plans
EPIC-006 Subscriptions
EPIC-007 Billing
EPIC-008 Payments
EPIC-009 Renewals
EPIC-010 Notifications
EPIC-011 Dashboard
EPIC-012 Reports
EPIC-013 Customer Portal
EPIC-014 Deployment
EPIC-015 Hardening
```

Each story:

```text
ID
Title
Description
Business value
Dependencies
Acceptance criteria
Technical notes
Testing requirements
```

---

# 69. IMPLEMENTATION METHOD

Build vertical slices.

Do NOT build:

```text
all Laravel models
then all APIs
then all React pages
```

Instead:

```text
Customer
  ↓
Migration
  ↓
Model
  ↓
Policy
  ↓
Controller
  ↓
Validation
  ↓
Inertia page
  ↓
React UI
  ↓
Tests
  ↓
Complete
```

Then:

```text
Plan
  ↓
backend
  ↓
frontend
  ↓
tests
```

Then:

```text
Subscription
...
```

Every completed slice should be usable.

---

# 70. LARAVEL CODE QUALITY

Follow:

* Laravel conventions
* SOLID
* cohesive classes
* clear business services
* policies
* Form Requests
* API Resources where appropriate
* Eloquent relationships
* transactions
* database constraints
* meaningful tests
* clean naming
* dependency injection where useful

Avoid:

* huge controllers
* giant service classes
* duplicated business logic
* generic abstraction for everything
* premature design patterns
* magic strings
* unnecessary repositories
* unnecessary interfaces
* overengineering

Use Laravel's strengths instead of fighting the framework.

---

# 71. REACT CODE QUALITY

Use:

* TypeScript
* reusable components
* accessible forms
* shadcn/ui primitives
* clear page structure
* reusable data tables
* validation
* loading states
* empty states
* error states
* responsive layouts

Do not create:

* giant page components
* giant global stores
* duplicated form logic
* unnecessary API abstractions

Keep server-side business logic in Laravel.

React should focus on presentation and user interaction.

---

# 72. INERTIA DESIGN

Use Inertia for:

* page navigation
* auth flows
* forms
* server-provided page props
* Laravel route integration

Do not force React to duplicate backend routing/business logic.

Avoid building a separate REST API for every Inertia page.

Use APIs where there is a genuine integration requirement.

---

# 73. VITE

Use Vite to build:

```text
React
TypeScript
CSS
```

Local:

```text
npm run dev
```

Production:

```text
npm run build
```

Ensure production assets can be served from the Laravel application's public directory.

---

# 74. TESTING

Use Laravel's testing stack for backend/application behavior.

Use appropriate React/browser tests for frontend behavior.

Critical tests:

```text
Authentication
Authorization
Tenant isolation
Customer creation
Plan creation
Subscription creation
Invoice creation
Payment recording
Renewal calculation
Reminder scheduling
Notification idempotency
```

At least one end-to-end test must verify:

```text
Business
→ Customer
→ Plan
→ Subscription
→ Invoice
→ Payment
→ Renewal
```

---

# 75. DEPLOYMENT TEST

Before declaring MVP deployment-ready, verify:

```text
Fresh server
↓
Configure PHP
↓
Create database
↓
Upload application
↓
Install Composer dependencies
↓
Deploy built assets
↓
Configure .env
↓
Run migrations
↓
Configure storage
↓
Configure cron
↓
Run optimization
↓
Enable SSL
↓
Open application
↓
Login
↓
Create customer
↓
Create subscription
↓
Record payment
```

The exact deployment process must work without undocumented manual fixes.

---

# 76. BACKUP STRATEGY

Document:

* database backup
* file backup
* backup frequency
* retention
* restore process
* backup testing

At minimum, database backups must be considered part of the production architecture.

Do not assume hosting backups are sufficient.

---

# 77. SECURITY AND PRODUCTION HARDENING

Before launch:

* APP_DEBUG=false
* production `.env` protected
* HTTPS enforced
* secure session configuration
* appropriate cookie settings
* CSRF enabled
* secure headers
* upload restrictions
* authorization reviewed
* tenant isolation tested
* sensitive logs reviewed
* error pages safe
* database credentials protected
* backups configured

---

# 78. PRODUCT METRICS

Track:

```text
New Customers
Active Customers
New Subscriptions
Renewals
Renewal Rate
Churn
MRR
Collected Revenue
Outstanding Revenue
Overdue Revenue
Average Subscription Value
Plan Distribution
Customer Lifetime
```

Every metric must have a documented definition.

---

# 79. FIRST COMMERCIAL VALIDATION

Initial validation should focus on:

```text
Gyms
Training centers
Cleaning companies
Maintenance companies
Coworking spaces
Security companies
```

Do not assume the needs are identical.

The core product remains common.

Use interviews and pilots to determine the first vertical-specific features worth building.

---

# 80. COMMERCIAL MODEL

FufaFlow should eventually generate recurring SaaS revenue.

Potential tiers:

### Starter

Affordable small-business plan.

### Business

More customers, users, reminders, reports, automation.

### Pro

Branches, advanced workflows, integrations, API.

Potential additional revenue:

```text
SMS
WhatsApp
Payment processing
AI features
API usage
Storage
Additional branches
```

Do not overbuild FufaFlow's own SaaS billing system before the core product is validated.

---

# 81. PRODUCT EXTENSIBILITY

Future:

```text
Fufa
 └── FufaFlow
      ├── Core
      ├── Payments
      ├── Notifications
      ├── Analytics
      ├── API
      ├── Fitness
      ├── Training
      ├── Services
      ├── Coworking
      └── Contracts
```

Do not create separate applications for each vertical.

Use the same recurring-business engine.

---

# 82. DOCUMENTATION REQUIREMENTS

Documentation must explain:

```text
WHY
WHAT
HOW
TRADE-OFFS
RISKS
EDGE CASES
```

Architecture decisions must explain:

* chosen solution
* alternatives considered
* reason for decision
* future migration implications

Do not create documentation that simply repeats class names.

---

# 83. DEFINITION OF DONE

A feature is NOT complete simply because the code compiles.

A feature is complete when:

* requirement documented
* acceptance criteria met
* Laravel implementation complete
* React implementation complete
* database migration created
* authorization implemented
* validation implemented
* tests implemented
* tenant isolation verified
* error handling exists
* UI states handled
* documentation updated
* local deployment works

---

# 84. REQUIRED DOCUMENTS BEFORE MAJOR CODING

Create:

1. `BRD.md`
2. `PRD.md`
3. `PRODUCT-SCOPE.md`
4. `PERSONAS.md`
5. `USER-FLOWS.md`
6. `DOMAIN-MODEL.md`
7. `DATABASE-DESIGN.md`
8. `ARCHITECTURE.md`
9. `API-DESIGN.md`
10. `SECURITY.md`
11. `INTEGRATIONS.md`
12. `DEPLOYMENT-ARCHITECTURE.md`
13. `DEVELOPMENT-PLAN.md`
14. `ROADMAP.md`
15. `BACKLOG.md`
16. `TEST-STRATEGY.md`
17. `DIRECTADMIN-DEPLOYMENT.md`
18. `ENVIRONMENT-VARIABLES.md`
19. ADR documents

---

# 85. FIRST TASK — DO NOT CODE YET

First inspect the repository.

Then create the complete `/docs` documentation.

Start with:

```text
1. Repository Assessment
2. Product Assumptions
3. BRD
4. PRD
5. MVP Scope
6. Personas
7. User Flows
8. Domain Model
9. ERD
10. Subscription State Machine
11. Billing State Machine
12. Payment State Machine
13. Laravel Architecture
14. React/Inertia Architecture
15. Multi-tenancy Architecture
16. Database Design
17. API Design
18. Security Design
19. Notification Architecture
20. Queue Architecture
21. Scheduler Architecture
22. DirectAdmin/cPanel Deployment Architecture
23. Development Roadmap
24. Detailed Backlog
25. ADRs
```

After documentation, produce an:

# Architecture & Product Review

Explain:

* what was decided
* what remains uncertain
* MVP scope
* deliberately postponed features
* what should be built first
* technical risks
* hosting/deployment risks
* business risks
* major assumptions
* migration paths for future scaling

Only then begin implementation.

---

# 86. NON-NEGOTIABLE PRODUCT FOCUS

Do not lose the central workflow:

```text
Customer
   ↓
Plan
   ↓
Subscription
   ↓
Invoice
   ↓
Payment
   ↓
Usage / Service
   ↓
Reminder
   ↓
Renewal
   ↓
Next Cycle
```

The product should help the business owner answer:

> **Who are my customers?**

> **Who is active?**

> **Who is expiring?**

> **Who owes me money?**

> **How much have I collected?**

> **How much recurring revenue should I expect?**

> **Who needs follow-up today?**

This is the core FufaFlow problem.

Everything else must strengthen this workflow.

---

# FINAL PRODUCT IDENTITY

**Company:**

# Fufa

**Product:**

# FufaFlow

**Positioning:**

> **Keep Business Going.**

**Category:**

> Recurring Business Management SaaS

**Primary value proposition:**

> **FufaFlow helps recurring businesses manage customers, subscriptions, payments, renewals, and follow-up from one simple system.**

Build the product to be:

```text
Simple enough for a small business today
        +
Structured enough to scale tomorrow
```

Do not sacrifice MVP simplicity for hypothetical future complexity.
