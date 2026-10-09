<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;

final readonly class ProductFacets
{
    /**
     * @param list<FacetValue>         $exposure
     * @param list<FacetValue>         $size
     * @param list<CategoryFacetValue> $category
     */
    public function __construct(
        #[ApiProperty(description: 'Toutes les expositions, y compris à 0.', required: true)]
        public array $exposure,
        #[ApiProperty(description: 'Toutes les tailles, y compris à 0.', required: true)]
        public array $size,
        #[ApiProperty(description: 'Sous-catégories directes de la catégorie filtrée (catégories racines sans filtre). Vide sur une feuille.', required: true)]
        public array $category,
    ) {
    }
}
