<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer', 'subject')->latest();

        // Members only see log entries for actions they personally performed; superadmin sees everything.
        if (! $request->user()->hasRole('superadmin')) {
            $query->where('causer_id', $request->user()->id)
                  ->where('causer_type', User::class);
        }

        if (in_array($request->action, ['created', 'updated', 'deleted'], true)) {
            $query->where('event', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            // "sangh receipt" should match App\Models\SanghRegistrationReceipt etc.
            $typeSearch = str_replace(' ', '%', $search);
            $query->where(function ($q) use ($search, $typeSearch) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$typeSearch}%")
                  ->orWhere('properties', 'like', "%{$search}%")
                  ->orWhereHasMorph('causer', [User::class], fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.activity_logs.index', compact('logs'));
    }
}
