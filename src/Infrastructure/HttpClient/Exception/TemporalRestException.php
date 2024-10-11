<?php
/**
 * Temporal REST Client
 *
 * @author Valentin Nazarov <v.nazarov@pos-credit.ru>
 * @copyright Copyright (c) 2024, The Vanta
 */

declare(strict_types=1);

namespace Vanta\Integration\Temporal\Infrastructure\HttpClient\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface as ClientException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Throwable;

abstract class TemporalRestException extends Exception implements ClientException
{
    public readonly Request $request;

    public readonly Response $response;

    final protected function __construct(
        Response $response,
        Request $request,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->response = $response;
        $this->request  = $request;

        parent::__construct($message, $code, $previous);
    }
}
