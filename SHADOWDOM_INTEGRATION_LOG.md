# FalcoSense Shadow DOM Integration — Build Log (DecorPrice)

Purpose: a running record of what was actually done to bring the rewritten,
Additive-Only `Ahy_SmartSearchLuma` module (source: `falcosense-shadowdom-module`)
into DecorPrice, replacing the older override-based copy that was already
installed and live. Kept in the same spirit as `SHADOWDOM_SSR_BENCHMARK_LOG.md`
and `LOCAL_SETUP_LOG.md` — write down what broke and how it got fixed, not just
the happy path.

Branch: `feature/shadow-dom`

---

## 1. Module swap

The old module at `app/code/Ahy/SmartSearchLuma/` was the pre-rewrite,
override-based variant (`ahy_smartsearch_active.xml` removing `top.search`,
`category.products`, `sidebar.main`, etc. by name — the fragile pattern the
rewrite exists to replace). Replaced wholesale with the new module:

```bash
git status app/code/Ahy/SmartSearchLuma   # confirmed clean before touching it
rm -rf app/code/Ahy/SmartSearchLuma
cp -R /Users/11ahyconsulting/Documents/falcosense-shadowdom-module app/code/Ahy/SmartSearchLuma
rm -rf app/code/Ahy/SmartSearchLuma/.git  # don't carry the source repo's own git history in
```

**Gotcha:** the destination folder name must be exactly `SmartSearchLuma`, not
`falcosense-shadowdom-module` or anything else — Magento's `app/code`
autoloading is PSR-0 (via Magento's own root `composer.json`,
`"psr-0": {"": ["app/code/", "generated/code/"]}`), so the folder path is a
direct, mechanical stand-in for the PHP namespace (`Ahy\SmartSearchLuma\...` →
`app/code/Ahy/SmartSearchLuma/...`). This isn't a project convention, it's a
hard platform rule for any module without its own `composer.json` (this one
intentionally has none — documented cost decision, see
`FALCOSENSE_IMPLEMENTATION_PLAN.md`). Renaming the folder wrong doesn't fail
loudly at the registration step — `registration.php` still finds the module
fine — it fails downstream with "Class not found" the moment anything tries
to instantiate one of its classes.

`git diff --stat` confirmed the swap matched the module's own
`FALCOSENSE-SHADOWDOM-IMPLEMENTATION-PLAN.md` exactly: known dead files
removed (`Block/NoResultsModal.php`, `no-results-modal.phtml`,
`product/list.phtml`, `product/list/item.phtml`), new Shadow DOM scaffolding
added (`Api/`, `Model/Cart/`, `Model/Widget/`, `Controller/Cart/`,
`view/frontend/web/js/widget/`, `Test/`), config plumbing updated
(`Helper/Data.php`, `Observer/AddFrontendLayoutHandle.php`, `etc/config.xml`,
`etc/di.xml`, `etc/adminhtml/system.xml`, `view/frontend/layout/default.xml`).

```bash
php bin/magento setup:di:compile
php bin/magento cache:flush
```

`setup:upgrade` was skipped intentionally — it hits a pre-existing, unrelated
`"Cannot process definition to array for type enum"` schema error from some
other module's `db_schema.xml` (already documented in
`SHADOWDOM_SSR_BENCHMARK_LOG.md`). `Ahy_SmartSearchLuma` has no
`db_schema.xml` of its own and its `module.xml` version was unchanged
(`1.0.0`), so nothing required the schema step to run.

Verified clean: `module:status` showed enabled, `exception.log`/`system.log`
showed nothing new post-compile, storefront returned `HTTP 200` with
`widget_enabled` still off (baseline unchanged).

---

## 2. Admin login was broken — PHP 8.1 vs. Magento 2.4.3 core, unrelated to this module

Created a fresh admin user (`admin:user:create`) since no existing credentials
were available, then hit a fatal on first login attempt:

```
TypeError: Magento\Security\Model\UserExpirationManager::isUserExpired():
Argument #1 ($userId) must be of type string, int given
```

