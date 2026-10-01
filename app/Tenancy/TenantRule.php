<?php

namespace App\Tenancy;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * Tenant-scoped validation rules.
 *
 * Laravel's `exists` and `unique` rules use the query builder, which ignores
 * Eloquent global scopes. Without the tenant filter, a user could submit
 * another business's plan or customer id and pass validation (IDOR).
 * Always use these helpers in tenant Form Requests; a test forbids the raw
 * string rules there.
 */
final class TenantRule
{
    public static function exists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)
            ->where('tenant_id', app(TenantContext::class)->id());
    }

    public static function unique(string $table, string $column = 'NULL'): Unique
    {
        return Rule::unique($table, $column)
            ->where('tenant_id', app(TenantContext::class)->id());
    }
}
