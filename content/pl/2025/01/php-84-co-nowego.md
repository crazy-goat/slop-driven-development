---
title: PHP 8.4 — Co nowego?
date: 2025-01-20
slug: php-84-co-nowego
lang: pl
translation_key: php-84-new-features
description: Przegląd najważniejszych nowości w PHP 8.4 — property hooks, asymmetric visibility, nowa składnia lambd.
keywords:
  - php
  - php84
  - programowanie
tags:
  - php
  - backend
  - programowanie
author: Piotr Hałas
draft: false
---

# PHP 8.4 — Co nowego?

PHP 8.4 przynosi kilka przełomowych zmian. Oto najciekawsze:

## Property Hooks

Nowa składnia pozwalająca na definiowanie getterów i setterów inline:

```php
class User
{
    public string $name {
        get => $this->name;
        set => strlen($value) > 0 ? $value : throw new \InvalidArgumentException();
    }
}
```

## Asymmetric Visibility

Możliwość ustawienia różnej widoczności dla gettera i settera:

```php
class Post
{
    public private(set) string $slug;
}
```

## Nowa składnia lambd

Krótsze lambda z `=>` bez nawiasów dla pojedynczych parametrów.

---

To dopiero początek — PHP 8.4 to solidny krok naprzód.
