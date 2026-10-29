<?php
// Each feature owns its actions and view. Only this allowlist is routable.
$definitions = [
    'dashboard' => [
        'Overview',
        '◫',
        'A clear view of your community services, all in one place.',
        false,
        'dashboard',
        [],
    ],
    'residents' => [
        'Residents',
        '♙',
        'Keep your community’s resident information up to date.',
        true,
        'residents',
        ['resident' => 'saveResidentAction', 'delete_resident' => 'deleteResidentAction'],
    ],
    'requests' => [
        'Document requests',
        '▤',
        'Track every document from submission to pickup.',
        false,
        'requests',
        ['request' => 'createRequestAction', 'status' => 'updateRequestStatusAction'],
    ],
    'types' => [
        'Document types',
        '▦',
        'Set available documents, fees, and submission requirements.',
        true,
        'documents',
        ['type' => 'saveDocumentTypeAction'],
    ],
    'payments' => [
        'Payments',
        '₱',
        'Record document payments and keep transactions organized.',
        true,
        'payments',
        ['payment' => 'recordPaymentAction'],
    ],
    'logs' => [
        'Activity log',
        '◷',
        'A transparent record of account and staff activity.',
        true,
        'logs',
        [],
    ],
    'reports' => [
        'Reports',
        '▥',
        'Review transactions and export records for your office.',
        true,
        'reports',
        [],
    ],
    'profile' => [
        'My profile',
        '⚙',
        'Manage your account information and password.',
        false,
        'profile',
        ['account' => 'updateProfileAction'],
    ],
];
$modules = [];
foreach ($definitions as $key => [$title, $icon, $description, $staffOnly, $folder, $actions]) {
    $handlers = [];
    foreach ($actions as $action => $function) {
        $handlers[$action] = [
            'file' => __DIR__ . '/' . $folder . '/actions.php',
            'function' => $function,
        ];
    }
    $modules[$key] = compact('title', 'icon', 'description', 'staffOnly') + [
        'view' => __DIR__ . '/' . $folder . '/view.php',
        'actions' => $handlers,
    ];
}
foreach (
    ['login' => 'loginAction', 'logout' => 'logoutAction', 'register' => 'registerAction']
    as $action => $function
) {
    $modules['dashboard']['actions'][$action] = [
        'file' => __DIR__ . '/auth/actions.php',
        'function' => $function,
    ];
}
return $modules;
