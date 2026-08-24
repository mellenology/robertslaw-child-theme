# Site Architecture & Sitemap — Joni K. Roberts Law and Mediation Office

Full page inventory with URLs, title tags, meta descriptions, target questions, schema, and internal linking. Built in three phases so you can launch without waiting on the whole thing.

**Read the capacity note at the bottom before committing to the full build.** This plan describes 34 pages. That is the right architecture, and it is also more writing than most solo firms sustain. Phase 1 alone is a complete, competitive site.

---

## Architecture logic

Four decisions drive everything below.

**1. One page per question people actually ask.** A visitor with a parenting time problem and a visitor with a probate problem need different pages. Bundling them into "Practice Areas" means neither ranks and neither converts. AI assistants retrieve at the passage level, so a dedicated page with a focused answer beats a paragraph inside a longer page nearly every time.

**2. Flat hierarchy, silo'd topics.** Nothing more than two levels deep. Every page reachable from the home page in two clicks. Practice areas grouped into silos (family law, probate, mediation) that link densely within themselves and sparsely across — this concentrates topical authority rather than diffusing it.

**3. Mediation lives in its own silo, not under family law.** Mediation is a distinct service with distinct searchers, and some of them are the *other* party or an opposing attorney looking for a neutral. Mixing it into family law confuses both audiences.

**4. FAQ content sits on the parent page, not on separate pages.** One question per URL creates dozens of thin pages that compete with each other. Instead, each practice area page carries its own FAQ block with FAQPage schema. The Mediation FAQ is the exception — it's substantial enough to stand alone.

---

## URL conventions

- Lowercase, hyphens, no underscores, no dates, no stop words
- Include the practice term, exclude the city except on the two intentional location pages
- No `/services/` or `/practice-areas/` prefix — it adds a level and no value
- Trailing slash consistent sitewide, and 301 whichever version you don't pick
- Never change a URL after launch without a 301 redirect

---

# PHASE 1 — LAUNCH (11 pages)

Everything here is either already drafted or short. This is a shippable site.

### 1. Home
- **URL:** `/`
- **Title:** Memphis Family Law Attorney and Mediator | Joni K. Roberts Law and Mediation Office
- **Meta:** Joni K. Roberts is a Memphis, TN attorney handling divorce, parenting time, adoption, probate, and estate planning, with mediation available across all practice areas. Practicing in Tennessee since 2004. Call (901) 800-2948.
- **H1:** Memphis Family Law Attorney and Mediator
- **Schema:** `LegalService` + `WebSite` + `BreadcrumbList`
- **Links out to:** all three silo hubs, attorney profile, contact
- **Word target:** 800–1,200
- **Status:** drafted

### 2. About the Firm
- **URL:** `/about/`
- **Title:** About Joni K. Roberts Law and Mediation Office | Memphis, TN
- **Meta:** Founded by attorney Joni K. Roberts, a Tennessee lawyer practicing since 2004. Family law, estate planning, general civil matters, and mediation in Memphis, Tennessee.
- **H1:** About Joni K. Roberts Law and Mediation Office
- **Schema:** `AboutPage` + `Organization`
- **Word target:** 600–800
- **Status:** drafted

### 3. Attorney Profile — Joni K. Roberts
- **URL:** `/joni-k-roberts/`
- **Title:** Joni K. Roberts, Memphis Attorney and Mediator | Family Law, Probate, Civil Litigation
- **Meta:** Joni K. Roberts has practiced law in Tennessee since 2004, handling family law, probate, and general civil litigation in Memphis, including a successful First Amendment appeal to the Sixth Circuit.
- **H1:** Joni K. Roberts — Memphis Attorney and Mediator
- **Schema:** `ProfilePage` + `Attorney`
- **Word target:** 700–1,000
- **Status:** drafted
- **Note:** Use the attorney's name in the URL, not `/attorney/` or `/our-team/`. This is the page most likely to be cited by an AI assistant asked to name a Memphis attorney, and a name-matching URL strengthens the entity signal. Link to it from every practice page.

