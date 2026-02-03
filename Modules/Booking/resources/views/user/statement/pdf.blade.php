<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>صورت وضعیت</title>
    <style>
        @font-face {
            font-family: 'Vazirmatn';
            src: url('{{ public_path('fonts/Vazirmatn-Regular.ttf') }}') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('{{ public_path('fonts/Vazirmatn-Bold.ttf') }}') format('truetype');
            font-weight: bold;
        }

        body {
            font-family: 'Vazirmatn', Tahoma, Arial, sans-serif;
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
            width: 100%;
            overflow: hidden;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
            padding: 15px;
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
            font-size: 11pt;
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
            font-size: 9pt;
            text-align: center;
            font-weight: normal;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }

        .ltr {
            direction: ltr;
            display: inline-block;
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
                        <th style="width: 5%;">ردیف</th>
                        <th style="width: 10%;">ساعت</th>
                        <th style="width: 20%;">نام بیمار</th>
                        <th style="width: 15%;">شماره پرونده</th>
                        <th>نوع درمان</th>
                        @if(!$isSingleDay)
                            <th style="width: 12%;">تاریخ نوبت</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $rowNumber = 1; @endphp
                    @foreach($categoryAppointments as $appointment)
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td>
                                {{ $appointment->start_at_utc ? $appointment->start_at_utc->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('H:i') : '-' }}
                            </td>
                            <td>{{ $appointment->client?->full_name ?? '-' }}</td>
                            <td>{{ $appointment->client?->case_number ?? '-' }}</td>
                            <td style="text-align: right;">
                                @php
                                    $parts = [];
                                    if($appointment->unit_count) {
                                        $parts[] = $appointment->unit_count . ' واحد';
                                    }
                                    if($appointment->service?->name) {
                                        $parts[] = $appointment->service->name;
                                    }

                                    if(!empty($appointment->processed_form_response)) {
                                        foreach($appointment->processed_form_response as $item) {
                                            if(!empty($item['value'])) {
                                                $val = is_array($item['value']) ? implode('/', $item['value']) : $item['value'];
                                                $parts[] = $item['label'] . ' ' . $val;
                                            }
                                        }
                                    }
                                @endphp
                                {{ implode(' - ', $parts) }}
                            </td>
                            @if(!$isSingleDay)
                                <td>
                                    <span class="ltr">{{ $appointment->start_at_utc ? $appointment->start_at_utc->copy()->timezone(config('booking.timezones.display_default', 'Asia/Tehran'))->format('Y/m/d') : '-' }}</span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</body>
</html>
