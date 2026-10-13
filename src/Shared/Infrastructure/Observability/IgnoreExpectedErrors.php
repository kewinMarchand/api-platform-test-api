<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Observability;

use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use App\Shared\Infrastructure\ApiPlatform\Scenario\SimulatedServerError;
use Sentry\Event;
use Sentry\EventHint;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Filtre before_send du SDK Sentry : seules les erreurs inattendues partent vers GlitchTip.
 * Une réponse 4xx (404, 405, 422 de validation, exception métier mappée par exception_to_status)
 * et les erreurs simulées par X-Scenario sont des réponses prévues, pas des incidents.
 */
final readonly class IgnoreExpectedErrors
{
    /**
     * @param array<class-string, int> $exceptionToStatus
     */
    public function __construct(
        #[Autowire(param: 'api_platform.exception_to_status')]
        private array $exceptionToStatus,
    ) {
    }

    public function __invoke(Event $event, ?EventHint $hint): ?Event
    {
        $exception = $hint?->exception;

        return null !== $exception && $this->isExpected($exception) ? null : $event;
    }

    private function isExpected(\Throwable $exception): bool
    {
        if ($exception instanceof SimulatedServerError) {
            return true;
        }

        $status = match (true) {
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof ProblemExceptionInterface => $exception->getStatus(),
            default => $this->mappedStatus($exception),
        };

        return null !== $status && $status < 500;
    }

    private function mappedStatus(\Throwable $exception): ?int
    {
        foreach ($this->exceptionToStatus as $class => $status) {
            if ($exception instanceof $class) {
                return $status;
            }
        }

        return null;
    }
}
