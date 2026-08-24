<?php
/**
 * Last Reviewed.
 *
 * A visible review date and a named attorney attribution. Both are AI-retrieval
 * signals on legal content — recency, and answers tied to a verifiable person
 * rather than an anonymous page.
 *
 * The date falls back to the post's modified date rather than to today, because
 * "reviewed" should mean reviewed.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_date = trim( (string) ( $atts['date'] ?? '' ) );

if ( '' === $rl_date ) {
	$rl_timestamp = get_post_modified_time( 'U', true );
	$rl_date      = $rl_timestamp ? wp_date( get_option( 'date_format' ), $rl_timestamp ) : '';
}

if ( '' === $rl_date ) {
	return;
}
?>
<p id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'last-reviewed', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">
	<span class="rl-last-reviewed__date">
		<?php
		printf(
			/* translators: %s: review date. */
			esc_html__( 'Last reviewed: %s', 'robertslaw' ),
			esc_html( $rl_date )
		);
		?>
	</span>

	<?php if ( ! empty( $atts['show_author'] ) ) : ?>
		<span class="rl-last-reviewed__author">
			<?php
			printf(
				/* translators: 1: attorney name, 2: city and state. */
				esc_html__( 'Reviewed by %1$s, attorney, %2$s', 'robertslaw' ),
				esc_html( \RobertsLaw\Firm::get( 'attorney' ) ),
				esc_html( \RobertsLaw\Firm::get( 'locality' ) . ', ' . \RobertsLaw\Firm::get( 'region_full' ) )
			);
			?>
		</span>
	<?php endif; ?>
</p>
