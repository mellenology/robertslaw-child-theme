<?php
/**
 * Page Hero.
 *
 * Carries the page's single H1. The heading defaults to the H1 declared for
 * this page in config/pages.php rather than to a builder field, which is how
 * "one H1 per page, matching the inventory" stays true without relying on
 * whoever last opened the builder.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_heading = trim( (string) ( $atts['heading'] ?? '' ) );

if ( '' === $rl_heading ) {
	$rl_heading = \RobertsLaw\SEO::current_h1();
}

$rl_eyebrow = trim( (string) ( $atts['eyebrow'] ?? '' ) );
$rl_answer  = trim( (string) ( $atts['answer'] ?? '' ) );
?>
<header id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'hero', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">
	<div class="rl-hero__inner">

		<?php if ( '' !== $rl_eyebrow ) : ?>
			<?php /* A <p>, not a heading — an eyebrow must not break heading order. */ ?>
			<p class="rl-hero__eyebrow"><?php echo esc_html( $rl_eyebrow ); ?></p>
		<?php endif; ?>

		<h1 class="rl-hero__heading"><?php echo esc_html( $rl_heading ); ?></h1>

		<?php
		if ( '' !== $rl_answer ) {
			// Reuse the answer-block component rather than duplicating its
			// markup, so a change there lands in the hero too.
			echo \RobertsLaw\Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in that component.
				'answer-block',
				array(
					'answer' => $rl_answer,
					'tone'   => 'lead',
				)
			);
		}
		?>

		<?php if ( ! empty( $atts['show_actions'] ) ) : ?>
			<p class="rl-hero__actions">
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
					<?php esc_html_e( 'Email the office', 'robertslaw' ); ?>
				</a>
			</p>
		<?php endif; ?>

	</div>
</header>
