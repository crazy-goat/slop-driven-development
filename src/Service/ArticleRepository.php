<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Article;
use Psr\Log\LoggerInterface;

class ArticleRepository
{
    /** @var array<string, Article> Indexed by article path */
    private array $articles = [];

    /** @var array<string, Article[]> Indexed by lang */
    private array $byLang = [];

    /** @var array<string, array<string, Article>> Indexed by lang+slug */
    private array $bySlug = [];

    /** @var array<string, array<string, Article>> Indexed by translation_key+lang */
    private array $byTranslationKey = [];

    /** @var array<string, Article[]> Indexed by lang+tag */
    private array $byTag = [];

    private bool $loaded = false;

    public function __construct(
        private readonly string $contentDir,
        private readonly MarkdownParser $markdownParser,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Ładuje artykuły z dysku (jednorazowo).
     */
    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        if (!is_dir($this->contentDir)) {
            $this->logger->warning('Content directory not found: {dir}', ['dir' => $this->contentDir]);
            return;
        }

        $finder = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->contentDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $count = 0;
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            try {
                $markdown = file_get_contents($file->getRealPath());
                if ($markdown === false) {
                    continue;
                }

                $parsed = $this->markdownParser->parse($markdown);
                $fm = $parsed['frontMatter'];

                // Skip drafts
                if (!empty($fm['draft'])) {
                    continue;
                }

                // Lang from filename (en.md → en), override front matter
                $lang = $file->getBasename('.' . $file->getExtension());

                // Slug from parent directory name
                $slugDir = $file->getPathInfo()->getFilename();

                // Year/month from grandparent directories
                $monthDir = $file->getPathInfo()->getPathInfo()->getFilename();
                $yearDir = $file->getPathInfo()->getPathInfo()->getPathInfo()->getFilename();

                // Use front matter values, fall back to directory-derived
                $effectiveSlug = !empty($fm['slug']) ? $fm['slug'] : $slugDir;
                $effectiveLang = !empty($fm['lang']) ? $fm['lang'] : $lang;

                // Validate required fields
                if (empty($fm['title']) || empty($fm['translation_key'])) {
                    $this->logger->warning('Skipping file with missing required fields: {file}', [
                        'file' => $file->getRealPath(),
                    ]);
                    continue;
                }

                // Build URL: /{lang}/{year}/{month}/{slug}
                $date = is_int($fm['date']) ? (new \DateTimeImmutable())->setTimestamp($fm['date']) : new \DateTimeImmutable($fm['date']);
                $path = sprintf(
                    '/%s/%s/%s/%s',
                    $effectiveLang,
                    $date->format('Y'),
                    $date->format('m'),
                    $effectiveSlug
                );

                $article = Article::fromFrontMatter($fm, $parsed['html'], $path);

                // Override lang and slug with dir-derived values
                $article->lang = $effectiveLang;
                $article->slug = $effectiveSlug;

                $this->articles[$article->path] = $article;
                $this->byLang[$article->lang][] = $article;
                $this->bySlug[$article->lang][$article->slug] = $article;
                $this->byTranslationKey[$article->translationKey][$article->lang] = $article;

                foreach ($article->tags as $tag) {
                    $this->byTag[$article->lang][$tag][] = $article;
                }

                $count++;
            } catch (\Throwable $e) {
                $this->logger->error('Failed to parse article: {file} — {error}', [
                    'file' => $file->getRealPath(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Sort each language's articles by date descending
        foreach ($this->byLang as $lang => &$articles) {
            usort($articles, fn(Article $a, Article $b) => $b->date->getTimestamp() <=> $a->date->getTimestamp());
        }
        unset($articles);

        // Sort tags too
        foreach ($this->byTag as $lang => &$tagGroups) {
            foreach ($tagGroups as $tag => &$articles) {
                usort($articles, fn(Article $a, Article $b) => $b->date->getTimestamp() <=> $a->date->getTimestamp());
            }
            unset($articles);
        }
        unset($tagGroups);

        $this->logger->info('Loaded {count} articles from {dir}', [
            'count' => $count,
            'dir' => $this->contentDir,
        ]);
    }

    /**
     * @return Article[]
     */
    public function findByLang(string $lang): array
    {
        $this->load();
        return $this->byLang[$lang] ?? [];
    }

    public function findBySlug(string $slug, string $lang): ?Article
    {
        $this->load();
        return $this->bySlug[$lang][$slug] ?? null;
    }

    /**
     * @return array<string, Article>
     */
    public function findTranslations(string $translationKey): array
    {
        $this->load();
        return $this->byTranslationKey[$translationKey] ?? [];
    }

    /**
     * @return Article[]
     */
    public function findByTag(string $tag, string $lang): array
    {
        $this->load();
        return $this->byTag[$lang][$tag] ?? [];
    }

    /**
     * @return Article[]
     */
    public function findAll(): array
    {
        $this->load();
        return array_values($this->articles);
    }

    /**
     * Zwraca listę dostępnych języków (alfabetycznie).
     *
     * @return string[]
     */
    public function getAvailableLanguages(): array
    {
        $this->load();
        $langs = array_keys($this->byLang);
        sort($langs);
        return $langs;
    }
}
