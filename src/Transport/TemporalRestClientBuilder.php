<?php
/**
 * Temporal REST Client
 *
 * @author Valentin Nazarov <v.nazarov@pos-credit.ru>
 * @copyright Copyright (c) 2024, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\Temporal\Transport;

use Psr\Http\Client\ClientInterface as PsrHttpClient;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface as Serializer;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\ConfigurationClient;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\HttpClient;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\Middleware\ClientErrorMiddleware;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\Middleware\InternalServerMiddleware;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\Middleware\Middleware;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\Middleware\PipelineMiddleware;
use Vanta\Integration\Temporal\Infrastructure\HttpClient\Middleware\UrlMiddleware;
use Vanta\Integration\Temporal\TemporalClient;

final readonly class TemporalRestClientBuilder
{
    /**
     * @param non-empty-list<Middleware> $middlewares
     */
    private function __construct(
        private PsrHttpClient $client,
        private Serializer $serializer,
        private ConfigurationClient $configuration,
        private array $middlewares,
    ) {
    }

    public static function create(PsrHttpClient $client, ConfigurationClient $configuration): self
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $objectNormalizer     = new ObjectNormalizer(
            $classMetadataFactory,
            new MetadataAwareNameConverter($classMetadataFactory),
            null,
            new PropertyInfoExtractor(
                [],
                [new PhpStanExtractor()],
                [],
                [],
                [],
            ),
            new ClassDiscriminatorFromClassMetadata($classMetadataFactory),
        );

        $normalizers = [
            new UnwrappingDenormalizer(),
            new BackedEnumNormalizer(),
            new DateTimeNormalizer(),
            new UidNormalizer(),
            new BackedEnumNormalizer(),
            $objectNormalizer,
            new ArrayDenormalizer(),
        ];

        $middlewares = [
            new UrlMiddleware(),
            new ClientErrorMiddleware(),
            new InternalServerMiddleware(),
        ];

        return new self(
            $client,
            new SymfonySerializer($normalizers, [new JsonEncoder()]),
            $configuration,
            $middlewares,
        );
    }

    public function addMiddleware(Middleware $middleware): self
    {
        return new self(
            client: $this->client,
            serializer: $this->serializer,
            configuration: $this->configuration,
            middlewares: array_merge($this->middlewares, [$middleware]),
        );
    }

    /**
     * @param non-empty-list<Middleware> $middlewares
     */
    public function withMiddlewares(array $middlewares): self
    {
        return new self(
            client: $this->client,
            serializer: $this->serializer,
            configuration: $this->configuration,
            middlewares: $middlewares,
        );
    }

    public function withSerializer(Serializer $serializer): self
    {
        return new self(
            client: $this->client,
            serializer: $this->serializer,
            configuration: $this->configuration,
            middlewares: $this->middlewares,
        );
    }

    public function withClient(PsrHttpClient $client): self
    {
        return new self(
            client: $client,
            serializer: $this->serializer,
            configuration: $this->configuration,
            middlewares: $this->middlewares,
        );
    }

    public function createRestClient(): TemporalClient
    {
        return new TemporalRestClient(
            $this->serializer,
            new HttpClient(
                $this->configuration,
                new PipelineMiddleware($this->middlewares, $this->client),
            ),
            $this->configuration->namespace,
        );
    }
}
