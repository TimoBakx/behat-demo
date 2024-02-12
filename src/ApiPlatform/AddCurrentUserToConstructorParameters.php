<?php

declare(strict_types=1);

namespace App\ApiPlatform;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[AsDecorator('api_platform.serializer.context_builder')]
final readonly class AddCurrentUserToConstructorParameters implements SerializerContextBuilderInterface
{
    private const ARGUMENTS = [
        Task::class => 'owner',
    ];

    public function __construct(
        private SerializerContextBuilderInterface $decorated,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $user = $this->getUser();

        $context[AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS] = $context[AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS] ?? [];
        foreach (self::ARGUMENTS as $class => $parameter) {
            $context[AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS][$class][$parameter] = $user;
        }

        return $context;
    }

    public function getUser(): ?User
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
