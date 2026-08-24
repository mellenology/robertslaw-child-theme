<?php
/**
 * robots.txt.
 *
 * AI crawlers stay allowed. CLAUDE.md is explicit: the site is optimized for
 * AI retrieval, and blocking GPTBot, ClaudeBot, PerplexityBot, or
 * Google-Extended removes it from exactly the surfaces this project targets.
 *
 * The allow list is emitted explicitly rather than left implicit, so a future
 * security plugin that appends blanket AI-crawler blocks is visibly in
 * conflict with a rule someone wrote down on purpose.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Robots {

	/**
	 * Hook up output.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'robots_txt', array( __CLASS__, 'filter' ), 20, 2 );
		add_action( 'init', array( __CLASS__, 'add_llms_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render_llms' ) );
	}

	/**
	 * Replace the generated robots.txt body.
	 *
	 * @param string $output Existing body.
	 * @param bool   $public Whether the site is set to be indexed.
	 * @return string
	 */
	public static function filter( $output, $public ) {
		// Respect the Reading setting — never override a deliberate "discourage
		// search engines" on a staging site.
		if ( ! $public ) {
			return $output;
		}

		$home  = untrailingslashit( Firm::get( 'home_url' ) );
		$lines = array(
			'User-agent: *',
			'Allow: /',
			'Disallow: /wp-admin/',
			'Allow: /wp-admin/admin-ajax.php',
			'Disallow: /thank-you/',
			'',
		);

		foreach ( Config::get( 'compliance.allowed_ai_crawlers', array() ) as $agent ) {
			$lines[] = 'User-agent: ' . $agent;
			$lines[] = 'Allow: /';
			$lines[] = '';
		}

		$lines[] = 'Sitemap: ' . $home . '/sitemap.xml';
		$lines[] = '';

		return implode( "\n", $lines );
	}

	/**
	 * Register the /llms.txt rewrite.
	 *
	 * An emerging convention: a plain-text summary of the site for AI systems.
	 * The architecture doc asks for one. It is generated from config rather
	 * than kept as a static file so it cannot drift from the page inventory.
	 *
	 * @return void
	 */
	public static function add_llms_rewrite() {
		add_rewrite_rule( '^llms\\.txt$', 'index.php?rl_llms=1', 'top' );
	}

	/**
	 * Register the llms.txt query var.
	 *
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public static function register_query_var( $vars ) {
		$vars[] = 'rl_llms';

		return $vars;
	}

	/**
	 * Render llms.txt.
	 *
	 * @return void
	 */
	public static function maybe_render_llms() {
		if ( ! get_query_var( 'rl_llms' ) ) {
			return;
		}

		$home = untrailingslashit( Firm::get( 'home_url' ) );

		header( 'Content-Type: text/plain; charset=UTF-8' );

		$lines = array(
			'# ' . Firm::get( 'name' ),
			'',
			'> Memphis, Tennessee law firm handling family law, probate and estate',
			'> planning, and general civil matters, with mediation available as an',
			'> alternative to litigation. Attorney ' . Firm::get( 'attorney' ) . ' has practiced in',
			'> Tennessee since ' . Config::get( 'firm.bar_admission.year', '' ) . '.',
			'',
			'Location: ' . Firm::get( 'locality' ) . ', ' . Firm::get( 'region_full' ) . '. Serves ' . Firm::get( 'service_area' ) . '.',
			'Phone: ' . Firm::get( 'phone_display' ),
			'Email: ' . Firm::get( 'email' ),
			'',
			'## Practice Areas',
		);

		// Only pages that actually exist. Advertising a planned URL to an AI
		// crawler is advertising a 404.
		foreach ( Config::load( 'pages' ) as $page ) {
			if ( ! in_array( $page['status'] ?? 'planned', array( 'ready', 'drafted' ), true ) ) {
				continue;
			}

			if ( ! in_array( $page['silo'] ?? '', array( 'family-law', 'mediation', 'probate', 'civil' ), true ) ) {
				continue;
			}

			$lines[] = '- [' . ( $page['h1'] ?? '' ) . '](' . $home . ( $page['url'] ?? '/' ) . ')';
		}

		$lines[] = '';
		$lines[] = '## Key Resources';

		foreach ( array( 'joni-k-roberts', 'mediation-faq', 'about', 'contact' ) as $key ) {
			$page = Config::get( 'pages.' . $key, array() );

			if ( empty( $page ) || ! in_array( $page['status'] ?? 'planned', array( 'ready', 'drafted' ), true ) ) {
				continue;
			}

			$lines[] = '- [' . ( $page['h1'] ?? $key ) . '](' . $home . ( $page['url'] ?? '/' ) . ')';
		}

		$lines[] = '';

		echo implode( "\n", array_map( 'wp_strip_all_tags', $lines ) );
		exit;
	}
}
