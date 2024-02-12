<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

final class TaskContext implements Context
{
    private EntityManager $manager;
    private TaskRepository $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManagerForClass(Task::class);
        $this->repository = $this->manager->getRepository(Task::class);
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
}
