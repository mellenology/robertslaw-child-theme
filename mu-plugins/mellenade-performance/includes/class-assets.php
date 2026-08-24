<?php
/**
 * Asset delivery: image decoding, LCP priority and resource hints.
 *
 * Sports card listings are image-dense — a category page is essentially a wall of scans.
 * That makes image delivery, not script size, the dominant Core Web Vitals factor here,
 * so this module concentrates on how images are prioritised and decoded.
 *
 * @package Mellenade\Performance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end asset tuning.
 */
final class Mellenade_Perf_Assets {

	/**
	 * Tracks whether the LCP candidate has been marked for this request.
	 *
	 * @var bool
	 */
	private static $lcp_assigned = false;

	/**
	 * Hook everything.
	 */
	public static function init() {
		add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'image_attributes' ), 10, 3 );
		add_filter( 'wp_resource_hints', array( __CLASS__, 'resource_hints' ), 10, 2 );
		add_filter( 'wp_lazy_loading_enabled', array( __CLASS__, 'lazy_loading_enabled' ), 10, 3 );
		add_filter( 'style_loader_tag', array( __CLASS__, 'clean_asset_tag' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_accessibility_css' ), 999 );
	}

	/**
	 * Add async decoding, and hand the first product image a high fetch priority.
	 *
	 * Rationale: browsers decode images on the main thread by default, which competes with
	 * script execution during page load. decoding="async" moves that off the critical path.
	 *
	 * Separately, the single largest LCP mistake on a WooCommerce store is lazy-loading the
	 * hero/first product image — the browser then refuses to fetch it until layout settles.
	 * The first image on a product page is eagerly loaded and given fetchpriority="high"
	 * so it starts downloading in the initial burst.
	 *
	 * @param array   $attr       Image attributes.
	 * @param WP_Post $attachment Attachment post object.
	 * @param mixed   $size       Requested size.
	 * @return array
	 */
	public static function image_attributes( $attr, $attachment, $size ) {
		unset( $attachment, $size );

		if ( ! is_array( $attr ) ) {
			return $attr;
		}

		if ( ! isset( $attr['decoding'] ) ) {
			$attr['decoding'] = 'async';
		}

		if ( ! mellenade_perf_is_frontend() ) {
			return $attr;
		}

		// Only the first image of a single product is treated as the LCP candidate.
		// On archives the hero is theme-controlled and guessing wrong would hurt.
		$is_product_page = mellenade_perf_has_woocommerce() && function_exists( 'is_product' ) && is_product();

		if ( $is_product_page && ! self::$lcp_assigned ) {
			self::$lcp_assigned    = true;
			$attr['loading']       = 'eager';
			$attr['fetchpriority'] = 'high';
		}

		return $attr;
	}

	/**
	 * Never lazy-load the very first in-content image.
	 *
	 * Core added this heuristic in 5.9, but themes that render images through their own
	 * markup can still bypass it. This is a belt-and-braces guard for the same problem.
	 *
	 * @param bool   $default   Whether to lazy load.
	 * @param string $tag_name  Tag name.
	 * @param string $context   Calling context.
	 * @return bool
	 */
	public static function lazy_loading_enabled( $default, $tag_name, $context ) {
		unset( $context );

		if ( 'img' === $tag_name && ! self::$lcp_assigned && mellenade_perf_is_frontend() ) {
			if ( mellenade_perf_has_woocommerce() && function_exists( 'is_product' ) && is_product() ) {
				return false;
			}
		}

		return $default;
	}

	/**
	 * Emit preconnect hints for origins the store actually talks to.
	 *
	 * Only origins that are provably in use are added. A preconnect to a host the page
	 * never contacts wastes a socket and can slow the page down, which is why this checks
	 * for the relevant plugin/handle before adding each hint.
	 *
	 * @param array  $hints         Current hints.
	 * @param string $relation_type Hint relation.
	 * @return array
	 */
	public static function resource_hints( $hints, $relation_type ) {
		if ( 'preconnect' !== $relation_type || ! mellenade_perf_is_frontend() ) {
			return $hints;
		}

		if ( ! is_array( $hints ) ) {
			$hints = array();
		}

		// Stripe and PayPal are only contacted on cart/checkout. Preconnecting there
		// shaves the TLS handshake off the payment iframe, which is on the critical path
		// for conversion.
		if ( mellenade_perf_has_woocommerce() && ( is_cart() || is_checkout() ) ) {
			if ( defined( 'WC_STRIPE_VERSION' ) || class_exists( 'WC_Gateway_Stripe' ) ) {
				$hints[] = array(
					'href'        => 'https://js.stripe.com',
					'crossorigin' => 'anonymous',
				);
			}

			if ( class_exists( 'WC_Gateway_PPEC_Plugin' ) || defined( 'PAYPAL_FOR_WOOCOMMERCE_VERSION' ) ) {
				$hints[] = array(
					'href'        => 'https://www.paypal.com',
					'crossorigin' => 'anonymous',
				);
			}
		}

		// Google Fonts, only if something on the page actually enqueued them.
		if ( self::uses_google_fonts() ) {
			$hints[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}

		return $hints;
	}

	/**
	 * Detect a queued Google Fonts stylesheet.
	 *
	 * @return bool
	 */
	private static function uses_google_fonts() {
		if ( ! function_exists( 'wp_styles' ) ) {
			return false;
		}

		$styles = wp_styles();

		if ( empty( $styles->queue ) ) {
			return false;
		}

		foreach ( $styles->queue as $handle ) {
			if ( empty( $styles->registered[ $handle ]->src ) ) {
				continue;
			}

			if ( false !== strpos( $styles->registered[ $handle ]->src, 'fonts.googleapis.com' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Load the contrast/CLS corrections.
	 *
	 * Priority 999 so it lands after the theme's own stylesheets and wins on specificity
	 * ties without needing !important.
	 */
	public static function enqueue_accessibility_css() {
		if ( ! mellenade_perf_is_frontend() ) {
			return;
		}

		$rel  = '/mellenade-performance/assets/accessibility.css';
		$path = MELLENADE_PERF_DIR . '/assets/accessibility.css';

		if ( ! is_readable( $path ) ) {
			return;
		}

		wp_enqueue_style(
			'mellenade-accessibility',
			plugins_url( $rel, dirname( MELLENADE_PERF_DIR ) . '/mellenade-performance.php' ),
			array(),
			MELLENADE_PERF_VERSION
		);
	}

	/**
	 * Drop the type="text/css" attribute that older plugins still emit.
	 *
	 * Cosmetic and tiny, but it keeps the head consistent and HTML5-valid.
	 *
	 * @param string $tag    Stylesheet tag.
	 * @param string $handle Style handle.
	 * @return string
	 */
	public static function clean_asset_tag( $tag, $handle ) {
		unset( $handle );

		return str_replace( " type='text/css'", '', $tag );
	}
}

Mellenade_Perf_Assets::init();
