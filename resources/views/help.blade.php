<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-bold text-xl text-gray-800">WHO WISN Methodology Guide</h2>
                <p class="text-sm text-gray-500 mt-0.5">Understanding the Workload Indicators of Staffing Need approach</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 font-medium text-sm inline-flex items-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Hero intro -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg shadow-blue-500/15">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2">What is WISN?</h3>
                        <p class="text-sm text-blue-100 leading-relaxed">
                            The <strong class="text-white">Workload Indicators of Staffing Need (WISN)</strong> methodology was developed by the World Health Organization (WHO)
                            to provide an evidence-based approach to calculating nursing and health workforce requirements.
                            Unlike population-to-staff ratios, WISN grounds calculations in the <em>actual workload</em> performed by health workers.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4-Step Process -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                        </svg>
                        The 4-Step Process in This Tool
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-sm shadow-blue-200">1</div>
                        <div>
                            <p class="font-semibold text-gray-800">Create a Department</p>
                            <p class="text-sm text-gray-600 mt-1">Define the ward name, type, current nurse count, and Available Working Time (AWT) breakdown. AWT = the hours a nurse is available for productive work after subtracting holidays, leave, and training days.</p>
                            <p class="text-xs text-blue-600 mt-2 font-mono bg-blue-50 inline-block px-2 py-1 rounded">AWT = (Working Days − Public Holidays − Annual Leave − Sick Leave − Training Days) × Hours per Day</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-sm shadow-blue-200">2</div>
                        <div>
                            <p class="font-semibold text-gray-800">Add Health Service Activities</p>
                            <p class="text-sm text-gray-600 mt-1">List every direct patient care task performed in the department. For each, enter the time standard (hours per occurrence) and the annual volume (total occurrences per year). The tool computes <strong>Standard Workload = AWT ÷ Time Standard</strong>, then <strong>Required Staff = Annual Volume ÷ Standard Workload</strong>.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-sm shadow-blue-200">3</div>
                        <div>
                            <p class="font-semibold text-gray-800">Add Support &amp; Additional Activities</p>
                            <p class="text-sm text-gray-600 mt-1"><strong>Support activities</strong> are recurring shift duties that consume nursing time but are not direct patient care (e.g., shift handover, ward rounds, documentation). Enter only the time standard in hours — no annual volume. The tool calculates the <strong>Category Allowance Factor (CAF) = 1 ÷ (1 − total support fraction)</strong>, which adjusts the clinical FTE requirement upward.</p>
                            <p class="text-sm text-gray-600 mt-2"><strong>Additional activities</strong> are cross-department duties like teaching junior nurses. Enter both time standard and annual volume. These are converted to FTE as the <strong>Additional Allowance Factor (AAF)</strong> and added directly to the required staff total.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-sm shadow-emerald-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">View Results on the Dashboard</p>
                            <p class="text-sm text-gray-600 mt-1">The dashboard shows each department's <strong>WISN Ratio = Current Staff ÷ Required Staff</strong>. A ratio below 1.0 indicates a shortage; above 1.0 indicates a surplus. Download a PDF report for hospital administration boards.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WISN Ratio Interpretation -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        WISN Ratio Interpretation
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">WISN Ratio</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Recommended Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr class="hover:bg-red-50/30 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-red-600 font-mono">&lt; 0.90</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-50 text-red-700 border border-red-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Critical Shortage
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">Urgent recruitment required. Patient safety risk. Consider task redistribution immediately.</td>
                            </tr>
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-amber-600 font-mono">0.90 – 0.99</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Borderline
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">Staff under pressure. Short-term recruitment or task redistribution recommended.</td>
                            </tr>
                            <tr class="hover:bg-emerald-50/30 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-emerald-600 font-mono">1.00</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Adequate
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">Staffing precisely meets calculated requirements. Monitor as workload changes.</td>
                            </tr>
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-blue-600 font-mono">&gt; 1.00</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Surplus
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">More staff than current workload requires. Consider redeployment to understaffed departments.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Key Formulas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" />
                        </svg>
                        Key Formulas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3 text-sm font-mono bg-gray-50 rounded-xl p-5 text-gray-700 border border-gray-100">
                        <p><span class="text-blue-600 font-sans font-semibold">AWT</span> = (Working Days − Public Holidays − Annual Leave − Sick Leave − Training Days) × Hours/Day</p>
                        <p><span class="text-blue-600 font-sans font-semibold">Standard Workload</span> = AWT ÷ Activity Time Standard</p>
                        <p><span class="text-blue-600 font-sans font-semibold">Health Service FTE</span> = Σ (Annual Volume ÷ Standard Workload) for all Health Service activities</p>
                        <p><span class="text-blue-600 font-sans font-semibold">CAF</span> = 1 ÷ (1 − Σ Support Activity Fractions)</p>
                        <p><span class="text-blue-600 font-sans font-semibold">AAF FTE</span> = Σ (Annual Volume × Time Standard) ÷ AWT for Additional activities</p>
                        <p><span class="text-blue-600 font-sans font-semibold">Required Staff</span> = (Health Service FTE × CAF) + AAF FTE</p>
                        <p><span class="text-blue-600 font-sans font-semibold">WISN Ratio</span> = Current Staff ÷ Required Staff</p>
                    </div>
                </div>
            </div>

            <!-- Reference -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <p class="text-sm text-gray-500">
                    <strong>Reference:</strong> World Health Organization. (2023). <em>Workload Indicators of Staffing Need (WISN): User&apos;s Manual</em> (updated ed.). WHO, Geneva.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
