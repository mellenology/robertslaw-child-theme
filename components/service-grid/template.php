<?php
/**
 * Practice Area Grid.
 *
 * Cards come from the Practice Areas content type, so adding a practice area is
 * one entry rather than an edit to every page that lists it. Each card links
 * with the descriptive anchor text stored on the entry — the architecture doc
 * rules out "learn more" and "click here".
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_silo  = trim( (string) ( $atts['silo'] ?? '' ) );
$rl_limit = max( 1, (int) ( $atts['limit'] ?? 12 ) );

$rl_query_args = array(
	'post_type'      => 'rl_practice_area',
	'post_status'    => 'publish',
	'posts_per_page' => $rl_limit,
	'orderby'        => 'menu_order title',
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

$rl_areas = new WP_Query( $rl_query_args );

if ( ! $rl_areas->have_posts() ) {
	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<p class="rl-editor-note">%s</p>',
			esc_html__( 'Editor note: no practice areas found for this silo. Add entries under Practice Areas.', 'robertslaw' )
		);
	}
	return;
}

$rl_heading = trim( (string) ( $atts['heading'] ?? '' ) );
$rl_columns = in_array( (string) ( $atts['columns'] ?? '3' ), array( '2', '3', '4' ), true ) ? (string) $atts['columns'] : '3';
?>
<section id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'service-grid', $atts, array( 'rl-service-grid--cols-' . $rl_columns ) ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">

	<?php if ( '' !== $rl_heading ) : ?>
		<h2 class="rl-service-grid__heading"><?php echo esc_html( $rl_heading ); ?></h2>
	<?php endif; ?>

	<ul class="rl-service-grid__list">
		<?php
		while ( $rl_areas->have_posts() ) :
			$rl_areas->the_post();

			$rl_page_key = (string) get_post_meta( get_the_ID(), '_rl_page_key', true );
			$rl_summary  = (string) get_post_meta( get_the_ID(), '_rl_card_summary', true );
			$rl_anchor   = (string) get_post_meta( get_the_ID(), '_rl_anchor_text', true );

			// The real URL comes from the page inventory, not from the entry's
			// own permalink — these entries are data behind flat page URLs.
			$rl_url = $rl_page_key
				? \RobertsLaw\Config::get( 'pages.' . $rl_page_key . '.url', '' )
				: '';

			if ( '' === $rl_summary ) {
				$rl_summary = get_the_excerpt();
			}
			?>
			<li class="rl-service-grid__item">
				<article class="rl-card">
					<h3 class="rl-card__title">
						<?php if ( $rl_url ) : ?>
							<a href="<?php echo esc_url( home_url( $rl_url ) ); ?>"><?php the_title(); ?></a>
						<?php else : ?>
							<?php the_title(); ?>
						<?php endif; ?>
					</h3>

					<?php if ( '' !== trim( $rl_summary ) ) : ?>
						<p class="rl-card__summary"><?php echo esc_html( wp_strip_all_tags( $rl_summary ) ); ?></p>
					<?php endif; ?>

					<?php if ( $rl_url && '' !== trim( $rl_anchor ) ) : ?>
						<p class="rl-card__link">
							<a href="<?php echo esc_url( home_url( $rl_url ) ); ?>"><?php echo esc_html( $rl_anchor ); ?></a>
						</p>
					<?php endif; ?>
				</article>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
</section>
