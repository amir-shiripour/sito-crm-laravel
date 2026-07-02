@php
    $bookingSettings = \Modules\Booking\Entities\BookingSetting::current();
    $numberingSystem = $bookingSettings->cure_tooth_numbering_system ?? 'universal';

    $palmerMap = [
        1 => ['num' => 7, 'pos' => 'UR'], 2 => ['num' => 6, 'pos' => 'UR'], 3 => ['num' => 5, 'pos' => 'UR'], 4 => ['num' => 4, 'pos' => 'UR'],
        5 => ['num' => 3, 'pos' => 'UR'], 6 => ['num' => 2, 'pos' => 'UR'], 7 => ['num' => 1, 'pos' => 'UR'],
        8 => ['num' => 1, 'pos' => 'UL'], 9 => ['num' => 2, 'pos' => 'UL'], 10 => ['num' => 3, 'pos' => 'UL'], 11 => ['num' => 4, 'pos' => 'UL'],
        12 => ['num' => 5, 'pos' => 'UL'], 13 => ['num' => 6, 'pos' => 'UL'], 14 => ['num' => 7, 'pos' => 'UL'],
        15 => ['num' => 7, 'pos' => 'LR'], 16 => ['num' => 6, 'pos' => 'LR'], 17 => ['num' => 5, 'pos' => 'LR'], 18 => ['num' => 4, 'pos' => 'LR'],
        19 => ['num' => 3, 'pos' => 'LR'], 20 => ['num' => 2, 'pos' => 'LR'], 21 => ['num' => 1, 'pos' => 'LR'],
        22 => ['num' => 1, 'pos' => 'LL'], 23 => ['num' => 2, 'pos' => 'LL'], 24 => ['num' => 3, 'pos' => 'LL'], 25 => ['num' => 4, 'pos' => 'LL'],
        26 => ['num' => 5, 'pos' => 'LL'], 27 => ['num' => 6, 'pos' => 'LL'], 28 => ['num' => 7, 'pos' => 'LL']
    ];

    $fdiMap = [
        1 => ['num' => 17, 'pos' => 'UR'], 2 => ['num' => 16, 'pos' => 'UR'], 3 => ['num' => 15, 'pos' => 'UR'], 4 => ['num' => 14, 'pos' => 'UR'],
        5 => ['num' => 13, 'pos' => 'UR'], 6 => ['num' => 12, 'pos' => 'UR'], 7 => ['num' => 11, 'pos' => 'UR'],
        8 => ['num' => 21, 'pos' => 'UL'], 9 => ['num' => 22, 'pos' => 'UL'], 10 => ['num' => 23, 'pos' => 'UL'], 11 => ['num' => 24, 'pos' => 'UL'],
        12 => ['num' => 25, 'pos' => 'UL'], 13 => ['num' => 26, 'pos' => 'UL'], 14 => ['num' => 27, 'pos' => 'UL'],
        15 => ['num' => 47, 'pos' => 'LR'], 16 => ['num' => 46, 'pos' => 'LR'], 17 => ['num' => 45, 'pos' => 'LR'], 18 => ['num' => 44, 'pos' => 'LR'],
        19 => ['num' => 43, 'pos' => 'LR'], 20 => ['num' => 42, 'pos' => 'LR'], 21 => ['num' => 41, 'pos' => 'LR'],
        22 => ['num' => 31, 'pos' => 'LL'], 23 => ['num' => 32, 'pos' => 'LL'], 24 => ['num' => 33, 'pos' => 'LL'], 25 => ['num' => 34, 'pos' => 'LL'],
        26 => ['num' => 35, 'pos' => 'LL'], 27 => ['num' => 36, 'pos' => 'LL'], 28 => ['num' => 37, 'pos' => 'LL']
    ];

    $toothMap = $numberingSystem === 'fdi' ? $fdiMap : $palmerMap;
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>صورت وضعیت</title>
    <style>
        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ base64_encode(file_get_contents(resource_path('fonts/iranYekanX/IRANYekanMediumFaNum.ttf'))) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'IRANYekanX';
            src: url('data:font/ttf;base64,{{ base64_encode(file_get_contents(resource_path('fonts/iranYekanX/IRANYekanMediumFaNum.ttf'))) }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'IRANYekanX', Tahoma, Arial, sans-serif;
            font-size: 13px;
            direction: rtl;
            color: #212121;
            background: #fff;
            margin: 0;
            padding: 20px;
        }

        /* Header */
        .pdf-header {
            width: 100%;
            overflow: hidden;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header-right {
            float: right;
            width: 60%;
            text-align: right;
        }

        .header-left {
            float: left;
            width: 40%;
            text-align: left;
        }

        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .report-date {
            font-size: 10pt;
        }

        /* Users Box */
        .users-box {
            width: 98%;
            overflow: hidden;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
            padding: 14px 8px;
            margin-bottom: 30px;
        }

        .user-col {
            float: right;
            width: 33.3%;
            font-size: 10pt;
        }

        /* Tables */
        .booking-category {
            padding: 6px 15px;
            border-radius: 8px;
            background-color: #e8e8e8;
            display: inline-block;
            margin: 20px auto 10px auto;
            color: #212121;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
        }

        .category-wrapper {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #d8d8d8;
            padding: 8px;
            font-size: 8pt;
            text-align: center;
            font-weight: normal;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 7.6pt;
        }

        .ltr {
            direction: ltr;
            display: inline-block;
        }

        /* Dental Map Palmer Cross Grid */
        .palmer-cross-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d8d8d8;
            background-color: #fff;
            padding: 3px 6px;
            border-radius: 4px;
            margin: 2px 0;
        }
        .palmer-cross-label {
            font-weight: bold;
            color: #666;
            font-size: 8pt;
        }
        .palmer-grid {
            display: grid;
            grid-template-cols: repeat(2, minmax(0, 1fr));
            direction: rtl;
            width: 100%;
        }
        .palmer-cell {
            min-width: 24px;
            min-height: 18px;
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .palmer-cell.ur {
            border-left: 1.5px solid #888;
            border-bottom: 1.5px solid #888;
            padding-bottom: 1px;
            padding-left: 3px;
            justify-content: flex-end;
        }
        .palmer-cell.ul {
            border-bottom: 1.5px solid #888;
            padding-bottom: 1px;
            padding-right: 3px;
            justify-content: flex-start;
        }
        .palmer-cell.lr {
            border-left: 1.5px solid #888;
            padding-top: 1px;
            padding-left: 3px;
            justify-content: flex-end;
        }
        .palmer-cell.ll {
            padding-top: 1px;
            padding-right: 3px;
            justify-content: flex-start;
        }
        .palmer-tooth {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 10px;
            font-size: 8pt;
            font-weight: bold;
            color: #4f46e5; /* Indigo */
        }
    </style>
</head>
<body>
@php
    $isSingleDay = ($startDateLocal == $endDateLocal);
    $provider = $selectedUsers['provider'] ?? null;
    $others = collect($selectedUsers)->except('provider');

    // Prepare other users for display (up to 2 slots)
    $otherUsersList = [];
    foreach($others as $roleId => $user) {
        $role = $statementRoles->firstWhere('id', $roleId);
        $roleName = $role ? ($role->display_name ?? $role->name) : 'همکار';
        $otherUsersList[] = ['role' => $roleName, 'name' => $user->name];
    }
@endphp

<div class="pdf-header">
    <div class="header-right">
        <div class="report-title">صورت وضعیت کلینیک</div>
        <div class="report-date">
            تاریخ:
            @if($isSingleDay)
                <span class="ltr">{{ $startDateLocal }}</span>
            @else
                <span class="ltr">{{ $startDateLocal }}</span> تا <span class="ltr">{{ $endDateLocal }}</span>
            @endif

            @if($firstAppointmentTime && $lastAppointmentTime)
                (از ساعت {{ $firstAppointmentTime->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') }}
                تا {{ $lastAppointmentTime->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') }})
            @endif
        </div>
    </div>
    <div class="header-left">
        {{-- Logo can be placed here if available --}}
    </div>
</div>

<div class="users-box">
    <div class="user-col" style="text-align: right;">
        پزشک: <span style="font-weight: bold;">{{ $provider ? $provider->name : '______' }}</span>
    </div>

    <div class="user-col" style="text-align: center;">
        @if(isset($otherUsersList[0]))
            {{ $otherUsersList[0]['role'] }}: <span style="font-weight: bold;">{{ $otherUsersList[0]['name'] }}</span>
        @else
            &nbsp;
        @endif
    </div>

    <div class="user-col" style="text-align: left;">
        @if(isset($otherUsersList[1]))
            {{ $otherUsersList[1]['role'] }}: <span style="font-weight: bold;">{{ $otherUsersList[1]['name'] }}</span>
        @else
            &nbsp;
        @endif
    </div>
    <div style="clear: both;"></div>
</div>

@if(count($appointments) === 0)
    <p style="text-align: center; margin-top: 50px;">هیچ رزروی یافت نشد</p>
@else
    @foreach($appointments as $categoryName => $categoryAppointments)
        <div class="category-wrapper">
            <div class="booking-category">{{ $categoryName }}</div>
        </div>

        <table>
            <thead>
            <tr>
                <th style="width: 8%;">ساعت</th>
                <th style="width: 16%;">نام بیمار</th>
                <th style="width: 11%;">شماره پرونده</th>
                <th>نوع درمان</th>
                <th style="width: 20%;">یادداشت</th>
                @if(!$isSingleDay)
                    <th style="width: 10%;">تاریخ نوبت</th>
                @endif
                <th style="width: 9%;">ورود</th>
                <th style="width: 9%;">خروج</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categoryAppointments as $appointment)
                <tr>
                    <td>
                        {{ $appointment->start_at_utc ? $appointment->start_at_utc->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') : '-' }}
                    </td>
                    <td>{{ $appointment->client?->full_name ?? '-' }}</td>
                    <td>{{ $appointment->client?->case_number ?? '-' }}</td>
                    <td style="text-align: center;">
                        @php
                            $parts = [];
                            if($appointment->unit_count) {
                                $parts[] = '<span>' . e($appointment->unit_count) . ' واحد</span>';
                            }
                            if($appointment->service?->name) {
                                $parts[] = '<span>' . e($appointment->service->name) . '</span>';
                            }

                            if(!empty($appointment->processed_form_response)) {
                                foreach($appointment->processed_form_response as $item) {
                                    if(!empty($item['value'])) {
                                        $isToothNumber = (
                                            (!empty($item['type']) && strtolower($item['type']) === 'tooth_number') ||
                                            str_contains($item['key'] ?? '', 'tooth') ||
                                            ($item['label'] ?? '') === 'شماره دندان'
                                        );
                                        if ($isToothNumber) {
                                            if (is_array($item['value'])) {
                                                $rawTeeth = $item['value'];
                                            } else {
                                                $rawTeeth = array_filter(array_map('trim', explode(',', (string)$item['value'])), fn($v) => $v !== '');
                                            }
                                            
                                            $groupedTeeth = ['UR' => [], 'UL' => [], 'LR' => [], 'LL' => []];
                                            foreach ($rawTeeth as $tooth) {
                                                $toothId = is_array($tooth) ? ($tooth['number'] ?? array_values($tooth)[0]) : $tooth;
                                                $toothInfo = $toothMap[$toothId] ?? ['num' => $toothId, 'pos' => 'UR'];
                                                $groupedTeeth[$toothInfo['pos']][] = $toothInfo['num'];
                                            }
                                            
                                            // Sort UR/LR descending
                                            usort($groupedTeeth['UR'], fn($a, $b) => $b - $a);
                                            usort($groupedTeeth['LR'], fn($a, $b) => $b - $a);
                                            
                                            // Sort UL/LL ascending
                                            usort($groupedTeeth['UL'], fn($a, $b) => $a - $b);
                                            usort($groupedTeeth['LL'], fn($a, $b) => $a - $b);
                                            
                                            // Build Palmer Cross HTML
                                            $html = '<div class="palmer-cross-container">';
                                            $html .= '<div class="palmer-grid">';
                                            
                                            // UR
                                            $html .= '<div class="palmer-cell ur">';
                                            foreach($groupedTeeth['UR'] as $num) {
                                                $html .= '<span class="palmer-tooth">' . e($num) . '</span>';
                                            }
                                            $html .= '</div>';
                                            
                                            // UL
                                            $html .= '<div class="palmer-cell ul">';
                                            foreach($groupedTeeth['UL'] as $num) {
                                                $html .= '<span class="palmer-tooth">' . e($num) . '</span>';
                                            }
                                            $html .= '</div>';
                                            
                                            // LR
                                            $html .= '<div class="palmer-cell lr">';
                                            foreach($groupedTeeth['LR'] as $num) {
                                                $html .= '<span class="palmer-tooth">' . e($num) . '</span>';
                                            }
                                            $html .= '</div>';
                                            
                                            // LL
                                            $html .= '<div class="palmer-cell ll">';
                                            foreach($groupedTeeth['LL'] as $num) {
                                                $html .= '<span class="palmer-tooth">' . e($num) . '</span>';
                                            }
                                            $html .= '</div>';
                                            
                                            $html .= '</div>'; // palmer-grid
                                            $html .= '</div>'; // palmer-cross-container
                                            
                                            $parts[] = $html;
                                        } else {
                                            $val = is_array($item['value']) ? implode('/', $item['value']) : $item['value'];
                                            $parts[] = '<span>' . e($item['label'] . ' ' . $val) . '</span>';
                                        }
                                    }
                                }
                            }
                        @endphp
                        {!! implode(' - ', $parts) !!}
                    </td>
                    <td>{{ $appointment->notes ?? '-' }}</td>
                    @if(!$isSingleDay)
                        <td>
                            <span class="ltr">{{ $appointment->start_at_utc ? \Morilog\Jalali\Jalalian::fromCarbon($appointment->start_at_utc->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran')))->format('Y/m/d') : '-' }}</span>
                        </td>
                    @endif
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach
@endif
</body>
</html>
