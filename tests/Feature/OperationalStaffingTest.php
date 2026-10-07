<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\MobilizationRecommendation;
use App\Models\OperationalRound;
use App\Models\OperationalStaffingStandard;
use App\Models\User;
use App\Services\WisnCalculatorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalStaffingTest extends TestCase
{
    use RefreshDatabase;

    public function test_day_handover_uses_configured_unit_targets_without_changing_annual_wisn_data(): void
    {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $date = Carbon::now($timezone)->toDateString();
        Carbon::setTestNow(Carbon::parse($date . ' 07:15:00', $timezone));

        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8, 'general_ward');
        $surgical = $this->createDepartment('Surgical Ward', 7, 'surgical_ward', 'Surgical/OT');
        $this->createStandard($user, 'general_ward', 'day', 4, 1, $date);
        $this->createStandard($user, 'surgical_ward', 'day', 3, 1, $date);

        $medical->activities()->create([
            'activity_name' => 'Annual nursing care',
            'activity_type' => 'health_service',
            'time_standard_hours' => 1,
            'annual_volume' => 1696,
        ]);
        $wisnBefore = app(WisnCalculatorService::class)
            ->calculateDepartmentStaffing($medical->fresh()->load('activities'));

        $this->actingAs($user)
            ->post(route('operations.snapshots.store'), [
                'census_date' => $date,
                'shift_code' => OperationalRound::SHIFT_DAY,
                'departments' => [
                    $medical->id => ['patient_count' => 4, 'on_duty_staff' => 3],
                    $surgical->id => ['patient_count' => 9, 'on_duty_staff' => 1],
                ],
                'round_notes' => 'Observed day handover',
            ])
            ->assertRedirect(route('operations.index', ['date' => $date, 'shift' => 'day']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('department_operational_snapshots', [
            'department_id' => $medical->id,
            'patient_count' => 4,
            'on_duty_staff' => 3,
            'calculated_required_staff' => 1,
        ]);
        $this->assertDatabaseHas('department_operational_snapshots', [
            'department_id' => $surgical->id,
            'patient_count' => 9,
            'on_duty_staff' => 1,
            'calculated_required_staff' => 3,
        ]);
        $this->assertDatabaseHas('mobilization_recommendations', [
            'from_department_id' => $medical->id,
            'to_department_id' => $surgical->id,
            'staff_count' => 2,
            'status' => MobilizationRecommendation::STATUS_PENDING,
        ]);

        $this->assertSame(8, $medical->fresh()->current_staff);
        $this->assertSame(7, $surgical->fresh()->current_staff);
        $wisnAfter = app(WisnCalculatorService::class)
            ->calculateDepartmentStaffing($medical->fresh()->load('activities'));
        $this->assertSame($wisnBefore, $wisnAfter);
    }

    public function test_midnight_is_a_census_only_snapshot_and_outpatient_units_are_excluded(): void
    {
        $date = Carbon::now(config('operations.timezone'))->toDateString();
        $user = User::factory()->create();
        $ward = $this->createDepartment('General Ward', 8, 'general_ward');
        $opd = $this->createDepartment('Outpatient Department (OPD)', 5, 'outpatient', 'Outpatient');

        $this->actingAs($user)
            ->post(route('operations.snapshots.store'), [
                'census_date' => $date,
                'shift_code' => OperationalRound::SHIFT_MIDNIGHT,
                'departments' => [
                    $ward->id => ['patient_count' => 12],
                ],
            ])
            ->assertRedirect(route('operations.index', ['date' => $date, 'shift' => 'midnight']))
            ->assertSessionHas('success');

        $round = OperationalRound::query()->firstOrFail();
        $this->assertSame('midnight', $round->shift_code);
        $this->assertSame($date, $round->census_date->toDateString());
        $this->assertDatabaseHas('department_operational_snapshots', [
            'operational_round_id' => $round->id,
            'department_id' => $ward->id,
            'patient_count' => 12,
            'on_duty_staff' => null,
            'calculated_required_staff' => null,
            'staffing_standard_id' => null,
        ]);
        $this->assertDatabaseMissing('department_operational_snapshots', ['department_id' => $opd->id]);
        $this->assertDatabaseCount('mobilization_recommendations', 0);
    }

    public function test_a_missing_standard_leaves_required_staff_blank_and_creates_no_recommendation(): void
    {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $date = Carbon::now($timezone)->toDateString();
        Carbon::setTestNow(Carbon::parse($date . ' 07:15:00', $timezone));
        $user = User::factory()->create();
        $ward = $this->createDepartment('General Ward', 8, 'general_ward');

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'census_date' => $date,
            'shift_code' => 'day',
            'departments' => [$ward->id => ['patient_count' => 15, 'on_duty_staff' => 2]],
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('department_operational_snapshots', [
            'department_id' => $ward->id,
            'calculated_required_staff' => null,
            'staffing_standard_id' => null,
        ]);
        $this->assertDatabaseCount('mobilization_recommendations', 0);
    }

    public function test_approved_move_review_is_audited_and_does_not_change_headcounts(): void
    {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $date = Carbon::now($timezone)->toDateString();
        Carbon::setTestNow(Carbon::parse($date . ' 07:15:00', $timezone));

        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8, 'general_ward');
        $surgical = $this->createDepartment('Surgical Ward', 7, 'surgical_ward', 'Surgical/OT');
        $this->createStandard($user, 'general_ward', 'day', 4, 1, $date);
        $this->createStandard($user, 'surgical_ward', 'day', 3, 1, $date);

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'census_date' => $date,
            'shift_code' => 'day',
            'departments' => [
                $medical->id => ['patient_count' => 4, 'on_duty_staff' => 3],
                $surgical->id => ['patient_count' => 9, 'on_duty_staff' => 1],
            ],
        ])->assertSessionHas('success');

        $recommendation = MobilizationRecommendation::query()->firstOrFail();
        $this->actingAs($user)
            ->post(route('operations.recommendations.approve', $recommendation), ['decision_note' => 'Reviewed by shift lead'])
            ->assertRedirect(route('operations.index', ['date' => $date, 'shift' => 'day']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mobilization_recommendations', [
            'id' => $recommendation->id,
            'status' => MobilizationRecommendation::STATUS_APPROVED,
            'reviewed_by' => $user->id,
            'decision_note' => 'Reviewed by shift lead',
        ]);
        $this->assertSame(8, $medical->fresh()->current_staff);
        $this->assertSame(7, $surgical->fresh()->current_staff);
    }

    public function test_operations_page_shows_three_census_times_and_excludes_outpatient_units(): void
    {
        $this->withoutVite();
        $date = Carbon::now(config('operations.timezone'))->toDateString();
        $user = User::factory()->create();
        $this->createDepartment('General Ward', 8, 'general_ward');
        $this->createDepartment('Outpatient Department (OPD)', 5, 'outpatient', 'Outpatient');

        $this->actingAs($user)
            ->get(route('operations.index', ['date' => $date, 'shift' => 'day']))
            ->assertOk()
            ->assertSee('Inpatient Census &amp; Shift Staffing', false)
            ->assertSee('12:00 AM')
            ->assertSee('7:00 AM')
            ->assertSee('7:00 PM')
            ->assertSee('General Ward')
            ->assertDontSee('Outpatient Department (OPD)')
            ->assertDontSee('Required nurses now');
    }

    public function test_only_admins_can_manage_target_standards_and_activation_is_audited(): void
    {
        $date = Carbon::now(config('operations.timezone'))->toDateString();
        $regularUser = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($regularUser)
            ->get(route('operations.standards.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('operations.standards.store'), [
                'unit_type' => 'general_ward',
                'shift_code' => 'day',
                'patients_per_nurse' => 6,
                'minimum_nurses_per_shift' => 1,
                'source_name' => 'Hospital nursing policy',
                'source_version' => '2026 approved version',
                'effective_from' => $date,
                'is_active' => 1,
            ])
            ->assertRedirect(route('operations.standards.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('operational_staffing_standards', [
            'unit_type' => 'general_ward',
            'shift_code' => 'day',
            'patients_per_nurse' => 6,
            'is_active' => 1,
            'approved_by' => $admin->id,
        ]);
    }

    public function test_new_snapshot_supersedes_prior_unreviewed_move_suggestions(): void
    {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $date = Carbon::now($timezone)->toDateString();
        Carbon::setTestNow(Carbon::parse($date . ' 07:15:00', $timezone));

        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8, 'general_ward');
        $surgical = $this->createDepartment('Surgical Ward', 7, 'surgical_ward', 'Surgical/OT');
        $this->createStandard($user, 'general_ward', 'day', 4, 1, $date);
        $this->createStandard($user, 'surgical_ward', 'day', 3, 1, $date);
        $payload = [
            'census_date' => $date,
            'shift_code' => 'day',
            'departments' => [
                $medical->id => ['patient_count' => 4, 'on_duty_staff' => 3],
                $surgical->id => ['patient_count' => 9, 'on_duty_staff' => 1],
            ],
        ];

        $this->actingAs($user)->post(route('operations.snapshots.store'), $payload);
        $firstRecommendation = MobilizationRecommendation::query()->firstOrFail();
        $this->actingAs($user)->post(route('operations.snapshots.store'), $payload);

        $this->assertDatabaseHas('mobilization_recommendations', [
            'id' => $firstRecommendation->id,
            'status' => MobilizationRecommendation::STATUS_SUPERSEDED,
        ]);
    }

    public function test_changing_an_active_target_supersedes_pending_move_suggestions(): void
    {
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $date = Carbon::now($timezone)->toDateString();
        Carbon::setTestNow(Carbon::parse($date . ' 07:15:00', $timezone));

        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $medical = $this->createDepartment('Medical Ward', 8, 'general_ward');
        $surgical = $this->createDepartment('Surgical Ward', 7, 'surgical_ward', 'Surgical/OT');
        $this->createStandard($admin, 'general_ward', 'day', 4, 1, $date);
        $this->createStandard($admin, 'surgical_ward', 'day', 3, 1, $date);

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'census_date' => $date,
            'shift_code' => 'day',
            'departments' => [
                $medical->id => ['patient_count' => 4, 'on_duty_staff' => 3],
                $surgical->id => ['patient_count' => 9, 'on_duty_staff' => 1],
            ],
        ]);
        $recommendation = MobilizationRecommendation::query()->firstOrFail();

        $this->actingAs($admin)->post(route('operations.standards.store'), [
            'unit_type' => 'general_ward',
            'shift_code' => 'day',
            'patients_per_nurse' => 4.5,
            'minimum_nurses_per_shift' => 1,
            'source_name' => 'Updated hospital nursing policy',
            'source_version' => '2026 revision',
            'effective_from' => $date,
            'is_active' => 1,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('mobilization_recommendations', [
            'id' => $recommendation->id,
            'status' => MobilizationRecommendation::STATUS_SUPERSEDED,
        ]);
    }

    public function test_unit_with_operational_history_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now(config('operations.timezone'))->toDateString();
        $ward = $this->createDepartment('General Ward', 8, 'general_ward');

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'census_date' => $date,
            'shift_code' => 'midnight',
            'departments' => [$ward->id => ['patient_count' => 10]],
        ]);

        $this->actingAs($user)
            ->delete(route('departments.destroy', $ward))
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $ward->id]);
        $this->assertDatabaseHas('department_operational_snapshots', ['department_id' => $ward->id]);
    }

    public function test_stale_handover_recommendations_cannot_be_approved(): void
    {
        config(['operations.recommendation_freshness_minutes' => 30]);
        $timezone = config('operations.timezone', 'Asia/Kathmandu');
        $date = Carbon::now($timezone)->toDateString();
        Carbon::setTestNow(Carbon::parse($date . ' 07:15:00', $timezone));

        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8, 'general_ward');
        $surgical = $this->createDepartment('Surgical Ward', 7, 'surgical_ward', 'Surgical/OT');
        $this->createStandard($user, 'general_ward', 'day', 4, 1, $date);
        $this->createStandard($user, 'surgical_ward', 'day', 3, 1, $date);

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'census_date' => $date,
            'shift_code' => 'day',
            'departments' => [
                $medical->id => ['patient_count' => 4, 'on_duty_staff' => 3],
                $surgical->id => ['patient_count' => 9, 'on_duty_staff' => 1],
            ],
        ]);

        $recommendation = MobilizationRecommendation::query()->firstOrFail();
        OperationalRound::query()->firstOrFail()->update([
            'observed_at' => Carbon::now(config('app.timezone', 'UTC'))->subMinutes(31),
        ]);

        $this->from(route('operations.index'))
            ->post(route('operations.recommendations.approve', $recommendation))
            ->assertRedirect(route('operations.index'))
            ->assertSessionHasErrors('recommendation');

        $this->assertDatabaseHas('mobilization_recommendations', [
            'id' => $recommendation->id,
            'status' => MobilizationRecommendation::STATUS_PENDING,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createDepartment(string $name, int $currentStaff, string $unitType, string $departmentType = 'Inpatient - Standard'): Department
    {
        return Department::create([
            'name' => $name,
            'type' => $departmentType,
            'operational_unit_type' => $unitType,
            'current_staff' => $currentStaff,
            'working_days_per_year' => 260,
            'public_holidays' => 13,
            'annual_leave_days' => 18,
            'sick_leave_days' => 12,
            'training_days' => 5,
            'working_hours_per_day' => 8,
        ]);
    }

    private function createStandard(
        User $approver,
        string $unitType,
        string $shiftCode,
        float $patientsPerNurse,
        int $minimumNurses,
        string $date
    ): OperationalStaffingStandard {
        return OperationalStaffingStandard::create([
            'unit_type' => $unitType,
            'shift_code' => $shiftCode,
            'patients_per_nurse' => $patientsPerNurse,
            'minimum_nurses_per_shift' => $minimumNurses,
            'source_name' => 'Test facility staffing standard',
            'source_version' => 'Test version',
            'effective_from' => $date,
            'is_active' => true,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }
}
