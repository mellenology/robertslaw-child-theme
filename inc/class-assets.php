<?php
/**
 * Stylesheet and script loading.
 *
 * The parent theme (Pro) enqueues its own stylesheet; this loads the child's
 * base styles on top, then inlines the component CSS.
 *
 * Component CSS is inlined rather than requested because the whole set is a
 * few kilobytes and which components a page uses is not known until after
 * <head> has been sent — an enqueue at render time would land in the footer
 * and flash unstyled content, which is a CLS problem, and CLAUDE.md sets a
 * CLS target of 0.1.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Assets {

	const CACHE_KEY = 'robertslaw_component_css';

	/**
	 * Hook up loading.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'print_component_css' ), 3 );
	}

	/**
	 * Enqueue the child stylesheet and any page-specific script.
	 *
	 * @return void
	 */
	public static function enqueue() {
		wp_enqueue_style(
			'robertslaw-base',
			ROBERTSLAW_URI . 'assets/css/base.css',
			array(),
			self::version( ROBERTSLAW_DIR . 'assets/css/base.css' )
		);

		// The quick-exit control ships only where it is needed. Loading it
		// sitewide would put a "leave this site" affordance on pages that have
		// no business showing one.
		$page = SEO::current_page();

		if ( ! empty( $page['sensitive'] ) || ! empty( $page['require_safety_exit'] ) ) {
			wp_enqueue_script(
				'robertslaw-safety-exit',
				ROBERTSLAW_URI . 'assets/js/safety-exit.js',
				array(),
				self::version( ROBERTSLAW_DIR . 'assets/js/safety-exit.js' ),
				true
			);
		}
	}

	/**
	 * Print every component's stylesheet inline, once.
	 *
	 * @return void
	 */
	public static function print_component_css() {
		$css = get_transient( self::CACHE_KEY );

		if ( false === $css || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			$css = self::collect_component_css();
			set_transient( self::CACHE_KEY, $css, DAY_IN_SECONDS );
		}

		if ( '' === trim( (string) $css ) ) {
			return;
		}

		echo '<style id="rl-components">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-authored CSS files.
	}

	/**
	 * Concatenate components/<slug>/style.css for every registered component.
	 *
	 * @return string
	 */
	private static function collect_component_css() {
		$css = '';

		foreach ( array_keys( Components::all() ) as $slug ) {
			$path = ROBERTSLAW_DIR . 'components/' . $slug . '/style.css';

			if ( ! file_exists( $path ) ) {
				continue;
			}

			$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.

			if ( false !== $contents ) {
				$css .= $contents;
			}
		}

		return self::minify( $css );
	}

	/**
	 * Conservative minifier: strips comments and collapses whitespace.
	 *
	 * @param string $css CSS source.
	 * @return string
	 */
	private static function minify( $css ) {
		$css = preg_replace( '!/\*.*?\*/!s', '', $css );
		$css = preg_replace( '/\s+/', ' ', (string) $css );
		$css = str_replace( array( ' {', '{ ', ' }', '} ', '; ', ': ', ', ' ), array( '{', '{', '}', '}', ';', ':', ',' ), (string) $css );

		return trim( (string) $css );
	}

	/**
	 * Cache-bust on file mtime in debug, on theme version otherwise.
	 *
	 * @param string $path Absolute file path.
	 * @return string
	 */
	private static function version( $path ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( $path ) ) {
			return (string) filemtime( $path );
		}

		return ROBERTSLAW_VERSION;
	}

	/**
	 * Drop the inlined-CSS cache. Called when the theme updates.
	 *
	 * @return void
	 */
	public static function flush() {
		delete_transient( self::CACHE_KEY );
	}
}

add_action( 'switch_theme', array( '\RobertsLaw\Assets', 'flush' ) );
add_action( 'upgrader_process_complete', array( '\RobertsLaw\Assets', 'flush' ) );
