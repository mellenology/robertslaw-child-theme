<?php
require __DIR__ . '/wp-stubs.php';

// WooCommerce present this time.
class WooCommerce {}
class WC_Cart {
	public $count = 0;
	public function get_cart_contents_count() { return $this->count; }
}
class WC_Main { public $cart; }
function WC() { return $GLOBALS['__wc']; }
$GLOBALS['__wc'] = new WC_Main();
$GLOBALS['__wc']->cart = new WC_Cart();

$fail = 0;
function ok( $c, $l ) {
	if ( $c ) { echo "  PASS  $l\n"; } else { echo "  FAIL  $l\n"; $GLOBALS['fail'] = 1; }
}

require __DIR__ . '/../mu-plugins/mellenade-performance.php';

echo "\n== Store-page detection ==\n";
$GLOBALS['__state']['is_woocommerce'] = true;
ok( Mellenade_Perf_WooCommerce::is_store_page(), 'shop/product pages detected' );

$GLOBALS['__state']['is_woocommerce'] = false;
$GLOBALS['__state']['is_cart'] = true;
ok( Mellenade_Perf_WooCommerce::is_store_page(), 'cart detected' );
$GLOBALS['__state']['is_cart'] = false;

ok( ! Mellenade_Perf_WooCommerce::is_store_page(), 'plain page is NOT a store page' );

// A page embedding a shortcode must keep its assets.
$p = new WP_Post();
$p->post_content = 'Check out our [product_category category="rookies"] selection.';
$GLOBALS['__state']['post'] = $p;
ok( Mellenade_Perf_WooCommerce::is_store_page(), 'shortcode page keeps assets' );

$p2 = new WP_Post();
$p2->post_content = '<!-- wp:woocommerce/featured-product -->';
$GLOBALS['__state']['post'] = $p2;
ok( Mellenade_Perf_WooCommerce::is_store_page(), 'WooCommerce block page keeps assets' );

$p3 = new WP_Post();
$p3->post_content = 'Just an about page with no store markup.';
$GLOBALS['__state']['post'] = $p3;
ok( ! Mellenade_Perf_WooCommerce::is_store_page(), 'ordinary content page correctly excluded' );

echo "\n== Dequeue on non-store pages ==\n";
$GLOBALS['__dequeued'] = array();
Mellenade_Perf_WooCommerce::dequeue_offsite_assets();
$d = $GLOBALS['__dequeued'];
ok( in_array( 'style:woocommerce-general', $d, true ), 'woocommerce-general dequeued' );
ok( in_array( 'script:wc-cart-fragments', $d, true ), 'cart fragments dequeued' );
ok( count( $d ) > 10, 'full asset set dequeued (' . count( $d ) . ' handles)' );

echo "\n== Dequeue does NOT run on store pages ==\n";
$GLOBALS['__state']['is_woocommerce'] = true;
$GLOBALS['__dequeued'] = array();
Mellenade_Perf_WooCommerce::dequeue_offsite_assets();
ok( array() === $GLOBALS['__dequeued'], 'store pages keep every asset' );
$GLOBALS['__state']['is_woocommerce'] = false;

echo "\n== Cart fragments: conservative default ==\n";
$GLOBALS['__wc']->cart->count = 0;
$GLOBALS['__dequeued'] = array();
Mellenade_Perf_WooCommerce::maybe_disable_cart_fragments();
ok( in_array( 'script:wc-cart-fragments', $GLOBALS['__dequeued'], true ), 'empty cart -> fragments dropped' );

$GLOBALS['__wc']->cart->count = 3;
$GLOBALS['__dequeued'] = array();
Mellenade_Perf_WooCommerce::maybe_disable_cart_fragments();
ok( array() === $GLOBALS['__dequeued'], 'NON-empty cart -> fragments preserved (counter keeps working)' );

echo "\n== Cart fragments: never on cart/checkout ==\n";
$GLOBALS['__wc']->cart->count = 0;
$GLOBALS['__state']['is_checkout'] = true;
$GLOBALS['__dequeued'] = array();
Mellenade_Perf_WooCommerce::maybe_disable_cart_fragments();
ok( array() === $GLOBALS['__dequeued'], 'checkout untouched' );
$GLOBALS['__state']['is_checkout'] = false;

echo "\n== Null-cart edge case (no fatal) ==\n";
$GLOBALS['__wc']->cart = null;
$GLOBALS['__dequeued'] = array();
Mellenade_Perf_WooCommerce::maybe_disable_cart_fragments();
ok( true, 'null cart handled without fatal' );
$GLOBALS['__wc']->cart = new WC_Cart();

echo "\n== LCP priority on product pages ==\n";
$GLOBALS['__state']['is_product'] = true;
$a1 = Mellenade_Perf_Assets::image_attributes( array(), null, 'full' );
ok( 'high' === ( $a1['fetchpriority'] ?? '' ), 'first product image gets fetchpriority=high' );
ok( 'eager' === ( $a1['loading'] ?? '' ), 'first product image is eager-loaded' );
$a2 = Mellenade_Perf_Assets::image_attributes( array(), null, 'full' );
ok( ! isset( $a2['fetchpriority'] ), 'SECOND image does not get high priority' );
ok( 'async' === $a2['decoding'], 'second image still decodes async' );

exit( $GLOBALS['fail'] );
