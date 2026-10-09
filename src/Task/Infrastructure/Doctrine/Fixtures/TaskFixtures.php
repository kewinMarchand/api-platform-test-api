<?php

declare(strict_types=1);

namespace App\Task\Infrastructure\Doctrine\Fixtures;

use App\Task\Domain\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(new Task('Brancher la vraie API'));
        $manager->persist(new Task('Générer les types OpenAPI'));
        $manager->persist(new Task('Lancer make qa', done: true));
        $manager->flush();
    }
}
