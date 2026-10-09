<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Task\Domain\Task;
use App\Task\Infrastructure\ApiPlatform\State\TaskCollectionProvider;

#[ApiResource(
    shortName: 'Task',
    description: 'Tâche de la liste de démonstration.',
    operations: [
        new GetCollection(
            description: 'Liste toutes les tâches, sans pagination.',
            provider: TaskCollectionProvider::class,
        ),
    ],
)]
final readonly class TaskResource
{
    public function __construct(
        #[ApiProperty(identifier: true, required: true)]
        public int $id,
        #[ApiProperty(required: true)]
        public string $title,
        #[ApiProperty(required: true)]
        public bool $done,
    ) {
    }

    public static function fromModel(Task $task): self
    {
        return new self((int) $task->getId(), $task->getTitle(), $task->isDone());
    }
}
