---
title: PHP 8.4 — What's New?
date: 2025-01-20
slug: php-84-whats-new
lang: en
translation_key: php-84-new-features
description: Overview of the most important new features in PHP 8.4 — property hooks, asymmetric visibility, new lambda syntax.
keywords:
  - php
  - php84
  - programming
tags:
  - php
  - backend
  - programming
author: Piotr Hałas
draft: false
---

# PHP 8.4 — What's New?

PHP 8.4 brings several groundbreaking changes. Here are the highlights:

## Property Hooks

New syntax for defining inline getters and setters:

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

Set different visibility for getter and setter:

```php
class Post
{
    public private(set) string $slug;
}
```

## New Lambda Syntax

Shorter lambdas with `=>` and no parentheses for single parameters.

---

This is just the beginning — PHP 8.4 is a solid step forward.
