<?php

return [
    /*'user' => [
        [
            'key'        => 'workflows',
            'label'      => 'گردش کارها',
            'icon'       => 'heroicon-o-arrow-path',
            'route'      => 'user.workflows.index',
            'permission' => 'workflows.view',
            'order'      => 260,
        ],
    ],*/
    [
        'title' => 'گردش کارها (workflows)',
        'route' => 'user.workflows.index',
        'permission' => 'workflows.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>',
        'group' => 'workflows',
        'position' => 10,
    ],
];
