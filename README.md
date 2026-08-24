# Mellenade Sports Cards — Site Optimization

Performance, cleanup and SEO hardening for the [mellenade.com](https://mellenade.com) WooCommerce store.

> **Status: audit-independent baseline.** Everything here is safe to deploy without having
> seen the live site. The site-specific tuning (image compression targets, plugin audit,
> caching config) is still outstanding — see [Pending](#pending-needs-site-access).

---

## Why a must-use plugin, not a child theme

The store's active theme is not known to this repository. A child theme has to declare a
parent template, and declaring the wrong one takes the site down. A **must-use plugin is
theme-agnostic** — it hooks core and WooCommerce only, so it drops onto the site as-is
regardless of what theme is running.

Must-use plugins also can't be deactivated by accident from the admin, which is what you
want for infrastructure-level code.

## Install

Copy both the loader and its directory into `wp-content/mu-plugins/`:

```
wp-content/mu-plugins/
├── mellenade-performance.php          <- loader (must be top-level)
└── mellenade-performance/
    ├── bootstrap.php
    └── includes/
```

WordPress only auto-loads PHP at the **top level** of `mu-plugins/`, which is why the
loader stub is separate from the code it boots. Create `mu-plugins/` if it doesn't exist.
There is no activation step — mu-plugins are always on.

## What it does

### Core cleanup (`class-cleanup.php`)
| Change | Why |
|---|---|
| Remove emoji script | `wp-emoji-release.min.js` is ~11KB polyfilling emoji browsers have supported for a decade |
| Remove oEmbed discovery + `wp-embed.js` | The store doesn't embed its own posts elsewhere |
| Remove generator/RSD/WLW/shortlink tags | Dead markup; the generator also advertises your exact WP version |
| Drop `wp-block-library` CSS when no blocks render | ~90KB uncompressed; WooCommerce templates are PHP, not blocks |
| Unbind jQuery Migrate on front end | Only shims jQuery 1.x-era calls |
| Throttle Heartbeat to 60s | `admin-ajax.php` polling eats PHP workers |
| Disable XML-RPC | Common brute-force vector; nothing on a modern store needs it |

The block-library removal checks `has_blocks()` and skips cart/checkout/account entirely,
so editorial pages and the WooCommerce block checkout keep their styles.

### WooCommerce (`class-woocommerce.php`) — the big one
WooCommerce loads its CSS and JS on **every page of the site**, including pages with no
store markup. It also ships `wc-cart-fragments`, which fires an **uncached
`admin-ajax.php` request on every single page view** — typically the largest single source
of both wasted bytes and wasted PHP workers on a small store.

This module scopes both to pages that actually need them. Store detection is deliberately
broad — it checks WooCommerce conditionals, endpoints, shortcodes (`[product_category]`,
`[add_to_cart]`, …) and WooCommerce blocks — because a false negative strips styling off a
real store page, which is far worse than shipping a few extra KB.

### Assets & Core Web Vitals (`class-assets.php`)
Card listings are image-dense, so **image delivery dominates Core Web Vitals here** — not
script size. This module:

- adds `decoding="async"` so image decode leaves the main thread
- gives the first product image `fetchpriority="high"` and `loading="eager"`

That second point matters: **lazy-loading the hero image is the single most common LCP
mistake on a WooCommerce store**, because the browser then won't fetch it until layout
settles. Only the *first* image on a *single product* page is promoted — archives are left
alone since the hero is theme-controlled there and guessing wrong would hurt.

Preconnect hints are only emitted for origins provably in use (Stripe/PayPal on
cart+checkout, Google Fonts if actually enqueued). A preconnect to a host the page never
contacts wastes a socket and can slow the page down.

### SEO (`class-seo.php`)
**This module deliberately does almost nothing if an SEO plugin is active.** Duplicate
canonicals or a second Product schema block are actively harmful — Google reads
conflicting structured data as a quality signal against the page. It detects Yoast, Rank
Math, AIOSEO, SEOPress and The SEO Framework and stands down.

When nothing else owns metadata, it adds site-level `Organization` + `WebSite` JSON-LD
(WooCommerce already emits Product and BreadcrumbList on its own templates). The
`SearchAction` node enables a sitelinks search box — worth having for a store people search
by player or set name. Search and author archives are `noindex,follow` to protect crawl
budget on a catalogue that changes constantly as cards sell; product/category/tag archives
stay fully indexable.

## Configuration

Every module is opt-out from `wp-config.php` — no code changes, no redeploy:

```php
define( 'MELLENADE_PERF_DISABLE_CLEANUP', true );      // or _ASSETS, _WOOCOMMERCE, _SEO
```

Two opt-**in** flags:

```php
// Drop cart fragments on ALL non-store pages, not just empty-cart sessions.
// Measurably faster — but verify your header cart counter still behaves first.
define( 'MELLENADE_PERF_AGGRESSIVE_FRAGMENTS', true );

// Turn off the WooCommerce Analytics package (rebuilt by scheduled actions, adds write load).
define( 'MELLENADE_PERF_DISABLE_WC_ANALYTICS', true );
```

### The one trade-off to understand
`wc-cart-fragments` is what updates a header cart counter without a page reload. The
default here is conservative: fragments are dropped **only when the cart is empty**, which
covers the large majority of sessions and is exactly when the AJAX call is pure waste.
Sessions with items keep full live-updating behaviour. Cart and checkout are never touched.

## Tests

```bash
./tests/run.sh
```

Lints every file, then runs 45 behavioural assertions against WordPress stubs — no
WordPress install needed. Covers store-page detection, the shortcode/block escape hatches,
cart-fragment cart-state logic, the LCP first-image-only rule, JSON-LD validity, and
null-cart edge cases.

## Pending (needs site access)

This environment's egress policy blocks `mellenade.com`, so none of the following could be
measured. These need either a widened network policy, a PageSpeed API key, or a pasted
report:

- [ ] Baseline Core Web Vitals (LCP/CLS/INP), mobile and desktop
- [ ] Image audit — card scans are usually the heaviest asset; WebP/AVIF conversion and
      correct `srcset` sizing are likely the biggest remaining win
- [ ] Plugin audit — count, overlap, and any abandoned plugins
- [ ] Page caching / CDN status at the host
- [ ] Render-blocking CSS/JS in the actual `<head>`
- [ ] Whether an SEO plugin is present (decides if the SEO module does anything at all)
- [ ] Product schema completeness: price, availability, condition, `gtin`/`mpn`
- [ ] Mobile UX pass — most card buyers browse on phones
