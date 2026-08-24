<?php
/**
 * Guardrail tests.
 *
 * Runs without WordPress: the handful of WP functions the tested classes touch
 * are stubbed below. Verifies the rules that must not silently break —
 * placeholder handling, forbidden schema stripping, restricted-term scanning,
 * the testimonial consent gate, and token compilation.
 *
 * Usage, from the theme root:
 *
 *     php tests/guardrails-test.php
 *
 * @package RobertsLaw
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'ROBERTSLAW_DIR', dirname( __DIR__ ) . '/' );
define( 'ROBERTSLAW_URI', 'https://example.test/theme/' );
define( 'ROBERTSLAW_VERSION', 'test' );

// --- Minimal WordPress stubs -------------------------------------------------

$GLOBALS['rl_test_meta'] = array();

function apply_filters( $hook, $value ) { return $value; }
function add_action() {}
function add_filter() {}
function add_shortcode() {}
function esc_html( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); }
function esc_attr( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); }
function esc_attr__( $t ) { return $t; }
function __( $t ) { return $t; }
function current_user_can() { return false; }
function wp_strip_all_tags( $t ) { return strip_tags( (string) $t ); }
function get_post_meta( $id, $key ) {
	return $GLOBALS['rl_test_meta'][ $id ][ $key ] ?? '';
}

require_once ROBERTSLAW_DIR . 'inc/class-config.php';
require_once ROBERTSLAW_DIR . 'inc/class-compliance.php';
require_once ROBERTSLAW_DIR . 'inc/class-tokens.php';
require_once ROBERTSLAW_DIR . 'inc/class-components.php';

use RobertsLaw\Compliance;
use RobertsLaw\Components;
use RobertsLaw\Config;
use RobertsLaw\Tokens;

// --- Tiny assertion harness --------------------------------------------------

$passed = 0;
$failed = 0;

/**
 * Assert a condition.
 *
 * @param string $name      Test name.
 * @param bool   $condition Result.
 * @param string $detail    Shown on failure.
 * @return void
 */
function check( $name, $condition, $detail = '' ) {
	global $passed, $failed;

	if ( $condition ) {
		$passed++;
		echo "  ok    {$name}\n";
		return;
	}

	$failed++;
	echo "  FAIL  {$name}" . ( $detail ? "  ({$detail})" : '' ) . "\n";
}

echo "\nPlaceholders\n";

check( 'detects [STREET_ADDRESS]', Compliance::is_placeholder( '[STREET_ADDRESS]' ) );
check( 'detects [OFFICE_HOURS]', Compliance::is_placeholder( '[OFFICE_HOURS]' ) );
check( 'a real address is not a placeholder', ! Compliance::is_placeholder( '100 Main St' ) );
check( 'lowercase brackets are not a placeholder', ! Compliance::is_placeholder( '[not a token]' ) );
check( 'resolve() empties a placeholder', '' === Compliance::resolve( '[POSTAL_CODE]' ) );
check( 'resolve() passes a real value through', '38103' === Compliance::resolve( '38103' ) );
check(
	'firm config still carries the four blocked values',
	4 === count( Compliance::unresolved_placeholders() ),
	'got ' . implode( ', ', array_keys( Compliance::unresolved_placeholders() ) )
);

echo "\nForbidden schema (CLAUDE.md rule 4)\n";

$graph = array(
	'@context' => 'https://schema.org',
	'@graph'   => array(
		array( '@type' => 'LegalService', 'name' => 'Firm' ),
		array( '@type' => 'Review', 'reviewBody' => 'Great lawyer' ),
		array(
			'@type'           => 'Attorney',
			'name'            => 'Joni K. Roberts',
			'aggregateRating' => array( '@type' => 'AggregateRating', 'ratingValue' => '5' ),
			'review'          => array( array( '@type' => 'Review' ) ),
		),
	),
);

$clean  = Compliance::strip_forbidden_schema( $graph );
$json   = json_encode( $clean );
$types  = array_column( $clean['@graph'], '@type' );

check( 'Review node removed', ! in_array( 'Review', $types, true ), $json );
check( 'AggregateRating removed', false === strpos( $json, 'AggregateRating' ), $json );
check( 'ratingValue removed', false === strpos( $json, 'ratingValue' ), $json );
check( 'review property removed', false === strpos( $json, '"review"' ), $json );
check( 'LegalService survives', in_array( 'LegalService', $types, true ), $json );
check( 'Attorney survives', in_array( 'Attorney', $types, true ), $json );

echo "\nRestricted language (RPC 7.1 / 7.4)\n";

$terms = static function ( $text ) {
	return array_column( Compliance::scan_text( $text ), 'term' );
};

