<?php

declare(strict_types=1);

namespace Tests\Application\Views;

use App\Application\Views\PhpRenderer;
use RuntimeException;
use Slim\Psr7\Factory\ResponseFactory;
use Tests\TestCase;

class PhpRendererTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bersatu_views_' . uniqid();
        mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'layouts', 0777, true);
        mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'pages', 0777, true);

        file_put_contents(
            $this->tempDir . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main.php',
            '<html><head><title><?= \App\Application\Views\PhpRenderer::e($title ?? "App") ?></title></head>'
            . '<body><main><?= $content ?></main></body></html>'
        );

        file_put_contents(
            $this->tempDir . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'hello.php',
            '<h1><?= \App\Application\Views\PhpRenderer::e($heading ?? "") ?></h1>'
        );
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    public function testRenderWrapsTemplateWithLayout(): void
    {
        $renderer = new PhpRenderer($this->tempDir, ['title' => 'Portal BERSATU']);
        $response = (new ResponseFactory())->createResponse();

        $result = $renderer->render($response, 'pages/hello.php', ['heading' => 'Kalimantan Utara']);

        $this->assertSame('text/html; charset=utf-8', $result->getHeaderLine('Content-Type'));
        $body = (string) $result->getBody();
        $this->assertStringContainsString('<title>Portal BERSATU</title>', $body);
        $this->assertStringContainsString('<main><h1>Kalimantan Utara</h1></main>', $body);
    }

    public function testRenderPartialWithoutLayout(): void
    {
        $renderer = new PhpRenderer($this->tempDir);
        $response = (new ResponseFactory())->createResponse();

        $result = $renderer->render($response, 'pages/hello.php', ['heading' => 'Parsial'], null);

        $this->assertSame('<h1>Parsial</h1>', (string) $result->getBody());
    }

    public function testEscapeHelperNeutralizesHtmlAndScripts(): void
    {
        $escaped = PhpRenderer::e('<script>alert("xss")</script>');

        $this->assertSame('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $escaped);
        $this->assertSame('', PhpRenderer::e(null));
    }

    public function testMissingTemplateThrowsRuntimeException(): void
    {
        $renderer = new PhpRenderer($this->tempDir);
        $response = (new ResponseFactory())->createResponse();

        $this->expectException(RuntimeException::class);
        $renderer->render($response, 'pages/tidak-ada.php');
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
