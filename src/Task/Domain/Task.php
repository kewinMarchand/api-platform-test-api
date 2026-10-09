<?php

declare(strict_types=1);

namespace App\Task\Domain;

class Task
{
    private ?int $id = null;

    public function __construct(
        private string $title,
        private bool $done = false,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isDone(): bool
    {
        return $this->done;
    }
}
