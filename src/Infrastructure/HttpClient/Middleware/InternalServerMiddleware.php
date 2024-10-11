<?php
/**
 * Temporal REST Client
 *
 * @author Valentin Nazarov <v.nazarov@pos-credit.ru>
 * @copyright Copyright (c) 2024, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\Temporal\Infrastructure\HttpClient\Middleware;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\ConfigurationClient;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\Exception\InternalServerErrorException;
use Yiisoft\Http\Status;

final readonly class InternalServerMiddleware implements Middleware
{
    public function process(Request $request, ConfigurationClient $configuration, callable $next): Response
    {
        $response   = $next($request, $configuration);
        $statusCode = $response->getStatusCode();

        if (!(Status::INTERNAL_SERVER_ERROR <= $statusCode && $statusCode <= Status::NETWORK_AUTHENTICATION_REQUIRED)) {
            return $response;
        }

        throw InternalServerErrorException::create($response, $request);
    }
}
