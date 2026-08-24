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
 * NOT IMPLEMENTED. See the header of this file — this needs one sample export
 * to write correctly, and guessing produces files that fail silently on import.
 *
 * @param array $tree Normalised tree.
 * @return string|null Encoded .tco contents, or null while unimplemented.
 */
function rl_encode_tco( array $tree ) {
	return null;
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

	if ( null !== $tco ) {
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