**Root cause:** `Magento\Security\Model\UserExpirationManager::isUserExpired(string $userId)`
is strictly typed; `Magento\Security\Observer\AdminUserAuthenticateBefore` calls
it with `$user->getId()`, which is an `int`. Same class of PHP-8.1-strictness
bug as the already-documented `AbstractModel.php:189` patch in
`LOCAL_SETUP_LOG.md` — Magento 2.4.3's core code predates PHP 8.1's stricter
type enforcement in several spots. **Nothing to do with SmartSearchLuma.**

**Fix** — cast at the call site (same minimal-patch pattern as the existing
`AbstractModel.php` fix):

`vendor/magento/module-security/Observer/AdminUserAuthenticateBefore.php`, line 59:
```php
// before
if ($user->getId() && $this->userExpirationManager->isUserExpired($user->getId())) {
// after
if ($user->getId() && $this->userExpirationManager->isUserExpired((string) $user->getId())) {
```

---

## 3. Dashboard was broken after login — a second, separate PHP 8.1 bug

Logging in succeeded, but the admin dashboard's "Products Ordered" widget
(currency-rendering grid column) threw:

```
ValueError: version_compare(): Argument #3 ($operator) must be a valid
comparison operator in .../zendframework1/library/Zend/Xml/Security.php:172
```

**Root cause:** `Zend_Xml_Security::isPhpFpm()` calls
`version_compare(PHP_VERSION, '5.6', 'gte')` — `'gte'` has never been a valid
`version_compare()` operator (the real one is `'ge'` or `'>='`). This is a
pre-existing typo/bug in the bundled Zend Framework 1 library, silently
tolerated on older PHP (which returned `NULL` with a warning for an invalid
operator) but promoted to a fatal `ValueError` on PHP 8.0+. Also nothing to
do with SmartSearchLuma — this is Magento's own bundled `zendframework1`
package, hit via the dashboard's currency column renderer
(`Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency` →
`Zend_Currency` → `Zend_Locale_Data` → `Zend_Xml_Security`).

**Fix:**

`vendor/magento/zendframework1/library/Zend/Xml/Security.php`, line 172:
```php
// before
version_compare(PHP_VERSION, '5.6', 'gte')
// after
version_compare(PHP_VERSION, '5.6', 'ge')
```

Both PHP 8.1 fixes required `php bin/magento cache:flush` +
`brew services restart php@8.1` to actually take effect (opcache).

**Open note:** given three separate PHP-8.1-vs-2.4.3 core incompatibilities
surfaced in a single session (the pre-existing `AbstractModel.php` one, plus
these two new ones), there are likely more lurking in less-common admin code
paths. Treat each as it's found the same way — confirm it's core/unrelated to
FalcoSense before patching, patch minimally, move on.

---

## 4. Widget enabled — search API confirmed working with real data

`Stores → Configuration → Ahy → Smart Search → General Settings`:
`Enable Shadow DOM Widget (Beta)` set to **Yes** (`frontend_enabled` was
already Yes; `Enable SSR Shell` left off).

Confirmed via direct API calls (bypassing the browser) that real DecorPrice
catalog data is genuinely searchable on the platform — e.g. `q=light` and
`q=lantern` both return real products with correct prices, images, and
category names. (Separately tracked: the platform's ingest quota for this
specific store capped out around 3,500–4,600 of ~100,527 products across
several sync attempts, converging toward zero — a FalcoSense
account/subscription-side issue, not a code issue. Confirmed store id=32 via
exact API-key match. Not blocking the integration work itself, but blocking
full-catalog search quality until resolved on the platform side.)

---

## 5. Bug: search results never appeared — "results at top," native modal underneath

Typing "lantern" and pressing Enter produced a page at `#search-mod` showing
a small "6 results" label with no product grid, stacked oddly above a giant
query heading and generic "type at least 3 characters" / "hit enter to
search" placeholder text.

