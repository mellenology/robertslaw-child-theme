<?php
/**
 * Attorney Card.
 *
 * Credentials render from config/firm.php only. Nothing here accepts a
 * free-text credential field, because CLAUDE.md rule 2 forbids adding bar
 * certifications, awards, or Rule 31 mediator listings that are not in the
 * source files — and a text box invites exactly that.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_summary   = trim( (string) ( $atts['summary'] ?? '' ) );
$rl_photo_id  = (int) ( $atts['photo'] ?? 0 );
$rl_admission = \RobertsLaw\Config::get( 'firm.bar_admission', array() );
$rl_profile   = \RobertsLaw\Config::get( 'pages.joni-k-roberts.url', '/joni-k-roberts/' );
?>
<div id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'attorney-card', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">

	<?php if ( $rl_photo_id ) : ?>
		<div class="rl-attorney-card__media">
			<?php
			// Explicit dimensions come from the attachment metadata, which
			// keeps the image from shifting layout as it loads.
			echo wp_get_attachment_image(
				$rl_photo_id,
				'medium_large',
				false,
				array(
					'class'   => 'rl-attorney-card__photo',
					'loading' => 'lazy',
					'alt'     => sprintf(
						/* translators: %s: attorney name. */
						esc_attr__( '%s, attorney and mediator', 'robertslaw' ),
						esc_attr( \RobertsLaw\Firm::get( 'attorney' ) )
					),
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="rl-attorney-card__body">
		<h2 class="rl-attorney-card__name">
			<a href="<?php echo esc_url( home_url( $rl_profile ) ); ?>">
				<?php echo esc_html( \RobertsLaw\Firm::get( 'attorney' ) ); ?>
			</a>
		</h2>

		<p class="rl-attorney-card__title"><?php echo esc_html( \RobertsLaw\Firm::get( 'attorney_title' ) ); ?></p>

		<?php if ( '' !== $rl_summary ) : ?>
			<p class="rl-attorney-card__summary"><?php echo wp_kses_post( $rl_summary ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $atts['show_credentials'] ) ) : ?>
			<dl class="rl-attorney-card__facts">
				<?php if ( ! empty( $rl_admission['jurisdiction'] ) ) : ?>
					<dt><?php esc_html_e( 'Admitted to practice', 'robertslaw' ); ?></dt>
					<dd>
						<?php
						echo esc_html(
							trim( $rl_admission['jurisdiction'] . ', ' . ( $rl_admission['year'] ?? '' ), ', ' )
						);
						?>
					</dd>
				<?php endif; ?>

				<?php $rl_education = \RobertsLaw\Config::get( 'firm.education', array() ); ?>
				<?php if ( $rl_education ) : ?>
					<dt><?php esc_html_e( 'Education', 'robertslaw' ); ?></dt>
					<dd>
						<ul class="rl-attorney-card__plain-list">
							<?php foreach ( $rl_education as $rl_entry ) : ?>
								<li>
									<?php
									echo esc_html(
										sprintf(
											'%s (%s, %s)',
											$rl_entry['institution'],
											$rl_entry['degree'],
											$rl_entry['year']
										)
									);
									?>
								</li>
							<?php endforeach; ?>
						</ul>
					</dd>
				<?php endif; ?>

				<?php $rl_memberships = \RobertsLaw\Config::get( 'firm.memberships', array() ); ?>
				<?php if ( $rl_memberships ) : ?>
					<dt><?php esc_html_e( 'Professional memberships', 'robertslaw' ); ?></dt>
					<dd><?php echo esc_html( implode( ' · ', $rl_memberships ) ); ?></dd>
				<?php endif; ?>
			</dl>
		<?php endif; ?>

		<?php if ( ! empty( $atts['show_courts'] ) ) : ?>
			<?php $rl_courts = \RobertsLaw\Config::get( 'firm.courts', array() ); ?>
			<?php if ( $rl_courts ) : ?>
				<p class="rl-attorney-card__courts">
					<strong><?php esc_html_e( 'Courts:', 'robertslaw' ); ?></strong>
					<?php echo esc_html( implode( ' · ', $rl_courts ) ); ?>
				</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