check( 'flags "expertise"', in_array( 'expertise', $terms( 'Our expertise in family law' ), true ) );
check( 'flags "specializes"', in_array( 'specializes', $terms( 'She specializes in mediation' ), true ) );
check( 'flags "specialize"', in_array( 'specialize', $terms( 'We specialize in probate' ), true ) );
check( 'flags "expert"', in_array( 'expert', $terms( 'an expert attorney' ), true ) );
check( 'flags "proven results"', in_array( 'proven results', $terms( 'Proven results for our clients' ), true ) );
check( 'flags "fighting for you"', in_array( 'fighting for you', $terms( 'Fighting for you every day' ), true ) );
check( 'approved wording is clean', array() === $terms( 'Joni K. Roberts handles divorce and practices in Shelby County.' ) );
// Word-boundary matched, so "expertly" must not trip the "expert" rule.
check( 'matches whole words only', ! in_array( 'expert', $terms( 'The brief was expertly bound.' ), true ) );

echo "\nTestimonial consent gate (CLAUDE.md rule 3)\n";

$GLOBALS['rl_test_meta'] = array(
	1 => array(
		'_rl_consent_on_file'        => '1',
		'_rl_final_wording_approved' => '1',
		'_rl_matter_concluded'       => '1',
		'_rl_consent_date'           => '2026-01-15',
	),
	2 => array(
		'_rl_consent_on_file'        => '1',
		'_rl_final_wording_approved' => '',
		'_rl_matter_concluded'       => '1',
		'_rl_consent_date'           => '2026-01-15',
	),
	3 => array(
		'_rl_consent_on_file'        => '1',
		'_rl_final_wording_approved' => '1',
		'_rl_matter_concluded'       => '1',
		'_rl_consent_date'           => '2026-01-15',
		'_rl_served_as_neutral'      => '1',
	),
	4 => array(),
);

check( 'fully consented entry renders', Compliance::testimonial_cleared( 1 ) );
check( 'unapproved wording blocks', ! Compliance::testimonial_cleared( 2 ) );
check( 'served-as-neutral blocks', ! Compliance::testimonial_cleared( 3 ) );
check( 'empty entry blocks', ! Compliance::testimonial_cleared( 4 ) );

echo "\nSensitive pages\n";

check( 'orders-of-protection blocks testimonials', ! Compliance::testimonials_allowed( 'orders-of-protection' ) );
check( 'divorce allows testimonials', Compliance::testimonials_allowed( 'divorce' ) );

echo "\nDisclaimer\n";

$disclaimer = Compliance::disclaimer( 'default' );

check( 'sitewide disclaimer is present', strlen( $disclaimer ) > 100 );
check(
	'wording is verbatim',
	0 === strpos( $disclaimer, 'The information on this website is for general information purposes only.' )
);
check( 'form variant differs', Compliance::disclaimer( 'form' ) !== $disclaimer );
check( 'testimonial variant differs', Compliance::disclaimer( 'testimonial' ) !== $disclaimer );

echo "\nTokens\n";

$css = Tokens::css();

check( 'compiles a :root block', 0 === strpos( $css, ':root{' ) );
check( 'emits --rl-color-ink', false !== strpos( $css, '--rl-color-ink: #16283F;' ) );
check( 'emits --rl-space-lg', false !== strpos( $css, '--rl-space-lg: 2rem;' ) );
check( 'emits a clamp() value intact', false !== strpos( $css, 'clamp(' ) );
check( 'var_ref builds a reference', 'var(--rl-color-accent)' === Tokens::var_ref( 'color', 'accent' ) );

echo "\nPage blueprints\n";

$blueprints = Config::load( 'page-templates' );
$pages      = Config::load( 'pages' );
$known      = array_keys( Components::all() );

check( 'blueprints load', count( $blueprints ) > 0, count( $blueprints ) . ' found' );

$bad_pages      = array();
$bad_components = array();
$missing_tpl    = array();
$unresolved     = array();

foreach ( $blueprints as $key => $blueprint ) {
	if ( ! isset( $pages[ $key ] ) ) {
		$bad_pages[] = $key;
	}

	foreach ( $blueprint['sections'] as $section ) {
		foreach ( $section['rows'] as $row ) {
			foreach ( $row['columns'] as $column ) {
				foreach ( $column['components'] as $entry ) {
					$slug = $entry['component'];

					if ( ! in_array( $slug, $known, true ) ) {
						$bad_components[] = "{$key}:{$slug}";
						continue;
					}

					if ( ! file_exists( ROBERTSLAW_DIR . "components/{$slug}/template.php" ) ) {
						$missing_tpl[] = $slug;
					}

					// Every declared att must be a real field on that component.
					$fields = array_keys( Components::get( $slug )['fields'] ?? array() );

					foreach ( array_keys( $entry['atts'] ?? array() ) as $att ) {
						if ( ! in_array( $att, $fields, true ) ) {
							$bad_components[] = "{$key}:{$slug}.{$att}";
						}
					}

					// Track slots still carrying an unresolved marker.
					$content = (string) ( $entry['atts']['content'] ?? '' );

					if ( preg_match( '/\[(NEEDS ATTORNEY INPUT|BLOCKED|FORM|MAP EMBED)/', $content ) ) {
						$unresolved[ $key ] = true;
					}
				}
			}
		}
	}
}

