<?php

return [
    'translatable' => [
        'multilang' => true,
        'locales' => ['fr', 'en'],
        'active_locales' => ['fr', 'en'],
        'fallback_locale' => 'fr',
    ],
    'urls' => [
        'backend' => 'panel'
    ],
    'navigation' => [
        'users_role' => 'super-admin',
        'messages_route' => 'publisher.mails.index',
        'log_viewer_route' => 'panel.log-viewer.index',
    ],
    'tables' => [
        'user' => 'users'
    ],
    'siteowner' => [
        'active' => true,
        'reg_number' => 'SIRET',
        'address_lines' => 1
    ],
];
