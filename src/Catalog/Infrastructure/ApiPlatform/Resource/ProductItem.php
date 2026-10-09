<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use App\Catalog\Domain\Product;

final readonly class ProductItem
{
    public function __construct(
        #[ApiProperty(required: true)]
        public int $id,
        #[ApiProperty(required: true)]
        public string $slug,
        #[ApiProperty(required: true)]
        public string $name,
        #[ApiProperty(required: true)]
        public string $categorySlug,
        #[ApiProperty(description: 'Prix en centimes.', required: true)]
        public int $price,
        #[ApiProperty(required: true, schema: ['type' => 'string', 'enum' => ['soleil', 'mi-ombre', 'ombre']])]
        public string $exposure,
        #[ApiProperty(required: true, schema: ['type' => 'string', 'enum' => ['S', 'M', 'L']])]
        public string $size,
        #[ApiProperty(required: true)]
        public bool $inStock,
        #[ApiProperty(description: 'Index du visuel partagé, de 1 à 12.', required: true)]
        public int $image,
    ) {
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            (int) $product->getId(),
            $product->getSlug(),
            $product->getName(),
            $product->getCategory()->getSlug(),
            $product->getPrice(),
            $product->getExposure()->value,
            $product->getSize()->value,
            $product->isInStock(),
            $product->getImage(),
        );
    }
}
