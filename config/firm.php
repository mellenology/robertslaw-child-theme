<?php
/**
 * Firm facts — SINGLE SOURCE OF TRUTH.
 *
 * Every phone number, email, name, and address on this site resolves from this
 * file. Nothing is hardcoded in a template, a page, or a schema block.
 *
 * Entity consistency is a deliberate SEO/AI-retrieval strategy for this site
 * (see content/site-architecture.md). One string, used character for character
 * everywhere, is the whole point. Change it here and it changes sitewide.
 *
 * PLACEHOLDERS
 * Values wrapped in square brackets are genuinely unknown and are listed in
 * CLAUDE.md as "do not fill these in". Leave them literal. The theme detects
 * them, refuses to emit them into JSON-LD, and shows an admin warning until
 * the client supplies a real value. Inventing a plausible Memphis address or
 * "9-5 Monday through Friday" is worse than an obvious blank.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

return array(

	/*
	 * Identity. These strings are asserted in prose, in the footer, and in
	 * JSON-LD. They must match each other exactly.
	 */
	'name'            => 'Joni K. Roberts Law and Mediation Office',
	'attorney'        => 'Joni K. Roberts',
	'attorney_title'  => 'Attorney and Mediator',
	'domain'          => 'robertslawandmediation.com',
	'home_url'        => 'https://robertslawandmediation.com/',

	/*
	 * Contact. 'phone_display' is what humans read; 'phone_schema' is E.164 for
	 * JSON-LD and 'phone_href' drives click-to-call. All three describe one
	 * number — never edit one without the others.
	 */
	'phone_display'   => '(901) 800-2948',
	'phone_schema'    => '+1-901-800-2948',
	'phone_href'      => 'tel:+19018002948',
	'email'           => 'jkroberts@robertslawandmediation.com',

	/*
	 * Address. street/postal are blocked on the client. See CLAUDE.md.
	 */
	'street_address'  => '[STREET_ADDRESS]',
	'locality'        => 'Memphis',
	'region'          => 'TN',
	'region_full'     => 'Tennessee',
	'postal_code'     => '[POSTAL_CODE]',
	'country'         => 'US',
	'office_hours'    => '[OFFICE_HOURS]',

	/*
	 * Service area. Used in prose and in schema areaServed.
	 */
	'service_area'    => 'Memphis and Shelby County, Tennessee',
	'area_served'     => array(
		array( 'type' => 'City', 'name' => 'Memphis' ),
		array( 'type' => 'AdministrativeArea', 'name' => 'Shelby County, Tennessee' ),
	),

	/*
	 * Outbound links. Google review link is not yet created — see CLAUDE.md.
	 */
	'google_review_url' => '[GOOGLE_REVIEW_LINK]',

	/*
	 * Attorney credentials.
	 *
	 * COMPLIANCE: this list is closed. CLAUDE.md rule 2 forbids adding bar
	 * certifications, awards, "Super Lawyers" style honors, Tennessee Supreme
	 * Court Rule 31 mediator listings, or years-of-experience claims that are
	 * not in content/website-copy.md. The Rule 31 listings were deliberately
	 * removed from this site. Do not reinstate them here.
	 */
	'bar_admission'   => array(
		'jurisdiction' => 'Tennessee',
		'year'         => '2004',
	),
	'education'       => array(
		array(
			'institution' => 'Cecil C. Humphreys School of Law, University of Memphis',
			'degree'      => 'J.D.',
			'year'        => '2003',
			'honors'      => 'Law Review Research Editor; CALI Award — International Law',
		),
		array(
			'institution' => 'University of Memphis',
			'degree'      => 'M.A.T.',
			'year'        => '1995',
			'honors'      => '',
		),
		array(
			'institution' => 'University of California, Davis',
			'degree'      => 'B.A.',
			'year'        => '1989',
			'honors'      => '',
		),
	),
	'memberships'     => array(
		'Tennessee Bar Association',
		'Tennessee Association of Professional Mediators',
		'Phi Delta Phi',
	),
	'courts'          => array(
		'General Sessions Court',
		'Circuit Court',
		'Chancery Court',
		'Probate Court',
		'Federal District Court',
		'United States Court of Appeals for the Sixth Circuit',
	),
	'knows_about'     => array(
		'Family law', 'Divorce', 'Parenting time', 'Child support', 'Adoption',
		'Paternity', 'Orders of protection', 'Probate', 'Estate planning',
		'Conservatorships', 'Guardianships', 'Mediation', 'Civil litigation',
	),

	/*
	 * Safety resources for /orders-of-protection/. See CLAUDE.md "Sensitive
	 * pages" — someone reading that page may be in immediate danger.
	 */
	'dv_hotline_display' => '1-800-799-7233',
	'dv_hotline_href'    => 'tel:+18007997233',
	'dv_hotline_name'    => 'National Domestic Violence Hotline',

	/*
	 * Tennessee Board of Professional Responsibility ethics counsel. Referenced
	 * in content/testimonial-process.md; surfaced in the admin testimonial
	 * screen so the reminder sits where the decision gets made.
	 */
	'ethics_counsel_phone' => '1-800-486-5714',
);
