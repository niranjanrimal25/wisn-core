<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Operational Target Ratios</h2>
                <p class="mt-1 text-sm text-gray-500">Configure source-backed targets for inpatient units and day/night shifts.</p>
            </div>
            <a href="{{ route('operations.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Back to Operations</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <p class="font-semibold">Please check the following:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 sm:p-5">
                <p class="font-bold">No universal ratio is preloaded.</p>
                <p class="mt-1">Enter only a ratio approved for your hospital and unit. Save its source, version, effective dates, and approval. Nepal MSS examples are candidate references, not automatically applicable rules. Midnight census does not calculate required nurses.</p>
                <p class="mt-2">A day/night requirement is calculated as <span class="font-mono font-semibold">max(minimum nurses, ceil(observed patients ÷ patients per nurse))</span>. Without a currently active standard that covers the selected unit, shift, and date, the app leaves required staffing blank and does not make a mobilization suggestion for that unit.</p>
            </div>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                    <h3 class="text-lg font-bold text-gray-900">Add a target standard</h3>
                    <p class="mt-1 text-sm text-gray-500">Activating a new version closes an overlapping earlier period the day before it starts. Prior standards and snapshots are retained for history.</p>
                </div>
                <form action="{{ route('operations.standards.store') }}" method="POST" class="space-y-5 p-5 sm:p-6">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="unit_type" class="mb-1 block text-sm font-semibold text-gray-700">Inpatient unit type <span class="text-rose-500">*</span></label>
                            <select id="unit_type" name="unit_type" required class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Choose a unit type</option>
                                @foreach($unitTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('unit_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="shift_code" class="mb-1 block text-sm font-semibold text-gray-700">Shift <span class="text-rose-500">*</span></label>
                            <select id="shift_code" name="shift_code" required class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Choose a handover</option>
                                @foreach($shifts as $value => $label)
                                    <option value="{{ $value }}" {{ old('shift_code') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="patients_per_nurse" class="mb-1 block text-sm font-semibold text-gray-700">Patients per nurse <span class="text-rose-500">*</span></label>
                            <input id="patients_per_nurse" name="patients_per_nurse" type="number" min="0.01" max="9999" step="0.01" required value="{{ old('patients_per_nurse') }}" placeholder="Enter approved value" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="minimum_nurses_per_shift" class="mb-1 block text-sm font-semibold text-gray-700">Minimum nurses per shift</label>
                            <input id="minimum_nurses_per_shift" name="minimum_nurses_per_shift" type="number" min="0" max="1000" required value="{{ old('minimum_nurses_per_shift', 0) }}" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Use the locally approved minimum; 0 means none is imposed by this entry.</p>
                        </div>
                        <div>
                            <label for="source_name" class="mb-1 block text-sm font-semibold text-gray-700">Standard / source name <span class="text-rose-500">*</span></label>
                            <input id="source_name" name="source_name" type="text" maxlength="255" required value="{{ old('source_name') }}" placeholder="Hospital-approved nursing standard" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="source_version" class="mb-1 block text-sm font-semibold text-gray-700">Version / edition</label>
                            <input id="source_version" name="source_version" type="text" maxlength="100" value="{{ old('source_version') }}" placeholder="e.g. 2026 revision" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="source_url" class="mb-1 block text-sm font-semibold text-gray-700">Source link <span class="font-normal text-gray-400">(optional)</span></label>
                            <input id="source_url" name="source_url" type="url" maxlength="2048" value="{{ old('source_url') }}" placeholder="https://..." class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="effective_from" class="mb-1 block text-sm font-semibold text-gray-700">Effective from <span class="text-rose-500">*</span></label>
                            <input id="effective_from" name="effective_from" type="date" required value="{{ old('effective_from') }}" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="effective_to" class="mb-1 block text-sm font-semibold text-gray-700">Effective to <span class="font-normal text-gray-400">(optional)</span></label>
                            <input id="effective_to" name="effective_to" type="date" value="{{ old('effective_to') }}" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex items-center gap-3 rounded-lg border border-gray-200 p-3">
                            <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-700 shadow-sm focus:ring-blue-500">
                            <label for="is_active" class="text-sm font-semibold text-gray-700">Activate and approve this standard</label>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="max-w-3xl text-xs text-gray-500">Only an administrator can activate a ratio. Activation means the facility has approved it for calculations; document the approval in your source or local governance process.</p>
                        <button type="submit" class="rounded-lg bg-indigo-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-800">Save target standard</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                    <h3 class="text-lg font-bold text-gray-900">Saved standards and approval status</h3>
                    <p class="mt-1 text-sm text-gray-500">Inactive or expired targets are not used for new calculations. Existing snapshots keep their recorded target for historical review.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3">Unit / shift</th>
                                <th class="px-5 py-3">Target</th>
                                <th class="px-5 py-3">Source</th>
                                <th class="px-5 py-3">Effective period</th>
                                <th class="px-5 py-3">Approval</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($standards as $standard)
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-gray-900">{{ $unitTypes[$standard->unit_type] ?? $standard->unit_type }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $shifts[$standard->shift_code] ?? $standard->shift_code }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-800">
                                        <p class="font-semibold">1 nurse per {{ rtrim(rtrim(number_format((float) $standard->patients_per_nurse, 2), '0'), '.') }} patients</p>
                                        <p class="mt-1 text-xs text-gray-500">Minimum: {{ $standard->minimum_nurses_per_shift }}</p>
                                    </td>
                                    <td class="max-w-sm px-5 py-4 text-sm text-gray-700">
                                        <p class="font-medium">{{ $standard->source_name }}</p>
                                        @if($standard->source_version)<p class="mt-1 text-xs text-gray-500">{{ $standard->source_version }}</p>@endif
                                        @if($standard->source_url)<a href="{{ $standard->source_url }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block break-all text-xs text-blue-700 underline">Open source</a>@endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        {{ $standard->effective_from->format('j M Y') }}<br>
                                        <span class="text-xs text-gray-500">to {{ $standard->effective_to?->format('j M Y') ?? 'no end date' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        {{ $standard->approver?->name ?? 'Not active / not approved' }}
                                        @if($standard->approved_at)<p class="mt-1 text-xs text-gray-500">{{ $standard->approved_at->timezone(config('operations.timezone'))->format('j M Y, g:i A') }}</p>@endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $standard->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $standard->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <form action="{{ route('operations.standards.status', $standard) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $standard->is_active ? 0 : 1 }}">
                                            <button type="submit" class="rounded-lg border px-3 py-2 text-xs font-bold {{ $standard->is_active ? 'border-rose-200 text-rose-700 hover:bg-rose-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">{{ $standard->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No staffing standards are configured. No required-nurse calculations will be made until an approved target is added.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
