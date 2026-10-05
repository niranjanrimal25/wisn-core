<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800">
                    Facility Staffing Dashboard
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">WHO WISN analysis across all departments</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('help') }}" class="inline-flex items-center gap-1.5 text-gray-600 hover:text-gray-900 font-medium text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                    WISN Help
                </a>
                <a href="{{ route('report.generate') }}"
                    class="inline-flex items-center gap-1.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm text-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUMMARY CARDS --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Current Nurses</div>
                            <div class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalCurrentStaff }}</div>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Required Nurses</div>
                            <div class="text-3xl font-extrabold text-gray-900 mt-1">{{ $totalRequiredStaff }}</div>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Facility WISN Ratio</div>
                            <div class="text-3xl font-extrabold mt-2 {{ $facilityRatio < 0.9 ? 'text-red-600' : ($facilityRatio < 1 ? 'text-amber-600' : 'text-emerald-600') }}">
                                {{ $facilityRatio }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                @if($facilityRatio <= 0) No data yet
                                @elseif($facilityRatio < 0.9) Critical shortage
                                @elseif($facilityRatio < 1) Minor shortage
                                @elseif($facilityRatio == 1) Perfectly balanced
                                @else Surplus available
                                @endif
                            </div>
                        </div>
                        <div class="w-12 h-12 {{ $facilityRatio < 0.9 ? 'bg-red-100' : ($facilityRatio < 1 ? 'bg-amber-100' : 'bg-emerald-100') }} rounded-xl flex items-center justify-center">
                            @if($facilityRatio < 0.9)
                                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            @elseif($facilityRatio < 1)
                                <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- CHARTS ROW --}}
            @if(count($departmentResults) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">WISN Ratio by Department</h3>
                            <p class="text-xs text-gray-400">Target: 1.0 (higher is better, lower = shortage)</p>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Critical
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 ml-1"></span> Borderline
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ml-1"></span> OK
                        </div>
                    </div>
                    <canvas id="wisnRatioChart" height="200"></canvas>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Staffing Gap Analysis</h3>
                            <p class="text-xs text-gray-400">Current vs. required nurses per department</p>
                        </div>
                    </div>
                    <canvas id="staffingGapChart" height="200"></canvas>
                </div>
            </div>
            @endif

            {{-- DEPARTMENT TABLE --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Department Analysis</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Click a department name to manage its workload activities</p>
                        </div>
                        <a href="{{ route('departments.create') }}" class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Department
                        </a>
                    </div>
                </div>

                @if(count($departmentResults) === 0)
                    <div class="text-center py-16 px-6">
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-1">No departments yet</h3>
                        <p class="text-sm text-gray-500 mb-4">Add your first department to start calculating WISN staffing requirements.</p>
                        <a href="{{ route('departments.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-sm text-sm transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add First Department
                        </a>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">AWT (hrs)</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Current</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Required</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">WISN Ratio</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($departmentResults as $result)
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('activities.index', $result['department_id']) }}" class="font-semibold text-gray-900 hover:text-blue-600 transition-colors flex items-center gap-2">
                                            {{ $result['department_name'] }}
                                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-500 font-mono">{{ $result['awt_hours'] }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900">{{ $result['current_staff'] }}</td>
                                    <td class="px-5 py-4 text-sm font-bold text-gray-900">{{ $result['total_required_staff'] }}</td>
                                    <td class="px-5 py-4">
                                        <span class="text-sm font-bold {{ $result['wisn_ratio'] < 0.9 ? 'text-red-600' : ($result['wisn_ratio'] < 1 ? 'text-amber-600' : 'text-emerald-600') }}">
                                            {{ $result['wisn_ratio'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($result['status'] === 'critical')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                Critical
                                            </span>
                                        @elseif($result['status'] === 'borderline')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Borderline
                                            </span>
                                        @elseif($result['status'] === 'adequate')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Adequate
                                            </span>
                                        @elseif($result['status'] === 'surplus')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                Surplus
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-50 text-gray-500 border border-gray-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                No Data
                                            </span>
                                        @endif
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const results = @json($departmentResults);
        const labels = results.map(r => r.department_name);
        const ratios = results.map(r => r.wisn_ratio);
        const current = results.map(r => r.current_staff);
        const required = results.map(r => r.total_required_staff);

        const ratioColors = ratios.map(r =>
            r <= 0   ? 'rgba(156,163,175,0.8)' :
            r < 0.9  ? 'rgba(239,68,68,0.8)'   :
            r < 1.0  ? 'rgba(234,179,8,0.8)'   :
                       'rgba(34,197,94,0.8)'
        );

        const ratioBorders = ratioColors.map(c => c.replace('0.8', '1'));

        new Chart(document.getElementById('wisnRatioChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'WISN Ratio',
                    data: ratios,
                    backgroundColor: ratioColors,
                    borderColor: ratioBorders,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.6,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(2, ...ratios) + 0.2,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17,24,39,0.9)',
                        padding: 12,
                        titleFont: { size: 13 },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: ctx => 'WISN Ratio: ' + ctx.raw + ' (target: 1.0)'
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('staffingGapChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Current Staff',
                        data: current,
                        backgroundColor: 'rgba(59,130,246,0.8)',
                        borderColor: 'rgba(59,130,246,1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Required Staff',
                        data: required,
                        backgroundColor: 'rgba(168,85,247,0.8)',
                        borderColor: 'rgba(168,85,247,1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { size: 11 } } },
                    tooltip: {
                        backgroundColor: 'rgba(17,24,39,0.9)',
                        padding: 12,
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
