<?php
/**
 * Renders a page blueprint from config/page-templates.php.
 *
 * The blueprints exist to be serialised into Themeco `.tco` templates, but they
 * are also renderable directly, which means a page can be reviewed before the
 * `.tco` pipeline exists and without opening Cornerstone. Both paths read the
 * same blueprint, so what is reviewed here is what gets exported.
 *
 * Use in a template file:
 *     \RobertsLaw\Page_Template::render( 'home' );
 *
 * Or as a shortcode on any page:
 *     [rl_page_template key="home"]
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Page_Template {

	/**
	 * Hook up the shortcode.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_shortcode' ) );
	}

	/**
	 * Register [rl_page_template key="..."].
	 *
	 * @return void
	 */
	public static function register_shortcode() {
		add_shortcode(
			'rl_page_template',
			static function ( $atts ) {
				$atts = shortcode_atts( array( 'key' => '' ), $atts, 'rl_page_template' );
				$key  = sanitize_key( $atts['key'] );

				if ( '' === $key ) {
					$key = SEO::current_page_key();
				}

				return self::get( $key );
			}
		);
	}

	/**
	 * One blueprint.
	 *
	 * @param string $page_key Page key.
	 * @return array
	 */
	public static function get_blueprint( $page_key ) {
		return Config::get( 'page-templates.' . $page_key, array() );
	}

	/**
	 * Render a blueprint and echo it.
	 *
	 * @param string $page_key Page key.
	 * @return void
	 */
	public static function render( $page_key ) {
		echo self::get( $page_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside each component template.
	}

	/**
	 * Render a blueprint and return it.
	 *
	 * @param string $page_key Page key.
	 * @return string HTML.
	 */
	public static function get( $page_key ) {
		$blueprint = self::get_blueprint( $page_key );

		if ( empty( $blueprint['sections'] ) ) {
			return '';
		}

		/*
		 * A blocked page is structurally complete but carries copy that is not
		 * cleared to publish. Editors see it so it can be reviewed; visitors do
		 * not, because a page full of [NEEDS ATTORNEY INPUT] markers must never
		 * be publicly reachable.
		 */
		$withheld = ! empty( $blueprint['blocked'] ) || ! empty( $blueprint['review_required'] );

		if ( $withheld && ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		ob_start();

		if ( $withheld ) {
			printf(
				'<p class="rl-editor-note">%s%s</p>',
				esc_html__( 'Draft page. Copy on this page has not been through attorney review and is hidden from visitors. ', 'robertslaw' ),
				esc_html( $blueprint['blocked'] ?? '' )
			);
		}

		foreach ( $blueprint['sections'] as $section ) {
			self::render_section( $section );
		}

		return (string) ob_get_clean();
	}

	/**
	 * Render one section.
	 *
	 * @param array $section Section definition.
	 * @return void
	 */
	private static function render_section( array $section ) {
		$classes = array(
			'rl-section',
			'rl-section--bg-' . sanitize_html_class( $section['background'] ?? 'surface' ),
			'rl-section--space-' . sanitize_html_class( $section['spacing'] ?? 'md' ),
		);

		printf(
			'<section class="%s" data-rl-section="%s">',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $section['label'] ?? '' )
		);

		$inner = ( 'full' === ( $section['width'] ?? 'contained' ) ) ? '' : ' rl-container';

		printf( '<div class="rl-section__inner%s">', esc_attr( $inner ) );

		foreach ( $section['rows'] ?? array() as $row ) {
			self::render_row( $row );
		}

		echo '</div></section>';
	}

	/**
	 * Render one row.
	 *
	 * @param array $row Row definition.
	 * @return void
	 */
	private static function render_row( array $row ) {
		echo '<div class="rl-row">';

		foreach ( $row['columns'] ?? array() as $column ) {
			$width = str_replace( '/', '-', (string) ( $column['width'] ?? '1/1' ) );

			printf( '<div class="rl-col rl-col--%s">', esc_attr( sanitize_html_class( $width ) ) );

			foreach ( $column['components'] ?? array() as $entry ) {
				if ( empty( $entry['component'] ) ) {
					continue;
				}

				echo Components::render( $entry['component'], $entry['atts'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the component template.
			}

			echo '</div>';
		}

		echo '</div>';
	}
}
