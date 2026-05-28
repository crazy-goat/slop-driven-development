<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(defaults: ['_format' => 'xml'])]
class SitemapController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ArticleRepository::class,
        ]);
    }

    private function getRepository(): ArticleRepository
    {
        return $this->container->get(ArticleRepository::class);
    }

    #[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function sitemap(): Response
    {
        $repo = $this->getRepository();
        $articles = $repo->findAll();

        // Group by translation_key so each entry shows all lang alternates
        $grouped = [];
        $seen = [];
        foreach ($articles as $article) {
            $key = $article->translationKey;
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $grouped[] = [
                    'article' => $article,
                    'translations' => $repo->findTranslations($key),
                ];
            }
        }

        $response = $this->render('sitemap.xml.twig', [
            'groups' => $grouped,
        ]);

        $response->headers->set('Content-Type', 'application/xml; charset=utf-8');

        return $response;
    }

    #[Route('/robots.txt', name: 'robots', methods: ['GET'])]
    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\n\nSitemap: https://example.com/sitemap.xml\n";
        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
