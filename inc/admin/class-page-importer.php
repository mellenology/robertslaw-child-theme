<?php
/**
 * Creates the site's pages from the blueprints.
 *
 * The reliable path. Where a .tco import depends on Themeco's template format
 * being exactly right, this just creates WordPress pages whose content is the
 * [rl_page_template] shortcode — so the page renders the same blueprint through
 * the same components, with no dependency on the export format at all.
 *
 * Behaviour worth knowing:
 *   - A page whose slug already exists is skipped, never overwritten. Running
 *     this twice is safe.
 *   - Pages carrying unreviewed or blocked copy are created as DRAFTS, so
 *     nothing with a [NEEDS ATTORNEY INPUT] marker can go public by accident.
 *   - Slugs come from config/pages.php, so the URLs match the architecture and
 *     the sitemap on the first try. Fixing a wrong slug later means a 301.
 *
 * @package RobertsLaw\Admin
 */

namespace RobertsLaw\Admin;

use RobertsLaw\Config;

defined( 'ABSPATH' ) || exit;

class Page_Importer {

	const PAGE   = 'robertslaw-pages';
	const ACTION = 'robertslaw_create_pages';

	/**
	 * Hook up the screen.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ), 30 );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
	}

	/**
	 * Add the submenu.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_submenu_page(
			Settings::PAGE,
			__( 'Create Pages', 'robertslaw' ),
			__( 'Create Pages', 'robertslaw' ),
			'publish_pages',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Which blueprints can be turned into pages, and their current state.
	 *
	 * @return array<string, array>
	 */
	private static function candidates() {
		$rows = array();

		foreach ( Config::load( 'page-templates' ) as $key => $blueprint ) {
			$page = Config::get( 'pages.' . $key, array() );

			if ( empty( $page['url'] ) ) {
				continue;
			}

			$slug     = trim( $page['url'], '/' );
			$lookup   = '' !== $slug ? $slug : 'home';
			$existing = get_page_by_path( $lookup );

			$rows[ $key ] = array(
				'title'    => $page['h1'] ?? $key,
				'url'      => $page['url'],
				'slug'     => '' !== $slug ? $slug : 'home',
				'draft'    => ! empty( $blueprint['review_required'] ) || ! empty( $blueprint['blocked'] ),
				'blocked'  => $blueprint['blocked'] ?? '',
				'existing' => $existing instanceof \WP_Post ? $existing->ID : 0,
			);
		}

		return $rows;
	}

	/**
	 * Render the screen.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'publish_pages' ) ) {
			return;
		}

		$rows = self::candidates();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Create Pages', 'robertslaw' ); ?></h1>

			<p class="description" style="max-width:52em">
				<?php esc_html_e( 'Creates a WordPress page for each blueprint, at the URL declared in the site architecture. Each page\'s content is a single shortcode that renders the blueprint through the theme\'s components, so the page stays in sync with the blueprint rather than becoming a separate copy of it.', 'robertslaw' ); ?>
			</p>

			<p class="description" style="max-width:52em">
				<strong><?php esc_html_e( 'Existing pages are never overwritten.', 'robertslaw' ); ?></strong>
				<?php esc_html_e( 'Pages whose copy has not been through attorney review are created as drafts.', 'robertslaw' ); ?>
			</p>

			<table class="widefat striped" style="max-width:60em;margin:16px 0">
				<thead>
					<tr>
						<th style="width:26%"><?php esc_html_e( 'URL', 'robertslaw' ); ?></th>
						<th style="width:14%"><?php esc_html_e( 'Will be', 'robertslaw' ); ?></th>
						<th><?php esc_html_e( 'Notes', 'robertslaw' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html( $row['url'] ); ?></code></td>
						<td>
							<?php if ( $row['existing'] ) : ?>
								<span style="color:#57606a"><?php esc_html_e( 'skipped', 'robertslaw' ); ?></span>
							<?php elseif ( $row['draft'] ) : ?>
								<span style="color:#8a6d0b;font-weight:600"><?php esc_html_e( 'draft', 'robertslaw' ); ?></span>
							<?php else : ?>
								<span style="color:#1a7f37;font-weight:600"><?php esc_html_e( 'published', 'robertslaw' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $row['existing'] ) : ?>
								<?php esc_html_e( 'A page already exists at this slug.', 'robertslaw' ); ?>
							<?php elseif ( $row['blocked'] ) : ?>
								<?php echo esc_html( $row['blocked'] ); ?>
							<?php elseif ( $row['draft'] ) : ?>
								<?php esc_html_e( 'Contains copy that needs attorney review before publishing.', 'robertslaw' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Copy approved.', 'robertslaw' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<?php wp_nonce_field( self::ACTION ); ?>
				<?php submit_button( __( 'Create the missing pages', 'robertslaw' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Confirm what happened after a run.
	 *
	 * @return void
	 */
	public static function notice() {
		if ( ! isset( $_GET['rl_created'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display.
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: pages created, 2: pages skipped. */
					__( 'Roberts Law: %1$d page(s) created, %2$d skipped because a page already existed at that slug.', 'robertslaw' ),
					(int) $_GET['rl_created'], // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					isset( $_GET['rl_skipped'] ) ? (int) $_GET['rl_skipped'] : 0 // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				)
			)
		);
	}

	/**
	 * Create the pages.
	 *
	 * @return void
	 */
	public static function handle() {
		if ( ! current_user_can( 'publish_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'robertslaw' ) );
		}

		check_admin_referer( self::ACTION );

		$created = 0;
		$skipped = 0;

		foreach ( self::candidates() as $key => $row ) {
			if ( $row['existing'] ) {
				$skipped++;
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'   => $row['title'],
					'post_name'    => $row['slug'],
					'post_type'    => 'page',
					'post_status'  => $row['draft'] ? 'draft' : 'publish',
					'post_content' => sprintf( '[rl_page_template key="%s"]', $key ),
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			// The home page is served from the site root rather than its slug.
			if ( 'home' === $key ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $post_id );
			}

			$created++;
		}

		// Slugs and the sitemap rewrite both depend on fresh rules.
		flush_rewrite_rules();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => self::PAGE,
					'rl_created' => $created,
					'rl_skipped' => $skipped,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
