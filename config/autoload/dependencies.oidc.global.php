<?php

use Doctrine\ORM\EntityManagerInterface;
use Passwordcockpit\Authentication\Oidc\OidcClient;
use Passwordcockpit\Authentication\Oidc\OidcLoginAction;
use Passwordcockpit\Authentication\Oidc\OidcCallbackAction;
use Passwordcockpit\Authentication\Oidc\OidcLogoutAction;
use Passwordcockpit\Authentication\Oidc\OidcSessionMiddleware;
use Passwordcockpit\User\Service\OidcProvisioningService;
use Passwordcockpit\User\Service\OidcRoleMapper;

return [
    'dependencies' => [
        'factories' => [
            OidcClient::class => function ($container) {
                $cfg = $container->get('config')['oidc'] ?? [];
                return new OidcClient($cfg);
            },
            OidcLoginAction::class => function ($c) {
                return new OidcLoginAction($c->get(OidcClient::class), $c->get('config')['oidc'] ?? []);
            },
            OidcRoleMapper::class => function ($c) {
                $cfg = $c->get('config');
                $map = $cfg['oidc_roles'] ?? [];
                return new OidcRoleMapper($map);
            },
            OidcProvisioningService::class => function ($c) {
                $em = $c->get(EntityManagerInterface::class);
                $cfg = $c->get('config');
                return new OidcProvisioningService(
                    $em,
                    $cfg['oidc'] ?? [],
                    $cfg['user']['entity_class'] ?? null
                );
            },
            OidcCallbackAction::class => function ($c) {
                return new OidcCallbackAction(
                    $c->get(OidcClient::class),
                    $c->get(OidcProvisioningService::class),
                    $c->get('config')['oidc'] ?? []
                );
            },
            OidcLogoutAction::class => function ($c) {
                return new OidcLogoutAction($c->get('config')['oidc'] ?? []);
            },
            OidcSessionMiddleware::class => function ($c) {
                return new OidcSessionMiddleware($c->get('config')['oidc'] ?? []);
            },
        ],
    ],
];
