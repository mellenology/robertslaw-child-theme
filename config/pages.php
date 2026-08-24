<?php
/**
 * Page inventory — SINGLE SOURCE OF TRUTH.
 *
 * Transcribed from content/site-architecture.md. This drives title tags, meta
 * descriptions, canonical URLs, breadcrumbs, page-specific JSON-LD, the silo
 * navigation, and sitemap.xml. One page's SEO metadata is never edited in a
 * builder — it is edited here.
 *
 * URL CONVENTIONS (architecture doc): lowercase, hyphens, no dates, no
 * /practice-areas/ prefix, trailing slash sitewide.
 *
 * NEVER change a 'url' after launch without adding a 301. The architecture doc
 * calls this non-negotiable, and CLAUDE.md lists it under "when to stop and ask".
 *
 * Keys per entry:
 *   url        Path with leading and trailing slash.
 *   title      <title>. Verbatim from the architecture doc.
 *   meta       Meta description. Verbatim from the architecture doc.
 *   h1         The single <h1>. One per page, no exceptions.
 *   schema     Page-specific JSON-LD types. Organization + Attorney are added
 *              sitewide by inc/class-schema.php and are not repeated here.
 *   silo       family-law | mediation | probate | civil | firm | legal
 *   parent     Key of the silo hub, for breadcrumbs and "links up" rule.
 *   phase      1 | 2 | 3
 *   status     ready | drafted | blocked | planned
 *   blocked_on Why it cannot ship. Rendered in the admin build screen.
 *   noindex    true to exclude from index and sitemap.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

return array(

	/* ---------------------------------------------------------------------
	 * PHASE 1 — LAUNCH
	 * ------------------------------------------------------------------ */

	'home' => array(
		'url'    => '/',
		'title'  => 'Memphis Family Law Attorney and Mediator | Joni K. Roberts Law and Mediation Office',
		'meta'   => 'Joni K. Roberts is a Memphis, TN attorney handling divorce, parenting time, adoption, probate, and estate planning, with mediation available across all practice areas. Practicing in Tennessee since 2004. Call (901) 800-2948.',
		'h1'     => 'Memphis Family Law Attorney and Mediator',
		'schema' => array( 'LegalService', 'WebSite', 'BreadcrumbList' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 1,
		'status' => 'drafted',
		'priority' => '1.0',
		'changefreq' => 'monthly',
	),

	'about' => array(
		'url'    => '/about/',
		'title'  => 'About Joni K. Roberts Law and Mediation Office | Memphis, TN',
		'meta'   => 'Founded by attorney Joni K. Roberts, a Tennessee lawyer practicing since 2004. Family law, estate planning, general civil matters, and mediation in Memphis, Tennessee.',
		'h1'     => 'About Joni K. Roberts Law and Mediation Office',
		'schema' => array( 'AboutPage' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 1,
		'status' => 'drafted',
		'priority' => '0.7',
		'changefreq' => 'yearly',
	),

	'joni-k-roberts' => array(
		'url'    => '/joni-k-roberts/',
		'title'  => 'Joni K. Roberts, Memphis Attorney and Mediator | Family Law, Probate, Civil Litigation',
		'meta'   => 'Joni K. Roberts has practiced law in Tennessee since 2004, handling family law, probate, and general civil litigation in Memphis, including a successful First Amendment appeal to the Sixth Circuit.',
		'h1'     => 'Joni K. Roberts — Memphis Attorney and Mediator',
		'schema' => array( 'ProfilePage' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 1,
		'status' => 'drafted',
		'priority' => '0.9',
		'changefreq' => 'yearly',
		// Architecture doc: most likely page to be cited by an AI assistant
		// asked to name a Memphis attorney. Link to it from every practice page.
		'link_from_all_practice_pages' => true,
	),

	'family-law' => array(
		'url'    => '/family-law/',
		'title'  => 'Memphis Family Law Attorney | Divorce, Parenting Time, Child Support',
		'meta'   => 'Family law representation in Memphis and Shelby County: divorce, parenting plans, child support, adoption, and paternity. Attorney Joni K. Roberts has practiced in Tennessee since 2004.',
		'h1'     => 'Family Law in Memphis and Shelby County',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => '',
		'phase'  => 1,
		'status' => 'planned',
		'priority' => '0.9',
		'changefreq' => 'monthly',
		'is_hub' => true,
	),

	'divorce' => array(
		'url'    => '/divorce/',
		'title'  => 'Memphis Divorce Attorney | Contested and Uncontested Divorce in Tennessee',
		'meta'   => 'Divorce representation in Memphis and Shelby County, including property division, alimony, and grounds for divorce. Tennessee requires a 60- or 90-day waiting period. Call (901) 800-2948.',
		'h1'     => 'Divorce Attorney in Memphis, Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 1,
		'status' => 'planned',
		'priority' => '0.9',
		'changefreq' => 'monthly',
	),

	'parenting-time' => array(
		'url'    => '/parenting-time/',
		'title'  => 'Memphis Child Custody and Parenting Time Attorney | Tennessee Parenting Plans',
		'meta'   => 'Tennessee courts issue permanent parenting plans setting residential schedules and decision-making authority. Representation in Memphis and Shelby County for parenting time and custody matters.',
		'h1'     => 'Parenting Time and Child Custody in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 1,
		'status' => 'planned',
		'priority' => '0.9',
		'changefreq' => 'monthly',
	),

	'mediation' => array(
		'url'    => '/mediation/',
		'title'  => 'Mediation Services in Memphis, TN | Divorce and Family Mediation',
		'meta'   => 'Mediation as an alternative to litigation in Memphis: divorce mediation, parenting plan mediation, property division, and family dispute resolution. Joni K. Roberts Law and Mediation Office.',
		'h1'     => 'Mediation Services in Memphis, Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'mediation',
		'parent' => '',
		'phase'  => 1,
		'status' => 'blocked',
		'priority' => '0.9',
		'changefreq' => 'monthly',
		'is_hub' => true,
		'blocked_on' => 'Rule 31 framing fix. With the Rule 31 credential claims removed sitewide, this page must describe the service without implying a Rule 31 listing. See the FLAG in content/website-copy.md.',
	),

	'mediation-faq' => array(
		'url'    => '/mediation-faq/',
		'title'  => 'Mediation FAQ | How Mediation Works in Tennessee',
		'meta'   => "Answers to common questions about mediation in Tennessee: what mediation is, whether it's confidential, whether a court can order it, how long it takes, and what it costs.",
		'h1'     => 'Mediation in Tennessee: Frequently Asked Questions',
		'schema' => array( 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'mediation',
		'parent' => 'mediation',
		'phase'  => 1,
		'status' => 'blocked',
		'priority' => '0.8',
		'changefreq' => 'monthly',
		'blocked_on' => 'Rule 31 framing fix, plus ten additional questions. Also: verify the Rule 31 training figures against the current ADR Commission standards before publishing ("46 hours" in the source is likely a typo for 40).',
		// Architecture doc: highest AI-retrieval potential on the site.
		'needs_last_reviewed' => true,
	),

	'probate-estate-planning' => array(
		'url'    => '/probate-estate-planning/',
		'title'  => 'Memphis Probate and Estate Planning Attorney | Wills, Trusts, Estates',
		'meta'   => 'Wills, trusts, estates, conservatorships, and guardianships in Memphis and Shelby County. Attorney Joni K. Roberts has served as Guardian ad Litem in Tennessee probate court.',
		'h1'     => 'Probate and Estate Planning in Memphis',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'probate',
		'parent' => '',
		'phase'  => 1,
		'status' => 'planned',
		'priority' => '0.8',
		'changefreq' => 'monthly',
		'is_hub' => true,
	),

	'contact' => array(
		'url'    => '/contact/',
		'title'  => 'Contact Joni K. Roberts Law and Mediation Office | Memphis, TN',
		'meta'   => 'Contact a Memphis family law and mediation attorney. Call (901) 800-2948 or email to schedule a consultation. Office located in Memphis, Tennessee.',
		'h1'     => 'Contact Our Memphis Office',
		'schema' => array( 'ContactPage', 'BreadcrumbList' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 1,
		'status' => 'blocked',
		'priority' => '0.8',
		'changefreq' => 'yearly',
		'blocked_on' => 'Street address and office hours outstanding from the client. Both are required for the page copy, the PostalAddress schema, openingHoursSpecification, and Google Business Profile.',
	),

	'privacy-policy' => array(
		'url'    => '/privacy-policy/',
		'title'  => 'Privacy Policy | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Privacy Policy',
		'schema' => array( 'WebPage' ),
		'silo'   => 'legal',
		'parent' => '',
		'phase'  => 1,
		'status' => 'planned',
		'noindex' => true,
	),

	'disclaimer' => array(
		'url'    => '/disclaimer/',
		'title'  => 'Disclaimer | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Disclaimer',
		'schema' => array( 'WebPage' ),
		'silo'   => 'legal',
		'parent' => '',
		'phase'  => 1,
		'status' => 'planned',
		'noindex' => true,
	),

	'accessibility' => array(
		'url'    => '/accessibility/',
		'title'  => 'Accessibility Statement | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Accessibility Statement',
		'schema' => array( 'WebPage' ),
		'silo'   => 'legal',
		'parent' => '',
		'phase'  => 1,
		'status' => 'planned',
		'noindex' => true,
	),

	/* ---------------------------------------------------------------------
	 * PHASE 2 — PRACTICE DEPTH
	 *
	 * Architecture doc recommends adding these one at a time, in this order:
	 * divorce-mediation, child-support, wills-and-trusts, civil-litigation.
	 * Ten strong pages beat thirty-four thin ones — do not bulk-generate.
	 * ------------------------------------------------------------------ */

	'child-support' => array(
		'url'    => '/child-support/',
		'title'  => 'Memphis Child Support Attorney | Tennessee Child Support Guidelines',
		'meta'   => '',
		'h1'     => 'Child Support in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 2,
		'status' => 'planned',
		'build_order' => 2,
	),

	'post-divorce-modification' => array(
		'url'    => '/post-divorce-modification/',
		'title'  => 'Post-Divorce Modification Attorney Memphis | Changing Custody and Support Orders',
		'meta'   => '',
		'h1'     => 'Post-Divorce Modification in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 2,
		'status' => 'planned',
	),

	'adoption' => array(
		'url'    => '/adoption/',
		'title'  => 'Memphis Adoption Attorney | Step-Parent and Relative Adoption in Tennessee',
		'meta'   => '',
		'h1'     => 'Adoption in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 2,
		'status' => 'planned',
	),

	'paternity' => array(
		'url'    => '/paternity/',
		'title'  => 'Memphis Paternity Attorney | Establishing and Disputing Parentage in Tennessee',
		'meta'   => '',
		'h1'     => 'Paternity in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 2,
		'status' => 'planned',
	),

	'orders-of-protection' => array(
		'url'    => '/orders-of-protection/',
		'title'  => 'Memphis Orders of Protection Attorney | Domestic Violence Petitions in Shelby County',
		'meta'   => '',
		'h1'     => 'Orders of Protection in Shelby County',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 2,
		'status' => 'planned',
		/*
		 * SENSITIVE PAGE. CLAUDE.md: someone reading this may be in immediate
		 * danger. The theme enforces all four of these automatically — see
		 * inc/class-compliance.php and the safety-exit component.
		 */
		'sensitive'      => true,
		'require_safety_exit' => true,
		'no_testimonials'     => true,
	),

	'divorce-mediation' => array(
		'url'    => '/divorce-mediation/',
		'title'  => 'Divorce Mediation in Memphis, TN | A Less Adversarial Path',
		'meta'   => '',
		'h1'     => 'Divorce Mediation in Memphis',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'mediation',
		'parent' => 'mediation',
		'phase'  => 2,
		'status' => 'planned',
		'build_order' => 1,
	),

	'parenting-plan-mediation' => array(
		'url'    => '/parenting-plan-mediation/',
		'title'  => 'Parenting Plan Mediation Memphis | Resolving Custody Disputes Out of Court',
		'meta'   => '',
		'h1'     => 'Parenting Plan Mediation',
		'schema' => array( 'Service', 'BreadcrumbList' ),
		'silo'   => 'mediation',
		'parent' => 'mediation',
		'phase'  => 2,
		'status' => 'planned',
	),

	'property-division-mediation' => array(
		'url'    => '/property-division-mediation/',
		'title'  => 'Property Division Mediation Memphis | Dividing Assets and Debts by Agreement',
		'meta'   => '',
		'h1'     => 'Property Division Mediation',
		'schema' => array( 'Service', 'BreadcrumbList' ),
		'silo'   => 'mediation',
		'parent' => 'mediation',
		'phase'  => 2,
		'status' => 'planned',
	),

	'family-dispute-resolution' => array(
		'url'    => '/family-dispute-resolution/',
		'title'  => 'Family Dispute Resolution Memphis | Mediation for Family Conflicts',
		'meta'   => '',
		'h1'     => 'Family Dispute Resolution',
		'schema' => array( 'Service', 'BreadcrumbList' ),
		'silo'   => 'mediation',
		'parent' => 'mediation',
		'phase'  => 2,
		'status' => 'blocked',
		'blocked_on' => 'The illegible margin word in the source markup. Candidates: "an open forum" / "an environment" / "an alternative". Client decision — do not guess.',
	),

	'wills-and-trusts' => array(
		'url'    => '/wills-and-trusts/',
		'title'  => 'Memphis Wills and Trusts Attorney | Estate Planning in Tennessee',
		'meta'   => '',
		'h1'     => 'Wills and Trusts in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'probate',
		'parent' => 'probate-estate-planning',
		'phase'  => 2,
		'status' => 'planned',
		'build_order' => 3,
	),

	'probate-administration' => array(
		'url'    => '/probate-administration/',
		'title'  => 'Memphis Probate Administration Attorney | Settling an Estate in Tennessee',
		'meta'   => '',
		'h1'     => 'Probate Administration in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'probate',
		'parent' => 'probate-estate-planning',
		'phase'  => 2,
		'status' => 'planned',
	),

	'conservatorships-guardianships' => array(
		'url'    => '/conservatorships-guardianships/',
		'title'  => 'Memphis Conservatorship and Guardianship Attorney | Tennessee Probate Court',
		'meta'   => '',
		'h1'     => 'Conservatorships and Guardianships in Tennessee',
		'schema' => array( 'Service', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'probate',
		'parent' => 'probate-estate-planning',
		'phase'  => 2,
		'status' => 'planned',
	),

	'civil-litigation' => array(
		'url'    => '/civil-litigation/',
		'title'  => 'Memphis Civil Litigation Attorney | Contract and Property Disputes',
		'meta'   => '',
		'h1'     => 'General Civil Litigation in Memphis',
		'schema' => array( 'Service', 'BreadcrumbList' ),
		'silo'   => 'civil',
		'parent' => '',
		'phase'  => 2,
		'status' => 'planned',
		'is_hub' => true,
		'build_order' => 4,
	),

	/* ---------------------------------------------------------------------
	 * PHASE 3 — AUTHORITY & REACH
	 * ------------------------------------------------------------------ */

	'client-testimonials' => array(
		'url'    => '/client-testimonials/',
		'title'  => 'Client Testimonials | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Client Testimonials',
		'schema' => array( 'WebPage', 'BreadcrumbList' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 3,
		'status' => 'blocked',
		'blocked_on' => 'Requires four or more testimonials with signed, documented client consent. See content/testimonial-process.md. No Review or AggregateRating schema on this page.',
	),

	'insights' => array(
		'url'    => '/insights/',
		'title'  => 'Insights | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Insights',
		'schema' => array( 'CollectionPage', 'BreadcrumbList' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 3,
		'status' => 'planned',
	),

	'fees' => array(
		'url'    => '/fees/',
		'title'  => 'Fees and Consultations | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Fees and Consultations',
		'schema' => array( 'WebPage', 'FAQPage', 'BreadcrumbList' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 3,
		'status' => 'blocked',
		'blocked_on' => 'Fee structure not provided, and whether consultations are free or paid. Client decision. Fee statements are on the CLAUDE.md stop-and-ask list.',
	),

	'resources' => array(
		'url'    => '/resources/',
		'title'  => 'Legal Resources | Shelby County Courts and Tennessee Forms',
		'meta'   => '',
		'h1'     => 'Legal Resources',
		'schema' => array( 'WebPage', 'BreadcrumbList' ),
		'silo'   => 'firm',
		'parent' => '',
		'phase'  => 3,
		'status' => 'planned',
	),

	'memphis-family-law-attorney' => array(
		'url'    => '/memphis-family-law-attorney/',
		'title'  => 'Memphis Family Law Attorney | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Family Law Attorney in Memphis, Tennessee',
		'schema' => array( 'Service', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 3,
		'status' => 'planned',
		'location_page' => true,
	),

	'shelby-county-divorce-attorney' => array(
		'url'    => '/shelby-county-divorce-attorney/',
		'title'  => 'Shelby County Divorce Attorney | Joni K. Roberts Law and Mediation Office',
		'meta'   => '',
		'h1'     => 'Divorce Attorney in Shelby County, Tennessee',
		'schema' => array( 'Service', 'BreadcrumbList' ),
		'silo'   => 'family-law',
		'parent' => 'family-law',
		'phase'  => 3,
		'status' => 'planned',
		'location_page' => true,
	),

	/*
	 * HARD STOP on location pages. The architecture doc is explicit: do not
	 * build /bartlett-divorce-attorney/, /germantown-divorce-attorney/, or any
	 * other city-swap variant. Near-identical pages with the city changed are
	 * doorway pages under Google's guidelines and a real penalty risk.
	 */
);
