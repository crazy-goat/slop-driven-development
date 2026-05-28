---
title: Por qué pasé 3 horas probando al límite deepseek v4 flash — el experimento de $2.97 que cambió mi forma de pensar
date: 2026-05-22
slug: stress-testing-deepseek-v4-flash
lang: es
translation_key: stress-testing-deepseek-v4-flash
description: Realicé 134 sesiones de codificación en 4 repos, quemé 7.498 llamadas a herramientas y gasté $2.97 para descubrir exactamente dónde fallan los LLMs en el flujo de trabajo de codificación.
keywords:
  - deepseek
  - llm
  - codificación
  - experimento
  - workflow
tags:
  - ai
  - programación
  - llm
  - experimento
  - opencode
  - deepseek
  - vibecoding
  - agentes
  - buildinpublic
author: Piotr Hałas
draft: false
canonical: https://slop-driven-development.com/en/2026/05/stress-testing-deepseek-v4-flash
abstract: >
    Si alguna vez has visto a un LLM gastar 8 llamadas a herramientas para averiguar si tu repositorio usa "main" o "master" — o hacer push directamente a la rama por defecto a pesar de que se le dijo que no — conoces el dolor. El modelo escribe código genial, pero es terrible siguiendo flujos de trabajo. Quería descubrir exactamente dónde fallan los LLMs en el flujo de codificación — para mover esas partes a herramientas deterministas y dejar que el modelo haga lo que mejor sabe hacer. Así que realicé un experimento.
---

## El problema con los flujos basados en prompts

Durante un tiempo intenté automatizar mis herramientas LLM escribiendo flujos de trabajo como archivos markdown dentro de los repos. Causó más problemas de los que resolvió. Los datos de este experimento muestran dolorosamente claro por qué ese enfoque no escala: el 38% de todas las llamadas a herramientas son comandos bash.

![Gráfico de uso de herramientas](/img/tool-usage-chart.png)

### Costos del experimento

| Modelo | Costo estimado | vs deepseek |
|---|---|---|
| deepseek v4 flash | **$2.97** (real) | 1× |
| Kimi K2.6 | ~$161 | 54× |
| Sonnet 4.6 | ~$1,663 | 560× |
| Opus 4.7 | ~$2,772 | 933× |

### Lo que aprendí:

- **La velocidad importa más de lo que crees.** Lo que esperaba me llevara varias tardes, se hizo en 3 horas.
- **La calidad del código me sorprendió.** Suficientemente buena como para reconsiderar mi elección de modelo predeterminado.
- **El enfoque de workflow en markdown falló** — y ese era exactamente el punto.

### Reflexiones finales

La mayor sorpresa — y la mayor ganadora — fue la propia deepseek v4 flash. Entré a este experimento esperando que la mayor parte del resultado terminara en la basura. Tras revisar manualmente varios cambios, el veredicto fue claro: **la mayor parte del código era sólido. Parte era genuinamente bueno.** Un modelo de $2.97 produciendo código de calidad de producción en 4 lenguajes y más de 400 archivos. Eso cambia lo que pensaba que era posible.
