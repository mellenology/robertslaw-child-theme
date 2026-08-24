<?php
/**
 * Boots the Mellenade performance modules.
 *
 * Design notes
 * ------------
 * - Nothing here assumes a particular theme. The store's active theme is not known to
 *   this package, so every hook is either core, WooCommerce, or guarded by a capability
 *   check. That keeps the plugin safe to drop onto the site as-is.
 * - Each module is gated by a `MELLENADE_PERF_DISABLE_*` constant so a single change can
 *   be rolled back from wp-config.php without touching this code or redeploying.
 * - Admin, AJAX, REST and WP-CLI requests are left alone unless a module explicitly opts
 *   in. Front-end optimisation that leaks into the admin is the usual cause of "the
 *   dashboard broke after we added a speed plugin".
 *
 * @package Mellenade\Performance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a given module should load.
 *
 * A module named 'assets' is disabled by defining MELLENADE_PERF_DISABLE_ASSETS as true.
 *
 * @param string $module Module slug.
 * @return bool
 */
function mellenade_perf_module_enabled( $module ) {
	$constant = 'MELLENADE_PERF_DISABLE_' . strtoupper( $module );

	if ( defined( $constant ) && constant( $constant ) ) {
		return false;
	}

	/**
	 * Filters whether a Mellenade performance module loads.
	 *
	 * @param bool   $enabled Whether the module is enabled.
	 * @param string $module  Module slug.
	 */
	return (bool) apply_filters( 'mellenade_perf_module_enabled', true, $module );
}

/**
 * True when this is a normal front-end page view.
 *
 * Deliberately excludes admin screens, AJAX, REST, cron, WP-CLI, feeds and the customizer
 * preview, because dequeuing assets in any of those contexts breaks editing tools.
 *
 * @return bool
 */
function mellenade_perf_is_frontend() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() ) {
		return false;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
		return false;
	}

	return true;
}

/**
 * True when WooCommerce is active.
 *
 * @return bool
 */
function mellenade_perf_has_woocommerce() {
	return class_exists( 'WooCommerce' );
}

$mellenade_perf_modules = array(
	'cleanup'     => '/includes/class-cleanup.php',
	'assets'      => '/includes/class-assets.php',
	'fonts'       => '/includes/class-fonts.php',
	'woocommerce' => '/includes/class-woocommerce.php',
	'seo'         => '/includes/class-seo.php',
);

foreach ( $mellenade_perf_modules as $mellenade_perf_slug => $mellenade_perf_file ) {
	if ( ! mellenade_perf_module_enabled( $mellenade_perf_slug ) ) {
		continue;
	}

	$mellenade_perf_path = MELLENADE_PERF_DIR . $mellenade_perf_file;

	if ( is_readable( $mellenade_perf_path ) ) {
		require_once $mellenade_perf_path;
	}
}

unset( $mellenade_perf_modules, $mellenade_perf_slug, $mellenade_perf_file, $mellenade_perf_path );
