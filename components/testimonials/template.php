<?php
/**
 * Testimonials.
 *
 * Ships empty and stays empty until real, consented testimonials exist.
 * CLAUDE.md rule 3: never invent testimonials, reviews, star ratings, or client
 * quotes — not even as placeholder content.
 *
 * Every entry must clear all four consent gates in
 * RobertsLaw\Compliance::testimonial_cleared() before it renders, whatever its
 * post status. No Review or AggregateRating schema is emitted here or anywhere
 * else; RobertsLaw\Schema strips those types on the way out.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_limit = max( 1, (int) ( $atts['limit'] ?? 4 ) );
$rl_silo  = trim( (string) ( $atts['silo'] ?? '' ) );

$rl_query_args = array(
	'post_type'      => 'rl_testimonial',
	'post_status'    => 'publish',
	// Over-fetch, because entries are filtered by consent after the query.
	'posts_per_page' => $rl_limit * 4,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
	'no_found_rows'  => true,
);

if ( '' !== $rl_silo ) {
	$rl_query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small editorial dataset.
		array(
			'taxonomy' => 'rl_silo',
			'field'    => 'slug',
			'terms'    => $rl_silo,
		),
	);
}

$rl_found   = new WP_Query( $rl_query_args );
$rl_cleared = array();

foreach ( $rl_found->posts as $rl_post ) {
	if ( count( $rl_cleared ) >= $rl_limit ) {
		break;
	}

	if ( \RobertsLaw\Compliance::testimonial_cleared( $rl_post->ID ) ) {
		$rl_cleared[] = $rl_post;
	}
}

wp_reset_postdata();

/*
 * Empty state. Visitors see nothing at all — an empty "In Their Words" heading
 * is worse than no section. Editors see why it is empty.
 */
if ( empty( $rl_cleared ) ) {
	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<p class="rl-editor-note">%s</p>',
			esc_html__( 'Editor note: no testimonials are cleared to publish. Each entry needs a signed consent form on file, a consent date, client approval of the final wording, and a concluded matter. See content/testimonial-process.md.', 'robertslaw' )
		);
	}
	return;
}

$rl_heading = trim( (string) ( $atts['heading'] ?? '' ) );
?>
<section id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'testimonials', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">

	<?php if ( '' !== $rl_heading ) : ?>
		<h2 class="rl-testimonials__heading"><?php echo esc_html( $rl_heading ); ?></h2>
	<?php endif; ?>

	<ul class="rl-testimonials__list">
		<?php foreach ( $rl_cleared as $rl_post ) : ?>
			<?php $rl_attribution = (string) get_post_meta( $rl_post->ID, '_rl_attribution', true ); ?>
			<li class="rl-testimonials__item">
				<figure class="rl-testimonial">
					<blockquote class="rl-testimonial__quote">
						<?php echo wp_kses_post( apply_filters( 'the_content', $rl_post->post_content ) ); ?>
					</blockquote>

					<?php if ( '' !== trim( $rl_attribution ) ) : ?>
						<?php /* Exactly the attribution the client chose on the consent form. */ ?>
						<figcaption class="rl-testimonial__attribution">
							<?php echo esc_html( $rl_attribution ); ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php
	/*
	 * The testimonial disclaimer sits adjacent to the testimonials, not in the
	 * footer — content/testimonial-process.md is explicit about placement.
	 */
	echo \RobertsLaw\Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in that component.
		'disclaimer',
		array( 'variant' => 'testimonial' )
	);
	?>
</section>
