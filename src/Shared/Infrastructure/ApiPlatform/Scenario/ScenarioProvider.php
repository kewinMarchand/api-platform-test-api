<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Scenario;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Simule un état de l'API pour les tests des fronts, via l'en-tête X-Scenario.
 * Enregistré en dev et en test uniquement : en prod l'en-tête n'a aucun effet.
 * La priorité 400 place ce décorateur juste avant la lecture, après la négociation de contenu.
 * Seule la requête principale est concernée : la sous-requête qui rend l'erreur reprend les
 * en-têtes et relancerait l'exception, ce qui ferait retomber Symfony sur sa page HTML.
 *
 * @implements ProviderInterface<object>
 */
#[When(env: 'dev')]
#[When(env: 'test')]
#[AsDecorator(decorates: 'api_platform.state_provider.main', priority: 400)]
final readonly class ScenarioProvider implements ProviderInterface
{
    public const string HEADER = 'X-Scenario';

    /**
     * @param ProviderInterface<object> $inner
     */
    public function __construct(
        #[AutowireDecorated]
        private ProviderInterface $inner,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || $request !== $this->requestStack->getMainRequest()) {
            return $this->inner->provide($operation, $uriVariables, $context);
        }

        $scenario = Scenario::tryFrom((string) $request->headers->get(self::HEADER));

        if (Scenario::Error === $scenario) {
            throw new SimulatedServerError();
        }

        if (Scenario::Empty === $scenario) {
            if ($operation instanceof CollectionOperationInterface) {
                return [];
            }

            $class = $operation->getClass();
            if (null !== $class && is_subclass_of($class, EmptyScenarioResult::class)) {
                return $class::emptyScenarioResult();
            }
        }

        return $this->inner->provide($operation, $uriVariables, $context);
    }
}
