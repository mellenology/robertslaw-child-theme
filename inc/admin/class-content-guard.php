<?php
/**
 * Editor-side content guard.
 *
 * Scans post content on save for language the Tennessee RPC restricts and
 * surfaces what it finds as an admin notice on the next screen load.
 *
 * It flags; it does not rewrite. Silently changing an attorney's words would
 * hide the problem rather than fix it, and the right substitution is a
 * judgement call — "handles", "practices in", and "focuses on" are not
 * interchangeable in every sentence.
 *
 * This is a guardrail, not a compliance review. RPC 7.1-7.5 review by the
 * attorney is still required before anything goes live.
 *
 * @package RobertsLaw\Admin
 */

namespace RobertsLaw\Admin;

use RobertsLaw\Compliance;
use RobertsLaw\Config;

defined( 'ABSPATH' ) || exit;

class Content_Guard {

	const NOTICE_KEY = 'robertslaw_content_findings';

	/**
	 * Hook up the guard.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'scan_on_save' ), 20, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'render_notice' ) );
	}

	/**
	 * Scan a saved post and stash any findings for the current user.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public static function scan_on_save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
			return;
		}

		$findings = Compliance::scan_text( $post->post_content . ' ' . $post->post_title );

		if ( empty( $findings ) ) {
			delete_transient( self::NOTICE_KEY . '_' . get_current_user_id() );
			return;
		}

		set_transient(
			self::NOTICE_KEY . '_' . get_current_user_id(),
			array(
				'post_id'  => $post_id,
				'title'    => $post->post_title,
				'findings' => $findings,
			),
			120
		);
	}

	/**
	 * Show the findings once, then clear them.
	 *
	 * @return void
	 */
	public static function render_notice() {
		$key    = self::NOTICE_KEY . '_' . get_current_user_id();
		$stored = get_transient( $key );

		if ( ! $stored || empty( $stored['findings'] ) ) {
			return;
		}

		delete_transient( $key );

		echo '<div class="notice notice-warning is-dismissible"><p><strong>';
		esc_html_e( 'Roberts Law — content flagged for review', 'robertslaw' );
		echo '</strong><br>';
		printf(
			esc_html__( 'Found in "%s". Nothing has been changed — review and edit before publishing.', 'robertslaw' ),
			esc_html( $stored['title'] )
		);
		echo '</p><ul style="list-style:disc;margin-left:20px">';

		$seen = array();

		foreach ( $stored['findings'] as $finding ) {
			$signature = $finding['type'] . '|' . $finding['term'];

			if ( isset( $seen[ $signature ] ) ) {
				continue;
			}

			$seen[ $signature ] = true;

			printf(
				'<li><code>%s</code> — %s</li>',
				esc_html( $finding['term'] ),
				esc_html( $finding['reason'] )
			);
		}

		echo '</ul></div>';
	}
}
