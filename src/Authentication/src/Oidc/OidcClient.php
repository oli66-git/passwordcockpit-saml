<?php
declare(strict_types=1);

namespace Passwordcockpit\Authentication\Oidc;

use Jumbojett\OpenIDConnectClient;

final class OidcClient
{
    private OpenIDConnectClient $client;
    private array $cfg;

    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
        $issuer = rtrim((string)($cfg['issuer'] ?? ''), '/');
        $clientId = (string)($cfg['clientId'] ?? '');
        $clientSecret = (string)($cfg['clientSecret'] ?? '');
        $redirectUri = (string)($cfg['redirectUri'] ?? '');

        $this->client = new OpenIDConnectClient($issuer, $clientId, $clientSecret);
        $this->client->setRedirectURL($redirectUri);

        $scopes = $cfg['scopes'] ?? ['openid', 'email', 'profile'];
        foreach ($scopes as $scope) {
            $this->client->addScope($scope);
        }
    }

    public function getAuthorizationUrl(): string
    {
        return $this->client->getAuthorizationURL();
    }

    public function authenticateWithCode(string $code): array
    {
        $this->client->authenticateWithCode($code);
        $idToken = $this->client->getIdToken();
        $user = (array)$this->client->requestUserInfo();
        return ['id_token' => $idToken, 'user' => $user];
    }
}
