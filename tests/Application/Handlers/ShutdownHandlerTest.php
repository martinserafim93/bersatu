<?php

declare(strict_types=1);

namespace Tests\Application\Handlers;

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use Prophecy\Argument;
use Tests\TestCase;

class ShutdownHandlerTest extends TestCase
{
    public function testIgnoresNonFatalDeprecationInErrorGetLast(): void
    {
        $request = $this->createRequest('GET', '/api/master/wilayah');
        $errorHandler = $this->prophesize(HttpErrorHandler::class);
        $errorHandler->__invoke(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->shouldNotBeCalled();

        /** @var HttpErrorHandler $handlerInstance */
        $handlerInstance = $errorHandler->reveal();
        $shutdownHandler = new ShutdownHandler($request, $handlerInstance, true);

        error_clear_last();
        @trigger_error('Legacy vendor deprecation notice', E_USER_DEPRECATED);

        try {
            $shutdownHandler();
            $this->assertTrue(true);
        } finally {
            error_clear_last();
        }
    }
}
