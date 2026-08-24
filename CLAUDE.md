# Roberts Law and Mediation — Website Build

## What this is

Marketing website for Joni K. Roberts Law and Mediation Office, a solo law firm in
Memphis, Tennessee. Practice areas: family law, probate and estate planning,
general civil litigation, and mediation.

Domain: robertslawandmediation.com

This is an attorney advertising site in a regulated profession. Content accuracy
is a compliance matter, not a quality preference. Read the rules below before
writing any copy.

## Source of truth

Approved copy and architecture live in `/content/`. Do not invent copy that
contradicts these files.

- @content/website-copy.md — approved page copy, title tags, meta descriptions, JSON-LD
- @content/site-architecture.md — URL structure, page inventory, internal linking rules
- @content/testimonial-process.md — testimonial collection process and consent requirements

If a page needs copy that isn't in those files, draft it and flag it for review.
Never publish placeholder copy as if it were final.

## Non-negotiable content rules

These come from the Tennessee Rules of Professional Conduct. Violating them
creates real exposure for the client.

1. **Never use "expert," "expertise," "specialist," or "specialize"** to describe
   the attorney or the firm. RPC 7.4 restricts specialist claims absent
   certification. Use "handles," "practices in," "focuses on."

2. **Never invent or reinstate credentials.** Tennessee Supreme Court Rule 31
   mediator listings were deliberately removed from this site. Do not add them
   back, do not add bar certifications, awards, "Super Lawyers" style honors, or
   years-of-experience claims not present in the source files.

3. **Never invent testimonials, reviews, star ratings, or client quotes.** Not
   even as placeholder or lorem-ipsum content. Testimonials require documented
   written client consent. If a testimonials section needs markup, build it with
   an empty state.

4. **Never add `Review` or `AggregateRating` schema.** Self-reported review
   schema risks Google penalties and, here, publishing unconsented client
   statements.

5. **Never promise or imply results.** No "we win," "proven results," "fighting
   for you," or outcome guarantees.

6. **The legal disclaimer appears on every page.** Exact wording is in the copy
   file. Do not paraphrase it.

## Placeholders — do not fill these in

These values are genuinely unknown. Inventing something plausible is worse than
leaving the placeholder, because wrong NAP data breaks local search and wrong
legal content is a compliance problem.

- `[STREET_ADDRESS]` — not yet provided by client
- `[POSTAL_CODE]` — not yet provided
- `[OFFICE_HOURS]` — not yet provided
- `[GOOGLE_REVIEW_LINK]` — not yet created

Leave them as literal placeholders. Do not substitute a generic Memphis address,
"9-5 Monday through Friday," or a guessed ZIP.

## Known contact facts

Use these exactly, character for character, everywhere they appear — page copy,
footer, JSON-LD, and any structured data. Entity consistency across the site is
a deliberate strategy, not incidental.

- Firm name: Joni K. Roberts Law and Mediation Office
- Attorney: Joni K. Roberts
- Phone: (901) 800-2948 — in schema as `+1-901-800-2948`
- Email: jkroberts@robertslawandmediation.com
- City/region: Memphis, TN
- Service area: Memphis and Shelby County, Tennessee

## Technical conventions

- **URLs:** lowercase, hyphenated, no dates, no `/practice-areas/` prefix.
  Full inventory in the architecture file. Never change a live URL without a 301.
- **One `<h1>` per page.** Heading levels in order, no skips.
- **Schema:** `Organization` and `Attorney` on every page via a shared include.
  Page-specific schema per the architecture file. Validate before commit.
- **Canonical:** self-referencing on every page. One hostname sitewide.
- **Mobile-first.** Click-to-call phone in the mobile header.
- **Core Web Vitals targets:** LCP < 2.5s, INP < 200ms, CLS < 0.1.
- **Images:** descriptive alt text, modern formats, explicit dimensions.
- **Do not block AI crawlers** in robots.txt. GPTBot, ClaudeBot, PerplexityBot,
  and Google-Extended stay allowed — the site is optimized for AI retrieval and
  blocking them defeats the purpose.

## Sensitive pages

`/orders-of-protection/` needs care beyond normal marketing. Someone reading it
may be in immediate danger.

- Include the National DV Hotline: 1-800-799-7233
- Include a note that browsing history may be visible to others
- Include a quick-exit mechanism
- No testimonials on this page

## When to stop and ask

- Copy is needed that isn't in the source files and involves a legal claim
- A change would alter a live URL
- Anything touching credentials, testimonials, results, or fee statements
- A placeholder above blocks the work

Draft it, flag it, and wait. Attorney review is required before any legal
content goes live.
