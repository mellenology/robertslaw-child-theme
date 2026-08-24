<?php
/**
 * Compliance guardrails.
 *
 * Turns the rules in config/compliance.php into behaviour: placeholder
 * handling, restricted-term scanning, forbidden schema stripping, and the
 * canonical disclaimer text.
 *
 * This is a guardrail, not a legal review. Attorney review is still required
 * before any legal content goes live (CLAUDE.md).
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Compliance {

	/**
	 * Is this value an unresolved placeholder such as [STREET_ADDRESS]?
	 *
	 * @param mixed $value Value to test.
	 * @return bool
	 */
	public static function is_placeholder( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return false;
		}

		$pattern = Config::get( 'compliance.placeholder_pattern', '/^\[[A-Z0-9_]+\]$/' );

		return (bool) preg_match( $pattern, trim( $value ) );
	}

	/**
	 * Return the value, or an empty string if it is an unresolved placeholder.
	 *
	 * Use this everywhere a value flows into JSON-LD or into machine-readable
	 * output. Emitting "[STREET_ADDRESS]" as a streetAddress is worse than
	 * omitting the property: wrong NAP data breaks local search.
	 *
	 * @param mixed $value Value to resolve.
	 * @return string
	 */
	public static function resolve( $value ) {
		return self::is_placeholder( $value ) ? '' : (string) $value;
	}

	/**
	 * Render a value for human-facing output, marking unresolved placeholders
	 * visibly rather than printing raw brackets into the page.
	 *
	 * Logged-in editors see what is missing and why. Visitors see nothing at
	 * all, because a half-finished address is worse than no address.
	 *
	 * @param mixed  $value Value to render.
	 * @param string $label Human label for the missing field.
	 * @return string HTML, already escaped.
	 */
	public static function render_value( $value, $label = '' ) {
		if ( ! self::is_placeholder( $value ) ) {
			return esc_html( (string) $value );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		return sprintf(
			'<mark class="rl-placeholder" title="%s">%s</mark>',
			esc_attr__( 'Awaiting a value from the client. Do not guess — see CLAUDE.md.', 'robertslaw' ),
			esc_html( $label ? $label : (string) $value )
		);
	}

	/**
	 * Every firm config value that is still an unresolved placeholder.
	 *
	 * @return array<string, string> Field key => placeholder token.
	 */
	public static function unresolved_placeholders() {
		$found = array();

		foreach ( Config::load( 'firm' ) as $key => $value ) {
			if ( self::is_placeholder( $value ) ) {
				$found[ $key ] = $value;
			}
		}

		return $found;
	}

	/**
	 * Scan text for language the Tennessee RPC restricts.
	 *
	 * Returns findings rather than mutating the text. Rewriting an attorney's
	 * words automatically would be worse than flagging them — the substitution
	 * is a judgement call, and silently changing legal copy hides the problem.
	 *
	 * @param string $text Text to scan.
	 * @return array<int, array{term:string, type:string, reason:string}>
	 */
	public static function scan_text( $text ) {
		$findings = array();

		if ( ! is_string( $text ) || '' === trim( $text ) ) {
			return $findings;
		}

		$plain = wp_strip_all_tags( $text );

		foreach ( Config::get( 'compliance.restricted_terms', array() ) as $term => $reason ) {
			if ( preg_match( '/\b' . preg_quote( $term, '/' ) . '\b/i', $plain ) ) {
				$findings[] = array(
					'term'   => $term,
					'type'   => 'restricted_term',
					'reason' => $reason,
				);
			}
		}

		foreach ( Config::get( 'compliance.results_language', array() ) as $phrase ) {
			if ( false !== stripos( $plain, $phrase ) ) {
				$findings[] = array(
					'term'   => $phrase,
					'type'   => 'results_language',
					'reason' => __( 'RPC 7.1 — may create unjustified expectations about results.', 'robertslaw' ),
				);
			}
		}

		return $findings;
	}

	/**
	 * Recursively strip forbidden schema types from a JSON-LD structure.
	 *
	 * CLAUDE.md rule 4: never add Review or AggregateRating schema. Applied at
	 * the single output point in RobertsLaw\Schema, so it catches anything —
	 * including markup added by a plugin or pasted into a builder field.
	 *
	 * @param mixed $data Schema fragment.
	 * @return mixed Filtered fragment, or null if the node itself is forbidden.
	 */
	public static function strip_forbidden_schema( $data ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		$forbidden = Config::get( 'compliance.forbidden_schema_types', array() );

		if ( isset( $data['@type'] ) ) {
			$types = (array) $data['@type'];
			foreach ( $types as $type ) {
				if ( in_array( $type, $forbidden, true ) ) {
					return null;
				}
			}
		}

		$clean = array();

		foreach ( $data as $key => $value ) {
			// Drop review/rating properties even on an otherwise allowed node.
			if ( in_array( $key, array( 'review', 'reviews', 'aggregateRating', 'ratingValue' ), true ) ) {
				continue;
			}

			$filtered = self::strip_forbidden_schema( $value );

			if ( null === $filtered ) {
				continue;
			}

			if ( is_array( $filtered ) && empty( $filtered ) && is_array( $value ) && ! empty( $value ) ) {
				continue;
			}

			$clean[ $key ] = $filtered;
		}

		return $clean;
	}

	/**
	 * The canonical disclaimer text. Never paraphrased, never edited in a
	 * builder — CLAUDE.md rule 6.
	 *
	 * @param string $variant default|form|testimonial|compact.
	 * @return string
	 */
	public static function disclaimer( $variant = 'default' ) {
		switch ( $variant ) {
			case 'form':
				return Config::get( 'compliance.form_disclaimer', '' );
			case 'testimonial':
				return Config::get( 'compliance.testimonial_disclaimer', '' );
			default:
				return Config::get( 'compliance.site_disclaimer', '' );
		}
	}

	/**
	 * May testimonials render on this page?
	 *
	 * False for pages flagged sensitive in config/pages.php — currently
	 * /orders-of-protection/. Someone reading that page may be in danger, and
	 * the page serves safety before marketing.
	 *
	 * @param string $page_key Page key from config/pages.php.
	 * @return bool
	 */
	public static function testimonials_allowed( $page_key ) {
		if ( ! $page_key ) {
			return true;
		}

		$blocked = Config::get( 'compliance.no_testimonial_pages', array() );

		if ( in_array( $page_key, $blocked, true ) ) {
			return false;
		}

		$page = Config::get( 'pages.' . $page_key, array() );

		if ( ! empty( $page['no_testimonials'] ) || ! empty( $page['sensitive'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Is a testimonial entry cleared to publish?
	 *
	 * All four consent gates must pass, and the firm must not have served as
	 * the neutral. An entry that fails any of these does not render, whatever
	 * its post status. CLAUDE.md rule 3 and content/testimonial-process.md.
	 *
	 * @param int $post_id Testimonial post ID.
	 * @return bool
	 */
	public static function testimonial_cleared( $post_id ) {
		$required = array(
			'consent_on_file',
			'final_wording_approved',
			'matter_concluded',
		);

		foreach ( $required as $key ) {
			if ( ! get_post_meta( $post_id, '_rl_' . $key, true ) ) {
				return false;
			}
		}

		if ( ! get_post_meta( $post_id, '_rl_consent_date', true ) ) {
			return false;
		}

		if ( get_post_meta( $post_id, '_rl_served_as_neutral', true ) ) {
			return false;
		}

		return true;
	}
}
