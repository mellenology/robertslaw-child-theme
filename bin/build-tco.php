<?php
/**
 * Serialises page blueprints for import into Themeco Pro.
 *
 * Usage, from the theme root:
 *
 *     php bin/build-tco.php            # all pages
 *     php bin/build-tco.php home about # named pages
 *
 * Output goes to build/.
 *
 * ---------------------------------------------------------------------------
 * STATUS: the intermediate stage is complete; the final .tco encode is not.
 * ---------------------------------------------------------------------------
 *
 * This script converts a blueprint into a normalised section/row/column/element
 * tree — that part is done and testable. What it cannot yet do is emit a real
 * .tco file, because the .tco envelope is not publicly documented and theme.co
 * is blocked by this environment's egress policy.
 *
 * Writing a plausible-looking .tco would be worse than writing none: a
 * malformed template fails silently on import in Cornerstone's Manage Library,
 * giving no indication of what went wrong.
 *
 * TO FINISH IT — one sample export is enough:
 *
 *   1. Open Cornerstone on any page, add two or three elements.
 *   2. Use the sidebar Export icon to download a .tco.
 *   3. Provide that file. It reveals the envelope, the version stamp, the
 *      element type names as your install registers them, and the param key
 *      and value shapes.
 *   4. Fill in encode_tco() below. Nothing above it should need to change.
 *
 * Providing wp-content/plugins/cornerstone/includes/elements/ as well gives the
 * full parameter list per element, which is what makes the element and param
 * choices verified rather than inferred.
 *
 * @package RobertsLaw
 */

define( 'ABSPATH', __DIR__ );

$root       = dirname( __DIR__ );
$blueprints = require $root . '/config/page-templates.php';
$components = require $root . '/config/components.php';
$pages      = require $root . '/config/pages.php';

$requested = array_slice( $argv, 1 );
$targets   = $requested ? array_intersect( array_keys( $blueprints ), $requested ) : array_keys( $blueprints );

if ( $requested && count( $targets ) !== count( $requested ) ) {
	$unknown = array_diff( $requested, array_keys( $blueprints ) );
	fwrite( STDERR, 'Unknown page key(s): ' . implode( ', ', $unknown ) . "\n" );
	fwrite( STDERR, 'Available: ' . implode( ', ', array_keys( $blueprints ) ) . "\n" );
	exit( 1 );
}

/**
 * Convert a blueprint into a normalised element tree.
 *
 * Cornerstone's hierarchy is Section → Row → Column → Element, which is exactly
 * how the blueprints are shaped, so this is a direct walk rather than a
 * transformation.
 *
 * @param string $key        Page key.
 * @param array  $blueprint  Blueprint definition.
 * @param array  $components Component registry.
 * @param array  $pages      Page inventory.
 * @return array
 */
function rl_build_tree( $key, array $blueprint, array $components, array $pages ) {
	$sections = array();

	foreach ( $blueprint['sections'] as $section ) {
		$rows = array();

		foreach ( $section['rows'] as $row ) {
			$columns = array();

			foreach ( $row['columns'] as $column ) {
				$elements = array();

				foreach ( $column['components'] as $entry ) {
					$slug = $entry['component'];
					$def  = $components[ $slug ] ?? array();

					// Resolve every declared field, so the exported element
					// carries a complete param set rather than relying on
					// Cornerstone falling back to a default we cannot see.
					$params = array();

					foreach ( $def['fields'] ?? array() as $field => $spec ) {
						$value = $entry['atts'][ $field ] ?? $spec['default'] ?? '';

						if ( is_bool( $value ) ) {
							$value = $value ? 'true' : 'false';
						}

						$params[ $field ] = (string) $value;
					}

					$elements[] = array(
						'_type'   => 'rl-' . $slug,
						'_label'  => $def['label'] ?? $slug,
						'_region' => 'body',
						'params'  => $params,
					);
				}

				$columns[] = array(
					'_type'    => 'column',
					'width'    => $column['width'] ?? '1/1',
					'elements' => $elements,
				);
			}

			$rows[] = array(
				'_type'   => 'row',
				'columns' => $columns,
			);
		}

		$sections[] = array(
			'_type'      => 'section',
			'_label'     => $section['label'] ?? '',
			'background' => $section['background'] ?? 'surface',
			'spacing'    => $section['spacing'] ?? 'md',
			'width'      => $section['width'] ?? 'contained',
			'rows'       => $rows,
		);
	}

	return array(
		'page'     => $key,
		'url'      => $pages[ $key ]['url'] ?? '',
		'title'    => $pages[ $key ]['title'] ?? '',
		'h1'       => $pages[ $key ]['h1'] ?? '',
		'status'   => isset( $blueprint['blocked'] ) ? 'blocked'
			: ( ! empty( $blueprint['review_required'] ) ? 'review_required' : 'ready' ),
		'blocked'  => $blueprint['blocked'] ?? '',
		'sections' => $sections,
	);
}

