<?php

// Single source of truth for the app's feature-based permission system.
// Both the RolesAndPermissionsSeeder and the Settings > User Permissions
// screen read from here, so a feature only ever needs to be defined once.
//
// Each feature's "actions" list should only include actions that a real
// route/controller method exists for — don't add "edit"/"delete" to a
// feature that has no edit/delete route.
//
// always_on:       every signed-in user has it (Dashboard, Contacts, own Activity Logs, …);
//                  not shown in Settings → User Permissions.
// superadmin_only: never grantable — only the superadmin role (Settings, Sangh Fees).

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
                'dashboard'     => ['always_on' => true, 'label' => 'Dashboard',       'icon' => 'ti-dashboard',  'actions' => ['view']],
                'contacts'      => ['always_on' => true, 'label' => 'Contacts',        'icon' => 'fa fa-address-book', 'actions' => ['view']],
                'profile'       => ['always_on' => true, 'label' => 'Profile',         'icon' => 'ti-id-badge',   'actions' => ['view', 'edit']],
                'settings'      => ['superadmin_only' => true, 'label' => 'Settings',        'icon' => 'ti-settings',   'actions' => ['view', 'edit']],
                'sangh_fee'     => ['superadmin_only' => true, 'label' => 'Sangh Fee Slabs', 'icon' => 'ti-money',      'actions' => ['view', 'edit']],
                'search'        => ['always_on' => true, 'label' => 'Search',          'icon' => 'ti-search',     'actions' => ['view']],
                'notifications' => ['always_on' => true, 'label' => 'Notifications',   'icon' => 'ti-bell',       'actions' => ['view']],
                'audit'         => ['always_on' => true, 'label' => 'Activity Logs',   'icon' => 'ti-list',       'actions' => ['view']],
            ],
        ],
    ],
];
