<?php
/**
 * Minimal WordPress stub to smoke-test the mu-plugin without a real WP install.
 * Records hook registrations so we can assert the plugin wired itself up correctly.
 */
define( 'ABSPATH', '/tmp/wp/' );

$GLOBALS['__hooks']   = array();
$GLOBALS['__state']   = array();
$GLOBALS['__printed'] = '';

function add_action( $h, $cb, $p = 10, $a = 1 ) { $GLOBALS['__hooks'][ $h ][] = $cb; return true; }
function add_filter( $h, $cb, $p = 10, $a = 1 ) { $GLOBALS['__hooks'][ $h ][] = $cb; return true; }
function remove_action( $h, $cb, $p = 10 ) { return true; }
function remove_filter( $h, $cb, $p = 10 ) { return true; }
function remove_meta_box( $i, $s, $c ) { return true; }
function apply_filters( $h, $v ) { return $v; }
function __return_false() { return false; }
function __return_true() { return true; }
function __return_empty_string() { return ''; }

function st( $k, $d = false ) { return $GLOBALS['__state'][ $k ] ?? $d; }

function is_admin() { return st( 'is_admin' ); }
function wp_doing_ajax() { return st( 'ajax' ); }
function wp_doing_cron() { return false; }
function is_feed() { return false; }
function is_customize_preview() { return false; }
function is_front_page() { return st( 'front_page' ); }
function is_search() { return st( 'is_search' ); }
function is_author() { return st( 'is_author' ); }
function is_singular() { return st( 'is_singular' ); }
function is_cart() { return st( 'is_cart' ); }
function is_checkout() { return st( 'is_checkout' ); }
function is_account_page() { return false; }
function is_woocommerce() { return st( 'is_woocommerce' ); }
function is_product() { return st( 'is_product' ); }
function is_wc_endpoint_url() { return false; }
function has_blocks( $id = null ) { return st( 'has_blocks' ); }
function get_queried_object_id() { return 1; }
function get_post() { return st( 'post', null ); }
function get_bloginfo( $k ) { return 'name' === $k ? 'Mellenade Sports Cards' : 'Cards and collectibles'; }
function home_url( $p = '/' ) { return 'https://mellenade.com' . $p; }
function get_theme_mod( $k ) { return st( 'logo_id', 0 ); }
function wp_get_attachment_image_src( $id, $s ) { return st( 'logo_src', false ); }
function wp_json_encode( $d, $f = 0 ) { return json_encode( $d, $f ); }
function esc_url( $u ) { return $u; }

$GLOBALS['__dequeued'] = array();
function wp_dequeue_style( $h ) { $GLOBALS['__dequeued'][] = "style:$h"; }
function wp_deregister_style( $h ) {}
function wp_dequeue_script( $h ) { $GLOBALS['__dequeued'][] = "script:$h"; }
function wp_deregister_script( $h ) {}
function wp_styles() { return (object) array( 'queue' => array(), 'registered' => array() ); }

class WP_Post { public $post_content = ''; }
