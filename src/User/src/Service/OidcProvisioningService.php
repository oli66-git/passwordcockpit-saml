<?php
declare(strict_types=1);

namespace Passwordcockpit\User\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

final class OidcProvisioningService
{
    public function __construct(
        private EntityManagerInterface $em,
        private array $oidcCfg,
        private ?string $userEntityClass = null
    ) {}

    public function findOrProvision(
        string $iss,
        string $sub,
        string $email,
        string $name,
        array $roles
    ): object {
        $class = $this->getUserEntityClass();
        $repo = $this->em->getRepository($class);

        $user = $repo->findOneBy(['oidcSub' => $sub, 'oidcIss' => $iss]);
        if ($user) {
            $this->touch($user);
            $this->em->flush();
            return $user;
        }

        $user = $repo->findOneBy(['email' => $email]);
        if ($user) {
            $this->setIfCallable($user, 'setOidcLink', $sub, $iss);
            $this->touch($user);
            $this->em->flush();
            return $user;
        }

        if (!($this->oidcCfg['provision_missing_users'] ?? false)) {
            throw new \RuntimeException('User not found and provisioning disabled');
        }

        $user = new $class();
        $this->setIfCallable($user, 'setEmail', $email);
        $this->setIfCallable($user, 'setName', $name ?: $email);
        $this->setIfCallable($user, 'setActive', true);
        $this->setIfCallable($user, 'setOidcLink', $sub, $iss);
        $this->touch($user);

        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }

    private function getUserEntityClass(): string
    {
        if (!$this->userEntityClass) {
            throw new \RuntimeException('USER_ENTITY_CLASS not configured');
        }
        return ltrim($this->userEntityClass, '\\');
    }

    private function setIfCallable(object $obj, string $method, mixed ...$args): void
    {
        if (is_callable([$obj, $method])) {
            $obj->$method(...$args);
        }
    }

    private function touch(object $user): void
    {
        if (is_callable([$user, 'setLastLoginAt'])) {
            $user->setLastLoginAt(new \DateTimeImmutable());
        }
    }
}
