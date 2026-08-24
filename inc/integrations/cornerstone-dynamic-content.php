<?php
/**
 * Cornerstone Dynamic Content for firm facts.
 *
 * Registers the firm's name, phone, email, and address as dynamic content
 * fields, so an editor writing copy in any Pro element inserts
 * {{dc:rl_firm:phone_display}} instead of retyping "(901) 800-2948".
 *
 * That matters more here than on a typical site: content/site-architecture.md
 * treats identical NAP strings across every page as the foundation of AI
 * citation, and retyping is exactly how they drift apart.
 *
 * Registration is guarded on function_exists, so the theme works unchanged if
 * the Dynamic Content API is unavailable — the [rl_firm] shortcode covers the
 * same ground.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw\Cornerstone;

use RobertsLaw\Compliance;
use RobertsLaw\Config;
use RobertsLaw\Firm;

defined( 'ABSPATH' ) || exit;

/**
 * Fields exposed to the builder.
 *
 * @return array<string, string> Field key => label.
 */
function dynamic_fields() {
	return array(
		'name'          => __( 'Firm name', 'robertslaw' ),
		'attorney'      => __( 'Attorney name', 'robertslaw' ),
		'phone_display' => __( 'Phone (display)', 'robertslaw' ),
		'phone_href'    => __( 'Phone (tel: link)', 'robertslaw' ),
		'email'         => __( 'Email', 'robertslaw' ),
		'locality'      => __( 'City', 'robertslaw' ),
		'region_full'   => __( 'State', 'robertslaw' ),
		'service_area'  => __( 'Service area', 'robertslaw' ),
		'street_address' => __( 'Street address', 'robertslaw' ),
		'postal_code'   => __( 'ZIP code', 'robertslaw' ),
		'office_hours'  => __( 'Office hours', 'robertslaw' ),
		'address_line'  => __( 'Full address (one line)', 'robertslaw' ),
	);
}

/**
 * Resolve one dynamic field.
 *
 * Unresolved placeholders return an empty string rather than "[STREET_ADDRESS]"
 * — a bracket token rendered into live page copy is worse than a gap.
 *
 * @param string $field Field key.
 * @return string
 */
function resolve_field( $field ) {
	if ( 'address_line' === $field ) {
		return Firm::address_line();
	}

	return Firm::resolved( $field );
}

/**
 * Register with the Dynamic Content API when it is present.
 *
 * @return void
 */
function register_dynamic_content() {
	if ( ! function_exists( 'cornerstone_dynamic_content_register_field' ) ) {
		return;
	}

	foreach ( dynamic_fields() as $field => $label ) {
		cornerstone_dynamic_content_register_field(
			'rl_firm_' . $field,
			array(
				'name'  => $label,
				'group' => __( 'Roberts Law — Firm', 'robertslaw' ),
				'type'  => 'string',
				'label' => $label,
				'callback' => static function () use ( $field ) {
					return resolve_field( $field );
				},
			)
		);
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_dynamic_content', 20 );

/**
 * Fallback: a `{{dc:rl_firm:field}}`-style shortcode filter for any Pro text
 * field, applied through the standard shortcode pipeline.
 *
 * Registered by RobertsLaw\Firm as [rl_firm field="..."]; this adds the
 * compliance-safe alias used inside builder copy.
 *
 * @return void
 */
function register_alias() {
	add_shortcode(
		'firm',
		static function ( $atts ) {
			$atts  = shortcode_atts( array( 'field' => 'name' ), $atts, 'firm' );
			$field = sanitize_key( $atts['field'] );

			return esc_html( resolve_field( $field ) );
		}
	);
}
add_action( 'init', __NAMESPACE__ . '\\register_alias', 20 );
