<?php
/**
 * Generates the Cornerstone element stub files for every component.
 *
 * Usage, from the theme root:
 *
 *     php bin/generate-elements.php
 *
 * Cornerstone's element API wants a directory per element containing
 * definition.php, controls.php, defaults.php, and shortcode.php. Those four
 * files are identical for every component apart from the slug, so they are
 * generated from config/components.php rather than maintained by hand.
 *
 * The generated files contain no configuration. They delegate to
 * RobertsLaw\Components, which reads config/components.php at runtime — so
 * editing a component's fields does NOT require regenerating. Regenerate only
 * when you add or remove a component.
 *
 * @package RobertsLaw
 */

// The config files guard on ABSPATH; this script is not running inside
// WordPress, so satisfy the guard before requiring them.
define( 'ABSPATH', __DIR__ );

$root       = dirname( __DIR__ );
$components = require $root . '/config/components.php';

if ( ! is_array( $components ) || empty( $components ) ) {
	fwrite( STDERR, "No components found in config/components.php\n" );
	exit( 1 );
}

/**
 * Mirror of RobertsLaw\Cornerstone\class_name(). The two must agree.
 *
 * @param string $slug Component slug.
 * @return string
 */
function rl_element_class_name( $slug ) {
	$parts = array_map(
		static function ( $part ) {
			return ucfirst( preg_replace( '/[^a-z0-9]/', '', strtolower( $part ) ) );
		},
		explode( '-', $slug )
	);

	return 'RL_Element_' . implode( '_', $parts );
}

$banner = <<<'BANNER'
<?php
/**
 * GENERATED FILE — do not edit.
 *
 * Regenerate with:  php bin/generate-elements.php
 * Source of truth:  config/components.php
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

BANNER;

$written = 0;
$skipped = 0;

foreach ( array_keys( $components ) as $slug ) {
	$dir = $root . '/components/' . $slug;

	if ( ! is_dir( $dir ) && ! mkdir( $dir, 0755, true ) && ! is_dir( $dir ) ) {
		fwrite( STDERR, "Could not create {$dir}\n" );
		exit( 1 );
	}

	$class = rl_element_class_name( $slug );

	$files = array(
		'definition.php' => $banner . "\n"
			. "class {$class} {\n\n"
			. "\t/**\n"
			. "\t * Builder metadata for this element.\n"
			. "\t *\n"
			. "\t * @return array\n"
			. "\t */\n"
			. "\tpublic function ui() {\n"
			. "\t\treturn \\RobertsLaw\\Cornerstone\\ui( '{$slug}' );\n"
			. "\t}\n"
			. "}\n",

		'controls.php'   => $banner . "\n"
			. "return \\RobertsLaw\\Components::controls( '{$slug}' );\n",

		'defaults.php'   => $banner . "\n"
			. "return \\RobertsLaw\\Components::defaults( '{$slug}' );\n",

		// Cornerstone extracts the element's attributes into this file's scope,
		// so get_defined_vars() hands the whole attribute set to the one render
		// path. Components::render() drops anything that is not a declared
		// field for this component.
		'shortcode.php'  => $banner . "\n"
			. "echo \\RobertsLaw\\Components::render( '{$slug}', get_defined_vars() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the component template.\n",
	);

	foreach ( $files as $name => $contents ) {
		$path = $dir . '/' . $name;

		if ( file_exists( $path ) && file_get_contents( $path ) === $contents ) {
			$skipped++;
			continue;
		}

		file_put_contents( $path, $contents );
		$written++;
	}

	// A component with no template renders nothing; leave a stub so the
	// directory is obviously incomplete rather than silently empty.
	$template = $dir . '/template.php';

	if ( ! file_exists( $template ) ) {
		file_put_contents(
			$template,
			"<?php\n/**\n * TODO: markup for the \"{$slug}\" component.\n *\n * @package RobertsLaw\n */\n\ndefined( 'ABSPATH' ) || exit;\n"
		);
		$written++;
		fwrite( STDOUT, "  stub template created for {$slug}\n" );
	}
}

fwrite( STDOUT, sprintf( "Done. %d file(s) written, %d unchanged.\n", $written, $skipped ) );
