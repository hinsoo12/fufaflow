<?php

namespace App\Enums;

/**
 * Fine-grained capabilities checked by policies and shared with the UI.
 *
 * Roles map to abilities in MembershipRole::abilities(). There is no
 * permission table by design: the matrix lives in code and in tests.
 */
enum Ability: string
{
    case CustomersView = 'customers.view';
    case CustomersManage = 'customers.manage';
    case CustomersArchive = 'customers.archive';
    case PlansView = 'plans.view';
    case PlansManage = 'plans.manage';
    case SubscriptionsView = 'subscriptions.view';
    case SubscriptionsStart = 'subscriptions.start';
    case SubscriptionsCancel = 'subscriptions.cancel';
    case PricesOverride = 'prices.override';
    case BillingView = 'billing.view';
    case PaymentsRecord = 'payments.record';
    case PaymentsVoid = 'payments.void';
    case InvoicesVoid = 'invoices.void';
    case CreditExtend = 'credit.extend';
    case DatesBackdate = 'dates.backdate';
    case FinancialsView = 'financials.view';
    case TeamManage = 'team.manage';
    case SettingsManage = 'settings.manage';
    case AuditView = 'audit.view';
}
