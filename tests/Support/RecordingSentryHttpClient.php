<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Sentry\HttpClient\HttpClientInterface;
use Sentry\HttpClient\Request;
use Sentry\HttpClient\Response;
use Sentry\Options;

/**
 * Client HTTP factice du SDK Sentry : garde les envois au lieu de les poster vers GlitchTip.
 * Liste statique, car chaque requête de test démarre son propre conteneur. Les tests la vident avant usage.
 */
final class RecordingSentryHttpClient implements HttpClientInterface
{
    /** @var list<Request> */
    public static array $requests = [];

    public function sendRequest(Request $request, Options $options): Response
    {
        self::$requests[] = $request;

        return new Response(200, [], '');
    }
}
