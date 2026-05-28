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
    public function __construct(
        private readonly ArticleRepository $repository,
    ) {}

    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        $defaultLang = $this->getParameter('default_lang') ?? 'pl';
        return $this->redirectToRoute('blog_index', ['lang' => $defaultLang], Response::HTTP_FOUND);
    }

    #[Route('/{lang}', name: 'blog_index', methods: ['GET'], requirements: ['lang' => '[a-z]{2,3}'])]
    public function index(string $lang): Response
    {
        $articles = $this->repository->findByLang($lang);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'lang' => $lang,
            'available_langs' => $this->repository->getAvailableLanguages(),
        ]);
    }

    #[Route('/{lang}/{year}/{month}/{slug}', name: 'blog_show', methods: ['GET'], requirements: ['lang' => '[a-z]{2,3}', 'year' => '\d{4}', 'month' => '\d{2}'])]
    public function show(Request $request, string $lang, string $year, string $month, string $slug): Response
    {
        $article = $this->repository->findBySlug($slug, $lang);

        if ($article === null) {
            throw $this->createNotFoundException('Artykuł nie został znaleziony.');
        }

        $translations = $this->repository->findTranslations($article->translationKey);

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'translations' => $translations,
            'lang' => $lang,
            'currentUrl' => $request->getUri(),
            'available_langs' => $this->repository->getAvailableLanguages(),
        ]);
    }

    #[Route('/{lang}/tag/{tag}', name: 'blog_tag', methods: ['GET'], requirements: ['lang' => '[a-z]{2,3}'])]
    public function byTag(string $lang, string $tag): Response
    {
        $articles = $this->repository->findByTag($tag, $lang);

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'lang' => $lang,
            'tag' => $tag,
            'available_langs' => $this->repository->getAvailableLanguages(),
        ]);
    }
}
