<?php

namespace App\Http\Controllers;

use App\Models\Sangh;
use App\Models\SanghFeeSlab;
use App\Models\SanghRegistrationReceipt;
use App\Models\SanghRenewal;
use App\Models\Setting;
use App\Support\Digits;
use App\Support\Phone;
use App\Support\SanghCodes;
use App\Support\SanghFormFields;
use App\Support\SanghYears;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;


class SanghController extends Controller
{
    /** Links rows to the exact Sangh on re-import (works even before it has a Unique number). */
    private const ID_HEADER = 'Sangh ID (system - do not change)';

    private const IMPORT_HEADERS = [
        self::ID_HEADER,
        'Sangh Sr. No.',
        'Unique संघाचा अनु क्र.',
        'प्रादेशिक विभागातील संघाचा अनु क्र.',
        'जिल्हा मधे संघाचा अनु. क्र.',
        'वर्ष',
        'संघाचे नाव',
        'श्रेणी',
        'संघ प्रकार',
        'प्रादेशिक विभाग',
        'जिल्हा',
        'तालुका',
        'गाव',
        'शहर',
        'मुक्काम पोस्ट',
        'पिनकोड',
        'पत्ता',
        'रस्ता / पथ',
        'विभाग/प्रभाग',
        'पुरुष सभासद संख्या',
        'महिला सभासद संख्या',
        'एकूण सभासद संख्या',
        'प्रादेशिक प्रवेश शुल्क',
        'प्रादेशिक वार्षिक शुल्क',
        'प्रादेशिक विकास निधी शुल्क',
        'अध्यक्ष',
        'अध्यक्ष मोबाईल',
        'अध्यक्ष व्हॉट्सअप',
        'अध्यक्ष इमेल',
        'सचिव',
        'सचिव मोबाईल',
        'सचिव व्हॉट्सअप',
        'सचिव इमेल',
        'संघ नोंदणी दिनांक',
    ];

    /** Cheque / transaction number column (receipt sheets). Old files used 'चेक क्रमांक'. */
    private const CHEQUE_HEADER = 'चेक क्रमांक / Transaction क्रमांक';
    private const OLD_CHEQUE_HEADER = 'चेक क्रमांक';

    /** Sheet 2: the New register Sangh receipt (one per Sangh). */
    private const REGISTRATION_HEADERS = [
        self::ID_HEADER,
        'Unique संघाचा अनु क्र.',
        'संघाचे नाव',
        'वर्ष',
        'स्थिती',
        'फेस्कॉम पावती क्र.',
        'फेस्कॉम पावती दिनांक',
        'संघ नोंदणी दिनांक',
        'पुरुष सभासद संख्या',
        'महिला सभासद संख्या',
        'एकूण सभासद संख्या',
        'प्रवेश शुल्क',
        'वार्षिक शुल्क',
        'विकास निधी शुल्क',
        'एकूण रक्कम',
        'पावती रक्कम (भरलेली)',
        'बाकी रक्कम',
        'बँकेचे नाव',
        self::CHEQUE_HEADER,
        'चेक दिनांक',
    ];

    /** Sheet 3: Renewal and Backdated receipts (one row per year). */
    private const RENEWAL_HEADERS = [
        self::ID_HEADER,
        'Unique संघाचा अनु क्र.',
        'संघाचे नाव',
        'वर्ष',
        'प्रकार',
        'स्थिती',
        'फेस्कॉम पावती क्र.',
        'फेस्कॉम पावती दिनांक',
        'संघ नोंदणी दिनांक',
        'पुरुष सभासद संख्या',
        'महिला सभासद संख्या',
        'एकूण सभासद संख्या',
        'वार्षिक शुल्क',
        'विकास निधी शुल्क',
        'दंड शुल्क',
        'एकूण रक्कम',
        'पावती रक्कम (भरलेली)',
        'बाकी रक्कम',
        'बँकेचे नाव',
        self::CHEQUE_HEADER,
        'चेक दिनांक',
    ];

    private const SHEET_MASTER = 'Sangh Master';
    private const SHEET_REGISTRATION = 'Registration Receipts';
    private const SHEET_RENEWALS = 'Renewals';

