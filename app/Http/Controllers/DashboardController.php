<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Receipt;
use App\Models\Sangh;
use App\Models\Meeting;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use App\Models\File;

use App\Models\DashboardCache;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Default: current year
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfYear()->toDateString());

        // Query with date filters
        $data = [
            'receipts'   => Receipt::whereBetween('created_at', [$startDate, $endDate])->count(),
            'sanghs'     => Sangh::whereBetween('created_at', [$startDate, $endDate])->count(),
            'meetings'   => Meeting::whereBetween('created_at', [$startDate, $endDate])->count(),
            'folders'    => Folder::whereBetween('created_at', [$startDate, $endDate])->count(),
            'groups'     => Group::whereBetween('created_at', [$startDate, $endDate])->count(),
            'users'      => User::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        return view('dashboard.index', compact('data','startDate','endDate'));
    }
}
