---
title: Welcome to Slop Driven Development
date: 2025-01-15
slug: welcome-to-slop-driven-development
lang: en
translation_key: hello-microblog
description: First post on Slop Driven Development — a self-hosted blog without a database, powered by Symfony and Workerman.
keywords:
  - slop-driven-development
  - symfony
  - workerman
tags:
  - intro
  - meta
author: Piotr Hałas
draft: false
---

# Welcome to Slop Driven Development!

This is the first post on my new, self-hosted **Slop Driven Development**.
Posts are written in Markdown and served from RAM by Symfony + Workerman.

## Features

- **Zero database** — articles are `.md` files on disk
- **Blazing fast** — everything is in RAM after startup
- **Multilingual** — full translation support via `translation_key`
- **SEO** — hreflang, Open Graph, canonical URLs
- **Markdown** — write in markdown, get HTML

```php
<?php
// Syntax highlighting via highlight.js
echo "Microblog rulez!";
```

Happy reading!
