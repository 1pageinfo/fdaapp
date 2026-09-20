<?php

// Single source of truth for the app's feature-based permission system.
// Both the RolesAndPermissionsSeeder and the Settings > User Permissions
// screen read from here, so a feature only ever needs to be defined once.
//
// Each feature's "actions" list should only include actions that a real
// route/controller method exists for — don't add "edit"/"delete" to a
// feature that has no edit/delete route.

return [
    'categories' => [
        'core' => [
            'label' => 'Core Modules',
            'icon' => 'ti-briefcase',
            'features' => [
                'sanghs'   => ['label' => 'Sanghs',   'icon' => 'ti-home',      'actions' => ['create', 'view', 'edit', 'delete']],
                'meetings' => ['label' => 'Meetings',  'icon' => 'ti-calendar',  'actions' => ['create', 'view', 'edit', 'delete']],
                'folders'  => ['label' => 'Folders',   'icon' => 'ti-folder',    'actions' => ['create', 'view', 'edit', 'delete']],
                'files'    => ['label' => 'Files',     'icon' => 'ti-file',      'actions' => ['create', 'view', 'edit', 'delete']],
                'groups'   => ['label' => 'Groups',    'icon' => 'ti-comments',  'actions' => ['create', 'view', 'edit', 'delete']],
                'chats'    => ['label' => 'Chats',     'icon' => 'ti-comment',   'actions' => ['create', 'view', 'edit', 'delete']],
                'links'    => ['label' => 'Links',     'icon' => 'ti-link',      'actions' => ['create', 'view', 'edit', 'delete']],
                'receipts' => ['label' => 'Receipts',  'icon' => 'ti-wallet',    'actions' => ['create', 'view']],
            ],
        ],
        'account' => [
            'label' => 'Account & System',
            'icon' => 'ti-settings',
            'features' => [
                'dashboard'     => ['label' => 'Dashboard',       'icon' => 'ti-dashboard',  'actions' => ['view']],
                'profile'       => ['label' => 'Profile',         'icon' => 'ti-id-badge',   'actions' => ['view', 'edit']],
                'settings'      => ['label' => 'Settings',        'icon' => 'ti-settings',   'actions' => ['view', 'edit']],
                'sangh_fee'     => ['label' => 'Sangh Fee Slabs', 'icon' => 'ti-money',      'actions' => ['view', 'edit']],
                'search'        => ['label' => 'Search',          'icon' => 'ti-search',     'actions' => ['view']],
                'notifications' => ['label' => 'Notifications',   'icon' => 'ti-bell',       'actions' => ['view']],
                'audit'         => ['label' => 'Activity Logs',   'icon' => 'ti-list',       'actions' => ['view']],
            ],
        ],
    ],
];