### 4. Family Law — silo hub
- **URL:** `/family-law/`
- **Title:** Memphis Family Law Attorney | Divorce, Parenting Time, Child Support
- **Meta:** Family law representation in Memphis and Shelby County: divorce, parenting plans, child support, adoption, and paternity. Attorney Joni K. Roberts has practiced in Tennessee since 2004.
- **H1:** Family Law in Memphis and Shelby County
- **Schema:** `Service` + `FAQPage` + `BreadcrumbList`
- **Links out to:** every family law child page
- **Word target:** 900–1,200
- **Target questions:** What does a family law attorney do? · How much does a family lawyer cost in Memphis? · Do I need a lawyer for an uncontested divorce in Tennessee?

### 5. Divorce
- **URL:** `/divorce/`
- **Title:** Memphis Divorce Attorney | Contested and Uncontested Divorce in Tennessee
- **Meta:** Divorce representation in Memphis and Shelby County, including property division, alimony, and grounds for divorce. Tennessee requires a 60- or 90-day waiting period. Call (901) 800-2948.
- **H1:** Divorce Attorney in Memphis, Tennessee
- **Schema:** `Service` + `FAQPage`
- **Word target:** 1,200–1,800 — this is your highest-volume page, give it room
- **Target questions:** How long does a divorce take in Tennessee? · How much does a divorce cost in Memphis? · What are the grounds for divorce in Tennessee? · Is Tennessee a 50/50 state? · Do I have to go to court for a divorce in Tennessee? · Can I get a divorce without my spouse agreeing?
- **Note:** the waiting-period distinction (60 days without minor children, 90 with) is the kind of concrete jurisdictional fact AI assistants retrieve and cite. Lead with facts like it.

### 6. Parenting Time and Parenting Plans
- **URL:** `/parenting-time/`
- **Title:** Memphis Child Custody and Parenting Time Attorney | Tennessee Parenting Plans
- **Meta:** Tennessee courts issue permanent parenting plans setting residential schedules and decision-making authority. Representation in Memphis and Shelby County for parenting time and custody matters.
- **H1:** Parenting Time and Child Custody in Tennessee
- **Schema:** `Service` + `FAQPage`
- **Word target:** 1,200–1,600
- **Target questions:** What is a permanent parenting plan in Tennessee? · How is custody decided in Tennessee? · What is the difference between custody and parenting time? · Can a parent move out of state with a child? · At what age can a child choose which parent to live with in Tennessee?
- **Note on the heading question from your markup:** this resolves it. URL and title carry "child custody" because that's what people search; the H1 and body use "parenting time" because that's what Tennessee courts and your client actually say. Both audiences served, no compromise needed.

### 7. Mediation — silo hub
- **URL:** `/mediation/`
- **Title:** Mediation Services in Memphis, TN | Divorce and Family Mediation
- **Meta:** Mediation as an alternative to litigation in Memphis: divorce mediation, parenting plan mediation, property division, and family dispute resolution. Joni K. Roberts Law and Mediation Office.
- **H1:** Mediation Services in Memphis, Tennessee
- **Schema:** `Service` + `FAQPage`
- **Links out to:** all mediation child pages + the Mediation FAQ
- **Word target:** 900–1,200
- **Flag:** review this page against the framing note in the website copy doc. With the Rule 31 credential claims removed, mediation pages should describe the service without implying a Rule 31 listing.

### 8. Mediation FAQ
- **URL:** `/mediation-faq/`
- **Title:** Mediation FAQ | How Mediation Works in Tennessee
- **Meta:** Answers to common questions about mediation in Tennessee: what mediation is, whether it's confidential, whether a court can order it, how long it takes, and what it costs.
- **H1:** Mediation in Tennessee: Frequently Asked Questions
- **Schema:** `FAQPage`
- **Word target:** 1,500–2,500
- **Status:** drafted, needs the framing fix and the ten additional questions
- **Note:** highest AI-retrieval potential on the site. Tennessee-specific mediation questions are heavily asked and thinly answered by authoritative sources. Add "Last reviewed: [date]" and author attribution.

