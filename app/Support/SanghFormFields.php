<?php

namespace App\Support;

use App\Models\User;

/**
 * The Sangh create/edit form fields an admin can lock per user
 * (Settings → Roles & Access → Sangh Form Fields).
 *
 * A locked field is shown read-only on the form and ignored on save:
 * editing keeps the stored value, creating leaves it empty.
 */
class SanghFormFields
{
    /** Grouped exactly like the form, field name => label. */
    public const GROUPS = [
        'मूलभूत माहिती (Basic details)' => [
            'registration_year' => 'वर्ष',
            'name_of_sangh' => 'संघाचे नाव',
            'category_code' => 'श्रेणी',
            'sangh_type_code' => 'संघ प्रकार',
        ],
        'पत्ता (Address)' => [
            'pradeshik_vibhag' => 'प्रादेशिक विभाग',
            'district' => 'जिल्हा',
            'taluka' => 'तालुका',
            'village' => 'गाव',
            'mukkam_post' => 'मुक्काम पोस्ट',
            'address' => 'पत्ता / Address',
            'road_path' => 'रस्ता / पथ / Road',
            'ward_section' => 'विभाग / प्रभाग / Area',
            'city' => 'शहर / City',
            'pincode' => 'पिनकोड',
        ],
        'सभासद व शुल्क (Members & fees)' => [
            'male' => 'पुरुष सभासद',
            'female' => 'महिला सभासद',
            // प्रादेशिक fees are calculated automatically (PradeshikFees), so not lockable.
        ],
        'अध्यक्ष (President)' => [
            'president' => 'अध्यक्ष',
            'president_phone' => 'मो.फ़ोन नंबर',
            'president_whatsapp' => 'व्हॉट्सअप नंबर',
            'president_email' => 'इमेल',
        ],
        'सचिव (Secretary)' => [
            'secretary' => 'सचिव',
            'secretary_phone' => 'मो.फ़ोन नंबर',
            'secretary_whatsapp' => 'व्हॉट्सअप नंबर',
            'secretary_email' => 'इमेल',
        ],
    ];

    /**
     * Auto-filled fields that follow a lockable field: locking the parent
     * locks these too, so their stored values can't drift apart.
     */
    private const DEPENDENTS = [
        'pradeshik_vibhag' => ['pradeshik_vibhag_code'],
        'district' => ['district_code'],
    ];

    /** @return string[] every lockable field name */
    public static function names(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::GROUPS)));
    }

    public static function label(string $field): string
    {
        foreach (self::GROUPS as $fields) {
            if (isset($fields[$field])) {
                return $fields[$field];
            }
        }

        return $field;
    }

    /** Keep only known field names (what the admin is allowed to store). */
    public static function sanitize(array $fields): array
    {
        return array_values(array_intersect(self::names(), $fields));
    }

    /**
     * Fields locked for this user, as chosen by the admin. Superadmins are never restricted.
     *
     * @return string[]
     */
    public static function lockedFor(?User $user): array
    {
        if (! $user || $user->hasRole('superadmin')) {
            return [];
        }

        return self::sanitize((array) ($user->locked_sangh_fields ?? []));
    }

    /**
     * Locked fields plus their auto-filled dependents — the full set the
     * server must ignore on save.
     *
     * @return string[]
     */
    public static function lockedWithDependentsFor(?User $user): array
    {
        $locked = self::lockedFor($user);
        foreach ($locked as $field) {
            array_push($locked, ...(self::DEPENDENTS[$field] ?? []));
        }

        return array_values(array_unique($locked));
    }
}
