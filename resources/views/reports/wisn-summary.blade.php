<!DOCTYPE html>
<html>
<head>
    <title>WISN Nursing Staffing Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #1e40af 0%, #4338ca 100%);
            color: white;
            border-radius: 6px;
        }
        .header h1 { color: #ffffff; margin: 0 0 4px 0; font-size: 18px; letter-spacing: 0.5px; }
        .header p { margin: 2px 0; color: #c7d2fe; font-size: 10px; }

        .metrics { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .metrics td {
            width: 33%;
            padding: 14px 10px;
            text-align: center;
            border: 1px solid #e0e7ff;
            background: #eef2ff;
            border-radius: 4px;
        }
        .metric-title { font-size: 9px; font-weight: bold; color: #6366f1; text-transform: uppercase; letter-spacing: 1px; }
        .metric-value { font-size: 24px; font-weight: bold; color: #1e1b4b; margin-top: 4px; }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 5px;
            margin: 18px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.dept-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.dept-table th {
            background: #1e40af;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.dept-table td { padding: 7px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; vertical-align: top; }
        table.dept-table tr:nth-child(even) td { background: #f8fafc; }

        .status-critical   { color: #dc2626; font-weight: bold; }
        .status-borderline { color: #ca8a04; font-weight: bold; }
        .status-adequate   { color: #16a34a; font-weight: bold; }
        .status-surplus    { color: #2563eb; font-weight: bold; }

        .guidance-box { background: #fefce8; border: 1px solid #fde68a; padding: 10px 14px; margin-bottom: 12px; border-radius: 4px; }
        .guidance-box h4 { color: #92400e; margin: 0 0 6px 0; font-size: 11px; }
        .guidance-box ul { margin: 0; padding-left: 16px; }
        .guidance-box li { margin-bottom: 3px; font-size: 10px; color: #555; }

        .breakdown-title { font-size: 9px; font-weight: bold; color: #555; margin-top: 6px; }
        .breakdown-row { font-size: 9px; color: #666; }
        .breakdown-total { font-size: 9px; font-weight: bold; color: #333; margin-top: 3px; }

        .formula-box { background: #f0f9ff; border: 1px solid #bae6fd; padding: 10px 14px; margin-bottom: 12px; border-radius: 4px; }
        .formula-box p { font-size: 9px; color: #555; margin-bottom: 2px; }
        .formula-box strong { color: #1e40af; }

        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; text-align: center; color: #888; }
    </style>
</head>
<body>

    <div class="header">
        <h1>WHO WISN Nursing Staffing Analysis</h1>
        <p>Workload Indicators of Staffing Need (WISN) Methodology &mdash; WHO 2010/2023</p>
        <p>Report Generated: {{ $date }}</p>
    </div>

    <table class="metrics">
        <tr>
            <td>
                <div class="metric-title">Current Facility Nurses</div>
                <div class="metric-value">{{ $totalCurrentStaff }}</div>
            </td>
            <td>
                <div class="metric-title">Required Facility Nurses</div>
                <div class="metric-value">{{ $totalRequiredStaff }}</div>
            </td>
            <td>
                <div class="metric-title">Overall WISN Ratio</div>
                <div class="metric-value {{ $facilityRatio < 0.9 ? 'status-critical' : ($facilityRatio < 1 ? 'status-borderline' : 'status-adequate') }}">
                    {{ $facilityRatio }}
                </div>
            </td>
        </tr>
    </table>

    <div class="guidance-box">
        <h4>How to Interpret the WISN Ratio</h4>
        <ul>
            <li><strong>Ratio &lt; 0.90 (Critical Shortage):</strong> Severe understaffing. Urgent action required &mdash; patient safety risk.</li>
            <li><strong>Ratio 0.90&ndash;0.99 (Borderline):</strong> Minor shortage. Staff are under pressure. Recruitment or task redistribution recommended.</li>
            <li><strong>Ratio = 1.00 (Adequate):</strong> Current staffing precisely meets calculated requirements.</li>
            <li><strong>Ratio &gt; 1.00 (Surplus):</strong> More staff than needed. Consider redeployment to understaffed departments.</li>
        </ul>
    </div>

    <div class="section-title">Department-Level Analysis</div>
    <table class="dept-table">
        <thead>
            <tr>
                <th>Department</th>
                <th>AWT (hrs)</th>
                <th>AWT Formula</th>
                <th>Current</th>
                <th>Required</th>
                <th>WISN Ratio</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departmentResults as $result)
            @php
                $gap = $result['current_staff'] - $result['total_required_staff'];
                $abd = $result['awt_breakdown'] ?? null;
            @endphp
            <tr>
                <td><strong>{{ $result['department_name'] }}</strong><br><span style="color:#888;font-size:9px">{{ $result['department_type'] ?? '' }}</span></td>
                <td>{{ $result['awt_hours'] }}</td>
                <td style="font-size:9px;color:#555">
                    @if($abd)
                        ({{ $abd['working_days'] }}&minus;{{ $abd['public_hols']+$abd['annual_leave']+$abd['sick_leave']+$abd['training'] }})&times;{{ $abd['hours_per_day'] }}h
                    @endif
                </td>
                <td>{{ $result['current_staff'] }}</td>
                <td><strong>{{ $result['total_required_staff'] }}</strong></td>
                <td><strong>{{ $result['wisn_ratio'] }}</strong></td>
                <td>
                    @if($result['status'] === 'critical')
                        <span class="status-critical">Critical</span>
                    @elseif($result['status'] === 'borderline')
                        <span class="status-borderline">Borderline</span>
                    @elseif($result['status'] === 'adequate')
                        <span class="status-adequate">Adequate</span>
                    @elseif($result['status'] === 'surplus')
                        <span class="status-surplus">Surplus</span>
                    @else
                        <span style="color:#888">No data</span>
                    @endif
                </td>
                <td style="font-size:9px">
                    @if($gap < 0)
                        Needs {{ abs(round($gap, 1)) }} more nurse(s)
                    @elseif($gap > 0)
                        {{ round($gap, 1) }} nurse(s) for redeployment
                    @else
                        Balanced
                    @endif
                </td>
            </tr>
            @if(!empty($result['breakdown']))
            <tr>
                <td colspan="8" style="padding: 4px 8px 10px 20px; background:#fafafa;">
                    <div class="breakdown-title">Activity Breakdown:</div>
                    @foreach($result['breakdown'] as $item)
                    <div class="breakdown-row">
                        &bull; {{ $item['activity'] }} ({{ $item['type'] }})
                        @if(isset($item['required_staff'])) &mdash; {{ $item['required_staff'] }} FTE @endif
                        @if(isset($item['allowance_percentage'])) &mdash; {{ $item['allowance_percentage'] }} of shift @endif
                        @if(isset($item['total_hours'])) &mdash; {{ $item['total_hours'] }} hrs/yr @endif
                    </div>
                    @endforeach
                    <div class="breakdown-total">
                        {{ $result['health_service_fte'] }} FTE &times; CAF {{ $result['support_allowance_multiplier'] }}
                        @if($result['aaf_fte'] > 0) + AAF {{ $result['aaf_fte'] }} @endif
                        = {{ $result['total_required_staff'] }} required
                    </div>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="formula-box">
        <div class="section-title" style="margin-top: 0;">Key Formulas Used</div>
        <p><strong>AWT</strong> = (Working Days &minus; Non-Working Days) &times; Hours/Day</p>
        <p><strong>Standard Workload</strong> = AWT &divide; Activity Time Standard</p>
        <p><strong>CAF</strong> = 1 &divide; (1 &minus; &Sigma; Support Activity Fractions)</p>
        <p><strong>Required Staff</strong> = (Health Service FTE &times; CAF) + AAF</p>
        <p><strong>WISN Ratio</strong> = Current Staff &divide; Required Staff</p>
    </div>

    <div class="footer">
        <p>Generated using WHO WISN methodology (WHO, 2010; updated 2023).</p>
        <p>This report was automatically generated by the WISN Staffing Tool for Nepalese hospitals.</p>
    </div>

</body>
</html>
