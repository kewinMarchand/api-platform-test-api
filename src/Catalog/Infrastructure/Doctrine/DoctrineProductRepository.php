<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine;

use App\Catalog\Domain\Product;
use App\Catalog\Domain\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProductRepository implements ProductRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findAll(): array
    {
        /* @var list<Product> */
        return $this->entityManager->createQueryBuilder()
            ->select('product', 'category')
            ->from(Product::class, 'product')
            ->join('product.category', 'category')
            ->orderBy('product.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
