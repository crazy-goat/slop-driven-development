---
title: Czemu spędziłem 3 godziny na stress-testowaniu deepseek v4 flash — eksperyment za $2.97, który zmienił moje myślenie
date: 2026-05-22
slug: stress-testing-deepseek-v4-flash
lang: pl
translation_key: stress-testing-deepseek-v4-flash
description: Przeprowadziłem 134 sesje kodowania na 4 repozytoriach, spaliłem 7 498 wywołań narzędzi i wydałem $2.97, by dowiedzieć się, gdzie dokładnie zawodzą LLMy w workflowzie kodowania.
keywords:
  - deepseek
  - llm
  - kodowanie
  - eksperyment
  - workflow
tags:
  - ai
  - programowanie
  - llm
  - eksperyment
  - opencode
  - deepseek
  - vibecoding
  - agenci
  - buildinpublic
author: Piotr Hałas
draft: false
canonical: https://slop-driven-development.com/en/2026/05/stress-testing-deepseek-v4-flash
abstract: >
    Jeśli kiedykolwiek patrzyłeś, jak LLM marnuje 8 wywołań narzędzi, by ustalić, czy Twoje repozytorium używa "main" czy "master" — albo pushuje bezpośrednio na domyślną gałąź mimo wyraźnego zakazu — znasz ten ból. Model pisze świetny kod, ale jest beznadziejny w przestrzeganiu workflowów. Chciałem dokładnie sprawdzić, gdzie LLMy zawodzą w procesie kodowania — żeby przenieść te części do deterministycznych narzędzi i zostawić modelowi to, co robi najlepiej. Więc przeprowadziłem eksperyment.
---

## Problem z workflowami opartymi na promptach

Przez jakiś czas próbowałem automatyzować moje narzędzia LLM przez pisanie workflowów jako plików markdown w repozytoriach. Przysporzyło to więcej problemów niż rozwiązań — tysiące plików MD zapychały kontekst, model wywoływał je w losowych momentach zamiast tam, gdzie były potrzebne. Dane z tego eksperymentu boleśnie pokazują, dlaczego to podejście nie skaluje — 38% wszystkich wywołań narzędzi to komendy bash. To operacje gita, testy, lint, tworzenie PR-ów na GitLabie. Te komendy są zawsze identyczne dla każdego zadania. Nie wymagają inteligencji, a jednak LLM często je wykonuje błędnie. Co gorsza, każde wywołanie bash zjada kontekst, który model mógłby użyć do faktycznego pisania kodu.

![Wykres użycia narzędzi](/img/tool-usage-chart.png)

### Koszty eksperymentu

I jest jeszcze inny problem. Z mojego doświadczenia, obecne modele gubią kontekst i łamią workflowy przy dłuższych zadaniach. Mówisz im "nigdy nie pushuj na main" — a 50 wiadomości później robią dokładnie to, bo instrukcja została pogrzebana gdzieś po drodze.

Im częściej napotykałem te błędy, tym jaśniejsze się stawało: poleganie na promptach do egzekwowania reguł workflow jest fundamentalnie kruche. Instrukcje degradują się, kontekst się zapycha, a model improwizuje. Zamiast z tym walczyć — co jeśli wynieść workflow poza rozmowę? Podejście oparte na YAML traktuje workflow jako kod — deterministyczny, kontrolowany wersjami, podlegający review. LLM nigdy nie musi pamiętać "nie pushuj na main", bo workflow po prostu mu na to nie pozwoli.

### Eksperyment:

Ten test był o ustanowieniu baseline'u. Jeśli piszesz cały workflow jako prompty LLM — pokrywając lint, testy, dokumentację, CI/CD na GitHubie, code review, obsługę issue'i, zarządzanie PR-ami — zasada Pareto działa: ~80% czasu działa dobrze. System prompt plus task prompt w opencode zajmuje około 25k tokenów i gdy ten kontekst jest załadowany, pipeline'y generalnie przechodzą.

Ale te ostatnie 20% jest bolesne. Przykład: jeśli Twoje repo używa "master" jako domyślnej gałęzi, a LLM upiera się przy "main" — potrzeba około 8 zmarnowanych wywołań narzędzi, zanim model się połapie. Osiem wywołań tylko po to, by ustalić nazwę gałęzi. Coś, co skrypt mógłby załatwić w zero.

