<?php
declare(strict_types=1);

use Passwordcockpit\Authentication\Oidc\OidcLoginAction;
use Passwordcockpit\Authentication\Oidc\OidcCallbackAction;
use Passwordcockpit\Authentication\Oidc\OidcLogoutAction;

return static function (\Mezzio\Application $app, \Psr\Container\ContainerInterface $container): void {
    $config = $container->get('config');
    $enabled = $config['oidc']['enabled'] ?? false;
    
    if (!$enabled) {
        return;
    }

    $app->get('/auth/login', OidcLoginAction::class, 'auth.login');
    $app->get('/auth/callback', OidcCallbackAction::class, 'auth.callback');
    $app->get('/auth/logout', OidcLogoutAction::class, 'auth.logout');
};