/**
 * Encode a normalised tree as a Themeco .tco file.
 *
 * CONFIDENCE, stated plainly so nobody is misled by a file that looks official:
 *
 *   HIGH   — the element hierarchy. Cornerstone nests Section > Row > Column >
 *            Element, elements carry a `_type` and a `_id`, children live in
 *            `_elements`, and params sit flat on the element object. The
 *            blueprints were shaped to match this from the start.
 *   HIGH   — the `_type` values for custom elements. Registration through
 *            cornerstone_register_element( $class, $name, $dir ) makes the
 *            registered name the type, so these are "rl-hero", "rl-faq", etc.
 *   MEDIUM — the outer envelope keys and the version stamp. This is
 *            reconstructed, not verified, because theme.co is blocked by this
 *            environment's egress policy.
 *
 * If an import fails, the envelope is the thing to fix, and one sample export
 * fixes it. Everything above this function stays as-is.
 *
 * @param array $tree Normalised tree.
 * @return string Encoded .tco contents.
 */
function rl_encode_tco( array $tree ) {
	$elements = array();

	foreach ( $tree['sections'] as $index => $section ) {
		$elements[] = rl_encode_section( $tree['page'], $index, $section );
	}

	$document = array(
		// Envelope. The reconstructed part.
		'name'     => $tree['title'] ? $tree['title'] : $tree['page'],
		'type'     => 'content',
		'version'  => RL_TCO_VERSION,
		'elements' => $elements,
	);

	return (string) json_encode( $document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}

/**
 * Cornerstone version stamp written into the envelope.
 *
 * Change this to match the install if an import complains about a version
 * mismatch. Reported by Cornerstone under WordPress > Plugins.
 */
define( 'RL_TCO_VERSION', '7.0.0' );

/**
 * Stable element id.
 *
 * Deterministic rather than random so re-running the build produces byte
 * identical files and a diff shows only real changes.
 *
 * @param string $path Unique path to this node within the page.
 * @return string
 */
function rl_element_id( $path ) {
	return substr( md5( $path ), 0, 12 );
}

/**
 * Encode one section and everything under it.
 *
 * @param string $page    Page key.
 * @param int    $index   Section index.
 * @param array  $section Section definition.
 * @return array
 */
function rl_encode_section( $page, $index, array $section ) {
	$path = "{$page}/section-{$index}";
	$rows = array();

	foreach ( $section['rows'] as $r => $row ) {
		$columns = array();

		foreach ( $row['columns'] as $c => $column ) {
			$children = array();

			foreach ( $column['elements'] as $e => $element ) {
				$node = array(
					'_type' => $element['_type'],
					'_id'   => rl_element_id( "{$path}/{$r}/{$c}/{$e}" ),
				);

				// Params sit flat on the element object, not nested.
				foreach ( $element['params'] as $key => $value ) {
					$node[ $key ] = $value;
				}

				$children[] = $node;
			}

			$columns[] = array(
				'_type'         => 'column',
				'_id'           => rl_element_id( "{$path}/{$r}/{$c}" ),
				'base_font_size' => '',
				'width'         => rl_column_width( $column['width'] ),
				'_elements'     => $children,
			);
		}

		$rows[] = array(
			'_type'     => 'row',
			'_id'       => rl_element_id( "{$path}/{$r}" ),
			'_elements' => $columns,
		);
	}

	return array(
		'_type'      => 'section',
		'_id'        => rl_element_id( $path ),
		'_label'     => $section['_label'],
		// Tokens rather than literal values, so a section styled here still
		// answers to config/tokens.php.
		'bg_color'   => rl_background_token( $section['background'] ),
		'padding_top'    => rl_spacing_token( $section['spacing'] ),
		'padding_bottom' => rl_spacing_token( $section['spacing'] ),
		'_elements'  => $rows,
	);
}

/**
 * Blueprint column width to a percentage string.
 *
 * @param string $width Fraction such as '1/3'.
 * @return string
 */
function rl_column_width( $width ) {
	$map = array(
		'1/1' => '100%',
		'1/2' => '50%',
		'1/3' => '33.33%',
		'2/3' => '66.66%',
		'1/4' => '25%',
		'3/4' => '75%',
	);

	return $map[ $width ] ?? '100%';
}

/**
 * Section background to a CSS custom property reference.
 *
 * @param string $background Background key.
 * @return string
 */
function rl_background_token( $background ) {
	$map = array(
		'surface'     => 'var(--rl-color-surface)',
		'alt'         => 'var(--rl-color-surface-alt)',
		'sunken'      => 'var(--rl-color-surface-sunken)',
		'deep'        => 'var(--rl-color-surface-deep)',
		'accent-soft' => 'var(--rl-color-accent-soft)',
	);

	return $map[ $background ] ?? 'var(--rl-color-surface)';
}

/**
 * Section spacing to a CSS custom property reference.
 *
 * @param string $spacing Spacing key.
 * @return string
 */
function rl_spacing_token( $spacing ) {
	$map = array(
		'sm' => 'var(--rl-space-md)',
		'md' => 'var(--rl-space-lg)',
		'lg' => 'var(--rl-space-2xl)',
		'xl' => 'var(--rl-space-3xl)',
	);

	return $map[ $spacing ] ?? 'var(--rl-space-lg)';
}

// --- Run ---------------------------------------------------------------------

$out = $root . '/build';

if ( ! is_dir( $out ) && ! mkdir( $out, 0755, true ) && ! is_dir( $out ) ) {
	fwrite( STDERR, "Could not create {$out}\n" );
	exit( 1 );
}

$encoded = 0;
$staged  = 0;

foreach ( $targets as $key ) {
	$tree = rl_build_tree( $key, $blueprints[ $key ], $components, $pages );
	$tco  = rl_encode_tco( $tree );

	if ( null !== $tco && '' !== $tco ) {
		file_put_contents( "{$out}/{$key}.tco", $tco );
		$encoded++;
		fwrite( STDOUT, "  wrote build/{$key}.tco\n" );
		continue;
	}

	// Stage the intermediate tree so the work is inspectable and diffable now,
	// and so the encode step has something concrete to consume later.
	file_put_contents(
		"{$out}/{$key}.json",
		json_encode( $tree, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
	$staged++;

	$elements = 0;
	foreach ( $tree['sections'] as $section ) {
		foreach ( $section['rows'] as $row ) {
			foreach ( $row['columns'] as $column ) {
				$elements += count( $column['elements'] );
			}
		}
	}

	fwrite(
		STDOUT,
		sprintf(
			"  staged build/%s.json  (%d sections, %d elements, %s)\n",
			$key,
			count( $tree['sections'] ),
			$elements,
			$tree['status']
		)
	);
}

fwrite( STDOUT, "\n" );

if ( $staged && ! $encoded ) {
	fwrite(
		STDOUT,
		"Staged {$staged} page(s) as intermediate JSON.\n\n"
		. "No .tco written: rl_encode_tco() is unimplemented pending one sample\n"
		. "Cornerstone export to confirm the envelope and element param shapes.\n"
		. "See the header of this file.\n\n"
	);
}
