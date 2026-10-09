<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Catalog\Infrastructure\ApiPlatform\State\CategoryTreeProvider;

#[ApiResource(
    shortName: 'Category',
    description: 'Catégorie du catalogue, avec ses sous-catégories imbriquées.',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            description: 'Renvoie l\'arbre des catégories : les catégories racines, chacune avec ses enfants, triées par position.',
            provider: CategoryTreeProvider::class,
        ),
    ],
)]
final readonly class CategoryResource
{
    /**
     * @param list<CategoryResource> $children
     */
    public function __construct(
        #[ApiProperty(identifier: true, required: true)]
        public string $slug,
        #[ApiProperty(required: true)]
        public string $name,
        #[ApiProperty(description: 'Slug de la catégorie parente, null à la racine.', required: true)]
        public ?string $parent,
        #[ApiProperty(readableLink: true, required: true)]
        public array $children,
    ) {
    }
}
