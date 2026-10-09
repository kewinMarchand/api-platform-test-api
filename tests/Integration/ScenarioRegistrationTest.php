<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Kernel;
use App\Shared\Infrastructure\ApiPlatform\Scenario\ScenarioProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ScenarioRegistrationTest extends TestCase
{
    #[DataProvider('environments')]
    public function testScenarioProviderIsRegisteredOutsideProductionOnly(string $environment, bool $registered): void
    {
        $kernel = new class($environment, false) extends Kernel {
            public function containerBuilder(): ContainerBuilder
            {
                $this->initializeBundles();

                return $this->buildContainer();
            }
        };

        $container = $kernel->containerBuilder();
        $isRegistered = $container->hasDefinition(ScenarioProvider::class)
            && !$container->getDefinition(ScenarioProvider::class)->hasTag('container.excluded');

        self::assertSame($registered, $isRegistered);
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function environments(): iterable
    {
        yield 'dev' => ['dev', true];
        yield 'test' => ['test', true];
        yield 'prod' => ['prod', false];
    }
}
