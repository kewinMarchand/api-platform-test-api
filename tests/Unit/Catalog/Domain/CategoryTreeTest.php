<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain;

use App\Catalog\Domain\Category;
use App\Catalog\Domain\CategoryNotFound;
use App\Catalog\Domain\CategoryTree;
use PHPUnit\Framework\TestCase;

final class CategoryTreeTest extends TestCase
{
    private Category $interior;
    private Category $foliage;
    private Category $monstera;
    private Category $aquatic;
    private CategoryTree $tree;

    protected function setUp(): void
    {
        $this->interior = new Category('plantes-interieur', 'Plantes d\'intérieur', 1);
        $this->foliage = new Category('feuillages', 'Feuillages', 2, $this->interior);
        $this->monstera = new Category('monstera', 'Monstera', 3, $this->foliage);
        $this->aquatic = new Category('plantes-aquatiques', 'Plantes aquatiques', 4);
        $this->tree = new CategoryTree([$this->aquatic, $this->monstera, $this->foliage, $this->interior]);
    }

    public function testFindsBySlugOrFails(): void
    {
        self::assertSame($this->foliage, $this->tree->find('feuillages'));

        $this->expectException(CategoryNotFound::class);
        $this->tree->find('cactus');
    }

    public function testListsDirectChildrenByPosition(): void
    {
        self::assertSame([$this->interior, $this->aquatic], $this->tree->childrenOf(null));
        self::assertSame([$this->foliage], $this->tree->childrenOf($this->interior));
        self::assertSame([], $this->tree->childrenOf($this->monstera));
    }

    public function testBranchContainsTheCategoryAndAllDescendants(): void
    {
        self::assertSame(['monstera' => true, 'feuillages' => true, 'plantes-interieur' => true], $this->tree->branchSlugs($this->interior));
        self::assertCount(4, $this->tree->branchSlugs(null));
    }
}
