<?php
/**
 * Temporal REST Client
 *
 * @author Valentin Nazarov <v.nazarov@pos-credit.ru>
 * @copyright Copyright (c) 2024, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\Temporal\Transport;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface as HttpClient;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\SerializerInterface as Serializer;
use Vanta\Integration\Temporal\TemporalClient;
use Yiisoft\Http\Method;

final readonly class TemporalRestClient implements TemporalClient
{
    /**
     * @param non-empty-string $namespace
     */
    public function __construct(
        private Serializer $serializer,
        private HttpClient $client,
        private string $namespace,
    ) {
    }

    /**
     * @param non-empty-string $namespace
     */
    public function withNamespace(string $namespace): self
    {
        return new self($this->serializer, $this->client, $namespace);
    }

    public function getWorkflowsCount(string $query): int
    {
        $url     = sprintf('/api/v1/namespaces/%s/workflow-count?%s', $this->namespace, http_build_query(['query' => $query]));
        $request = new Request(Method::GET, $url);
        $content = $this->client->sendRequest($request)->getBody()->__toString();

        // NB: API returns number as string, deserializing directly into `int` throws `NotNormalizableValueException`.
        return intval($this->serializer->deserialize($content, 'string', 'json', [
            UnwrappingDenormalizer::UNWRAP_PATH => '[count]',
        ]));
    }
}
