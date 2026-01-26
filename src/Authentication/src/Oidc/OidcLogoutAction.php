<?php
declare(strict_types=1);

namespace Passwordcockpit\Authentication\Oidc;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class OidcLogoutAction implements RequestHandlerInterface
{
    public function __construct(private array $cfg) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (PHP_SESSION_ACTIVE === session_status()) {
            session_destroy();
        }

        $logoutRedirect = $this->cfg['logout_redirect'] ?? '/';
        
        return new RedirectResponse($logoutRedirect, 302);
    }
}
