<?php

namespace App\Support;

use App\Models\Folder;
use App\Models\Group;
use App\Models\Sangh;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * Turns raw Spatie activity log rows into plain-language text for the
 * admin Activity Logs page (who did what, to which record, and what changed).
 */
class ActivityLogPresenter
{
    /** Friendly names for each logged model. */
    private const MODEL_LABELS = [
        'Sangh' => 'Sangh',
        'SanghRegistrationReceipt' => 'Sangh registration receipt',
        'SanghRenewal' => 'Sangh renewal',
        'SanghFeeSlab' => 'Fee slab',
        'Receipt' => 'Receipt',
        'User' => 'User',
        'Role' => 'Role',
        'Permission' => 'Permission',
        'Folder' => 'Folder',
        'File' => 'File',
        'Group' => 'Group',
        'Chat' => 'Group chat tab',
        'ChatMessage' => 'Chat message',
        'Meeting' => 'Meeting',
        'Link' => 'Link',
        'Setting' => 'Setting',
    ];

    /** Fields never worth showing to a person reading the log. */
    private const HIDDEN_FIELDS = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token',
        'email_verified_at', 'path', 'disk_path',
        'sort_order', 'pinned_message_id',
    ];

    /** Readable labels for field names (Marathi where the form itself uses Marathi). */
    private const FIELD_LABELS = [
        // Sangh
        'sangh_sr_no' => 'Sangh Sr. No.',
        'unique_ref_no' => 'Sangh Number',
        'pradeshik_sr_no' => 'प्रादेशिक Sr. No.',
        'pradeshik_ref_no' => 'प्रादेशिक Ref. No.',
        'district_sr_no' => 'जिल्हा Sr. No.',
        'district_ref_no' => 'जिल्हा Ref. No.',
        'registration_year' => 'वर्ष (Year)',
        'name_of_sangh' => 'संघाचे नाव (Sangh name)',
        'category_code' => 'श्रेणी (Category)',
        'sangh_type_code' => 'संघ प्रकार (Sangh type)',
        'pradeshik_vibhag' => 'प्रादेशिक विभाग',
        'pradeshik_vibhag_code' => 'प्रादेशिक कोड',
        'district' => 'जिल्हा (District)',
        'district_code' => 'जिल्हा कोड',
        'district_no' => 'जिल्हा No.',
        'taluka' => 'तालुका',
        'village' => 'गाव (Village)',
        'city' => 'शहर (City)',
        'mukkam_post' => 'मुक्काम पोस्ट',
        'pincode' => 'पिनकोड',
        'division' => 'Division',
        'division_no' => 'Division No.',
        'division_membership_no' => 'Division membership No.',
        'address' => 'पत्ता (Address)',
        'road_path' => 'रस्ता / पथ (Road)',
        'ward_section' => 'विभाग / प्रभाग (Area)',
        'male' => 'पुरुष सभासद (Male members)',
        'female' => 'महिला सभासद (Female members)',
        'total_m_f' => 'Total members',
        'total_members' => 'एकूण सभासद (Total members)',
        'male_members' => 'पुरुष सभासद (Male members)',
        'female_members' => 'महिला सभासद (Female members)',
        'pradeshik_admission_fee' => 'प्रवेश शुल्क (Admission fee)',
        'pradeshik_annual_fee' => 'वार्षिक शुल्क (Annual fee)',
        'pradeshik_development_fee' => 'विकास निधी शुल्क (Development fee)',
        'president' => 'अध्यक्ष (President)',
        'president_phone' => 'President mobile',
        'president_whatsapp' => 'President WhatsApp',
        'president_email' => 'President email',
        'secretary' => 'सचिव (Secretary)',
        'secretary_phone' => 'Secretary mobile',
        'secretary_whatsapp' => 'Secretary WhatsApp',
        'secretary_email' => 'Secretary email',
        'tel_no' => 'Telephone',
        'alt_tel_no' => 'Alternate telephone',
        'date_meeting' => 'Meeting date',
        'created_date' => 'Created date',
        'created_by' => 'Created by',
        'assigned_to' => 'Assigned to',

        // Receipts / renewals
        'sangh_id' => 'Sangh',
        'receipt_year' => 'Receipt year',
        'renewal_year' => 'Renewal year',
        'is_paid' => 'Paid?',
        'status' => 'Status',
        'feskcom_receipt_no' => 'FESCOM receipt No.',
        'feskcom_receipt_date' => 'FESCOM receipt date',
        'receipt_no' => 'Receipt No.',
        'receipt_date' => 'Receipt date',
        'receipt_amount' => 'Receipt amount',
        'annual_fee' => 'Annual fee',
        'admission_fee' => 'Admission fee',
        'development_fee' => 'Development fee',
        'penalty_fee' => 'Penalty fee',
        'paid_amount' => 'Amount paid',
        'payment_mode' => 'Payment mode',
        'transaction_number' => 'Transaction No.',
        'transaction_date' => 'Transaction date',
        'bank_name' => 'Bank name',
        'cheque_no' => 'Cheque No.',
        'cheque_date' => 'Cheque date',
        'user_id' => 'Entered by',
        'photo_path' => 'Profile photo',
        'file_path' => 'Attached file',
        'attachment_path' => 'Attachment',
        'subject' => 'Subject',
        'amount' => 'Amount',
        'total_amount' => 'Total amount',

        // Fee slab
        'min_members' => 'Minimum members',
        'max_members' => 'Maximum members',

        // Users
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'designation' => 'Designation',

        // Files, folders, groups, chat, meetings, links
        'folder_id' => 'Folder',
        'parent_id' => 'Parent folder',
        'owner_group_id' => 'Group',
        'group_id' => 'Group',
        'year' => 'Year',
        'mime' => 'File type',
        'size_bytes' => 'File size',
        'uploaded_by' => 'Uploaded by',
        'description' => 'Description',
        'chat_id' => 'Chat',
        'tab' => 'Tab',
        'body' => 'Message',
        'file_id' => 'Attached file',
        'title' => 'Title',
        'start_at' => 'Starts at',
        'platform' => 'Platform',
        'category' => 'Category',
        'url' => 'Link (URL)',
        'icon' => 'Icon',
        'is_active' => 'Active?',

        // Roles, permissions, settings
        'slug' => 'Name',
        'key' => 'Setting',
        'value' => 'Value',
    ];

    private const USER_FIELDS = ['created_by', 'assigned_to', 'user_id', 'uploaded_by'];
    private const BOOL_FIELDS = ['is_paid', 'is_active'];
    private const MONEY_FIELDS = [
        'receipt_amount', 'pradeshik_admission_fee', 'pradeshik_annual_fee', 'pradeshik_development_fee',
        'annual_fee', 'admission_fee', 'development_fee', 'penalty_fee', 'paid_amount', 'amount', 'total_amount',
    ];

    private const CATEGORY_CODES = ['R' => 'ग्रामीण - R', 'U' => 'शहरी - U', 'A' => 'निमशहरी - A'];
    private const SANGH_TYPE_CODES = ['G' => 'सर्वसाधारण - G', 'F' => 'महिला - F'];

    /** Small per-request lookup caches so the page doesn't repeat queries. */
    private static array $cache = [];

    public static function modelLabel(Activity $log): string
    {
        $base = class_basename((string) $log->subject_type);

        return self::MODEL_LABELS[$base] ?? trim(ucfirst(strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $base))));
    }

    public static function actionVerb(Activity $log): string
    {
        return match ($log->event) {
            'created' => 'added',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'restored' => 'restored',
            default => strtolower((string) ($log->event ?: $log->description)),
        };
    }

    /** The badge shown in the "Action" column: [label, bootstrap colour, icon]. */
    public static function badge(Activity $log): array
    {
        return match ($log->event) {
            'created' => ['Added', 'success', 'fa-plus'],
            'updated' => ['Edited', 'primary', 'fa-pencil'],
            'deleted' => ['Deleted', 'danger', 'fa-trash'],
            'restored' => ['Restored', 'warning', 'fa-undo'],
            default => [ucfirst((string) ($log->event ?: $log->description)), 'info', 'fa-info-circle'],
        };
    }

    /** A readable name for the record the action touched, e.g. the Sangh's name. */
    public static function recordName(Activity $log): ?string
    {
        $data = array_merge(
            (array) ($log->properties['old'] ?? []),
            (array) ($log->properties['attributes'] ?? []),
            $log->subject ? $log->subject->getAttributes() : [],
        );

        $base = class_basename((string) $log->subject_type);

        if (in_array($base, ['SanghRegistrationReceipt', 'SanghRenewal'], true)) {
            $year = $data['receipt_year'] ?? $data['renewal_year'] ?? null;
            $sangh = self::sanghName($data['sangh_id'] ?? null);

            if ($sangh) {
                return $sangh . ($year ? " ({$year})" : '');
            }

            return $log->subject_id ? '#' . $log->subject_id . ($year ? " ({$year})" : '') : null;
        }

        if ($base === 'SanghFeeSlab' && isset($data['min_members'])) {
            return "{$data['min_members']}–{$data['max_members']} members";
        }

        if ($base === 'ChatMessage' && ! empty($data['body'])) {
            return '"' . mb_strimwidth(strip_tags((string) $data['body']), 0, 60, '…') . '"';
        }

        foreach (['name_of_sangh', 'name', 'title', 'subject', 'key', 'slug', 'tab'] as $field) {
            if (! empty($data[$field]) && is_scalar($data[$field])) {
                return (string) $data[$field];
            }
        }

        return $log->subject_id ? '#' . $log->subject_id : null;
    }

    /** One plain sentence, e.g. "Super Admin updated Sangh "Shivaji Sangh" (3 changes)". */
    public static function summary(Activity $log): string
    {
        $who = $log->causer?->name ?? 'The system';
        $what = self::modelLabel($log);
        $name = self::recordName($log);

        // Keep "Sangh" capitalised; lower-case the generic nouns ("user", "folder", ...).
        $whatInSentence = str_starts_with($what, 'Sangh') ? $what : lcfirst($what);

        $sentence = "{$who} " . self::actionVerb($log) . ' ' . $whatInSentence;
        if ($name) {
            $sentence .= str_starts_with($name, '#') ? " {$name}" : " “{$name}”";
        }

        if ($log->event === 'updated') {
            $count = count(self::changes($log));
            if ($count > 0) {
                $sentence .= ' — ' . $count . ' ' . ($count === 1 ? 'change' : 'changes');
            }
        }

        return $sentence;
    }

    /**
     * Rows for the details table: [label, before, after], with technical
     * fields removed and values formatted for people.
     */
    public static function changes(Activity $log): array
    {
        $old = (array) ($log->properties['old'] ?? []);
        $new = (array) ($log->properties['attributes'] ?? []);

        $rows = [];
        foreach (array_unique(array_merge(array_keys($new), array_keys($old))) as $field) {
            if (in_array($field, self::HIDDEN_FIELDS, true)) {
                continue;
            }

            $before = array_key_exists($field, $old) ? self::formatValue($field, $old[$field]) : null;
            $after = array_key_exists($field, $new) ? self::formatValue($field, $new[$field]) : null;

            // Skip empty fields on add/delete so the list only shows what was actually filled in.
            if ($log->event !== 'updated' && ($before ?? '') === '' && ($after ?? '') === '') {
                continue;
            }
            if ($log->event === 'updated' && $before === $after) {
                continue;
            }

            $rows[] = [self::fieldLabel($field), $before, $after];
        }

        return $rows;
    }

    public static function fieldLabel(string $field): string
    {
        return self::FIELD_LABELS[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    public static function formatValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (in_array($field, ['photo_path', 'file_path', 'attachment_path'], true)) {
            return 'Uploaded (' . basename((string) $value) . ')';
        }

        if (in_array($field, self::BOOL_FIELDS, true)) {
            return $value ? 'Yes' : 'No';
        }

        if ($field === 'category_code') {
            return self::CATEGORY_CODES[strtoupper((string) $value)] ?? (string) $value;
        }

        if ($field === 'sangh_type_code') {
            return self::SANGH_TYPE_CODES[strtoupper((string) $value)] ?? (string) $value;
        }

        if (in_array($field, self::USER_FIELDS, true)) {
            return self::lookup(User::class, $value, 'name') ?? "User #{$value}";
        }

        if ($field === 'sangh_id') {
            return self::sanghName($value) ?? "Sangh #{$value}";
        }

        if (in_array($field, ['group_id', 'owner_group_id'], true)) {
            return self::lookup(Group::class, $value, 'name') ?? "Group #{$value}";
        }

        if (in_array($field, ['folder_id', 'parent_id'], true)) {
            return self::lookup(Folder::class, $value, 'name') ?? "Folder #{$value}";
        }

        if ($field === 'size_bytes' && is_numeric($value)) {
            return self::humanSize((int) $value);
        }

        if (in_array($field, self::MONEY_FIELDS, true) && is_numeric($value)) {
            return '₹ ' . number_format((float) $value, 2);
        }

        if (in_array($field, ['status', 'payment_mode'], true)) {
            return ucfirst(str_replace('_', ' ', (string) $value));
        }

        // ISO timestamps / dates → dd-mm-yyyy (with time when present).
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2})?/', $value, $m)) {
            try {
                $date = Carbon::parse($value)->timezone(config('app.timezone'));

                return isset($m[1]) && $date->format('H:i:s') !== '00:00:00'
                    ? $date->format('d-m-Y h:i A')
                    : $date->format('d-m-Y');
            } catch (\Throwable) {
                // Fall through to the raw value.
            }
        }

        return (string) $value;
    }

    private static function sanghName(mixed $id): ?string
    {
        return self::lookup(Sangh::class, $id, 'name_of_sangh');
    }

    private static function lookup(string $model, mixed $id, string $column): ?string
    {
        if (! is_numeric($id)) {
            return null;
        }

        $key = $model . ':' . $id;
        if (! array_key_exists($key, self::$cache)) {
            $query = $model::query();
            if (method_exists($model, 'bootSoftDeletes')) {
                $query->withTrashed();
            }
            self::$cache[$key] = $query->whereKey($id)->value($column);
        }

        return self::$cache[$key] ?: null;
    }

    private static function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $bytes;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1) . ' ' . $units[$i];
    }
}
