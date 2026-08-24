<?php
/**
 * Firm facts accessor.
 *
 * Reads config/firm.php, with per-field overrides from the Firm Details admin
 * screen layered on top. Code-first so the repo stays the source of truth, but
 * the client can supply the blocked values (address, hours) without a deploy.
 *
 * Every template, component, shortcode and schema block reads the firm's name,
 * phone, and email through here. None of them contain the literal string.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Firm {

	const OPTION = 'robertslaw_firm_overrides';

	/**
	 * Cached overrides.
	 *
	 * @var array|null
	 */
	private static $overrides = null;

	/**
	 * Fields the admin screen is allowed to override.
	 *
	 * Deliberately narrow. The firm name, phone, and email are entity-critical
	 * and are consistent across the site by design — they change in the repo,
	 * in review, not in a text box. The address and hours are here because they
	 * are the two values genuinely blocked on the client.
	 *
	 * @var string[]
	 */
	const EDITABLE = array(
		'street_address',
		'postal_code',
		'office_hours',
		'google_review_url',
	);

	/**
	 * Get a firm field.
	 *
	 * @param string $key     Field key.
	 * @param mixed  $default Fallback.
	 * @return mixed
	 */
	public static function get( $key, $default = '' ) {
		if ( null === self::$overrides ) {
			$stored          = get_option( self::OPTION, array() );
			self::$overrides = is_array( $stored ) ? $stored : array();
		}

		if ( in_array( $key, self::EDITABLE, true )
			&& isset( self::$overrides[ $key ] )
			&& '' !== trim( (string) self::$overrides[ $key ] ) ) {
			return self::$overrides[ $key ];
		}

		return Config::get( 'firm.' . $key, $default );
	}

	/**
	 * Get a field, resolved for machine output — empty string if it is still an
	 * unresolved placeholder.
	 *
	 * @param string $key Field key.
	 * @return string
	 */
	public static function resolved( $key ) {
		return Compliance::resolve( self::get( $key ) );
	}

	/**
	 * Is this field still awaiting a value from the client?
	 *
	 * @param string $key Field key.
	 * @return bool
	 */
	public static function is_blocked( $key ) {
		return Compliance::is_placeholder( self::get( $key ) );
	}

	/**
	 * Formatted single-line address, omitting unresolved parts.
	 *
	 * @return string
	 */
	public static function address_line() {
		$parts = array_filter(
			array(
				self::resolved( 'street_address' ),
				self::resolved( 'locality' ),
				trim( self::resolved( 'region' ) . ' ' . self::resolved( 'postal_code' ) ),
			),
			static function ( $part ) {
				return '' !== trim( (string) $part );
			}
		);

		return implode( ', ', $parts );
	}

	/**
	 * Click-to-call anchor. Required in the mobile header (CLAUDE.md).
	 *
	 * @param string $classes Extra CSS classes.
	 * @param string $label   Override the visible label.
	 * @return string
	 */
	public static function phone_link( $classes = '', $label = '' ) {
		return sprintf(
			'<a class="rl-phone %s" href="%s">%s</a>',
			esc_attr( $classes ),
			esc_url( self::get( 'phone_href' ) ),
			esc_html( $label ? $label : self::get( 'phone_display' ) )
		);
	}

	/**
	 * Mailto anchor.
	 *
	 * @param string $classes Extra CSS classes.
	 * @param string $label   Override the visible label.
	 * @return string
	 */
	public static function email_link( $classes = '', $label = '' ) {
		return sprintf(
			'<a class="rl-email %s" href="%s">%s</a>',
			esc_attr( $classes ),
			esc_url( 'mailto:' . self::get( 'email' ) ),
			esc_html( $label ? $label : self::get( 'email' ) )
		);
	}

	/**
	 * Register [rl_firm field="phone_display"] and friends.
	 *
	 * Lets an editor drop a firm fact into any Cornerstone text field or page
	 * without retyping it — which is how NAP inconsistency creeps in.
	 *
	 * @return void
	 */
	public static function register_shortcodes() {
		add_shortcode(
			'rl_firm',
			static function ( $atts ) {
				$atts = shortcode_atts(
					array(
						'field' => 'name',
						'link'  => 'false',
					),
					$atts,
					'rl_firm'
				);

				$field = sanitize_key( $atts['field'] );
				$link  = filter_var( $atts['link'], FILTER_VALIDATE_BOOLEAN );

				if ( $link && 'phone_display' === $field ) {
					return self::phone_link();
				}

				if ( $link && 'email' === $field ) {
					return self::email_link();
				}

				if ( 'address_line' === $field ) {
					return esc_html( self::address_line() );
				}

				return Compliance::render_value( self::get( $field ), $field );
			}
		);
	}
}

add_action( 'init', array( '\RobertsLaw\Firm', 'register_shortcodes' ) );
