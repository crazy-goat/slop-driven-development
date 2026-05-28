<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AssetController
{
    private function serve(string $subpath, string $mimeType): Response
    {
        $path = __DIR__ . '/../../public/' . $subpath;
        if (!is_file($path)) {
            return new Response('Not Found', 404);
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return new Response('Not Found', 404);
        }
        return new Response($content, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    #[Route('/img/{name}', name: 'asset_img', methods: ['GET'], requirements: ['name' => '.+\.(png|jpg|jpeg|gif|svg|webp)$'])]
    public function image(string $name): Response
    {
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        return $this->serve('img/' . $name, $mimeTypes[$ext] ?? 'application/octet-stream');
    }

    #[Route('/fonts/{name}', name: 'asset_font', methods: ['GET'], requirements: ['name' => '.+\.woff2$'])]
    public function font(string $name): Response
    {
        return $this->serve('fonts/' . $name, 'font/woff2');
    }

    #[Route('/favicon.svg', name: 'asset_favicon', methods: ['GET'])]
    public function favicon(): Response
    {
        return $this->serve('favicon.svg', 'image/svg+xml');
    }
}
