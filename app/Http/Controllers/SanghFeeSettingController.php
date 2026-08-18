<?php

namespace App\Http\Controllers;

use App\Models\SanghFeeSlab;
use App\Models\Setting;
use Illuminate\Http\Request;

class SanghFeeSettingController extends Controller
{
    public function edit()
    {
        $slabs = SanghFeeSlab::orderBy('min_members')->get();
        $admissionFee = Setting::getValue('sangh_admission_fee', 0);
        $developmentFeeRate = Setting::getValue('sangh_development_fee_rate', 0);

        return view('settings.sangh_fees', compact('slabs', 'admissionFee', 'developmentFeeRate'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'admission_fee' => 'required|numeric|min:0',
            'development_fee_rate' => 'required|numeric|min:0',
        ]);

        Setting::set('sangh_admission_fee', $validated['admission_fee']);
        Setting::set('sangh_development_fee_rate', $validated['development_fee_rate']);

        return back()->with('success', 'Standard fees updated.');
    }

    public function storeSlab(Request $request)
    {
        $validated = $request->validate([
            'min_members' => 'required|integer|min:0',
            'max_members' => 'nullable|integer|gte:min_members',
            'annual_fee' => 'required|numeric|min:0',
        ]);

        SanghFeeSlab::create($validated);

        return back()->with('success', 'Fee slab added.');
    }

    public function destroySlab(SanghFeeSlab $slab)
    {
        $slab->delete();

        return back()->with('success', 'Fee slab removed.');
    }
}
