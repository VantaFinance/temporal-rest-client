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
     * @param non-empty-string $namespace
     */
    public function withNamespace(string $namespace): self;

    /**
     * @param non-empty-string $query
     */
    public function getWorkflowsCount(string $query): int;
}
