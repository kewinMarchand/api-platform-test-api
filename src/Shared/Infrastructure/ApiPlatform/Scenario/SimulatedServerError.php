<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Scenario;

final class SimulatedServerError extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Erreur serveur simulée par l\'en-tête X-Scenario: error.');
    }
}
