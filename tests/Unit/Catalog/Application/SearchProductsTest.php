<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application;

use App\Catalog\Application\SearchProducts;
use App\Catalog\Domain\Category;
use App\Catalog\Domain\CategoryNotFound;
use App\Catalog\Domain\CategoryRepository;
use App\Catalog\Domain\Exposure;
use App\Catalog\Domain\Price;
use App\Catalog\Domain\Product;
use App\Catalog\Domain\ProductCriteria;
use App\Catalog\Domain\ProductRepository;
use App\Catalog\Domain\ProductSearch;
use App\Catalog\Domain\Size;
use PHPUnit\Framework\TestCase;

final class SearchProductsTest extends TestCase
{
    private SearchProducts $searchProducts;

    protected function setUp(): void
    {
        $palms = new Category('palmiers', 'Palmiers', 1);
        $ferns = new Category('fougeres', 'Fougères', 2);
        $products = [
            new Product('palmier-nain', 'Palmier nain', $palms, Price::fromCents(4490), Exposure::Sun, Size::Medium, false, 1),
            new Product('fougere-de-boston', 'Fougère de Boston', $ferns, Price::fromCents(1590), Exposure::Shade, Size::Medium, true, 2),
        ];

        $this->searchProducts = new SearchProducts(
            new readonly class($products) implements ProductRepository {
                /** @param list<Product> $products */
                public function __construct(private array $products)
                {
                }

                public function findAll(): array
                {
                    return $this->products;
                }
            },
            new readonly class([$palms, $ferns]) implements CategoryRepository {
                /** @param list<Category> $categories */
                public function __construct(private array $categories)
                {
                }

                public function findAll(): array
                {
                    return $this->categories;
                }
            },
            new ProductSearch(),
        );
    }

    public function testSearchesTheCatalogLoadedThroughThePorts(): void
    {
        $result = ($this->searchProducts)(new ProductCriteria(categorySlug: 'fougeres'));

        self::assertSame(1, $result->totalItems);
        self::assertSame('fougere-de-boston', $result->items[0]->getSlug());
    }

    public function testUnknownCategoryIsReportedByTheDomain(): void
    {
        $this->expectException(CategoryNotFound::class);

        ($this->searchProducts)(new ProductCriteria(categorySlug: 'cactus'));
    }
}
