<?php

return [
    'name' => 'Workflows',

    'default_reminder_channel' => 'IN_APP',

    'dependencies' => [
        'tasks'     => 'Tasks',
        'followups' => 'FollowUps',
        'reminders' => 'Reminders',
        'sms'       => 'Sms',
    ],
];
