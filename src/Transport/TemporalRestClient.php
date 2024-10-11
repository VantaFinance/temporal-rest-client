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
use Symfony\Component\Serializer\SerializerInterface as Serializer;
use Vanta\Integration\Temporal\Response\WorkflowCountResponse;
use Vanta\Integration\Temporal\TemporalClient;
use Yiisoft\Http\Method;

final readonly class TemporalRestClient implements TemporalClient
{
    public function __construct(
        private Serializer $serializer,
        private HttpClient $client,
    ) {
    }

    public function getWorkflowsCount(string $type, array $attributes = [], string $namespace = 'default'): int
    {
        $query = [];
        foreach ($attributes + ['WorkflowType' => $type] as $k => $v) {
            $query[] = sprintf('%s="%s"', $k, $v);
        }
        $query = implode(' AND ', $query);
        $query = http_build_query(['query' => $query]);

        $request = new Request(Method::GET, sprintf('/api/v1/namespaces/%s/workflow-count?%s', $namespace, $query));
        $content = $this->client->sendRequest($request)->getBody()->__toString();
        $data    = $this->serializer->deserialize($content, WorkflowCountResponse::class, 'json');

        return $data->count;
    }
}
