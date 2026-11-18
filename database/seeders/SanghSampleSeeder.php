<?php

namespace Database\Seeders;

use App\Models\Sangh;
use App\Models\SanghFeeSlab;
use App\Models\SanghRegistrationReceipt;
use App\Models\SanghRenewal;
use App\Models\Setting;
use App\Models\User;
use App\Support\SanghCodes;
use App\Support\SanghYears;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 50 sample Sanghs for local testing, with variation:
 * every प्रादेशिक विभाग, rural/urban/tribal, general/women, all three receipt
 * statuses, renewals and backdated receipts, registered dates spread over ~3 years
 * (so the dashboard date filters show different numbers).
 *
 *   php artisan db:seed --class=SanghSampleSeeder
 *
 * Not called from DatabaseSeeder — sample data is opt-in. Safe to re-run: it only
 * adds rows. Sample names end with "(नमुना)" so they are easy to spot.
 */
class SanghSampleSeeder extends Seeder
{
    private const COUNT = 50;

    /** प्रादेशिक विभाग => its जिल्हे (same as the Sangh form). */
    private const VIBHAG_DISTRICTS = [
        'अहिल्यानगर' => ['अहिल्यानगर'],
        'बुलढाणा' => ['बुलढाणा'],
        'पूर्व.विदर्भ' => ['भंडारा', 'नागपूर', 'वर्धा'],
        'खानदेश' => ['धुळे', 'जळगाव', 'नंदुरबार'],
        'कोकण' => ['ठाणे', 'पालघर', 'रायगड', 'रत्नागिरी', 'सिंधुदुर्ग'],
        'कोल्हापूर' => ['कोल्हापूर'],
        'मुंबई' => ['मुंबई सिटी', 'मुंबई इतर'],
        'नाशिक' => ['नाशिक'],
        'उत्तर.मराठवाडा' => ['संभाजी नगर', 'हिंगोली', 'जालना', 'नांदेड'],
        'पुणे' => ['पुणे', 'सातारा', 'सोलापूर'],
        'सांगली' => ['सांगली'],
        'दक्षिण.मराठवाडा' => ['बीड', 'लातूर', 'धाराशिव', 'परभणी'],
        'वनवैभव' => ['चंद्रपूर', 'गोंदिया', 'गडचिरोली'],
        'पश्चिम.विदर्भ' => ['अकोला', 'अमरावती', 'वाशीम', 'यवतमाळ'],
    ];

    private const NAME_PARTS = ['दत्तात्रय', 'श्रीराम', 'साईबाबा', 'ज्ञानेश्वर', 'शिवाजी', 'गणेश', 'विठ्ठल', 'संत तुकाराम', 'महात्मा फुले', 'आनंद', 'समर्थ', 'सावित्रीबाई', 'जिजामाता', 'गुरुकृपा', 'नवचैतन्य'];
    private const NAME_SUFFIX = ['ज्येष्ठ नागरिक संघ', 'ज्ये.ना.सं', 'ज्येष्ठ नागरिक मंडळ'];
    private const PEOPLE = ['रमेश पाटील', 'सुरेश जाधव', 'अनिल देशमुख', 'विजय कुलकर्णी', 'प्रकाश शिंदे', 'मधुकर पवार', 'शांता गायकवाड', 'सुमन भोसले', 'लता काळे', 'अरुण मोरे', 'नंदा चव्हाण', 'दिलीप सावंत'];
    private const VILLAGES = ['शिरपूर', 'वडगाव', 'कोरेगाव', 'पिंपळगाव', 'खेडगाव', 'सोनगाव', 'नांदगाव', 'आंबेगाव'];
    private const BANKS = ['बँक ऑफ महाराष्ट्र', 'स्टेट बँक ऑफ इंडिया', 'जिल्हा मध्यवर्ती सहकारी बँक', null];

