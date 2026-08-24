<?php
/**
 * Component registry — SINGLE SOURCE OF TRUTH.
 *
 * One definition per component. From each entry the theme generates, at once:
 *
 *   1. A Cornerstone / Pro element     (drag-and-drop in the builder)
 *   2. A WordPress shortcode           [rl_hero heading="..."]
 *   3. A PHP render call               RobertsLaw\Components::render( 'hero', $atts )
 *
 * All three go through the same render path and the same template file, so a
 * change to a component's markup or fields lands everywhere it is used. There
 * is no second copy of a component living inside a Cornerstone element.
 *
 * FIELD TYPES map to Cornerstone control types:
 *   text · textarea · rich-text · color · icon-choose · toggle · number
 *   select (needs 'options') · image
 *
 * Keys per field: type, label, default, tooltip, options, help.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

return array(

	/*
	 * The single most important block on the site.
	 *
	 * content/website-copy.md: "That paragraph is the single most important
	 * block on the site. It is self-contained, factual, and dense with
	 * checkable entities — it's what an AI assistant will lift."
	 *
	 * Sits immediately under the H1, above the fold. The component enforces a
	 * word-count advisory (25-50 words) in the builder rather than leaving it
	 * to memory.
	 */
	'answer-block' => array(
		'label'       => 'Answer Block',
		'description' => 'The 25-50 word direct answer that sits under the H1. AI assistants retrieve this passage first.',
		'category'    => 'Roberts Law — Content',
		'icon'        => 'quote-left',
		'fields'      => array(
			'answer' => array(
				'type'    => 'textarea',
				'label'   => 'Answer',
				'default' => '',
				'tooltip' => 'Lead with the answer. Self-contained — it must make sense quoted with no surrounding page. Name the firm in full rather than saying "our firm". Target 25-50 words.',
			),
			'tone' => array(
				'type'    => 'select',
				'label'   => 'Emphasis',
				'default' => 'lead',
				'options' => array(
					'lead'  => 'Lead (above the fold)',
					'inset' => 'Inset (mid-page)',
				),
			),
		),
	),

	'hero' => array(
		'label'       => 'Page Hero',
		'description' => 'H1 plus optional answer block and primary actions. One per page.',
		'category'    => 'Roberts Law — Layout',
		'icon'        => 'header',
		'fields'      => array(
			'heading' => array(
				'type'    => 'text',
				'label'   => 'H1',
				'default' => '',
				'tooltip' => 'The page\'s single H1. Leave empty to inherit the H1 declared for this page in config/pages.php, which is the preferred approach.',
			),
			'eyebrow' => array(
				'type'    => 'text',
				'label'   => 'Eyebrow',
				'default' => '',
				'tooltip' => 'Small label above the H1. Not a heading element — it will not break heading order.',
			),
			'answer' => array(
				'type'    => 'textarea',
				'label'   => 'Answer Block',
				'default' => '',
				'tooltip' => '25-50 word direct answer, rendered immediately under the H1.',
			),
			'show_actions' => array(
				'type'    => 'toggle',
				'label'   => 'Show Call / Email actions',
				'default' => true,
			),
			'variant' => array(
				'type'    => 'select',
				'label'   => 'Variant',
				'default' => 'default',
				'options' => array(
					'default' => 'Default',
					'deep'    => 'Deep (navy background)',
					'plain'   => 'Plain (no background)',
				),
			),
		),
	),

	/*
	 * Practice area cards. Pulls from the practice_area CPT rather than taking
	 * hardcoded items, so the silo hub pages and the home page always show the
	 * same set and adding a practice area is one entry, not five page edits.
	 */
	'service-grid' => array(
		'label'       => 'Practice Area Grid',
		'description' => 'Cards for practice areas, pulled from the Practice Areas content type. Filter by silo.',
		'category'    => 'Roberts Law — Content',
		'icon'        => 'th-large',
		'fields'      => array(
			'heading' => array(
				'type'    => 'text',
				'label'   => 'Section heading',
				'default' => '',
			),
			'silo' => array(
				'type'    => 'select',
				'label'   => 'Silo',
				'default' => '',
				'options' => array(
					''            => 'All silos',
					'family-law'  => 'Family Law',
					'mediation'   => 'Mediation',
					'probate'     => 'Probate and Estate Planning',
					'civil'       => 'Civil Litigation',
				),
				'tooltip' => 'Silo hubs should show only their own children. The architecture doc links densely within silos and sparsely across them.',
			),
			'limit' => array(
				'type'    => 'number',
				'label'   => 'Maximum cards',
				'default' => 12,
			),
			'columns' => array(
				'type'    => 'select',
				'label'   => 'Columns',
				'default' => '3',
				'options' => array( '2' => 'Two', '3' => 'Three', '4' => 'Four' ),
			),
		),
	),

	/*
	 * FAQ. Emits FAQPage JSON-LD whose text is identical to the visible answer,
	 * which content/website-copy.md calls for explicitly.
	 */
	'faq' => array(
		'label'       => 'FAQ',
		'description' => 'Question-and-answer block with FAQPage schema. Questions come from the FAQ content type.',
		'category'    => 'Roberts Law — Content',
		'icon'        => 'question-circle',
		'fields'      => array(
			'heading' => array(
				'type'    => 'text',
				'label'   => 'Section heading',
				'default' => 'Frequently Asked Questions',
			),
			'group' => array(
				'type'    => 'text',
				'label'   => 'FAQ group',
				'default' => '',
				'tooltip' => 'Slug of the faq_group term to pull, e.g. "divorce" or "mediation". Leave empty to use the FAQs assigned to this page.',
			),
			'heading_level' => array(
				'type'    => 'select',
				'label'   => 'Question heading level',
				'default' => 'h2',
				'options' => array( 'h2' => 'H2', 'h3' => 'H3' ),
				'tooltip' => 'Pick the level that keeps heading order unbroken on this page. Questions are headings phrased as questions — that is what gets retrieved.',
			),
			'emit_schema' => array(
				'type'    => 'toggle',
				'label'   => 'Emit FAQPage schema',
				'default' => true,
				'tooltip' => 'Only one FAQ block per page should emit schema. Turn this off on a second block.',
			),
		),
	),

	'cta-band' => array(
		'label'       => 'CTA Band',
		'description' => 'Call / Email / Schedule actions. Phone and email resolve from config/firm.php.',
		'category'    => 'Roberts Law — Layout',
		'icon'        => 'bullhorn',
		'fields'      => array(
			'heading' => array(
				'type'    => 'text',
				'label'   => 'Heading',
				'default' => 'Ready to take the next step?',
			),
			'body' => array(
				'type'    => 'textarea',
				'label'   => 'Body',
				'default' => '',
				'tooltip' => 'No results language. No "fighting for you". See CLAUDE.md rule 5.',
			),
			'show_schedule' => array(
				'type'    => 'toggle',
				'label'   => 'Show Schedule a Consultation',
				'default' => true,
			),
			'variant' => array(
				'type'    => 'select',
				'label'   => 'Variant',
				'default' => 'deep',
				'options' => array(
					'deep'  => 'Deep (navy)',
					'soft'  => 'Soft (warm)',
					'plain' => 'Plain',
				),
			),
		),
	),

	'contact-card' => array(
		'label'       => 'Contact Card',
		'description' => 'Firm NAP block. Every value resolves from config/firm.php — never typed into a page.',
		'category'    => 'Roberts Law — Firm',
		'icon'        => 'map-marker',
		'fields'      => array(
			'heading' => array(
				'type'    => 'text',
				'label'   => 'Heading',
				'default' => '',
			),
			'show_address' => array(
				'type'    => 'toggle',
				'label'   => 'Show address',
				'default' => true,
			),
			'show_hours' => array(
				'type'    => 'toggle',
				'label'   => 'Show hours',
				'default' => true,
			),
			'layout' => array(
				'type'    => 'select',
				'label'   => 'Layout',
				'default' => 'stacked',
				'options' => array( 'stacked' => 'Stacked', 'inline' => 'Inline' ),
			),
		),
	),

	'attorney-card' => array(
		'label'       => 'Attorney Card',
		'description' => 'Joni K. Roberts summary with a link to the profile page. Credentials resolve from config/firm.php.',
		'category'    => 'Roberts Law — Firm',
		'icon'        => 'user',
		'fields'      => array(
			'summary' => array(
				'type'    => 'textarea',
				'label'   => 'Summary',
				'default' => '',
				'tooltip' => 'Do not add credentials here that are not in config/firm.php. See CLAUDE.md rule 2.',
			),
			'photo' => array(
				'type'    => 'image',
				'label'   => 'Photograph',
				'default' => '',
				'tooltip' => 'Descriptive alt text is required. Set explicit dimensions to protect CLS.',
			),
			'show_credentials' => array(
				'type'    => 'toggle',
				'label'   => 'Show admission and education',
				'default' => true,
			),
			'show_courts' => array(
				'type'    => 'toggle',
				'label'   => 'Show courts',
				'default' => false,
			),
		),
	),

	/*
	 * Testimonials.
	 *
	 * CLAUDE.md rule 3: never invent testimonials, reviews, star ratings, or
	 * client quotes — not even as placeholder content. This component ships
	 * with an empty state and renders nothing but that empty state until
	 * consented testimonials exist. It emits no Review or AggregateRating
	 * schema, ever, and it refuses to render on pages marked sensitive.
	 */
	'testimonials' => array(
		'label'       => 'Testimonials',
		'description' => 'Consented client testimonials. Renders an empty state until testimonials with documented consent exist. Never emits Review schema.',
		'category'    => 'Roberts Law — Firm',
		'icon'        => 'comment',
		'fields'      => array(
			'heading' => array(
				'type'    => 'text',
				'label'   => 'Heading',
				'default' => 'In Their Words',
			),
			'limit' => array(
				'type'    => 'number',
				'label'   => 'Maximum shown',
				'default' => 4,
				'tooltip' => 'content/testimonial-process.md: four to six is plenty. More than that and visitors stop reading.',
			),
			'silo' => array(
				'type'    => 'text',
				'label'   => 'Filter by silo',
				'default' => '',
				'tooltip' => 'Spread testimonials across practice areas rather than stacking them all on divorce.',
			),
		),
	),

	/*
	 * The legal disclaimer. CLAUDE.md rule 6: appears on every page, exact
	 * wording, no paraphrase. The theme also outputs it automatically in the
	 * footer, so this component is only for placing it somewhere additional.
	 */
	'disclaimer' => array(
		'label'       => 'Legal Disclaimer',
		'description' => 'The sitewide disclaimer, exact wording from config/compliance.php. Not editable in the builder by design.',
		'category'    => 'Roberts Law — Compliance',
		'icon'        => 'balance-scale',
		'fields'      => array(
			'variant' => array(
				'type'    => 'select',
				'label'   => 'Variant',
				'default' => 'default',
				'options' => array(
					'default' => 'Default',
					'form'    => 'Contact form disclaimer',
					'compact' => 'Compact',
				),
				'tooltip' => 'The text itself comes from config/compliance.php and cannot be edited here. Paraphrasing it is a compliance problem.',
			),
		),
	),

	'breadcrumbs' => array(
		'label'       => 'Breadcrumbs',
		'description' => 'Silo breadcrumb trail with BreadcrumbList schema. Path resolves from config/pages.php.',
		'category'    => 'Roberts Law — Layout',
		'icon'        => 'angle-right',
		'fields'      => array(
			'emit_schema' => array(
				'type'    => 'toggle',
				'label'   => 'Emit BreadcrumbList schema',
				'default' => true,
			),
		),
	),

	/*
	 * Safety exit for /orders-of-protection/.
	 *
	 * CLAUDE.md "Sensitive pages": someone reading may be in immediate danger.
	 * Provides the National DV Hotline, a browsing-history warning, and a
	 * quick-exit mechanism. The theme injects this automatically on any page
	 * flagged 'sensitive' in config/pages.php — placing it manually is optional.
	 */
	'safety-exit' => array(
		'label'       => 'Safety Exit',
		'description' => 'DV hotline, browsing-history warning, and quick-exit control. Auto-injected on sensitive pages.',
		'category'    => 'Roberts Law — Compliance',
		'icon'        => 'shield',
		'fields'      => array(
			'position' => array(
				'type'    => 'select',
				'label'   => 'Position',
				'default' => 'inline',
				'options' => array(
					'inline' => 'Inline notice',
					'fixed'  => 'Fixed to viewport corner',
				),
			),
		),
	),

	/*
	 * content/website-copy.md asks for a visible "Last reviewed" line and named
	 * author attribution on the FAQ. Recency and a named, verifiable author are
	 * both AI-retrieval signals.
	 */
	'last-reviewed' => array(
		'label'       => 'Last Reviewed',
		'description' => 'Review date and attorney attribution line. Recency and named authorship are AI-retrieval signals.',
		'category'    => 'Roberts Law — Content',
		'icon'        => 'calendar-check-o',
		'fields'      => array(
			'date' => array(
				'type'    => 'text',
				'label'   => 'Review date',
				'default' => '',
				'tooltip' => 'Leave empty to use the post\'s last modified date. Do not enter a date the content was not actually reviewed on.',
			),
			'show_author' => array(
				'type'    => 'toggle',
				'label'   => 'Show attorney attribution',
				'default' => true,
			),
		),
	),
);