### 9. Probate and Estate Planning — silo hub
- **URL:** `/probate-estate-planning/`
- **Title:** Memphis Probate and Estate Planning Attorney | Wills, Trusts, Estates
- **Meta:** Wills, trusts, estates, conservatorships, and guardianships in Memphis and Shelby County. Attorney Joni K. Roberts has served as Guardian ad Litem in Tennessee probate court.
- **H1:** Probate and Estate Planning in Memphis
- **Schema:** `Service` + `FAQPage`
- **Word target:** 900–1,200
- **Target questions:** How long does probate take in Tennessee? · Do I need a will in Tennessee? · What happens if someone dies without a will in Tennessee? · How much does probate cost in Tennessee?
- **Note:** most underbuilt opportunity on the site. Estate planning searchers convert well, competition in Memphis is thinner than family law, and the Guardian ad Litem appointments are a genuine, verifiable differentiator here.

### 10. Contact
- **URL:** `/contact/`
- **Title:** Contact Joni K. Roberts Law and Mediation Office | Memphis, TN
- **Meta:** Contact a Memphis family law and mediation attorney. Call (901) 800-2948 or email to schedule a consultation. Office located in Memphis, Tennessee.
- **H1:** Contact Our Memphis Office
- **Schema:** `ContactPage` + `LegalService` with full `PostalAddress` and `openingHoursSpecification`
- **Must include:** street address, phone, email, hours, embedded map, consultation form, the not-attorney-client disclaimer
- **Blocked on:** street address and office hours — still outstanding from the copy review

### 11. Legal pages
- `/privacy-policy/` — required if the contact form collects data
- `/disclaimer/` — expanded version of the footer language
- `/accessibility/` — ADA statement; law firm sites are a frequent target
- **Schema:** `WebPage`, set `noindex` on all three

---

# PHASE 2 — PRACTICE DEPTH (13 pages)

### Family law silo

**12. Child Support** — `/child-support/`
Title: Memphis Child Support Attorney | Tennessee Child Support Guidelines
Questions: How is child support calculated in Tennessee? · How much is child support in TN? · Can child support be modified? · What happens if a parent doesn't pay child support?
Note: the Tennessee Child Support Guidelines income-shares formula is concrete and citable. Explain how days of parenting time affect the calculation.

**13. Post-Divorce Modification** — `/post-divorce-modification/`
Title: Post-Divorce Modification Attorney Memphis | Changing Custody and Support Orders
Questions: How do I change a parenting plan in Tennessee? · What is a material change in circumstances? · Can alimony be modified in Tennessee?

**14. Adoption** — `/adoption/`
Title: Memphis Adoption Attorney | Step-Parent and Relative Adoption in Tennessee
Questions: How does step-parent adoption work in Tennessee? · How long does adoption take? · Do I need the other parent's consent?

**15. Paternity** — `/paternity/`
Title: Memphis Paternity Attorney | Establishing and Disputing Parentage in Tennessee
Questions: How do I establish paternity in Tennessee? · Can I dispute paternity after signing a birth certificate?

**16. Orders of Protection** — `/orders-of-protection/`
Title: Memphis Orders of Protection Attorney | Domestic Violence Petitions in Shelby County
Questions: How do I get an order of protection in Shelby County? · How long does an order of protection last in Tennessee?
**Handle with care:** include the National DV Hotline (1-800-799-7233), a note that browsing history may be visible, and a quick-exit button. Do not put testimonials on this page. Someone reading it may be in danger, and the page should serve safety before marketing.

### Mediation silo

**17. Divorce Mediation** — `/divorce-mediation/`
Title: Divorce Mediation in Memphis, TN | A Less Adversarial Path
Questions: How does divorce mediation work in Tennessee? · How much does divorce mediation cost? · Is mediation required before divorce in Tennessee? · Is mediation cheaper than divorce court?
Note: highest-intent mediation query. "Is mediation required" is especially valuable — Tennessee courts generally require mediation in contested divorces before trial, with exceptions including domestic violence cases.

**18. Parenting Plan Mediation** — `/parenting-plan-mediation/`
Title: Parenting Plan Mediation Memphis | Resolving Custody Disputes Out of Court

**19. Property Division Mediation** — `/property-division-mediation/`
Title: Property Division Mediation Memphis | Dividing Assets and Debts by Agreement

**20. Family Dispute Resolution** — `/family-dispute-resolution/`
Title: Family Dispute Resolution Memphis | Mediation for Family Conflicts
**Note:** still blocked on the illegible margin word from your markup.

### Probate silo

