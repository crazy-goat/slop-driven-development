---
title: PHP 8.4 — Was ist neu?
date: 2025-01-20
slug: php-84-was-ist-neu
lang: de
translation_key: php-84-new-features
description: Überblick über die wichtigsten Neuerungen in PHP 8.4 — Property Hooks, asymmetrische Sichtbarkeit, neue Lambda-Syntax.
keywords:
  - php
  - php84
  - programmierung
tags:
  - php
  - backend
  - programmierung
author: Piotr Hałas
draft: false
---

# PHP 8.4 — Was ist neu?

PHP 8.4 bringt einige bahnbrechende Änderungen. Hier die Highlights:

## Property Hooks

Neue Syntax für Inline-Getter und -Setter:

```php
class User
{
    public string $name {
        get => $this->name;
        set => strlen($value) > 0 ? $value : throw new \InvalidArgumentException();
    }
}
```

## Asymmetrische Sichtbarkeit

Unterschiedliche Sichtbarkeit für Getter und Setter:

```php
class Post
{
    public private(set) string $slug;
}
```

## Neue Lambda-Syntax

Kürzere Lambdas mit `=>` und ohne Klammern für einzelne Parameter.

---

Dies ist erst der Anfang — PHP 8.4 ist ein solider Schritt nach vorne.
