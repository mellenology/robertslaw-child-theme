<?php
/**
 * Core WordPress head/output cleanup.
 *
 * These removals are the low-risk tier: they strip markup and requests that a modern
 * storefront does not use. Nothing here changes layout or checkout behaviour.
 *
 * @package Mellenade\Performance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Removes unused core output from wp_head and the front-end asset queue.
 */
final class Mellenade_Perf_Cleanup {

	/**
	 * Hook everything.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'clean_head' ) );
		add_action( 'init', array( __CLASS__, 'disable_emojis' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_block_library' ), 100 );
		add_filter( 'wp_default_scripts', array( __CLASS__, 'remove_jquery_migrate' ) );
		add_filter( 'heartbeat_settings', array( __CLASS__, 'throttle_heartbeat' ) );
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}

	/**
	 * Strip generator tags, shortlinks and the RSD/WLW discovery endpoints.
	 *
	 * The generator tag also advertises the exact WordPress version, which is worth
	 * removing on a store that takes card payments.
	 */
	public static function clean_head() {
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

		// oEmbed discovery + the 5KB wp-embed.min.js that ships with it. A card store
		// does not embed its own posts elsewhere, so the discovery links are dead weight.
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}

	/**
	 * Remove the emoji detection script and its inline styles.
	 *
	 * wp-emoji-release.min.js is ~11KB and runs on every page to polyfill emoji in
	 * browsers that have supported them natively for a decade.
	 */
	public static function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		add_filter( 'tiny_mce_plugins', array( __CLASS__, 'remove_tinymce_emoji' ) );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	/**
	 * Drop the emoji plugin from TinyMCE so the editor stops loading it too.
	 *
	 * @param array $plugins TinyMCE plugins.
	 * @return array
	 */
	public static function remove_tinymce_emoji( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		return array_diff( $plugins, array( 'wpemoji' ) );
	}

	/**
	 * Remove the block editor front-end stylesheet when no block is actually rendered.
	 *
	 * wp-block-library is ~90KB uncompressed. WooCommerce templates are PHP, not blocks,
	 * so on a classic product/shop page it is pure overhead. The has_blocks() check keeps
	 * the stylesheet on any page that genuinely uses blocks, so editorial pages are safe.
	 */
	public static function dequeue_block_library() {
		if ( ! mellenade_perf_is_frontend() ) {
			return;
		}

		// WooCommerce cart/checkout blocks depend on block styles. Never touch those.
		if ( mellenade_perf_has_woocommerce() && ( is_cart() || is_checkout() || is_account_page() ) ) {
			return;
		}

		if ( is_singular() && has_blocks( get_queried_object_id() ) ) {
			return;
		}

		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}

	/**
	 * Unbind jQuery Migrate from the front end.
	 *
	 * Migrate only exists to shim jQuery 1.x-era calls. If a legacy plugin on the store
	 * still needs it, JS errors will show in the console immediately, and this module can
	 * be turned off with MELLENADE_PERF_DISABLE_CLEANUP.
	 *
	 * @param WP_Scripts $scripts Script registry.
	 */
	public static function remove_jquery_migrate( $scripts ) {
		if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
			return;
		}

		$jquery = $scripts->registered['jquery'];

		if ( ! empty( $jquery->deps ) ) {
			$jquery->deps = array_diff( $jquery->deps, array( 'jquery-migrate' ) );
		}
	}

	/**
	 * Slow the Heartbeat API down.
	 *
	 * Heartbeat fires admin-ajax.php on an interval. On shared/managed hosting that is a
	 * meaningful slice of PHP workers, and a store gains nothing from 15-second polling.
	 *
	 * @param array $settings Heartbeat settings.
	 * @return array
	 */
	public static function throttle_heartbeat( $settings ) {
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$settings['interval'] = 60;

		return $settings;
	}
}

Mellenade_Perf_Cleanup::init();