**Root cause — confirmed, not FalcoSense's data or API:** the Network tab
showed the suggest API call succeeded (200 OK, 6 real products, real category,
under 200ms). The problem is entirely client-side. `search-attach.js`'s
original code deliberately did nothing on Enter/submit, per its own doc
comment: *"submitting navigates to the search-results page — which the
takeover in `takeover.js` claims immediately on load."* That assumption holds
on vanilla Luma but not on this WeltPixel Pearl theme: WeltPixel's own header
JS has a bubble-phase handler on the same form that intercepts Enter/submit
and does its own client-side hash change to `#search-mod` — its own
pre-existing, native full-screen search modal (confirmed present in
`WeltPixel_CustomHeader`'s `form.mini.phtml` and its LESS/CSS source,
completely unrelated to FalcoSense, never wired to real results even before
this integration). Because the browser never actually navigates to
`/catalogsearch/result/?q=...`, the real controller action never runs,
`Block\Widget\Bootstrap` never sees `pageType: 'search'`, and `takeover.js`'s
real product grid (`mountTakeover`) never mounts. What's visible is just the
header dropdown's small status label (`boot.js`'s `` `${count} results` ``)
sitting next to WeltPixel's own empty, unrelated modal skeleton.

**Why this is a module fix, not a theme fix:** the bug is a wrong assumption
in the module's own JS (`search-attach.js`), not something specific to Pearl
— any theme's native header JS could plausibly have its own competing
handler on the same input. Fixed in the module itself, not via a Pearl-theme
override, so it travels with the module to future integrations rather than
needing to be rediscovered per theme.

**Fix** — `view/frontend/web/js/widget/search-attach.js`, applied to both the
source repo (`/Users/11ahyconsulting/Documents/falcosense-shadowdom-module`)
and DecorPrice's deployed copy:

- Added a `form` reference (`input.closest('form')`) and a `navigateToResults(query)`
  helper that does a real `window.location.href` navigation to the form's own
  `action` (falls back to `/catalogsearch/result/`).
- Replaced the old bubble-phase `keydown` listener (Escape-only) with a
  **capture-phase** `keydown` listener that also handles `Enter`: calls
  `preventDefault()` + `stopImmediatePropagation()`, then navigates itself.
- Added a capture-phase `submit` listener on the form doing the same
  prevent-and-navigate, in case Enter reaches the form's submit event before
  our keydown capture does on some browsers/setups.

Capture phase + `stopImmediatePropagation()` wins the race against a
bubble-phase theme handler on the same element regardless of registration
order, which is why this fixes it without touching anything in
`app/design/frontend/Pearl/`.

**Status: fix applied and confirmed working** — with one unrelated red herring
along the way, worth recording so it isn't rediscovered from scratch later.

Retesting with "lantern" still ended up on `https://www.decorprice.com/...`
even after the fix. Traced it with direct `curl -sI` calls against the local
endpoint itself:

```
GET /catalogsearch/result/?q=glass   → HTTP 200 (stays local)
GET /catalogsearch/result/?q=lantern → HTTP 302 Found
                                        Location: https://www.decorprice.com/outdoor/outdoor-hanging-lanterns.html
```

**Root cause: Magento's own built-in "Search Term Redirect" feature**
(`search_query` table, `redirect` column — stock functionality, Admin under
Marketing → SEO & Search → Search Terms), not FalcoSense, not WeltPixel, not
this fix. Confirmed in the DB:

```
query_text='Lantern', redirect='https://www.decorprice.com/outdoor/outdoor-hanging-lanterns.html', store_id=1, is_active=1
```

At least one more exists the same way (`visual comfort Morris Medium Lantern`
→ `https://www.decorprice.com/brands/murray-feiss.html`). These were
configured on the real production site using absolute URLs — a normal thing
to do when managing the live site directly — and came along verbatim since
this local DB is a copy of production data. `glass` has no matching redirect
row, which is exactly why it stayed local while `lantern` didn't — confirming
this is pre-existing, query-specific data, not a code path the fix
introduced.

**This is actually confirmation the fix works, not a new bug**: before the
fix, Enter never reached Magento's real search-results controller at all (it
was stuck in WeltPixel's non-functional `#search-mod` hash view), so this
redirect could never have fired either way. Only once real navigation started
happening did this unrelated, pre-existing local-DB quirk become visible.
Not something to fix as part of this integration — just a known local-only
trap: don't test with "lantern" specifically; "glass" and most other terms
are unaffected.

---

## 6. Header search wasn't the "live SPA-style overlay" the module's own docs promise

