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
        'title' => 'گردش کارها',
        'route' => 'user.workflows.index',
        'permission' => 'workflows.view',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-jump-rope"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 14v-6a3 3 0 1 1 6 0v8a3 3 0 0 0 6 0v-6" /><path d="M16 5a2 2 0 0 1 2 -2a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2a2 2 0 0 1 -2 -2l0 -3" /><path d="M4 16a2 2 0 0 1 2 -2a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2a2 2 0 0 1 -2 -2l0 -3" /></svg>',
        'group' => 'workflows',
        'position' => 10,
    ],
];
