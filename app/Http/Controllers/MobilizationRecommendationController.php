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

        return redirect()->route('operations.index')
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

        return redirect()->route('operations.index')
            ->with('success', 'Recommendation declined. The operational snapshot and WISN results were not changed.');
    }
}
