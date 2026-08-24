# Roberts Law and Mediation — child theme

Child theme of **Themeco Pro** for Joni K. Roberts Law and Mediation Office,
Memphis, Tennessee.

The point of this theme is that **nothing is edited in two places.** Colors,
firm contact details, page metadata, component markup, and the Tennessee RPC
compliance rules each live in exactly one file, and everything else reads from
there.

Read [`CLAUDE.md`](CLAUDE.md) before writing any copy. This is an attorney
advertising site in a regulated profession, and content accuracy is a
compliance matter rather than a quality preference.

---

## Where things live

| Change this… | …edit this file |
|---|---|
| A color, font, size, or spacing value | `config/tokens.php` |
| Phone, email, address, attorney credentials | `config/firm.php` |
| A page's URL, title tag, meta description, H1, or schema | `config/pages.php` |
| A component's fields or which components exist | `config/components.php` |
| Practice areas, FAQs, testimonials as content types | `config/content-types.php` |
| Restricted terms, the disclaimer wording, schema denylist | `config/compliance.php` |
| A component's markup | `components/<slug>/template.php` |
| A component's styles | `components/<slug>/style.css` |

Approved copy and architecture live in [`content/`](content/) and are the source
of truth for everything the site says:

- `content/website-copy.md` — approved page copy, title tags, meta descriptions, JSON-LD
- `content/site-architecture.md` — URL structure, page inventory, internal linking rules
- `content/testimonial-process.md` — testimonial collection and consent requirements

---

## Components

One entry in `config/components.php` produces three things at once:

1. A **Cornerstone / Pro element**, drag-and-drop in the builder
2. A **shortcode**, `[rl_hero heading="..."]`
3. A **PHP call**, `RobertsLaw\Components::render( 'hero', $atts )`

All three run the same `render()` and load the same `template.php`, so there is
no second copy of a component hiding inside a Pro element. Change the template,
and every page using that component changes.

| Component | Shortcode | What it does |
|---|---|---|
| `answer-block` | `[rl_answer_block]` | The 25–50 word direct answer under the H1 — the passage AI assistants quote |
| `hero` | `[rl_hero]` | The page's single H1, defaulting to the inventory |
| `service-grid` | `[rl_service_grid]` | Practice area cards, from the Practice Areas content type |
| `faq` | `[rl_faq]` | Q&A block with `FAQPage` schema matching the visible text |
| `cta-band` | `[rl_cta_band]` | Call / Email / Schedule, phone from `config/firm.php` |
| `contact-card` | `[rl_contact_card]` | Firm NAP block |
| `attorney-card` | `[rl_attorney_card]` | Joni K. Roberts summary and credentials |
| `testimonials` | `[rl_testimonials]` | Consent-gated; empty until real testimonials exist |
| `disclaimer` | `[rl_disclaimer]` | The exact sitewide disclaimer |
| `breadcrumbs` | `[rl_breadcrumbs]` | Silo trail with `BreadcrumbList` schema |
| `safety-exit` | `[rl_safety_exit]` | DV hotline, history warning, quick exit |
| `last-reviewed` | `[rl_last_reviewed]` | Review date and attorney attribution |

### Adding a component

1. Add an entry to `config/components.php`.
2. Run `php bin/generate-elements.php` — this writes the four Cornerstone stub
   files, which contain no configuration and never need editing again.
3. Write `components/<slug>/template.php` and `style.css`.
4. Commit the generated files.

Changing an existing component's **fields** does not require regenerating. The
stubs read the registry at runtime.

---

## Firm facts

Every phone number, email, and name resolves from `config/firm.php`. Nothing is
typed into a page. Entity consistency — the same string, character for
character, in the footer, the copy, and the JSON-LD — is a deliberate strategy
here, not incidental tidiness.

In builder copy, insert a firm fact rather than retyping it:

```
[rl_firm field="phone_display" link="true"]
[rl_firm field="address_line"]
```

Cornerstone Dynamic Content fields are registered too, when the API is
available.

### Placeholders — do not fill these in

Four values are genuinely unknown and are listed in `CLAUDE.md`:
`[STREET_ADDRESS]`, `[POSTAL_CODE]`, `[OFFICE_HOURS]`, `[GOOGLE_REVIEW_LINK]`.

The theme handles them rather than trusting anyone to remember:

- They are **omitted from JSON-LD**, never emitted as literal bracket tokens
- They render as **nothing to visitors**, and as a visible marker to editors
- They raise an **admin notice** on every screen until supplied
- The client can supply address, ZIP, hours, and the review link under
  **Roberts Law → Firm Details** without a deploy

Inventing a plausible Memphis address or "9–5 Monday through Friday" is worse
than an obvious blank: wrong NAP data breaks local search.

---

## Compliance guardrails

`config/compliance.php` holds the rules; `inc/class-compliance.php` and
`inc/class-guards.php` enforce them.

- **Restricted terms.** Saving a post scans it for "expert", "expertise",
  "specialist", "specialize" (RPC 7.4) and results language such as "proven
  results" or "fighting for you" (RPC 7.1). Findings appear as an admin notice.
  Nothing is rewritten — the right substitution is a judgement call, and
  silently changing an attorney's words would hide the problem.
- **Forbidden schema.** `Review` and `AggregateRating` are stripped from the
  JSON-LD graph at the single output point, so they cannot appear even if a
  plugin adds them.
