<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Doctrine\Fixtures;

use App\Blog\Domain\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(new Article(
            'pourquoi-rendre-le-contenu-cote-serveur',
            'Pourquoi rendre le contenu côté serveur',
            'Le contenu éditorial arrive dans le HTML initial : meilleur référencement et LCP.',
            new \DateTimeImmutable('2026-09-15'),
        ));
        $manager->persist(new Article(
            'un-carrousel-accessible-sans-dependance',
            'Un carrousel accessible sans dépendance',
            'Défilement natif, boutons explicites, pas de lecture automatique.',
            new \DateTimeImmutable('2026-09-22'),
        ));
        $manager->persist(new Article(
            'le-mode-accessibilite-renforcee',
            'Le mode accessibilité renforcée',
            'Texte agrandi, espacements WCAG 1.4.12 et contraste AAA, sans logique dupliquée.',
            new \DateTimeImmutable('2026-10-01'),
        ));
        $manager->flush();
    }
}