    private function applyFilters($query, \Illuminate\Http\Request $request)
    {
        // Members only ever see Sanghs they created or were assigned; superadmin sees all.
        if (! $request->user()->hasRole('superadmin')) {
            $userId = $request->user()->id;
            $query->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)->orWhere('assigned_to', $userId);
            });
        }

        if ($request->filled('pradeshik_vibhag')) {
            $query->where('pradeshik_vibhag', $request->input('pradeshik_vibhag'));
        }

        if ($request->filled('district')) {
            $query->where('district', $request->input('district'));
        }

        if ($request->filled('year')) {
            $query->where('registration_year', (int) $request->input('year'));
        }

        // संघ नोंदणी दिनांक range (the date the Sangh became "Sangh Registered" — the
        // created date is for activity tracking only). Invalid dates are ignored; a
        // reversed range is swapped so "From 30-11 To 01-11" still means November.
        $fromDate = $this->parseFilterDate($request->input('from_date'));
        $toDate = $this->parseFilterDate($request->input('to_date'));
        if ($fromDate && $toDate && $fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }
        if ($fromDate) {
            $query->whereDate('registered_at', '>=', $fromDate->toDateString());
        }
        if ($toDate) {
            $query->whereDate('registered_at', '<=', $toDate->toDateString());
        }

        // Ownership filter
        $ownership = $request->input('ownership');
        if ($ownership === 'created_by_me') {
            $query->where('created_by', $request->user()->id);
        } elseif ($ownership === 'assigned_to_me') {
            $query->where('assigned_to', $request->user()->id);
        }

        $status = in_array($request->input('payment_status'), ['unpaid', 'information_approved', 'sangh_registered'], true)
            ? $request->input('payment_status')
            : null;
        $registerYear = $request->filled('register_receipt_year') ? (int) $request->input('register_receipt_year') : null;
        $renewalYear = $request->filled('renewal_receipt_year') ? (int) $request->input('renewal_receipt_year') : null;

        // Register Receipts filter (year, plus status when chosen)
        if ($registerYear) {
            $query->whereHas('registrationReceipt', function ($r) use ($registerYear, $status) {
                $r->where('receipt_year', $registerYear);
                if ($status) {
                    $r->where('status', $status);
                }
            });
        }

        // Renewal Receipts filter (year, plus status when chosen)
        if ($renewalYear) {
            $query->whereHas('renewals', function ($r) use ($renewalYear, $status) {
                $r->where('renewal_year', $renewalYear);
                if ($status) {
                    $r->where('status', $status);
                }
            });
        }

        // Status alone (no receipt year): match the registration receipt or any renewal.
        // A Sangh with no registration receipt yet is shown as "Unpaid" on its page,
        // so it counts as Unpaid here too.
        if ($status && ! $registerYear && ! $renewalYear) {
            $query->where(function ($q) use ($status) {
                $q->whereHas('registrationReceipt', fn ($r) => $r->where('status', $status))
                  ->orWhereHas('renewals', fn ($r) => $r->where('status', $status));
                if ($status === 'unpaid') {
                    $q->orWhereDoesntHave('registrationReceipt');
                }
            });
        }

        return $query;
    }

    /** A Y-m-d filter date, or null when empty/invalid. */
    private function parseFilterDate(mixed $value): ?\Carbon\Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = \Carbon\Carbon::createFromFormat('!Y-m-d', $value);

            return $date && $date->format('Y-m-d') === $value ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function index(Request $request)
    {
        $query = Sangh::query();
        $this->applyFilters($query, $request);

        $stats = $this->filterStats($query, $request);
        $sanghs = $query->orderBy('sangh_sr_no', 'asc')->paginate(15)->withQueryString();

        // Filter options come only from Sanghs this user can see, so a member is
        // never offered a vibhag/district that can only return 0 results.
        $visible = function () use ($request) {
            $q = Sangh::query();
            if (! $request->user()->hasRole('superadmin')) {
                $userId = $request->user()->id;
                $q->where(fn ($w) => $w->where('created_by', $userId)->orWhere('assigned_to', $userId));
            }

            return $q;
        };

        $vibhags = $visible()->whereNotNull('pradeshik_vibhag')->where('pradeshik_vibhag', '!=', '')->distinct()->orderBy('pradeshik_vibhag')->pluck('pradeshik_vibhag');
        $districts = $visible()->whereNotNull('district')->where('district', '!=', '')->distinct()->orderBy('district')->pluck('district');
        $years = SanghYears::all();

        $vibhagDistricts = $visible()
            ->whereNotNull('pradeshik_vibhag')
            ->whereNotNull('district')
            ->select('pradeshik_vibhag', 'district')
            ->distinct()
            ->get()
            ->groupBy('pradeshik_vibhag')
            ->map->pluck('district');
        
        // Receipt year filters offer the same full year range as everywhere else.
        $registerReceiptYears = $years;
        $renewalReceiptYears = $years;

        // Show "Pre-add Blank Sangh rows" only to superadmin, and only until it has been done.
        $showPlaceholderSeeder = $request->user()->hasRole('superadmin') && Sangh::count() < self::PLACEHOLDER_TARGET;

        // "Import by Sangh No." (superadmin): show the available number range and how many are blank.
        $rangeImportInfo = $request->user()->hasRole('superadmin') ? [
            'min' => (int) Sangh::query()->min('sangh_sr_no'),
            'max' => (int) Sangh::query()->max('sangh_sr_no'),
            'blank' => Sangh::query()->whereNotNull('sangh_sr_no')
                ->where(fn ($q) => $q->whereNull('name_of_sangh')->orWhere('name_of_sangh', ''))
                ->count(),
        ] : null;

        return view('sanghs.index', compact('sanghs', 'stats', 'vibhags', 'districts', 'vibhagDistricts', 'years', 'registerReceiptYears', 'renewalReceiptYears', 'showPlaceholderSeeder', 'rangeImportInfo'));
    }

    /**
     * Counts shown beside "Total Sangh Found" for the current filters:
     * how many प्रादेशिक विभाग / जिल्हा the found Sanghs are in, and how many
     * Register / Renewal receipts they have (limited to the chosen receipt year
     * and status, when those filters are set).
     */
    private function filterStats($filteredQuery, Request $request): array
    {
        $ids = (clone $filteredQuery)->select('sanghs.id');
        $status = in_array($request->input('payment_status'), ['unpaid', 'information_approved', 'sangh_registered'], true)
            ? $request->input('payment_status') : null;
        $registerYear = $request->filled('register_receipt_year') ? (int) $request->input('register_receipt_year') : null;
        $renewalYear = $request->filled('renewal_receipt_year') ? (int) $request->input('renewal_receipt_year') : null;

        $distinct = fn (string $column) => (clone $filteredQuery)->whereNotNull($column)->where($column, '!=', '')
            ->distinct()->count($column);

        return [
            'vibhags' => $distinct('pradeshik_vibhag'),
            'districts' => $distinct('district'),
            'register_receipts' => SanghRegistrationReceipt::query()->whereIn('sangh_id', $ids)
                ->when($registerYear, fn ($q) => $q->where('receipt_year', $registerYear))
                ->when($status, fn ($q) => $q->where('status', $status))
                ->count(),
            'renewal_receipts' => SanghRenewal::query()->whereIn('sangh_id', $ids)
                ->when($renewalYear, fn ($q) => $q->where('renewal_year', $renewalYear))
                ->when($status, fn ($q) => $q->where('status', $status))
                ->count(),
        ];
    }

    public function create()
    {
        $users = \App\Models\User::all();
        return view('sanghs.create', array_merge($this->feeFormData(), compact('users')));
    }

    /**
     * Sangh fee slab data used by the create/edit form's live fee estimate.
     * Fetched here (not in the Blade template) to avoid an @php/@endphp block
     * inside sanghs._form.blade.php, which conflicts with that file's several
     * other inline @php(...) directives during Blade compilation.
     */
    private function feeFormData(): array
    {
        return [
            'feeSlabsForForm' => SanghFeeSlab::orderBy('min_members')->get(['min_members', 'max_members', 'annual_fee']),
            'admissionFeeForForm' => (float) Setting::getValue('sangh_admission_fee', 0),
            'developmentFeeRateForForm' => (float) Setting::getValue('sangh_development_fee_rate', 0),
        ];
    }

    /**
     * Minimum 25 members is mandatory to register/renew a sangh (per fee slab rules).
     */
    private function assertMinimumMembers(array $validated): void
    {
        $male = $this->intOrNull($validated['male'] ?? null);
        $female = $this->intOrNull($validated['female'] ?? null);
        $total = ($male ?? 0) + ($female ?? 0);

        if ($total < 25) {
            throw ValidationException::withMessages([
                'male' => 'एकूण सभासद किमान 25 असणे आवश्यक आहे. (Minimum 25 total members required to save.)',
            ]);
        }
    }

    /**
     * Validate the Sangh form, honouring the fields the admin locked for this user
     * (Settings → Sangh Form Fields). Locked fields are dropped from validation, so
     * anything submitted for them is ignored, and are filled with the stored value
     * (edit) or left empty (create) — a locked field can never be changed.
     */
    private function validateSanghForm(Request $request, ?Sangh $sangh = null): array
    {
        $locked = SanghFormFields::lockedWithDependentsFor($request->user());

        $validated = $request->validate(array_diff_key($this->rules($sangh === null), array_flip($locked)));

        foreach ($locked as $field) {
            $validated[$field] = $sangh?->getAttribute($field);
        }

        return $validated;
    }

    public function store(Request $request)
    {
        $validated = $this->validateSanghForm($request);

        // A user who can't edit the member counts can't satisfy the 25-member rule;
        // the admin fills those in later.
        $lockedFields = SanghFormFields::lockedFor($request->user());
        if (! array_intersect(['male', 'female'], $lockedFields)) {
            $this->assertMinimumMembers($validated);
        }
        $allowAssignedTo = $request->user()->hasRole('superadmin');

        DB::transaction(function () use ($validated, $allowAssignedTo) {
            $sangh = Sangh::create(array_merge(
                $this->normalizePayload($validated, $allowAssignedTo),
                [
                    'created_by' => Auth::id(),
                    'created_date' => now(),
                    'sangh_sr_no' => null,
                    'unique_ref_no' => null,
                    'pradeshik_sr_no' => null,
                    'district_sr_no' => null,
                    'pradeshik_ref_no' => null,
                    'district_ref_no' => null,
                    'pradeshik_vibhag_code' => $this->vibhagCode($validated['pradeshik_vibhag'] ?? null, $validated['pradeshik_vibhag_code'] ?? null),
                    'district_code' => $this->districtCode($validated['district'] ?? null, $validated['district_code'] ?? null),
                ]
            ));

            $this->ensureRenewalsForSangh($sangh);
        });

        return redirect()->route('sanghs.index')->with('success', 'Sangh created successfully.');
    }

    public function edit(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $users = \App\Models\User::all();
        return view('sanghs.edit', array_merge(compact('sangh', 'users'), $this->feeFormData()));
    }
    public function show(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isVisibleTo($request->user()), 403);

        $sangh->load(['creator']);

        // Quick prev/next navigation between sangh records (ordered by sr. no., same as the
        // listing). sangh_sr_no is nullable (unregistered sanghs have none yet), so comparisons
        // use COALESCE to a sentinel below any real sr_no — this keeps the same "nulls sort
        // first" ordering as the index page's plain `orderBy('sangh_sr_no')` while avoiding
        // Laravel's where('col', '<', null) guard, which throws rather than silently matching nothing.
        $sortKey = $sangh->sangh_sr_no ?? -1;

        $prevSangh = Sangh::query()
            ->whereRaw('(COALESCE(sangh_sr_no, -1) < ?) OR (COALESCE(sangh_sr_no, -1) = ? AND id < ?)', [
                $sortKey, $sortKey, $sangh->id,
            ])
            ->orderByRaw('COALESCE(sangh_sr_no, -1) DESC')
            ->orderByDesc('id')
            ->first(['id']);

        $nextSangh = Sangh::query()
            ->whereRaw('(COALESCE(sangh_sr_no, -1) > ?) OR (COALESCE(sangh_sr_no, -1) = ? AND id > ?)', [
                $sortKey, $sortKey, $sangh->id,
            ])
            ->orderByRaw('COALESCE(sangh_sr_no, -1) ASC')
            ->orderBy('id')
            ->first(['id']);

        // All receipts (every status) are shown — and printed — on the details page.
        return view('sanghs.show', array_merge(
            compact('sangh', 'prevSangh', 'nextSangh'),
            $this->receiptData($sangh)
        ));
    }

    /** Separate Receipts page for one Sangh: Registered / Renewal / Backdated, in one table. */
    public function receipts(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isVisibleTo($request->user()), 403);

        return view('sanghs.receipts', array_merge(['sangh' => $sangh], $this->receiptData($sangh)));
    }

    /**
     * Everything the Receipts page needs: the registration receipt (with fees always
     * following the current fee settings), renewals with data, and which years each
     * "create" tab may offer.
     */
    private function receiptData(Sangh $sangh): array
    {
        $sangh->load(['renewals', 'registrationReceipt']);
        $registrationYear = $this->intOrNull($sangh->registration_year);

        // Fees for the New register Sangh Receipt always follow the admin-configured
        // sangh fee settings (standard fees + member-count slab) — never manually entered.
        $maleForFee = $this->intOrNull($sangh->male);
        $femaleForFee = $this->intOrNull($sangh->female);
        $totalForFee = ($maleForFee === null && $femaleForFee === null)
            ? null
            : (($maleForFee ?? 0) + ($femaleForFee ?? 0));

        $newRegisterReceipt = $sangh->registrationReceipt;
        if (! $newRegisterReceipt && $registrationYear !== null) {
            $newRegisterReceipt = new SanghRegistrationReceipt([
                'sangh_id' => $sangh->id,
                'receipt_year' => $registrationYear,
                'is_paid' => false,
            ]);
        }

        if ($newRegisterReceipt) {
            // Always reflect the current fee-slab configuration, not whatever was last saved.
            $newRegisterReceipt->admission_fee = (float) Setting::getValue('sangh_admission_fee', 0);
            $newRegisterReceipt->annual_fee = SanghFeeSlab::annualFeeForMemberCount($totalForFee) ?? 0;
            $newRegisterReceipt->development_fee = (float) Setting::getValue('sangh_development_fee_rate', 0) * ($totalForFee ?? 0);
        }

        // Only renewals that have been actively used (any meaningful data entered)
        $renewals = $this->visibleRenewals($sangh)->sortByDesc('renewal_year');

        // Which year each tab may create (same rules enforced in createRenewal()).
        [$nextRenewalYear, $backdatedYears] = $this->receiptYearOptions($sangh, $renewals);

        $receiptCount = $renewals->count() + ($newRegisterReceipt ? 1 : 0);

        // One list of every receipt (registration + renewal + backdated), newest year first,
        // for the Receipts page tables and the Sangh Details page / print.
        $allReceipts = collect();
        if ($newRegisterReceipt) {
            $allReceipts->push($this->receiptRow($newRegisterReceipt, 'registration', (int) ($newRegisterReceipt->receipt_year ?? $registrationYear)));
        }
        foreach ($renewals as $r) {
            $allReceipts->push($this->receiptRow($r, $r->is_backdated ? 'backdated' : 'renewal', (int) $r->renewal_year));
        }
        $allReceipts = $allReceipts->sortByDesc('year')->values();

        $isRegistered = $newRegisterReceipt?->status === 'sangh_registered';

        return compact('registrationYear', 'newRegisterReceipt', 'renewals', 'nextRenewalYear', 'backdatedYears', 'receiptCount', 'allReceipts', 'isRegistered');
    }

    /** A receipt (registration or renewal) as one display row. */
    private function receiptRow($receipt, string $kind, int $year): array
    {
        $isRegistration = $kind === 'registration';
        $total = $isRegistration
            ? (float) $receipt->admission_fee + (float) $receipt->annual_fee + (float) $receipt->development_fee
            : (float) $receipt->annual_fee + (float) $receipt->development_fee + (float) $receipt->penalty_fee;
        $paid = (float) ($receipt->paid_amount ?? 0);
        $status = in_array($receipt->status, ['information_approved', 'sangh_registered'], true) ? $receipt->status
            : ($receipt->status === 'paid' ? 'information_approved' : 'unpaid');

        return [
            'kind' => $kind,
            'year' => $year,
            'no' => $receipt->feskcom_receipt_no,
            'date' => $receipt->feskcom_receipt_date,
            'registered_at' => $receipt->registered_at,
            'status' => $status,
            'male' => $receipt->male_members,
            'female' => $receipt->female_members,
            'members' => $receipt->total_members,
            'admission' => $isRegistration ? (float) $receipt->admission_fee : null,
            'annual' => (float) $receipt->annual_fee,
            'development' => (float) $receipt->development_fee,
            'penalty' => $isRegistration ? null : (float) $receipt->penalty_fee,
            'total' => $total,
            'paid' => $paid,
            'balance' => $total - $paid,
            'bank' => $receipt->bank_name,
            'cheque_no' => $receipt->cheque_no,
            'cheque_date' => $receipt->cheque_date,
            'modal' => $isRegistration ? '#receiptModalNewRegister' : '#receiptModal' . $year,
            'edit' => $isRegistration ? '#editReceiptNewRegister' : '#editRenewal' . $year,
        ];
    }

    /** Oldest year a backdated receipt may be created for. */
    public const FIRST_RECEIPT_YEAR = SanghYears::FIRST; // 1979-80

    /**
     * Years the Receipts section may create for this Sangh:
     *  - Renewal: only the NEXT year — one after the latest renewal (or after the
     *    registration receipt year when there are no renewals yet), at most next year.
     *  - Backdated: any PAST year from 1979-80 to last year without a receipt (never the current year).
     *
     * @return array{0: ?int, 1: int[]}
     */
    private function receiptYearOptions(Sangh $sangh, $visibleRenewals): array
    {
        $currentYear = (int) date('Y');
        $usedYears = $visibleRenewals->pluck('renewal_year')->map(fn ($y) => (int) $y)->all();

        $base = $usedYears ? max($usedYears)
            : ($sangh->registrationReceipt?->receipt_year ?? $this->intOrNull($sangh->registration_year) ?? ($currentYear - 1));
        $next = (int) $base + 1;
        $nextRenewalYear = ($next <= SanghYears::last() && ! in_array($next, $usedYears, true)) ? $next : null;

        $backdatedYears = array_values(array_filter(
            range($currentYear - 1, self::FIRST_RECEIPT_YEAR),
            fn ($y) => ! in_array($y, $usedYears, true)
        ));

        return [$nextRenewalYear, $backdatedYears];
    }

    /** Renewals that have real data (blank auto-created stubs are ignored). */
    private function visibleRenewals(Sangh $sangh)
    {
        return $sangh->renewals()->get()->filter(fn ($r) => $r->is_paid
            || $r->feskcom_receipt_no !== null || $r->feskcom_receipt_date !== null
            || $r->annual_fee !== null || $r->development_fee !== null || $r->penalty_fee !== null
            || $r->paid_amount !== null || $r->male_members !== null || $r->female_members !== null
            || $r->total_members !== null);
    }

    public function updateRegistrationReceipt(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $validated = $request->validate([
            'status' => 'required|in:unpaid,information_approved,sangh_registered',
            'feskcom_receipt_date' => 'nullable|date',
            'penalty_fee' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'cheque_no' => 'nullable|string|max:50',
            'cheque_date' => 'nullable|date',
        ]);

        $this->saveRegistrationReceipt($sangh, $validated);

        return redirect()->route('sanghs.receipts', $sangh)->with('success', 'Registered receipt updated.');
    }

    /** Map a status label or code (screen / Excel) to the stored code. */
    private function statusCode(mixed $value): string
    {
        $v = str_replace([' ', '-'], '_', strtolower(trim((string) $value)));

        return match ($v) {
            'sangh_registered', 'registered' => 'sangh_registered',
            'information_approved', 'approved', 'paid' => 'information_approved', // "Paid" = old files
            default => 'unpaid',
        };
    }

    /** Human label for a status code (what screens and Excel show). */
    private function statusLabel(?string $code): string
    {
        return match ($code) {
            'sangh_registered' => 'Sangh Registered',
            'information_approved', 'paid' => 'Information approved',
            default => 'Unpaid',
        };
    }

    /**
     * Save the New register Sangh receipt — the same rules for the screen and Excel import:
     * fees from Settings → Sangh Fees and the Sangh's members; a receipt date makes it
     * "Information approved"; receipt no. FSNEW/{id} is assigned once; "Sangh Registered"
     * records the registered date and assigns the Sangh numbering.
     * $data: status, feskcom_receipt_date, penalty_fee, paid_amount, bank_name, cheque_no,
     * cheque_date — and from import only: feskcom_receipt_no, registered_at.
     */
    private function saveRegistrationReceipt(Sangh $sangh, array $data): SanghRegistrationReceipt
    {
        $maleMembers = $this->intOrNull($sangh->male);
        $femaleMembers = $this->intOrNull($sangh->female);
        $totalMembers = ($maleMembers === null && $femaleMembers === null)
            ? null
            : (($maleMembers ?? 0) + ($femaleMembers ?? 0));

        // प्रवेश शुल्क / वार्षिक शुल्क / विकास निधी शुल्क are never entered manually —
        // always derived from the admin-configured sangh fee settings/slabs.
        $admissionFee = (float) Setting::getValue('sangh_admission_fee', 0);
        $annualFee = SanghFeeSlab::annualFeeForMemberCount($totalMembers) ?? 0;
        $developmentFee = (float) Setting::getValue('sangh_development_fee_rate', 0) * ($totalMembers ?? 0);

        $year = $this->intOrNull($sangh->registration_year) ?? (int) date('Y');

        $receipt = SanghRegistrationReceipt::query()->firstOrCreate(
            ['sangh_id' => $sangh->id],
            ['receipt_year' => $year, 'is_paid' => false, 'status' => 'unpaid']
        );

        $hasReceiptDate = ! empty($data['feskcom_receipt_date']);
        $status = $data['status'] ?? 'unpaid';
        if ($hasReceiptDate && $status === 'unpaid') {
            $status = 'information_approved';
        }

        // "Sangh Registered" happens once: after that the status (and its date) is locked;
        // only the payment details can still be corrected.
        $alreadyRegistered = $receipt->status === 'sangh_registered';
        if ($alreadyRegistered) {
            $status = 'sangh_registered';
        }

        // Receipt number: an imported one is kept; otherwise auto-assigned once, never overwritten.
        if (! empty($data['feskcom_receipt_no'])) {
            $receipt->feskcom_receipt_no = (string) $data['feskcom_receipt_no'];
        } elseif (empty($receipt->feskcom_receipt_no) && ($hasReceiptDate || $status === 'sangh_registered')) {
            $receipt->feskcom_receipt_no = 'FSNEW/' . $receipt->id;
        }

        // संघ नोंदणी दिनांक: set the one time it becomes "Sangh Registered", never changed after.
        $registeredAt = $status === 'sangh_registered'
            ? ($alreadyRegistered && $receipt->registered_at
                ? $receipt->registered_at
                : (($data['registered_at'] ?? null) ?: now()->toDateString()))
            : null;

        $receipt->update([
            'registered_at' => $registeredAt,
            'receipt_year' => $year,
            'is_paid' => in_array($status, ['information_approved', 'sangh_registered'], true),
            'status' => $status,
            'feskcom_receipt_no' => $receipt->feskcom_receipt_no,
            'feskcom_receipt_date' => $data['feskcom_receipt_date'] ?? null,
            'user_id' => auth()->id(),
            'male_members' => $maleMembers,
            'female_members' => $femaleMembers,
            'total_members' => $totalMembers,
            'annual_fee' => $annualFee,
            'admission_fee' => $admissionFee,
            'development_fee' => $developmentFee,
            'penalty_fee' => $data['penalty_fee'] ?? null,
            'paid_amount' => $data['paid_amount'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'cheque_no' => $data['cheque_no'] ?? null,
            'cheque_date' => $data['cheque_date'] ?? null,
        ]);

        $this->assignNumberingIfRegistered($sangh, $status, $registeredAt);

        return $receipt;
    }

    /**
     * Save a Renewal / Backdated receipt — the same rules for the screen and Excel import:
     * fees from Settings → Sangh Fees for the member count; receipt no. FSREN/{id} assigned
     * once; a receipt date → "Information approved". A renewal is never "Sangh Registered"
     * (that happens once, on the registration receipt), so it never assigns numbering.
     * $data: status, feskcom_receipt_date, male_members, female_members, penalty_fee,
     * paid_amount, bank_name, cheque_no, cheque_date — import only: feskcom_receipt_no,
     * is_backdated.
     */
    private function saveRenewal(Sangh $sangh, int $year, array $data): SanghRenewal
    {
        $maleMembers = $data['male_members'] ?? null;
        $femaleMembers = $data['female_members'] ?? null;
        $totalMembers = ($maleMembers === null && $femaleMembers === null)
            ? null
            : (($maleMembers ?? 0) + ($femaleMembers ?? 0));

        // वार्षिक शुल्क / विकास निधी शुल्क are never entered manually —
        // always derived from the admin-configured sangh fee settings/slabs.
        $annualFee = SanghFeeSlab::annualFeeForMemberCount($totalMembers) ?? 0;
        $developmentFee = (float) Setting::getValue('sangh_development_fee_rate', 0) * ($totalMembers ?? 0);

        $renewal = SanghRenewal::query()->firstOrCreate(
            ['sangh_id' => $sangh->id, 'renewal_year' => $year],
            ['is_paid' => false, 'status' => 'unpaid', 'is_backdated' => (bool) ($data['is_backdated'] ?? false)]
        );

        // Receipt number: an imported one is kept; otherwise auto-assigned once, never overwritten.
        if (! empty($data['feskcom_receipt_no'])) {
            $renewal->feskcom_receipt_no = (string) $data['feskcom_receipt_no'];
        } elseif (empty($renewal->feskcom_receipt_no)) {
            $renewal->feskcom_receipt_no = 'FSREN/' . $renewal->id;
        }

        $hasReceiptDate = ! empty($data['feskcom_receipt_date']);
        // "Sangh Registered" is registration-only; on a renewal (e.g. an old Excel file) it means approved.
        $status = match ($data['status'] ?? null) {
            'information_approved', 'sangh_registered' => 'information_approved',
            default => 'unpaid',
        };
        if ($hasReceiptDate && $status === 'unpaid') {
            $status = 'information_approved';
        }

        $attributes = [
            'registered_at' => null,
            'is_paid' => in_array($status, ['information_approved', 'sangh_registered'], true),
            'status' => $status,
            'feskcom_receipt_no' => $renewal->feskcom_receipt_no,
            'feskcom_receipt_date' => $data['feskcom_receipt_date'] ?? null,
            'user_id' => auth()->id(),
            'male_members' => $maleMembers,
            'female_members' => $femaleMembers,
            'total_members' => $totalMembers,
            'annual_fee' => $annualFee,
            'development_fee' => $developmentFee,
            'penalty_fee' => $data['penalty_fee'] ?? null,
            'paid_amount' => $data['paid_amount'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'cheque_no' => $data['cheque_no'] ?? null,
            'cheque_date' => $data['cheque_date'] ?? null,
        ];
        if (array_key_exists('is_backdated', $data)) {
            $attributes['is_backdated'] = (bool) $data['is_backdated'];
        }
        $renewal->update($attributes);

        return $renewal;
    }

    /** Statuses a Renewal / Backdated receipt can have ("Sangh Registered" is registration-only). */
    public const RENEWAL_STATUSES = ['unpaid', 'information_approved'];

    /** A Sangh is registered once its registration receipt is "Sangh Registered". */
    private function isRegistered(Sangh $sangh): bool
    {
        return SanghRegistrationReceipt::query()->where('sangh_id', $sangh->id)
            ->where('status', 'sangh_registered')->exists();
    }

    /**
     * The one time the registration receipt becomes "Sangh Registered": the Sangh records that
     * date as its संघ नोंदणी दिनांक (registered_at) and — if it has none yet — gets
     * its numbering, with the FY/quarter taken from that date.
     */
    private function assignNumberingIfRegistered(Sangh $sangh, string $status, $registeredAt = null): void
    {
        if ($status !== 'sangh_registered') {
            return;
        }

        $registeredAt = $sangh->registered_at ?? ($registeredAt ?: now()->toDateString());
        $updates = $sangh->registered_at === null ? ['registered_at' => $registeredAt] : [];
        if ($sangh->sangh_sr_no === null) {
            $updates += $this->makeNumbering($sangh, $registeredAt);
        }
        if ($updates) {
            $sangh->update($updates);
        }
    }


    public function update(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $validated = $this->validateSanghForm($request, $sangh);
        // Minimum-25-members is only enforced at initial registration (store()), not on
        // every subsequent edit — federations often digitize records in stages (name/address
        // captured first, headcount confirmed later), and placeholder/legacy rows shouldn't
        // become permanently un-editable until that headcount is filled in.

        $selectedVibhag = $validated['pradeshik_vibhag'] ?? null;
        $selectedDistrict = $validated['district'] ?? null;

        // Numbering (sangh_sr_no, unique_ref_no, pradeshik/district ref no) is assigned once,
        // when the receipt/renewal status first becomes "sangh_registered" — never regenerated
        // on a plain edit. Only the short display codes are kept in sync with the selected
        // vibhag/district here.
        $numbering = [
            'pradeshik_vibhag_code' => $this->vibhagCode($selectedVibhag, $validated['pradeshik_vibhag_code'] ?? null),
            'district_code' => $this->districtCode($selectedDistrict, $validated['district_code'] ?? null),
        ];

        $sangh->update(array_merge($this->normalizePayload($validated, $request->user()->hasRole('superadmin')), $numbering));
        $this->ensureRenewalsForSangh($sangh);

        return redirect()->route('sanghs.index')->with('success', 'Sangh updated successfully.');
    }


    public function destroy(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $sangh->delete();
        return redirect()->route('sanghs.index')->with('success', 'Sangh deleted.');
    }

    public function exportExcel(\Illuminate\Http\Request $request)
    {
        // Renewals sheet follows the filter too: with a renewal year chosen, only that
        // year's renewals (and, with a status, only renewals in that status).
        $renewalYear = $request->filled('renewal_receipt_year') ? (int) $request->input('renewal_receipt_year') : null;
        $status = in_array($request->input('payment_status'), ['unpaid', 'information_approved', 'sangh_registered'], true)
            ? $request->input('payment_status') : null;
        $query = Sangh::query()->with(['registrationReceipt', 'renewals' => function ($r) use ($renewalYear, $status) {
            if ($renewalYear) {
                $r->where('renewal_year', $renewalYear);
                if ($status) {
                    $r->where('status', $status);
                }
            }
            $r->orderBy('renewal_year');
        }]);
        $this->applyFilters($query, $request);
        $sanghs = $query->orderBy('sangh_sr_no')->orderBy('id')->get();

        $masterRows = [];
        $registrationRows = [];
        $renewalRows = [];
        foreach ($sanghs as $s) {
            $masterRows[] = $this->rowFor(self::IMPORT_HEADERS, $this->masterValues($s));

            if ($rc = $s->registrationReceipt) {
                $total = (float) $rc->admission_fee + (float) $rc->annual_fee + (float) $rc->development_fee;
                $registrationRows[] = $this->rowFor(self::REGISTRATION_HEADERS, $this->receiptLinkValues($s) + [
                    'वर्ष' => $rc->receipt_year,
                    'स्थिती' => $this->statusLabel($rc->status),
                    'फेस्कॉम पावती क्र.' => $rc->feskcom_receipt_no,
                    'फेस्कॉम पावती दिनांक' => optional($rc->feskcom_receipt_date)->format('Y-m-d'),
                    'संघ नोंदणी दिनांक' => optional($rc->registered_at)->format('Y-m-d'),
                    'पुरुष सभासद संख्या' => $rc->male_members,
                    'महिला सभासद संख्या' => $rc->female_members,
                    'एकूण सभासद संख्या' => $rc->total_members,
                    'प्रवेश शुल्क' => $rc->admission_fee,
                    'वार्षिक शुल्क' => $rc->annual_fee,
                    'विकास निधी शुल्क' => $rc->development_fee,
                    'एकूण रक्कम' => $total,
                    'पावती रक्कम (भरलेली)' => $rc->paid_amount,
                    'बाकी रक्कम' => $total - (float) $rc->paid_amount,
                    'बँकेचे नाव' => $rc->bank_name,
                    self::CHEQUE_HEADER => $rc->cheque_no,
                    'चेक दिनांक' => optional($rc->cheque_date)->format('Y-m-d'),
                ]);
            }

            foreach ($s->renewals as $rn) {
                $total = (float) $rn->annual_fee + (float) $rn->development_fee + (float) $rn->penalty_fee;
                $renewalRows[] = $this->rowFor(self::RENEWAL_HEADERS, $this->receiptLinkValues($s) + [
                    'वर्ष' => $rn->renewal_year,
                    'प्रकार' => $rn->is_backdated ? 'Backdated' : 'Renewal',
                    'स्थिती' => $this->statusLabel($rn->status),
                    'फेस्कॉम पावती क्र.' => $rn->feskcom_receipt_no,
                    'फेस्कॉम पावती दिनांक' => optional($rn->feskcom_receipt_date)->format('Y-m-d'),
                    'संघ नोंदणी दिनांक' => optional($rn->registered_at)->format('Y-m-d'),
                    'पुरुष सभासद संख्या' => $rn->male_members,
                    'महिला सभासद संख्या' => $rn->female_members,
                    'एकूण सभासद संख्या' => $rn->total_members,
                    'वार्षिक शुल्क' => $rn->annual_fee,
                    'विकास निधी शुल्क' => $rn->development_fee,
                    'दंड शुल्क' => $rn->penalty_fee,
                    'एकूण रक्कम' => $total,
                    'पावती रक्कम (भरलेली)' => $rn->paid_amount,
                    'बाकी रक्कम' => $total - (float) $rn->paid_amount,
                    'बँकेचे नाव' => $rn->bank_name,
                    self::CHEQUE_HEADER => $rn->cheque_no,
                    'चेक दिनांक' => optional($rn->cheque_date)->format('Y-m-d'),
                ]);
            }
        }

        $export = new \App\Exports\SanghWorkbookExport([
            [self::SHEET_MASTER, self::IMPORT_HEADERS, $masterRows],
            [self::SHEET_REGISTRATION, self::REGISTRATION_HEADERS, $registrationRows],
            [self::SHEET_RENEWALS, self::RENEWAL_HEADERS, $renewalRows],
        ]);

        return Excel::download($export, 'sangh_export_' . now()->format('Ymd_His') . '.xlsx');
    }

    /** One row in header order from a [header => value] map (missing headers → empty). */
    private function rowFor(array $headers, array $values): array
    {
        return array_map(fn ($h) => $values[$h] ?? null, $headers);
    }

    /** All Sangh Master columns for one Sangh, keyed by header. */
    private function masterValues(Sangh $s): array
    {
        return [
            self::ID_HEADER => $s->id,
            'Sangh Sr. No.' => $s->sangh_sr_no,
            'Unique संघाचा अनु क्र.' => $s->unique_ref_no,
            'प्रादेशिक विभागातील संघाचा अनु क्र.' => $s->pradeshik_ref_no,
            'जिल्हा मधे संघाचा अनु. क्र.' => $s->district_ref_no,
            'वर्ष' => $s->registration_year,
            'संघाचे नाव' => $s->name_of_sangh,
            'श्रेणी' => $s->category_code,
            'संघ प्रकार' => $s->sangh_type_code,
            'प्रादेशिक विभाग' => $s->pradeshik_vibhag,
            'जिल्हा' => $s->district,
            'तालुका' => $s->taluka,
            'गाव' => $s->village,
            'शहर' => $s->city,
            'मुक्काम पोस्ट' => $s->mukkam_post,
            'पिनकोड' => $s->pincode,
            'पत्ता' => $s->address,
            'रस्ता / पथ' => $s->road_path,
            'विभाग/प्रभाग' => $s->ward_section,
            'पुरुष सभासद संख्या' => $s->male,
            'महिला सभासद संख्या' => $s->female,
            'एकूण सभासद संख्या' => $s->total_members,
            'प्रादेशिक प्रवेश शुल्क' => $s->pradeshik_admission_fee,
            'प्रादेशिक वार्षिक शुल्क' => $s->pradeshik_annual_fee,
            'प्रादेशिक विकास निधी शुल्क' => $s->pradeshik_development_fee,
            'अध्यक्ष' => $s->president,
            'अध्यक्ष मोबाईल' => $s->president_phone,
            'अध्यक्ष व्हॉट्सअप' => $s->president_whatsapp,
            'अध्यक्ष इमेल' => $s->president_email,
            'सचिव' => $s->secretary,
            'सचिव मोबाईल' => $s->secretary_phone,
            'सचिव व्हॉट्सअप' => $s->secretary_whatsapp,
            'सचिव इमेल' => $s->secretary_email,
            'संघ नोंदणी दिनांक' => optional($s->registered_at)->format('Y-m-d'),
        ];
    }

    /** Columns that link a receipt row back to its Sangh. */
    private function receiptLinkValues(Sangh $s): array
    {
        return [
            self::ID_HEADER => $s->id,
            'Unique संघाचा अनु क्र.' => $s->unique_ref_no,
            'संघाचे नाव' => $s->name_of_sangh,
        ];
    }

    /**
     * Import a Sangh workbook (same layout as Export / Template):
     *  - "Sangh Master": updates the Sangh with that Sangh ID (or Unique number), else creates it.
     *    Numbering in the file is kept for new Sanghs; otherwise it is assigned on registration.
     *  - "Registration Receipts" and "Renewals": saved with the same rules as the Receipts page.
     * Old two-sheet files (Master + Renewals with Paid/Unpaid) still import.
     * All or nothing: any problem → nothing is saved and the rows are listed.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => Uploads::rule(Uploads::EXCEL_TYPES, Uploads::MAX_EXCEL_KB),
        ]);

        $book = IOFactory::load($request->file('excel_file')->getRealPath());
        $sheetRows = fn ($sheet) => $sheet ? $sheet->toArray(null, true, true, false) : [];
        $byTitle = [];
        foreach ($book->getAllSheets() as $sheet) {
            $byTitle[mb_strtolower(trim($sheet->getTitle()))] = $sheet;
        }
        $masterRows = $sheetRows($byTitle[mb_strtolower(self::SHEET_MASTER)] ?? $book->getSheet(0));
        $registrationRows = $sheetRows($byTitle[mb_strtolower(self::SHEET_REGISTRATION)] ?? null);
        // Old files: second sheet = renewals.
        $renewalRows = $sheetRows($byTitle[mb_strtolower(self::SHEET_RENEWALS)]
            ?? (! isset($byTitle[mb_strtolower(self::SHEET_REGISTRATION)]) && $book->getSheetCount() > 1 ? $book->getSheet(1) : null));

        if (count($masterRows) < 2 && count($registrationRows) < 2 && count($renewalRows) < 2) {
            return back()->with('error', 'Excel file has no data rows.');
        }

        $errors = [];
        $counts = ['created' => 0, 'updated' => 0, 'registration' => 0, 'renewal' => 0];
        $byName = []; // Sanghs touched in this file, by name — links receipt rows of brand-new Sanghs

        try {
            DB::transaction(function () use ($masterRows, $registrationRows, $renewalRows, &$errors, &$counts, &$byName) {
                // ---------- Sangh Master ----------
                foreach ($this->dataRows($masterRows) as [$line, $mapped]) {
                    $attributes = $this->sanghAttributesFromImportRow($mapped);
                    if ($attributes['name_of_sangh'] === '') {
                        $errors[] = self::SHEET_MASTER . " row {$line}: संघाचे नाव is required.";
                        continue;
                    }
                    if ($phoneErrors = $this->importPhoneErrors($attributes, self::SHEET_MASTER . " row {$line}")) {
                        array_push($errors, ...$phoneErrors);
                        continue;
                    }

                    $sangh = $this->findImportSangh($mapped, false);
                    if ($sangh && ! $sangh->isManageableBy(auth()->user())) {
                        $errors[] = self::SHEET_MASTER . " row {$line}: you can only update your own / assigned Sanghs.";
                        continue;
                    }
                    if ($sangh) {
                        // Existing Sangh: update details, never touch its issued numbering.
                        $sangh->update($attributes);
                        $counts['updated']++;
                    } else {
                        // New Sangh: keep numbering given in the file (historic records);
                        // otherwise it is assigned when the Sangh is registered.
                        $numbering = array_filter([
                            'sangh_sr_no' => $this->intOrNull($mapped['Sangh Sr. No.'] ?? null),
                            'unique_ref_no' => trim((string) ($mapped['Unique संघाचा अनु क्र.'] ?? '')) ?: null,
                            'pradeshik_ref_no' => trim((string) ($mapped['प्रादेशिक विभागातील संघाचा अनु क्र.'] ?? '')) ?: null,
                            'district_ref_no' => trim((string) ($mapped['जिल्हा मधे संघाचा अनु. क्र.'] ?? '')) ?: null,
                        ], fn ($v) => $v !== null);
                        if (isset($numbering['sangh_sr_no']) && Sangh::where('sangh_sr_no', $numbering['sangh_sr_no'])->exists()) {
                            $errors[] = self::SHEET_MASTER . " row {$line}: Sangh Sr. No. {$numbering['sangh_sr_no']} already belongs to another Sangh.";
                            continue;
                        }
                        $sangh = Sangh::create($attributes + $numbering + [
                            'created_by' => Auth::id(),
                            'created_date' => now(),
                            'pradeshik_vibhag_code' => $this->vibhagCode($attributes['pradeshik_vibhag']),
                            'district_code' => $this->districtCode($attributes['district']),
                        ]);
                        $counts['created']++;
                    }
                    $byName[$this->nameKey($sangh->name_of_sangh)] = $sangh;
                }

                // ---------- Registration Receipts ----------
                foreach ($this->dataRows($registrationRows) as [$line, $mapped]) {
                    $sangh = $this->findImportSangh($mapped, true, $byName);
                    if (! $sangh || ! $sangh->isManageableBy(auth()->user())) {
                        $errors[] = self::SHEET_REGISTRATION . " row {$line}: " . (! $sangh ? 'Sangh not found (check Sangh ID / Unique number / संघाचे नाव).' : 'you can only update your own / assigned Sanghs.');
                        continue;
                    }
                    $this->saveRegistrationReceipt($sangh->refresh(), $this->receiptDataFromImportRow($mapped));
                    $counts['registration']++;
                }

                // ---------- Renewals (Renewal + Backdated) ----------
                foreach ($this->dataRows($renewalRows) as [$line, $mapped]) {
                    $sangh = $this->findImportSangh($mapped, true, $byName);
                    $year = $this->intOrNull($mapped['वर्ष'] ?? null);
                    if (! $sangh || $year === null || ! $sangh->isManageableBy(auth()->user())) {
                        $errors[] = self::SHEET_RENEWALS . " row {$line}: " . (! $sangh ? 'Sangh not found (check Sangh ID / Unique number / संघाचे नाव).'
                            : ($year === null ? 'वर्ष is required.' : 'you can only update your own / assigned Sanghs.'));
                        continue;
                    }
                    if (! $this->isRegistered($sangh) && ! SanghRenewal::where('sangh_id', $sangh->id)->where('renewal_year', $year)->exists()) {
                        $errors[] = self::SHEET_RENEWALS . " row {$line}: the Sangh is not registered yet — add its Registration Receipts row with स्थिती \"Sangh Registered\" first.";
                        continue;
                    }
                    if (! SanghYears::contains($year)) {
                        $errors[] = self::SHEET_RENEWALS . " row {$line}: वर्ष {$year} is outside " . self::FIRST_RECEIPT_YEAR . '–' . SanghYears::last() . '.';
                        continue;
                    }
                    $data = $this->receiptDataFromImportRow($mapped) + [
                        'male_members' => $this->intOrNull($mapped['पुरुष सभासद संख्या'] ?? null),
                        'female_members' => $this->intOrNull($mapped['महिला सभासद संख्या'] ?? null),
                    ];
                    if (isset($mapped['प्रकार'])) {
                        $data['is_backdated'] = mb_strtolower(trim((string) $mapped['प्रकार'])) === 'backdated';
                    }
                    $this->saveRenewal($sangh->refresh(), $year, $data);
                    $counts['renewal']++;
                }

                if ($errors) {
                    throw new \RuntimeException('import-errors'); // roll everything back
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'import-errors') {
                throw $e;
            }

            return redirect()->route('sanghs.index')
                ->with('error', 'Nothing was imported. Please fix the rows below and upload again.')
                ->with('import_errors', array_slice($errors, 0, 30));
        }

        return redirect()->route('sanghs.index')->with('success', sprintf(
            'Excel imported: %d Sangh(s) created, %d updated, %d registration receipt(s), %d renewal/backdated receipt(s).',
            $counts['created'], $counts['updated'], $counts['registration'], $counts['renewal']
        ));
    }

    /** Non-empty data rows of a sheet as [Excel row number, row mapped by header]. */
    private function dataRows(array $rows): array
    {
        if (count($rows) < 2) {
            return [];
        }
        $header = array_map(fn ($h) => trim((string) $h), $rows[0]);
        $out = [];
        for ($i = 1; $i < count($rows); $i++) {
            if (count(array_filter($rows[$i], fn ($v) => $v !== null && trim((string) $v) !== ''))) {
                $out[] = [$i + 1, $this->mapRowByHeader($header, $rows[$i])];
            }
        }

        return $out;
    }

    /**
     * The Sangh a row belongs to: Sangh ID, then Unique number, then — for receipt
     * rows only — a Sangh with that exact name from this file (or one unique match).
     */
    private function findImportSangh(array $mapped, bool $allowName, array $byName = []): ?Sangh
    {
        $id = $this->intOrNull($mapped[self::ID_HEADER] ?? null);
        if ($id && ($sangh = Sangh::find($id))) {
            return $sangh;
        }
        $ref = trim((string) ($mapped['Unique संघाचा अनु क्र.'] ?? ''));
        if ($ref !== '' && ($sangh = Sangh::where('unique_ref_no', $ref)->first())) {
            return $sangh;
        }
        if ($allowName) {
            $key = $this->nameKey($mapped['संघाचे नाव'] ?? '');
            if ($key !== '' && isset($byName[$key])) {
                return $byName[$key];
            }
            if ($key !== '') {
                // Name match only among Sanghs this user can see.
                $matches = Sangh::query()->visibleTo(auth()->user())
                    ->where('name_of_sangh', trim((string) $mapped['संघाचे नाव']))->limit(2)->get();
                if ($matches->count() === 1) {
                    return $matches->first();
                }
            }
        }

        return null;
    }

    private function nameKey(mixed $name): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $name)));
    }

    /** Receipt fields shared by the Registration and Renewals sheets (fees are calculated, not read). */
    private function receiptDataFromImportRow(array $mapped): array
    {
        return [
            'status' => $this->statusCode($mapped['स्थिती'] ?? ''),
            'feskcom_receipt_no' => trim((string) ($mapped['फेस्कॉम पावती क्र.'] ?? '')) ?: null,
            'feskcom_receipt_date' => $this->importDate($mapped['फेस्कॉम पावती दिनांक'] ?? null),
            'registered_at' => $this->importDate($mapped['संघ नोंदणी दिनांक'] ?? null),
            'penalty_fee' => $this->intOrNull($mapped['दंड शुल्क'] ?? null),
            'paid_amount' => is_numeric($mapped['पावती रक्कम (भरलेली)'] ?? null) ? (int) round((float) $mapped['पावती रक्कम (भरलेली)']) : null,
            'bank_name' => trim((string) ($mapped['बँकेचे नाव'] ?? '')) ?: null,
            'cheque_no' => trim((string) ($mapped[self::CHEQUE_HEADER] ?? $mapped[self::OLD_CHEQUE_HEADER] ?? '')) ?: null,
            'cheque_date' => $this->importDate($mapped['चेक दिनांक'] ?? null),
        ];
    }

    /** Excel date cell → Y-m-d. Accepts 2026-11-05, 05-11-2026, 05/11/2026 or an Excel date number. */
    private function importDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $value = trim((string) $value);
        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 80000) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'd.m.Y', 'm/d/Y'] as $format) {
            $d = \DateTime::createFromFormat('!' . $format, $value);
            if ($d && $d->format($format) === $value) {
                return $d->format('Y-m-d');
            }
        }
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Sangh fields from one Excel row (template columns, already mapped by header).
     * Shared by the normal Import and the "Fill Blank Rows" import.
     */
    private function sanghAttributesFromImportRow(array $mapped): array
    {
        // एकूण सभासद = पुरुष + महिला (as on the form) — a stale total column in Excel is ignored.
        $male = $this->intOrNull($mapped['पुरुष सभासद संख्या'] ?? null);
        $female = $this->intOrNull($mapped['महिला सभासद संख्या'] ?? null);
        $total = ($male === null && $female === null)
            ? $this->intOrNull($mapped['एकूण सभासद संख्या'] ?? null)
            : ($male ?? 0) + ($female ?? 0);

        return [
            'name_of_sangh' => trim((string) ($mapped['संघाचे नाव'] ?? '')),
            'registration_year' => $this->intOrNull($mapped['वर्ष'] ?? null),
            'category_code' => $this->codeChar($mapped['श्रेणी'] ?? null),
            'sangh_type_code' => $this->codeChar($mapped['संघ प्रकार'] ?? null),
            'pradeshik_vibhag' => $mapped['प्रादेशिक विभाग'] ?? null,
            'district' => $mapped['जिल्हा'] ?? null,
            'taluka' => $mapped['तालुका'] ?? null,
            'village' => $mapped['गाव'] ?? null,
            'city' => $mapped['शहर'] ?? null,
            'mukkam_post' => $mapped['मुक्काम पोस्ट'] ?? null,
            'pincode' => $mapped['पिनकोड'] ?? null,
            'address' => $mapped['पत्ता'] ?? null,
            'road_path' => $mapped['रस्ता / पथ'] ?? null,
            'ward_section' => $mapped['विभाग/प्रभाग'] ?? null,
            'male' => $this->intOrNull($mapped['पुरुष सभासद संख्या'] ?? null),
            'female' => $this->intOrNull($mapped['महिला सभासद संख्या'] ?? null),
            'total_members' => $total,
            'pradeshik_admission_fee' => $this->decimalOrNull($mapped['प्रादेशिक प्रवेश शुल्क'] ?? null),
            'pradeshik_annual_fee' => $this->decimalOrNull($mapped['प्रादेशिक वार्षिक शुल्क'] ?? null),
            'pradeshik_development_fee' => $this->decimalOrNull($mapped['प्रादेशिक विकास निधी शुल्क'] ?? null),
            'president' => $mapped['अध्यक्ष'] ?? null,
            // Contact numbers: formatting removed (+91, spaces, dashes); checked by importPhoneErrors().
            'president_phone' => Phone::clean($mapped['अध्यक्ष मोबाईल'] ?? null),
            'president_whatsapp' => Phone::clean($mapped['अध्यक्ष व्हॉट्सअप'] ?? null),
            'president_email' => $mapped['अध्यक्ष इमेल'] ?? null,
            'secretary' => $mapped['सचिव'] ?? null,
            'secretary_phone' => Phone::clean($mapped['सचिव मोबाईल'] ?? null),
            'secretary_whatsapp' => Phone::clean($mapped['सचिव व्हॉट्सअप'] ?? null),
            'secretary_email' => $mapped['सचिव इमेल'] ?? null,
        ];
    }

    /** Contact-number problems in one imported Sangh row (each must be exactly 10 digits). */
    private function importPhoneErrors(array $attributes, string $where): array
    {
        $columns = [
            'president_phone' => 'अध्यक्ष मोबाईल', 'president_whatsapp' => 'अध्यक्ष व्हॉट्सअप',
            'secretary_phone' => 'सचिव मोबाईल', 'secretary_whatsapp' => 'सचिव व्हॉट्सअप',
        ];
        $errors = [];
        foreach ($columns as $field => $header) {
            if (! Phone::isValidMobile($attributes[$field] ?? null)) {
                $errors[] = "{$where}: {$header} \"{$attributes[$field]}\" must be a 10-digit number (numbers only).";
            }
        }

        return $errors;
    }

    /**
     * Import into a range of existing Sanghs by Sangh No. (blank placeholder rows
     * or already-filled ones): Excel data row 1 → Sangh No. "from", row 2 → from+1, …
     * Each Sangh keeps its number. Filled Sanghs are only updated when the user ticks
     * "overwrite", and an empty Excel cell keeps the current value. All-or-nothing:
     * if the row count doesn't match the range, a number doesn't exist, a row has no
     * name, or filled Sanghs are in range without "overwrite", nothing is saved.
     */
    public function importByRange(Request $request)
    {
        $request->validate([
            'from_no' => 'required|integer|min:1',
            'to_no' => 'required|integer|gte:from_no',
            'excel_file' => Uploads::rule(Uploads::EXCEL_TYPES, Uploads::MAX_EXCEL_KB),
            'overwrite' => 'nullable|boolean',
        ], [
            'to_no.gte' => '"To Sangh No." must be the same as or after "From Sangh No.".',
        ]);

        $from = (int) $request->input('from_no');
        $to = (int) $request->input('to_no');
        $expected = $to - $from + 1;
        $overwrite = $request->boolean('overwrite');
        $fail = fn (string $message, array $details = []) => back()->withInput()
            ->with('error', $message)->with('import_errors', $details)->with('open_range_import', true);

        if ($expected > 2000) {
            return $fail('Please fill at most 2000 Sanghs per upload.');
        }

        // Read the first sheet; keep non-empty data rows with their Excel row number.
        $sheet = IOFactory::load($request->file('excel_file')->getRealPath())->getSheet(0)->toArray(null, true, true, false);
        $header = array_map(fn ($h) => trim((string) $h), $sheet[0] ?? []);
        if (! in_array('संघाचे नाव', $header, true)) {
            return $fail('This Excel file is not in the Sangh template format (column "संघाचे नाव" not found). Download the Template and use that.');
        }

        $rows = [];
        for ($i = 1; $i < count($sheet); $i++) {
            if (count(array_filter($sheet[$i], fn ($v) => $v !== null && trim((string) $v) !== ''))) {
                $rows[] = ['excel_row' => $i + 1, 'mapped' => $this->mapRowByHeader($header, $sheet[$i])];
            }
        }

        if (count($rows) !== $expected) {
            return $fail("Sangh No. {$from} to {$to} is {$expected} Sangh(s), but the Excel file has " . count($rows) . ' data row(s). They must match exactly.');
        }

        // Every number in the range must exist; filled ones need "overwrite" ticked.
        $targets = Sangh::query()->whereBetween('sangh_sr_no', [$from, $to])->get()->keyBy('sangh_sr_no');
        $problems = [];
        $filledCount = 0;
        for ($no = $from; $no <= $to; $no++) {
            $sangh = $targets->get($no);
            if (! $sangh) {
                $problems[] = "Sangh No. {$no} does not exist.";
            } elseif (trim((string) $sangh->name_of_sangh) !== '') {
                $filledCount++;
                if (! $overwrite) {
                    $problems[] = "Sangh No. {$no} already has data (\"{$sangh->name_of_sangh}\"). Tick \"Also update Sanghs that already have data\" to update it.";
                }
            }
        }
        foreach ($rows as $row) {
            if (trim((string) ($row['mapped']['संघाचे नाव'] ?? '')) === '') {
                $problems[] = "Excel row {$row['excel_row']}: संघाचे नाव is empty.";
            }
            array_push($problems, ...$this->importPhoneErrors($this->sanghAttributesFromImportRow($row['mapped']), "Excel row {$row['excel_row']}"));
        }
        if ($problems) {
            return $fail('Nothing was imported. Please fix the following and upload again:', array_slice($problems, 0, 25)
                + (count($problems) > 25 ? [25 => '…and ' . (count($problems) - 25) . ' more.'] : []));
        }

        DB::transaction(function () use ($rows, $targets, $from) {
            foreach ($rows as $index => $row) {
                $sangh = $targets->get($from + $index);
                // Empty Excel cells keep the Sangh's current value.
                $attributes = array_filter(
                    $this->sanghAttributesFromImportRow($row['mapped']),
                    fn ($v) => $v !== null && $v !== ''
                );
                // Sangh No. and its reference numbers stay as they are; only the short
                // vibhag/district codes follow the imported vibhag/district.
                if (isset($attributes['pradeshik_vibhag'])) {
                    $attributes['pradeshik_vibhag_code'] = $this->vibhagCode($attributes['pradeshik_vibhag']);
                }
                if (isset($attributes['district'])) {
                    $attributes['district_code'] = $this->districtCode($attributes['district']);
                }
                // Keep the total in step when only one of male/female is given.
                if (isset($attributes['male']) || isset($attributes['female'])) {
                    $attributes['total_members'] = ($attributes['male'] ?? $sangh->male ?? 0) + ($attributes['female'] ?? $sangh->female ?? 0);
                }
                $sangh->update($attributes);
            }
        });

        $blankCount = $expected - $filledCount;

        return redirect()->route('sanghs.index')->with('success',
            "Imported Sangh No. {$from} to {$to}: {$blankCount} blank Sangh(s) filled, {$filledCount} existing Sangh(s) updated.");
    }

    public function downloadTemplate()
    {
        $year = (int) date('Y');
        // Sangh ID is left blank for new Sanghs; rows link to their Sangh by संघाचे नाव
        // (receipt sheets) when there is no ID or Unique number yet.
        $master = $this->rowFor(self::IMPORT_HEADERS, [
            'वर्ष' => $year,
            'संघाचे नाव' => 'दत्तात्रय ज्ये.ना.सं',
            'श्रेणी' => 'R',
            'संघ प्रकार' => 'G',
            'प्रादेशिक विभाग' => 'मुंबई',
            'जिल्हा' => 'मुंबई सिटी',
            'तालुका' => 'Taluka Name',
            'गाव' => 'Village Name',
            'शहर' => 'Mumbai',
            'मुक्काम पोस्ट' => 'Mukkam Post',
            'पिनकोड' => '400001',
            'पत्ता' => '402/A, Building Name',
            'रस्ता / पथ' => 'Main Road',
            'विभाग/प्रभाग' => 'Ward 3',
            'पुरुष सभासद संख्या' => 25,
            'महिला सभासद संख्या' => 22,
            'अध्यक्ष' => 'President Name',
            'अध्यक्ष मोबाईल' => '9000000001',
            'अध्यक्ष व्हॉट्सअप' => '9000000001',
            'अध्यक्ष इमेल' => 'president@example.com',
            'सचिव' => 'Secretary Name',
            'सचिव मोबाईल' => '9000000002',
            'सचिव व्हॉट्सअप' => '9000000002',
            'सचिव इमेल' => 'secretary@example.com',
        ]);

        $registration = $this->rowFor(self::REGISTRATION_HEADERS, [
            'संघाचे नाव' => 'दत्तात्रय ज्ये.ना.सं',
            'वर्ष' => $year,
            'स्थिती' => 'Information approved',
            'फेस्कॉम पावती दिनांक' => now()->format('Y-m-d'),
            'पावती रक्कम (भरलेली)' => 300,
            'बँकेचे नाव' => 'Bank Name',
        ]);

        $renewal = $this->rowFor(self::RENEWAL_HEADERS, [
            'संघाचे नाव' => 'दत्तात्रय ज्ये.ना.सं',
            'वर्ष' => $year - 1,
            'प्रकार' => 'Backdated',
            'स्थिती' => 'Unpaid',
            'पुरुष सभासद संख्या' => 25,
            'महिला सभासद संख्या' => 22,
            'दंड शुल्क' => 0,
            'पावती रक्कम (भरलेली)' => 0,
        ]);

        $export = new \App\Exports\SanghWorkbookExport([
            [self::SHEET_MASTER, self::IMPORT_HEADERS, [$master]],
            [self::SHEET_REGISTRATION, self::REGISTRATION_HEADERS, [$registration]],
            [self::SHEET_RENEWALS, self::RENEWAL_HEADERS, [$renewal]],
        ]);

        return Excel::download($export, 'sangh_template.xlsx');
    }


     public function downloadPdf(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isVisibleTo($request->user()), 403);

        // load relations if needed
        $sangh->load('creator');

        // Use a dedicated PDF view to avoid including action buttons etc.
        $pdf = app('dompdf.wrapper')->loadView('sanghs.pdf', compact('sangh'))
                   ->setPaper('a4', 'portrait');

        // Stream as download
        $filename = 'sangh_' . $sangh->sangh_sr_no . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate PDF and save to storage/app/public/sangh-pdfs/
     * Returns a redirect or JSON with the stored file path / URL.
     */
    public function savePdfToStorage(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isVisibleTo($request->user()), 403);

        $sangh->load('creator');

        $pdf = app('dompdf.wrapper')->loadView('sanghs.pdf', compact('sangh'))
                   ->setPaper('a4', 'portrait');

        $filename = 'sangh_' . $sangh->sangh_sr_no . '_' . now()->format('Ymd_His') . '.pdf';
        $folder = 'sangh-pdfs';
        $path = $folder . '/' . $filename;

        // Ensure public disk is configured (default exists)
        Storage::disk('public')->put($path, $pdf->output());

        $url = \App\Support\Media::url($path);

        // Option 1: redirect back with link
        return redirect()->back()->with('pdf_saved', $url);

        // Option 2 (API): return response()->json(['url' => $url]);
    }

    /**
     * Download a previously saved PDF from storage
     */
    public function downloadStoredPdf(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isVisibleTo($request->user()), 403);

        $folder = 'sangh-pdfs';
        // find latest file for this sangh (simple approach)
        $files = Storage::disk('public')->files($folder);

        $pattern = '/sangh_' . $sangh->sangh_sr_no . '_/';
        $matched = array_filter($files, function($f) use ($pattern) {
            return (bool) preg_match($pattern, $f);
        });

        if (empty($matched)) {
            return redirect()->back()->with('error', 'No stored PDF found for this Sangh.');
        }

        // take latest by filename (timestamps in name)
        usort($matched, function($a,$b){
            return strcmp($b, $a);
        });
        $path = $matched[0];

        $absolutePath = Storage::disk('public')->path($path);
        return response()->download($absolutePath);
    }

    public function downloadReceiptPdf(Request $request, Sangh $sangh, int $year)
    {
        abort_unless($sangh->isVisibleTo($request->user()), 403);

        $renewal = SanghRenewal::query()
            ->where('sangh_id', $sangh->id)
            ->where('renewal_year', $year)
            ->first();

        if (!$renewal || !$renewal->is_paid) {
            return redirect()->route('sanghs.receipts', $sangh)->with('error', 'Receipt not available or not paid.');
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('sanghs.receipt-pdf', compact('sangh', 'renewal'))
            ->setPaper('a4')
            ->setOption(['dpi' => 150, 'defaultFont' => 'Dejavu Sans']);

        return $pdf->download('Receipt_' . str_replace(' ', '_', $sangh->name_of_sangh) . '_' . $year . '.pdf');
    }

    public function createRenewal(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $request->validate([
            'renewal_year' => ['required', 'integer', 'min:' . self::FIRST_RECEIPT_YEAR, 'max:' . SanghYears::last()],
            'receipt_type' => ['nullable', 'in:renewal,backdated'],
        ]);
        $year = (int) $request->input('renewal_year');
        $isBackdated = $request->input('receipt_type') === 'backdated';

        // Registration happens once; renewal / backdated receipts only after it.
        if (! $this->isRegistered($sangh)) {
            return redirect()->route('sanghs.receipts', $sangh)->with('error',
                'Register the Sangh first: set the Registered Receipt status to "Sangh Registered". Renewal and backdated receipts can be created only after that.');
        }

        // Same rules the Receipts tabs show: Renewal = next year only; Backdated = 1979-80…last year, unused.
        [$nextRenewalYear, $backdatedYears] = $this->receiptYearOptions($sangh, $this->visibleRenewals($sangh));
        $allowed = $isBackdated ? in_array($year, $backdatedYears, true) : $year === $nextRenewalYear;
        if (! $allowed) {
            return redirect()->route('sanghs.receipts', $sangh)->with('error', $isBackdated
                ? "A receipt for {$year} already exists or the year is not allowed (" . self::FIRST_RECEIPT_YEAR . '–' . ((int) date('Y') - 1) . ').'
                : 'Renewal can only be created for the next year' . ($nextRenewalYear ? " ({$nextRenewalYear})." : ' — none is due right now.'));
        }
        $maleMembers = $this->intOrNull($sangh->male);
        $femaleMembers = $this->intOrNull($sangh->female);
        $totalMembers = ($maleMembers === null && $femaleMembers === null)
            ? null
            : (($maleMembers ?? 0) + ($femaleMembers ?? 0));

        // Remove any blank auto-created stub for this year, then create fresh
        SanghRenewal::query()
            ->where('sangh_id', $sangh->id)
            ->where('renewal_year', $year)
            ->whereNull('feskcom_receipt_no')
            ->whereNull('annual_fee')
            ->whereNull('paid_amount')
            ->where('is_paid', false)
            ->delete();

        SanghRenewal::query()->firstOrCreate(
            ['sangh_id' => $sangh->id, 'renewal_year' => $year],
            [
                'is_paid' => false,
                'is_backdated' => $isBackdated,
                'male_members' => $maleMembers,
                'female_members' => $femaleMembers,
                'total_members' => $totalMembers,
            ]
        );
        return redirect()->route('sanghs.receipts', $sangh)->with('success',
            ($isBackdated ? 'Backdated' : 'Renewal') . " receipt for {$year} created.");
    }

    public function destroyRenewal(Request $request, Sangh $sangh, int $year)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        SanghRenewal::query()
            ->where('sangh_id', $sangh->id)
            ->where('renewal_year', $year)
            ->delete();
        return redirect()->route('sanghs.receipts', $sangh)->with('success', "Receipt for {$year} deleted.");
    }

    public function updateRenewal(Request $request, Sangh $sangh, int $year)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', self::RENEWAL_STATUSES),
            'feskcom_receipt_date' => 'nullable|date',
            'male_members' => 'nullable|integer|min:0',
            'female_members' => 'nullable|integer|min:0',
            'penalty_fee' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'cheque_no' => 'nullable|string|max:50',
            'cheque_date' => 'nullable|date',
        ]);

        $this->saveRenewal($sangh, $year, $validated);

        return redirect()->route('sanghs.receipts', $sangh)->with('success', 'Receipt updated.');
    }

    /** Placeholder rows are pre-added up to this Sangh count (one-time setup). */
    public const PLACEHOLDER_TARGET = 6676;

    public function seedPlaceholders()
    {
        $target = self::PLACEHOLDER_TARGET;
        $currentCount = Sangh::count();

        if ($currentCount >= $target) {
            return redirect()->route('sanghs.index')->with('success', 'Placeholders already available.');
        }

        DB::transaction(function () use ($target, $currentCount) {
            for ($i = $currentCount + 1; $i <= $target; $i++) {
                $code = 'SG';
                Sangh::create([
                    'sangh_sr_no' => $i,
                    'unique_ref_no' => $code . '/' . $i,
                    'pradeshik_sr_no' => $i,
                    'pradeshik_ref_no' => $code . '/' . $i,
                    'district_sr_no' => $i,
                    'district_ref_no' => $code . '/' . $i,
                    'name_of_sangh' => null,
                    'created_by' => Auth::id(),
                    'created_date' => now(),
                ]);
            }
        });

        return redirect()->route('sanghs.index')->with('success', 'Placeholder Sangh rows added till ' . $target . '.');
    }

    private function rules(bool $isCreate = true): array
    {
        // Names may legitimately contain digits (society/building/chawl numbers, e.g.
        // "सहकारी गृहनिर्माण संस्था क्र. ३") — the client-side "Marathi only" filter already
        // allows 0-9, so the server-side rule matches that rather than rejecting them.
        $nameRule = $isCreate ? 'required|string|max:255' : 'nullable|string|max:255';

        return [
            'assigned_to' => 'nullable|exists:users,id',
            'name_of_sangh' => $nameRule,
            'registration_year' => SanghYears::rule(),
            'category_code' => 'required|string|in:R,U,A',
            'sangh_type_code' => 'required|string|in:G,F',
            'pradeshik_vibhag' => 'required|string|max:255',
            'pradeshik_vibhag_code' => 'nullable|string|max:10',
            'district' => 'required|string|max:255',
            'district_code' => 'nullable|string|max:10',
            'taluka' => 'nullable|string|max:255',
            'village' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'mukkam_post' => 'nullable|string|max:255',
            'pincode' => ['nullable', 'digits:6', Rule::in(config('pincodes.allowed', []))],
            'address' => 'nullable|string',
            'road_path' => 'nullable|string|max:255',
            'ward_section' => 'nullable|string|max:255',
            'male' => 'nullable|integer|min:0',
            'female' => 'nullable|integer|min:0',
            'total_members' => 'nullable|integer|min:0',
            'pradeshik_admission_fee' => 'nullable|numeric|min:0',
            'pradeshik_annual_fee' => 'nullable|numeric|min:0',
            'pradeshik_development_fee' => 'nullable|numeric|min:0',
            'president' => 'nullable|string|max:255',
            'president_phone' => Phone::MOBILE_RULE,
            'president_whatsapp' => Phone::MOBILE_RULE,
            'president_email' => 'nullable|email|max:255',
            'tel_no' => Phone::TELEPHONE_RULE,
            'alt_tel_no' => Phone::TELEPHONE_RULE,
            'secretary' => 'nullable|string|max:255',
            'secretary_phone' => Phone::MOBILE_RULE,
            'secretary_whatsapp' => Phone::MOBILE_RULE,
            'secretary_email' => 'nullable|email|max:255',
            'email' => 'nullable|email|max:255',
        ];
    }

    private function normalizePayload(array $validated, bool $allowAssignedTo = true): array
    {
        $male = $this->intOrNull($validated['male'] ?? null);
        $female = $this->intOrNull($validated['female'] ?? null);
        $totalMembers = ($male === null && $female === null) ? null : (($male ?? 0) + ($female ?? 0));

        $payload = [
            'registration_year' => $validated['registration_year'] ?? null,
            'name_of_sangh' => $validated['name_of_sangh'] ?? null,
            'category_code' => $validated['category_code'] ?? null,
            'sangh_type_code' => $validated['sangh_type_code'] ?? null,
            'pradeshik_vibhag' => $validated['pradeshik_vibhag'] ?? null,
            'pradeshik_vibhag_code' => $validated['pradeshik_vibhag_code'] ?? null,
            'district' => $validated['district'] ?? null,
            'district_code' => $validated['district_code'] ?? null,
            'taluka' => $validated['taluka'] ?? null,
            'village' => $validated['village'] ?? null,
            'city' => $validated['city'] ?? null,
            'mukkam_post' => $validated['mukkam_post'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'address' => $validated['address'] ?? null,
            'road_path' => $validated['road_path'] ?? null,
            'ward_section' => $validated['ward_section'] ?? null,
            'male' => $male,
            'female' => $female,
            'total_members' => $totalMembers,
            'pradeshik_admission_fee' => $validated['pradeshik_admission_fee'] ?? null,
            'pradeshik_annual_fee' => $validated['pradeshik_annual_fee'] ?? null,
            'pradeshik_development_fee' => $validated['pradeshik_development_fee'] ?? null,
            'president' => $validated['president'] ?? null,
            'president_phone' => $validated['president_phone'] ?? null,
            'president_whatsapp' => $validated['president_whatsapp'] ?? null,
            'president_email' => $validated['president_email'] ?? null,
            'tel_no' => $validated['tel_no'] ?? null,
            'alt_tel_no' => $validated['alt_tel_no'] ?? null,
            'secretary' => $validated['secretary'] ?? null,
            'secretary_phone' => $validated['secretary_phone'] ?? null,
            'secretary_whatsapp' => $validated['secretary_whatsapp'] ?? null,
            'secretary_email' => $validated['secretary_email'] ?? null,
            'email' => $validated['email'] ?? null,
        ];

        if ($allowAssignedTo) {
            $payload['assigned_to'] = $validated['assigned_to'] ?? null;
        }

        return $payload;
    }

    /**
     * Sangh numbering, assigned once when the Sangh is registered. The FY and quarter
     * in the Unique number come from the संघ नोंदणी दिनांक ($registeredDate) — never
     * from the created date. Pradeshik / District numbers use the form's codes
     * (मुंबई → MM/…, मुंबई सिटी → MC/…).
     */
    private function makeNumbering(Sangh $sangh, $registeredDate = null): array
    {
        $vibhag = $sangh->pradeshik_vibhag;
        $district = $sangh->district;
        $categoryCode = $sangh->category_code;
        $sanghTypeCode = $sangh->sangh_type_code;
        $vibhagCode = $this->vibhagCode($vibhag, $sangh->pradeshik_vibhag_code);
        $districtCode = $this->districtCode($district, $sangh->district_code);

        // Fetch max existing sequence numbers
        $existingGlobalNo = Sangh::query()->max('sangh_sr_no');
        $nextGlobal = $existingGlobalNo ? ((int) $existingGlobalNo) + 1 : 1;

        $existingVibhagNo = Sangh::query()->where('pradeshik_vibhag', $vibhag)->max('pradeshik_sr_no');
        $nextVibhag = $existingVibhagNo ? ((int) $existingVibhagNo) + 1 : 1;

        $existingDistrictNo = Sangh::query()->where('district', $district)->max('district_sr_no');
        $nextDistrict = $existingDistrictNo ? ((int) $existingDistrictNo) + 1 : 1;

        $category = in_array($categoryCode, ['R', 'U', 'A'], true) ? $categoryCode : 'R';
        $sanghType = in_array($sanghTypeCode, ['G', 'F'], true) ? $sanghTypeCode : 'G';

        $date = $registeredDate ? \Carbon\Carbon::parse($registeredDate) : now();
        $month = $date->month;
        $year = $date->year;

        if ($month >= 4 && $month <= 6) {
            $quarter = 'J';
            $fy = substr($year, -2) . '-' . substr($year + 1, -2);
        } elseif ($month >= 7 && $month <= 9) {
            $quarter = 'S';
            $fy = substr($year, -2) . '-' . substr($year + 1, -2);
        } elseif ($month >= 10 && $month <= 12) {
            $quarter = 'D';
            $fy = substr($year, -2) . '-' . substr($year + 1, -2);
        } else {
            $quarter = 'M';
            $fy = substr($year - 1, -2) . '-' . substr($year, -2);
        }

        return [
            'sangh_sr_no' => $nextGlobal,
            'unique_ref_no' => $category . '/' . $sanghType . '/' . $fy . '/' . $quarter . '/' . $nextGlobal,
            'pradeshik_sr_no' => $nextVibhag,
            'pradeshik_ref_no' => $vibhagCode . '/' . $nextVibhag,
            'district_sr_no' => $nextDistrict,
            'district_ref_no' => $districtCode . '/' . $nextDistrict,
            'pradeshik_vibhag_code' => $vibhagCode,
            'district_code' => $districtCode,
        ];
    }

    /** प्रादेशिक विभाग code: the form's fixed code (मुंबई → MM), else the given code. */
    private function vibhagCode(?string $vibhag, ?string $code = null): string
    {
        return SanghCodes::vibhag($vibhag) ?? $this->normalizeCode($code ?: $vibhag);
    }

    /** जिल्हा code: the form's fixed code (मुंबई सिटी → MC), else the given code. */
    private function districtCode(?string $district, ?string $code = null): string
    {
        return SanghCodes::district($district) ?? $this->normalizeCode($code ?: $district);
    }

    /** Fallback for values outside the fixed lists: up to 3 English letters, else "SG". */
    private function normalizeCode(?string $value): string
    {
        $clean = preg_replace('/[^A-Z]/', '', strtoupper(Str::ascii((string) $value)));
        return $clean !== '' ? Str::substr($clean, 0, 3) : 'SG';
    }

    private function ensureRenewalsForSangh(Sangh $sangh): void
    {
        // Renewals are now created on-demand by the user from the details page.
    }

    private function mapRowByHeader(array $header, array $row): array
    {
        $mapped = [];
        foreach ($header as $index => $head) {
            // Excel cells may hold Marathi digits (२५); store English digits (25).
            $mapped[trim((string) $head)] = Digits::toEnglish($row[$index] ?? null);
        }
        return $mapped;
    }

    private function intOrNull($value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function decimalOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function codeChar($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return strtoupper(Str::substr(trim((string) $value), 0, 1));
    }

}