- **Testimonials.** An entry renders only if it has a signed consent form on
  file, a consent date, client approval of the final published wording, and a
  concluded matter — and only if the firm did not serve as the neutral. A
  failing entry does not render whatever its post status. There is no
  placeholder or demo testimonial anywhere in this theme.
- **The disclaimer** prints on every page from one string and is not editable
  in the builder.
- **Sensitive pages.** `/orders-of-protection/` gets the safety block injected
  automatically and testimonials suppressed, whether or not the page was set up
  correctly.

These are guardrails, not a compliance review. Attorney review against the
current Tennessee RPC 7.1–7.5 is still required before anything goes live.

---

## SEO and machine surfaces

Driven entirely by `config/pages.php`:

- Title tags, meta descriptions, and self-referencing canonicals
- One `<h1>` per page, taken from the inventory
- Breadcrumbs and `BreadcrumbList` schema following the silo parents
- `Organization` and `Attorney` on every page via a shared graph
- `/sitemap.xml`, listing only pages that actually exist — `lastmod` omitted,
  because a date that never changes is worse than none
- `/llms.txt`, generated from the same inventory
- `robots.txt` that keeps GPTBot, ClaudeBot, PerplexityBot, and Google-Extended
  **allowed** — the site is optimized for AI retrieval and blocking them
  defeats the purpose

If Yoast, Rank Math, SEOPress, or AIOSEO is active, the theme defers to it for
head tags and keeps only page-key resolution and breadcrumbs, so the two do not
fight over the same tags.

**Never change a `url` in `config/pages.php` after launch without adding a 301.**

---

## Build status

**Roberts Law → Build Status** renders `config/pages.php` as a working
checklist: what is shippable, what is blocked, and on what.

Currently blocked, all client decisions rather than writing tasks:

| Page | Blocked on |
|---|---|
| `/contact/` | Street address and office hours |
| `/mediation/`, `/mediation-faq/` | Rule 31 framing fix; verify the Rule 31 training figures against current ADR Commission standards |
| `/family-dispute-resolution/` | The illegible margin word in the source markup |
| `/client-testimonials/` | Four or more testimonials with documented consent |
| `/fees/` | Fee structure, and whether consultations are free or paid |

---

## Page templates

`config/page-templates.php` holds one blueprint per page: the exact ordered
stack of sections, rows, columns, and components, with copy already placed.
Cornerstone's hierarchy is Section → Row → Column → Element, and the blueprints
are shaped the same way, so the export is a walk rather than a translation.

Two things consume a blueprint:

```bash
php bin/build-tco.php              # serialise for import into Pro
php bin/build-tco.php home about   # or just named pages
```

```php
\RobertsLaw\Page_Template::render( 'home' );   // render directly, no Cornerstone
[rl_page_template key="home"]                   // or as a shortcode
```

Both read the same blueprint, so what you review on screen is what gets
exported.

### Copy provenance

Every block of prose is marked in the surrounding comment:

- **APPROVED** — from `content/website-copy.md`
- **DRAFT** — written in the blueprint, *not reviewed*, must not publish as-is
- **BLOCKED** — cannot be written until a client decision lands

Legal specifics are only asserted where the source documents already assert
them. Where a target question has no sourced answer, the slot reads
`[NEEDS ATTORNEY INPUT — …]` rather than inventing Tennessee law.

A page carrying unresolved slots is flagged `review_required` or `blocked`, and
`Page_Template` **hides it from visitors** while showing it to logged-in
editors. The test suite enforces that pairing, so a draft page cannot quietly
become publicly reachable.

| Page | Copy status |
|---|---|
| `/`, `/about/`, `/joni-k-roberts/` | Approved — ready to review |
| `/family-law/`, `/divorce/`, `/parenting-time/`, `/probate-estate-planning/` | Draft, needs attorney input |
| `/orders-of-protection/` | Draft; safety block leads, testimonials blocked |
| `/mediation/`, `/mediation-faq/` | Blocked on the Rule 31 framing fix |
| `/contact/` | Blocked on address and hours |

### The `.tco` step is not finished

`bin/build-tco.php` builds the normalised element tree and stages it as JSON in
`build/`. It does **not** yet emit `.tco`, because that envelope is not publicly
documented and `theme.co` is blocked by this environment's egress policy. A
malformed `.tco` fails *silently* on import in Manage Library, so guessing at it
would be worse than not shipping one.

One sample Cornerstone export finishes it — everything above `rl_encode_tco()`
stays as-is.

---

## Development

```bash
php bin/generate-elements.php     # regenerate Cornerstone element stubs
php tests/guardrails-test.php     # verify the compliance guardrails (no WP needed)
find . -name '*.php' -not -path './.git/*' -exec php -l {} \;
```

`tests/guardrails-test.php` covers placeholder handling, forbidden schema
stripping, restricted-term scanning, the testimonial consent gate, disclaimer
wording, and token compilation. Run it before committing a change to anything
in `config/compliance.php` or `inc/class-compliance.php`.

### A note on the Themeco Pro API

`theme.co` is blocked by the egress policy of the environment this theme was
built in, so the Cornerstone element registration signature and the control-type
mapping follow the published Element API shape rather than a page-by-page check
against Themeco's current reference. Both are confined to two places —
`inc/integrations/cornerstone-elements.php` and
`RobertsLaw\Components::controls()` — so if Pro's API has moved, the fix is a
small one and every component picks it up.

Everything else in the theme is plain WordPress and does not depend on Pro.
