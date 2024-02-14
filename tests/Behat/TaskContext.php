<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behatch\Context\RestContext;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class TaskContext implements Context
{
    private EntityManager $manager;
    private TaskRepository $repository;
    private UserContext $userContext;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManagerForClass(Task::class);
        $this->repository = $this->manager->getRepository(Task::class);
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $this->userContext = $scope->getEnvironment()->getContext(UserContext::class);
    }

    /**
     * @Then /^the newest task should be owned by "([^"]*)"$/
     */
    public function theNewestTaskShouldBeOwnedBy(string $email)
    {
        $this->manager->clear();

        $newestTask = $this->repository->createQueryBuilder('task')
            ->join('task.owner', 'owner')
            ->orderBy('task.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        if ($newestTask === null) {
            throw new \RuntimeException('No task found');
        }

        if ($newestTask->getOwner()->email !== $email) {
            throw new \RuntimeException('Task is not owned by the expected user');
        }
    }

    /**
     * @Given /^there is a task \{([0-9a-f-]+)\} owned by "([^"]*)"$/
     */
    public function thereIsATaskOwnedBy(string $id, string $ownerEmail): void
    {
        $task = new Task('Test task', $this->userContext->thereIsAUser($ownerEmail));

        // Set task ID through reflection
        $reflection = new \ReflectionClass($task);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($task, new Uuid($id));
        $property->setAccessible(false);

        $this->manager->persist($task);
        $this->manager->flush();
    }
}
