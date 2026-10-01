<?php

namespace App\Support;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Normalises phone numbers to E.164 (+251911234567) so the same number typed
 * as 0911 234 567, +251911234567 or 251911234567 matches in search, duplicate
 * checks and (later) SMS. The tenant's country is the default region.
 */
final class PhoneNumbers
{
    public static function normalize(?string $input, string $region): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $number = $util->parse($input, strtoupper($region));
        } catch (NumberParseException) {
            return null;
        }

        if (! $util->isValidNumber($number)) {
            return null;
        }

        return $util->format($number, PhoneNumberFormat::E164);
    }
}
