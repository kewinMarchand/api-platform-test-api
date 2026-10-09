<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

class Category
{
    private ?int $id = null;

    public function __construct(
        private string $slug,
        private string $name,
        private int $position,
        private ?self $parent = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }
}
