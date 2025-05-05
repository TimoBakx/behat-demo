<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => [self::READ]],
    denormalizationContext: ['groups' => [self::WRITE]],
)]
class Task
{
    private const READ = 'task:read';
    private const WRITE = 'task:write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $id;

    public function __construct(
        #[ORM\Column(length: 255, nullable: false)]
        #[Groups([self::READ, self::WRITE])]
        public string $title,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        private User $owner,

        #[ORM\Column(nullable: true)]
        #[Groups([self::READ, self::WRITE])]
        public ?\DateTimeImmutable $dueDate = null,
    ) {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }
}
