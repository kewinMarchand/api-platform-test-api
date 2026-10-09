<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

class Product
{
    private ?int $id = null;

    /**
     * @param int $price prix en centimes
     * @param int $image index du visuel partagé, de 1 à 12
     */
    public function __construct(
        private string $slug,
        private string $name,
        private Category $category,
        private int $price,
        private Exposure $exposure,
        private Size $size,
        private bool $inStock,
        private int $image,
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

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getExposure(): Exposure
    {
        return $this->exposure;
    }

    public function getSize(): Size
    {
        return $this->size;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function getImage(): int
    {
        return $this->image;
    }
}
