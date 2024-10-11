<?php
/**
 * Temporal REST Client
 *
 * @author Valentin Nazarov <v.nazarov@pos-credit.ru>
 * @copyright Copyright (c) 2024, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\Temporal\Infrastructure\HttpClient\Exception;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class NotFoundException extends TemporalRestException
{
    public static function create(Response $response, Request $request): self
    {
        return new self($response, $request, 'Not Found');
    }
}
