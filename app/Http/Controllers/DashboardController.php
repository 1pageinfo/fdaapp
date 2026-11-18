<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Group;
use App\Models\Link;
use App\Models\Meeting;
use App\Models\Receipt;
use App\Models\Sangh;
use App\Models\SanghRegistrationReceipt;
use App\Models\SanghRenewal;
use App\Models\User;
use App\Support\ActivityLogPresenter;
use App\Support\SanghCodes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Dashboard: a detailed overview of the app.
 *
 * Scope — Superadmin sees the whole organisation; Members and Admins see only
 * what is theirs (created by / assigned to them, or shared via their groups), using
 * the same rules as every module's own list (the models' scopeVisibleTo()).
 *
 * Every Sangh figure follows the chosen period, by date:
 *  - Sanghs (count, members, vibhag/district/category/type split): संघ नोंदणी दिनांक
 *    (sanghs.registered_at — the day the Sangh became "Sangh Registered"). The
 *    created date is for activity tracking only and is never used here.
 *  - Receipts (collections, status, balance due, monthly chart): the receipt date
 *    (फेस्कॉम पावती दिनांक), else the day it was registered.
 * "All time" (the default) applies no date limit, so every Sangh and receipt counts.
 */
class DashboardController extends Controller
{
    /** Start of "All time": financial year 1979-80. */
    private const ALL_TIME_START = '1979-04-01';

