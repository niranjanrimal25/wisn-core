<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800">Manage Activities</h2>
                <p class="text-sm text-gray-500 mt-0.5">Workload components for <span class="text-blue-600 font-medium">{{ $department->name }}</span></p>
            </div>
            <a href="{{ route('departments.index') }}" class="text-gray-500 hover:text-gray-700 font-medium text-sm inline-flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Departments
            </a>
        </div>
    </x-slot>

    {{-- WISN SETUP STEPPER --}}
    @php
        $hasHealthService = $activities->where('activity_type', 'health_service')->count() > 0;
        $hasSupport       = $activities->where('activity_type', 'support')->count() > 0;
        $currentStep = !$hasHealthService ? 2 : (!$hasSupport ? 3 : 4);
    @endphp
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-6">
        <div class="bg-white border border-gray-100 rounded-xl shadow-sm p-4 flex items-center gap-3 sm:gap-4 text-sm overflow-x-auto">

            {{-- Step 1: always done --}}
            <div class="flex items-center gap-2 text-emerald-600 font-semibold whitespace-nowrap">
                <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                </span>
                <span class="hidden sm:inline">Department Created</span>
                <span class="sm:hidden">Step 1</span>
            </div>
            <div class="w-8 h-px bg-gray-200 flex-shrink-0"></div>

            {{-- Step 2: Health Service Activities --}}
            @if($hasHealthService)
                <div class="flex items-center gap-2 text-emerald-600 font-semibold whitespace-nowrap">
                    <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </span>
                    <span class="hidden sm:inline">Health Service Added</span>
                    <span class="sm:hidden">Step 2</span>
                </div>
            @else
                <div class="flex items-center gap-2 text-blue-600 font-semibold whitespace-nowrap">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-sm shadow-blue-200">2</span>
                    <span class="hidden sm:inline">Add Health Service</span>
                    <span class="sm:hidden">Step 2</span>
                </div>
            @endif
            <div class="w-8 h-px bg-gray-200 flex-shrink-0"></div>

            {{-- Step 3: Support Activities --}}
            @if($hasSupport)
                <div class="flex items-center gap-2 text-emerald-600 font-semibold whitespace-nowrap">
                    <span class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </span>
                    <span class="hidden sm:inline">Support Added</span>
                    <span class="sm:hidden">Step 3</span>
                </div>
            @elseif($hasHealthService)
                <div class="flex items-center gap-2 text-blue-600 font-semibold whitespace-nowrap">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-sm shadow-blue-200">3</span>
                    <span class="hidden sm:inline">Add Support</span>
                    <span class="sm:hidden">Step 3</span>
                </div>
            @else
                <div class="flex items-center gap-2 text-gray-400 whitespace-nowrap">
                    <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-bold">3</span>
                    <span class="hidden sm:inline">Add Support</span>
                    <span class="sm:hidden">Step 3</span>
                </div>
            @endif
            <div class="w-8 h-px bg-gray-200 flex-shrink-0"></div>

            {{-- Step 4: View Results --}}
            @if($hasHealthService && $hasSupport)
                <div class="flex items-center gap-2 text-blue-600 font-semibold whitespace-nowrap">
                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-sm shadow-blue-200">4</span>
                    <a href="{{ route('dashboard') }}" class="hover:underline inline-flex items-center gap-1">
                        <span class="hidden sm:inline">View Results</span>
                        <span class="sm:hidden">Step 4</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>
            @else
                <div class="flex items-center gap-2 text-gray-400 whitespace-nowrap">
                    <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-bold">4</span>
                    <span class="hidden sm:inline">View Results</span>
                    <span class="sm:hidden">Step 4</span>
                </div>
            @endif

        </div>
    </div>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ADD ACTIVITY FORM --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add New Activity
                        </h3>
                    </div>
                    <div class="p-6">

                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                <p class="text-xs text-red-700 font-medium">Please fix the errors below.</p>
                            </div>
                        @endif

                        <form action="{{ route('activities.store', $department->id) }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Activity Name <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="activity_name" value="{{ old('activity_name') }}" required
                                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    placeholder="e.g., Medication administration">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Activity Type <span class="text-red-400">*</span>
                                </label>
                                <select name="activity_type" id="activity_type" required
                                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="health_service" {{ old('activity_type') == 'health_service' ? 'selected' : '' }}>Health Service (Direct Care)</option>
                                    <option value="support" {{ old('activity_type') == 'support' ? 'selected' : '' }}>Support (Admin / Handover)</option>
                                    <option value="additional" {{ old('activity_type') == 'additional' ? 'selected' : '' }}>Additional (Teaching / Cross-dept)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Time Standard (Hours) <span class="text-red-400">*</span>
                                </label>
                                <input type="number" step="0.01" name="time_standard_hours"
                                    value="{{ old('time_standard_hours') }}" required
                                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    placeholder="e.g., 0.5 for 30 mins">
                                <p class="text-xs text-gray-400 mt-1">Duration of one occurrence in hours</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    Annual Volume
                                </label>
                                <input type="number" name="annual_volume" value="{{ old('annual_volume') }}"
                                    class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    placeholder="e.g., 8760">
                                <p class="text-xs text-gray-400 mt-1">Required for Health Service &amp; Additional types</p>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-sm text-sm transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Save Activity
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Activity Type Guide -->
                <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                        </svg>
                        Activity Types
                    </p>
                    <div class="space-y-2.5">
                        <div class="flex items-start gap-2">
                            <span class="mt-0.5 w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                            <p class="text-xs text-gray-600"><strong class="text-gray-800">Health Service:</strong> Direct patient care with annual volume (e.g., medication administration)</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="mt-0.5 w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <p class="text-xs text-gray-600"><strong class="text-gray-800">Support:</strong> Recurring shift duties, no volume needed (e.g., handover, documentation)</p>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="mt-0.5 w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                            <p class="text-xs text-gray-600"><strong class="text-gray-800">Additional:</strong> Cross-dept duties with volume (e.g., teaching junior nurses)</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIVITY LIST --}}
            <div class="lg:col-span-2">
                @if (session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4 flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            Current Workload Profile
                            <span class="ml-auto text-xs font-normal text-gray-400">{{ $activities->count() }} {{ Str::plural('activity', $activities->count()) }}</span>
                        </h3>
                    </div>

                    @if($activities->count() === 0)
                        <div class="text-center py-12 px-6">
                            <div class="mx-auto w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3.251l4.135 4.327 4.132-4.327M6.622 18.75a.75.75 0 000 1.5h11.256a.75.75 0 000-1.5H6.622z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">No activities yet</h4>
                            <p class="text-xs text-gray-400">Use the form on the left to add your first workload activity.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50/80">
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Activity</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Time (Hrs)</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Volume</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($activities as $activity)
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="px-5 py-3.5">
                                            <span class="text-sm font-medium text-gray-900">{{ $activity->activity_name }}</span>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            @if($activity->activity_type === 'health_service')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                    Health Service
                                                </span>
                                            @elseif($activity->activity_type === 'support')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Support
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 border border-purple-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                    Additional
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-sm font-semibold text-gray-700 font-mono">{{ $activity->time_standard_hours }}</td>
                                        <td class="px-5 py-3.5 text-sm text-gray-500 font-mono">{{ $activity->annual_volume ?? '—' }}</td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('activities.edit', [$department->id, $activity->id]) }}"
                                                    class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-blue-600 font-medium transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                    Edit
                                                </a>
                                                <span class="text-gray-200">|</span>
                                                <form action="{{ route('activities.destroy', [$department->id, $activity->id]) }}" method="POST"
                                                    onsubmit="return confirm('Delete this activity? This will affect WISN calculations.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 text-sm text-red-400 hover:text-red-600 font-medium transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
