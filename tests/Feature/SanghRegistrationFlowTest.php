<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Sangh;
use App\Models\SanghFeeSlab;
use App\Models\SanghRegistrationReceipt;
use App\Models\SanghRenewal;
use App\Models\Setting;
use App\Models\User;
use App\Support\PradeshikFees;
use App\Support\SanghYears;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Sangh flow end to end, through real HTTP requests:
 * create → edit → registration receipt (Unpaid → Information approved → Sangh Registered,
 * locked) → renewal / backdated (only after registration) → list filters & counts →
 * details / receipts pages → dashboard → export.
 */
class SanghRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::firstOrCreate(['slug' => 'superadmin'], ['name' => 'Superadmin']));

        Setting::set('sangh_admission_fee', 100);
        Setting::set('sangh_development_fee_rate', 10);
        SanghFeeSlab::create(['min_members' => 25, 'max_members' => 50, 'annual_fee' => 300]);
        SanghFeeSlab::create(['min_members' => 51, 'max_members' => null, 'annual_fee' => 500]);
    }

    private function formData(array $overrides = []): array
    {
        return array_merge([
            'name_of_sangh' => 'दत्तात्रय ज्ये.ना.सं',
            'registration_year' => (string) SanghYears::last(),   // current year + 2 is allowed
            'category_code' => 'A',
            'sangh_type_code' => 'G',
            'pradeshik_vibhag' => 'मुंबई',
            'pradeshik_vibhag_code' => 'MM',
            'district' => 'मुंबई सिटी',
            'district_code' => 'MC',
            'taluka' => 'मुंबई शहर',
            'village' => 'दादर',
            'male' => '२०',          // Marathi digits → 20
            'female' => '10',
            'president' => 'रमेश पाटील',
            'president_phone' => '9876543210',
            'secretary' => 'सुमन भोसले',
        ], $overrides);
    }

    public function test_full_sangh_flow(): void
    {
        $this->actingAs($this->admin);

        // ---------- 1. Create ----------
        $this->post(route('sanghs.store'), $this->formData())
            ->assertSessionHasNoErrors()->assertRedirect(route('sanghs.index'));
        $sangh = Sangh::firstOrFail();
        $this->assertSame(20, (int) $sangh->male, 'Marathi digits are saved as English');
        $this->assertSame(30, (int) $sangh->total_members);
        $this->assertSame(SanghYears::last(), (int) $sangh->registration_year);
        $this->assertNull($sangh->unique_ref_no, 'no number before registration');
        $this->assertNull($sangh->registered_at);
        $this->assertSame(100.0, (float) $sangh->pradeshik_admission_fee, 'प्रवेश शुल्क × 1');
        $this->assertSame(600.0, (float) $sangh->pradeshik_annual_fee, 'वार्षिक × 2');
        $this->assertSame(900.0, (float) $sangh->pradeshik_development_fee, 'विकास 10 × 30 × 3');

        // Year outside 1979-80 … current + 2 is rejected.
        $this->post(route('sanghs.store'), $this->formData(['registration_year' => '1979', 'name_of_sangh' => 'दुसरा संघ']))
            ->assertSessionHasErrors('registration_year');

        // ---------- 2. Edit keeps it unnumbered ----------
        $this->put(route('sanghs.update', $sangh), $this->formData(['female' => '15']))
            ->assertSessionHasNoErrors();
        $sangh->refresh();
        $this->assertSame(35, (int) $sangh->total_members);
        $this->assertNull($sangh->unique_ref_no);

        // ---------- 3. Renewal / backdated blocked before registration ----------
        $this->post(route('sanghs.renewals.create', $sangh), ['renewal_year' => SanghYears::last(), 'receipt_type' => 'renewal'])
            ->assertSessionHas('error');
        $this->post(route('sanghs.renewals.create', $sangh), ['renewal_year' => 2000, 'receipt_type' => 'backdated'])
            ->assertSessionHas('error');
        $this->assertSame(0, SanghRenewal::count());
        $this->get(route('sanghs.receipts', $sangh))->assertOk()->assertSee('Register the Sangh first', false)
            ->assertSee('Renewal is available after the Sangh is registered', false);

        // ---------- 4. Registration receipt: Unpaid → Information approved ----------
        $this->post(route('sanghs.registration_receipt.update', $sangh), ['status' => 'unpaid'])->assertSessionHasNoErrors();
        $this->assertSame('unpaid', SanghRegistrationReceipt::first()->status);

        $this->post(route('sanghs.registration_receipt.update', $sangh), [
            'status' => 'unpaid', 'feskcom_receipt_date' => now()->toDateString(), // a receipt date → approved
            'paid_amount' => '200', 'bank_name' => 'बँक ऑफ महाराष्ट्र', 'cheque_no' => 'UPI12345678',
        ])->assertSessionHasNoErrors();
        $receipt = SanghRegistrationReceipt::first();
        $this->assertSame('information_approved', $receipt->status);
        $this->assertStringStartsWith('FSNEW/', $receipt->feskcom_receipt_no);
        $this->assertNull($sangh->fresh()->unique_ref_no, 'approved is not registered');

        // ---------- 5. Sangh Registered: numbering from registered date + form codes ----------
        $this->post(route('sanghs.registration_receipt.update', $sangh), [
            'status' => 'sangh_registered', 'feskcom_receipt_date' => now()->toDateString(), 'paid_amount' => '200',
        ])->assertSessionHasNoErrors();
        $sangh->refresh();
        $this->assertSame(now()->toDateString(), $sangh->registered_at->toDateString());
        $fyStart = now()->month >= 4 ? now()->year : now()->year - 1;
        $fy = substr((string) $fyStart, -2) . '-' . substr((string) ($fyStart + 1), -2);
        $this->assertMatchesRegularExpression("#^A/G/{$fy}/[JSDM]/1$#", $sangh->unique_ref_no, 'FY from registered date, not created date');
        $this->assertSame('MM/1', $sangh->pradeshik_ref_no);
        $this->assertSame('MC/1', $sangh->district_ref_no);
        $number = $sangh->unique_ref_no;

        // ---------- 6. Registration is locked ----------
        $this->post(route('sanghs.registration_receipt.update', $sangh), [
            'status' => 'unpaid', 'paid_amount' => '400', // try to move it back
        ])->assertSessionHasNoErrors();
        $receipt->refresh();
        $this->assertSame('sangh_registered', $receipt->status, 'status stays Sangh Registered');
        $this->assertSame(400, (int) $receipt->paid_amount, 'payment can still be corrected');
        $this->assertSame(now()->toDateString(), $receipt->registered_at->toDateString());
        $this->assertSame($number, $sangh->fresh()->unique_ref_no, 'number never changes');

        // ---------- 7. Renewal (next year only) and backdated, after registration ----------
        $this->get(route('sanghs.receipts', $sangh))->assertOk()
            ->assertSee('Registration is done once', false)
            ->assertDontSee('Renewal is available after the Sangh is registered', false);

        $nextYear = (int) $sangh->registration_year + 1;
        if ($nextYear <= SanghYears::last()) {
            $this->post(route('sanghs.renewals.create', $sangh), ['renewal_year' => $nextYear, 'receipt_type' => 'renewal'])
                ->assertSessionHas('success');
        }
        $this->post(route('sanghs.renewals.create', $sangh), ['renewal_year' => 2000, 'receipt_type' => 'backdated'])
            ->assertSessionHas('success');
        $this->post(route('sanghs.renewals.create', $sangh), ['renewal_year' => 2000, 'receipt_type' => 'backdated'])
            ->assertSessionHas('error'); // same year twice
        $this->post(route('sanghs.renewals.create', $sangh), ['renewal_year' => 1979, 'receipt_type' => 'backdated'])
            ->assertSessionHasErrors('renewal_year'); // before 1979-80

        // Renewal statuses: Unpaid / Information approved only.
        $this->post(route('sanghs.renewals.update', [$sangh, 2000]), [
            'status' => 'sangh_registered', 'male_members' => '20', 'female_members' => '15',
        ])->assertSessionHasErrors('status');
        $this->post(route('sanghs.renewals.update', [$sangh, 2000]), [
            'status' => 'information_approved', 'male_members' => '20', 'female_members' => '15',
            'penalty_fee' => '50', 'paid_amount' => '100', 'cheque_no' => '654321',
        ])->assertSessionHasNoErrors();
        $renewal = SanghRenewal::where('renewal_year', 2000)->first();
        $this->assertTrue((bool) $renewal->is_backdated);
        $this->assertSame('information_approved', $renewal->status);
        $this->assertSame(300.0, (float) $renewal->annual_fee);
        $this->assertSame(350.0, (float) $renewal->development_fee);
        $this->assertSame($number, $sangh->fresh()->unique_ref_no, 'renewals never renumber');

        // ---------- 8. Details page: two-column details + every receipt, printable ----------
        $this->get(route('sanghs.show', $sangh))->assertOk()
            ->assertSee('आदिवासी - A', false)
            ->assertSee('kv-grid', false)
            ->assertSee('MM/1', false)
            ->assertSee('Sangh Registered: 1', false)
            ->assertSee('Information approved: 1', false)
            ->assertSee('FSNEW/', false)
            ->assertSee('654321', false)
            ->assertSee('(× 1)', false);

        // ---------- 9. Receipts page: status tabs, no Type column, new cheque label ----------
        $this->get(route('sanghs.receipts', $sangh))->assertOk()
            ->assertSee('data-status-tab="all"', false)
            ->assertSee('data-status-tab="unpaid"', false)
            ->assertSee('data-status-tab="information_approved"', false)
            ->assertSee('data-status-tab="sangh_registered"', false)
            ->assertDontSee('<th>Type</th>', false)
            ->assertSee('चेक क्रमांक / Transaction क्रमांक', false)
            ->assertDontSee('value="sangh_registered" @selected', false);

        // ---------- 10. List: registered-date filter and the counts line ----------
        $this->get(route('sanghs.index', ['from_date' => now()->toDateString(), 'to_date' => now()->toDateString()]))
            ->assertOk()->assertSee('दत्तात्रय ज्ये.ना.सं', false)
            ->assertSee('Register Receipts', false)->assertSee('Renewal Receipts', false);
        $this->get(route('sanghs.index', ['from_date' => '2001-01-01', 'to_date' => '2001-12-31']))
            ->assertOk()->assertDontSee('दत्तात्रय ज्ये.ना.सं', false);

        // ---------- 11. Dashboard follows the period (registered date) ----------
        $this->get(route('dashboard'))->assertOk()->assertSee('All time', false);
        $this->get(route('dashboard', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]))
            ->assertOk()->assertViewHas('kpi', fn ($k) => $k['sanghs'] === 1 && $k['registered'] === 1);
        $this->get(route('dashboard', ['start_date' => '2001-01-01', 'end_date' => '2001-12-31']))
            ->assertOk()->assertViewHas('kpi', fn ($k) => $k['sanghs'] === 0 && $k['registered'] === 0);

        // ---------- 12. Export downloads ----------
        $this->get(route('sanghs.export'))->assertOk()->assertDownload();

        // ---------- 13. Edit form: year list 1979-80 … current + 2, text member fields ----------
        $this->get(route('sanghs.edit', $sangh))->assertOk()
            ->assertSee('<option value="1980"', false)
            ->assertSee('<option value="' . SanghYears::last() . '"', false)
            ->assertDontSee('<option value="1979"', false)
            ->assertSee('data-int id="male"', false);
    }

    public function test_minimum_members_and_fee_multiplier(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('sanghs.store'), $this->formData(['male' => '5', 'female' => '5']))
            ->assertSessionHasErrors('male');
        $this->assertSame(1, PradeshikFees::ADMISSION_MULTIPLIER);
    }
}
