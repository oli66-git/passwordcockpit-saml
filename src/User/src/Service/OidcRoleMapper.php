<?php
declare(strict_types=1);

namespace Passwordcockpit\User\Service;

final class OidcRoleMapper
{
    public function __construct(private array $roleMap) {}

    public function map(array $kcRoles): array
    {
        $internal = [];

        foreach ($this->roleMap as $internalRole => $kcEquivalentList) {
            foreach ((array)$kcEquivalentList as $role) {
                if (in_array($role, $kcRoles, true)) {
                    $internal[] = $internalRole;
                    break;
                }
            }
        }

        return array_values(array_unique($internal));
    }
}
