<?php

return [
    'client_quick_create' => [
        'label'      => 'ایجاد سریع '.config('clients.labels.singular'),
        'view'       => 'clients::widgets.client-quick-create', // مسیر view ویجت
        'permission' => 'clients.create',                       // مجوز مورد نیاز
        'group'      => config('clients.labels.plural'),                              // گروه دسته‌بندی برای UI نقش‌ها
    ],
];
