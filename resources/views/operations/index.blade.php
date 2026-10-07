<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Inpatient Census &amp; Shift Staffing</h2>
                <p class="mt-1 text-sm text-gray-500">Point-in-time nursing-unit coverage. Annual WISN planning remains separate.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()->is_admin)
                    <a href="{{ route('operations.standards.index') }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Configure target ratios</a>
                @endif
                <a href="{{ route('departments.index') }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Manage units</a>
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800">Annual WISN</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <p class="font-semibold">Please check the following:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 sm:p-5">
                <div class="flex gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700" aria-hidden="true">i</div>
                    <div class="space-y-1 text-sm text-sky-950">
                        <p class="font-semibold">Record observed counts at three fixed times: 12:00 AM, 7:00 AM, and 7:00 PM.</p>
                        <p>Enter the patient census actually observed at that handover; this version does not infer census from admissions, discharges, or transfers. At 7 AM and 7 PM, also enter nurses who actually came on duty. Midnight records census only.</p>
                        <p>Operations is manual entry, not a live EHR or staff roster. Do not enter patient names. Emergency includes admitted or observation-bed patients only; walk-in visits and OPD are excluded. No staffing requirement is calculated until an approved, effective unit-and-shift target is configured.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end sm:justify-between">
                <form action="{{ route('operations.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="shift" value="{{ $selectedShift }}">
                    <div>
                        <label for="date" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Daily census date</label>
                        <input type="date" id="date" name="date" value="{{ $selectedDate }}" max="{{ $today }}" required class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">Open day</button>
                </form>
                <div class="flex items-center gap-2 text-sm">
                    <a href="{{ route('operations.index', ['date' => $previousDate, 'shift' => $selectedShift]) }}" class="rounded-lg border border-gray-200 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">← Previous day</a>
                    @if($nextDate <= $today)
                        <a href="{{ route('operations.index', ['date' => $nextDate, 'shift' => $selectedShift]) }}" class="rounded-lg border border-gray-200 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Next day →</a>
                    @endif
                    @if($selectedDate !== $today)
                        <a href="{{ route('operations.index', ['date' => $today, 'shift' => $selectedShift]) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 font-semibold text-blue-700 hover:bg-blue-100">Today</a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                @foreach(\App\Models\OperationalRound::SHIFTS as $shiftCode => $shiftLabel)
                    @php
                        $shiftRound = $roundsByShift->get($shiftCode);
                        $shiftCounts = $snapshotsByShift[$shiftCode];
                        $shiftPatients = $shiftCounts->sum('patient_count');
                        $active = $selectedShift === $shiftCode;
                    @endphp
                    <a href="{{ route('operations.index', ['date' => $selectedDate, 'shift' => $shiftCode]) }}" class="rounded-2xl border p-4 transition {{ $active ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide {{ $active ? 'text-blue-700' : 'text-gray-500' }}">{{ $shiftLabel }}</p>
                                <p class="mt-2 text-2xl font-bold text-gray-900">{{ $shiftRound ? number_format($shiftPatients) : '—' }} <span class="text-sm font-medium text-gray-500">patients</span></p>
                            </div>
                            @if($shiftRound)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Recorded</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">Not recorded</span>
                            @endif
                        </div>
                        @if($shiftRound)
                            <p class="mt-3 text-xs text-gray-500">Saved {{ $shiftRound->captured_at->timezone(config('operations.timezone'))->format('g:i A') }} by {{ $shiftRound->enteredBy?->name ?? 'former user' }} · revision {{ $shiftRound->id }}</p>
                        @else
                            <p class="mt-3 text-xs text-gray-500">Observed count at {{ $shiftCode === 'midnight' ? '12:00 AM' : ($shiftCode === 'day' ? '7:00 AM' : '7:00 PM') }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            @if($selectedRound)
                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Observed patients</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['patients']) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Nurses actually on duty</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $selectedShift === 'midnight' ? 'Not captured' : number_format($summary['on_duty']) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Calculated required nurses</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $selectedShift === 'midnight' || $summary['units_with_target'] === 0 ? '—' : number_format($summary['required']) }}</p>
                        @if($selectedShift !== 'midnight' && $summary['units_with_target'] > 0)
                            <p class="mt-1 text-xs text-gray-500">Configured for {{ $summary['units_with_target'] }} of {{ $summary['unit_count'] }} units</p>
                        @endif
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Units below target</p>
                        <p class="mt-2 text-2xl font-bold {{ $summary['units_below_target'] > 0 ? 'text-rose-700' : 'text-gray-900' }}">{{ $selectedShift === 'midnight' ? '—' : $summary['units_below_target'] }}</p>
                    </div>
                </div>
            @endif

            @php
                $missingStandards = $selectedShift === 'midnight'
                    ? collect()
                    : $departments->filter(fn($department) => !$currentStandards->has($department->operational_unit_type));
            @endphp
            @if($selectedShift !== 'midnight' && $missingStandards->isNotEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-semibold">{{ $missingStandards->count() }} unit(s) have no approved {{ $selectedShift }} target for this date.</p>
                    <p class="mt-1">Census and on-duty counts can still be saved, but no required-nurse estimate or mobilization suggestion will be made for those units.
                        @if(auth()->user()->is_admin)
                            <a class="font-semibold underline" href="{{ route('operations.standards.index') }}">Configure a source-backed target</a> after local approval.
                        @endif
                    </p>
                </div>
            @endif

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ \App\Models\OperationalRound::SHIFTS[$selectedShift] }} entry</h3>
                            <p class="mt-1 text-sm text-gray-500">Daily cycle: 12:00 AM through 11:59 PM on {{ \Carbon\Carbon::parse($selectedDate)->format('l, j F Y') }}. Counts are unit-level and point-in-time.</p>
                        </div>
                        @if($selectedRound)
                            <span class="text-xs text-gray-500">Latest saved version #{{ $selectedRound->id }} · {{ $selectedRound->captured_at->timezone(config('operations.timezone'))->format('j M Y, g:i A') }}</span>
                        @endif
                    </div>
                </div>

                @if($departments->isEmpty())
                    <div class="p-8 text-center">
                        <h4 class="font-semibold text-gray-900">No inpatient nursing units configured</h4>
                        <p class="mt-1 text-sm text-gray-500">Add General Ward, ICU, PICU, Surgical Ward, Emergency admitted/observation beds, or another inpatient unit first.</p>
                        <a href="{{ route('departments.create') }}" class="mt-4 inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Add a nursing unit</a>
                    </div>
                @else
                    <form action="{{ route('operations.snapshots.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="census_date" value="{{ $selectedDate }}">
                        <input type="hidden" name="shift_code" value="{{ $selectedShift }}">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                        <th class="px-5 py-3">Inpatient nursing unit</th>
                                        <th class="min-w-40 px-5 py-3">Observed patients</th>
                                        @if($selectedShift !== 'midnight')
                                            <th class="min-w-40 px-5 py-3">Nurses actually on duty</th>
                                            <th class="min-w-56 px-5 py-3">Approved target for this date</th>
                                        @else
                                            <th class="min-w-56 px-5 py-3">Midnight capture</th>
                                        @endif
                                        <th class="min-w-48 px-5 py-3">Saved calculation</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($departments as $department)
                                        @php
                                            $existing = $snapshotsByShift[$selectedShift]->get($department->id);
                                            $standard = $currentStandards->get($department->operational_unit_type);
                                            $unitTypeLabel = \App\Models\Department::OPERATIONAL_UNIT_TYPES[$department->operational_unit_type] ?? $department->operational_unit_type;
                                        @endphp
                                        <tr class="align-top">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-gray-900">{{ $department->name }}</p>
                                                <p class="mt-1 text-xs text-gray-500">{{ $unitTypeLabel }}</p>
                                                <input type="text" name="departments[{{ $department->id }}][notes]" value="{{ old('departments.'.$department->id.'.notes', $existing?->notes) }}" maxlength="1000" placeholder="Optional unit note" class="mt-2 w-full max-w-xs rounded-md border-gray-200 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </td>
                                            <td class="px-5 py-4">
                                                <label class="sr-only" for="patients-{{ $department->id }}">Observed patient count for {{ $department->name }}</label>
                                                <input id="patients-{{ $department->id }}" type="number" name="departments[{{ $department->id }}][patient_count]" min="0" max="100000" required value="{{ old('departments.'.$department->id.'.patient_count', $existing?->patient_count) }}" class="w-32 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <p class="mt-1 text-xs text-gray-500">Patients physically admitted / in beds at handover</p>
                                            </td>
                                            @if($selectedShift !== 'midnight')
                                                <td class="px-5 py-4">
                                                    <label class="sr-only" for="nurses-{{ $department->id }}">Nurses actually on duty in {{ $department->name }}</label>
                                                    <input id="nurses-{{ $department->id }}" type="number" name="departments[{{ $department->id }}][on_duty_staff]" min="0" max="65535" required value="{{ old('departments.'.$department->id.'.on_duty_staff', $existing?->on_duty_staff) }}" class="w-32 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <p class="mt-1 text-xs text-gray-500">People who came on duty, not annual headcount</p>
                                                </td>
                                                <td class="px-5 py-4">
                                                    @if($standard)
                                                        <p class="font-semibold text-gray-800">1 nurse per {{ rtrim(rtrim(number_format((float) $standard->patients_per_nurse, 2), '0'), '.') }} patients</p>
                                                        @if($standard->minimum_nurses_per_shift > 0)
                                                            <p class="mt-1 text-xs text-gray-600">Minimum {{ $standard->minimum_nurses_per_shift }} nurse(s) per shift</p>
                                                        @endif
                                                        <p class="mt-1 text-xs text-gray-500">{{ $standard->source_name }}{{ $standard->source_version ? ' · '.$standard->source_version : '' }}</p>
                                                        <p class="mt-1 text-xs text-gray-500">Effective {{ $standard->effective_from->format('j M Y') }}{{ $standard->effective_to ? ' to '.$standard->effective_to->format('j M Y') : '' }}</p>
                                                    @else
                                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">No approved target</span>
                                                        <p class="mt-1 text-xs text-gray-500">No required count or cross-unit suggestion will be calculated.</p>
                                                    @endif
                                                </td>
                                            @else
                                                <td class="px-5 py-4">
                                                    <p class="font-medium text-gray-700">Census only</p>
                                                    <p class="mt-1 text-xs text-gray-500">No midnight staffing target is applied.</p>
                                                </td>
                                            @endif
                                            <td class="px-5 py-4">
                                                @if($existing && $existing->calculated_required_staff !== null)
                                                    <p class="font-semibold text-gray-900">{{ $existing->calculated_required_staff }} nurses required</p>
                                                    @if($existing->staffingStandard)
                                                        <p class="mt-1 text-xs text-gray-600">Applied target: 1 nurse per {{ rtrim(rtrim(number_format((float) $existing->staffingStandard->patients_per_nurse, 2), '0'), '.') }} patients; minimum {{ $existing->staffingStandard->minimum_nurses_per_shift }}.</p>
                                                        <p class="mt-1 text-xs text-gray-500">{{ $existing->staffingStandard->source_name }}{{ $existing->staffingStandard->source_version ? ' · '.$existing->staffingStandard->source_version : '' }} · effective {{ $existing->staffingStandard->effective_from->format('j M Y') }}{{ $existing->staffingStandard->effective_to ? ' to '.$existing->staffingStandard->effective_to->format('j M Y') : '' }}</p>
                                                    @endif
                                                @elseif($existing && $selectedShift === 'midnight')
                                                    <p class="font-medium text-gray-700">Census saved</p>
                                                @else
                                                    <p class="text-sm text-gray-400">Not calculated yet</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="space-y-3 border-t border-gray-100 bg-gray-50 px-5 py-4 sm:px-6">
                            <div>
                                <label for="round_notes" class="mb-1 block text-sm font-semibold text-gray-700">Handover note <span class="font-normal text-gray-400">(optional)</span></label>
                                <textarea id="round_notes" name="round_notes" rows="2" maxlength="2000" placeholder="Record a brief unit-level note; do not include patient-identifying information." class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('round_notes', $selectedRound?->notes) }}</textarea>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="max-w-3xl text-xs text-gray-500">Saving a correction creates a new version of this date and handover. Older versions remain in the audit history; only the newest version is shown in the daily table.</p>
                                <button type="submit" class="shrink-0 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800">Save observed {{ $selectedShift === 'midnight' ? 'midnight census' : 'handover census & staffing' }}</button>
                            </div>
                        </div>
                    </form>
                @endif
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                    <h3 class="text-lg font-bold text-gray-900">Daily unit timeline</h3>
                    <p class="mt-1 text-sm text-gray-500">Latest saved observation at 12:00 AM, 7:00 AM, and 7:00 PM for each unit. Blank cells mean no count was recorded for that handover.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Nursing unit</th>
                                <th class="px-5 py-3">12:00 AM census</th>
                                <th class="px-5 py-3">7:00 AM census</th>
                                <th class="px-5 py-3">Day nurses / required</th>
                                <th class="px-5 py-3">7:00 PM census</th>
                                <th class="px-5 py-3">Night nurses / required</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($departments as $department)
                                @php
                                    $midnight = $snapshotsByShift['midnight']->get($department->id);
                                    $day = $snapshotsByShift['day']->get($department->id);
                                    $night = $snapshotsByShift['night']->get($department->id);
                                @endphp
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-gray-900">{{ $department->name }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ \App\Models\Department::OPERATIONAL_UNIT_TYPES[$department->operational_unit_type] ?? $department->operational_unit_type }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-800">{{ $midnight ? number_format($midnight->patient_count) : '—' }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-800">{{ $day ? number_format($day->patient_count) : '—' }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        @if($day)
                                            {{ $day->on_duty_staff ?? '—' }} / {{ $day->calculated_required_staff ?? 'Not configured' }}
                                            @if($day->calculated_required_staff !== null)
                                                <span class="mt-1 block text-xs {{ $day->on_duty_staff < $day->calculated_required_staff ? 'font-semibold text-rose-700' : 'text-emerald-700' }}">{{ $day->on_duty_staff < $day->calculated_required_staff ? 'Below target' : 'At / above target' }}</span>
                                            @endif
                                        @else — @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-800">{{ $night ? number_format($night->patient_count) : '—' }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        @if($night)
                                            {{ $night->on_duty_staff ?? '—' }} / {{ $night->calculated_required_staff ?? 'Not configured' }}
                                            @if($night->calculated_required_staff !== null)
                                                <span class="mt-1 block text-xs {{ $night->on_duty_staff < $night->calculated_required_staff ? 'font-semibold text-rose-700' : 'text-emerald-700' }}">{{ $night->on_duty_staff < $night->calculated_required_staff ? 'Below target' : 'At / above target' }}</span>
                                            @endif
                                        @else — @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">No inpatient nursing units are configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if($selectedShift !== 'midnight')
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Potential cross-unit mobilization</h3>
                            <p class="mt-1 text-sm text-gray-500">Aggregate possibilities only. A qualified human must check competencies, patient acuity, local rules, and safe coverage before action.</p>
                        </div>
                        <span class="rounded-full {{ $pendingCount ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }} px-3 py-1 text-xs font-bold">{{ $pendingCount }} pending review</span>
                    </div>
                    @if(!$selectedRound)
                        <div class="p-6 text-sm text-gray-500">Save this handover before cross-unit coverage can be reviewed.</div>
                    @elseif($recommendations->isEmpty())
                        <div class="p-6 text-sm text-gray-500">No potential staff move was calculated for this handover. This may mean units are within configured targets, or one or more units do not have a configured target.</div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($recommendations as $recommendation)
                                <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-bold text-gray-900">{{ $recommendation->staff_count }} nurse(s): {{ $recommendation->fromDepartment->name }} → {{ $recommendation->toDepartment->name }}</p>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $recommendation->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($recommendation->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($recommendation->status === 'declined' ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-700')) }}">{{ ucfirst($recommendation->status) }}</span>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-600">{{ $recommendation->rationale }}</p>
                                        @if($recommendation->reviewed_at)
                                            <p class="mt-2 text-xs text-gray-500">Reviewed by {{ $recommendation->reviewer?->name ?? 'former user' }} at {{ $recommendation->reviewed_at->timezone(config('operations.timezone'))->format('j M Y, g:i A') }}{{ $recommendation->decision_note ? ' — '.$recommendation->decision_note : '' }}</p>
                                        @endif
                                    </div>
                                    @if($recommendation->status === 'pending')
                                        @if($isStale || !$isComplete)
                                            <p class="max-w-sm rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800">Review is disabled: this handover is incomplete or older than {{ $freshnessMinutes }} minutes.</p>
                                        @else
                                            <div class="flex shrink-0 flex-wrap gap-2">
                                                <form action="{{ route('operations.recommendations.approve', $recommendation) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    <input type="text" name="decision_note" maxlength="1000" placeholder="Optional review note" class="w-44 rounded-lg border-gray-300 text-xs shadow-sm">
                                                    <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-800">Record approval</button>
                                                </form>
                                                <form action="{{ route('operations.recommendations.decline', $recommendation) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    <input type="text" name="decision_note" maxlength="1000" placeholder="Reason (optional)" class="w-40 rounded-lg border-gray-300 text-xs shadow-sm">
                                                    <button type="submit" class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Decline</button>
                                                </form>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="border-t border-gray-100 bg-gray-50 px-5 py-3 text-xs text-gray-500 sm:px-6">
                        Recording approval or decline documents a review only. It does not alter actual nurse counts, rosters, payroll, assignments, or annual WISN results.
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