Aby dokładnie sprawdzić, gdzie modele zawodzą najczęściej, przygotowałem ~100 realnych zadań programistycznych na 4 różnych repozytoriach — różne języki (PHP, C/C++, Go), różne konwencje nazewnictwa gałęzi (main vs master), różne konfiguracje CI. Każde zadanie zostało wygenerowane przez GLM-5.1 z rzeczywistych raportów analizy kodu (code smell'e, problemy bezpieczeństwa, brakujące testy, wycieki pamięci), a następnie wykonane przez deepseek v4 flash przez opencode-go. Cel: doprowadzić model do granic i zmapować każdy słaby punkt.

![Różne użycie narzędzi między sesjami](/img/different-tool-usage.png)

### Różne użycie narzędzi między sesjami

Dałem LLM-owi każdą możliwą przewagę. Każde repo miało plik AGENTS.md z jasnymi regułami. System pamięci o nazwie mind był dostępny do przechowywania ważnego kontekstu między sesjami — żeby model pamiętał, czego nauczył się ostatnim razem. Nic nie pomogło. Te same błędy powtarzały się w kółko:

- Próby pushowania bezpośrednio na domyślną gałąź bez PR-a
- Próby zamykania PR-ów, które nie przeszły jeszcze CI — a gdy CI blokowało, model próbował flag --admin, by wymusić
- Niezamykanie issue'ów po skończeniu pracy, co powodowało rozpoczęcie pracy nad tym samym issue'em w nowej sesji

Jeszcze gorzej robi się, gdy dodasz lint i statyczną analizę kodu. Powiedz modelowi, żeby uruchomił linter, a on często po prostu tego nie zrobi — albo co gorsza, stwierdzi, że błędy istniały wcześniej i pójdzie dalej. Jedynym niezawodnym rozwiązaniem jest wymuszanie lintowania przez git hooki.

### Liczby:

Celowo wybrałem "słabszy" model do tego eksperymentu — tani i szybki, ale bardziej podatny na błędy. Założenie: jeśli gdzieś zawiedzie, to prawdziwy słaby punkt, który lepsze modele też pewnie napotkają, tylko rzadziej.

Wyniki:

- 134 sesje (131 aktywnych) na 4 repozytoriach
- 4 600 wiadomości
- 8,5M tokenów wejściowych, 2,4M wyjściowych
- 7 498 wywołań narzędzi
- Całkowity koszt: **$2.97**
- Czas: ~3 godziny

**Kod:**

- +23 505 dodanych linii, -12 503 usuniętych, 412 plików
- Na sesję (średnio): +398 linii, -212 linii, 7 plików
- Max w jednej sesji: +6 338, -6 680, 106 plików

### Ile kosztowałoby to na innych modelach?

| Model | Szacowany koszt | vs deepseek |
|---|---|---|
| deepseek v4 flash | **$2.97** (rzeczywisty) | 1× |
| Kimi K2.6 | ~$161 | 54× |
| Sonnet 4.6 | ~$1,663 | 560× |
| Opus 4.7 | ~$2,772 | 933× |

### Czego się nauczyłem:

- **Szybkość ma znaczenie bardziej niż myślisz.** To, co spodziewałem się robić przez kilka wieczorów, zostało zrobione w 3 godziny.
- **Jakość kodu mnie zaskoczyła.** Wystarczająco dobra, bym rozważał deepseek v4 flash jako poważnego kandydata do codziennego użytku.
- **Podejście workflow w markdownie zawiodło** — i o to właśnie chodziło.

### Co dalej:

1. Stworzyć własne narzędzie workflow oparte na YAML
2. Porównać zużycie tokenów: YAML vs wszystko-w-LLM
3. Migracja z opencode-go do lżejszej alternatywy

### Podsumowanie

Budowanie niezawodnych systemów agentowego kodowania wciąż jest otwartym problemem. Mój zakład: słodki punkt to używanie drogich modeli do planowania i rozbijania zadań, a potem przekazywanie wykonania tanim, szybkim modelom.

Największym zaskoczeniem — i największym zwycięzcą — był sam deepseek v4 flash. Poszedłem do tego eksperymentu spodziewając się, że większość outputu wyląduje w koszu. Po ręcznym przejrzeniu kilku zmian i przepuszczeniu reszty przez Opus, werdykt był jasny: **większość kodu była solidna. Część była naprawdę dobra.** Model za $2.97 produkujący kod jakości produkcyjnej w 4 językach i 400+ plikach. To zmienia to, co myślałem, że jest możliwe.
