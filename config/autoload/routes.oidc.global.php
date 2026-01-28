<?php

use Passwordcockpit\Authentication\Oidc\OidcLoginAction;
use Passwordcockpit\Authentication\Oidc\OidcCallbackAction;
use Passwordcockpit\Authentication\Oidc\OidcLogoutAction;

$enabled = getenv('OIDC_ENABLED') === 'true';

if (!$enabled) {
    return [];
}

return [
    'routes' => [
        [
            'name' => 'auth.login',
            'path' => '/auth/login',
            'middleware' => [OidcLoginAction::class],
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'auth.callback',
            'path' => '/auth/callback',
            'middleware' => [OidcCallbackAction::class],
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'auth.logout',
            'path' => '/auth/logout',
            'middleware' => [OidcLogoutAction::class],
            'allowed_methods' => ['GET']
        ]
    ]
];