    public function run(): void
    {
        $user = User::query()->orderBy('id')->first();
        if (! $user) {
            $this->command?->error('No user found — run the UserSeeder (or log in once) first.');

            return;
        }

        mt_srand(20261118); // same sample data on every fresh run
        $this->ensureFeeSettings();

        $pincodes = array_values(config('pincodes.allowed', [])) ?: ['400001'];
        $vibhags = array_keys(self::VIBHAG_DISTRICTS);

        DB::transaction(function () use ($user, $pincodes, $vibhags) {
            for ($i = 0; $i < self::COUNT; $i++) {
                $vibhag = $vibhags[$i % count($vibhags)];            // every vibhag appears
                $districts = self::VIBHAG_DISTRICTS[$vibhag];
                $district = $districts[intdiv($i, count($vibhags)) % count($districts)];
                $category = ['R', 'U', 'A'][$i % 3];
                $type = $i % 4 === 3 ? 'F' : 'G';                     // ~1 in 4 women's Sanghs
                $male = $type === 'F' ? 0 : mt_rand(10, 70);
                $female = $type === 'F' ? mt_rand(25, 80) : mt_rand(10, 60);
                $isCity = $category === 'U';

                // Status mix: 0-1 → no receipt / unpaid, 2-3 → information approved, 4-9 → registered
                $bucket = $i % 10;
                $status = $bucket <= 1 ? 'unpaid' : ($bucket <= 3 ? 'information_approved' : 'sangh_registered');

                // Registered dates spread across the last ~3 years.
                $registeredAt = $status === 'sangh_registered'
                    ? Carbon::today()->subDays(mt_rand(0, 1080))
                    : null;
                $regYear = $registeredAt
                    ? ($registeredAt->month >= 4 ? $registeredAt->year + 1 : $registeredAt->year)  // FY end year
                    : mt_rand((int) date('Y') - 2, min((int) date('Y') + 1, SanghYears::last()));

                $sangh = Sangh::create([
                    'name_of_sangh' => self::NAME_PARTS[$i % count(self::NAME_PARTS)] . ' ' . self::NAME_SUFFIX[$i % 3] . ' ' . ($i + 1) . ' (नमुना)',
                    'registration_year' => $regYear,
                    'category_code' => $category,
                    'sangh_type_code' => $type,
                    'pradeshik_vibhag' => $vibhag,
                    'pradeshik_vibhag_code' => SanghCodes::vibhag($vibhag),
                    'district' => $district,
                    'district_code' => SanghCodes::district($district),
                    'taluka' => $isCity ? null : $district,
                    'village' => $isCity ? null : self::VILLAGES[$i % count(self::VILLAGES)],
                    'mukkam_post' => $isCity ? null : self::VILLAGES[($i + 3) % count(self::VILLAGES)],
                    'address' => $isCity ? 'इमारत क्र. ' . mt_rand(1, 99) . ', ' . $district : null,
                    'road_path' => $isCity ? 'मुख्य रस्ता' : null,
                    'ward_section' => $isCity ? 'प्रभाग ' . mt_rand(1, 20) : null,
                    'city' => $isCity ? $district : null,
                    'pincode' => (string) $pincodes[mt_rand(0, count($pincodes) - 1)],
                    'male' => $male,
                    'female' => $female,
                    'total_members' => $male + $female,
                    'president' => self::PEOPLE[$i % count(self::PEOPLE)],
                    'president_phone' => '98' . str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'president_whatsapp' => '98' . str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'secretary' => self::PEOPLE[($i + 5) % count(self::PEOPLE)],
                    'secretary_phone' => '97' . str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'created_by' => $user->id,
                    'created_date' => now()->subDays(mt_rand(0, 30)),
                ]);

                if ($bucket === 0) {
                    continue; // no registration receipt yet (shown as Unpaid)
                }

                $this->registrationReceipt($sangh, $status, $regYear, $registeredAt, $user->id);

                if ($status === 'sangh_registered') {
                    $sangh->update(['registered_at' => $registeredAt->toDateString()] + $this->numbering($sangh, $registeredAt));
                    $this->renewals($sangh, $regYear, $user->id, $i);
                }

            }
        });

        $this->command?->info(self::COUNT . ' sample Sanghs added.');
    }

    /** Fee settings are needed for the receipts; only filled in when missing. */
    private function ensureFeeSettings(): void
    {
        if (Setting::getValue('sangh_admission_fee') === null) {
            Setting::set('sangh_admission_fee', 100);
        }
        if (Setting::getValue('sangh_development_fee_rate') === null) {
            Setting::set('sangh_development_fee_rate', 10);
        }
        if (! SanghFeeSlab::query()->exists()) {
            SanghFeeSlab::create(['min_members' => 25, 'max_members' => 50, 'annual_fee' => 300]);
            SanghFeeSlab::create(['min_members' => 51, 'max_members' => 100, 'annual_fee' => 500]);
            SanghFeeSlab::create(['min_members' => 101, 'max_members' => null, 'annual_fee' => 800]);
        }
    }

    private function fees(int $members): array
    {
        return [
            'admission' => (float) Setting::getValue('sangh_admission_fee', 0),
            'annual' => (float) (SanghFeeSlab::annualFeeForMemberCount($members) ?? 0),
            'development' => (float) Setting::getValue('sangh_development_fee_rate', 0) * $members,
        ];
    }

