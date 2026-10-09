<?php

declare(strict_types=1);

namespace App\Task\Domain;

interface TaskRepository
{
    /**
     * @return list<Task>
     */
    public function findAll(): array;
}
