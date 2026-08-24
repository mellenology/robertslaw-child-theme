<?php
/**
 * Design token compiler.
 *
 * Turns config/tokens.php into CSS custom properties on :root, printed inline
 * in <head>. Inline rather than an enqueued file because the token block is
 * roughly 2KB and every above-the-fold rule depends on it — a separate request
 * on the critical path would cost more than it saves, and the LCP target in
 * CLAUDE.md is 2.5s.
 *
 * The same block is printed inside the Cornerstone builder, so tokens
 * referenced in a Pro element's style field resolve while editing, not just on
 * the front end.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Tokens {

	/**
	 * Compiled CSS, memoised per request.
	 *
	 * @var string|null
	 */
	private static $css = null;

	/**
	 * Hook up output.
	 *
	 * @return void
	 */
	public static function init() {
		// Priority 1: tokens must precede every stylesheet that consumes them.
		add_action( 'wp_head', array( __CLASS__, 'print_tokens' ), 1 );

		// Cornerstone renders the builder preview in an iframe that loads the
		// front end, but the builder chrome itself is admin-side.
		add_action( 'admin_head', array( __CLASS__, 'print_tokens' ), 1 );
	}

	/**
	 * Print the token block.
	 *
	 * @return void
	 */
	public static function print_tokens() {
		echo "<style id=\"rl-tokens\">\n" . self::css() . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- compiled from a sanitised config array below.
	}

	/**
	 * Compile the token array to a :root block.
	 *
	 * @return string
	 */
	public static function css() {
		if ( null !== self::$css ) {
			return self::$css;
		}

		$lines = array();

		foreach ( Config::load( 'tokens' ) as $group => $values ) {
			if ( ! is_array( $values ) ) {
				continue;
			}

			foreach ( $values as $key => $value ) {
				if ( is_array( $value ) ) {
					continue;
				}

				$name = self::property_name( $group, $key );

				if ( '' === $name ) {
					continue;
				}

				$lines[] = sprintf( "\t%s: %s;", $name, self::sanitize_value( $value ) );
			}
		}

		self::$css = ":root{\n" . implode( "\n", $lines ) . "\n}\n";

		return self::$css;
	}

	/**
	 * Build a custom property name from a group and key.
	 *
	 * @param string $group Token group.
	 * @param string $key   Token key.
	 * @return string Empty if either part is unusable.
	 */
	private static function property_name( $group, $key ) {
		$group = preg_replace( '/[^a-z0-9\-]/', '', strtolower( (string) $group ) );
		$key   = preg_replace( '/[^a-z0-9\-]/', '', strtolower( (string) $key ) );

		if ( '' === $group || '' === $key ) {
			return '';
		}

		return '--rl-' . $group . '-' . $key;
	}

	/**
	 * Strip anything that could break out of a declaration.
	 *
	 * Token values are authored in the repo, not user input, but this keeps a
	 * careless edit from producing invalid CSS that silently kills the whole
	 * :root block.
	 *
	 * @param mixed $value Token value.
	 * @return string
	 */
	private static function sanitize_value( $value ) {
		$value = (string) $value;
		$value = str_replace( array( '</style', '<', '>', ';', '{', '}' ), '', $value );

		return trim( $value );
	}

	/**
	 * Read a single token value, for PHP that needs it (inline styles, schema
	 * theme colour, and so on).
	 *
	 * @param string $group   Token group.
	 * @param string $key     Token key.
	 * @param string $default Fallback.
	 * @return string
	 */
	public static function get( $group, $key, $default = '' ) {
		return (string) Config::get( "tokens.{$group}.{$key}", $default );
	}

	/**
	 * The CSS var() reference for a token, for use in inline style attributes.
	 *
	 * @param string $group Token group.
	 * @param string $key   Token key.
	 * @return string
	 */
	public static function var_ref( $group, $key ) {
		$name = self::property_name( $group, $key );

		return $name ? "var({$name})" : '';
	}
}
