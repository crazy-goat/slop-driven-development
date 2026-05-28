<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ArticleRepository;
use App\Service\MarkdownParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'content:validate',
    description: 'Sprawdza poprawność artykułów: tłumaczenia, canonical, metadane',
)]
class ContentValidateCommand extends Command
{
    /** @var string[] Języki, które muszą mieć tłumaczenie każdego EN artykułu */
    private const SUPPORTED_LANGS = ['en', 'pl', 'de', 'es', 'zh', 'hi', 'ar', 'ru'];

    private int $errors = 0;
    private int $warnings = 0;

    public function __construct(
        private readonly string $contentDir,
        private readonly MarkdownParser $markdownParser,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Content Validation');

        if (!is_dir($this->contentDir)) {
            $io->error('Content directory not found: ' . $this->contentDir);
            return Command::FAILURE;
        }

        // Scan all .md files and group by translation_key
        $articles = $this->scanAllArticles();
        $io->info(sprintf('Found %d .md files', count($articles)));

        $byKey = [];
        foreach ($articles as $a) {
            $byKey[$a['translation_key']][$a['lang']] = $a;
        }

        $io->section('1. Translation coverage');
        $this->validateTranslations($byKey, $io);

        $io->section('2. Canonical links');
        $this->validateCanonicals($byKey, $io);

        $io->section('3. Required metadata');
        $this->validateMetadata($articles, $io);

        // Summary
        $io->newLine();
        if ($this->errors === 0 && $this->warnings === 0) {
            $io->success('All articles are valid!');
            return Command::SUCCESS;
        }

        if ($this->errors > 0) {
            $io->error(sprintf('%d errors, %d warnings', $this->errors, $this->warnings));
            return Command::FAILURE;
        }

        $io->warning(sprintf('0 errors, %d warnings', $this->warnings));
        return Command::SUCCESS;
    }

    /**
     * @return array<int, array{path: string, lang: string, translation_key: string, slug: string, fm: array}>
     */
    private function scanAllArticles(): array
    {
        $articles = [];

        /** @var \SplFileInfo $file */
        $finder = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->contentDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($finder as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $markdown = file_get_contents($file->getRealPath());
            if ($markdown === false) {
                continue;
            }

            $lang = $file->getBasename('.' . $file->getExtension());
            $slug = $file->getPathInfo()->getFilename();

            // Parse only front matter (faster than full parse)
            $fm = null;
            if (preg_match('/^---\s*\n(.*?)\n---/s', $markdown, $m)) {
                try {
                    $fm = (new \Symfony\Component\Yaml\Parser())->parse($m[1]);
                } catch (\Throwable) {
                    // Skip invalid YAML
                }
            }

            if (!is_array($fm) || empty($fm['translation_key'])) {
                continue;
            }

            $articles[] = [
                'path' => $file->getRealPath(),
                'lang' => $lang,
                'translation_key' => $fm['translation_key'],
                'slug' => $slug,
                'fm' => $fm,
            ];
        }

        return $articles;
    }

    /**
     * @param array<string, array<string, array>> $byKey
     */
    private function validateTranslations(array $byKey, SymfonyStyle $io): void
    {
        foreach ($byKey as $key => $langs) {
            // Check if English original exists
            if (!isset($langs['en'])) {
                $this->errors++;
                $io->error("translation_key '$key': missing English original");
                continue;
            }

            // Check all supported languages have a translation
            $missing = array_diff(self::SUPPORTED_LANGS, array_keys($langs));
            if ($missing !== []) {
                $this->errors++;
                $io->error(sprintf(
                    '[%s] Missing translations: %s',
                    $key,
                    implode(', ', $missing)
                ));
            }
        }
    }

    /**
     * @param array<string, array<string, array>> $byKey
     */
    private function validateCanonicals(array $byKey, SymfonyStyle $io): void
    {
        foreach ($byKey as $key => $langs) {
            if (!isset($langs['en'])) {
                continue;
            }

            $enArticle = $langs['en'];
            $enSlug = $enArticle['slug'];
            $enCanonical = $enArticle['fm']['canonical'] ?? null;

            foreach ($langs as $lang => $article) {
                if ($lang === 'en') {
                    // English article should NOT have canonical (it IS the original)
                    if ($enCanonical !== null) {
                        $this->warnings++;
                        $io->warning("[$key/en] English original has canonical URL — should be the original");
                    }
                    continue;
                }

                // Non-English articles should link to the English version
                $canonical = $article['fm']['canonical'] ?? null;
                if ($canonical === null) {
                    $this->warnings++;
                    $io->warning("[$key/$lang] Missing canonical — should point to English original");
                }
            }
        }
    }

    /**
     * @param array<int, array> $articles
     */
    private function validateMetadata(array $articles, SymfonyStyle $io): void
    {
        $requiredFields = ['title', 'date', 'translation_key', 'description', 'author', 'abstract'];
        $recommendedFields = ['tags', 'keywords'];

        foreach ($articles as $article) {
            $fm = $article['fm'];
            $ref = sprintf('[%s/%s]', $fm['translation_key'] ?? '?', $fm['lang'] ?? '?');

            foreach ($requiredFields as $field) {
                if (empty($fm[$field])) {
                    $this->errors++;
                    $io->error("$ref Missing required field: '$field'");
                }
            }

            foreach ($recommendedFields as $field) {
                if (empty($fm[$field])) {
                    $this->warnings++;
                    $io->warning("$ref Missing recommended field: '$field'");
                }
            }
        }
    }
}
