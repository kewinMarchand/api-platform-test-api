<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\ApiPlatform\Scenario;

/**
 * Ressource non collection qui sait se représenter vide pour X-Scenario: empty.
 */
interface EmptyScenarioResult
{
    public static function emptyScenarioResult(): static;
}