    private function registrationReceipt(Sangh $sangh, string $status, int $year, ?Carbon $registeredAt, int $userId): SanghRegistrationReceipt
    {
        $members = (int) $sangh->total_members;
        $fees = $this->fees($members);
        $total = $fees['admission'] + $fees['annual'] + $fees['development'];
        $receiptDate = match ($status) {
            'sangh_registered' => $registeredAt->copy()->subDays(mt_rand(3, 20)),
            'information_approved' => Carbon::today()->subDays(mt_rand(0, 300)),
            default => null,
        };
        // Paid: full, partial or nothing — so balances vary.
        $paid = $status === 'unpaid' ? null : [(int) $total, (int) round($total / 2), (int) $total][mt_rand(0, 2)];
        $bank = $paid ? self::BANKS[mt_rand(0, count(self::BANKS) - 1)] : null;

        $receipt = SanghRegistrationReceipt::create([
            'sangh_id' => $sangh->id,
            'user_id' => $userId,
            'receipt_year' => $year,
            'status' => $status,
            'is_paid' => $status !== 'unpaid',
            'feskcom_receipt_date' => $receiptDate?->toDateString(),
            'registered_at' => $registeredAt?->toDateString(),
            'male_members' => $sangh->male,
            'female_members' => $sangh->female,
            'total_members' => $members,
            'admission_fee' => $fees['admission'],
            'annual_fee' => $fees['annual'],
            'development_fee' => $fees['development'],
            'paid_amount' => $paid,
            'bank_name' => $bank,
            'cheque_no' => $bank ? (mt_rand(0, 1) ? (string) mt_rand(100000, 999999) : 'UPI' . mt_rand(10000000, 99999999)) : null,
            'cheque_date' => $bank ? $receiptDate?->toDateString() : null,
        ]);
        if ($receiptDate) {
            $receipt->update(['feskcom_receipt_no' => 'FSNEW/' . $receipt->id]);
        }

        return $receipt;
    }

    /** Some registered Sanghs get next-year renewals (any status) and an older backdated receipt. */
    private function renewals(Sangh $sangh, int $regYear, int $userId, int $i): void
    {
        $renewalCount = $i % 3;                       // 0, 1 or 2 renewals
        $years = [];
        for ($n = 1; $n <= $renewalCount && $regYear + $n <= SanghYears::last(); $n++) {
            $years[] = [$regYear + $n, false];
        }
        if ($i % 4 === 0 && $regYear - 2 >= SanghYears::FIRST) {
            $years[] = [$regYear - 2, true];          // backdated
        }

        foreach ($years as $k => [$year, $backdated]) {
            $members = (int) $sangh->total_members + mt_rand(-3, 6);
            $male = $sangh->sangh_type_code === 'F' ? 0 : (int) round($members * 0.55);
            $female = $members - $male;
            $fees = $this->fees($members);
            $penalty = $backdated ? 50 : 0;
            $total = $fees['annual'] + $fees['development'] + $penalty;
            $status = ['information_approved', 'unpaid'][($i + $k) % 2]; // renewals are never "Sangh Registered"
            $date = $status === 'unpaid' ? null : Carbon::today()->subDays(mt_rand(0, 400));

            $renewal = SanghRenewal::create([
                'sangh_id' => $sangh->id,
                'user_id' => $userId,
                'renewal_year' => $year,
                'is_backdated' => $backdated,
                'status' => $status,
                'is_paid' => $status !== 'unpaid',
                'feskcom_receipt_date' => $date?->toDateString(),
                'registered_at' => null,
                'male_members' => $male,
                'female_members' => $female,
                'total_members' => $members,
                'annual_fee' => $fees['annual'],
                'development_fee' => $fees['development'],
                'penalty_fee' => $penalty ?: null,
                'paid_amount' => $status === 'unpaid' ? null : (int) ($k % 2 ? round($total / 2) : $total),
                'bank_name' => $status === 'unpaid' ? null : self::BANKS[$k % 3],
            ]);
            $renewal->update(['feskcom_receipt_no' => 'FSREN/' . $renewal->id]);
        }
    }

    /** Same numbering scheme as the app (codes from the form, FY/quarter from the registered date). */
    private function numbering(Sangh $sangh, Carbon $date): array
    {
        $next = fn (string $col, ?string $where = null, $value = null) => (int) Sangh::query()
            ->when($where, fn ($q) => $q->where($where, $value))->max($col) + 1;

        $global = $next('sangh_sr_no');
        $vibhagNo = $next('pradeshik_sr_no', 'pradeshik_vibhag', $sangh->pradeshik_vibhag);
        $districtNo = $next('district_sr_no', 'district', $sangh->district);

        [$quarter, $fyStart] = match (true) {
            $date->month >= 4 && $date->month <= 6 => ['J', $date->year],
            $date->month >= 7 && $date->month <= 9 => ['S', $date->year],
            $date->month >= 10 => ['D', $date->year],
            default => ['M', $date->year - 1],
        };
        $fy = substr((string) $fyStart, -2) . '-' . substr((string) ($fyStart + 1), -2);

        return [
            'sangh_sr_no' => $global,
            'unique_ref_no' => "{$sangh->category_code}/{$sangh->sangh_type_code}/{$fy}/{$quarter}/{$global}",
            'pradeshik_sr_no' => $vibhagNo,
            'pradeshik_ref_no' => SanghCodes::vibhag($sangh->pradeshik_vibhag) . '/' . $vibhagNo,
            'district_sr_no' => $districtNo,
            'district_ref_no' => SanghCodes::district($sangh->district) . '/' . $districtNo,
        ];
    }
}
