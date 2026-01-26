<?php
declare(strict_types=1);

namespace Passwordcockpit\Authentication\Oidc;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Passwordcockpit\User\Service\OidcProvisioningService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class OidcCallbackAction implements RequestHandlerInterface
{
    public function __construct(
        private OidcClient $oidc,
        private OidcProvisioningService $provisioning,
        private array $cfg
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (PHP_SESSION_NONE === session_status()) {
            session_start();
        }
        
        $params = $request->getQueryParams();
        $code = $params['code'] ?? null;
        
        if (!$code) {
            return new JsonResponse(['error' => 'missing_code'], 400);
        }
        
        try {
            $auth = $this->oidc->authenticateWithCode($code);
            $claims = $auth['user'] ?? [];
            $map = $this->cfg['claims'] ?? [];

            $email = $claims[$map['email'] ?? 'email'] ?? null;
            $name = $claims[$map['name'] ?? 'name'] ?? ($claims['preferred_username'] ?? '');
            $roles = $this->extractRoles($claims, $map['roles'] ?? 'realm_access.roles');
            $sub = $claims['sub'] ?? null;
            $iss = rtrim((string)($this->cfg['issuer'] ?? ''), '/');

            if (!$email || !$sub || !$iss) {
                return new JsonResponse(['error' => 'missing_required_claims'], 400);
            }

            $user = $this->provisioning->findOrProvision($iss, $sub, $email, $name, $roles);

            $_SESSION['user_id'] = method_exists($user, 'getId') ? $user->getId() : null;
            $_SESSION['user_email'] = $email;
            $_SESSION['oidc_iss'] = $iss;
            $_SESSION['oidc_sub'] = $sub;

            return new RedirectResponse('/', 302);
        } catch (\Throwable $e) {
            return new JsonResponse(
                ['error' => 'oidc_callback_failed', 'detail' => $e->getMessage()],
                500
            );
        }
    }

    private function extractRoles(array $claims, string $path): array
    {
        $parts = array_filter(explode('.', $path));
        $node = $claims;
        
        foreach ($parts as $p) {
            if (is_array($node) && array_key_exists($p, $node)) {
                $node = $node[$p];
            } else {
                return [];
            }
        }
        
        return is_array($node) ? $node : [];
    }
}
