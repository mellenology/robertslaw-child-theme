<?php
/**
 * Config registry. Loads and caches the /config files and exposes dot-path
 * access to them.
 *
 * Everything in the theme reads its values through here, so there is exactly
 * one place a value can come from and exactly one place to change it.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Config {

	/**
	 * Loaded config files, keyed by name.
	 *
	 * @var array<string, array>
	 */
	private static $loaded = array();

	/**
	 * Read a value by dot path, e.g. Config::get( 'firm.phone_display' ).
	 *
	 * The first path segment is the config filename. Later segments walk into
	 * the returned array.
	 *
	 * @param string $path    Dot-separated path.
	 * @param mixed  $default Returned when the path does not resolve.
	 * @return mixed
	 */
	public static function get( $path, $default = null ) {
		$segments = explode( '.', $path );
		$file     = array_shift( $segments );
		$value    = self::load( $file );

		foreach ( $segments as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}
			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Load one config file, once.
	 *
	 * Each file is filterable so a future plugin or a staging environment can
	 * override values without editing the file. The filter name is
	 * `robertslaw_config_{$name}`.
	 *
	 * @param string $name Filename without extension.
	 * @return array
	 */
	public static function load( $name ) {
		if ( isset( self::$loaded[ $name ] ) ) {
			return self::$loaded[ $name ];
		}

		$path = ROBERTSLAW_DIR . 'config/' . $name . '.php';

		if ( ! file_exists( $path ) ) {
			self::$loaded[ $name ] = array();
			return self::$loaded[ $name ];
		}

		$data = require $path;

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		/**
		 * Filter a loaded config array.
		 *
		 * @param array  $data Config contents.
		 * @param string $name Config file name.
		 */
		self::$loaded[ $name ] = apply_filters( "robertslaw_config_{$name}", $data, $name );

		return self::$loaded[ $name ];
	}

	/**
	 * Clear the in-memory cache. Used by tests and by the admin settings screen
	 * after a save.
	 *
	 * @return void
	 */
	public static function flush() {
		self::$loaded = array();
	}
}
