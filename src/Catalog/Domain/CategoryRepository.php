<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

interface CategoryRepository
{
    /**
     * @return list<Category> toutes les catégories, triées par position
     */
    public function findAll(): array;
}
