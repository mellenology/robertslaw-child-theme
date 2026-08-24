<?php
require __DIR__ . '/wp-stubs.php';

$fail = 0;
function ok( $cond, $label ) {
	global $fail;
	if ( $cond ) { echo "  PASS  $label\n"; }
	else { echo "  FAIL  $label\n"; $GLOBALS['fail'] = 1; }
}

// WooCommerce absent for the first boot.
require __DIR__ . '/../mu-plugins/mellenade-performance.php';

echo "\n== Boot ==\n";
ok( class_exists( 'Mellenade_Perf_Cleanup' ), 'cleanup module loaded' );
ok( class_exists( 'Mellenade_Perf_Assets' ), 'assets module loaded' );
ok( class_exists( 'Mellenade_Perf_SEO' ), 'seo module loaded' );
ok( ! empty( $GLOBALS['__hooks']['wp_head'] ), 'wp_head hook registered' );

echo "\n== Heartbeat throttle ==\n";
$hb = Mellenade_Perf_Cleanup::throttle_heartbeat( array() );
ok( 60 === $hb['interval'], 'interval set to 60s' );
$hb2 = Mellenade_Perf_Cleanup::throttle_heartbeat( 'not-an-array' );
ok( is_array( $hb2 ) && 60 === $hb2['interval'], 'non-array input handled without fatal' );

echo "\n== TinyMCE emoji removal ==\n";
ok( ! in_array( 'wpemoji', Mellenade_Perf_Cleanup::remove_tinymce_emoji( array( 'wpemoji', 'lists' ) ), true ), 'wpemoji stripped' );
ok( array( 'lists' ) === array_values( Mellenade_Perf_Cleanup::remove_tinymce_emoji( array( 'wpemoji', 'lists' ) ) ), 'other plugins preserved' );
ok( is_array( Mellenade_Perf_Cleanup::remove_tinymce_emoji( 'garbage' ) ), 'bad input handled' );

echo "\n== Image attributes / LCP ==\n";
$GLOBALS['__state']['is_product'] = false;
$a = Mellenade_Perf_Assets::image_attributes( array(), null, 'full' );
ok( 'async' === $a['decoding'], 'decoding=async added' );
ok( ! isset( $a['fetchpriority'] ), 'no fetchpriority off product pages' );

echo "\n== SEO robots ==\n";
$GLOBALS['__state']['is_search'] = true;
$r = Mellenade_Perf_SEO::robots( array( 'index' => true ) );
ok( ! empty( $r['noindex'] ), 'search noindexed' );
ok( ! isset( $r['index'] ), 'conflicting index directive removed' );
$GLOBALS['__state']['is_search'] = false;
$r2 = Mellenade_Perf_SEO::robots( array( 'index' => true ) );
ok( empty( $r2['noindex'] ), 'normal pages stay indexable' );

echo "\n== SEO schema output ==\n";
$GLOBALS['__state']['front_page'] = true;
ob_start();
Mellenade_Perf_SEO::output_organization_schema();
$out = ob_get_clean();
ok( false !== strpos( $out, 'application/ld+json' ), 'JSON-LD emitted on front page' );
$json = json_decode( trim( str_replace( array( '<script type="application/ld+json">', '</script>' ), '', $out ) ), true );
ok( null !== $json, 'emitted JSON-LD is valid JSON' );
ok( 'Organization' === $json['@graph'][0]['@type'], 'Organization node present' );
ok( 'WebSite' === $json['@graph'][1]['@type'], 'WebSite node present' );
ok( ! isset( $json['@graph'][0]['logo'] ), 'logo omitted when none set' );

echo "\n== Logo node: partial dimensions ==\n";
// Logo present but WP returned no width/height -> keys must be absent, never null.
$GLOBALS['__state']['logo_id']  = 42;
$GLOBALS['__state']['logo_src'] = array( 'https://mellenade.com/logo.png', 0, 0 );
ob_start();
Mellenade_Perf_SEO::output_organization_schema();
$o2 = ob_get_clean();
$j2 = json_decode( trim( str_replace( array( '<script type="application/ld+json">', '</script>' ), '', $o2 ) ), true );
ok( null !== $j2, 'JSON still valid with logo' );
ok( isset( $j2['@graph'][0]['logo']['url'] ), 'logo url present' );
ok( ! array_key_exists( 'width', $j2['@graph'][0]['logo'] ), 'null width omitted, not emitted as null' );
ok( ! array_key_exists( 'height', $j2['@graph'][0]['logo'] ), 'null height omitted' );
ok( false === strpos( $o2, 'null' ), 'no null literal anywhere in JSON-LD' );

$GLOBALS['__state']['logo_src'] = array( 'https://mellenade.com/logo.png', 512, 256 );
ob_start();
Mellenade_Perf_SEO::output_organization_schema();
$j3 = json_decode( trim( str_replace( array( '<script type="application/ld+json">', '</script>' ), '', ob_get_clean() ) ), true );
ok( 512 === $j3['@graph'][0]['logo']['width'], 'real width included when available' );
ok( 256 === $j3['@graph'][0]['logo']['height'], 'real height included when available' );

$GLOBALS['__state']['front_page'] = false;
ob_start();
Mellenade_Perf_SEO::output_organization_schema();
ok( '' === ob_get_clean(), 'no schema off the front page' );

exit( $GLOBALS['fail'] );
