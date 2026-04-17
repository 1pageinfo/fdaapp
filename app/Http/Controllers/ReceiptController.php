<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Group;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = Receipt::with('user');

        // Date filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Group filter (if receipts belong to groups)
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $receipts = $query->latest()->paginate(10);
        $groups = Group::all();

        return view('receipts.index', compact('receipts', 'groups'));
    }

public function create()
    {
        // $groups = Group::all(); // if you need a group dropdown
        return view('receipts.create'/*, compact('groups')*/);
    }


 public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'amount'  => 'required|numeric',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            // 'group_id' => 'nullable|exists:groups,id', // only if you added group_id
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('receipts', 'public');
        }

        Receipt::create([
            'subject'   => $request->subject,
            'amount'    => $request->amount,
            'file_path' => $path,
            'user_id'   => auth()->id(),
            // 'group_id'  => $request->group_id, // only if you added group_id
        ]);

        return redirect()->route('receipts.index')->with('success', 'Receipt added successfully.');
    }
    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = "receipts_" . now()->format('Ymd_His') . ".csv";
        $query = Receipt::with('user');

        // Apply same filters for export
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $receipts = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Subject', 'Amount', 'User', 'Group', 'File Path', 'Created At'];

        $callback = function() use ($receipts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($receipts as $receipt) {
                fputcsv($file, [
                    $receipt->id,
                    $receipt->subject,
                    $receipt->amount,
                    $receipt->user?->name,
                    $receipt->group?->name ?? '-',   // ✅ show group name
                    $receipt->file_path,
                    $receipt->created_at,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
