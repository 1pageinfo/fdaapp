<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Receipt;
use App\Models\Sangh;
use App\Models\File;
use App\Models\Meeting;
use App\Models\Group;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        if ($q === '') {
            return view('search.results', [
                'q' => '',
                'receipts' => collect(),
                'sanghs'   => collect(),
                'files'    => collect(),
                'meetings' => collect(),
                'groups'   => collect(),
            ]);
        }

        $user = $request->user();
        $isSuperadmin = $user->hasRole('superadmin');
        $like = '%'.$q.'%';

        // Each section is only searched at all if the user holds that feature's
        // view permission, and results are further scoped to what they can see —
        // matching the same boundary each resource's own index() enforces.
        $receipts = collect();
        if ($isSuperadmin || $user->hasPermission('receipts.view')) {
            $receiptsQuery = Receipt::select('id', 'subject', 'amount', 'user_id', 'created_at')
                ->where('subject', 'like', $like);
            if (! $isSuperadmin) {
                $receiptsQuery->where('user_id', $user->id);
            }
            $receipts = $receiptsQuery->orderByDesc('created_at')->limit(10)->get();
        }

        // --- Sanghs: select/search only existing columns ---
        $sanghs = collect();
        if ($isSuperadmin || $user->hasPermission('sanghs.view')) {
            $sanghTable = (new Sangh())->getTable();
            $wantedSelect = ['id', 'name_of_sangh', 'district', 'pradeshik_vibhag', 'created_by', 'assigned_to', 'created_at'];
            $wantedSearch  = ['name_of_sangh', 'district', 'pradeshik_vibhag'];

            $selectCols = array_values(array_filter($wantedSelect, fn($c) => Schema::hasColumn($sanghTable, $c)));
            $searchCols = array_values(array_filter($wantedSearch, fn($c) => Schema::hasColumn($sanghTable, $c)));

            if (empty($selectCols)) {
                $selectCols = ['id', 'created_at'];
            }

            $sanghQuery = Sangh::select($selectCols);

            if (! $isSuperadmin) {
                $sanghQuery->where(function ($w) use ($user) {
                    $w->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                });
            }

            if (!empty($searchCols)) {
                $sanghQuery->where(function($w) use ($searchCols, $like) {
                    foreach ($searchCols as $i => $col) {
                        if ($i === 0) $w->where($col, 'like', $like);
                        else $w->orWhere($col, 'like', $like);
                    }
                });
                $orderBy = in_array('created_at', $selectCols) ? 'created_at' : 'id';
                $sanghs = $sanghQuery->orderByDesc($orderBy)->limit(10)->get();
            } elseif (is_numeric($q)) {
                $sanghs = $sanghQuery->where('id', (int) $q)->limit(10)->get();
            }
        }

        // --- Files: ensure 'name' (or fallback) exists before searching ---
        $files = collect();
        if ($isSuperadmin || $user->hasPermission('files.view')) {
            $fileTable = (new File())->getTable();
            $fileSelect = ['id', 'name', 'path', 'folder_id', 'uploaded_by', 'created_at'];
            $fileSelect = array_values(array_filter($fileSelect, fn($c) => Schema::hasColumn($fileTable, $c)));

            $fileSearchCols = [];
            if (Schema::hasColumn($fileTable, 'name')) {
                $fileSearchCols[] = 'name';
            } elseif (Schema::hasColumn($fileTable, 'title')) {
                $fileSearchCols[] = 'title';
            }

            if (!empty($fileSearchCols)) {
                $filesQuery = File::select($fileSelect ?: ['id', 'created_at']);
                $filesQuery->where(function($w) use ($fileSearchCols, $like) {
                    foreach ($fileSearchCols as $i => $col) {
                        if ($i === 0) $w->where($col, 'like', $like);
                        else $w->orWhere($col, 'like', $like);
                    }
                });

                $candidates = $filesQuery->orderByDesc(in_array('created_at', $fileSelect) ? 'created_at' : 'id')
                    ->limit(50)->get();

                $files = $isSuperadmin
                    ? $candidates->take(10)
                    : $candidates->filter(fn ($f) => $f->isVisibleTo($user))->take(10)->values();
            }
        }

        // Meetings
        $meetings = collect();
        if ($isSuperadmin || $user->hasPermission('meetings.view')) {
            $meetingCandidates = Meeting::select('id', 'title', 'start_at', 'group_id', 'created_by', 'assigned_to')
                ->where('title', 'like', $like)
                ->orderByDesc('start_at')->limit(50)->get();

            $meetings = $isSuperadmin
                ? $meetingCandidates->take(10)
                : $meetingCandidates->filter(fn ($m) => $m->isVisibleTo($user))->take(10)->values();
        }

        // Groups
        $groups = collect();
        if ($isSuperadmin || $user->hasPermission('groups.view')) {
            $groupCandidates = Group::select('id', 'name', 'description', 'created_by', 'assigned_to', 'created_at')
                ->where(function($w) use ($like){
                    $w->where('name','like',$like)
                      ->orWhere('description','like',$like);
                })->orderByDesc('created_at')->limit(50)->get();

            $groups = $isSuperadmin
                ? $groupCandidates->take(10)
                : $groupCandidates->filter(fn ($g) => $g->isVisibleTo($user))->take(10)->values();
        }

        return view('search.results', compact('q','receipts','sanghs','files','meetings','groups'));
    }
}
