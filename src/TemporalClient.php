<?php
/**
 * Temporal REST Client
 *
 * @author Valentin Nazarov <v.nazarov@pos-credit.ru>
 * @copyright Copyright (c) 2024, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\Temporal;

interface TemporalClient
{
    /**
     * @param non-empty-string|class-string $type
     * @param array<string, mixed> $attributes
     * @param non-empty-string $namespace
     */
    public function getWorkflowsCount(
        string $type,
        array $attributes = [],
        string $namespace = 'default',
    ): int;
}
