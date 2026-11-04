<?php

namespace App\Support;

use App\Models\SanghFeeSlab;
use App\Models\Setting;

/**
 * प्रादेशिक विभागाने घेतलेल्या रकमेचे तपशील (fees taken by Pradeshik Vibhag).
 *
 * Calculated automatically from Settings → Sangh Fees (standard fees + member
 * slabs), never typed in:
 *   प्रवेश शुल्क     = admission fee × 2
 *   वार्षिक शुल्क     = slab annual fee (for the member count) × 2
 *   विकास निधी शुल्क = development rate × members × 3
 */
class PradeshikFees
{
    public const ADMISSION_MULTIPLIER = 2;
    public const ANNUAL_MULTIPLIER = 2;
    public const DEVELOPMENT_MULTIPLIER = 3;

    /**
     * @return array{admission: float, annual: float, development: float, total: float}|null
     *         null when the member counts are not entered yet.
     */
    public static function calculate(mixed $male, mixed $female): ?array
    {
        $male = is_numeric($male) ? (int) $male : null;
        $female = is_numeric($female) ? (int) $female : null;
        if ($male === null && $female === null) {
            return null;
        }
        $members = ($male ?? 0) + ($female ?? 0);

        $admission = (float) Setting::getValue('sangh_admission_fee', 0) * self::ADMISSION_MULTIPLIER;
        $annual = (float) (SanghFeeSlab::annualFeeForMemberCount($members) ?? 0) * self::ANNUAL_MULTIPLIER;
        $development = round((float) Setting::getValue('sangh_development_fee_rate', 0) * $members * self::DEVELOPMENT_MULTIPLIER, 2);

        return [
            'admission' => $admission,
            'annual' => $annual,
            'development' => $development,
            'total' => $admission + $annual + $development,
        ];
    }

    /** The three Sangh columns, ready to save (all null when members are unknown). */
    public static function attributes(mixed $male, mixed $female): array
    {
        $fees = self::calculate($male, $female);

        return [
            'pradeshik_admission_fee' => $fees['admission'] ?? null,
            'pradeshik_annual_fee' => $fees['annual'] ?? null,
            'pradeshik_development_fee' => $fees['development'] ?? null,
        ];
    }

    /** What the Sangh form's live calculator needs. */
    public static function forJs(): array
    {
        return [
            'admission' => (float) Setting::getValue('sangh_admission_fee', 0),
            'developmentRate' => (float) Setting::getValue('sangh_development_fee_rate', 0),
            'multipliers' => [
                'admission' => self::ADMISSION_MULTIPLIER,
                'annual' => self::ANNUAL_MULTIPLIER,
                'development' => self::DEVELOPMENT_MULTIPLIER,
            ],
        ];
    }
}
