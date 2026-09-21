<?php

namespace App\Http\Controllers;

use App\Models\Sangh;
use App\Models\SanghFeeSlab;
use App\Models\SanghRegistrationReceipt;
use App\Models\SanghRenewal;
use App\Models\Setting;
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
    private const IMPORT_HEADERS = [
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
    ];

    private const RENEWAL_HEADERS = [
        'Unique संघाचा अनु क्र.',
        'वर्ष',
        'फेस्कॉम पावती क्र.',
        'फेस्कॉम पावती दिनांक',
        'पुरुष सभासद संख्या',
        'महिला सभासद संख्या',
        'एकूण सभासद संख्या',
        'वार्षिक शुल्क',
        'विकास निधी शुल्क',
        'दंड शुल्क',
        'पावती रक्कम (भरलेली)',
        'स्थिती',
    ];

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

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        // Ownership filter
        if ($request->filled('ownership')) {
            $ownership = $request->input('ownership');
            if ($ownership === 'created_by_me') {
                $query->where('created_by', auth()->id());
            } elseif ($ownership === 'assigned_to_me') {
                $query->where('assigned_to', auth()->id());
            }
        }

        // Register Receipts filter (Year with payment status)
        if ($request->filled('register_receipt_year')) {
            $query->whereHas('registrationReceipt', function ($receiptQuery) use ($request) {
                $receiptQuery->where('receipt_year', (int) $request->input('register_receipt_year'));
                if ($request->filled('payment_status')) {
                    $receiptQuery->where('status', $request->input('payment_status'));
                }
            });
        }

        // Renewal Receipts filter (Year with payment status)
        if ($request->filled('renewal_receipt_year')) {
            $query->whereHas('renewals', function ($renewalQuery) use ($request) {
                $renewalQuery->where('renewal_year', (int) $request->input('renewal_receipt_year'));
                if ($request->filled('payment_status')) {
                    $renewalQuery->where('status', $request->input('payment_status'));
                }
            });
        }

        // If ONLY payment_status is selected (no years), filter across both
        if ($request->filled('payment_status') && !$request->filled('register_receipt_year') && !$request->filled('renewal_receipt_year')) {
            $status = $request->input('payment_status');
            $query->where(function ($q) use ($status) {
                $q->whereHas('registrationReceipt', function ($receiptQuery) use ($status) {
                    $receiptQuery->where('status', $status);
                })->orWhereHas('renewals', function ($renewalQuery) use ($status) {
                    $renewalQuery->where('status', $status);
                });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = Sangh::query();
        $this->applyFilters($query, $request);

        // Calculate total members dynamically based on the filtered query BEFORE pagination
        $totalMembers = (clone $query)->sum('total_members');

        // Status counts for the index filter (unpaid, paid, information_approved, sangh_registered)
        $paidCount = (clone $query)->where(function ($q) use ($request) {
            if ($request->filled('register_receipt_year')) {
                $q->whereHas('registrationReceipt', fn($r) => $r->where('receipt_year', (int)$request->input('register_receipt_year'))->where('status', 'paid'));
            } elseif ($request->filled('renewal_receipt_year')) {
                $q->whereHas('renewals', fn($r) => $r->where('renewal_year', (int)$request->input('renewal_receipt_year'))->where('status', 'paid'));
            } else {
                $q->whereHas('registrationReceipt', fn($r) => $r->where('status', 'paid'))
                  ->orWhereHas('renewals', fn($r) => $r->where('status', 'paid'));
            }
        })->count();

        // Calculate Unpaid as Total minus Paid to ensure the math perfectly aligns for the user
        $totalCount = (clone $query)->count();
        $unpaidCount = $totalCount - $paidCount;

        $infoApprovedCount = (clone $query)->where(function ($q) use ($request) {
            if ($request->filled('register_receipt_year')) {
                $q->whereHas('registrationReceipt', fn($r) => $r->where('receipt_year', (int)$request->input('register_receipt_year'))->where('status', 'information_approved'));
            } elseif ($request->filled('renewal_receipt_year')) {
                $q->whereHas('renewals', fn($r) => $r->where('renewal_year', (int)$request->input('renewal_receipt_year'))->where('status', 'information_approved'));
            } else {
                $q->whereHas('registrationReceipt', fn($r) => $r->where('status', 'information_approved'))
                  ->orWhereHas('renewals', fn($r) => $r->where('status', 'information_approved'));
            }
        })->count();

        $registeredCount = (clone $query)->where(function ($q) use ($request) {
            if ($request->filled('register_receipt_year')) {
                $q->whereHas('registrationReceipt', fn($r) => $r->where('receipt_year', (int)$request->input('register_receipt_year'))->where('status', 'sangh_registered'));
            } elseif ($request->filled('renewal_receipt_year')) {
                $q->whereHas('renewals', fn($r) => $r->where('renewal_year', (int)$request->input('renewal_receipt_year'))->where('status', 'sangh_registered'));
            } else {
                $q->whereHas('registrationReceipt', fn($r) => $r->where('status', 'sangh_registered'))
                  ->orWhereHas('renewals', fn($r) => $r->where('status', 'sangh_registered'));
            }
        })->count();

        $sanghs = $query->orderBy('sangh_sr_no', 'asc')->paginate(15)->withQueryString();

        // Get filter options from distinct data
        $vibhags = Sangh::query()->whereNotNull('pradeshik_vibhag')->distinct()->orderBy('pradeshik_vibhag')->pluck('pradeshik_vibhag');
        $districts = Sangh::query()->whereNotNull('district')->distinct()->orderBy('district')->pluck('district');
        $years = range((int) date('Y'), 1970);
        
        $vibhagDistricts = Sangh::query()
            ->whereNotNull('pradeshik_vibhag')
            ->whereNotNull('district')
            ->select('pradeshik_vibhag', 'district')
            ->distinct()
            ->get()
            ->groupBy('pradeshik_vibhag')
            ->map->pluck('district');
        
        // Get unique receipt years (excluding sangh data that has no receipt)
        $registerReceiptYears = SanghRegistrationReceipt::query()->distinct()->orderBy('receipt_year', 'desc')->pluck('receipt_year');
        $renewalReceiptYears = SanghRenewal::query()->distinct()->orderBy('renewal_year', 'desc')->pluck('renewal_year');

        return view('sanghs.index', compact('sanghs', 'vibhags', 'districts', 'vibhagDistricts', 'years', 'registerReceiptYears', 'renewalReceiptYears', 'totalMembers', 'unpaidCount', 'paidCount', 'infoApprovedCount', 'registeredCount'));
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

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $this->assertMinimumMembers($validated);
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
                    'pradeshik_vibhag_code' => $this->normalizeCode($validated['pradeshik_vibhag_code'] ?? $validated['pradeshik_vibhag']),
                    'district_code' => $this->normalizeCode($validated['district_code'] ?? $validated['district']),
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

        $sangh->load(['creator', 'renewals', 'registrationReceipt']);

        $registrationYear = $this->intOrNull($sangh->registration_year);

        // Fees for the New register Sangh Receipt always follow the admin-configured
        // sangh fee settings (standard fees + member-count slab) — never manually entered.
        $maleForFee = $this->intOrNull($sangh->male);
        $femaleForFee = $this->intOrNull($sangh->female);
        $totalForFee = ($maleForFee === null && $femaleForFee === null)
            ? null
            : (($maleForFee ?? 0) + ($femaleForFee ?? 0));

        $admissionFeeStd = (float) Setting::getValue('sangh_admission_fee', 0);
        $annualFeeComputed = SanghFeeSlab::annualFeeForMemberCount($totalForFee) ?? 0;
        $developmentFeeRate = (float) Setting::getValue('sangh_development_fee_rate', 0);
        $developmentFeeComputed = $developmentFeeRate * ($totalForFee ?? 0);

        // Dedicated registration receipt is stored in separate table.
        $newRegisterReceipt = $sangh->registrationReceipt;
        if (!$newRegisterReceipt && $registrationYear !== null) {
            $newRegisterReceipt = new SanghRegistrationReceipt([
                'sangh_id' => $sangh->id,
                'receipt_year' => $registrationYear,
                'is_paid' => false,
            ]);
        }

        if ($newRegisterReceipt) {
            // Always reflect the current fee-slab configuration, not whatever was last saved.
            $newRegisterReceipt->admission_fee = $admissionFeeStd;
            $newRegisterReceipt->annual_fee = $annualFeeComputed;
            $newRegisterReceipt->development_fee = $developmentFeeComputed;
        }

        // Only show renewals that have been actively used (any meaningful data entered)
        $allRenewals = $sangh->renewals;
        $renewals = $allRenewals->filter(function ($r) {
            return $r->is_paid
                || $r->feskcom_receipt_no !== null
                || $r->feskcom_receipt_date !== null
                || $r->annual_fee !== null
                || $r->development_fee !== null
                || $r->penalty_fee !== null
                || $r->paid_amount !== null
                || $r->male_members !== null
                || $r->female_members !== null
                || $r->total_members !== null;
        })->sortByDesc('renewal_year');

        // Available years = all years 1970→current that don't already have a visible record
        $usedYears = $renewals->pluck('renewal_year')->all();
        $currentYear = (int) date('Y');
        $availableYears = array_values(array_filter(
            range($currentYear, 1970),
            fn($y) => !in_array($y, $usedYears, true)
        ));

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

        return view('sanghs.show', compact('sangh', 'renewals', 'availableYears', 'newRegisterReceipt', 'registrationYear', 'prevSangh', 'nextSangh'));
    }

    public function updateRegistrationReceipt(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $validated = $request->validate([
            'status' => 'required|in:unpaid,paid,information_approved,sangh_registered',
            'feskcom_receipt_date' => 'nullable|date',
            'penalty_fee' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'cheque_no' => 'nullable|string|max:50',
            'cheque_date' => 'nullable|date',
        ]);

        $maleMembers = $this->intOrNull($sangh->male);
        $femaleMembers = $this->intOrNull($sangh->female);
        $totalMembers = ($maleMembers === null && $femaleMembers === null)
            ? null
            : (($maleMembers ?? 0) + ($femaleMembers ?? 0));

        // प्रवेश शुल्क / वार्षिक शुल्क / विकास निधी शुल्क are never entered manually —
        // always derived from the admin-configured sangh fee settings/slabs.
        $admissionFee = (float) Setting::getValue('sangh_admission_fee', 0);
        $annualFee = SanghFeeSlab::annualFeeForMemberCount($totalMembers) ?? 0;
        $developmentFeeRate = (float) Setting::getValue('sangh_development_fee_rate', 0);
        $developmentFee = $developmentFeeRate * ($totalMembers ?? 0);

        $year = $this->intOrNull($sangh->registration_year) ?? (int) date('Y');

        $receipt = SanghRegistrationReceipt::query()->firstOrCreate(
            ['sangh_id' => $sangh->id],
            ['receipt_year' => $year, 'is_paid' => false, 'status' => 'unpaid']
        );

        // Entering the receipt date marks it paid and auto-assigns the receipt number (once, never overwritten)
        $hasReceiptDate = !empty($validated['feskcom_receipt_date']);
        $status = $validated['status'];
        if ($hasReceiptDate && $status === 'unpaid') {
            $status = 'paid';
        }
        $isPaid = in_array($status, ['paid', 'information_approved', 'sangh_registered']);

        if (empty($receipt->feskcom_receipt_no) && $hasReceiptDate) {
            $receipt->feskcom_receipt_no = 'FSNEW/' . $receipt->id;
            $receipt->save();
        }

        $receipt->update([
            'receipt_year' => $year,
            'is_paid' => $isPaid,
            'status' => $status,
            'feskcom_receipt_no' => $receipt->feskcom_receipt_no,
            'feskcom_receipt_date' => $validated['feskcom_receipt_date'] ?? null,
            'user_id' => auth()->id(),
            'male_members' => $maleMembers,
            'female_members' => $femaleMembers,
            'total_members' => $totalMembers,
            'annual_fee' => $annualFee,
            'admission_fee' => $admissionFee,
            'development_fee' => $developmentFee,
            'penalty_fee' => $validated['penalty_fee'] ?? null,
            'paid_amount' => $validated['paid_amount'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'cheque_date' => $validated['cheque_date'] ?? null,
        ]);

        if ($status === 'sangh_registered' && $sangh->sangh_sr_no === null) {
            $numbering = $this->makeNumbering(
                $sangh->pradeshik_vibhag,
                $sangh->district,
                $sangh->category_code,
                $sangh->sangh_type_code,
                $sangh->created_date
            );
            $sangh->update($numbering);
        }

        return redirect()->route('sanghs.show', $sangh)->with('success', 'New register Sangh receipt updated.');
    }


    public function update(Request $request, Sangh $sangh)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $validated = $request->validate($this->rules(false));
        $this->assertMinimumMembers($validated);

        $selectedVibhag = $validated['pradeshik_vibhag'] ?? null;
        $selectedDistrict = $validated['district'] ?? null;

        // Numbering (sangh_sr_no, unique_ref_no, pradeshik/district ref no) is assigned once,
        // when the receipt/renewal status first becomes "sangh_registered" — never regenerated
        // on a plain edit. Only the short display codes are kept in sync with the selected
        // vibhag/district here.
        $numbering = [
            'pradeshik_vibhag_code' => $this->normalizeCode($validated['pradeshik_vibhag_code'] ?? $selectedVibhag),
            'district_code' => $this->normalizeCode($validated['district_code'] ?? $selectedDistrict),
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
        $query = Sangh::query()->with('renewals');
        $this->applyFilters($query, $request);
        $sanghs = $query->orderBy('sangh_sr_no')->get();

        $masterRows = $sanghs->map(function (Sangh $s) {
            return [
                $s->unique_ref_no,
                $s->pradeshik_ref_no,
                $s->district_ref_no,
                $s->registration_year,
                $s->name_of_sangh,
                $s->category_code,
                $s->sangh_type_code,
                $s->pradeshik_vibhag,
                $s->district,
                $s->taluka,
                $s->village,
                $s->city,
                $s->mukkam_post,
                $s->pincode,
                $s->address,
                $s->road_path,
                $s->ward_section,
                $s->male,
                $s->female,
                $s->total_members,
                $s->pradeshik_admission_fee,
                $s->pradeshik_annual_fee,
                $s->pradeshik_development_fee,
                $s->president,
                $s->president_phone,
                $s->president_whatsapp,
                $s->president_email,
                $s->secretary,
                $s->secretary_phone,
                $s->secretary_whatsapp,
                $s->secretary_email,
            ];
        })->values()->all();

        $renewalRows = [];
        foreach ($sanghs as $s) {
            foreach ($s->renewals as $renewal) {
                $renewalRows[] = [
                    $s->unique_ref_no,
                    $renewal->renewal_year,
                    $renewal->feskcom_receipt_no,
                    optional($renewal->feskcom_receipt_date)->format('Y-m-d'),
                    $renewal->male_members,
                    $renewal->female_members,
                    $renewal->total_members,
                    $renewal->annual_fee,
                    $renewal->development_fee,
                    $renewal->penalty_fee,
                    $renewal->paid_amount,
                    $renewal->is_paid ? 'Paid' : 'Unpaid',
                ];
            }
        }

        $export = new \App\Exports\SanghWorkbookExport(
            self::IMPORT_HEADERS,
            $masterRows,
            self::RENEWAL_HEADERS,
            $renewalRows
        );

        return Excel::download($export, 'sangh_export_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('excel_file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheets = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheets[] = $sheet->toArray(null, true, true, false);
        }

        $masterRows = $sheets[0] ?? [];
        $renewalRows = $sheets[1] ?? [];

        if (count($masterRows) < 2) {
            return back()->with('error', 'Excel file has no data rows.');
        }

        $masterHeader = array_map('trim', $masterRows[0]);
        $importErrors = [];

        DB::transaction(function () use ($masterRows, $masterHeader, $renewalRows, &$importErrors) {
            for ($i = 1; $i < count($masterRows); $i++) {
                $row = $masterRows[$i];
                if (!count(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                $mapped = $this->mapRowByHeader($masterHeader, $row);
                $name = trim((string) ($mapped['संघाचे नाव'] ?? ''));

                if ($name === '') {
                    $importErrors[] = 'Row ' . ($i + 1) . ': संघाचे नाव required. Skipped.';
                    continue;
                }

                $pradeshikVibhag = $mapped['प्रादेशिक विभाग'] ?? null;
                $district = $mapped['जिल्हा'] ?? null;

                $numbering = $this->makeNumbering(
                    $pradeshikVibhag,
                    $district,
                    $this->codeChar($mapped['श्रेणी'] ?? null),
                    $this->codeChar($mapped['संघ प्रकार'] ?? null)
                );

                $sangh = Sangh::create(array_merge([
                    'name_of_sangh' => $name,
                    'registration_year' => $this->intOrNull($mapped['वर्ष'] ?? null),
                    'category_code' => $this->codeChar($mapped['श्रेणी'] ?? null),
                    'sangh_type_code' => $this->codeChar($mapped['संघ प्रकार'] ?? null),
                    'pradeshik_vibhag' => $pradeshikVibhag,
                    'district' => $district,
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
                    'total_members' => $this->intOrNull($mapped['एकूण सभासद संख्या'] ?? null),
                    'pradeshik_admission_fee' => $this->decimalOrNull($mapped['प्रादेशिक प्रवेश शुल्क'] ?? null),
                    'pradeshik_annual_fee' => $this->decimalOrNull($mapped['प्रादेशिक वार्षिक शुल्क'] ?? null),
                    'pradeshik_development_fee' => $this->decimalOrNull($mapped['प्रादेशिक विकास निधी शुल्क'] ?? null),
                    'president' => $mapped['अध्यक्ष'] ?? null,
                    'president_phone' => $mapped['अध्यक्ष मोबाईल'] ?? null,
                    'president_whatsapp' => $mapped['अध्यक्ष व्हॉट्सअप'] ?? null,
                    'president_email' => $mapped['अध्यक्ष इमेल'] ?? null,
                    'secretary' => $mapped['सचिव'] ?? null,
                    'secretary_phone' => $mapped['सचिव मोबाईल'] ?? null,
                    'secretary_whatsapp' => $mapped['सचिव व्हॉट्सअप'] ?? null,
                    'secretary_email' => $mapped['सचिव इमेल'] ?? null,
                    'created_by' => Auth::id(),
                    'created_date' => now(),
                ], $numbering));

                $this->ensureRenewalsForSangh($sangh);
            }

            $this->importRenewals($renewalRows);
        });

        if (!empty($importErrors)) {
            return redirect()->route('sanghs.index')
                ->with('import_errors', $importErrors)
                ->with('success', 'Import completed with skipped rows.');
        }

        return redirect()->route('sanghs.index')->with('success', 'Excel imported successfully.');
    }

    public function downloadTemplate()
    {
        $sampleMaster = [[
            '',
            'MM/1',
            'MO/1',
            date('Y'),
            'दत्तात्रय ज्ये.ना.सं',
            'R',
            'G',
            'Mumbai Metropolitan',
            'Mumbai',
            'Taluka Name',
            'Village Name',
            'Mumbai',
            'Mukkam Post',
            '400001',
            '402/A, Building Name',
            'Main Road',
            'Ward 3',
            25,
            22,
            47,
            5000,
            6000,
            2000,
            'President Name',
            '9000000001',
            '9000000001',
            'president@example.com',
            'Secretary Name',
            '9000000002',
            '9000000002',
            'secretary@example.com',
        ]];

        $sampleRenewal = [[
            'MM/1',
            date('Y'),
            'FES-001',
            now()->format('Y-m-d'),
            25,
            22,
            47,
            500,
            250,
            0,
            750,
            'Paid',
        ]];

        $export = new \App\Exports\SanghWorkbookExport(
            self::IMPORT_HEADERS,
            $sampleMaster,
            self::RENEWAL_HEADERS,
            $sampleRenewal
        );

        return Excel::download($export, 'sangh_template.xlsx');
    }


     public function downloadPdf(Sangh $sangh)
    {
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
    public function savePdfToStorage(Sangh $sangh)
    {
        $sangh->load('creator');

        $pdf = app('dompdf.wrapper')->loadView('sanghs.pdf', compact('sangh'))
                   ->setPaper('a4', 'portrait');

        $filename = 'sangh_' . $sangh->sangh_sr_no . '_' . now()->format('Ymd_His') . '.pdf';
        $folder = 'sangh-pdfs';
        $path = $folder . '/' . $filename;

        // Ensure public disk is configured (default exists)
        Storage::disk('public')->put($path, $pdf->output());

        // Public URL (needs `php artisan storage:link` once)
        $url = asset('storage/' . $path);

        // Option 1: redirect back with link
        return redirect()->back()->with('pdf_saved', $url);

        // Option 2 (API): return response()->json(['url' => $url]);
    }

    /**
     * Download a previously saved PDF from storage
     */
    public function downloadStoredPdf(Sangh $sangh)
    {
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

    public function downloadReceiptPdf(Sangh $sangh, int $year)
    {
        $renewal = SanghRenewal::query()
            ->where('sangh_id', $sangh->id)
            ->where('renewal_year', $year)
            ->first();

        if (!$renewal || !$renewal->is_paid) {
            return redirect()->route('sanghs.show', $sangh)->with('error', 'Receipt not available or not paid.');
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
            'renewal_year' => ['required', 'integer', 'min:1970', 'max:' . (int) date('Y')],
        ]);
        $year = (int) $request->input('renewal_year');
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
                'male_members' => $maleMembers,
                'female_members' => $femaleMembers,
                'total_members' => $totalMembers,
            ]
        );
        return redirect()->route('sanghs.show', $sangh)->with('success', "Year {$year} renewal record created.");
    }

    public function destroyRenewal(Request $request, Sangh $sangh, int $year)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        SanghRenewal::query()
            ->where('sangh_id', $sangh->id)
            ->where('renewal_year', $year)
            ->delete();
        return redirect()->route('sanghs.show', $sangh)->with('success', "Year {$year} renewal record deleted.");
    }

    public function updateRenewal(Request $request, Sangh $sangh, int $year)
    {
        abort_unless($sangh->isManageableBy($request->user()), 403);

        $validated = $request->validate([
            'status' => 'required|in:unpaid,paid,information_approved,sangh_registered',
            'feskcom_receipt_date' => 'nullable|date',
            'male_members' => 'nullable|integer|min:0',
            'female_members' => 'nullable|integer|min:0',
            'penalty_fee' => 'nullable|integer|min:0',
            'paid_amount' => 'nullable|integer|min:0',
            'bank_name' => 'nullable|string|max:255',
            'cheque_no' => 'nullable|string|max:50',
            'cheque_date' => 'nullable|date',
        ]);

        $maleMembers = $validated['male_members'] ?? null;
        $femaleMembers = $validated['female_members'] ?? null;
        $totalMembers = ($maleMembers === null && $femaleMembers === null)
            ? null
            : (($maleMembers ?? 0) + ($femaleMembers ?? 0));

        // वार्षिक शुल्क / विकास निधी शुल्क are never entered manually —
        // always derived from the admin-configured sangh fee settings/slabs.
        $annualFee = SanghFeeSlab::annualFeeForMemberCount($totalMembers) ?? 0;
        $developmentFeeRate = (float) Setting::getValue('sangh_development_fee_rate', 0);
        $developmentFee = $developmentFeeRate * ($totalMembers ?? 0);

        $renewal = SanghRenewal::query()->firstOrCreate(
            ['sangh_id' => $sangh->id, 'renewal_year' => $year],
            ['is_paid' => false, 'status' => 'unpaid']
        );

        // Auto-assign receipt number once, never overwrite
        if (empty($renewal->feskcom_receipt_no)) {
            $renewal->feskcom_receipt_no = 'FSREN/' . $renewal->id;
            $renewal->save();
        }

        $hasReceiptDate = !empty($validated['feskcom_receipt_date']);
        $status = $validated['status'];
        if ($hasReceiptDate && $status === 'unpaid') {
            $status = 'paid';
        }
        $isPaid = in_array($status, ['paid', 'information_approved', 'sangh_registered']);

        $renewal->update([
            'is_paid' => $isPaid,
            'status' => $status,
            'feskcom_receipt_no' => $renewal->feskcom_receipt_no,
            'feskcom_receipt_date' => $validated['feskcom_receipt_date'] ?? null,
            'user_id' => auth()->id(),
            'male_members' => $maleMembers,
            'female_members' => $femaleMembers,
            'total_members' => $totalMembers,
            'annual_fee' => $annualFee,
            'development_fee' => $developmentFee,
            'penalty_fee' => $validated['penalty_fee'] ?? null,
            'paid_amount' => $validated['paid_amount'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'cheque_date' => $validated['cheque_date'] ?? null,
        ]);

        if ($status === 'sangh_registered' && $sangh->sangh_sr_no === null) {
            $numbering = $this->makeNumbering(
                $sangh->pradeshik_vibhag,
                $sangh->district,
                $sangh->category_code,
                $sangh->sangh_type_code,
                $sangh->created_date
            );
            $sangh->update($numbering);
        }

        return redirect()->route('sanghs.show', $sangh)->with('success', 'Renewal updated.');
    }

    public function seedPlaceholders()
    {
        $target = 6676;
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

        return redirect()->route('sanghs.index')->with('success', 'Placeholder Sangh rows added till 6676.');
    }

    private function rules(bool $isCreate = true): array
    {
        $nameRule = $isCreate ? 'required|string|max:255|not_regex:/[0-9]/' : 'nullable|string|max:255|not_regex:/[0-9]/';

        return [
            'assigned_to' => 'nullable|exists:users,id',
            'name_of_sangh' => $nameRule,
            'registration_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'category_code' => 'required|string|in:R,U,A',
            'sangh_type_code' => 'required|string|in:G,F',
            'pradeshik_vibhag' => 'required|string|max:255|not_regex:/[0-9]/',
            'pradeshik_vibhag_code' => 'nullable|string|max:10',
            'district' => 'required|string|max:255|not_regex:/[0-9]/',
            'district_code' => 'nullable|string|max:10',
            'taluka' => 'nullable|string|max:255|not_regex:/[0-9]/',
            'village' => 'nullable|string|max:255|not_regex:/[0-9]/',
            'city' => 'nullable|string|max:255|not_regex:/[0-9]/',
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
            'president' => 'nullable|string|max:255|not_regex:/[0-9]/',
            'president_phone' => 'nullable|digits:10',
            'president_whatsapp' => 'nullable|digits:10',
            'president_email' => 'nullable|email|max:255',
            'tel_no' => 'nullable|string|max:30',
            'alt_tel_no' => 'nullable|string|max:30',
            'secretary' => 'nullable|string|max:255|not_regex:/[0-9]/',
            'secretary_phone' => 'nullable|digits:10',
            'secretary_whatsapp' => 'nullable|digits:10',
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

    private function makeNumbering($vibhag, $district, $categoryCode, $sanghTypeCode, $createdDate = null)
    {
        $vibhagCode = $this->normalizeCode($vibhag);
        $districtCode = $this->normalizeCode($district);

        // Fetch max existing sequence numbers
        $existingGlobalNo = Sangh::query()->max('sangh_sr_no');
        $nextGlobal = $existingGlobalNo ? ((int) $existingGlobalNo) + 1 : 1;

        $existingVibhagNo = Sangh::query()->where('pradeshik_vibhag', $vibhag)->max('pradeshik_sr_no');
        $nextVibhag = $existingVibhagNo ? ((int) $existingVibhagNo) + 1 : 1;

        $existingDistrictNo = Sangh::query()->where('district', $district)->max('district_sr_no');
        $nextDistrict = $existingDistrictNo ? ((int) $existingDistrictNo) + 1 : 1;

        $category = in_array($categoryCode, ['R', 'U', 'A'], true) ? $categoryCode : 'R';
        $sanghType = in_array($sanghTypeCode, ['G', 'F'], true) ? $sanghTypeCode : 'G';

        $date = $createdDate ? \Carbon\Carbon::parse($createdDate) : now();
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

    private function normalizeCode(?string $value): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z]/', '', Str::ascii((string) $value)));
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
            $mapped[trim((string) $head)] = $row[$index] ?? null;
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

    private function importRenewals(array $renewalRows): void
    {
        if (count($renewalRows) < 2) {
            return;
        }

        $header = array_map('trim', $renewalRows[0]);
        for ($i = 1; $i < count($renewalRows); $i++) {
            $row = $renewalRows[$i];
            if (!count(array_filter($row, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $mapped = $this->mapRowByHeader($header, $row);
            $uniqueRef = trim((string) ($mapped['Unique संघाचा अनु क्र.'] ?? ''));
            $year = $this->intOrNull($mapped['वर्ष'] ?? null);

            if ($uniqueRef === '' || $year === null) {
                continue;
            }

            $sangh = Sangh::query()->where('unique_ref_no', $uniqueRef)->first();
            if (!$sangh) {
                continue;
            }

            SanghRenewal::query()->updateOrCreate(
                ['sangh_id' => $sangh->id, 'renewal_year' => $year],
                [
                    'is_paid' => strtolower((string) ($mapped['स्थिती'] ?? '')) === 'paid',
                    'feskcom_receipt_no' => $mapped['फेस्कॉम पावती क्र.'] ?? null,
                    'feskcom_receipt_date' => $mapped['फेस्कॉम पावती दिनांक'] ?? null,
                    'male_members' => $this->intOrNull($mapped['पुरुष सभासद संख्या'] ?? null),
                    'female_members' => $this->intOrNull($mapped['महिला सभासद संख्या'] ?? null),
                    'total_members' => $this->intOrNull($mapped['एकूण सभासद संख्या'] ?? null),
                    'annual_fee' => is_numeric($mapped['वार्षिक शुल्क'] ?? null) ? $mapped['वार्षिक शुल्क'] : null,
                    'development_fee' => is_numeric($mapped['विकास निधी शुल्क'] ?? null) ? $mapped['विकास निधी शुल्क'] : null,
                    'penalty_fee' => is_numeric($mapped['दंड शुल्क'] ?? null) ? $mapped['दंड शुल्क'] : null,
                    'paid_amount' => is_numeric($mapped['पावती रक्कम (भरलेली)'] ?? null) ? $mapped['पावती रक्कम (भरलेली)'] : null,
                ]
            );
        }
    }
}
