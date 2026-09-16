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
            'sangh_registrations'    => \App\Models\SanghRegistrationReceipt::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count(),
            'sangh_renewals'         => \App\Models\SanghRenewal::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count(),
            'total_collections'      => \App\Models\SanghRegistrationReceipt::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->sum('paid_amount') 
                                      + \App\Models\SanghRenewal::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->sum('paid_amount')
                                      + \App\Models\Receipt::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->sum('amount'),
            'sanghs'     => Sangh::whereDate('created_date', '>=', $startDate)->whereDate('created_date', '<=', $endDate)->count(),
            'meetings'   => Meeting::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count(),
            'folders'    => Folder::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count(),
            'groups'     => Group::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count(),
            'users'      => User::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count(),
        ];

        return view('dashboard.index', compact('data','startDate','endDate'));
    }
}
