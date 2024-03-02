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
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\MappingException;
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
     * @Given /^there is a task \{([0-9a-f-]+)\} owned by "([^"]+)"(?: with title "([^"]*)")?$/
     */
    public function thereIsATaskOwnedBy(string $id, string $ownerEmail, string $title = 'Test task'): void
    {
        $task = new Task($title, $this->userContext->thereIsAUser($ownerEmail));

        // Set task ID through reflection
        $reflection = new \ReflectionClass($task);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($task, new Uuid($id));
        $property->setAccessible(false);

        $task->dueDate = new \DateTimeImmutable('tomorrow');

        $this->manager->persist($task);
        $this->manager->flush();
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
     * @Then /^task \{([0-9a-f-]+)\} should (not )?exist$/
     *
     * @throws NotSupported
     * @throws ORMInvalidArgumentException
     * @throws MappingException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function taskShouldExist(string $id, bool $not = false): void
    {
        $this->manager->clear();

        $task = $this->repository->find(Uuid::fromString($id));

        if ($not && $task !== null) {
            throw new \RuntimeException('Task %d exists, while expeciting it should not.');
        }

        if (!$not && $task === null) {
            throw new \RuntimeException('Task %d does not exist, while expecting it should.');
        }
    }

    /**
     * @Then /^the title of task \{([0-9a-f-]+)\} should be "([^"]*)"$/
     *
     * @throws MappingException
     * @throws NotSupported
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function theTitleOfTaskShouldBe(string $id, string $expected): void
    {
        $this->manager->clear();

        try {
            $actual = $this->repository->createQueryBuilder('task')
                ->select('task.title')
                ->where('task.id = :id')
                ->setParameter('id', Uuid::fromString($id))
                ->getQuery()
                ->getsingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $exception) {
            throw new \RuntimeException(\sprintf('Task %s does not exist', $id));
        }

        if ($expected !== $actual) {
            throw new \RuntimeException(\sprintf('Task %s has title "%s", while expecting "%s"', $id, $actual, $expected));
        }
    }
}
