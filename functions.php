<?php
/**
 * Roberts Law and Mediation — child theme of Themeco Pro.
 *
 * This file does one thing: load the bootstrap. Nothing else belongs here.
 * Configuration lives in /config, behaviour lives in /inc, markup lives in
 * /components. See README.md.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

define( 'ROBERTSLAW_VERSION', '1.0.0' );
define( 'ROBERTSLAW_DIR', trailingslashit( get_stylesheet_directory() ) );
define( 'ROBERTSLAW_URI', trailingslashit( get_stylesheet_directory_uri() ) );

require_once ROBERTSLAW_DIR . 'inc/bootstrap.php';
