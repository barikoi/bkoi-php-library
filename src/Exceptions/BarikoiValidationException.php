<?php

namespace Barikoi\BarikoiApis\Exceptions;

use Illuminate\Http\Client\Response;

/**
 * Exception thrown when input validation fails
 */
class BarikoiValidationException extends BarikoiException
{
    protected array $validationErrors = [];

    public static function fromResponse(Response $response): self
    {
        $data = $response->json() ?? [];
        $apiMessage = $data['message'] ?? 'Validation failed';

        // Extract validation errors if available
        $errors = $data['errors'] ?? [];

        $message = self::formatValidationMessage($apiMessage, $errors);

        $exception = new self($message, 400, $response);
        $exception->validationErrors = $errors;

        return $exception;
    }

    protected static function formatValidationMessage(string $apiMessage, array $errors): string
    {
        $message = "Validation Error: {$apiMessage}";

        if (!empty($errors)) {
            $message .= "\nDetails:";
            foreach ($errors as $field => $fieldErrors) {
                $errorList = is_array($fieldErrors) ? implode(', ', $fieldErrors) : $fieldErrors;
                $message .= "\n  - {$field}: {$errorList}";
            }
        }

        return $message;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