    public function index(Request $request)
    {
        $user = $request->user();
        $isSuperadmin = $user->hasRole('superadmin');

        // ---------- Period ----------
        $start = $this->validDate($request->input('start_date'));
        $end = $this->validDate($request->input('end_date'));
        $allTime = $start === null && $end === null;
        $start ??= self::ALL_TIME_START;
        $end ??= now()->toDateString();
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        // ---------- Scope ----------
        $sanghs = fn () => Sangh::query()->visibleTo($user);
        $mySanghIds = Sangh::query()->visibleTo($user)->select('id');
        $regReceipts = fn () => SanghRegistrationReceipt::query()->whereIn('sangh_id', $mySanghIds);
        $renewals = fn () => SanghRenewal::query()->whereIn('sangh_id', $mySanghIds);
        $otherReceipts = fn () => Receipt::query()->visibleTo($user);

        // A Sangh receipt's date: its FESCOM receipt date, else the day it was registered.
        $receiptDate = 'COALESCE(feskcom_receipt_date, registered_at)';
        // Compare as dates (DATE(...)) so stored date-times like "2026-11-18 00:00:00" still match the last day.
        $inPeriod = fn ($q, $expr) => $allTime ? $q : $q->whereRaw("DATE($expr) BETWEEN ? AND ?", [$start, $end]);

        // Sanghs in the period = registered (संघ नोंदणी दिनांक) within it. All time = every Sangh.
        $namedSanghs = fn () => $sanghs()->whereNotNull('name_of_sangh')->where('name_of_sangh', '!=', '');
        $periodSanghs = fn () => $allTime ? $namedSanghs()
            : $namedSanghs()->whereDate('registered_at', '>=', $start)->whereDate('registered_at', '<=', $end);

        // ---------- Headline figures ----------
        $collections = (float) $inPeriod($regReceipts(), $receiptDate)->sum('paid_amount')
            + (float) $inPeriod($renewals(), $receiptDate)->sum('paid_amount')
            + (float) $inPeriod($otherReceipts(), 'DATE(created_at)')->sum('amount');

        // Balance due on the receipts in the period (all time: every receipt).
        // Sum of positive balances (overpaid receipts count as 0). CASE instead of GREATEST so it runs on any database.
        $positiveSum = fn (string $balance) => "COALESCE(SUM(CASE WHEN ($balance) > 0 THEN ($balance) ELSE 0 END),0) AS due";
        $balanceDue = (float) $inPeriod($regReceipts(), $receiptDate)->selectRaw($positiveSum('COALESCE(admission_fee,0)+COALESCE(annual_fee,0)+COALESCE(development_fee,0)-COALESCE(paid_amount,0)'))->value('due')
            + (float) $inPeriod($renewals(), $receiptDate)->selectRaw($positiveSum('COALESCE(annual_fee,0)+COALESCE(development_fee,0)+COALESCE(penalty_fee,0)-COALESCE(paid_amount,0)'))->value('due');

        $registeredSanghs = fn () => $allTime
            ? $namedSanghs()->whereNotNull('registered_at')
            : $periodSanghs();

        $kpi = [
            'all_time' => $allTime,
            'sanghs_total' => $namedSanghs()->count(),
            'sanghs' => $periodSanghs()->count(),
            'blank_rows' => $isSuperadmin ? $sanghs()->where(fn ($q) => $q->whereNull('name_of_sangh')->orWhere('name_of_sangh', ''))->count() : 0,
            'members' => (int) $periodSanghs()->sum('total_members'),
            'male' => (int) $periodSanghs()->sum('male'),
            'female' => (int) $periodSanghs()->sum('female'),
            'collections' => $collections,
            'balance_due' => $balanceDue,
            'registered' => $registeredSanghs()->count(),
            'receipts_in_period' => $inPeriod($regReceipts(), $receiptDate)->count() + $inPeriod($renewals(), $receiptDate)->count(),
        ];

        // ---------- Collections by month (period, max 24 months) ----------
        $from = Carbon::parse($allTime ? now()->subMonths(11)->toDateString() : $start)->startOfMonth();
        $to = Carbon::parse($end)->startOfMonth();
        if ($from->diffInMonths($to) > 23) {
            $from = $to->copy()->subMonths(23);
        }
        $isSqlite = DB::getDriverName() === 'sqlite';
        $monthly = [];
        for ($m = $from->copy(); $m <= $to; $m->addMonth()) {
            $monthly[$m->format('Y-m')] = ['label' => $m->format('M Y'), 'registration' => 0, 'renewal' => 0, 'other' => 0];
        }
        $byMonth = fn ($q, $expr, $sum) => $q->whereRaw("DATE($expr) BETWEEN ? AND ?", [$from->toDateString(), $to->copy()->endOfMonth()->toDateString()])
            ->selectRaw(($isSqlite ? "strftime('%Y-%m', $expr)" : "DATE_FORMAT($expr, '%Y-%m')") . " AS ym, COALESCE(SUM($sum),0) AS total")->groupBy('ym')->pluck('total', 'ym');
        foreach ($byMonth($regReceipts(), $receiptDate, 'paid_amount') as $ym => $t) { if (isset($monthly[$ym])) $monthly[$ym]['registration'] = (float) $t; }
        foreach ($byMonth($renewals(), $receiptDate, 'paid_amount') as $ym => $t) { if (isset($monthly[$ym])) $monthly[$ym]['renewal'] = (float) $t; }
        foreach ($byMonth($otherReceipts(), 'DATE(created_at)', 'amount') as $ym => $t) { if (isset($monthly[$ym])) $monthly[$ym]['other'] = (float) $t; }

        // ---------- Receipt status (in period) ----------
        // A receipt's status date: registered → registered date, approved → receipt date;
        // an unpaid receipt has neither, so the day its status was last saved.
        $statusDate = 'COALESCE(registered_at, feskcom_receipt_date, DATE(updated_at))';
        $statusCounts = ['unpaid' => 0, 'information_approved' => 0, 'sangh_registered' => 0];
        foreach ([$regReceipts(), $renewals()] as $q) {
            foreach ($inPeriod($q, $statusDate)->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status') as $st => $c) {
                $key = $st === 'paid' ? 'information_approved' : ($st ?: 'unpaid');
                $statusCounts[$key] = ($statusCounts[$key] ?? 0) + (int) $c;
            }
        }

        // ---------- Where the Sanghs are (registered in period) ----------
        $groupCount = fn ($column) => $periodSanghs()->whereNotNull($column)->where($column, '!=', '')
            ->selectRaw("$column AS label, COUNT(*) AS c")->groupBy($column)->orderByDesc('c')->get();
        $byVibhag = $this->topWithOther($groupCount('pradeshik_vibhag'), 8);
        $byDistrict = $this->topWithOther($groupCount('district'), 10);

        $byCategory = collect(SanghCodes::CATEGORIES)->map(fn ($label, $code) => ['label' => "$label ($code)", 'c' => $periodSanghs()->where('category_code', $code)->count()])->values();
        $byType = collect(SanghCodes::TYPES)->map(fn ($label, $code) => ['label' => "$label ($code)", 'c' => $periodSanghs()->where('sangh_type_code', $code)->count()])->values();

        // ---------- Workspace ----------
        $workspace = [
            'folders' => Folder::query()->visibleTo($user)->count(),
            'files' => File::query()->visibleTo($user)->count(),
            'groups' => Group::query()->visibleTo($user)->count(),
            'meetings_upcoming' => Meeting::query()->visibleTo($user)->where('start_at', '>=', now())->count(),
            'links' => Link::query()->visibleTo($user)->count(),
            'users' => $isSuperadmin ? User::count() : null,
        ];

        // ---------- Lists ----------
        $upcomingMeetings = Meeting::query()->visibleTo($user)->with('group')
            ->where('start_at', '>=', now())->orderBy('start_at')->limit(5)->get();
        // Most recently registered Sanghs (by संघ नोंदणी दिनांक).
        $recentSanghs = $namedSanghs()->with('registrationReceipt')->whereNotNull('registered_at')
            ->orderByDesc('registered_at')->orderByDesc('id')->limit(6)->get();
        $recentActivity = Activity::query()->with('causer', 'subject')
            ->when(! $isSuperadmin, fn ($q) => $q->where('causer_id', $user->id)->where('causer_type', User::class))
            ->latest()->limit(8)->get()
            ->map(fn ($log) => [
                'summary' => ActivityLogPresenter::summary($log),
                'badge' => ActivityLogPresenter::badge($log),
                'when' => $log->created_at,
            ]);

        return view('dashboard.index', [
            'isSuperadmin' => $isSuperadmin,
            'allTime' => $allTime,
            'startDate' => $start,
            'endDate' => $end,
            'kpi' => $kpi,
            'monthly' => array_values($monthly),
            'statusCounts' => $statusCounts,
            'byVibhag' => $byVibhag,
            'byDistrict' => $byDistrict,
            'byCategory' => $byCategory,
            'byType' => $byType,
            'workspace' => $workspace,
            'upcomingMeetings' => $upcomingMeetings,
            'recentSanghs' => $recentSanghs,
            'recentActivity' => $recentActivity,
        ]);
    }

    /** Top N rows by count; the rest folded into one "Other" row (never a 9th colour). */
    private function topWithOther($rows, int $limit): array
    {
        $top = $rows->take($limit)->map(fn ($r) => ['label' => $r->label, 'c' => (int) $r->c])->values()->all();
        $rest = (int) $rows->slice($limit)->sum('c');
        if ($rest > 0) {
            $top[] = ['label' => 'Other', 'c' => $rest];
        }

        return $top;
    }

    private function validDate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) ? $value : null;
    }
}