**21. Wills and Trusts** — `/wills-and-trusts/`
Questions: What happens if you die without a will in Tennessee? · Do I need a trust or just a will? · How much does a will cost in Tennessee?

**22. Probate Administration** — `/probate-administration/`
Questions: How long does probate take in Tennessee? · Do all estates go through probate in Tennessee? · What does an executor do?

**23. Conservatorships and Guardianships** — `/conservatorships-guardianships/`
Questions: What is the difference between a conservatorship and a guardianship in Tennessee? · How do I get a conservatorship for a parent?
Note: the Guardian ad Litem experience belongs here prominently.

### Civil silo

**24. General Civil Litigation** — `/civil-litigation/`
Title: Memphis Civil Litigation Attorney | Contract and Property Disputes
Note: mention the courts by name and the Sixth Circuit appeal. This page carries the appellate credential.

---

# PHASE 3 — AUTHORITY & REACH (10 pages)

**25. Testimonials** — `/client-testimonials/`
Only build once you have four or more consented testimonials. Include the disclaimer language from the testimonial package. `Review` schema is optional and risky — self-reported reviews in schema can trigger Google penalties; Google Business Profile reviews are the safer surface.

**26. Insights / Blog hub** — `/insights/`
Then individual posts at `/insights/post-slug/`. Not `/blog/2026/03/title/` — dates in URLs age content visibly.

**27–31. Five foundational articles.** Answer questions too narrow for a practice page but heavily searched:
- How Long Does a Divorce Take in Tennessee?
- What Is a Permanent Parenting Plan? A Tennessee Guide
- Divorce Mediation vs. Litigation: Cost, Time, and Control
- What Happens If You Die Without a Will in Tennessee?
- How Child Support Is Calculated in Tennessee
Schema: `Article` with `author` pointing at the attorney's `@id`. Publishing under a named, credentialed author is a meaningful AI-retrieval signal.

**32. Fees and Consultations** — `/fees/`
Even a general explanation of billing structure outperforms silence. Cost is the most common unanswered question in legal search, and pages that address it directly get retrieved.

**33. Resources** — `/resources/`
Links to Shelby County court forms, the Tennessee Child Support Calculator, Memphis Area Legal Services, court locations. Genuinely useful, and outbound links to authoritative `.gov` sources support your own credibility.

**34. Location pages — two only**
- `/memphis-family-law-attorney/`
- `/shelby-county-divorce-attorney/`

**Hard stop here.** Do not build `/bartlett-divorce-attorney/`, `/germantown-divorce-attorney/`, and so on. Near-identical pages with the city swapped are doorway pages under Google's guidelines and a real penalty risk. Two location pages with genuinely local content — the actual courthouses, local filing procedure, Shelby County specifics — beat fifteen templated ones.

---

## Internal linking rules

- **Every page links up to its silo hub** and the hub links down to every child. Non-negotiable.
- **Sibling links within a silo**, sparingly and contextually. Divorce → parenting time, parenting time → child support.
- **Cross-silo only where genuinely relevant.** Divorce → divorce mediation is real and valuable. Divorce → conservatorships is noise.
- **Every practice page links to the attorney profile and contact.** Concentrates authority on the entity page and the conversion page.
- **Descriptive anchor text.** "Tennessee parenting plans," never "click here" or "learn more."
- **Breadcrumbs everywhere** with `BreadcrumbList` schema.
- Aim for three or more internal links into every page. Nothing orphaned.

---

## sitemap.xml

Generate dynamically if the platform supports it. Phase 1 version:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>https://robertslawandmediation.com/</loc><changefreq>monthly</changefreq><priority>1.0</priority></url>
  <url><loc>https://robertslawandmediation.com/joni-k-roberts/</loc><changefreq>yearly</changefreq><priority>0.9</priority></url>
  <url><loc>https://robertslawandmediation.com/family-law/</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
  <url><loc>https://robertslawandmediation.com/divorce/</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
  <url><loc>https://robertslawandmediation.com/parenting-time/</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
  <url><loc>https://robertslawandmediation.com/mediation/</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
  <url><loc>https://robertslawandmediation.com/mediation-faq/</loc><changefreq>monthly</changefreq><priority>0.8</priority></url>
  <url><loc>https://robertslawandmediation.com/probate-estate-planning/</loc><changefreq>monthly</changefreq><priority>0.8</priority></url>
  <url><loc>https://robertslawandmediation.com/about/</loc><changefreq>yearly</changefreq><priority>0.7</priority></url>
  <url><loc>https://robertslawandmediation.com/contact/</loc><changefreq>yearly</changefreq><priority>0.8</priority></url>
