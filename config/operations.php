<?php

return [
    // Operational recommendations should not be actioned from an old snapshot.
    // Override this value after agreeing a local data-freshness policy.
    'stale_after_minutes' => (int) env('OPERATIONS_STALE_AFTER_MINUTES', 30),
];
