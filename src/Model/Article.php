<?php

declare(strict_types=1);

namespace App\Model;

class Article
{
    public string $title;
    public \DateTimeImmutable $date;
    public string $slug;
    public string $lang;
    public string $translationKey;
    public string $description;
    public string $html;
    public string $path;
    public array $keywords = [];
    public array $tags = [];
    public string $author = '';
    public bool $draft = false;
    public ?string $canonical = null;
    public ?string $abstract = null;

    /**
     * @param array<string, mixed> $frontMatter
     */
    public static function fromFrontMatter(array $frontMatter, string $html, string $path): self
    {
        $article = new self();

        $article->title = $frontMatter['title'];
        $article->date = self::parseDate($frontMatter['date']);
        $article->slug = $frontMatter['slug'];
        $article->lang = $frontMatter['lang'];
        $article->translationKey = $frontMatter['translation_key'];
        $article->description = $frontMatter['description'];
        $article->html = $html;
        $article->path = $path;

        if (isset($frontMatter['keywords'])) {
            $article->keywords = $frontMatter['keywords'];
        }
        if (isset($frontMatter['tags'])) {
            $article->tags = $frontMatter['tags'];
        }
        if (isset($frontMatter['author'])) {
            $article->author = $frontMatter['author'];
        }
        if (isset($frontMatter['draft'])) {
            $article->draft = (bool) $frontMatter['draft'];
        }
        if (isset($frontMatter['canonical'])) {
            $article->canonical = $frontMatter['canonical'];
        }
        if (isset($frontMatter['abstract'])) {
            $article->abstract = $frontMatter['abstract'];
        }

        return $article;
    }

    /**
     * YAML parsuje daty typu "2025-01-15" jako Unix timestamp (int).
     * Przyjmujemy zarówno int (timestamp) jak i string (ISO).
     */
    private static function parseDate(string|int|\DateTimeImmutable $date): \DateTimeImmutable
    {
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        if (is_int($date)) {
            return (new \DateTimeImmutable())->setTimestamp($date);
        }

        return new \DateTimeImmutable($date);
    }
}
