<?php
/**
 * Build Status screen.
 *
 * Renders config/pages.php as a working checklist: what is shippable, what is
 * blocked, and on what. The blockers listed in content/site-architecture.md
 * are client decisions rather than writing tasks, so they need to be visible
 * to whoever is chasing them, not buried in a markdown file in the repo.
 *
 * @package RobertsLaw\Admin
 */

namespace RobertsLaw\Admin;

use RobertsLaw\Config;

defined( 'ABSPATH' ) || exit;

class Build_Status {

	const PAGE = 'robertslaw-build';

	/**
	 * Hook up the screen.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ), 20 );
	}

	/**
	 * Add the submenu.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_submenu_page(
			Settings::PAGE,
			__( 'Build Status', 'robertslaw' ),
			__( 'Build Status', 'robertslaw' ),
			'edit_pages',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Render the screen.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		$pages   = Config::load( 'pages' );
		$blocked = array_filter(
			$pages,
			static function ( $page ) {
				return 'blocked' === ( $page['status'] ?? '' );
			}
		);

		$colors = array(
			'ready'   => '#1a7f37',
			'drafted' => '#8a6d0b',
			'planned' => '#57606a',
			'blocked' => '#b32d2e',
		);

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Build Status', 'robertslaw' ); ?></h1>

			<p class="description" style="max-width:52em">
				<?php esc_html_e( 'Generated from config/pages.php, which is the page inventory transcribed from content/site-architecture.md. Change a URL, title, or meta description there, not here — and never change a live URL without adding a 301.', 'robertslaw' ); ?>
			</p>

			<?php if ( $blocked ) : ?>
				<div class="notice notice-error inline" style="margin:16px 0;padding:8px 12px">
					<h2 style="margin-top:8px"><?php esc_html_e( 'Blocked — waiting on a decision', 'robertslaw' ); ?></h2>
					<ul style="list-style:disc;margin-left:20px">
						<?php foreach ( $blocked as $key => $page ) : ?>
							<li style="margin-bottom:8px">
								<strong><?php echo esc_html( $page['url'] ?? $key ); ?></strong><br>
								<?php echo esc_html( $page['blocked_on'] ?? '' ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php foreach ( array( 1, 2, 3 ) as $phase ) : ?>
				<h2><?php printf( esc_html__( 'Phase %d', 'robertslaw' ), (int) $phase ); ?></h2>
				<table class="widefat striped">
					<thead>
						<tr>
							<th style="width:20%"><?php esc_html_e( 'URL', 'robertslaw' ); ?></th>
							<th style="width:10%"><?php esc_html_e( 'Status', 'robertslaw' ); ?></th>
							<th style="width:10%"><?php esc_html_e( 'Silo', 'robertslaw' ); ?></th>
							<th><?php esc_html_e( 'Title tag', 'robertslaw' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $pages as $key => $page ) :
						if ( (int) ( $page['phase'] ?? 1 ) !== $phase ) {
							continue;
						}

						$status = $page['status'] ?? 'planned';
						?>
						<tr>
							<td>
								<code><?php echo esc_html( $page['url'] ?? '' ); ?></code>
								<?php if ( ! empty( $page['sensitive'] ) ) : ?>
									<br><span style="color:#b32d2e;font-size:11px">⚠ <?php esc_html_e( 'sensitive page', 'robertslaw' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<span style="color:<?php echo esc_attr( $colors[ $status ] ?? '#57606a' ); ?>;font-weight:600">
									<?php echo esc_html( $status ); ?>
								</span>
							</td>
							<td><?php echo esc_html( $page['silo'] ?? '' ); ?></td>
							<td>
								<?php echo esc_html( $page['title'] ?? '' ); ?>
								<?php
								$length = strlen( (string) ( $page['title'] ?? '' ) );
								if ( $length > 60 ) :
									?>
									<br><span style="color:#8a6d0b;font-size:11px">
										<?php
										printf(
											/* translators: %d: character count. */
											esc_html__( '%d characters — will truncate in most result snippets.', 'robertslaw' ),
											(int) $length
										);
										?>
									</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>

			<h2><?php esc_html_e( 'A note on scope', 'robertslaw' ); ?></h2>
			<p class="description" style="max-width:52em">
				<?php esc_html_e( 'The full inventory is 34 pages, roughly 35,000 words, all of which needs attorney review before publishing. Ten strong pages beat thirty-four thin ones — thin, near-duplicate practice pages are what Google\'s helpful-content systems demote, and an AI assistant will not retrieve from a page that does not actually answer anything. Ship Phase 1 well, then add Phase 2 pages one at a time.', 'robertslaw' ); ?>
			</p>
		</div>
		<?php
	}
}
