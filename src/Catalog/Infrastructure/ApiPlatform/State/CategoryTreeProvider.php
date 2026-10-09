<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Catalog\Domain\Category;
use App\Catalog\Domain\CategoryRepository;
use App\Catalog\Domain\CategoryTree;
use App\Catalog\Infrastructure\ApiPlatform\Resource\CategoryResource;

/**
 * @implements ProviderInterface<CategoryResource>
 */
final readonly class CategoryTreeProvider implements ProviderInterface
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    /**
     * @return list<CategoryResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->nodes(new CategoryTree($this->categoryRepository->findAll()), null);
    }

    /**
     * @return list<CategoryResource>
     */
    private function nodes(CategoryTree $tree, ?Category $parent): array
    {
        return array_map(
            fn (Category $category): CategoryResource => new CategoryResource(
                $category->getSlug(),
                $category->getName(),
                $parent?->getSlug(),
                $this->nodes($tree, $category),
            ),
            $tree->childrenOf($parent),
        );
    }
}
