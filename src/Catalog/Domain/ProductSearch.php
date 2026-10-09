<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

/**
 * Filtre, compte, trie et pagine les produits en mémoire. Le catalogue de test tient en
 * quelques dizaines de lignes : un calcul en PHP garde les facettes disjonctives lisibles.
 *
 * Comptage disjonctif : le compteur d'une valeur de facette applique tous les filtres actifs
 * sauf ceux de sa propre facette, pour indiquer ce que donnerait l'ajout de cette valeur.
 */
final class ProductSearch
{
    /**
     * @param list<Product>  $products
     * @param list<Category> $categories
     */
    public function search(array $products, array $categories, ProductCriteria $criteria): ProductSearchResult
    {
        $current = null === $criteria->categorySlug ? null : $this->findCategory($categories, $criteria->categorySlug);
        $branch = $this->branchSlugs($categories, $current);

        $inBranch = static fn (Product $product): bool => isset($branch[$product->getCategory()->getSlug()]);
        $matchesExposure = static fn (Product $product): bool => [] === $criteria->exposures || \in_array($product->getExposure(), $criteria->exposures, true);
        $matchesSize = static fn (Product $product): bool => [] === $criteria->sizes || \in_array($product->getSize(), $criteria->sizes, true);
        $matchesOthers = static fn (Product $product): bool => (null === $criteria->priceMin || $product->getPrice() >= $criteria->priceMin)
            && (null === $criteria->priceMax || $product->getPrice() <= $criteria->priceMax)
            && (!$criteria->inStockOnly || $product->isInStock());

        $results = array_values(array_filter(
            $products,
            static fn (Product $product): bool => $inBranch($product) && $matchesExposure($product) && $matchesSize($product) && $matchesOthers($product),
        ));

        $exposureCounts = [];
        foreach (Exposure::cases() as $exposure) {
            $exposureCounts[$exposure->value] = \count(array_filter(
                $products,
                static fn (Product $product): bool => $product->getExposure() === $exposure && $inBranch($product) && $matchesSize($product) && $matchesOthers($product),
            ));
        }

        $sizeCounts = [];
        foreach (Size::cases() as $size) {
            $sizeCounts[$size->value] = \count(array_filter(
                $products,
                static fn (Product $product): bool => $product->getSize() === $size && $inBranch($product) && $matchesExposure($product) && $matchesOthers($product),
            ));
        }

        $categoryCounts = [];
        foreach ($this->children($categories, $current) as $child) {
            $childBranch = $this->branchSlugs($categories, $child);
            $categoryCounts[] = ['category' => $child, 'count' => \count(array_filter(
                $products,
                static fn (Product $product): bool => isset($childBranch[$product->getCategory()->getSlug()]) && $matchesExposure($product) && $matchesSize($product) && $matchesOthers($product),
            ))];
        }

        $sorted = $this->sort($results, $criteria->sort);
        $items = \array_slice($sorted, ($criteria->page - 1) * $criteria->itemsPerPage, $criteria->itemsPerPage);

        return new ProductSearchResult($items, \count($results), $exposureCounts, $sizeCounts, $categoryCounts);
    }

    /**
     * @param list<Category> $categories
     */
    private function findCategory(array $categories, string $slug): Category
    {
        foreach ($categories as $category) {
            if ($category->getSlug() === $slug) {
                return $category;
            }
        }

        throw new CategoryNotFound($slug);
    }

    /**
     * @param list<Category> $categories
     *
     * @return list<Category>
     */
    private function children(array $categories, ?Category $parent): array
    {
        $children = array_values(array_filter($categories, static fn (Category $category): bool => $category->getParent() === $parent));
        usort($children, static fn (Category $a, Category $b): int => $a->getPosition() <=> $b->getPosition());

        return $children;
    }

    /**
     * @param list<Category> $categories
     *
     * @return array<string, true> slugs de la catégorie et de toutes ses descendantes (tout le catalogue si null)
     */
    private function branchSlugs(array $categories, ?Category $root): array
    {
        $slugs = [];
        foreach ($categories as $category) {
            for ($ancestor = $category; null !== $ancestor; $ancestor = $ancestor->getParent()) {
                if (null === $root || $ancestor === $root) {
                    $slugs[$category->getSlug()] = true;
                    break;
                }
            }
        }

        return $slugs;
    }

    /**
     * @param list<Product> $products
     *
     * @return list<Product>
     */
    private function sort(array $products, ?ProductSort $sort): array
    {
        if (null === $sort) {
            return $products;
        }

        $collator = new \Collator('fr_FR');
        usort($products, static fn (Product $a, Product $b): int => match ($sort) {
            ProductSort::PriceAsc => $a->getPrice() <=> $b->getPrice(),
            ProductSort::PriceDesc => $b->getPrice() <=> $a->getPrice(),
            ProductSort::NameAsc => (int) $collator->compare($a->getName(), $b->getName()),
            ProductSort::NameDesc => (int) $collator->compare($b->getName(), $a->getName()),
        });

        return $products;
    }
}
