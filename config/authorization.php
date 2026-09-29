<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Cache Configuration
    |--------------------------------------------------------------------------
    */
    'sidebar_cache_ttl' => env('AUTHORIZATION_SIDEBAR_CACHE_TTL', 3600),
    'sidebar_cache_prefix' => env('AUTHORIZATION_SIDEBAR_CACHE_PREFIX', 'user_menus_v2_'),

    /*
    |--------------------------------------------------------------------------
    | Database Bulk Insert Chunk Size
    |--------------------------------------------------------------------------
    */
    'db_chunk_size' => env('AUTHORIZATION_DB_CHUNK_SIZE', 100),

    /*
    |--------------------------------------------------------------------------
    | Role Slugs Definitions
    |--------------------------------------------------------------------------
    */
    'admin_role_slugs' => [
        'super_admin',
        'admin',
        'hr_admin',
        'finance_admin',
        'project_admin',
        'operations_admin',
        'custom_admin',
        'manager',
    ],

    'hr_admin_slugs' => [
        'super_admin',
        'admin',
        'hr_admin',
        'hr admin',
        'hr',
        'human resources',
    ],

    'super_admin_slugs' => [
        'super_admin',
        'super admin',
    ],
];
