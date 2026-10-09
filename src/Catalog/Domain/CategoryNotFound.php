<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

final class CategoryNotFound extends \DomainException
{
    public function __construct(string $slug)
    {
        parent::__construct(\sprintf('Catégorie introuvable : %s', $slug));
    }
}
