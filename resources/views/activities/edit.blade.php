<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800">Edit Activity</h2>
                <p class="text-sm text-gray-500 mt-0.5">Update activity in <span class="text-blue-600 font-medium">{{ $department->name }}</span></p>
            </div>
            <a href="{{ route('activities.index', $department->id) }}" class="text-gray-500 hover:text-gray-700 font-medium text-sm inline-flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Activities
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        Editing: {{ $activity->activity_name }}
                    </h3>
                </div>
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

                    <form action="{{ route('activities.update', [$department->id, $activity->id]) }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Activity Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="activity_name" value="{{ old('activity_name', $activity->activity_name) }}" required
                                class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="e.g., Medication administration">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Activity Type <span class="text-red-400">*</span>
                            </label>
                            <select name="activity_type" id="activity_type" required
                                class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="health_service" {{ old('activity_type', $activity->activity_type) == 'health_service' ? 'selected' : '' }}>Health Service (Direct Care)</option>
                                <option value="support" {{ old('activity_type', $activity->activity_type) == 'support' ? 'selected' : '' }}>Support (Admin / Meetings / Handover)</option>
                                <option value="additional" {{ old('activity_type', $activity->activity_type) == 'additional' ? 'selected' : '' }}>Additional (Teaching / Cross-department)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Time Standard (Hours per Occurrence) <span class="text-red-400">*</span>
                            </label>
                            <input type="number" step="0.01" name="time_standard_hours"
                                value="{{ old('time_standard_hours', $activity->time_standard_hours) }}" required
                                class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="e.g., 0.5 for 30 min">
                            <p class="text-xs text-gray-400 mt-1">How long this activity takes each time it is performed</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Annual Volume
                            </label>
                            <input type="number" name="annual_volume"
                                value="{{ old('annual_volume', $activity->annual_volume) }}"
                                class="w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="e.g., 8760 for hourly monitoring in ICU">
                            <p class="text-xs text-gray-400 mt-1">Required for Health Service and Additional types. Leave blank for Support activities.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('activities.index', $department->id) }}"
                                class="text-gray-600 hover:text-gray-900 font-medium text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-sm text-sm transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Update Activity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
