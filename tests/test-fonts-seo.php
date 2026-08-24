<?php
require __DIR__ . '/wp-stubs.php';

$fail = 0;
function ok( $c, $l ) {
	if ( $c ) { echo "  PASS  $l\n"; } else { echo "  FAIL  $l\n"; $GLOBALS['fail'] = 1; }
}

require __DIR__ . '/../mu-plugins/mellenade-performance.php';

echo "\n== Fonts module ==\n";
ok( class_exists( 'Mellenade_Perf_Fonts' ), 'fonts module loaded' );

$h = Mellenade_Perf_Fonts::preconnect_gstatic( array(), 'preconnect' );
ok( ! empty( $h ) && 'https://fonts.gstatic.com' === $h[0]['href'], 'gstatic preconnect added' );
ok( 'anonymous' === $h[0]['crossorigin'], 'preconnect is crossorigin (required for fonts)' );

$h2 = Mellenade_Perf_Fonts::preconnect_gstatic( array(), 'dns-prefetch' );
ok( array() === $h2, 'no hint added for non-preconnect relations' );

echo "\n== Metric-matched fallback (CLS fix) ==\n";
ob_start();
Mellenade_Perf_Fonts::print_font_metrics();
$css = ob_get_clean();
ok( false !== strpos( $css, '@font-face' ), 'fallback @font-face emitted' );
ok( false !== strpos( $css, 'size-adjust' ), 'size-adjust present' );
ok( false !== strpos( $css, 'ascent-override' ), 'ascent-override present' );
ok( false !== strpos( $css, 'Poppins Fallback' ), 'targets the Poppins fallback family' );

echo "\n== font-display injection ==\n";
$tag = "<link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Poppins' />";
$out = Mellenade_Perf_Fonts::add_font_display( $tag, 'x', 'https://fonts.googleapis.com/css2?family=Poppins', 'all' );
ok( false !== strpos( $out, 'display=swap' ), 'display=swap appended to Google Fonts' );

$already = 'https://fonts.googleapis.com/css2?family=Poppins&display=optional';
$out2 = Mellenade_Perf_Fonts::add_font_display( "<link href='$already'>", 'x', $already, 'all' );
ok( false === strpos( $out2, 'display=swap' ), 'existing display value is respected, not overwritten' );

$local = 'https://mellenade.com/wp-content/themes/pro/style.css';
$out3 = Mellenade_Perf_Fonts::add_font_display( "<link href='$local'>", 'x', $local, 'all' );
ok( $out3 === "<link href='$local'>", 'non-Google stylesheets untouched' );
ok( Mellenade_Perf_Fonts::add_font_display( '<link>', 'x', null, 'all' ) === '<link>', 'null href handled without fatal' );

echo "\n== Meta description (Lighthouse SEO failure) ==\n";
$GLOBALS['__state']['front_page'] = true;
ob_start();
Mellenade_Perf_SEO::output_meta_description();
$m = ob_get_clean();
ok( false !== strpos( $m, 'name="description"' ), 'meta description emitted on front page' );
ok( false !== strpos( $m, 'Cards and collectibles' ), 'uses the site tagline' );

// Long content must be truncated on a word boundary.
$GLOBALS['__state']['front_page'] = false;
$GLOBALS['__state']['is_singular'] = true;
$p = new WP_Post();
$p->post_excerpt = str_repeat( 'sportscard ', 40 );
$GLOBALS['__state']['post'] = $p;
ob_start();
Mellenade_Perf_SEO::output_meta_description();
$m2 = ob_get_clean();
preg_match( '/content="([^"]*)"/', $m2, $mm );
$len = mb_strlen( html_entity_decode( $mm[1] ) );
ok( $len <= 156, "truncated to <=156 chars (got $len)" );
ok( false !== strpos( $mm[1], '…' ), 'ellipsis appended' );
ok( false === strpos( $mm[1], 'sportsca…' ), 'cut on a word boundary, not mid-word' );

// Shortcodes and tags must never leak into the snippet.
$p2 = new WP_Post();
$p2->post_excerpt = '';
$p2->post_content = '<p>Live [product_category cat="rookies"] breaks &amp; repacks</p>';
$GLOBALS['__state']['post'] = $p2;
ob_start();
Mellenade_Perf_SEO::output_meta_description();
$m3 = ob_get_clean();
ok( false === strpos( $m3, '[product_category' ), 'shortcodes stripped from description' );
ok( false === strpos( $m3, '<p>' ), 'HTML tags stripped from description' );

// Empty content -> emit nothing at all.
$p3 = new WP_Post();
$p3->post_excerpt = '';
$p3->post_content = '   ';
$GLOBALS['__state']['post'] = $p3;
ob_start();
Mellenade_Perf_SEO::output_meta_description();
ok( '' === ob_get_clean(), 'no empty description tag emitted' );

echo "\n== Accessibility CSS enqueued ==\n";
$GLOBALS['__enqueued'] = array();
Mellenade_Perf_Assets::enqueue_accessibility_css();
ok( in_array( 'mellenade-accessibility', $GLOBALS['__enqueued'], true ), 'contrast stylesheet enqueued' );

exit( $GLOBALS['fail'] );
