# Mellenade Sports Cards — Site Optimization

Performance, accessibility and SEO work for [mellenade.com](https://mellenade.com).

Every change in this repo is tied to a **measured** finding from a PageSpeed Insights /
Lighthouse 13.4.1 run against the live home page on **2026-08-24**.

---

## Audit baseline

**Stack (confirmed from the audit):** WordPress · WooCommerce 11.0.1 · Themeco **Pro**
theme + Cornerstone 7.9.2 · **WP Rocket** with RocketCDN.

| Category | Mobile score |
|---|---|
| Performance | **88** |
| Accessibility | **94** |
| Best Practices | **100** |
| SEO | **92** |

| Core Web Vital (lab) | Value | Verdict |
|---|---|---|
| Largest Contentful Paint | 2.1 s | good |
| First Contentful Paint | 1.8 s | needs work |
| Total Blocking Time | **0 ms** | perfect |
| **Cumulative Layout Shift** | **0.171** | **fails** (target < 0.10) |
| Speed Index | 4.0 s | needs work |
| Server response time | **800 ms** | **fails** |

No CrUX field data — the site doesn't yet have the real-user traffic volume Google needs,
so all figures above are lab measurements.

### The site is in better shape than expected

Two results reset the priorities, and I'd rather say so plainly than quietly drop my
earlier assumptions:

- **TBT is 0 ms.** JavaScript is *not* a bottleneck. Script-weight reduction — which is
  where most WooCommerce advice starts — buys effectively nothing here.
- **WP Rocket and RocketCDN are already installed.** Caching, minification and CDN
  delivery are handled. There's no win available in re-solving that.

The real problem is **fonts**, and it wasn't visible without the measurement.

### Page weight: 733 KB over 27 requests

| Type | Requests | Size | Share |
|---|---|---|---|
| **Font** | 5 | **340 KB** | **46%** |
| Image | 12 | 266 KB | 36% |
| Script | 8 | 91 KB | 12% |
| Document | 1 | 36 KB | 5% |

**`fa-solid-900.woff2` is 276 KB on its own — 38% of the entire page.** Cornerstone ships
the complete Font Awesome Solid set to render a handful of icons. It is, by a wide margin,
the single most expensive thing on the site, and it is the top action item below.

---

## What's fixed in this repo

Implemented as a **must-use plugin**, not a child theme. That was chosen before the theme
was known, but it still holds: an mu-plugin can't be deactivated by accident, and it won't
be overwritten by a Pro theme update.

### 1. Fonts & layout shift — `class-fonts.php`
CLS is 0.171 against a 0.10 target. The recorded shifts are:

| # | Score | Element |
|---|---|---|
| 1 | 0.126 | `main.x-layout` |
| 2 | 0.032 | hero `h2.x-text-content-text-primary` |
| 3 | 0.013 | `div.x-section` (article body) |

Text blocks moving *after* paint is the signature of a web font swapping in and re-flowing
the line box. The fix is a **metric-matched fallback**: a `@font-face` whose
`size-adjust` / `ascent-override` / `descent-override` make the fallback occupy exactly the
same space as Poppins, so the swap becomes visually invisible and contributes no CLS.

Also adds a `preconnect` to `fonts.gstatic.com` — confirmed by the audit as the origin of
four Poppins requests, each currently paying a full DNS + TCP + TLS handshake on the
critical path for text — and forces `display=swap`, which is only safe *because* the metric
overrides remove the reflow.

### 2. Accessibility — `assets/accessibility.css`
Four contrast failures, fixed with colours computed to clear WCAG AA while preserving hue
and saturation so the brand still reads as the brand:

| Element | Was | Ratio | Now | Ratio |
|---|---|---|---|---|
| `.mln-hdr-cta` (header CTA) | `#28ab4d` | 2.99:1 | `#20883d` | 4.52:1 |
| `h1 …-subheadline` | `#28ab4d` | 2.99:1 | `#20883d` | 4.52:1 |
| `.x-anchor-text-primary` | `#939fab` | 2.70:1 | `#697887` | 4.53:1 |
| `h6 …-text-primary` (×6) | `#989a9c` | 2.82:1 | `#747779` | 4.51:1 |

The h6 grey failed on six separate elements, making it the most frequent failure — though
the header CTA is the most commercially important one, since it's your
*Sell Your Collection* call to action.

`tests/verify-contrast.py` recomputes every ratio from the stylesheet and fails the build
if any drops below 4.5:1, or if an original failing colour is reintroduced.

### 3. SEO — `class-seo.php`
The home page **has no meta description** (SEO 92). Without one Google composes the snippet
from whatever text it finds first — navigation chrome, not the value proposition. Now
generated from excerpt → content → tagline, stripped of shortcodes and HTML, and truncated
to ~155 characters on a word boundary.

Still stands down entirely if Yoast / Rank Math / AIOSEO / SEOPress / TSF is active, since
duplicate metadata is worse than none.

### 4. WooCommerce — `class-woocommerce.php`
**The audit confirmed this one was worth doing.** The home page — which sells nothing
directly — fires `cart-fragments.min.js` *and* an uncached
`https://mellenade.com/?wc-ajax=get_refreshed_fragments` XHR on every single view. That's a
full PHP request per pageview for a cart counter that is usually empty.

Now scoped to pages that actually render store markup. Detection covers WooCommerce
conditionals, endpoints, shortcodes and blocks, because a false negative would strip
styling from a real store page.

### 5. Core cleanup — `class-cleanup.php`
Emoji script, oEmbed discovery, generator/RSD/WLW tags, jQuery Migrate, XML-RPC; Heartbeat
throttled to 60 s. Modest given TBT is already 0 ms — kept because it's free and reduces
attack surface, not because it'll move the score.

---

## Install

```
wp-content/mu-plugins/
├── mellenade-performance.php          <- loader (must be top-level)
└── mellenade-performance/
    ├── bootstrap.php
    ├── assets/accessibility.css
    └── includes/
```

WordPress only auto-loads PHP at the **top level** of `mu-plugins/`, which is why the
loader is separate from the code it boots. No activation step.

**After deploying, purge the WP Rocket cache** or the old markup will keep serving.

### Configuration
Every module is opt-out from `wp-config.php`:

```php
define( 'MELLENADE_PERF_DISABLE_FONTS', true );   // or _CLEANUP, _ASSETS, _WOOCOMMERCE, _SEO
```

Opt-in flags:

```php
// Drop cart fragments on ALL non-store pages, not just empty-cart sessions.
define( 'MELLENADE_PERF_AGGRESSIVE_FRAGMENTS', true );

// Turn off the WooCommerce Analytics package.
define( 'MELLENADE_PERF_DISABLE_WC_ANALYTICS', true );
```

Cart fragments default to the conservative empty-cart-only behaviour so header counters
keep updating live. Cart and checkout are never touched.

## Tests

```bash
./tests/run.sh
```

Lints every PHP file, runs **72 behavioural assertions** against WordPress stubs, then
verifies the contrast maths independently. No WordPress install required.

---

## Recommended next, in measured-impact order

**1. Font Awesome — 276 KB, 38% of the page.** The biggest win available, and it needs a
decision only you can make because it depends on which icons the design actually uses:
  - *Best:* replace the handful of used icons with inline SVG and drop the font entirely.
  - *Good:* subset `fa-solid-900.woff2` to just those glyphs — typically 276 KB → under 10 KB.
  - *Quick:* Cornerstone can be told to load only the icon sets in use.

**2. Server response 800 ms** (est. 700 ms saving). This is TTFB on the HTML document,
which bypasses the CDN. Since WP Rocket is already caching, suspect the origin host —
check whether the home page is actually being served from cache, and review PHP version
and any slow plugin queries.

**3. Images — 241 KB available.** The brand logos are oversized PNGs: `Topps_Logo.png`
(98 KB), `Upper_Deck_Logo.svg_.png` (72 KB), `international-pokmon-logo…png` (56 KB).
Note the filename on the second one — an **SVG that was exported to PNG**, which is
backwards; the original vector would be a fraction of the size. Serve these as WebP/AVIF,
or better, as SVG.

**4. Heading order.** An `h6` is used out of sequence, which breaks screen-reader document
outline. A styling choice that should be CSS, not heading level.

**5. Third-party textures.** Two background patterns load from `transparenttextures.com`
(3 KB total). Trivial in bytes, but it's an extra DNS + TLS handshake to an origin you
don't control — self-host them.

### Still unmeasured
Only the **home page** was audited. Product and category pages are where the money is and
they behave differently — more images, real WooCommerce markup, cart fragments legitimately
active. Worth a separate run once the above lands.

Desktop strategy returned HTTP 500 from Google's API across two attempts — a PSI-side
fault, not a site problem. Mobile is the right primary target regardless, since most card
buyers browse on phones.
