<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Task\Domain\TaskRepository;
use App\Task\Infrastructure\ApiPlatform\Resource\TaskResource;

/**
 * @implements ProviderInterface<TaskResource>
 */
final readonly class TaskCollectionProvider implements ProviderInterface
{
    public function __construct(private TaskRepository $taskRepository)
    {
    }

    /**
     * @return list<TaskResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(TaskResource::fromModel(...), $this->taskRepository->findAll());
    }
}
