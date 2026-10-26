<?php

return [
    'booking_daily_appointments' => [
        'label'       => 'نوبت‌های امروز و مدیریت سریع',
        'view'        => 'booking::widgets.daily-appointments',
        'permission'  => 'booking.appointments.view',
        'group'       => 'نوبت‌دهی',
        'module'      => 'Booking',
        'description' => 'مشاهده لحظه‌ای نوبت‌های امروز، ثبت ورود، شروع و پایان ویزیت و تغییر وضعیت سریع',
    ],
];
