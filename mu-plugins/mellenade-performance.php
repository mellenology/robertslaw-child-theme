<?php
/**
 * Plugin Name:  Mellenade Performance
 * Description:  Theme-agnostic performance, cleanup and SEO hardening for the Mellenade Sports Cards WooCommerce store.
 * Version:      1.0.0
 * Author:       Cobble Marketing
 * License:      GPL-2.0-or-later
 *
 * MU-PLUGIN LOADER
 * ----------------
 * WordPress only auto-loads PHP files sitting at the top level of wp-content/mu-plugins/.
 * Files inside sub-directories are ignored, so this stub is what boots the real code in
 * mu-plugins/mellenade-performance/.
 *
 * Every optimisation in this package is opt-out via a constant defined in wp-config.php.
 * See the README for the full list and the trade-offs of each one.
 *
 * @package Mellenade\Performance
 */

defined( 'ABSPATH' ) || exit;

define( 'MELLENADE_PERF_VERSION', '1.0.0' );
define( 'MELLENADE_PERF_DIR', __DIR__ . '/mellenade-performance' );

require_once MELLENADE_PERF_DIR . '/bootstrap.php';
