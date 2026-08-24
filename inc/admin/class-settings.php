<?php
/**
 * Firm Details screen.
 *
 * Exposes only the fields genuinely blocked on the client (address, postal
 * code, hours, Google review link). Name, phone, and email are entity-critical
 * and stay in the repo where a change goes through review — see
 * RobertsLaw\Firm::EDITABLE.
 *
 * @package RobertsLaw\Admin
 */

namespace RobertsLaw\Admin;

use RobertsLaw\Compliance;
use RobertsLaw\Config;
use RobertsLaw\Firm;

defined( 'ABSPATH' ) || exit;

class Settings {

	const PAGE = 'robertslaw-firm';

	/**
	 * Hook up the screen.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_notices', array( __CLASS__, 'placeholder_notice' ) );
	}

	/**
	 * Add the top-level menu.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_menu_page(
			__( 'Roberts Law', 'robertslaw' ),
			__( 'Roberts Law', 'robertslaw' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' ),
			'dashicons-bank',
			20
		);

		add_submenu_page(
			self::PAGE,
			__( 'Firm Details', 'robertslaw' ),
			__( 'Firm Details', 'robertslaw' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Register the option.
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			'robertslaw_firm',
			Firm::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitise submitted overrides.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$clean = array();

		if ( ! is_array( $input ) ) {
			return $clean;
		}

		foreach ( Firm::EDITABLE as $key ) {
			if ( ! isset( $input[ $key ] ) ) {
				continue;
			}

			$value = trim( sanitize_text_field( $input[ $key ] ) );

			// An empty box means "fall back to the repo value", not "store an
			// empty string" — otherwise clearing a field would hide the fact
			// that the value is still outstanding.
			if ( '' === $value ) {
				continue;
			}

			$clean[ $key ] = $value;
		}

		return $clean;
	}

	/**
	 * Render the screen.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$labels = array(
			'street_address'    => __( 'Street address', 'robertslaw' ),
			'postal_code'       => __( 'ZIP code', 'robertslaw' ),
			'office_hours'      => __( 'Office hours', 'robertslaw' ),
			'google_review_url' => __( 'Google review link', 'robertslaw' ),
		);

		$help = array(
			'street_address'    => __( 'Required for the Contact page, the PostalAddress schema, and Google Business Profile. Enter the real address only — a guessed one breaks local search.', 'robertslaw' ),
			'postal_code'       => __( 'Must match the address exactly as it appears on Google Business Profile.', 'robertslaw' ),
			'office_hours'      => __( 'Free text, e.g. "Monday to Thursday, 9:00am to 4:00pm". Leave blank until the firm confirms — do not enter a plausible guess.', 'robertslaw' ),
			'google_review_url' => __( 'The direct review link from Google Business Profile. Used by the review request email template.', 'robertslaw' ),
		);

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Firm Details', 'robertslaw' ); ?></h1>

			<p class="description" style="max-width:46em">
				<?php esc_html_e( 'Only the values still outstanding from the client are editable here. The firm name, phone number, and email address are set in the theme (config/firm.php) because they must stay identical everywhere on the site — that consistency is what search engines and AI assistants use to recognise the firm as one entity.', 'robertslaw' ); ?>
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'robertslaw_firm' ); ?>

				<table class="form-table" role="presentation"><tbody>
				<?php foreach ( Firm::EDITABLE as $key ) : ?>
					<?php
					$current  = Firm::get( $key );
					$blocked  = Compliance::is_placeholder( $current );
					$stored   = get_option( Firm::OPTION, array() );
					$value    = $blocked ? '' : ( $stored[ $key ] ?? '' );
					?>
					<tr>
						<th scope="row">
							<label for="rl-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $labels[ $key ] ?? $key ); ?></label>
						</th>
						<td>
							<input
								type="text"
								class="regular-text"
								id="rl-<?php echo esc_attr( $key ); ?>"
								name="<?php echo esc_attr( Firm::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( $value ); ?>"
								placeholder="<?php echo $blocked ? esc_attr( $current ) : ''; ?>">
							<?php if ( $blocked ) : ?>
								<p style="color:#b32d2e;margin:6px 0 0">
									<strong><?php esc_html_e( 'Outstanding.', 'robertslaw' ); ?></strong>
									<?php esc_html_e( 'This value is omitted from the page and from structured data until it is filled in.', 'robertslaw' ); ?>
								</p>
							<?php endif; ?>
							<p class="description"><?php echo esc_html( $help[ $key ] ?? '' ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>

				<?php submit_button(); ?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Set in the theme', 'robertslaw' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Change these in config/firm.php and deploy. They appear character for character in page copy, the footer, and structured data.', 'robertslaw' ); ?>
			</p>
			<table class="widefat striped" style="max-width:46em"><tbody>
				<?php
				foreach ( array( 'name', 'attorney', 'phone_display', 'phone_schema', 'email', 'locality', 'region', 'service_area' ) as $key ) :
					?>
					<tr>
						<td style="width:12em"><code><?php echo esc_html( $key ); ?></code></td>
						<td><?php echo esc_html( Firm::get( $key ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody></table>
		</div>
		<?php
	}

	/**
	 * Warn on every admin screen while placeholders remain.
	 *
	 * @return void
	 */
	public static function placeholder_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$outstanding = Compliance::unresolved_placeholders();

		if ( empty( $outstanding ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
			esc_html__( 'Roberts Law:', 'robertslaw' ),
			esc_html(
				sprintf(
					/* translators: %s: comma-separated list of field names. */
					__( 'awaiting values from the client for %s. These are omitted from the site and from structured data until supplied — do not substitute a guess.', 'robertslaw' ),
					implode( ', ', array_keys( $outstanding ) )
				)
			),
			esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ),
			esc_html__( 'Firm Details', 'robertslaw' )
		);
	}
}
