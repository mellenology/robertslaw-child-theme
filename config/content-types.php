<?php
/**
 * Content types — SINGLE SOURCE OF TRUTH.
 *
 * Practice areas, FAQs, and testimonials are entries, not page copy. Adding a
 * practice area is one entry that appears in every grid, silo nav, and schema
 * block that references it — not five separate page edits.
 *
 * NOTE ON SLUGS: these content types are deliberately NOT publicly queryable
 * at their own URLs. The architecture doc puts practice areas at flat URLs
 * (/divorce/, not /practice-area/divorce/) served by real pages. These entries
 * are the reusable data behind those pages, so 'public' is false and
 * 'show_ui' is true.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

return array(

	'post_types' => array(

		'rl_practice_area' => array(
			'labels_singular' => 'Practice Area',
			'labels_plural'   => 'Practice Areas',
			'menu_icon'       => 'dashicons-portfolio',
			'supports'        => array( 'title', 'editor', 'excerpt', 'page-attributes', 'thumbnail' ),
			'taxonomies'      => array( 'rl_silo' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => true,
			'menu_position'   => 21,
			'meta'            => array(
				'page_key' => array(
					'type'  => 'string',
					'label' => 'Page key',
					'help'  => 'Key from config/pages.php, e.g. "divorce". Links this entry to its real URL, title tag, and schema.',
				),
				'card_summary' => array(
					'type'  => 'string',
					'label' => 'Card summary',
					'help'  => 'One or two sentences for the practice area grid. Self-contained — no "our firm".',
				),
				'anchor_text' => array(
					'type'  => 'string',
					'label' => 'Preferred anchor text',
					'help'  => 'Descriptive anchor used when other pages link here, e.g. "Tennessee parenting plans". Never "learn more".',
				),
			),
		),

		'rl_faq' => array(
			'labels_singular' => 'FAQ',
			'labels_plural'   => 'FAQs',
			'menu_icon'       => 'dashicons-editor-help',
			'supports'        => array( 'title', 'editor', 'page-attributes' ),
			'taxonomies'      => array( 'rl_faq_group' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => true,
			'menu_position'   => 22,
			'meta'            => array(
				'short_answer' => array(
					'type'  => 'string',
					'label' => 'Direct answer (first sentence)',
					'help'  => 'One sentence that answers the question outright, before any elaboration. This is the passage AI assistants lift. Include "Tennessee" in the answer itself — extracted passages travel without their headings.',
				),
				'last_reviewed' => array(
					'type'  => 'string',
					'label' => 'Last reviewed',
					'help'  => 'YYYY-MM-DD. Only set this to a date the answer was actually reviewed.',
				),
			),
			// The post title IS the question, phrased the way a person types it.
			'title_placeholder' => 'How long does a divorce take in Tennessee?',
		),

		/*
		 * Testimonials.
		 *
		 * The consent fields are not optional metadata — they are the gate.
		 * RobertsLaw\Components refuses to render a testimonial whose consent
		 * fields are incomplete, so an unconsented entry cannot reach the front
		 * end even if someone publishes it.
		 *
		 * See content/testimonial-process.md. RPC 1.6 covers information
		 * relating to the representation, and in family law that can include
		 * the bare fact that someone was a client. Removing the name does not
		 * remove the confidentiality issue.
		 */
		'rl_testimonial' => array(
			'labels_singular' => 'Testimonial',
			'labels_plural'   => 'Testimonials',
			'menu_icon'       => 'dashicons-format-quote',
			'supports'        => array( 'editor' ),
			'taxonomies'      => array( 'rl_silo' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_position'   => 23,
			'meta'            => array(
				'consent_on_file' => array(
					'type'     => 'boolean',
					'label'    => 'Signed consent form on file',
					'help'     => 'Required. Written informed consent under RPC 1.6. Without this the testimonial will not render, published or not.',
					'required' => true,
				),
				'consent_date' => array(
					'type'     => 'string',
					'label'    => 'Consent date',
					'help'     => 'YYYY-MM-DD. RPC 7.2 requires retaining advertising materials for two years; keep the consent alongside them.',
					'required' => true,
				),
				'final_wording_approved' => array(
					'type'     => 'boolean',
					'label'    => 'Client approved the final published wording',
					'help'     => 'Required. Not the draft they sent — the exact version that goes live, in the place it goes live. This is the step that prevents nearly every problem.',
					'required' => true,
				),
				'attribution' => array(
					'type'  => 'string',
					'label' => 'Attribution as consented',
					'help'  => 'Exactly what the client chose on the consent form: full name, first name and last initial, initials, a general description such as "A family law client, Memphis", or nothing.',
				),
				'matter_concluded' => array(
					'type'     => 'boolean',
					'label'    => 'Matter fully concluded',
					'help'     => 'Required. Never solicit or publish mid-representation.',
					'required' => true,
				),
				'served_as_neutral' => array(
					'type'  => 'boolean',
					'label' => 'Firm served as the neutral mediator in this matter',
					'help'  => 'If checked, this testimonial will not render. A testimonial from one side of a mediation conducted as the neutral undercuts the impartiality the role requires.',
				),
			),
		),
	),

	'taxonomies' => array(

		'rl_silo' => array(
			'labels_singular' => 'Silo',
			'labels_plural'   => 'Silos',
			'object_types'    => array( 'rl_practice_area', 'rl_testimonial' ),
			'hierarchical'    => true,
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => true,
			/*
			 * Seeded on activation. Mediation is its own silo, deliberately NOT
			 * nested under family law — it has distinct searchers, some of whom
			 * are the other party or an opposing attorney looking for a neutral.
			 */
			'default_terms'   => array(
				'family-law' => 'Family Law',
				'mediation'  => 'Mediation',
				'probate'    => 'Probate and Estate Planning',
				'civil'      => 'Civil Litigation',
			),
		),

		'rl_faq_group' => array(
			'labels_singular' => 'FAQ Group',
			'labels_plural'   => 'FAQ Groups',
			'object_types'    => array( 'rl_faq' ),
			'hierarchical'    => true,
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => true,
			'default_terms'   => array(
				'mediation'      => 'Mediation',
				'divorce'        => 'Divorce',
				'parenting-time' => 'Parenting Time',
				'child-support'  => 'Child Support',
				'probate'        => 'Probate and Estate Planning',
				'fees'           => 'Fees and Consultations',
			),
		),
	),
);
