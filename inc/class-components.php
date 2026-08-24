<?php
/**
 * Component engine.
 *
 * One definition in config/components.php produces three consumers — a
 * Cornerstone element, a shortcode, and a PHP call — and all three funnel
 * through render(), which loads the one template file for that component.
 *
 * That is the whole reason this layer exists: there is no second copy of a
 * component's markup living inside a Pro element, so changing a component
 * changes it everywhere it appears.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Components {

	/**
	 * Slugs rendered at least once this request, so their CSS can be printed.
	 *
	 * @var array<string, bool>
	 */
	private static $rendered = array();

	/**
	 * Hook up shortcodes.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
	}

	/**
	 * The full registry.
	 *
	 * @return array
	 */
	public static function all() {
		return Config::load( 'components' );
	}

	/**
	 * One component definition.
	 *
	 * @param string $slug Component slug.
	 * @return array Empty if unknown.
	 */
	public static function get( $slug ) {
		return Config::get( 'components.' . $slug, array() );
	}

	/**
	 * Shortcode tag for a component: 'cta-band' => 'rl_cta_band'.
	 *
	 * @param string $slug Component slug.
	 * @return string
	 */
	public static function shortcode_tag( $slug ) {
		return 'rl_' . str_replace( '-', '_', $slug );
	}

	/**
	 * Cornerstone element name for a component: 'cta-band' => 'rl-cta-band'.
	 *
	 * @param string $slug Component slug.
	 * @return string
	 */
	public static function element_name( $slug ) {
		return 'rl-' . $slug;
	}

	/**
	 * Register a shortcode per component.
	 *
	 * @return void
	 */
	public static function register_shortcodes() {
		foreach ( array_keys( self::all() ) as $slug ) {
			add_shortcode(
				self::shortcode_tag( $slug ),
				static function ( $atts, $content = '' ) use ( $slug ) {
					$atts = is_array( $atts ) ? $atts : array();

					if ( '' !== $content ) {
						$atts['content'] = $content;
					}

					return self::render( $slug, $atts );
				}
			);
		}
	}

	/**
	 * Default attribute values for a component.
	 *
	 * Includes the id/class/style attributes every Cornerstone element carries,
	 * so the same array can serve as the element's defaults.php return value.
	 *
	 * @param string $slug Component slug.
	 * @return array
	 */
	public static function defaults( $slug ) {
		$component = self::get( $slug );

		$defaults = array(
			'id'    => '',
			'class' => '',
			'style' => '',
		);

		foreach ( $component['fields'] ?? array() as $key => $field ) {
			$default = $field['default'] ?? '';

			// Cornerstone stores everything as strings.
			if ( is_bool( $default ) ) {
				$default = $default ? 'true' : 'false';
			}

			$defaults[ $key ] = (string) $default;
		}

		return $defaults;
	}

	/**
	 * Cornerstone controls array for a component, generated from its fields.
	 *
	 * NOTE ON THE CONTROL SCHEMA
	 * theme.co is blocked by this environment's egress policy, so this mapping
	 * was written against the published Element API shape rather than verified
	 * page by page against Themeco's current reference. Every control type is
	 * produced here and only here — if Pro's schema differs on a type, correct
	 * it in this one method and every component picks up the fix.
	 *
	 * @param string $slug Component slug.
	 * @return array
	 */
	public static function controls( $slug ) {
		$component = self::get( $slug );
		$controls  = array();

		foreach ( $component['fields'] ?? array() as $key => $field ) {
			$control = array(
				'type' => self::control_type( $field['type'] ?? 'text' ),
				'ui'   => array(
					'title' => $field['label'] ?? $key,
				),
			);

			if ( ! empty( $field['tooltip'] ) ) {
				$control['ui']['tooltip'] = $field['tooltip'];
			}

			if ( 'select' === ( $field['type'] ?? '' ) && ! empty( $field['options'] ) ) {
				$choices = array();

				foreach ( $field['options'] as $value => $label ) {
					$choices[] = array(
						'value' => (string) $value,
						'label' => $label,
					);
				}

				$control['options'] = array( 'choices' => $choices );
			}

			$controls[ $key ] = $control;
		}

		return $controls;
	}

	/**
	 * Map a registry field type to a Cornerstone control type.
	 *
	 * @param string $type Registry type.
	 * @return string
	 */
	private static function control_type( $type ) {
		$map = array(
			'text'        => 'text',
			'textarea'    => 'textarea',
			'rich-text'   => 'textarea',
			'color'       => 'color',
			'icon-choose' => 'icon-choose',
			'toggle'      => 'toggle',
			'number'      => 'number',
			'select'      => 'select',
			'image'       => 'image',
		);

		return $map[ $type ] ?? 'text';
	}

	/**
	 * Render a component.
	 *
	 * The single render path. Cornerstone elements, shortcodes, and template
	 * calls all land here.
	 *
	 * @param string $slug Component slug.
	 * @param array  $atts Attributes; missing keys fall back to defaults.
	 * @return string HTML.
	 */
	public static function render( $slug, $atts = array() ) {
		$component = self::get( $slug );

		if ( empty( $component ) ) {
			return '';
		}

		$template = ROBERTSLAW_DIR . 'components/' . $slug . '/template.php';

		if ( ! file_exists( $template ) ) {
			return '';
		}

		$atts = self::normalize( $slug, is_array( $atts ) ? $atts : array() );

		/**
		 * Filter a component's attributes before render.
		 *
		 * @param array  $atts Resolved attributes.
		 * @param string $slug Component slug.
		 */
		$atts = apply_filters( 'robertslaw_component_atts', $atts, $slug );

		/**
		 * Short-circuit a component render. Returning a non-null value skips
		 * the template. Used by the compliance layer to suppress testimonials
		 * on sensitive pages.
		 *
		 * @param string|null $output Replacement output.
		 * @param string      $slug   Component slug.
		 * @param array       $atts   Resolved attributes.
		 */
		$pre = apply_filters( 'robertslaw_pre_render_component', null, $slug, $atts );

		if ( null !== $pre ) {
			return (string) $pre;
		}

		self::$rendered[ $slug ] = true;

		ob_start();

		// Available to every template.
		$component_slug = $slug;
		$component_def  = $component;

		include $template;

		return (string) ob_get_clean();
	}

	/**
	 * Merge attributes over defaults and cast booleans.
	 *
	 * Cornerstone hands every value in as a string, including 'true'/'false'
	 * for toggles, so toggles are normalised to real booleans here rather than
	 * in each template.
	 *
	 * @param string $slug Component slug.
	 * @param array  $atts Raw attributes.
	 * @return array
	 */
	private static function normalize( $slug, array $atts ) {
		$component = self::get( $slug );
		$defaults  = self::defaults( $slug );
		$merged    = array_merge( $defaults, array_intersect_key( $atts, $defaults ) );

		// Preserve shortcode inner content, which is not a declared field.
		if ( isset( $atts['content'] ) ) {
			$merged['content'] = $atts['content'];
		}

		foreach ( $component['fields'] ?? array() as $key => $field ) {
			if ( 'toggle' !== ( $field['type'] ?? '' ) ) {
				continue;
			}

			$merged[ $key ] = filter_var( $merged[ $key ], FILTER_VALIDATE_BOOLEAN );
		}

		return $merged;
	}

	/**
	 * Slugs rendered this request.
	 *
	 * @return string[]
	 */
	public static function rendered() {
		return array_keys( self::$rendered );
	}

	/**
	 * Build the class attribute for a component root element.
	 *
	 * @param string $slug  Component slug.
	 * @param array  $atts  Resolved attributes.
	 * @param array  $extra Extra classes.
	 * @return string
	 */
	public static function root_class( $slug, array $atts, array $extra = array() ) {
		$classes = array_merge( array( 'rl-' . $slug ), $extra );

		if ( ! empty( $atts['variant'] ) ) {
			$classes[] = 'rl-' . $slug . '--' . sanitize_html_class( $atts['variant'] );
		}

		if ( ! empty( $atts['class'] ) ) {
			$classes[] = $atts['class'];
		}

		return esc_attr( trim( implode( ' ', array_filter( $classes ) ) ) );
	}
}
