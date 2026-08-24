<?php
/**
 * Title tags, meta descriptions, canonicals, breadcrumbs, and sitemap.xml —
 * all driven by config/pages.php.
 *
 * Page metadata is never edited in a builder. It is edited in the repo, where
 * it can be reviewed, because a title tag on an attorney advertising site is
 * a compliance surface as much as a marketing one.
 *
 * If an SEO plugin (Yoast, Rank Math, SEOPress) is active, this class defers
 * to it for output and keeps only the page-key resolution and breadcrumbs, so
 * the two do not fight over the same tags.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class SEO {

	/**
	 * Resolved page key for this request.
	 *
	 * @var string|null
	 */
	private static $page_key = null;

	/**
	 * Hook up output.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'pre_get_document_title', array( __CLASS__, 'document_title' ) );
		add_action( 'wp_head', array( __CLASS__, 'print_head' ), 2 );
		add_filter( 'robots_txt', array( __CLASS__, 'noop' ), 5 );

		add_action( 'init', array( __CLASS__, 'add_sitemap_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render_sitemap' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
	}

	/**
	 * Placeholder filter so the hook list above reads honestly; robots.txt is
	 * owned by RobertsLaw\Robots.
	 *
	 * @param string $output robots.txt body.
	 * @return string
	 */
	public static function noop( $output ) {
		return $output;
	}

	/**
	 * Is another SEO plugin managing head tags?
	 *
	 * @return bool
	 */
	public static function seo_plugin_active() {
		return defined( 'WPSEO_VERSION' )
			|| defined( 'RANK_MATH_VERSION' )
			|| defined( 'SEOPRESS_VERSION' )
			|| class_exists( 'All_in_One_SEO_Pack' );
	}

	/**
	 * The config/pages.php key matching this request, or '' if none.
	 *
	 * @return string
	 */
	public static function current_page_key() {
		if ( null !== self::$page_key ) {
			return self::$page_key;
		}

		self::$page_key = '';

		$path = wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH );
		$path = '/' . trim( (string) $path, '/' );
		$path = ( '/' === $path ) ? '/' : $path . '/';

		foreach ( Config::load( 'pages' ) as $key => $page ) {
			if ( isset( $page['url'] ) && $page['url'] === $path ) {
				self::$page_key = $key;
				break;
			}
		}

		/**
		 * Filter the resolved page key. Useful if a page is served from a URL
		 * that differs from the inventory during a migration.
		 *
		 * @param string $key  Resolved key.
		 * @param string $path Request path.
		 */
		self::$page_key = (string) apply_filters( 'robertslaw_page_key', self::$page_key, $path );

		return self::$page_key;
	}

	/**
	 * The config entry for the current page.
	 *
	 * @return array
	 */
	public static function current_page() {
		$key = self::current_page_key();

		return $key ? Config::get( 'pages.' . $key, array() ) : array();
	}

	/**
	 * The declared H1 for the current page.
	 *
	 * Templates read the H1 from here rather than from a builder field, which
	 * is how "one H1 per page" stays true.
	 *
	 * @return string
	 */
	public static function current_h1() {
		$page = self::current_page();

		return $page['h1'] ?? get_the_title();
	}

	/**
	 * Title tag from the inventory.
	 *
	 * @param string $title Incoming title.
	 * @return string
	 */
	public static function document_title( $title ) {
		if ( self::seo_plugin_active() ) {
			return $title;
		}

		$page = self::current_page();

		return ! empty( $page['title'] ) ? $page['title'] : $title;
	}

	/**
	 * Meta description, canonical, and robots directives.
	 *
	 * @return void
	 */
	public static function print_head() {
		$page = self::current_page();

		if ( ! self::seo_plugin_active() ) {
			if ( ! empty( $page['meta'] ) ) {
				printf(
					'<meta name="description" content="%s">' . "\n",
					esc_attr( $page['meta'] )
				);
			}

			// Self-referencing canonical on every page, one hostname sitewide.
			printf(
				'<link rel="canonical" href="%s">' . "\n",
				esc_url( Schema::current_url() )
			);

			if ( ! empty( $page['noindex'] ) ) {
				echo '<meta name="robots" content="noindex,follow">' . "\n";
			}
		}

		// Emitted regardless of plugin: the mobile click-to-call target in
		// CLAUDE.md relies on the theme colour matching the header.
		printf(
			'<meta name="theme-color" content="%s">' . "\n",
			esc_attr( Tokens::get( 'color', 'ink', '#16283F' ) )
		);
	}

	/**
	 * Breadcrumb trail for the current page, walking config/pages.php parents.
	 *
	 * @return array<int, array{name:string, url:string}>
	 */
	public static function breadcrumb_trail() {
		$home  = untrailingslashit( Firm::get( 'home_url' ) );
		$key   = self::current_page_key();
		$trail = array();

		while ( $key ) {
			$page = Config::get( 'pages.' . $key, array() );

			if ( empty( $page ) ) {
				break;
			}

			array_unshift(
				$trail,
				array(
					'name' => $page['h1'] ?? $key,
					'url'  => $home . ( $page['url'] ?? '/' ),
				)
			);

			$key = $page['parent'] ?? '';
		}

		if ( empty( $trail ) || '/' !== Config::get( 'pages.home.url', '/' ) ) {
			return $trail;
		}

		if ( ! $trail || $trail[0]['url'] !== $home . '/' ) {
			array_unshift(
				$trail,
				array(
					'name' => 'Home',
					'url'  => $home . '/',
				)
			);
		}

		return $trail;
	}

	/**
	 * Register the /sitemap.xml rewrite.
	 *
	 * @return void
	 */
	public static function add_sitemap_rewrite() {
		add_rewrite_rule( '^sitemap\.xml$', 'index.php?rl_sitemap=1', 'top' );
	}

	/**
	 * Register the sitemap query var.
	 *
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public static function register_query_var( $vars ) {
		$vars[] = 'rl_sitemap';

		return $vars;
	}

	/**
	 * Render sitemap.xml from the inventory.
	 *
	 * Only pages marked 'ready' or 'drafted' are listed — a sitemap that
	 * advertises planned URLs is a sitemap full of 404s. Legal pages and
	 * anything noindexed stay out, per the architecture doc.
	 *
	 * lastmod is deliberately omitted: the architecture doc notes a date that
	 * never changes is worse than none.
	 *
	 * @return void
	 */
	public static function maybe_render_sitemap() {
		if ( ! get_query_var( 'rl_sitemap' ) ) {
			return;
		}

		$home = untrailingslashit( Firm::get( 'home_url' ) );

		header( 'Content-Type: application/xml; charset=UTF-8' );

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach ( Config::load( 'pages' ) as $page ) {
			if ( ! empty( $page['noindex'] ) ) {
				continue;
			}

			if ( ! in_array( $page['status'] ?? 'planned', array( 'ready', 'drafted' ), true ) ) {
				continue;
			}

			printf(
				"  <url><loc>%s</loc><changefreq>%s</changefreq><priority>%s</priority></url>\n",
				esc_url( $home . ( $page['url'] ?? '/' ) ),
				esc_html( $page['changefreq'] ?? 'monthly' ),
				esc_html( $page['priority'] ?? '0.5' )
			);
		}

		echo '</urlset>';
		exit;
	}
}
