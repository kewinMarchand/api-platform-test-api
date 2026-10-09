<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Blog\Domain\Article;
use App\Blog\Infrastructure\ApiPlatform\State\ArticleCollectionProvider;

#[ApiResource(
    shortName: 'Article',
    description: 'Article du blog affiché sur la page d\'accueil.',
    operations: [
        new GetCollection(
            description: 'Liste les articles, du plus récent au plus ancien, sans pagination.',
            provider: ArticleCollectionProvider::class,
        ),
    ],
)]
final readonly class ArticleResource
{
    public function __construct(
        #[ApiProperty(identifier: true, required: true)]
        public int $id,
        #[ApiProperty(required: true)]
        public string $slug,
        #[ApiProperty(required: true)]
        public string $title,
        #[ApiProperty(required: true)]
        public string $excerpt,
        #[ApiProperty(description: 'Date de publication (AAAA-MM-JJ).', required: true, schema: ['type' => 'string', 'format' => 'date'])]
        public string $publishedAt,
    ) {
    }

    public static function fromModel(Article $article): self
    {
        return new self(
            (int) $article->getId(),
            $article->getSlug(),
            $article->getTitle(),
            $article->getExcerpt(),
            $article->getPublishedAt()->format('Y-m-d'),
        );
    }
}
