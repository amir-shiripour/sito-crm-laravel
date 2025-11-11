<?php
return [
    'notify_admin_on_create' => true,
    'items_per_page' => 20,

    'labels' => [
        'singular' => env('CLIENTS_LABEL_SINGULAR', 'مشتری'),
        'plural'   => env('CLIENTS_LABEL_PLURAL',   'مشتریان'),
    ],
];
