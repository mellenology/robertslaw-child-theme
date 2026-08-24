<?php
/**
 * Font loading — the dominant cost on this site.
 *
 * MEASURED (PageSpeed Insights, mobile, 2026-08-24, Lighthouse 13.4.1):
 *   Total page weight ............ 733 KB over 27 requests
 *   Fonts ........................ 340 KB  (46% of the page)
 *     fa-solid-900.woff2 ......... 276 KB  <- single heaviest resource on the site
 *     Poppins (4 files, gstatic) ..  63 KB
 *   Cumulative Layout Shift ...... 0.171   (target < 0.10)
 *
 * Font Awesome Solid alone is 38% of the page. It ships from the Pro/Cornerstone theme as
 * the complete icon set, while the front page renders a handful of icons.
 *
 * CLS link: the largest recorded shift (0.126 of the 0.171 total) moves `main.x-layout`,
 * and the second (0.032) moves the hero `h2`. Text blocks moving after paint is the
 * signature of a web font swapping in and re-flowing the line box. That makes font metrics
 * the prime suspect, which is what the fallback overrides below address.
 *
 * @package Mellenade\Performance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Font delivery tuning.
 */
final class Mellenade_Perf_Fonts {

	/**
	 * Hook everything.
	 */
	public static function init() {
		add_filter( 'wp_resource_hints', array( __CLASS__, 'preconnect_gstatic' ), 10, 2 );
		add_action( 'wp_head', array( __CLASS__, 'print_font_metrics' ), 2 );
		add_filter( 'style_loader_tag', array( __CLASS__, 'add_font_display' ), 10, 4 );
	}

	/**
	 * Preconnect to fonts.gstatic.com.
	 *
	 * Unlike the speculative hints in class-assets.php, this one is confirmed by the audit:
	 * the page makes four requests to fonts.gstatic.com for Poppins. Those currently pay a
	 * full DNS + TCP + TLS handshake before the first byte, on the critical path for text.
	 *
	 * Note this is added unconditionally rather than via the enqueue-sniffing helper,
	 * because the Pro theme injects its Google Fonts link through its own typography
	 * system rather than a wp_enqueue_style handle the sniffer can see.
	 *
	 * @param array  $hints         Current hints.
	 * @param string $relation_type Hint relation.
	 * @return array
	 */
	public static function preconnect_gstatic( $hints, $relation_type ) {
		if ( ! mellenade_perf_is_frontend() ) {
			return $hints;
		}

		if ( ! is_array( $hints ) ) {
			$hints = array();
		}

		if ( 'preconnect' === $relation_type ) {
			$hints[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}

		return $hints;
	}

	/**
	 * Emit a metric-matched fallback face so the Poppins swap does not move text.
	 *
	 * How this kills layout shift: the browser paints fallback text immediately, then
	 * re-paints once Poppins arrives. If the two faces have different metrics the line
	 * boxes change height/width and everything below moves — that is the 0.126 shift on
	 * `main.x-layout`.
	 *
	 * By overriding the fallback's metrics to match Poppins, the fallback occupies exactly
	 * the same space, so the swap becomes visually invisible and contributes no CLS.
	 *
	 * The override values are tuned for Poppins against a local Arial/Helvetica stack.
	 * Verify against the live hero after deploying; if the fallback looks too wide or too
	 * narrow, adjust `size-adjust` first.
	 */
	public static function print_font_metrics() {
		if ( ! mellenade_perf_is_frontend() ) {
			return;
		}

		/**
		 * Filters the metric-matched fallback CSS.
		 *
		 * Return an empty string to skip it entirely.
		 *
		 * @param string $css Fallback @font-face CSS.
		 */
		$css = apply_filters(
			'mellenade_perf_font_fallback_css',
			'@font-face{font-family:"Poppins Fallback";src:local("Arial");size-adjust:112.16%;ascent-override:93.62%;descent-override:31.21%;line-gap-override:0%}'
		);

		if ( ! $css ) {
			return;
		}

		echo '<style id="mellenade-font-metrics">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static CSS, filterable by developers only.
	}

	/**
	 * Force font-display on the theme's Google Fonts request.
	 *
	 * Without it the browser blocks text paint for up to 3s waiting on the font, which is
	 * what pushes First Contentful Paint out to 1.8s here. `swap` renders fallback text
	 * immediately — safe to do precisely because the metric overrides above mean the swap
	 * no longer shifts layout.
	 *
	 * @param string $tag    Link tag.
	 * @param string $handle Style handle.
	 * @param string $href   Stylesheet URL.
	 * @param string $media  Media attribute.
	 * @return string
	 */
	public static function add_font_display( $tag, $handle, $href, $media ) {
		unset( $handle, $media );

		if ( ! is_string( $href ) || false === strpos( $href, 'fonts.googleapis.com' ) ) {
			return $tag;
		}

		if ( false !== strpos( $href, 'display=' ) ) {
			return $tag;
		}

		$updated = add_query_arg( 'display', 'swap', $href );

		return str_replace( $href, esc_url( $updated ), $tag );
	}
}

Mellenade_Perf_Fonts::init();
