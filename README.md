# Microblog

Self-hosted microblog oparty na **Symfony 7** + **Workerman**, serwujący artykuły w Markdown bez bazy danych.

## Stack

| Warstwa | Technologia |
|---|---|
| Serwer HTTP | `crazy-goat/workerman-bundle` (Workerman) |
| Framework | Symfony 7.x |
| Parser Markdown | league/commonmark + FrontMatter |
| Templating | Twig |
| Syntax highlighting | highlight.js (CDN — self-host opcjonalnie) |
| Fonty | Special Elite + Courier Prime (OFL) |

## Struktura

```
├── bin/worker.php       ← entry point Workermana
├── config/              ← konfiguracja Symfony
├── content/             ← artykuły .md (pl/en/de)
├── public/              ← assets (CSS, fonts, favicon)
├── src/                 ← PHP (Model, Service, Controller)
├── templates/           ← Twig
└── composer.json
```

## Uruchomienie

```bash
composer install
php bin/worker.php
```

Serwer startuje na `http://0.0.0.0:8080`.

## Format artykułu

Pliki `.md` w `content/{lang}/{year}/{month}/{slug}.md` z front matter YAML:

```yaml
---
title: Tytuł artykułu
date: 2025-01-15
slug: tytul-artykulu
lang: pl
translation_key: unique-article-identifier
description: Opis (meta description)
tags:
  - tag1
  - tag2
draft: false
---
```

## API

| URL | Opis |
|---|---|
| `/` | Redirect do domyślnego języka |
| `/{lang}` | Lista artykułów |
| `/{lang}/{year}/{month}/{slug}` | Pojedynczy artykuł |
| `/{lang}/tag/{tag}` | Artykuły po tagu |
| `/sitemap.xml` | Sitemap XML |
| `/robots.txt` | Robots.txt |

## Wydajność

Artykuły wczytywane z dysku **jednorazowo przy starcie** i trzymane w RAM. Przy >5000 artykułów rekomendowane SQLite jako cache indeksu.
