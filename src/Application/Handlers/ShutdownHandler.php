<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\ResponseEmitter\ResponseEmitter;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;

class ShutdownHandler
{
    private Request $request;

    private HttpErrorHandler $errorHandler;

    private bool $displayErrorDetails;

    public function __construct(
        Request $request,
        HttpErrorHandler $errorHandler,
        bool $displayErrorDetails
    ) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function __invoke()
    {
        $error = error_get_last();
        $fatalTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR,
            E_RECOVERABLE_ERROR,
        ];

        // Ignore non-fatal notices/deprecations left in error_get_last()
        // so buffered 200 responses are never replaced with HTTP 500.
        if (!$error || !in_array($error['type'], $fatalTypes, true)) {
            return;
        }

        $errorFile = $error['file'];
        $errorLine = $error['line'];
        $errorMessage = $error['message'];
        $errorType = $error['type'];
        $message = 'An error while processing your request. Please try again later.';

        if ($this->displayErrorDetails) {
            if ($errorType === E_USER_ERROR) {
                $message = "FATAL ERROR: {$errorMessage}. ";
                $message .= " on line {$errorLine} in file {$errorFile}.";
            } else {
                $message = "ERROR: {$errorMessage}";
                $message .= " on line {$errorLine} in file {$errorFile}.";
            }
        }

        $exception = new HttpInternalServerErrorException($this->request, $message);
        $response = $this->errorHandler->__invoke(
            $this->request,
            $exception,
            $this->displayErrorDetails,
            false,
            false,
        );

        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }
}
