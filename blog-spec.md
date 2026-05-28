# Specyfikacja implementacji — Microblog Markdown

## Przegląd architektury

Self-hosted microblog oparty na Symfony + Workerman, serwujący artykuły w Markdown bez bazy danych. Artykuły wczytywane są z dysku **jednorazowo przy starcie serwera** i trzymane w pamięci RAM. Indeks budowany jest z metadanych front matter.

---

## Stack technologiczny

| Warstwa | Technologia |
|---|---|
| Serwer HTTP | `crazy-goat/workerman-bundle` (fork `luzrain/workerman-bundle`) |
| Framework | Symfony 7.x |
| Parser Markdown | `league/commonmark` |
| Parser YAML (front matter) | `symfony/yaml` (wbudowany w Symfony) |
| Templating | Twig |
| Fonty | Self-hosted `.woff2` — Special Elite + Courier Prime (OFL) |
| Syntax highlighting | highlight.js (client-side, CDN lub self-hosted) |

---

## Struktura katalogów

```
project/
├── content/
│   ├── pl/
│   │   └── 2025/01/
│   │       └── moj-post.md
│   ├── en/
│   │   └── 2025/01/
│   │       └── my-post.md
│   └── de/
│       └── 2025/01/
│           └── mein-post.md
├── public/
│   ├── fonts/
│   │   ├── special-elite.woff2
│   │   └── courier-prime.woff2
│   └── js/
│       └── highlight.min.js
├── src/
│   ├── Controller/
│   │   ├── BlogController.php
│   │   └── SitemapController.php
│   ├── Service/
│   │   ├── ArticleRepository.php
│   │   └── MarkdownParser.php
│   └── Model/
│       └── Article.php
└── templates/
    ├── base.html.twig
    ├── blog/
    │   ├── index.html.twig
    │   └── show.html.twig
    └── sitemap.xml.twig
```

---

## Format artykułu (front matter)

Każdy plik `.md` zaczyna się od bloku YAML otoczonego `---`:

```yaml
---
title: Tytuł artykułu
date: 2025-01-15
slug: tytul-artykulu
lang: pl
translation_key: unique-article-identifier
description: Opis artykułu do meta description (150–160 znaków)
canonical: https://example.com/pl/2025/01/tytul-artykulu
keywords:
  - php
  - symfony
  - workerman
tags:
  - backend
  - async
author: Piotr
draft: false
---

Treść artykułu w Markdown...
```

### Pola obowiązkowe

| Pole | Typ | Opis |
|---|---|---|
| `title` | string | Tytuł artykułu |
| `date` | date (YYYY-MM-DD) | Data publikacji |
| `slug` | string | URL-friendly identyfikator |
| `lang` | string | Kod języka (pl, en, de, ...) |
| `translation_key` | string | Wspólny klucz dla wszystkich wersji językowych |
| `description` | string | Meta description (SEO) |

### Pola opcjonalne

| Pole | Typ | Opis |
|---|---|---|
| `keywords` | string[] | Słowa kluczowe (meta keywords) |
| `tags` | string[] | Tagi do grupowania artykułów |
| `author` | string | Autor (przydatne przy wielu autorach) |
| `draft` | bool | Jeśli `true` — artykuł pomijany przy wczytywaniu (domyślnie `false`) |
| `canonical` | string | Kanoniczny URL (jeśli crosspostujesz) |

---

## Model danych — `Article`

```php
class Article
{
    public string $title;
    public \DateTimeImmutable $date;
    public string $slug;
    public string $lang;
    public string $translationKey;
    public string $description;
    public string $html;          // sparsowany HTML z Markdown
    public string $path;          // ścieżka URL np. /pl/2025/01/moj-post
    public array  $keywords = [];
    public array  $tags = [];
    public string $author = '';
    public bool   $draft = false;
    public ?string $canonical = null;
}
```

---

## ArticleRepository

Singleton serwis trzymany przez Workermana w pamięci przez cały czas życia procesu.

### Odpowiedzialności

- Skanowanie katalogu `content/` rekurencyjnie przy pierwszym wywołaniu
- Parsowanie front matter przez `FrontMatterExtension` z `league/commonmark`
- Konwersja Markdown → HTML
- Pomijanie artykułów z `draft: true`
- Indeksowanie po: `slug`, `lang`, `translation_key`, `tags`
- Zwracanie posortowanej listy (po `date` malejąco)

### Publiczne API

```php
class ArticleRepository
{
    // Wszystkie opublikowane artykuły w danym języku
    public function findByLang(string $lang): array;

    // Pojedynczy artykuł po slug i języku
    public function findBySlug(string $slug, string $lang): ?Article;

    // Wszystkie wersje językowe artykułu (po translation_key)
    public function findTranslations(string $translationKey): array; // ['pl' => Article, 'en' => Article]

    // Artykuły po tagu
    public function findByTag(string $tag, string $lang): array;

    // Wszystkie artykuły (do generowania sitemap)
    public function findAll(): array;
}
```

---

## Routing

| Metoda | URL | Akcja |
|---|---|---|
| GET | `/` | Redirect do domyślnego języka (np. `/pl`) |
| GET | `/{lang}` | Lista artykułów w danym języku |
| GET | `/{lang}/{year}/{month}/{slug}` | Pojedynczy artykuł |
| GET | `/{lang}/tag/{tag}` | Artykuły po tagu |
| GET | `/sitemap.xml` | Sitemap XML |
| GET | `/robots.txt` | Robots.txt |

