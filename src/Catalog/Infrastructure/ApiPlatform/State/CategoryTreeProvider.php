<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Catalog\Domain\Category;
use App\Catalog\Domain\CategoryRepository;
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
        return $this->childrenOf($this->categoryRepository->findAll(), null);
    }

    /**
     * @param list<Category> $categories triées par position
     *
     * @return list<CategoryResource>
     */
    private function childrenOf(array $categories, ?Category $parent): array
    {
        $children = [];
        foreach ($categories as $category) {
            if ($category->getParent() === $parent) {
                $children[] = new CategoryResource(
                    $category->getSlug(),
                    $category->getName(),
                    $parent?->getSlug(),
                    $this->childrenOf($categories, $category),
                );
            }
        }

        return $children;
    }
}
