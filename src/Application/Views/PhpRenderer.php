<?php

declare(strict_types=1);

namespace App\Application\Views;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

final class PhpRenderer
{
    private string $templatesPath;

    /**
     * @param string $templatesPath
     * @param array<string, mixed> $globals
     */
    public function __construct(
        string $templatesPath = __DIR__ . '/../../../templates',
        private array $globals = []
    ) {
        $this->templatesPath = rtrim($templatesPath, '/\\');
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param ResponseInterface $response
     * @param string $template
     * @param array<string, mixed> $data
     * @param string|null $layout
     * @return ResponseInterface
     */
    public function render(
        ResponseInterface $response,
        string $template,
        array $data = [],
        ?string $layout = 'layouts/main.php'
    ): ResponseInterface {
        $mergedData = array_merge($this->globals, $data);
        $content = $this->renderFile($template, $mergedData);

        if ($layout !== null) {
            $output = $this->renderFile($layout, array_merge($mergedData, ['content' => $content]));
        } else {
            $output = $content;
        }

        $response->getBody()->write($output);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * @param string $relativePath
     * @param array<string, mixed> $variables
     * @return string
     */
    private function renderFile(string $relativePath, array $variables): string
    {
        $cleanPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if ($cleanPath === '' || str_contains($cleanPath, '..')) {
            throw new RuntimeException("Path template tidak valid: {$relativePath}");
        }

        $fullPath = $this->templatesPath
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);

        if (!is_file($fullPath)) {
            throw new RuntimeException("Template tidak ditemukan: {$relativePath}");
        }

        $includeIsolated = static function (string $file, array $vars): string {
            extract($vars, EXTR_SKIP);
            ob_start();
            try {
                include $file;
                return (string) ob_get_clean();
            } catch (Throwable $e) {
                ob_end_clean();
                throw $e;
            }
        };

        return $includeIsolated($fullPath, $variables);
    }
}
