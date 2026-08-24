<?php
/**
 * Bootstrap. Loads and initialises every subsystem, in dependency order.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

require_once ROBERTSLAW_DIR . 'inc/class-config.php';
require_once ROBERTSLAW_DIR . 'inc/class-compliance.php';
require_once ROBERTSLAW_DIR . 'inc/class-firm.php';
require_once ROBERTSLAW_DIR . 'inc/class-tokens.php';
require_once ROBERTSLAW_DIR . 'inc/class-content-types.php';
require_once ROBERTSLAW_DIR . 'inc/class-components.php';
require_once ROBERTSLAW_DIR . 'inc/class-schema.php';
require_once ROBERTSLAW_DIR . 'inc/class-seo.php';
require_once ROBERTSLAW_DIR . 'inc/class-assets.php';
require_once ROBERTSLAW_DIR . 'inc/class-robots.php';
require_once ROBERTSLAW_DIR . 'inc/class-guards.php';
require_once ROBERTSLAW_DIR . 'inc/class-page-template.php';

require_once ROBERTSLAW_DIR . 'inc/admin/class-settings.php';
require_once ROBERTSLAW_DIR . 'inc/admin/class-build-status.php';
require_once ROBERTSLAW_DIR . 'inc/admin/class-content-guard.php';
require_once ROBERTSLAW_DIR . 'inc/admin/class-page-importer.php';

require_once ROBERTSLAW_DIR . 'inc/integrations/cornerstone-elements.php';
require_once ROBERTSLAW_DIR . 'inc/integrations/cornerstone-dynamic-content.php';

/**
 * Boot everything on `after_setup_theme`, which is late enough for the parent
 * theme (Pro) to have loaded and early enough to register post types and
 * hook into `init`.
 */
add_action(
	'after_setup_theme',
	function () {
		\RobertsLaw\Tokens::init();
		\RobertsLaw\Content_Types::init();
		\RobertsLaw\Components::init();
		\RobertsLaw\Schema::init();
		\RobertsLaw\SEO::init();
		\RobertsLaw\Assets::init();
		\RobertsLaw\Robots::init();
		\RobertsLaw\Guards::init();
		\RobertsLaw\Page_Template::init();

		if ( is_admin() ) {
			\RobertsLaw\Admin\Settings::init();
			\RobertsLaw\Admin\Build_Status::init();
			\RobertsLaw\Admin\Content_Guard::init();
			\RobertsLaw\Admin\Page_Importer::init();
		}

		load_child_theme_textdomain( 'robertslaw', ROBERTSLAW_DIR . 'languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'script', 'style' ) );
	},
	5
);
