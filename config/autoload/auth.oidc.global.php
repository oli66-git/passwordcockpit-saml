<?php
declare(strict_types=1);

return [
    'oidc' => [
        'enabled' => getenv('OIDC_ENABLED') === 'true',
        'issuer' => rtrim((string)getenv('OIDC_ISSUER'), '/'),
        'clientId' => (string)getenv('OIDC_CLIENT_ID'),
        'clientSecret' => (string)getenv('OIDC_CLIENT_SECRET'),
        'redirectUri' => (string)getenv('OIDC_REDIRECT_URI'),
        'scopes' => array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(' ', getenv('OIDC_SCOPES') ?: 'openid email profile')
                )
            )
        ),
        'claims' => [
            'email' => getenv('OIDC_CLAIM_EMAIL') ?: 'email',
            'name' => getenv('OIDC_CLAIM_NAME') ?: 'name',
            'roles' => getenv('OIDC_CLAIM_ROLES') ?: 'realm_access.roles',
        ],
        'logout_redirect' => (string)(getenv('OIDC_LOGOUT_REDIRECT') ?: '/'),
        'roles_paths' => array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(',', getenv('OIDC_ROLES_PATHS') ?: 'realm_access.roles,resource_access.{clientId}.roles')
                )
            )
        ),
        'provision_missing_users' => getenv('OIDC_PROVISION_MISSING_USERS') === 'true',
    ],
    'user' => [
        'entity_class' => getenv('USER_ENTITY_CLASS') ?: '\\Passwordcockpit\\User\\Api\\V1\\Entity\\User',
    ],
];
