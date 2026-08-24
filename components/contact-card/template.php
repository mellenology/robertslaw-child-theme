<?php
/**
 * Contact Card.
 *
 * Every value resolves from config/firm.php. Fields still awaiting a value
 * from the client render as nothing for visitors and as a visible marker for
 * logged-in editors, rather than printing "[STREET_ADDRESS]" onto a live page.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_heading  = trim( (string) ( $atts['heading'] ?? '' ) );
$rl_street   = \RobertsLaw\Firm::get( 'street_address' );
$rl_hours    = \RobertsLaw\Firm::get( 'office_hours' );
$rl_show_add = ! empty( $atts['show_address'] );
$rl_show_hrs = ! empty( $atts['show_hours'] );
?>
<div id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'contact-card', $atts, array( 'rl-contact-card--' . sanitize_html_class( $atts['layout'] ?? 'stacked' ) ) ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">

	<?php if ( '' !== $rl_heading ) : ?>
		<h2 class="rl-contact-card__heading"><?php echo esc_html( $rl_heading ); ?></h2>
	<?php endif; ?>

	<p class="rl-contact-card__name"><?php echo esc_html( \RobertsLaw\Firm::get( 'name' ) ); ?></p>

	<ul class="rl-contact-card__list">

		<?php if ( $rl_show_add ) : ?>
			<li class="rl-contact-card__row">
				<span class="rl-contact-card__label"><?php esc_html_e( 'Office', 'robertslaw' ); ?></span>
				<span class="rl-contact-card__value">
					<?php if ( \RobertsLaw\Compliance::is_placeholder( $rl_street ) ) : ?>
						<?php
						// Address outstanding. Show the city, which is known and
						// true, rather than an empty row.
						echo esc_html(
							\RobertsLaw\Firm::get( 'locality' ) . ', ' . \RobertsLaw\Firm::get( 'region' )
						);
						?>
						<?php echo \RobertsLaw\Compliance::render_value( $rl_street, __( 'street address outstanding', 'robertslaw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render_value(). ?>
					<?php else : ?>
						<?php echo esc_html( \RobertsLaw\Firm::address_line() ); ?>
					<?php endif; ?>
				</span>
			</li>
		<?php endif; ?>

		<li class="rl-contact-card__row">
			<span class="rl-contact-card__label"><?php esc_html_e( 'Phone', 'robertslaw' ); ?></span>
			<span class="rl-contact-card__value">
				<?php echo \RobertsLaw\Firm::phone_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in phone_link(). ?>
			</span>
		</li>

		<li class="rl-contact-card__row">
			<span class="rl-contact-card__label"><?php esc_html_e( 'Email', 'robertslaw' ); ?></span>
			<span class="rl-contact-card__value">
				<?php echo \RobertsLaw\Firm::email_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in email_link(). ?>
			</span>
		</li>

		<?php if ( $rl_show_hrs && ! \RobertsLaw\Compliance::is_placeholder( $rl_hours ) ) : ?>
			<li class="rl-contact-card__row">
				<span class="rl-contact-card__label"><?php esc_html_e( 'Hours', 'robertslaw' ); ?></span>
				<span class="rl-contact-card__value"><?php echo esc_html( $rl_hours ); ?></span>
			</li>
		<?php elseif ( $rl_show_hrs && current_user_can( 'edit_posts' ) ) : ?>
			<li class="rl-contact-card__row">
				<span class="rl-contact-card__label"><?php esc_html_e( 'Hours', 'robertslaw' ); ?></span>
				<span class="rl-contact-card__value">
					<?php echo \RobertsLaw\Compliance::render_value( $rl_hours, __( 'office hours outstanding', 'robertslaw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in render_value(). ?>
				</span>
			</li>
		<?php endif; ?>

		<li class="rl-contact-card__row">
			<span class="rl-contact-card__label"><?php esc_html_e( 'Serving', 'robertslaw' ); ?></span>
			<span class="rl-contact-card__value"><?php echo esc_html( \RobertsLaw\Firm::get( 'service_area' ) ); ?></span>
		</li>

	</ul>
</div>
