<?php

namespace Vendor\BarikoiApi\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

/**
 * Base exception for all Barikoi API errors
 */
class BarikoiException extends Exception
{
    protected ?Response $response = null;
    protected array $errorData = [];

    public function __construct(string $message, int $code = 0, ?Response $response = null)
    {
        parent::__construct($message, $code);
        $this->response = $response;

        if ($response) {
            $this->errorData = $response->json() ?? [];
        }
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getErrorData(): array
    {
        return $this->errorData;
    }

    public function getErrorMessage(): string
    {
        return $this->errorData['message'] ?? $this->getMessage();
    }
}
