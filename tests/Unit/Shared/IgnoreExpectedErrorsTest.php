<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared;

use App\Catalog\Domain\CategoryNotFound;
use App\Shared\Infrastructure\ApiPlatform\Scenario\SimulatedServerError;
use App\Shared\Infrastructure\Observability\IgnoreExpectedErrors;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sentry\Event;
use Sentry\EventHint;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class IgnoreExpectedErrorsTest extends TestCase
{
    #[DataProvider('exceptions')]
    public function testKeepsOnlyUnexpectedErrors(\Throwable $exception, bool $sent): void
    {
        $filter = new IgnoreExpectedErrors([CategoryNotFound::class => 404]);
        $event = Event::createEvent();

        self::assertSame($sent ? $event : null, $filter($event, EventHint::fromArray(['exception' => $exception])));
    }

    /**
     * @return iterable<string, array{\Throwable, bool}>
     */
    public static function exceptions(): iterable
    {
        yield '404' => [new NotFoundHttpException(), false];
        yield '405' => [new MethodNotAllowedHttpException(['GET']), false];
        yield 'catégorie inconnue (exception_to_status)' => [new CategoryNotFound('cactus'), false];
        yield 'X-Scenario: error' => [new SimulatedServerError(), false];
        yield 'exception inattendue' => [new \RuntimeException('Panne'), true];
    }
}
