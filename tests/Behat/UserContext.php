<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\User;
use App\Repository\UserRepository;
use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final readonly class UserContext implements Context
{
    /**
     * This is a hashed version of the test password "testtest"
     */
    private const TEST_PASSWORD = '$2y$13$LmBXtXmWImoqknAOugb3muzk8ydwGxxNqJ8nDlPG40iR5cyxvvq7u';

    private EntityManager $manager;
    private UserRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManagerForClass(User::class);
        $this->repository = $this->manager->getRepository(User::class);
    }

    /**
     * @Given there is a user :email
     */
    public function thereIsAUser(string $email)
    {
        $user = $this->repository->findOneBy(['email' => $email]);

        if (!$user instanceof User) {
            $user = new User($email);
            $user->setPassword(self::TEST_PASSWORD);

            $this->manager->persist($user);
            $this->manager->flush();
        }

        return $user;
    }

    #[Given('there is a user :email with UUID :uuid')]
    public function thereIsAUserWithUuid(string $email, string $uuid)
    {
        $user = $this->thereIsAUser($email);

        $property = (new \ReflectionClass($user))->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, Uuid::fromString($uuid));
        $property->setAccessible(false);

        $this->manager->persist($user);
        $this->manager->flush();
    }
}
