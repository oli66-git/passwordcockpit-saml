<?php
declare(strict_types=1);

namespace Passwordcockpit\Authentication\Oidc;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class OidcLoginAction implements RequestHandlerInterface
{
    public function __construct(
        private OidcClient $oidc,
        private array $cfg
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!($this->cfg['enabled'] ?? false)) {
            return new RedirectResponse('/', 302);
        }
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }
        $url = $this->oidc->getAuthorizationUrl();
        return new RedirectResponse($url, 302);
    }
}
