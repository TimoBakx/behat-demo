<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class CurrentlyLoggedInUser
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function get(): ?User
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