</urlset>
```

Omit `lastmod` unless it's accurate — a date that never changes is worse than none. Legal pages stay out of the sitemap.

## robots.txt

```
User-agent: *
Allow: /
Disallow: /wp-admin/
Disallow: /thank-you/

Sitemap: https://robertslawandmediation.com/sitemap.xml
```

Do not block GPTBot, ClaudeBot, PerplexityBot, or Google-Extended. Blocking them removes the site from exactly the AI surfaces this project is optimizing for. Some firms block them reflexively over content-scraping concerns; for a firm that wants to be recommended by assistants, that's self-defeating.

## llms.txt

Emerging convention — a plain-text file at the root summarizing the site for AI systems. Cheap to add, no downside:

```
# Joni K. Roberts Law and Mediation Office

> Memphis, Tennessee law firm handling family law, probate and estate
> planning, and general civil matters, with mediation available as an
> alternative to litigation. Attorney Joni K. Roberts has practiced in
> Tennessee since 2004.

Location: Memphis, Tennessee. Serves Memphis and Shelby County.
Phone: (901) 800-2948
Email: jkroberts@robertslawandmediation.com

## Practice Areas
- [Family Law](https://robertslawandmediation.com/family-law/)
- [Divorce](https://robertslawandmediation.com/divorce/)
- [Parenting Time](https://robertslawandmediation.com/parenting-time/)
- [Mediation](https://robertslawandmediation.com/mediation/)
- [Probate and Estate Planning](https://robertslawandmediation.com/probate-estate-planning/)

## Key Resources
- [Attorney Profile](https://robertslawandmediation.com/joni-k-roberts/)
- [Mediation FAQ](https://robertslawandmediation.com/mediation-faq/)
```

---

## Technical requirements

- **HTTPS sitewide**, HTTP 301'd
- **One canonical hostname** — pick www or non-www, redirect the other
- **Self-referencing canonical** on every page
- **Mobile-first.** Most legal searches are mobile, and family law searches skew higher still.
- **Core Web Vitals:** LCP under 2.5s, INP under 200ms, CLS under 0.1
- **Click-to-call phone number** in the header on mobile
- **`Organization` and `Attorney` schema on every page** via a shared include, so the entity is asserted consistently sitewide
- **One `H1` per page**, headings in order, no skipped levels
- **Descriptive alt text** on every image
- **Google Search Console and Bing Webmaster** verified at launch; submit the sitemap to both

---

## The capacity question

This plan is 34 pages. Done well, that's roughly 35,000 words of legal content, all of which needs attorney review before publishing.

**Ten strong pages beat thirty-four thin ones.** Thin, near-duplicate practice pages are exactly what Google's helpful-content systems demote, and AI assistants won't retrieve from a page that doesn't actually answer anything. If Phase 2 and 3 would mean 400-word pages padded with boilerplate, don't build them — the architecture will still be there when there's capacity.

**My recommendation:** ship Phase 1 well. Then add Phase 2 pages one at a time in this order, based on what actually drives calls:

1. `/divorce-mediation/` — highest commercial intent on the site
2. `/child-support/` — highest search volume in the family silo
3. `/wills-and-trusts/` — thinnest competition, good conversion
4. `/civil-litigation/` — carries the Sixth Circuit credential
5. Everything else as capacity allows

## Still blocked

- **Street address and office hours** — needed for `/contact/`, the schema, and Google Business Profile
- **The illegible margin word** — blocks `/family-dispute-resolution/`
- **Testimonial confirmation** — blocks `/client-testimonials/`
- **The Mediation FAQ framing fix** — should land before `/mediation-faq/` publishes
- **Fee structure** — blocks `/fees/`, and cost questions are the biggest content gap in legal search

Everything on that list is a client decision, not a writing task. The first two are the ones holding up Phase 1.
