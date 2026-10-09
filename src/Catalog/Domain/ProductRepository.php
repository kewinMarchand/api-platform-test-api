<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

interface ProductRepository
{
    /**
     * @return list<Product> tous les produits, dans l'ordre de pertinence
     */
    public function findAll(): array;
}
