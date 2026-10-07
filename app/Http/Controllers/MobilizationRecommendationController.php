<?php

namespace App\Http\Controllers;

use App\Models\MobilizationRecommendation;
use App\Services\OperationalStaffingService;
use Illuminate\Http\Request;

class MobilizationRecommendationController extends Controller
{
    public function approve(
        Request $request,
        MobilizationRecommendation $recommendation,
        OperationalStaffingService $staffingService
    ) {
        $validated = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $staffingService->approve(
            $recommendation,
            $request->user(),
            $validated['decision_note'] ?? null,
        );

        return redirect()->route('operations.index', $this->roundLocation($recommendation))
            ->with('success', 'Recommendation approval recorded. This aggregate approval does not assign a named nurse or update the roster.');
    }

    public function decline(
        Request $request,
        MobilizationRecommendation $recommendation,
        OperationalStaffingService $staffingService
    ) {
        $validated = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $staffingService->decline(
            $recommendation,
            $request->user(),
            $validated['decision_note'] ?? null,
        );

        return redirect()->route('operations.index', $this->roundLocation($recommendation))
            ->with('success', 'Recommendation declined. The operational snapshot and WISN results were not changed.');
    }

    private function roundLocation(MobilizationRecommendation $recommendation): array
    {
        $round = $recommendation->operationalRound;
        $location = [];

        if ($round?->census_date) {
            $location['date'] = $round->census_date->toDateString();
        }
        if ($round?->shift_code) {
            $location['shift'] = $round->shift_code;
        }

        return $location;
    }
}