check( 'every blueprint maps to a page in the inventory', empty( $bad_pages ), implode( ', ', $bad_pages ) );
check( 'every component reference is real', empty( $bad_components ), implode( ', ', array_unique( $bad_components ) ) );
check( 'every referenced component has a template', empty( $missing_tpl ), implode( ', ', array_unique( $missing_tpl ) ) );

// Not a failure — a report. These are the pages that must not publish yet.
echo '  note  ' . count( $unresolved ) . " page(s) carry unresolved copy slots: "
	. implode( ', ', array_keys( $unresolved ) ) . "\n";

// A page with unresolved slots must be flagged so Build Status can catch it.
$unflagged = array();

foreach ( array_keys( $unresolved ) as $key ) {
	$blueprint = $blueprints[ $key ];

	if ( empty( $blueprint['review_required'] ) && empty( $blueprint['blocked'] ) ) {
		$unflagged[] = $key;
	}
}

check(
	'pages with unresolved slots are flagged review_required or blocked',
	empty( $unflagged ),
	implode( ', ', $unflagged )
);

// The H1 belongs to the hero. A blueprint must never hardcode one in prose.
$h1_in_prose = array();

foreach ( $blueprints as $key => $blueprint ) {
	foreach ( $blueprint['sections'] as $section ) {
		foreach ( $section['rows'] as $row ) {
			foreach ( $row['columns'] as $column ) {
				foreach ( $column['components'] as $entry ) {
					if ( false !== stripos( (string) ( $entry['atts']['content'] ?? '' ), '<h1' ) ) {
						$h1_in_prose[] = $key;
					}
				}
			}
		}
	}
}

check( 'no blueprint hardcodes an H1 in prose', empty( $h1_in_prose ), implode( ', ', $h1_in_prose ) );

// Restricted language must not have crept into any drafted copy.
$flagged_copy = array();

foreach ( $blueprints as $key => $blueprint ) {
	foreach ( $blueprint['sections'] as $section ) {
		foreach ( $section['rows'] as $row ) {
			foreach ( $row['columns'] as $column ) {
				foreach ( $column['components'] as $entry ) {
					foreach ( $entry['atts'] ?? array() as $value ) {
						if ( ! is_string( $value ) ) {
							continue;
						}

						foreach ( Compliance::scan_text( $value ) as $finding ) {
							$flagged_copy[] = "{$key}: {$finding['term']}";
						}
					}
				}
			}
		}
	}
}

check(
	'no restricted language in any blueprint copy',
	empty( $flagged_copy ),
	implode( '; ', array_unique( $flagged_copy ) )
);

// The sensitive page must lead with safety.
$oop   = $blueprints['orders-of-protection'] ?? array();
$first = $oop['sections'][0]['rows'][0]['columns'][0]['components'][0]['component'] ?? '';

check( 'orders-of-protection leads with the safety block', 'safety-exit' === $first, "got '{$first}'" );

$has_testimonials = false;

foreach ( $oop['sections'] ?? array() as $section ) {
	foreach ( $section['rows'] as $row ) {
		foreach ( $row['columns'] as $column ) {
			foreach ( $column['components'] as $entry ) {
				if ( 'testimonials' === $entry['component'] ) {
					$has_testimonials = true;
				}
			}
		}
	}
}

check( 'orders-of-protection has no testimonials', ! $has_testimonials );

// No blueprint anywhere ships a testimonial, since none are consented yet.
$testimonial_pages = array();

foreach ( $blueprints as $key => $blueprint ) {
	foreach ( $blueprint['sections'] as $section ) {
		foreach ( $section['rows'] as $row ) {
			foreach ( $row['columns'] as $column ) {
				foreach ( $column['components'] as $entry ) {
					if ( 'testimonials' === $entry['component'] ) {
						$testimonial_pages[] = $key;
					}
				}
			}
		}
	}
}

check( 'no blueprint places testimonials yet', empty( $testimonial_pages ), implode( ', ', $testimonial_pages ) );

echo "\n" . str_repeat( '-', 46 ) . "\n";
printf( "%d passed, %d failed\n\n", $passed, $failed );

exit( $failed > 0 ? 1 : 0 );
