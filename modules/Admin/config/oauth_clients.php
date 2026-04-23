<?php
return [
    'name' => 'OAuth Clients',
    'description' => 'Manage OAuth client applications for SSO integration.',
    'icon' => 'fa fa-link',
    'route' => '/admin/oauth-clients',
    'permissions' => [
        'view' => 'View OAuth clients',
        'add' => 'Add OAuth client',
        'edit' => 'Edit OAuth client',
        'delete' => 'Delete OAuth client',
    ],
];
