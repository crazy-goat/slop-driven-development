<?php

declare(strict_types=1);

namespace App\Service;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterProviderInterface;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownParser
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new FrontMatterExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Parsuje Markdown z front matter.
     *
     * @return array{frontMatter: array<string, mixed>, html: string}
     */
    public function parse(string $markdown): array
    {
        $result = $this->converter->convert($markdown);

        $frontMatter = [];
        if ($result instanceof FrontMatterProviderInterface) {
            $frontMatter = $result->getFrontMatter();
            if (!is_array($frontMatter)) {
                $frontMatter = [];
            }
            $html = $result->getContent();
        } else {
            $html = (string) $result;
        }

        return [
            'frontMatter' => $frontMatter,
            'html' => $html,
        ];
    }

    /**
     * Parsuje tylko front matter (bez konwersji Markdown do HTML).
     *
     * @return array<string, mixed>|null
     */
    public function parseFrontMatter(string $markdown): ?array
    {
        $parser = new \League\CommonMark\Extension\FrontMatter\FrontMatterParser();
        $result = $parser->parse($markdown);

        $frontMatter = $result->getFrontMatter();

        return is_array($frontMatter) ? $frontMatter : null;
    }
}
