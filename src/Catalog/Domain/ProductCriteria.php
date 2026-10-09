<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

final readonly class ProductCriteria
{
    public const int DEFAULT_ITEMS_PER_PAGE = 12;

    /**
     * @param list<Exposure> $exposures valeurs combinées en OU
     * @param list<Size>     $sizes     valeurs combinées en OU
     */
    public function __construct(
        public ?string $categorySlug = null,
        public array $exposures = [],
        public array $sizes = [],
        public ?Price $priceMin = null,
        public ?Price $priceMax = null,
        public bool $inStockOnly = false,
        public ?ProductSort $sort = null,
        public int $page = 1,
        public int $itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE,
    ) {
    }
}
