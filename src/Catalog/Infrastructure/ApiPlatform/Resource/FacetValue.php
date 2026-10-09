<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;

final readonly class FacetValue
{
    public function __construct(
        #[ApiProperty(required: true)]
        public string $value,
        #[ApiProperty(description: 'Nombre de produits obtenus en ajoutant cette valeur aux filtres des autres facettes.', required: true)]
        public int $count,
    ) {
    }
}
