<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-900">Shift Operations</h2>
                    <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-800">Manual snapshot</span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Shift-level coverage view, separate from the annual WISN staffing calculation.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h7.5v6.75h-7.5V4.5zm9 0h7.5v4.5h-7.5V4.5zm-9 9.75h7.5V19.5h-7.5v-5.25zm9-3h7.5v8.25h-7.5v-8.25z" />
                </svg>
                Annual WISN dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 sm:p-5">
                <div class="flex gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v3.75m0 3.75h.008v.008H12v-.008zM10.29 3.86L1.82 18.12A1.5 1.5 0 003.1 20.4h16.96a1.5 1.5 0 001.28-2.28L12.87 3.86a1.5 1.5 0 00-2.58 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-blue-950">Decision support only — not a live roster or automatic staffing rule.</p>
                        <p class="mt-1 text-sm leading-6 text-blue-900">Enter the current census and on-duty count for every department. Set “Required nurses now” using your facility’s approved shift-level clinical coverage policy; this is not calculated from the annual WISN result. Recommendations are aggregate counts only. Verify individual nurses’ competency, availability, and local rules before acting. Approval is logged here but does not assign a named nurse or update the roster. Do not enter patient names or identifiers in notes.</p>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
                    <p class="font-semibold text-red-900">Please check the following:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($departments->isEmpty())
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-500">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5.25 21V6.75L12 3l6.75 3.75V21M9 9h.008v.008H9V9zm0 3h.008v.008H9V12zm0 3h.008v.008H9V15zm6-6h.008v.008H15V9zm0 3h.008v.008H15V12zm0 3h.008v.008H15V15z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900">Add departments to begin</h3>
                    <p class="mx-auto mt-1 max-w-lg text-sm text-gray-500">Operational coverage snapshots are recorded for all configured departments. Add departments before entering shift-level data.</p>
                    <a href="{{ route('departments.create') }}" class="mt-5 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Add a department</a>
                </div>
            @else
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700">Capture current conditions</p>
                                <h3 class="mt-1 text-lg font-bold text-gray-900">New operational snapshot</h3>
                                <p class="mt-1 text-sm text-gray-500">A snapshot is timestamped when you save it. New data supersedes unreviewed recommendations from an older snapshot.</p>
                            </div>
                            @if ($latestRound)
                                <p class="text-xs text-gray-500">Last saved {{ $latestRound->captured_at->format('M j, Y, g:i A') }}</p>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('operations.snapshots.store') }}">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-[980px] w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Department</th>
                                        <th scope="col" class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Patients</th>
                                        <th scope="col" class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">High acuity</th>
                                        <th scope="col" class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">On duty</th>
                                        <th scope="col" class="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Required nurses now</th>
                                        <th scope="col" class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Note (optional)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($departments as $department)
                                        @php($snapshot = $snapshots->get($department->id))
                                        <tr class="align-top">
                                            <th scope="row" class="px-5 py-4 text-left">
                                                <span class="block text-sm font-semibold text-gray-900">{{ $department->name }}</span>
                                                <span class="mt-0.5 block text-xs text-gray-500">{{ $department->type ?: 'Department' }}</span>
                                            </th>
                                            <td class="px-3 py-4">
                                                <label class="sr-only" for="patients-{{ $department->id }}">Patient count for {{ $department->name }}</label>
                                                <input id="patients-{{ $department->id }}" name="departments[{{ $department->id }}][patient_count]" type="number" min="0" max="100000" step="1" required value="{{ old("departments.{$department->id}.patient_count", $snapshot?->patient_count ?? '') }}" class="w-24 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @error("departments.{$department->id}.patient_count")<p class="mt-1 max-w-28 text-xs text-red-600">{{ $message }}</p>@enderror
                                            </td>
                                            <td class="px-3 py-4">
                                                <label class="sr-only" for="acuity-{{ $department->id }}">High-acuity patient count for {{ $department->name }}</label>
                                                <input id="acuity-{{ $department->id }}" name="departments[{{ $department->id }}][high_acuity_patient_count]" type="number" min="0" max="100000" step="1" required value="{{ old("departments.{$department->id}.high_acuity_patient_count", $snapshot?->high_acuity_patient_count ?? '') }}" class="w-24 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @error("departments.{$department->id}.high_acuity_patient_count")<p class="mt-1 max-w-28 text-xs text-red-600">{{ $message }}</p>@enderror
                                            </td>
                                            <td class="px-3 py-4">
                                                <label class="sr-only" for="on-duty-{{ $department->id }}">Nurses currently on duty in {{ $department->name }}</label>
                                                <input id="on-duty-{{ $department->id }}" name="departments[{{ $department->id }}][on_duty_staff]" type="number" min="0" max="65535" step="1" required value="{{ old("departments.{$department->id}.on_duty_staff", $snapshot?->on_duty_staff ?? '') }}" class="w-24 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @error("departments.{$department->id}.on_duty_staff")<p class="mt-1 max-w-28 text-xs text-red-600">{{ $message }}</p>@enderror
                                            </td>
                                            <td class="px-3 py-4">
                                                <label class="sr-only" for="required-{{ $department->id }}">Facility-approved nurses required now in {{ $department->name }}</label>
                                                <input id="required-{{ $department->id }}" name="departments[{{ $department->id }}][required_on_duty_staff]" type="number" min="1" max="65535" step="1" required value="{{ old("departments.{$department->id}.required_on_duty_staff", $snapshot?->required_on_duty_staff ?? '') }}" class="w-28 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @error("departments.{$department->id}.required_on_duty_staff")<p class="mt-1 max-w-32 text-xs text-red-600">{{ $message }}</p>@enderror
                                            </td>
                                            <td class="px-5 py-4">
                                                <label class="sr-only" for="notes-{{ $department->id }}">Operational note for {{ $department->name }}</label>
                                                <input id="notes-{{ $department->id }}" name="departments[{{ $department->id }}][notes]" type="text" maxlength="1000" value="{{ old("departments.{$department->id}.notes", $snapshot?->notes ?? '') }}" placeholder="Staffing context only; no patient IDs" class="w-48 rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                @error("departments.{$department->id}.notes")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-gray-100 px-5 py-4 sm:px-6">
                            <label for="round-notes" class="block text-xs font-semibold text-gray-700">Snapshot note (optional; no patient identifiers)</label>
                            <textarea id="round-notes" name="round_notes" rows="2" maxlength="2000" placeholder="Non-identifiable staffing constraints or operational context only" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('round_notes') }}</textarea>
                            @error('round_notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <p class="max-w-3xl text-xs leading-5 text-gray-500">High-acuity census is recorded for context; no patient-to-nurse ratio is assumed by this prototype. Enter the required count from your locally approved shift policy.</p>
                            <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Save snapshot &amp; calculate suggestions
                            </button>
                        </div>
                    </form>
                </section>

                @if ($latestRound)
                    <section class="space-y-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-500">Latest operational round</p>
                                <h3 class="mt-1 text-lg font-bold text-gray-900">Coverage by department</h3>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                @if ($isStale)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 font-semibold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        @if (!$isComplete)
                                            Department list changed — refresh before approval
                                        @else
                                            Stale — refresh before approval
                                        @endif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Within freshness window
                                    </span>
                                @endif
                                <span class="text-gray-500">Saved {{ $latestRound->captured_at->diffForHumans() }} ({{ $latestRound->captured_at->format('M j, g:i A') }}){{ $latestRound->enteredBy ? ' · by ' . $latestRound->enteredBy->name : '' }}</span>
                            </div>
                        </div>
                        @if ($latestRound->notes)
                            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                                <span class="font-semibold">Round note:</span> {{ $latestRound->notes }}
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Patients</p>
                                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ number_format($summary['patients']) }}</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">High acuity</p>
                                <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ number_format($summary['high_acuity']) }}</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">On duty</p>
                                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ number_format($summary['on_duty']) }}</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Required now</p>
                                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ number_format($summary['required']) }}</p>
                            </div>
                            <div class="col-span-2 rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Below target</p>
                                <p class="mt-1 text-2xl font-extrabold {{ $summary['departments_below_target'] > 0 ? 'text-red-600' : 'text-emerald-700' }}">{{ $summary['departments_below_target'] }} <span class="text-sm font-semibold text-gray-500">units</span></p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="min-w-[760px] w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-500">Department</th>
                                            <th scope="col" class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Patients</th>
                                            <th scope="col" class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">High acuity</th>
                                            <th scope="col" class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">On duty</th>
                                            <th scope="col" class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Required now</th>
                                            <th scope="col" class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($departments as $department)
                                            @php($snapshot = $snapshots->get($department->id))
                                            <tr class="hover:bg-gray-50/80">
                                                <th scope="row" class="px-5 py-4 text-left">
                                                    <span class="block text-sm font-semibold text-gray-900">{{ $department->name }}</span>
                                                    <span class="mt-0.5 block text-xs text-gray-500">{{ $department->type ?: 'Department' }}</span>
                                                </th>
                                                @if ($snapshot)
                                                    @php($balance = $snapshot->on_duty_staff - $snapshot->required_on_duty_staff)
                                                    <td class="px-4 py-4 text-right text-sm text-gray-700">{{ number_format($snapshot->patient_count) }}</td>
                                                    <td class="px-4 py-4 text-right text-sm text-gray-700">{{ number_format($snapshot->high_acuity_patient_count) }}</td>
                                                    <td class="px-4 py-4 text-right text-sm font-semibold text-gray-900">{{ $snapshot->on_duty_staff }}</td>
                                                    <td class="px-4 py-4 text-right text-sm font-semibold text-gray-900">{{ $snapshot->required_on_duty_staff }}</td>
                                                    <td class="px-5 py-4 text-right">
                                                        @if ($balance < 0)
                                                            <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700">{{ $balance }} short</span>
                                                        @elseif ($balance > 0)
                                                            <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">+{{ $balance }} above target</span>
                                                        @else
                                                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">At target</span>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td colspan="5" class="px-5 py-4 text-sm font-medium text-red-700">Not included in this snapshot — record a complete new snapshot.</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">Human review required</p>
                                <h3 class="mt-1 text-lg font-bold text-gray-900">Potential mobilization</h3>
                            </div>
                            <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">{{ $pendingCount }} pending</span>
                        </div>

                        @if ($recommendations->isEmpty())
                            <div class="px-6 py-10 text-center">
                                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-900">No cross-department suggestion from this snapshot</p>
                                <p class="mt-1 text-sm text-gray-500">No unit is above its entered target while another is below it.</p>
                            </div>
                        @else
                            <div class="divide-y divide-gray-100">
                                @foreach ($recommendations as $recommendation)
                                    <div class="flex flex-col gap-4 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-sm font-bold text-gray-900">{{ $recommendation->fromDepartment->name }}</span>
                                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6-6m6 6l-6 6" />
                                                </svg>
                                                <span class="text-sm font-bold text-gray-900">{{ $recommendation->toDepartment->name }}</span>
                                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ $recommendation->staff_count }} {{ \Illuminate\Support\Str::plural('nurse', $recommendation->staff_count) }}</span>
                                                @if ($recommendation->status === 'pending')
                                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">Pending review</span>
                                                @elseif ($recommendation->status === 'approved')
                                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Approved count only</span>
                                                @elseif ($recommendation->status === 'declined')
                                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">Declined</span>
                                                @else
                                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">Superseded</span>
                                                @endif
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600">{{ $recommendation->rationale }}</p>
                                            @if ($recommendation->reviewer)
                                                <p class="mt-1 text-xs text-gray-500">Reviewed by {{ $recommendation->reviewer->name }}{{ $recommendation->reviewed_at ? ' · ' . $recommendation->reviewed_at->format('M j, g:i A') : '' }}</p>
                                            @endif
                                            @if ($recommendation->decision_note)
                                                <p class="mt-1 text-xs text-gray-500">Note: {{ $recommendation->decision_note }}</p>
                                            @endif
                                        </div>

                                        @if ($recommendation->status === 'pending')
                                            <div class="flex shrink-0 flex-wrap gap-2">
                                                @if (!$isStale)
                                                    <form method="POST" action="{{ route('operations.recommendations.approve', $recommendation) }}" onsubmit="return confirm('Record approval of this aggregate count recommendation? This will not assign a named nurse. Verify skill, availability, and local policy before acting.');">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Approve count</button>
                                                    </form>
                                                @else
                                                    <button type="button" disabled class="cursor-not-allowed rounded-lg bg-gray-200 px-3.5 py-2 text-xs font-bold text-gray-500" title="Record a fresh snapshot before approving">Snapshot stale</button>
                                                @endif
                                                <form method="POST" action="{{ route('operations.recommendations.decline', $recommendation) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50">Decline</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @else
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center">
                        <p class="text-sm font-semibold text-gray-900">No operational snapshot has been recorded yet.</p>
                        <p class="mt-1 text-sm text-gray-500">Enter the current shift figures above to see coverage gaps and aggregate suggestions.</p>
                    </div>
                @endif
            @endif

            <p class="text-center text-xs leading-5 text-gray-400">Snapshots are manually entered in this prototype. Recommendations are aggregate decision support, not clinical orders or a named-staff roster.</p>
        </div>
    </div>
</x-app-layout>
