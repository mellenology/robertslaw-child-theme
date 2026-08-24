<?php
/**
 * Compliance rules — SINGLE SOURCE OF TRUTH.
 *
 * These encode the non-negotiable content rules from CLAUDE.md, which derive
 * from the Tennessee Rules of Professional Conduct. They are data here so the
 * theme can actually enforce them (admin warnings, schema filtering, required
 * disclaimer output) rather than relying on everyone remembering.
 *
 * This is a guardrail, not a legal review. Attorney review is still required
 * before any legal content goes live.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

return array(

	/*
	 * RPC 7.4 restricts specialist claims absent certification. Flagged in the
	 * editor when they appear in post content. Approved substitutes: "handles",
	 * "practices in", "focuses on".
	 *
	 * Word-boundary matched, case-insensitive.
	 */
	'restricted_terms' => array(
		'expert'      => 'RPC 7.4 — specialist claims. Use "handles", "practices in", or "focuses on".',
		'experts'     => 'RPC 7.4 — specialist claims. Use "handles", "practices in", or "focuses on".',
		'expertise'   => 'RPC 7.4 — specialist claims. Use "handles", "practices in", or "focuses on".',
		'specialist'  => 'RPC 7.4 — specialist claims absent certification.',
		'specialists' => 'RPC 7.4 — specialist claims absent certification.',
		'specialize'  => 'RPC 7.4 — specialist claims absent certification. Use "handles".',
		'specializes' => 'RPC 7.4 — specialist claims absent certification. Use "handles".',
		'specialized' => 'RPC 7.4 — specialist claims absent certification.',
		'specializing' => 'RPC 7.4 — specialist claims absent certification.',
	),

	/*
	 * RPC 7.1 — no communication that creates unjustified expectations about
	 * results. CLAUDE.md rule 5: never promise or imply results.
	 */
	'results_language' => array(
		'proven results',
		'we win',
		'guaranteed',
		'guarantee results',
		'best outcome guaranteed',
		'fighting for you',
		'fight for you',
		'no fee unless we win',
		'award-winning',
		'super lawyers',
		'best attorney',
		'best lawyer',
		'top-rated',
		'top rated',
	),

	/*
	 * Schema types that must never appear in JSON-LD on this site.
	 * CLAUDE.md rule 4: self-reported review schema risks Google penalties and,
	 * here, publishing unconsented client statements.
	 *
	 * RobertsLaw\Schema strips these before output, at every entry point.
	 */
	'forbidden_schema_types' => array(
		'Review',
		'AggregateRating',
		'Rating',
		'UserReview',
	),

	/*
	 * The legal disclaimer. Appears on EVERY page (CLAUDE.md rule 6).
	 * Exact wording from content/website-copy.md. Do not paraphrase.
	 */
	'site_disclaimer' => 'The information on this website is for general information purposes only. Nothing on this site should be taken as legal advice for any individual case or situation. This information is not intended to create, and receipt or viewing does not constitute, an attorney-client relationship.',

	/*
	 * Contact form disclaimer. Exact wording from content/website-copy.md.
	 */
	'form_disclaimer' => 'Submitting this form does not create an attorney-client relationship. Your information will be kept confidential. We will review your case and contact you to discuss further steps.',

	/*
	 * Testimonials disclaimer. Exact wording from
	 * content/testimonial-process.md. Must sit adjacent to any testimonials,
	 * not buried in the footer.
	 */
	'testimonial_disclaimer' => 'Testimonials on this page are from former clients of Joni K. Roberts Law and Mediation Office and are published with their written permission. Some clients are identified by first name, initials, or a general description at their request. Every case is different, and results in one matter do not predict or guarantee the outcome of any other. These statements do not create an attorney-client relationship and are not a promise of any particular result.',

	/*
	 * Pages that must never render testimonials, regardless of what an editor
	 * drops on them. Matched against the page key in config/pages.php.
	 */
	'no_testimonial_pages' => array(
		'orders-of-protection',
	),

	/*
	 * Placeholder pattern. Any config value matching this is treated as
	 * unresolved: it is omitted from JSON-LD, rendered as a visible editorial
	 * placeholder on the front end, and surfaced as an admin warning.
	 */
	'placeholder_pattern' => '/^\[[A-Z0-9_]+\]$/',

	/*
	 * AI crawlers that must stay allowed in robots.txt. CLAUDE.md: the site is
	 * optimized for AI retrieval; blocking these defeats the purpose.
	 */
	'allowed_ai_crawlers' => array(
		'GPTBot',
		'ClaudeBot',
		'Claude-Web',
		'PerplexityBot',
		'Google-Extended',
		'CCBot',
		'anthropic-ai',
	),
);
