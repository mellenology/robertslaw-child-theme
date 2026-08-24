<?php
/**
 * JSON-LD output.
 *
 * The single place structured data is emitted. Organization and Attorney go on
 * every page via a shared graph so the entity is asserted identically sitewide
 * — content/site-architecture.md treats that consistency as the foundation of
 * AI citation, not a nicety.
 *
 * Everything passes through Compliance::strip_forbidden_schema() on the way
 * out, so Review and AggregateRating cannot appear even if a plugin or a
 * pasted builder field tries to add them (CLAUDE.md rule 4).
 *
 * Unresolved placeholders are omitted rather than emitted — a streetAddress of
 * "[STREET_ADDRESS]" is worse than no streetAddress at all.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Schema {

	/**
	 * FAQ pairs collected during render, emitted as one FAQPage node.
	 *
	 * @var array<int, array{question:string, answer:string}>
	 */
	private static $faq_items = array();

	/**
	 * Breadcrumb trail collected during render.
	 *
	 * @var array<int, array{name:string, url:string}>
	 */
	private static $breadcrumbs = array();

	/**
	 * Hook up output.
	 *
	 * @return void
	 */
	public static function init() {
		// Late in the footer so components have had a chance to register FAQ
		// items and breadcrumbs during the main render.
		add_action( 'wp_footer', array( __CLASS__, 'print_graph' ), 20 );
	}

	/**
	 * Organization / LegalService node. Referenced by @id from everywhere else.
	 *
	 * @return array
	 */
	public static function organization() {
		$home = untrailingslashit( Firm::get( 'home_url' ) );

		$node = array(
			'@type'     => 'LegalService',
			'@id'       => $home . '/#organization',
			'name'      => Firm::get( 'name' ),
			'url'       => Firm::get( 'home_url' ),
			'telephone' => Firm::get( 'phone_schema' ),
			'email'     => Firm::get( 'email' ),
			'founder'   => array( '@id' => $home . '/about#joni-roberts' ),
			'knowsAbout' => Config::get( 'firm.knows_about', array() ),
		);

		$address = array_filter(
			array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => Firm::resolved( 'street_address' ),
				'addressLocality' => Firm::resolved( 'locality' ),
				'addressRegion'   => Firm::resolved( 'region' ),
				'postalCode'      => Firm::resolved( 'postal_code' ),
				'addressCountry'  => Firm::resolved( 'country' ),
			)
		);

		// Only emit an address once it says something real beyond the type.
		if ( count( $address ) > 2 ) {
			$node['address'] = $address;
		}

		$area = array();

		foreach ( Config::get( 'firm.area_served', array() ) as $entry ) {
			$area[] = array(
				'@type' => $entry['type'],
				'name'  => $entry['name'],
			);
		}

		if ( $area ) {
			$node['areaServed'] = $area;
		}

		return $node;
	}

	/**
	 * Attorney node.
	 *
	 * The credential list is closed and comes from config/firm.php. CLAUDE.md
	 * rule 2 forbids adding bar certifications, awards, or Rule 31 mediator
	 * listings that are not in the source files.
	 *
	 * @return array
	 */
	public static function attorney() {
		$home      = untrailingslashit( Firm::get( 'home_url' ) );
		$admission = Config::get( 'firm.bar_admission', array() );

		$node = array(
			'@type'     => 'Attorney',
			'@id'       => $home . '/about#joni-roberts',
			'name'      => Firm::get( 'attorney' ),
			'jobTitle'  => Firm::get( 'attorney_title' ),
			'telephone' => Firm::get( 'phone_schema' ),
			'email'     => Firm::get( 'email' ),
			'worksFor'  => array( '@id' => $home . '/#organization' ),
			'url'       => $home . '/joni-k-roberts/',
			'knowsAbout' => Config::get( 'firm.knows_about', array() ),
		);

		$alumni = array();

		foreach ( Config::get( 'firm.education', array() ) as $entry ) {
			$alumni[] = array(
				'@type' => 'CollegeOrUniversity',
				'name'  => $entry['institution'],
			);
		}

		if ( $alumni ) {
			$node['alumniOf'] = $alumni;
		}

		if ( ! empty( $admission['jurisdiction'] ) ) {
			$node['hasCredential'] = array(
				array(
					'@type'              => 'EducationalOccupationalCredential',
					'credentialCategory' => 'Bar Admission',
					'recognizedBy'       => array(
						'@type' => 'Organization',
						'name'  => 'State of ' . $admission['jurisdiction'],
					),
					'dateCreated'        => $admission['year'] ?? '',
				),
			);
		}

		$member_of = array();

		foreach ( Config::get( 'firm.memberships', array() ) as $name ) {
			$member_of[] = array(
				'@type' => 'Organization',
				'name'  => $name,
			);
		}

		if ( $member_of ) {
			$node['memberOf'] = $member_of;
		}

		return $node;
	}

	/**
	 * Register an FAQ pair for this page's FAQPage node.
	 *
	 * The answer text must match the visible answer exactly —
	 * content/website-copy.md is explicit about that.
	 *
	 * @param string $question Question, as displayed.
	 * @param string $answer   Answer, as displayed.
	 * @return void
	 */
	public static function add_faq( $question, $answer ) {
		$question = trim( wp_strip_all_tags( $question ) );
		$answer   = trim( wp_strip_all_tags( $answer ) );

		if ( '' === $question || '' === $answer ) {
			return;
		}

		self::$faq_items[] = array(
			'question' => $question,
			'answer'   => $answer,
		);
	}

	/**
	 * Register the breadcrumb trail for this page.
	 *
	 * @param array<int, array{name:string, url:string}> $trail Trail.
	 * @return void
	 */
	public static function set_breadcrumbs( array $trail ) {
		self::$breadcrumbs = $trail;
	}

	/**
	 * Page-specific node driven by config/pages.php.
	 *
	 * @param string $page_key Page key.
	 * @return array
	 */
	private static function page_node( $page_key ) {
		$page = Config::get( 'pages.' . $page_key, array() );

		if ( empty( $page ) ) {
			return array();
		}

		$home  = untrailingslashit( Firm::get( 'home_url' ) );
		$url   = $home . ( $page['url'] ?? '/' );
		$types = (array) ( $page['schema'] ?? array() );

		// Types handled by their own dedicated nodes elsewhere in the graph.
		$handled = array( 'FAQPage', 'BreadcrumbList', 'LegalService', 'WebSite' );
		$primary = '';

		foreach ( $types as $type ) {
			if ( ! in_array( $type, $handled, true ) ) {
				$primary = $type;
				break;
			}
		}

		if ( ! $primary ) {
			return array();
		}

		$node = array(
			'@type' => $primary,
			'@id'   => $url . '#page',
			'url'   => $url,
			'name'  => $page['h1'] ?? '',
			'isPartOf' => array( '@id' => $home . '/#website' ),
		);

		if ( ! empty( $page['meta'] ) ) {
			$node['description'] = $page['meta'];
		}

		if ( 'ProfilePage' === $primary ) {
			$node['mainEntity'] = array( '@id' => $home . '/about#joni-roberts' );
		}

		if ( 'Service' === $primary ) {
			$node['provider']    = array( '@id' => $home . '/#organization' );
			$node['areaServed']  = Firm::get( 'service_area' );
			$node['serviceType'] = $page['h1'] ?? '';
			unset( $node['isPartOf'] );
		}

		return $node;
	}

	/**
	 * Assemble and print the graph.
	 *
	 * @return void
	 */
	public static function print_graph() {
		if ( is_admin() ) {
			return;
		}

		$home  = untrailingslashit( Firm::get( 'home_url' ) );
		$graph = array(
			self::organization(),
			self::attorney(),
			array(
				'@type'     => 'WebSite',
				'@id'       => $home . '/#website',
				'url'       => Firm::get( 'home_url' ),
				'name'      => Firm::get( 'name' ),
				'publisher' => array( '@id' => $home . '/#organization' ),
			),
		);

		$page_key  = SEO::current_page_key();
		$page_node = $page_key ? self::page_node( $page_key ) : array();

		if ( $page_node ) {
			$graph[] = $page_node;
		}

		if ( self::$faq_items ) {
			$questions = array();

			foreach ( self::$faq_items as $item ) {
				$questions[] = array(
					'@type'          => 'Question',
					'name'           => $item['question'],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $item['answer'],
					),
				);
			}

			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => self::current_url() . '#faq',
				'mainEntity' => $questions,
			);
		}

		if ( count( self::$breadcrumbs ) > 1 ) {
			$items = array();
			$position = 1;

			foreach ( self::$breadcrumbs as $crumb ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => $crumb['name'],
					'item'     => $crumb['url'],
				);
			}

			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => self::current_url() . '#breadcrumb',
				'itemListElement' => $items,
			);
		}

		$document = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		/**
		 * Filter the whole JSON-LD document before the compliance pass.
		 *
		 * @param array $document Schema document.
		 */
		$document = apply_filters( 'robertslaw_schema', $document );

		// The compliance pass runs last, deliberately — nothing downstream can
		// reintroduce a forbidden type after it.
		$document = Compliance::strip_forbidden_schema( $document );

		if ( empty( $document['@graph'] ) ) {
			return;
		}

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}

	/**
	 * Canonical URL for the current request, built from config rather than the
	 * request host so one hostname is asserted sitewide.
	 *
	 * @return string
	 */
	public static function current_url() {
		$page_key = SEO::current_page_key();
		$home     = untrailingslashit( Firm::get( 'home_url' ) );

		if ( $page_key ) {
			return $home . Config::get( 'pages.' . $page_key . '.url', '/' );
		}

		$path = wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH );

		return $home . ( $path ? $path : '/' );
	}
}
