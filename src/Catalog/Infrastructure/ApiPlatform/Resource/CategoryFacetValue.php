<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;

final readonly class CategoryFacetValue
{
    public function __construct(
        #[ApiProperty(required: true)]
        public string $slug,
        #[ApiProperty(required: true)]
        public string $name,
        #[ApiProperty(description: 'Nombre de produits de cette sous-catégorie qui respectent les autres filtres.', required: true)]
        public int $count,
    ) {
    }
}
