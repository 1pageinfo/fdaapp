<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Sangh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;


class SanghController extends Controller
{
    public function index(Request $request)
    {
        $sanghs = Sangh::orderBy('sangh_sr_no', 'asc')->paginate(15);
        return view('sanghs.index', compact('sanghs'));
    }

    public function create()
    {
        return view('sanghs.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name_of_sangh' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'male' => 'nullable|integer|min:0',
            'female' => 'nullable|integer|min:0',
            'receipt_amount' => 'nullable|numeric',
            'date_meeting' => 'nullable|date',
            'receipt_date' => 'nullable|date',
            'total_members' => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Dynamic Sangh Sr No: max + 1
        $max = Sangh::max('sangh_sr_no');
        $nextSr = $max ? $max + 1 : 1;

        $data = $request->only([
            'name_of_sangh','district','district_no','taluka','city','division','division_no',
            'total_m_f','date_meeting','receipt_no','receipt_date','receipt_amount','division_membership_no',
            'male','female','total_members','address','president','tel_no','alt_tel_no','email','secretary'
        ]);

        $data['sangh_sr_no'] = $nextSr;
        $data['created_by'] = Auth::id() ?? null;
        $data['created_date'] = now();

        Sangh::create($data);

        return redirect()->route('sanghs.index')->with('success', 'Sangh created successfully.');
    }

    public function edit(Sangh $sangh)
    {
        return view('sanghs.edit', compact('sangh'));
    }
    public function show(Sangh $sangh)
{
    $sangh->load('creator');  
    return view('sanghs.show', compact('sangh'));
}


    public function update(Request $request, Sangh $sangh)
    {
        $rules = [
            'name_of_sangh' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'male' => 'nullable|integer|min:0',
            'female' => 'nullable|integer|min:0',
            'receipt_amount' => 'nullable|numeric',
            'date_meeting' => 'nullable|date',
            'receipt_date' => 'nullable|date',
            'total_members' => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name_of_sangh','district','district_no','taluka','city','division','division_no',
            'total_m_f','date_meeting','receipt_no','receipt_date','receipt_amount','division_membership_no',
            'male','female','total_members','address','president','tel_no','alt_tel_no','email','secretary'
        ]);

        $sangh->update($data);

        return redirect()->route('sanghs.index')->with('success', 'Sangh updated successfully.');
    }

    public function destroy(Sangh $sangh)
    {
        $sangh->delete();
        return redirect()->route('sanghs.index')->with('success', 'Sangh deleted.');
    }

