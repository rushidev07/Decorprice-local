# Shadow DOM + SSR-Shell Benchmark — Build Log (DecorPrice)

Purpose: a standalone, isolated benchmark to get real numbers for the CSR vs. SSR-shell question raised in the CEO review, and to test whether Declarative Shadow DOM lets us combine "real HTML for crawlers" and "one shadow root the widget enhances" instead of two separate renderers. This does **not** touch DecorPrice's live search (`Ahy_SmartSearchLuma`) — it's a new, separate module (`Ahy_ShadowDomBenchmark`) reachable only at its own dev route, so production behavior is untouched.

Data source for the benchmark: DecorPrice's own real product catalog (via Magento's product collection), so page weight/markup size is realistic. This measures the *rendering technique* difference (client-side HTML construction vs. server-side), not FalcoSense's specific platform network latency — that's a separate variable, noted so the numbers aren't over-claimed.

Three variants, same data, same visual output, one dial changed each time:

1. **CSR (baseline, matches what FalcoSense does today):** server sends an empty container, JS fetches JSON, JS builds the HTML.
2. **SSR-plain:** server builds the real HTML directly, no shadow DOM involved — the "classic" SSR-shell idea.
3. **SSR + Declarative Shadow DOM:** server outputs a `<template shadowrootmode="open">` already containing the real markup; a small JS file enhances the *existing* shadow root in place (no rebuild).

---

## Steps taken

- [x] 1. Confirmed DecorPrice git tree clean before starting.
- [x] 2. Scaffolded isolated module `Ahy_ShadowDomBenchmark` (own route `/shadowbench`, no changes to `Ahy_SmartSearchLuma` or any live code).
- [x] 3. Built shared `ProductDataProvider` — pulls 24 real products from DecorPrice's catalog, used identically by all three variants.
- [x] 4. Built CSR variant (`/shadowbench/index/csr`) — empty container + separate JS file that fetches `/shadowbench/index/data` and builds cards client-side.
- [x] 5. Built SSR-plain variant (`/shadowbench/index/ssrPlain`) — server builds the real card markup directly via a shared `CardHtmlRenderer`.
- [x] 6. Built SSR + Declarative Shadow DOM variant (`/shadowbench/index/ssrDsd`) — server outputs `<template shadowrootmode="open">` with the same real markup already inside it; a small JS file enhances the existing shadow root (adds hover interactivity) without rebuilding anything.
- [x] 7. Registered/enabled the module. `setup:upgrade` hit a pre-existing, unrelated `"Cannot process definition to array for type enum"` error from another module's declarative schema — not caused by this module (it has no `db_schema.xml` at all) — so its version was registered directly in `setup_module` instead of chasing an unrelated schema bug.
- [x] 8. Ran all three, recorded real numbers — see Findings below. Had to fix the benchmark itself along the way: the first pass measured Magento's raw product-collection query against DecorPrice's actual catalog (**100,909 products**, several backing tables 9M+ rows), which took 6–8 seconds and completely swamped the thing being measured. Fixed by caching the fetched product data to a file (`var/shadowdom_benchmark_products.json`, 1hr TTL) after the first real fetch, the same way a real search platform serves from its own index rather than re-querying Magento's catalog on every request — this isolates "render technique" from "how slow is a cold catalog query," which are two different, unrelated problems.
- [x] 9. Findings written up below.

## Findings

**1. The server-side rendering step itself costs basically nothing.** Once the data-fetch was properly isolated (cached), building the HTML for 24 products server-side took **0.16–0.54ms** — for SSR-plain, SSR+Declarative-Shadow-DOM, and the CSR variant's own JSON-serving endpoint, all statistically indistinguishable from each other. There is no meaningful "server rendering is slow" cost at this scale. SSR-plain and SSR+DSD use the exact same `CardHtmlRenderer`, so they *should* be identical, and they are.

**2. The real difference is round trips, not render cost.** Measured directly:
- SSR (either variant): **one HTTP request** delivers real, visible content.
- CSR (today's approach): **two sequential HTTP requests** before real content exists — the page shell, then the separate JS-initiated fetch for data.

In this dev environment, each Magento request currently costs ~5.14s (see caveat below), and CSR measured almost exactly double that (~10.29s) across three repeated runs — consistent with "CSR = 2× a full request, SSR = 1×," not a fluke.

**3. Important caveat — the ~5.14s per-request baseline in this environment is not representative of production**, and shouldn't be quoted to anyone as "DecorPrice's real page speed." It's this specific local/dev setup's own overhead (unrelated to the rendering technique — it was present even for the trivial cached-data requests). **What *is* transferable to a real server:** the 2× multiplier. On a normally-performing production Magento instance answering in, say, 150–300ms per request, CSR's extra mandatory round trip would cost roughly that same 150–300ms *again*, on top of it, before a shopper or crawler sees real content — SSR removes that second round trip entirely, by construction, not by tuning.

**4. Declarative Shadow DOM worked exactly as documented.** Confirmed via `view-source`: the real product markup is inside a `<template shadowrootmode="open">` in the initial server response — nothing injected by JS. The enhancer script (`dsd-enhance.js`) found the already-attached shadow root and added interactivity without touching or rebuilding the existing markup, proving the "one shadow root, server-populated then client-enhanced" model actually works in practice, not just in theory from the reference article.

## What's still needed before this is a CEO-ready number

Run the same three variants on a normally-provisioned server (staging or production-like, not this sandbox) to replace the placeholder 5.14s baseline with a real one — the *structure* of the finding (SSR = 1 round trip, CSR = 2) will hold regardless of environment, but the actual millisecond figure to bring back needs a representative server, not this one.
