<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Per-tenant, gap-free document numbers (customers, invoices, receipts).
 *
 * Must be called inside a database transaction: the sequence row is locked
 * so concurrent requests never receive the same number.
 */
final class DocumentNumbers
{
    public const CUSTOMER = 'customer';

    public const INVOICE = 'invoice';

    public const RECEIPT = 'receipt';

    private const PREFIXES = [
        self::CUSTOMER => 'C',
        self::INVOICE => 'INV',
        self::RECEIPT => 'RCT',
    ];

    public static function next(int $tenantId, string $sequence): string
    {
        if (! isset(self::PREFIXES[$sequence])) {
            throw new LogicException("Unknown document sequence [{$sequence}].");
        }

        if (DB::transactionLevel() === 0) {
            throw new LogicException('Document numbers must be allocated inside a transaction.');
        }

        DB::table('sequences')->insertOrIgnore([
            'tenant_id' => $tenantId,
            'name' => $sequence,
            'next_value' => 1,
        ]);

        $row = DB::table('sequences')
            ->where('tenant_id', $tenantId)
            ->where('name', $sequence)
            ->lockForUpdate()
            ->first();

        $value = (int) ($row->next_value ?? 1);

        DB::table('sequences')
            ->where('tenant_id', $tenantId)
            ->where('name', $sequence)
            ->update(['next_value' => $value + 1]);

        return sprintf('%s-%06d', self::PREFIXES[$sequence], $value);
    }
}
