---
title: 为什么我花了3小时对deepseek v4 flash进行压力测试——一个改变我想法、只需2.97美元实验
date: 2026-05-22
slug: stress-testing-deepseek-v4-flash
lang: zh
translation_key: stress-testing-deepseek-v4-flash
description: 我在4个仓库上运行了134个编码会话，消耗了7,498次工具调用，花费了2.97美元，以精确找出LLM在编码工作流程中失败的地方。
keywords:
  - deepseek
  - llm
  - 编码
  - 实验
  - 工作流
tags:
  - ai
  - 编程
  - llm
  - 实验
  - opencode
  - deepseek
  - vibecoding
  - 代理
  - buildinpublic
author: Piotr Hałas
draft: false
canonical: https://slop-driven-development.com/en/2026/05/stress-testing-deepseek-v4-flash
abstract: >
    如果你曾经看过LLM花费8次工具调用来弄清楚你的仓库使用的是"main"还是"master"——或者尽管被告知不要，却直接推送到默认分支——你就知道这种痛苦。模型编写出色的代码，但在遵循工作流程方面却很糟糕。我想精确找出LLM在编码工作流程中失败的地方——以便将这些部分转移到确定性工具，让模型做它最擅长的事情。所以我进行了一个实验。
---

## 基于提示的工作流的问题

有一段时间，我尝试通过在仓库中编写markdown文件作为工作流来自动化我的LLM工具。它造成的问题比解决的问题还多。这个实验的数据痛苦地揭示了为什么这种方法无法扩展——38%的所有工具调用都是bash命令。

![工具使用图表](/img/tool-usage-chart.png)

### 实验成本

| 模型 | 估计成本 | vs deepseek |
|---|---|---|
| deepseek v4 flash | **$2.97** (实际) | 1× |
| Kimi K2.6 | ~$161 | 54× |
| Sonnet 4.6 | ~$1,663 | 560× |
| Opus 4.7 | ~$2,772 | 933× |

### 我的收获：

- **速度比你想象的更重要。** 我原以为需要几个晚上的工作，3小时就完成了。
- **代码质量让我惊讶。** 好到让我重新考虑我的默认模型选择。
- **基于markdown的工作流方法失败了**——而这正是关键所在。

### 最后的思考

最大的惊喜——也是最大的赢家——是deepseek v4 flash本身。我进入这个实验时预期大部分输出都会进垃圾桶。在手动审查了几个更改后，结论很清楚：**大部分代码是可靠的。有些代码真的很好。** 一个价值$2.97的模型在4种语言和400多个文件中生成了生产质量的代码。这改变了我对可能的认知。
