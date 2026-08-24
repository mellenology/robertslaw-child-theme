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

use RobertsLaw\Compliance;
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

echo "\n" . str_repeat( '-', 46 ) . "\n";
printf( "%d passed, %d failed\n\n", $passed, $failed );

exit( $failed > 0 ? 1 : 0 );
