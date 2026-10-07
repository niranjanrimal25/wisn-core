<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800">Edit Department</h2>
                <p class="text-sm text-gray-500 mt-0.5">Update <span class="text-blue-600 font-medium">{{ $department->name }}</span> staffing parameters</p>
            </div>
            <a href="{{ route('departments.index') }}" class="text-gray-500 hover:text-gray-700 font-medium text-sm inline-flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Departments
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6">

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                            <div class="flex items-center gap-2 text-red-800 font-semibold text-sm mb-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                Please fix the following errors
                            </div>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('departments.update', $department->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Info Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xs font-bold">1</span>
                                Department Information
                            </h3>
                            <div class="space-y-4 ml-8">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">
                                        Department Name <span class="text-red-400">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" required
                                        class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="e.g., Intensive Care Unit (ICU)">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="type" class="block text-sm font-semibold text-gray-700 mb-1">
                                            Department Type <span class="text-red-400">*</span>
                                        </label>
                                        <select name="type" id="type" required
                                            class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            <option value="" disabled>Select type...</option>
                                            @foreach(['Inpatient - Standard','Inpatient - High Acuity','Outpatient','Emergency','Surgical/OT'] as $opt)
                                                <option value="{{ $opt }}" {{ old('type', $department->type) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="operational_unit_type" class="block text-sm font-semibold text-gray-700 mb-1">
                                            Operations Unit Classification <span class="text-red-400">*</span>
                                        </label>
                                        <select name="operational_unit_type" id="operational_unit_type" required
                                            class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            @foreach($operationalUnitTypes as $value => $label)
                                                <option value="{{ $value }}" {{ old('operational_unit_type', $department->operational_unit_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Outpatient is excluded from Operations; Emergency counts are admitted/observation-bed patients only.</p>
                                    </div>
                                    <div>
                                        <label for="current_staff" class="block text-sm font-semibold text-gray-700 mb-1">
                                            Current Staff Headcount (WISN) <span class="text-red-400">*</span>
                                        </label>
                                        <input type="number" name="current_staff" id="current_staff" value="{{ old('current_staff', $department->current_staff) }}" min="0" required
                                            class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            placeholder="e.g., 12">
                                        <p class="mt-1 text-xs text-gray-500">Planning headcount used by annual WISN; it is not the number actually on duty at a handover.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AWT Breakdown Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xs font-bold">2</span>
                                Available Working Time (AWT)
                            </h3>
                            <div class="ml-8">
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-800">AWT Formula</p>
                                            <p class="text-xs text-blue-600 mt-1 font-mono">AWT = (Working Days − Holidays − Leave − Sick − Training) × Hours/Day</p>
                                            <p class="text-xs text-blue-500 mt-1">Defaults follow Nepal Government Health Service standards (AWT ≈ 1,696 hrs/year).</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Live AWT Preview -->
                                <div id="awt-preview" class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 mb-4 text-sm font-semibold text-emerald-700 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>
                                    <span id="awt-preview-text">Computed AWT: {{ $department->available_working_time_hours }} hours/nurse/year</span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    @php
                                    $awtFields = [
                                        ['working_days_per_year', 'Working Days/Year', 'Days', 'e.g., 260', 1, 366],
                                        ['public_holidays', 'Public Holidays', 'Days', 'e.g., 13', 0, 50],
                                        ['annual_leave_days', 'Annual Leave', 'Days', 'e.g., 18', 0, 60],
                                        ['sick_leave_days', 'Sick Leave', 'Days', 'e.g., 12', 0, 60],
                                        ['training_days', 'Training Days', 'Days', 'e.g., 5', 0, 30],
                                        ['working_hours_per_day', 'Hours/Day', 'Hours', 'e.g., 8', 4, 24],
                                    ];
                                    @endphp
                                    @foreach($awtFields as [$field, $label, $unit, $placeholder, $min, $max])
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                                            {{ $label }} <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" name="{{ $field }}"
                                                value="{{ old($field, $department->$field) }}"
                                                min="{{ $min }}" max="{{ $max }}" required
                                                class="awt-field w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm pr-12"
                                                placeholder="{{ $placeholder }}">
                                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400">{{ $unit }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('departments.index') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-sm text-sm transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Update Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateAwtPreview() {
            const fields = ['working_days_per_year','public_holidays','annual_leave_days','sick_leave_days','training_days','working_hours_per_day'];
            const vals = fields.map(f => parseInt(document.querySelector('[name="' + f + '"]').value) || 0);
            const netDays = vals[0] - vals[1] - vals[2] - vals[3] - vals[4];
            const awt = netDays * vals[5];
            const el = document.getElementById('awt-preview');
            const text = document.getElementById('awt-preview-text');
            text.textContent = 'Computed AWT: (' + vals[0] + ' − ' + (vals[1]+vals[2]+vals[3]+vals[4]) + ') × ' + vals[5] + ' = ' + awt.toLocaleString() + ' hours/nurse/year';
            if (netDays <= 0) {
                el.className = 'bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4 text-sm font-semibold text-red-700 flex items-center gap-2';
            } else {
                el.className = 'bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 mb-4 text-sm font-semibold text-emerald-700 flex items-center gap-2';
            }
        }
        document.querySelectorAll('.awt-field').forEach(el => el.addEventListener('input', updateAwtPreview));
        updateAwtPreview();
    </script>
    @endpush
</x-app-layout>
