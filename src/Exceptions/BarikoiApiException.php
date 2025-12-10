<?php

namespace Vendor\BarikoiApi\Exceptions;

use Illuminate\Http\Client\Response;

/**
 * Exception thrown when the Barikoi API returns an error response
 */
class BarikoiApiException extends BarikoiException
{
    public static function fromResponse(Response $response): self
    {
        $statusCode = $response->status();
        $data = $response->json() ?? [];
        $apiMessage = $data['message'] ?? 'Unknown API error';

        $message = self::formatErrorMessage($statusCode, $apiMessage, $data);

        return new self($message, $statusCode, $response);
    }

    protected static function formatErrorMessage(int $statusCode, string $apiMessage, array $data): string
    {
        switch ($statusCode) {
            case 400:
                return "Bad Request: {$apiMessage}. Please check your input parameters.";

            case 401:
                return "Authentication Failed: {$apiMessage}. Please verify your API key is correct.";

            case 403:
                return "Access Denied: {$apiMessage}. Your API key does not have permission for this operation.";

            case 404:
                return "Not Found: {$apiMessage}. The requested resource or endpoint does not exist.";

            case 429:
                return "Rate Limit Exceeded: {$apiMessage}. Please reduce the number of requests or try again later.";

            case 500:
                return "Server Error: {$apiMessage}. The Barikoi API is experiencing issues. Please try again later.";

            case 502:
            case 503:
            case 504:
                return "Service Unavailable: {$apiMessage}. The Barikoi API is temporarily unavailable. Please try again later.";

            default:
                return "API Error ({$statusCode}): {$apiMessage}";
        }
    }
}
