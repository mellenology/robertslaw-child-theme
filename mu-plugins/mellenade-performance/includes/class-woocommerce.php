<?php
/**
 * WooCommerce-specific performance work.
 *
 * WooCommerce enqueues its stylesheets and scripts on every page of the site, including
 * the home page, blog posts and contact pages that contain no store markup at all. It also
 * ships `wc-cart-fragments`, which fires an uncached admin-ajax.php request on every single
 * page view. On a small store those two behaviours are usually the largest single source of
 * both wasted bytes and wasted PHP workers.
 *
 * @package Mellenade\Performance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Trims WooCommerce's global footprint down to the pages that need it.
 */
final class Mellenade_Perf_WooCommerce {

	/**
	 * Hook everything, but only when WooCommerce is present.
	 */
	public static function init() {
		if ( ! mellenade_perf_has_woocommerce() ) {
			return;
		}

		// Priority 99: run after WooCommerce and the theme have queued their assets.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_offsite_assets' ), 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_disable_cart_fragments' ), 99 );

		add_filter( 'woocommerce_admin_disabled', array( __CLASS__, 'maybe_disable_analytics' ) );
		add_filter( 'woocommerce_helper_suppress_admin_notices', '__return_true' );
		add_filter( 'woocommerce_queue_class', array( __CLASS__, 'keep_default_queue' ) );

		// The status/marketplace widgets run remote calls on dashboard load.
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'remove_dashboard_widgets' ), 99 );
	}

	/**
	 * True when the current request renders any WooCommerce UI.
	 *
	 * Checked broadly on purpose — a false negative here strips styling off a real store
	 * page, which is far worse than shipping a few unnecessary KB.
	 *
	 * @return bool
	 */
	public static function is_store_page() {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return true;
		}

		if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
			return true;
		}

		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url() ) {
			return true;
		}

		// Any page that embeds a store shortcode or a WooCommerce block still needs assets.
		$post = get_post();

		if ( $post instanceof WP_Post ) {
			$content = $post->post_content;

			$needles = array(
				'[woocommerce_',
				'[product',
				'[add_to_cart',
				'[shop_',
				'wp:woocommerce/',
			);

			foreach ( $needles as $needle ) {
				if ( false !== strpos( $content, $needle ) ) {
					return true;
				}
			}
		}

		/**
		 * Filters whether the current request counts as a store page.
		 *
		 * Use this to whitelist any custom template that renders WooCommerce markup
		 * without matching the checks above.
		 *
		 * @param bool $is_store_page Whether assets should load.
		 */
		return (bool) apply_filters( 'mellenade_perf_is_store_page', false );
	}

	/**
	 * Dequeue WooCommerce CSS/JS on pages with no store markup.
	 */
	public static function dequeue_offsite_assets() {
		if ( ! mellenade_perf_is_frontend() || self::is_store_page() ) {
			return;
		}

		$styles = array(
			'woocommerce-general',
			'woocommerce-layout',
			'woocommerce-smallscreen',
			'woocommerce_frontend_styles',
			'woocommerce_fancybox_styles',
			'woocommerce_chosen_styles',
			'woocommerce_prettyPhoto_css',
			'wc-blocks-style',
			'wc-blocks-vendors-style',
			'brands-styles',
		);

		foreach ( $styles as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}

		$scripts = array(
			'woocommerce',
			'wc-add-to-cart',
			'wc-cart-fragments',
			'jquery-blockui',
			'jquery-placeholder',
			'prettyPhoto',
			'prettyPhoto-init',
			'fancybox',
			'jqueryui',
			'selectWoo',
			'wc-country-select',
			'wc-address-i18n',
		);

		foreach ( $scripts as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}

	/**
	 * Drop cart fragments on pages where a live cart count is not being displayed.
	 *
	 * TRADE-OFF — read before enabling site-wide:
	 * `wc-cart-fragments` is what keeps a header cart counter updating without a page
	 * reload. Removing it everywhere makes that counter go stale until the next navigation.
	 *
	 * Because that is a visible behaviour change and depends on the theme's header, this is
	 * conservative by default: fragments are only dropped when the store has no products in
	 * the cart, which is the overwhelming majority of sessions (and exactly the case where
	 * the AJAX call is pure waste). Sessions with a live cart keep full functionality.
	 *
	 * Set MELLENADE_PERF_AGGRESSIVE_FRAGMENTS to true to drop them on all non-store pages
	 * regardless of cart state — measurably faster, but verify the header counter first.
	 */
	public static function maybe_disable_cart_fragments() {
		if ( ! mellenade_perf_is_frontend() ) {
			return;
		}

		// Never touch the pages where the cart itself is the point.
		if ( is_cart() || is_checkout() ) {
			return;
		}

		$aggressive = defined( 'MELLENADE_PERF_AGGRESSIVE_FRAGMENTS' ) && MELLENADE_PERF_AGGRESSIVE_FRAGMENTS;

		if ( $aggressive ) {
			wp_dequeue_script( 'wc-cart-fragments' );
			return;
		}

		// WC()->cart is null on some early/edge requests; bail rather than fatal.
		$cart = function_exists( 'WC' ) && isset( WC()->cart ) ? WC()->cart : null;

		if ( $cart && 0 === $cart->get_cart_contents_count() ) {
			wp_dequeue_script( 'wc-cart-fragments' );
		}
	}

	/**
	 * Optionally switch off the WooCommerce Analytics package.
	 *
	 * The analytics tables are rebuilt by scheduled actions and add meaningful write load.
	 * A store of this size generally reads its numbers from the built-in reports or the
	 * payment processor instead. Off by default; opt in from wp-config.php.
	 *
	 * @param bool $disabled Current value.
	 * @return bool
	 */
	public static function maybe_disable_analytics( $disabled ) {
		if ( defined( 'MELLENADE_PERF_DISABLE_WC_ANALYTICS' ) && MELLENADE_PERF_DISABLE_WC_ANALYTICS ) {
			return true;
		}

		return $disabled;
	}

	/**
	 * Keep WooCommerce's default Action Scheduler queue.
	 *
	 * Present as an explicit no-op so that a future change here is a deliberate decision
	 * rather than an accidental override.
	 *
	 * @param string $class Queue class.
	 * @return string
	 */
	public static function keep_default_queue( $class ) {
		return $class;
	}

	/**
	 * Remove dashboard widgets that make remote requests on every admin load.
	 */
	public static function remove_dashboard_widgets() {
		remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );
		remove_meta_box( 'wc_admin_dashboard_setup', 'dashboard', 'normal' );
	}
}

Mellenade_Perf_WooCommerce::init();
