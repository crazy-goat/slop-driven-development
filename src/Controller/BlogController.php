<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(defaults: ['_format' => 'html'])]
class BlogController extends AbstractController
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

    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        $defaultLang = $this->getParameter('default_lang') ?? 'pl';
        return $this->redirectToRoute('blog_index', ['lang' => $defaultLang], Response::HTTP_FOUND);
    }

    #[Route('/{lang}', name: 'blog_index', methods: ['GET'], requirements: ['lang' => '[a-z]{2,3}'])]
    public function index(string $lang): Response
    {
        $repo = $this->getRepository();
        $articles = $repo->findByLang($lang);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'lang' => $lang,
            'available_langs' => $repo->getAvailableLanguages(),
        ]);
    }

    #[Route('/{lang}/{year}/{month}/{slug}', name: 'blog_show', methods: ['GET'], requirements: ['lang' => '[a-z]{2,3}', 'year' => '\d{4}', 'month' => '\d{2}'])]
    public function show(Request $request, string $lang, string $year, string $month, string $slug): Response
    {
        $repo = $this->getRepository();
        $article = $repo->findBySlug($slug, $lang);

        if ($article === null) {
            throw $this->createNotFoundException('Artykuł nie został znaleziony.');
        }

        $translations = $repo->findTranslations($article->translationKey);

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'translations' => $translations,
            'lang' => $lang,
            'currentUrl' => $request->getUri(),
            'available_langs' => $repo->getAvailableLanguages(),
        ]);
    }

    #[Route('/{lang}/tag/{tag}', name: 'blog_tag', methods: ['GET'], requirements: ['lang' => '[a-z]{2,3}'])]
    public function byTag(string $lang, string $tag): Response
    {
        $repo = $this->getRepository();
        $articles = $repo->findByTag($tag, $lang);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'lang' => $lang,
            'tag' => $tag,
            'available_langs' => $repo->getAvailableLanguages(),
        ]);
    }
}
