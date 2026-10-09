<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Blog\Domain\ArticleRepository;
use App\Blog\Infrastructure\ApiPlatform\Resource\ArticleResource;

/**
 * @implements ProviderInterface<ArticleResource>
 */
final readonly class ArticleCollectionProvider implements ProviderInterface
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    /**
     * @return list<ArticleResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(ArticleResource::fromModel(...), $this->articleRepository->findLatestFirst());
    }
}
