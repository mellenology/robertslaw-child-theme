<?php
/**
 * Runtime guards.
 *
 * The rules in CLAUDE.md that must hold whatever an editor does in the builder:
 *
 *   - The legal disclaimer appears on every page (rule 6).
 *   - Testimonials never render on a sensitive page.
 *   - The safety block is injected on sensitive pages whether or not someone
 *     remembered to place the component.
 *
 * Enforced here rather than trusted to page setup, because "someone forgot to
 * add the safety block to the orders of protection page" is a failure with a
 * real-world cost.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Guards {

	/**
	 * Has the disclaimer already been printed this request?
	 *
	 * @var bool
	 */
	private static $disclaimer_printed = false;

	/**
	 * Hook up the guards.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'robertslaw_pre_render_component', array( __CLASS__, 'block_testimonials' ), 10, 3 );
		add_filter( 'the_content', array( __CLASS__, 'inject_safety_block' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'print_disclaimer' ), 5 );
		add_action( 'robertslaw_disclaimer_printed', array( __CLASS__, 'mark_printed' ) );
	}

	/**
	 * Suppress the testimonials component on sensitive pages.
	 *
	 * @param string|null $output Short-circuit output.
	 * @param string      $slug   Component slug.
	 * @param array       $atts   Component attributes.
	 * @return string|null
	 */
	public static function block_testimonials( $output, $slug, $atts ) {
		if ( 'testimonials' !== $slug ) {
			return $output;
		}

		$page_key = SEO::current_page_key();

		if ( Compliance::testimonials_allowed( $page_key ) ) {
			return $output;
		}

		if ( current_user_can( 'edit_posts' ) ) {
			return sprintf(
				'<p class="rl-editor-note">%s</p>',
				esc_html__( 'Testimonials are blocked on this page. Someone reading it may be in danger, and the page serves safety before marketing. Remove the component.', 'robertslaw' )
			);
		}

		return '';
	}

	/**
	 * Prepend the safety block on sensitive pages.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function inject_safety_block( $content ) {
		if ( is_admin() || ! is_main_query() || ! in_the_loop() ) {
			return $content;
		}

		$page = SEO::current_page();

		if ( empty( $page['sensitive'] ) && empty( $page['require_safety_exit'] ) ) {
			return $content;
		}

		// Already placed by hand — do not stack two of them.
		if ( false !== strpos( $content, 'rl-safety-exit' ) ) {
			return $content;
		}

		return Components::render( 'safety-exit', array( 'position' => 'inline' ) ) . $content;
	}

	/**
	 * Note that a template already printed the disclaimer.
	 *
	 * @return void
	 */
	public static function mark_printed() {
		self::$disclaimer_printed = true;
	}

	/**
	 * Print the sitewide disclaimer in the footer.
	 *
	 * A Pro footer built in the Layout Builder may already include it via the
	 * component or the shortcode; in that case the template should fire
	 * `do_action( 'robertslaw_disclaimer_printed' )` and this stays quiet.
	 *
	 * @return void
	 */
	public static function print_disclaimer() {
		if ( self::$disclaimer_printed || is_admin() ) {
			return;
		}

		printf(
			'<div class="rl-site-disclaimer"><div class="rl-site-disclaimer__inner"><p>%s</p></div></div>',
			esc_html( Compliance::disclaimer( 'default' ) )
		);
	}
}
