<?php
declare(strict_types=1);

namespace Passwordcockpit\Authentication\Oidc;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class OidcSessionMiddleware implements MiddlewareInterface
{
    public function __construct(private array $cfg) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!($this->cfg['enabled'] ?? false)) {
            return $handler->handle($request);
        }
        
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $path = $request->getUri()->getPath();
            if (str_starts_with($path, '/api/')) {
                return new JsonResponse(['error' => 'unauthorized'], 401);
            }
            return new RedirectResponse('/auth/login', 302);
        }
        
        return $handler->handle($request);
    }
}
