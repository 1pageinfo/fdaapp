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

        $like = '%'.$q.'%';

        // Receipts (simple)
        $receipts = Receipt::select('id','subject','amount','created_at')
            ->where('subject','like',$like)
            ->orderByDesc('created_at')->limit(10)->get();

        // --- Sanghs: select/search only existing columns ---
        $sanghTable = (new Sangh())->getTable();
        $wantedSelect = ['id', 'name', 'district', 'pradeshik_vibhag_name', 'created_at'];
        $wantedSearch  = ['name', 'district', 'pradeshik_vibhag_name'];

        $selectCols = array_values(array_filter($wantedSelect, fn($c) => Schema::hasColumn($sanghTable, $c)));
        $searchCols = array_values(array_filter($wantedSearch, fn($c) => Schema::hasColumn($sanghTable, $c)));

        if (empty($selectCols)) {
            $selectCols = ['id', 'created_at'];
        }

        $sanghQuery = Sangh::select($selectCols);

        if (!empty($searchCols)) {
            $sanghQuery->where(function($w) use ($searchCols, $like) {
                foreach ($searchCols as $i => $col) {
                    if ($i === 0) $w->where($col, 'like', $like);
                    else $w->orWhere($col, 'like', $like);
                }
            });
        } else {
            // fallback: if q numeric, try id
            if (is_numeric($q)) {
                $sanghQuery->where('id', (int)$q);
            } else {
                $sanghs = collect();
            }
        }

        if (!isset($sanghs)) {
            $orderBy = in_array('created_at', $selectCols) ? 'created_at' : 'id';
            $sanghs = $sanghQuery->orderByDesc($orderBy)->limit(10)->get();
        }

        // --- Files: ensure 'name' (or fallback) exists before searching ---
        $fileTable = (new File())->getTable();
        $fileSelect = ['id', 'name', 'path', 'created_at'];
        $fileSelect = array_values(array_filter($fileSelect, fn($c) => Schema::hasColumn($fileTable, $c)));

        // If 'name' doesn't exist, try 'title' as fallback in where/search
        $fileSearchCols = [];
        if (Schema::hasColumn($fileTable, 'name')) {
            $fileSearchCols[] = 'name';
        } elseif (Schema::hasColumn($fileTable, 'title')) {
            $fileSearchCols[] = 'title';
        }

        if (!empty($fileSearchCols)) {
            $filesQuery = File::select($fileSelect ?: ['id','created_at']);
            $filesQuery->where(function($w) use ($fileSearchCols, $like) {
                foreach ($fileSearchCols as $i => $col) {
                    if ($i === 0) $w->where($col, 'like', $like);
                    else $w->orWhere($col, 'like', $like);
                }
            });
            $files = $filesQuery->orderByDesc(in_array('created_at', $fileSelect) ? 'created_at' : 'id')
                        ->limit(10)->get();
        } else {
            // no searchable file columns: return empty collection
            $files = collect();
        }

        // Meetings
        $meetings = Meeting::select('id','title','start_at')
            ->where('title','like',$like)
            ->orderByDesc('start_at')->limit(10)->get();

        // Groups
        $groups = Group::select('id','name','description','created_at')
            ->where(function($w) use ($like){
                $w->where('name','like',$like)
                  ->orWhere('description','like',$like);
            })->orderByDesc('created_at')->limit(10)->get();

        return view('search.results', compact('q','receipts','sanghs','files','meetings','groups'));
    }
}
