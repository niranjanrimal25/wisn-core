<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\MobilizationRecommendation;
use App\Models\OperationalRound;
use App\Models\User;
use App\Services\WisnCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalStaffingTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_snapshot_generates_count_recommendations_without_changing_wisn_data(): void
    {
        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8);
        $surgical = $this->createDepartment('Surgical Ward', 7);

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
                'departments' => [
                    $medical->id => [
                        'patient_count' => 4,
                        'high_acuity_patient_count' => 1,
                        'on_duty_staff' => 6,
                        'required_on_duty_staff' => 4,
                        'notes' => 'Lower census this shift',
                    ],
                    $surgical->id => [
                        'patient_count' => 18,
                        'high_acuity_patient_count' => 5,
                        'on_duty_staff' => 3,
                        'required_on_duty_staff' => 5,
                        'notes' => null,
                    ],
                ],
                'round_notes' => 'Day shift operational review',
            ])
            ->assertRedirect(route('operations.index'))
            ->assertSessionHas('success');

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

    public function test_approval_is_audited_and_does_not_change_department_headcounts(): void
    {
        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8);
        $surgical = $this->createDepartment('Surgical Ward', 7);

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'departments' => [
                $medical->id => [
                    'patient_count' => 4,
                    'high_acuity_patient_count' => 0,
                    'on_duty_staff' => 6,
                    'required_on_duty_staff' => 4,
                ],
                $surgical->id => [
                    'patient_count' => 18,
                    'high_acuity_patient_count' => 3,
                    'on_duty_staff' => 3,
                    'required_on_duty_staff' => 5,
                ],
            ],
        ])->assertRedirect(route('operations.index'));

        $recommendation = MobilizationRecommendation::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('operations.recommendations.approve', $recommendation))
            ->assertRedirect(route('operations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mobilization_recommendations', [
            'id' => $recommendation->id,
            'status' => MobilizationRecommendation::STATUS_APPROVED,
            'reviewed_by' => $user->id,
        ]);
        $this->assertSame(8, $medical->fresh()->current_staff);
        $this->assertSame(7, $surgical->fresh()->current_staff);
    }

    public function test_operations_page_explains_the_manual_shift_snapshot(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $this->createDepartment('Medical Ward', 8);

        $this->actingAs($user)
            ->get(route('operations.index'))
            ->assertOk()
            ->assertSee('Shift Operations')
            ->assertSee('Manual snapshot')
            ->assertSee('Required nurses now');
    }

    public function test_high_acuity_count_cannot_exceed_total_census(): void
    {
        $user = User::factory()->create();
        $department = $this->createDepartment('Medical Ward', 8);

        $this->actingAs($user)
            ->from(route('operations.index'))
            ->post(route('operations.snapshots.store'), [
                'departments' => [
                    $department->id => [
                        'patient_count' => 3,
                        'high_acuity_patient_count' => 4,
                        'on_duty_staff' => 4,
                        'required_on_duty_staff' => 4,
                    ],
                ],
            ])
            ->assertRedirect(route('operations.index'))
            ->assertSessionHasErrors("departments.{$department->id}.high_acuity_patient_count");

        $this->assertDatabaseCount('operational_rounds', 0);
    }

    public function test_a_new_snapshot_supersedes_unreviewed_recommendations(): void
    {
        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8);
        $surgical = $this->createDepartment('Surgical Ward', 7);
        $payload = [
            'departments' => [
                $medical->id => [
                    'patient_count' => 4,
                    'high_acuity_patient_count' => 0,
                    'on_duty_staff' => 6,
                    'required_on_duty_staff' => 4,
                ],
                $surgical->id => [
                    'patient_count' => 18,
                    'high_acuity_patient_count' => 2,
                    'on_duty_staff' => 3,
                    'required_on_duty_staff' => 5,
                ],
            ],
        ];

        $this->actingAs($user)->post(route('operations.snapshots.store'), $payload);
        $firstRecommendation = MobilizationRecommendation::query()->firstOrFail();

        $this->actingAs($user)->post(route('operations.snapshots.store'), $payload);

        $this->assertDatabaseHas('mobilization_recommendations', [
            'id' => $firstRecommendation->id,
            'status' => MobilizationRecommendation::STATUS_SUPERSEDED,
        ]);
        $this->assertSame(1, MobilizationRecommendation::query()
            ->where('status', MobilizationRecommendation::STATUS_SUPERSEDED)
            ->count());
    }

    public function test_department_with_operational_history_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8);
        $surgical = $this->createDepartment('Surgical Ward', 7);

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'departments' => [
                $medical->id => [
                    'patient_count' => 4,
                    'high_acuity_patient_count' => 0,
                    'on_duty_staff' => 6,
                    'required_on_duty_staff' => 4,
                ],
                $surgical->id => [
                    'patient_count' => 18,
                    'high_acuity_patient_count' => 3,
                    'on_duty_staff' => 3,
                    'required_on_duty_staff' => 5,
                ],
            ],
        ]);

        $this->actingAs($user)
            ->delete(route('departments.destroy', $medical))
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $medical->id]);
        $this->assertDatabaseHas('department_operational_snapshots', [
            'department_id' => $medical->id,
        ]);
    }

    public function test_stale_snapshot_recommendations_cannot_be_approved(): void
    {
        config(['operations.stale_after_minutes' => 30]);

        $user = User::factory()->create();
        $medical = $this->createDepartment('Medical Ward', 8);
        $surgical = $this->createDepartment('Surgical Ward', 7);

        $this->actingAs($user)->post(route('operations.snapshots.store'), [
            'departments' => [
                $medical->id => [
                    'patient_count' => 4,
                    'high_acuity_patient_count' => 0,
                    'on_duty_staff' => 6,
                    'required_on_duty_staff' => 4,
                ],
                $surgical->id => [
                    'patient_count' => 18,
                    'high_acuity_patient_count' => 3,
                    'on_duty_staff' => 3,
                    'required_on_duty_staff' => 5,
                ],
            ],
        ]);

        $recommendation = MobilizationRecommendation::query()->firstOrFail();
        OperationalRound::query()->firstOrFail()->update([
            'captured_at' => now()->subMinutes(31),
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

    private function createDepartment(string $name, int $currentStaff): Department
    {
        return Department::create([
            'name' => $name,
            'type' => 'Inpatient - Standard',
            'current_staff' => $currentStaff,
            'working_days_per_year' => 260,
            'public_holidays' => 13,
            'annual_leave_days' => 18,
            'sick_leave_days' => 12,
            'training_days' => 5,
            'working_hours_per_day' => 8,
        ]);
    }
}