    /**
     * Export all Sanghs to CSV
     */
    public function exportCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sanghs_export_' . date('Y-m-d_His') . '.csv"',
        ];

        $columns = [
            'Sangh Sr. No',
            'Name of Sangh',
            'District',
            'District No.',
            'Taluka',
            'City',
            'Division',
            'Division No.',
            'Total M/F',
            'Date (Regulatory Board / Annual / Special Meeting )',
            'Receipt No.',
            'Receipt Date',
            'Receipt Amout',
            'Division Membership No.',
            'Male',
            'Female',
            'Total Members',
            'Address',
            'President',
            'Tel. No.',
            'Alt Tel. No.',
            'Email ID',
            'Secretary',
            'Created By',
            'Created Date',
        ];

        $callback = function() use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);

            Sangh::orderBy('sangh_sr_no')->chunk(500, function($sanghs) use ($out) {
                foreach ($sanghs as $s) {
                    fputcsv($out, [
                        $s->sangh_sr_no,
                        $s->name_of_sangh,
                        $s->district,
                        $s->district_no,
                        $s->taluka,
                        $s->city,
                        $s->division,
                        $s->division_no,
                        $s->total_m_f,
                        $s->date_meeting ? $s->date_meeting->format('Y-m-d') : '',
                        $s->receipt_no,
                        $s->receipt_date ? $s->receipt_date->format('Y-m-d') : '',
                        $s->receipt_amount,
                        $s->division_membership_no,
                        $s->male,
                        $s->female,
                        $s->total_members,
                        $s->address,
                        $s->president,
                        $s->tel_no,
                        $s->alt_tel_no,
                        $s->email,
                        $s->secretary,
                        $s->created_by,
                        $s->created_date ? $s->created_date->format('Y-m-d H:i:s') : $s->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Sanghs from CSV file.
     * Expected header names (case-insensitive): match the export column names
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $headerMap = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $row = 0;
            $errors = [];
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                $row++;
                if ($row == 1) {
                    // header
                    foreach ($data as $i => $h) {
                        $headerMap[$i] = strtolower(trim($h));
                    }
                    continue;
                }

                // skip empty rows
                if (count(array_filter($data)) == 0) {
                    continue;
                }

                // map values by header name
                $rowAssoc = [];
                foreach ($headerMap as $i => $colName) {
                    $rowAssoc[$colName] = isset($data[$i]) ? trim($data[$i]) : null;
                }

                // Build data for Sangh
                $nextSr = Sangh::max('sangh_sr_no');
                $nextSr = $nextSr ? $nextSr + 1 : 1;

                $sanghData = [
                    'sangh_sr_no' => $nextSr,
                    'name_of_sangh' => $rowAssoc['name of sangh'] ?? $rowAssoc['name_of_sangh'] ?? null,
                    'district' => $rowAssoc['district'] ?? null,
                    'district_no' => $rowAssoc['district no.'] ?? $rowAssoc['district no'] ?? $rowAssoc['district_no'] ?? null,
                    'taluka' => $rowAssoc['taluka'] ?? null,
                    'city' => $rowAssoc['city'] ?? null,
                    'division' => $rowAssoc['division'] ?? null,
                    'division_no' => $rowAssoc['division no.'] ?? $rowAssoc['division no'] ?? null,
                    'total_m_f' => $rowAssoc['total m/f'] ?? $rowAssoc['total_m_f'] ?? null,
                    'date_meeting' => $this->parseDateIfPresent($rowAssoc['date (regulatory board / annual / special meeting )'] ?? $rowAssoc['date'] ?? null),
                    'receipt_no' => $rowAssoc['receipt no.'] ?? $rowAssoc['receipt_no'] ?? null,
                    'receipt_date' => $this->parseDateIfPresent($rowAssoc['receipt date'] ?? $rowAssoc['receipt_date'] ?? null),
                    'receipt_amount' => $rowAssoc['receipt amout'] ?? $rowAssoc['receipt amount'] ?? $rowAssoc['receipt_amount'] ?? null,
                    'division_membership_no' => $rowAssoc['division membership no.'] ?? null,
                    'male' => is_numeric($rowAssoc['male'] ?? null) ? (int)$rowAssoc['male'] : null,
                    'female' => is_numeric($rowAssoc['female'] ?? null) ? (int)$rowAssoc['female'] : null,
                    'total_members' => is_numeric($rowAssoc['total members'] ?? null) ? (int)$rowAssoc['total members'] : null,
                    'address' => $rowAssoc['address'] ?? null,
                    'president' => $rowAssoc['president'] ?? null,
                    'tel_no' => $rowAssoc['tel. no.'] ?? $rowAssoc['tel no'] ?? $rowAssoc['tel_no'] ?? null,
                    'alt_tel_no' => $rowAssoc['alt tel. no.'] ?? $rowAssoc['alt tel no'] ?? $rowAssoc['alt_tel_no'] ?? null,
                    'email' => $rowAssoc['email id'] ?? $rowAssoc['email'] ?? null,
                    'secretary' => $rowAssoc['secretary'] ?? null,
                    'created_by' => Auth::id() ?? null,
                    'created_date' => now(),
                ];

                // Basic validation per row (you can extend)
                if (empty($sanghData['name_of_sangh'])) {
                    $errors[] = "Row {$row}: Name of Sangh is required. Skipped.";
                    continue;
                }

                Sangh::create($sanghData);
            }

            fclose($handle);

            if (!empty($errors)) {
                return redirect()->back()->with('import_errors', $errors)->with('success', 'Import finished with some skipped rows.');
            }

            return redirect()->route('sanghs.index')->with('success', 'CSV imported successfully.');
        }

        return redirect()->back()->with('error', 'Could not open the CSV file.');
    }

    private function parseDateIfPresent($value)
    {
        if (empty($value)) return null;
        $value = trim($value);
        // Try common formats
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        return null;
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $columns = [
            'Sangh Sr. No', // optional, will be ignored while importing because we generate dynamically
            'Name of Sangh',
            'District',
            'District No.',
            'Taluka',
            'City',
            'Division',
            'Division No.',
            'Total M/F',
            'Date (Regulatory Board / Annual / Special Meeting )',
            'Receipt No.',
            'Receipt Date',
            'Receipt Amout',
            'Division Membership No.',
            'Male',
            'Female',
            'Total Members',
            'Address',
            'President',
            'Tel. No.',
            'Alt Tel. No.',
            'Email ID',
            'Secretary',
            // Created By/Date not necessary in upload
        ];

        $response = new StreamedResponse(function() use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            // sample row (optional)
            fputcsv($handle, [
                '', 'Example Sangh', 'Pune', '12', 'Haveli', 'Pune', 'West Division', '5', '100/90', '2025-01-10',
                'REC001', '2025-01-11', '1500.00', 'DMN1001', '60', '40', '100',
                'Street 12, Pune', 'John Doe', '0123456789', '0987654321', 'example@example.com', 'Jane Doe'
            ]);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sanghs_template.csv"',
        ]);

        return $response;
    }


     public function downloadPdf(Sangh $sangh)
    {
        // load relations if needed
        $sangh->load('creator');

        // Use a dedicated PDF view to avoid including action buttons etc.
        $pdf = \PDF::loadView('sanghs.pdf', compact('sangh'))
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

        $pdf = \PDF::loadView('sanghs.pdf', compact('sangh'))
                   ->setPaper('a4', 'portrait');

        $filename = 'sangh_' . $sangh->sangh_sr_no . '_' . now()->format('Ymd_His') . '.pdf';
        $folder = 'sangh-pdfs';
        $path = $folder . '/' . $filename;

        // Ensure public disk is configured (default exists)
        Storage::disk('public')->put($path, $pdf->output());

        // Public URL (needs `php artisan storage:link` once)
        $url = Storage::disk('public')->url($path);

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

        return Storage::disk('public')->download($path);
    }
}
