<?php
/**
 * Temporal REST Client
 *
 * @author Valentin Nazarov <v.nazarov@pos-credit.ru>
 * @copyright Copyright (c) 2024, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\Temporal\Infrastructure\HttpClient;

use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\Middleware\PipelineMiddleware;

final readonly class HttpClient implements PsrHttpClient
{
    public function __construct(
        private ConfigurationClient $configuration,
        private PipelineMiddleware $pipeline
    ) {
    }

    public function sendRequest(Request $request): Response
    {
        return $this->pipeline->process($request, $this->configuration);
    }
}
