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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Dashboard: a detailed overview of the app.
 *
 * Scope — Superadmin sees the whole organisation; Members and Moderators see only
 * what is theirs (created by / assigned to them, or shared via their groups), using
 * the same rules as every module's own list (the models' scopeVisibleTo()).
 *
 * "Period" figures (collections, new Sanghs, receipts, the monthly chart) follow the
 * date filter; snapshot figures (totals, status, vibhag/district split) are current.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isSuperadmin = $user->hasRole('superadmin');

        // ---------- Period ----------
        $start = $this->validDate($request->input('start_date')) ?? now()->startOfYear()->toDateString();
        $end = $this->validDate($request->input('end_date')) ?? now()->toDateString();
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        // ---------- Scope ----------
        $sanghs = fn () => Sangh::query()->visibleTo($user);
        $mySanghIds = Sangh::query()->visibleTo($user)->select('id');
        $regReceipts = fn () => SanghRegistrationReceipt::query()->whereIn('sangh_id', $mySanghIds);
        $renewals = fn () => SanghRenewal::query()->whereIn('sangh_id', $mySanghIds);
        $otherReceipts = fn () => Receipt::query()->visibleTo($user);
        // A receipt's money date: its FESCOM receipt date, else when it was entered.
        $paidOn = 'COALESCE(feskcom_receipt_date, DATE(created_at))';
        $inPeriod = fn ($q, $expr) => $q->whereRaw("$expr BETWEEN ? AND ?", [$start, $end]);

        // ---------- Headline figures ----------
        $namedSanghs = fn () => $sanghs()->whereNotNull('name_of_sangh')->where('name_of_sangh', '!=', '');
        $collections = (float) $inPeriod($regReceipts(), $paidOn)->sum('paid_amount')
            + (float) $inPeriod($renewals(), $paidOn)->sum('paid_amount')
            + (float) $inPeriod($otherReceipts(), 'DATE(created_at)')->sum('amount');

        $balanceDue = (float) $regReceipts()->selectRaw('COALESCE(SUM(GREATEST(COALESCE(admission_fee,0)+COALESCE(annual_fee,0)+COALESCE(development_fee,0)-COALESCE(paid_amount,0),0)),0) AS due')->value('due')
            + (float) $renewals()->selectRaw('COALESCE(SUM(GREATEST(COALESCE(annual_fee,0)+COALESCE(development_fee,0)+COALESCE(penalty_fee,0)-COALESCE(paid_amount,0),0)),0) AS due')->value('due');

        $kpi = [
            'sanghs' => $namedSanghs()->count(),
            'sanghs_new' => $namedSanghs()->whereRaw('DATE(COALESCE(created_date, created_at)) BETWEEN ? AND ?', [$start, $end])->count(),
            'blank_rows' => $isSuperadmin ? $sanghs()->where(fn ($q) => $q->whereNull('name_of_sangh')->orWhere('name_of_sangh', ''))->count() : 0,
            'members' => (int) $namedSanghs()->sum('total_members'),
            'male' => (int) $namedSanghs()->sum('male'),
            'female' => (int) $namedSanghs()->sum('female'),
            'collections' => $collections,
            'balance_due' => $balanceDue,
            'registered' => $regReceipts()->where('status', 'sangh_registered')->count(),
            'receipts_in_period' => $inPeriod($regReceipts(), $paidOn)->count() + $inPeriod($renewals(), $paidOn)->count(),
        ];

        // ---------- Collections by month (period, max 24 months) ----------
        $from = Carbon::parse($start)->startOfMonth();
        $to = Carbon::parse($end)->startOfMonth();
        if ($from->diffInMonths($to) > 23) {
            $from = $to->copy()->subMonths(23);
        }
        $monthly = [];
        for ($m = $from->copy(); $m <= $to; $m->addMonth()) {
            $monthly[$m->format('Y-m')] = ['label' => $m->format('M Y'), 'registration' => 0, 'renewal' => 0, 'other' => 0];
        }
        $byMonth = fn ($q, $expr, $sum) => $q->whereRaw("$expr BETWEEN ? AND ?", [$from->toDateString(), $to->copy()->endOfMonth()->toDateString()])
            ->selectRaw("DATE_FORMAT($expr, '%Y-%m') AS ym, COALESCE(SUM($sum),0) AS total")->groupBy('ym')->pluck('total', 'ym');
        foreach ($byMonth($regReceipts(), $paidOn, 'paid_amount') as $ym => $t) { if (isset($monthly[$ym])) $monthly[$ym]['registration'] = (float) $t; }
        foreach ($byMonth($renewals(), $paidOn, 'paid_amount') as $ym => $t) { if (isset($monthly[$ym])) $monthly[$ym]['renewal'] = (float) $t; }
        foreach ($byMonth($otherReceipts(), 'DATE(created_at)', 'amount') as $ym => $t) { if (isset($monthly[$ym])) $monthly[$ym]['other'] = (float) $t; }

        // ---------- Receipt status (current) ----------
        $statusCounts = ['unpaid' => 0, 'information_approved' => 0, 'sangh_registered' => 0];
        foreach ([$regReceipts(), $renewals()] as $q) {
            foreach ($q->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status') as $st => $c) {
                $key = $st === 'paid' ? 'information_approved' : ($st ?: 'unpaid');
                $statusCounts[$key] = ($statusCounts[$key] ?? 0) + (int) $c;
            }
        }

        // ---------- Where the Sanghs are ----------
        $groupCount = fn ($column, $limit) => $namedSanghs()->whereNotNull($column)->where($column, '!=', '')
            ->selectRaw("$column AS label, COUNT(*) AS c")->groupBy($column)->orderByDesc('c')->get();
        $byVibhag = $this->topWithOther($groupCount('pradeshik_vibhag', 8), 8);
        $byDistrict = $this->topWithOther($groupCount('district', 10), 10);

        $categoryLabels = ['R' => 'ग्रामीण (R)', 'U' => 'शहरी (U)', 'A' => 'निमशहरी (A)'];
        $byCategory = collect($categoryLabels)->map(fn ($label, $code) => ['label' => $label, 'c' => $namedSanghs()->where('category_code', $code)->count()])->values();
        $typeLabels = ['G' => 'सर्वसाधारण (G)', 'F' => 'महिला (F)'];
        $byType = collect($typeLabels)->map(fn ($label, $code) => ['label' => $label, 'c' => $namedSanghs()->where('sangh_type_code', $code)->count()])->values();

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
        $recentSanghs = $namedSanghs()->with('registrationReceipt')
            ->orderByDesc(DB::raw('COALESCE(created_date, created_at)'))->orderByDesc('id')->limit(6)->get();
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
