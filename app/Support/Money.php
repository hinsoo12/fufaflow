<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Exact money handling. Amounts are always integers in minor units
 * (e.g. santim for ETB). Never use floats for money.
 */
final class Money
{
    /**
     * Largest amount accepted from user input, in major units (13 digits).
     */
    private const MAX_WHOLE_DIGITS = 13;

    public static function exponent(string $currency): int
    {
        $currencies = config('fufaflow.currencies');

        if (! is_array($currencies) || ! isset($currencies[$currency]['exponent'])) {
            throw new InvalidArgumentException("Unsupported currency [{$currency}].");
        }

        return (int) $currencies[$currency]['exponent'];
    }

    public static function isSupported(string $currency): bool
    {
        $currencies = config('fufaflow.currencies');

        return is_array($currencies) && isset($currencies[$currency]);
    }

    /**
     * Parse a user-entered decimal amount (e.g. "2,000.50") into minor units.
     *
     * Returns null when the input is not a valid non-negative amount for the
     * currency (too many decimals, letters, negative numbers, overflow).
     */
    public static function parse(string $input, string $currency): ?int
    {
        $exponent = self::exponent($currency);
        $clean = str_replace([',', ' ', "\u{00A0}"], '', trim($input));

        $pattern = $exponent === 0
            ? '/^\d+$/'
            : '/^\d+(?:\.\d{1,'.$exponent.'})?$/';

        if ($clean === '' || preg_match($pattern, $clean) !== 1) {
            return null;
        }

        [$whole, $fraction] = array_pad(explode('.', $clean, 2), 2, '');
        $whole = ltrim($whole, '0');

        if (strlen($whole) > self::MAX_WHOLE_DIGITS) {
            return null;
        }

        $fraction = str_pad($fraction, $exponent, '0');

        return ((int) ($whole === '' ? '0' : $whole)) * (10 ** $exponent) + (int) ($fraction === '' ? '0' : $fraction);
    }

    /**
     * Minor units as a plain decimal string, e.g. 200050 => "2000.50".
     */
    public static function toDecimal(int $minor, string $currency): string
    {
        $exponent = self::exponent($currency);

        if ($exponent === 0) {
            return (string) $minor;
        }

        $sign = $minor < 0 ? '-' : '';
        $absolute = str_pad((string) abs($minor), $exponent + 1, '0', STR_PAD_LEFT);

        return $sign.substr($absolute, 0, -$exponent).'.'.substr($absolute, -$exponent);
    }

    /**
     * Display format, e.g. "ETB 2,000.50". The UI formats with Intl; this is
     * for server-rendered text such as audit entries and emails.
     */
    public static function format(int $minor, string $currency): string
    {
        $exponent = self::exponent($currency);

        return $currency.' '.number_format($minor / (10 ** $exponent), $exponent);
    }
}
