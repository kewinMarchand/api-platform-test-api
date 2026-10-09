<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Doctrine;

use App\Blog\Domain\Article;
use App\Blog\Domain\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineArticleRepository implements ArticleRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findLatestFirst(): array
    {
        return $this->entityManager->getRepository(Article::class)->findBy([], ['publishedAt' => 'DESC', 'id' => 'DESC']);
    }
}