---

## SEO

### Meta tagi w `<head>`

```html
<html lang="{{ article.lang }}">
<head>
  <title>{{ article.title }}</title>
  <meta name="description" content="{{ article.description }}">
  <meta name="keywords" content="{{ article.keywords|join(', ') }}">
  <link rel="canonical" href="{{ article.canonical ?? currentUrl }}">

  <!-- Open Graph -->
  <meta property="og:title" content="{{ article.title }}">
  <meta property="og:description" content="{{ article.description }}">
  <meta property="og:locale" content="{{ article.lang }}_{{ article.lang|upper }}">
  {% for lang, translation in translations %}
  <meta property="og:locale:alternate" content="{{ lang }}_{{ lang|upper }}">
  {% endfor %}

  <!-- hreflang -->
  {% for lang, translation in translations %}
  <link rel="alternate" hreflang="{{ lang }}" href="{{ translation.canonical }}">
  {% endfor %}
  <link rel="alternate" hreflang="x-default" href="{{ defaultTranslation.canonical }}">
</head>
```

### Sitemap XML

Generowany dynamicznie z `ArticleRepository::findAll()`, serwowany pod `/sitemap.xml`. Zawiera wszystkie wersje językowe każdego artykułu z atrybutem `hreflang`.

```xml
<url>
  <loc>https://example.com/pl/2025/01/moj-post</loc>
  <lastmod>2025-01-15</lastmod>
  <xhtml:link rel="alternate" hreflang="pl" href="https://example.com/pl/2025/01/moj-post"/>
  <xhtml:link rel="alternate" hreflang="en" href="https://example.com/en/2025/01/my-post"/>
</url>
```

---

## Typografia

### Fonty (self-hosted, OFL)

| Font | Zastosowanie | Źródło |
|---|---|---|
| Special Elite | Nagłówki (h1–h3) | Google Fonts |
| Courier Prime | Treść, UI | Google Fonts |

Pliki `.woff2` pobrane lokalnie, serwowane przez Workermana z `public/fonts/`.

```css
@font-face {
    font-family: 'Special Elite';
    src: url('/fonts/special-elite.woff2') format('woff2');
    font-display: swap;
}

@font-face {
    font-family: 'Courier Prime';
    src: url('/fonts/courier-prime.woff2') format('woff2');
    font-display: swap;
}

h1, h2, h3 { font-family: 'Special Elite', serif; }
body        { font-family: 'Courier Prime', monospace; }
```

### Syntax Highlighting

Client-side przez **highlight.js** (self-hosted `.js` + `.css` w `public/`).
Automatyczne wykrywanie języka z atrybutu `class="language-*"` generowanego przez CommonMark.

```html
<link rel="stylesheet" href="/css/highlight-github-dark.min.css">
<script src="/js/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
```

---

## Konfiguracja Workermana

```yaml
# config/packages/workerman.yaml
workerman:
  servers:
    - host: 0.0.0.0
      port: 8080
      worker_count: 4
      reuse_port: true      # kernel load balancer na Linuksie
      serve_files: true     # serwowanie plików statycznych z public/
  reload_strategy:
    - on_exception          # restart workera po nieobsłużonym wyjątku
    - max_requests: 10000   # restart co 10k requestów (memory leak prevention)
```

---

## Wydajność przy dużej liczbie artykułów

Szacowana skala po roku przy ~5–10 oryginałów tygodniowo + 4–5 tłumaczeń:

| Metryka | Szacunek |
|---|---|
| Pliki `.md` łącznie | ~10 000–25 000 |
| RAM na treść HTML (≈10KB/artykuł) | ~100–250 MB |
| Czas cold start (parsowanie) | 10–30 sekund |

### Rekomendacja przy skali > 5000 artykułów

Rozważyć **SQLite jako indeks** przy zachowaniu plików `.md` jako source of truth:

- Pliki `.md` edytowane ręcznie / przez git
- Przy starcie Workermana: rebuild SQLite tylko dla plików nowszych niż ostatni rebuild
- Serwowanie z SQLite (szybkie query po slug, lang, tag)
- Treść HTML cache'owana w SQLite, nie parsowana przy każdym restarcie

---

## Przepływ danych — żądanie artykułu

```
Request GET /pl/2025/01/moj-post
    │
    ▼
BlogController::show()
    │
    ▼
ArticleRepository::findBySlug('moj-post', 'pl')
    │   (dane już w RAM — bez I/O)
    ▼
ArticleRepository::findTranslations('unique-key')
    │   (lista wszystkich wersji językowych)
    ▼
Twig::render('blog/show.html.twig', [article, translations])
    │
    ▼
Response (HTML z meta hreflang, og:tags, canonical)
```

---

## Kolejne kroki implementacji

1. Instalacja i konfiguracja `crazy-goat/workerman-bundle`
2. Model `Article` + `ArticleRepository` z lazy loading
3. Parser Markdown z `FrontMatterExtension`
4. Routing i kontrolery
5. Szablony Twig z SEO meta tagami
6. Self-hosting fontów i highlight.js
7. Generowanie sitemap XML
8. Testy cold start przy dużej liczbie plików
