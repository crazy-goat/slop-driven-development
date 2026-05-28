<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_format' => 'txt'])]
class AssetController
{
    #[Route('/fonts/{name}', name: 'font', methods: ['GET'], requirements: ['name' => '.+\.woff2$'])]
    public function font(string $name): Response
    {
        $path = __DIR__ . '/../../public/fonts/' . $name;

        if (!is_file($path)) {
            return new Response('Not Found', 404);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return new Response('Not Found', 404);
        }

        return new Response($content, 200, [
            'Content-Type' => 'font/woff2',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    #[Route('/favicon.svg', name: 'favicon', methods: ['GET'])]
    public function favicon(): Response
    {
        $path = __DIR__ . '/../../public/favicon.svg';

        if (!is_file($path)) {
            return new Response('Not Found', 404);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return new Response('Not Found', 404);
        }

        return new Response($content, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
