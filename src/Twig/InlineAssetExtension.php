<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class InlineAssetExtension extends AbstractExtension
{
    private const TRANSLATIONS = [
        'pl' => [
            'latest_posts' => 'Ostatnie wpisy',
            'articles_tagged' => 'Artykuły z tagiem:',
            'no_articles' => 'Brak artykułów.',
            'date' => 'Data:',
            'author' => 'Autor:',
            'translations' => 'Dostępne wersje językowe:',
            'tag' => 'Tag',
            'powered_by' => 'napędzane przez',
        ],
        'en' => [
            'latest_posts' => 'Latest posts',
            'articles_tagged' => 'Articles tagged:',
            'no_articles' => 'No articles.',
            'date' => 'Date:',
            'author' => 'Author:',
            'translations' => 'Available translations:',
            'tag' => 'Tag',
            'powered_by' => 'powered by',
        ],
        'de' => [
            'latest_posts' => 'Neueste Beiträge',
            'articles_tagged' => 'Artikel mit Tag:',
            'no_articles' => 'Keine Artikel.',
            'date' => 'Datum:',
            'author' => 'Autor:',
            'translations' => 'Verfügbare Übersetzungen:',
            'tag' => 'Tag',
            'powered_by' => 'unterstützt von',
        ],
        'es' => [
            'latest_posts' => 'Últimas publicaciones',
            'articles_tagged' => 'Artículos etiquetados:',
            'no_articles' => 'Sin artículos.',
            'date' => 'Fecha:',
            'author' => 'Autor:',
            'translations' => 'Traducciones disponibles:',
            'tag' => 'Etiqueta',
            'powered_by' => 'impulsado por',
        ],
        'zh' => [
            'latest_posts' => '最新文章',
            'articles_tagged' => '标签文章：',
            'no_articles' => '暂无文章。',
            'date' => '日期：',
            'author' => '作者：',
            'translations' => '可用翻译：',
            'tag' => '标签',
            'powered_by' => '由',
        ],
        'hi' => [
            'latest_posts' => 'नवीनतम पोस्ट',
            'articles_tagged' => 'टैग किए गए लेख:',
            'no_articles' => 'कोई लेख नहीं।',
            'date' => 'दिनांक:',
            'author' => 'लेखक:',
            'translations' => 'उपलब्ध अनुवाद:',
            'tag' => 'टैग',
            'powered_by' => 'द्वारा संचालित',
        ],
        'ar' => [
            'latest_posts' => 'أحدث المقالات',
            'articles_tagged' => 'المقالات الموسومة:',
            'no_articles' => 'لا توجد مقالات.',
            'date' => 'التاريخ:',
            'author' => 'الكاتب:',
            'translations' => 'الترجمات المتاحة:',
            'tag' => 'وسم',
            'powered_by' => 'مدعوم بواسطة',
        ],
        'ru' => [
            'latest_posts' => 'Последние записи',
            'articles_tagged' => 'Статьи по тегу:',
            'no_articles' => 'Нет статей.',
            'date' => 'Дата:',
            'author' => 'Автор:',
            'translations' => 'Доступные переводы:',
            'tag' => 'Тег',
            'powered_by' => 'работает на',
        ],
    ];

    private const RTL_LANGS = ['ar'];

    public function __construct(
        private readonly string $publicDir,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('inline_asset', $this->inlineAsset(...)),
            new TwigFunction('__', $this->trans(...)),
            new TwigFunction('is_rtl', $this->isRtl(...)),
        ];
    }

    public function trans(string $key, string $lang): string
    {
        return self::TRANSLATIONS[$lang][$key] ?? self::TRANSLATIONS['en'][$key] ?? $key;
    }

    public function isRtl(string $lang): bool
    {
        return in_array($lang, self::RTL_LANGS, true);
    }

    public function inlineAsset(string $path): string
    {
        $fullPath = $this->publicDir . '/' . ltrim($path, '/');

        if (!is_file($fullPath)) {
            return '';
        }

        $content = file_get_contents($fullPath);

        return $content !== false ? $content : '';
    }
}
