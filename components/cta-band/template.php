<?php
/**
 * CTA Band.
 *
 * Phone and email resolve from config/firm.php — never typed into the band, so
 * the number in a CTA can never drift from the number in the footer or the
 * schema.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_heading = trim( (string) ( $atts['heading'] ?? '' ) );
$rl_body    = trim( (string) ( $atts['body'] ?? '' ) );
$rl_contact = \RobertsLaw\Config::get( 'pages.contact.url', '/contact/' );
?>
<section id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'cta-band', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">
	<div class="rl-cta-band__inner">

		<?php if ( '' !== $rl_heading ) : ?>
			<h2 class="rl-cta-band__heading"><?php echo esc_html( $rl_heading ); ?></h2>
		<?php endif; ?>

		<?php if ( '' !== $rl_body ) : ?>
			<p class="rl-cta-band__body"><?php echo wp_kses_post( $rl_body ); ?></p>
		<?php endif; ?>

		<p class="rl-cta-band__actions">
			<a class="rl-btn rl-btn--primary" href="<?php echo esc_url( \RobertsLaw\Firm::get( 'phone_href' ) ); ?>">
				<?php
				printf(
					/* translators: %s: phone number. */
					esc_html__( 'Call %s', 'robertslaw' ),
					esc_html( \RobertsLaw\Firm::get( 'phone_display' ) )
				);
				?>
			</a>

			<a class="rl-btn rl-btn--ghost" href="<?php echo esc_url( 'mailto:' . \RobertsLaw\Firm::get( 'email' ) ); ?>">
				<?php esc_html_e( 'Email us', 'robertslaw' ); ?>
			</a>

			<?php if ( ! empty( $atts['show_schedule'] ) ) : ?>
				<a class="rl-btn rl-btn--ghost" href="<?php echo esc_url( home_url( $rl_contact ) ); ?>">
					<?php esc_html_e( 'Schedule a consultation', 'robertslaw' ); ?>
				</a>
			<?php endif; ?>
		</p>

	</div>
</section>