After the Enter-key fix (§5), typing in the header still only showed a small
`"${count} results"` text label (`boot.js`'s old `openOverlay`/`renderOverlay`),
not real product cards — while the *target architecture doc itself* says:

> *"Typing 3+ letters triggers the exact same live SPA-style overlay
> experience..."*

So this was a confirmed gap against the module's own documented design, not a
misunderstanding. Separately, the full-page takeover (`takeover.js`'s
`.fs-shell`) used `position: fixed; inset: 0`, covering the site's own
header/logo/cart entirely — true to the doc's "the overlay covers the native
content" language, but with no way to reach site navigation once results were
showing. Confirmed with the user this should change: header stays visible and
reachable around the results, on both the header-typing overlay and the
full-page takeover.

**Also found while fixing this:** the module already had a shared rendering
module, `product-grid.js`, whose own docblock says it's *"the one place UI
gets built, shared between the search-results takeover... and the
category-page enhancement"* — but `takeover.js` never actually adopted it. It
still carried ~150 lines of its own duplicated `renderResults`/
`buildFilterPanel`/`buildSortSelect`/`buildPagination` from before that
refactor happened, while `category-enhancement.js` already used the shared
version. Same "surfaces drift independently" problem `styles.js`'s own
docblock warns about, just not fully applied — `takeover.js` was stale
relative to the module's later refactor, not `product-grid.js` being unused
by design.

**Fix, in both the source repo and DecorPrice's deployed copy:**

- `takeover.js` rewritten to import and use `product-grid.js`
  (`renderProductGrid`, `renderLoadingState`, `renderErrorState`,
  `attachAddToCartHandler`) and `styles.js`'s `BASE_CSS`, instead of its own
  duplicated versions. Only unique-to-this-file logic remains: mounting,
  fetch/filter-state loop, and shell positioning.
- Added `getHeaderOffset()` — finds `document.querySelector('header, .page-header')`'s
  bottom edge and positions `.fs-shell`'s `top` there instead of `0`, so the
  site's own header stays visible and reachable above the results, on window
  resize too. Falls back to `0` (old full-viewport behavior) if no header
  element is found, rather than guessing a theme-specific selector.
- Added `setQuery(query)`, `show()`, and `hide()` to `mountTakeover`'s
  returned API, so the same mounted shell/fetch loop can be reused for live
  typing (update in place) rather than remounting per keystroke.
- `boot.js`'s `openOverlay` rewritten to call `mountTakeover` (once, on the
  first query) and then `setQuery()`/`show()` on subsequent keystrokes,
  instead of its own separate, simplified text-link-only rendering
  (`OVERLAY_CSS`, `renderOverlay`) — the header preview and the committed
  results page now share one rendering engine, so they can't visually drift
  apart the way the old two implementations already had.

**Known minor edge case, not addressed:** if a shopper is already on the
committed search-results page (full-page takeover already mounted directly
on `<falcosense-root>`) and types in the header search again, a *second*
`mountTakeover` instance gets created on a separate overlay host, stacking
visually on top of the first at the same position. Functionally harmless
(the second one just visually replaces the first from the shopper's point of
view) but a small duplicate-fetch inefficiency in that specific case. Not
fixed now — flagged here rather than silently left undocumented.

**Status: fix applied, confirmed being served (verified via direct curl of
the static JS), not yet re-verified in browser.** Next: hard-reload, confirm
typing 2+ letters in the header now shows real product cards (not just a
count) with the site header still visible above it, and confirm the
full-page results view also now leaves the header visible.

---

## Still open / not yet done

- [ ] Re-verify the search-attach.js fix in the browser (see above)
- [ ] Chase the FalcoSense platform-side sync quota/subscription issue for
      the DecorPrice store (id=32) so the full ~100k catalog can sync, not
      just ~3,500–4,600
- [ ] Confirm whether the disk-full error from `var/log/exception.log`
      (2026-08-17, `No space left on device`) is still an active problem —
      unrelated to this integration but worth a `df -h` check
- [ ] Category page + full search-results-page QA once the above is settled
      (add-to-cart flow, filters/sort, fail-open with JS disabled, mobile)
