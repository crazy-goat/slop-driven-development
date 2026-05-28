---
title: Warum ich 3 Stunden mit Stress-Testing von deepseek v4 flash verbrachte — das $2,97 Experiment, das meine Denkweise änderte
date: 2026-05-22
slug: stress-testing-deepseek-v4-flash
lang: de
translation_key: stress-testing-deepseek-v4-flash
description: Ich führte 134 Coding-Sessions auf 4 Repos durch, verbrannte 7.498 Tool-Aufrufe und gab $2,97 aus, um herauszufinden, wo LLMs im Coding-Workflow genau versagen.
keywords:
  - deepseek
  - llm
  - coden
  - experiment
  - workflow
tags:
  - ai
  - programmierung
  - llm
  - experiment
  - opencode
  - deepseek
  - vibecoding
  - agenten
  - buildinpublic
author: Piotr Hałas
draft: false
canonical: https://slop-driven-development.com/en/2026/05/stress-testing-deepseek-v4-flash
abstract: >
    Wenn du jemals zugesehen hast, wie ein LLM 8 Tool-Aufrufe verschwendet, um herauszufinden, ob dein Repo "main" oder "master" verwendet — oder direkt auf den Standard-Branch pusht, obwohl es ihm verboten wurde — kennst du den Schmerz. Das Modell schreibt großartigen Code, ist aber schrecklich darin, Workflows zu befolgen. Ich wollte genau herausfinden, wo LLMs im Coding-Workflow versagen — um diese Teile zu deterministischen Tools zu verschieben und dem Modell das zu überlassen, was es am besten kann. Also führte ich ein Experiment durch.
---

## Das Problem mit prompt-basierten Workflows

Eine Weile versuchte ich, meine LLM-Tools zu automatisieren, indem ich Workflows als Markdown-Dateien in Repos schrieb. Es verursachte mehr Probleme als es löste. Die Daten aus diesem Experiment zeigen schmerzhaft klar, warum dieser Ansatz nicht skaliert: 38% aller Tool-Aufrufe sind Bash-Befehle. Das sind Git-Operationen, Tests, Linting, PR-Erstellung auf GitLab. Diese Befehle sind für jede Aufgabe identisch. Sie erfordern keine Intelligenz, doch das LLM bekommt sie häufig falsch hin.

![Tool-Nutzungsdiagramm](/img/tool-usage-chart.png)

### Experimentkosten

| Modell | Geschätzte Kosten | vs deepseek |
|---|---|---|
| deepseek v4 flash | **$2,97** (tatsächlich) | 1× |
| Kimi K2.6 | ~$161 | 54× |
| Sonnet 4.6 | ~$1.663 | 560× |
| Opus 4.7 | ~$2.772 | 933× |

### Was ich gelernt habe:

- **Geschwindigkeit ist wichtiger als du denkst.** Was ich an mehreren Abenden erledigen wollte, war in 3 Stunden fertig.
- **Code-Qualität hat mich überrascht.** Gut genug, dass ich deepseek v4 flash als ernsthaften Kandidaten für den täglichen Gebrauch in Betracht ziehe.
- **Der Markdown-Workflow-Ansatz ist gescheitert** — und genau darum ging es.

### Zusammenfassung

Die größte Überraschung — und der größte Gewinner — war deepseek v4 flash selbst. Ich ging in dieses Experiment mit der Erwartung, dass der Großteil der Ausgabe im Müll landen würde. Nach manueller Überprüfung mehrerer Änderungen war das Urteil klar: **Der meiste Code war solide. Ein Teil war echt gut.** Ein $2,97-Modell, das Produktionscode in 4 Sprachen und 400+ Dateien produziert. Das verändert, was ich für möglich hielt.
