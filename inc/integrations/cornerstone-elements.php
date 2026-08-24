<?php
/**
 * Themeco Pro / Cornerstone element registration.
 *
 * Every component in config/components.php is exposed as a drag-and-drop
 * element in the Cornerstone builder. The element carries no markup and no
 * defaults of its own — its controls, defaults, and render all delegate to
 * RobertsLaw\Components, so an element edited in the builder and the same
 * component called from a template are the same thing.
 *
 * WHY THERE ARE GENERATED FILES
 * cornerstone_register_element( $class, $name, $dir ) wants a directory holding
 * definition.php, controls.php, defaults.php, and shortcode.php, and the
 * definition must declare a uniquely named class. Those four files per
 * component are pure boilerplate, so they are generated rather than
 * hand-maintained:
 *
 *     php bin/generate-elements.php
 *
 * Add a component to config/components.php, run that, commit the result.
 *
 * A NOTE ON VERIFICATION
 * theme.co is blocked by this environment's egress policy, so the registration
 * signature here follows the published Element API shape rather than a
 * page-by-page check against Themeco's current reference. If Pro's API has
 * moved, the fix is confined to this file and to Components::controls().
 *
 * @package RobertsLaw
 */

namespace RobertsLaw\Cornerstone;

use RobertsLaw\Components;

defined( 'ABSPATH' ) || exit;

/**
 * The `ui()` payload for a component's element.
 *
 * Called by each generated definition.php.
 *
 * @param string $slug Component slug.
 * @return array
 */
function ui( $slug ) {
	$component = Components::get( $slug );

	return array(
		'title'      => $component['label'] ?? $slug,
		'description' => $component['description'] ?? '',
		'icon_group' => 'robertslaw',
		'group'      => $component['category'] ?? 'Roberts Law',
		'supports'   => array( 'id', 'class', 'style' ),
	);
}

/**
 * Register every component as an element.
 *
 * @return void
 */
function register_elements() {
	if ( ! function_exists( 'cornerstone_register_element' ) ) {
		return;
	}

	foreach ( array_keys( Components::all() ) as $slug ) {
		$dir = ROBERTSLAW_DIR . 'components/' . $slug;

		// Skip components whose element files have not been generated yet, so
		// a missing generator run degrades to "not in the builder" rather than
		// a fatal error.
		if ( ! file_exists( $dir . '/definition.php' ) ) {
			continue;
		}

		cornerstone_register_element( class_name( $slug ), Components::element_name( $slug ), $dir );
	}
}
add_action( 'cornerstone_register_elements', __NAMESPACE__ . '\\register_elements' );

/**
 * Element class name for a component: 'cta-band' => 'RL_Element_Cta_Band'.
 *
 * Used by the generator and by registration; both must agree, so it lives in
 * one function.
 *
 * @param string $slug Component slug.
 * @return string
 */
function class_name( $slug ) {
	$parts = array_map(
		static function ( $part ) {
			return ucfirst( preg_replace( '/[^a-z0-9]/', '', strtolower( $part ) ) );
		},
		explode( '-', $slug )
	);

	return 'RL_Element_' . implode( '_', $parts );
}

/**
 * Point the builder's icon lookup at the theme's sprite.
 *
 * @param array $icon_map Existing map.
 * @return array
 */
function icon_map( $icon_map ) {
	$icon_map['robertslaw'] = ROBERTSLAW_URI . 'assets/svg/elements.svg';

	return $icon_map;
}
add_filter( 'cornerstone_icon_map', __NAMESPACE__ . '\\icon_map' );
