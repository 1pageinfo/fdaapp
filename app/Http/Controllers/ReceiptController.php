<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    protected function scopeVisible($query, Request $request)
    {
        if (! $request->user()->hasRole('superadmin')) {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = Receipt::with('user');
        $this->scopeVisible($query, $request);

        // Date filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $receipts = $query->latest()->paginate(10);

        return view('receipts.index', compact('receipts'));
    }

    public function create()
    {
        return view('receipts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0',
            'file'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
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
        ]);

        return redirect()->route('receipts.index')->with('success', 'Receipt added successfully.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = "receipts_" . now()->format('Ymd_His') . ".csv";
        $query = Receipt::with('user');
        $this->scopeVisible($query, $request);

        // Apply same filters for export
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $receipts = $query->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Subject', 'Amount', 'User', 'File Path', 'Created At'];

        $callback = function() use ($receipts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($receipts as $receipt) {
                fputcsv($file, [
                    $receipt->id,
                    $receipt->subject,
                    $receipt->amount,
                    $receipt->user?->name,
                    $receipt->file_path,
                    $receipt->created_at,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
