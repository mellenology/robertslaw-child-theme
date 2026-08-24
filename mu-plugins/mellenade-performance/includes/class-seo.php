<?php
/**
 * Conservative SEO hardening.
 *
 * IMPORTANT: this module deliberately does very little when a dedicated SEO plugin is
 * active. Duplicate canonical tags, duplicate Open Graph tags or a second Product schema
 * block are actively harmful — Google treats conflicting structured data as a quality
 * signal against the page. So every addition below is guarded by a "does something else
 * already do this?" check.
 *
 * @package Mellenade\Performance
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds only the metadata that nothing else on the site is emitting.
 */
final class Mellenade_Perf_SEO {

	/**
	 * Hook everything.
	 */
	public static function init() {
		add_action( 'wp_head', array( __CLASS__, 'output_meta_description' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'output_organization_schema' ), 20 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	/**
	 * Detect a dedicated SEO plugin.
	 *
	 * Covers the plugins that would realistically already own metadata on a WooCommerce
	 * store: Yoast, Rank Math, All in One SEO, SEOPress and The SEO Framework.
	 *
	 * @return bool
	 */
	public static function has_seo_plugin() {
		return (
			defined( 'WPSEO_VERSION' )            // Yoast.
			|| class_exists( 'RankMath' )         // Rank Math.
			|| defined( 'AIOSEO_VERSION' )        // All in One SEO.
			|| defined( 'SEOPRESS_VERSION' )      // SEOPress.
			|| function_exists( 'tsf' )           // The SEO Framework.
		);
	}

	/**
	 * Emit a meta description when nothing else does.
	 *
	 * MEASURED: the Lighthouse SEO audit flagged `meta-description` as failing on the home
	 * page (SEO score 92). Without one, Google composes the search snippet from whatever
	 * page text it finds first, which for this site is navigation chrome rather than the
	 * value proposition.
	 *
	 * Sources are tried in order of quality: an explicit excerpt, the product short
	 * description on a product page, then the tagline as a last resort. Nothing is emitted
	 * if an SEO plugin owns descriptions, or if no usable text exists — an empty or
	 * duplicated description is worse than none.
	 */
	public static function output_meta_description() {
		if ( self::has_seo_plugin() || ! mellenade_perf_is_frontend() ) {
			return;
		}

		$description = '';

		if ( is_front_page() ) {
			$description = get_bloginfo( 'description' );
		} elseif ( is_singular() ) {
			$post = get_post();

			if ( $post instanceof WP_Post ) {
				if ( ! empty( $post->post_excerpt ) ) {
					$description = $post->post_excerpt;
				} else {
					$description = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
				}
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$description = wp_strip_all_tags( term_description() );
		}

		$description = trim( preg_replace( '/\s+/', ' ', (string) $description ) );

		if ( '' === $description ) {
			return;
		}

		// ~155 chars is where Google truncates the snippet; cut on a word boundary.
		if ( function_exists( 'mb_strlen' ) && mb_strlen( $description ) > 155 ) {
			$description = mb_substr( $description, 0, 155 );
			$space       = mb_strrpos( $description, ' ' );

			if ( $space ) {
				$description = mb_substr( $description, 0, $space );
			}

			$description .= '…';
		}

		printf(
			'<meta name="description" content="%s" />' . "\n",
			esc_attr( $description )
		);
	}

	/**
	 * Emit Organization + WebSite JSON-LD.
	 *
	 * WooCommerce already outputs Product and BreadcrumbList schema on its own templates,
	 * so this only adds the site-level Organization node, and only on the front page, and
	 * only when no SEO plugin is present to own it.
	 *
	 * The sitewide SearchAction node is what enables a sitelinks search box in Google
	 * results — worth having for a store people search by player or set name.
	 */
	public static function output_organization_schema() {
		if ( self::has_seo_plugin() || ! is_front_page() || ! mellenade_perf_is_frontend() ) {
			return;
		}

		$home = home_url( '/' );

		$graph = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type' => 'Organization',
					'@id'   => $home . '#organization',
					'name'  => get_bloginfo( 'name' ),
					'url'   => $home,
				),
				array(
					'@type'           => 'WebSite',
					'@id'             => $home . '#website',
					'url'             => $home,
					'name'            => get_bloginfo( 'name' ),
					'publisher'       => array( '@id' => $home . '#organization' ),
					'potentialAction' => array(
						array(
							'@type'       => 'SearchAction',
							'target'      => array(
								'@type'       => 'EntryPoint',
								'urlTemplate' => $home . '?s={search_term_string}',
							),
							'query-input' => 'required name=search_term_string',
						),
					),
				),
			),
		);

		$description = get_bloginfo( 'description' );

		if ( $description ) {
			$graph['@graph'][0]['description'] = $description;
		}

		// A logo strengthens the Organization node; only included if one is actually set.
		$logo_id = (int) get_theme_mod( 'custom_logo' );

		if ( $logo_id ) {
			$logo = wp_get_attachment_image_src( $logo_id, 'full' );

			if ( ! empty( $logo[0] ) ) {
				$logo_node = array(
					'@type' => 'ImageObject',
					'url'   => $logo[0],
				);

				// Only include dimensions we actually have; null values in JSON-LD
				// are treated as malformed by Google's structured data parser.
				if ( ! empty( $logo[1] ) ) {
					$logo_node['width'] = (int) $logo[1];
				}

				if ( ! empty( $logo[2] ) ) {
					$logo_node['height'] = (int) $logo[2];
				}

				$graph['@graph'][0]['logo'] = $logo_node;
			}
		}

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}

	/**
	 * Keep thin, duplicate-prone archives out of the index.
	 *
	 * Search results and author archives on a single-operator store add no unique value and
	 * dilute crawl budget across a catalogue that changes constantly as cards sell.
	 * Product, category and tag archives are left fully indexable — those are the pages
	 * that earn traffic.
	 *
	 * @param array $robots Robots directives.
	 * @return array
	 */
	public static function robots( $robots ) {
		if ( self::has_seo_plugin() || ! is_array( $robots ) ) {
			return $robots;
		}

		if ( is_search() || is_author() ) {
			$robots['noindex']  = true;
			$robots['follow']   = true;
			unset( $robots['index'] );
		}

		return $robots;
	}
}

Mellenade_Perf_SEO::init();
