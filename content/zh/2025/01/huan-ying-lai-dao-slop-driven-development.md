---
title: 欢迎来到 Slop Driven Development
date: 2025-01-15
slug: huan-ying-lai-dao-slop-driven-development
lang: zh
translation_key: hello-microblog
description: Slop Driven Development 的第一篇文章 —— 一个自托管、无数据库、基于 Symfony 和 Workerman 的博客。
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

# 欢迎来到 Slop Driven Development！

这是我全新自托管 **Slop Driven Development** 的第一篇文章。
文章以 Markdown 编写，由 Symfony + Workerman 从内存中提供。

## 特点

- **无数据库** —— 文章是磁盘上的 `.md` 文件
- **极速响应** —— 启动后全部在内存中
- **多语言** —— 通过 `translation_key` 提供完整翻译支持
- **SEO** —— hreflang、Open Graph、规范 URL
- **Markdown** —— 用 Markdown 写作，获取 HTML

```php
<?php
echo "Slop Driven Development 最棒！";
```

祝阅读愉快！
