<?php

declare(strict_types=1);

namespace Tests\Application\Middleware;

use App\Application\Middleware\RequireJsonMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;
use Tests\TestCase;

class RequireJsonMiddlewareTest extends TestCase
{
    private function createHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new SlimResponse(200);
            }
        };
    }

    public function testGetAndHeadPassWithoutContentType(): void
    {
        $middleware = new RequireJsonMiddleware();

        $getRequest = $this->createRequest('GET', '/test');
        $this->assertSame(200, $middleware->process($getRequest, $this->createHandler())->getStatusCode());

        $headRequest = $this->createRequest('HEAD', '/test');
        $this->assertSame(200, $middleware->process($headRequest, $this->createHandler())->getStatusCode());
    }

    public function testPostWithoutOrWithNonJsonContentTypeReturns415(): void
    {
        $middleware = new RequireJsonMiddleware();

        $noTypeRequest = $this->createRequest('POST', '/test');
        $this->assertSame(415, $middleware->process($noTypeRequest, $this->createHandler())->getStatusCode());

        $htmlRequest = $this->createRequest('POST', '/test', ['Content-Type' => 'text/html']);
        $this->assertSame(415, $middleware->process($htmlRequest, $this->createHandler())->getStatusCode());

        $formRequest = $this->createRequest('POST', '/test', ['Content-Type' => 'application/x-www-form-urlencoded']);
        $this->assertSame(415, $middleware->process($formRequest, $this->createHandler())->getStatusCode());
    }

    public function testPostWithApplicationJsonPasses(): void
    {
        $middleware = new RequireJsonMiddleware();

        $jsonRequest = $this->createRequest('POST', '/test', ['Content-Type' => 'application/json']);
        $this->assertSame(200, $middleware->process($jsonRequest, $this->createHandler())->getStatusCode());

        $jsonCharsetRequest = $this->createRequest(
            'POST',
            '/test',
            ['Content-Type' => 'application/json; charset=utf-8']
        );
        $this->assertSame(200, $middleware->process($jsonCharsetRequest, $this->createHandler())->getStatusCode());
    }
}
