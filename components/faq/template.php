<?php
/**
 * FAQ.
 *
 * Questions are headings phrased the way people type them, and each answer
 * opens with a one-sentence direct answer before elaborating — that opening
 * sentence is what gets retrieved and quoted.
 *
 * The FAQPage schema text is taken from the same strings rendered on the page,
 * never from a separate field, because content/website-copy.md requires the
 * schema text to be identical to the visible answer.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_group = trim( (string) ( $atts['group'] ?? '' ) );

$rl_query_args = array(
	'post_type'      => 'rl_faq',
	'post_status'    => 'publish',
	'posts_per_page' => 50,
	'orderby'        => 'menu_order title',
	'order'          => 'ASC',
	'no_found_rows'  => true,
);

if ( '' !== $rl_group ) {
	$rl_query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small, cached, editorial dataset.
		array(
			'taxonomy' => 'rl_faq_group',
			'field'    => 'slug',
			'terms'    => $rl_group,
		),
	);
}

$rl_faqs = new WP_Query( $rl_query_args );

if ( ! $rl_faqs->have_posts() ) {
	// No invented filler. An FAQ block with nothing behind it renders nothing.
	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<p class="rl-editor-note">%s</p>',
			esc_html__( 'Editor note: no FAQs found for this group. Add entries under FAQs, then assign them to this group.', 'robertslaw' )
		);
	}
	return;
}

$rl_level   = in_array( $atts['heading_level'] ?? 'h2', array( 'h2', 'h3' ), true ) ? $atts['heading_level'] : 'h2';
$rl_heading = trim( (string) ( $atts['heading'] ?? '' ) );
?>
<section id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'faq', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">

	<?php if ( '' !== $rl_heading ) : ?>
		<?php /* The section heading sits one level above the questions. */ ?>
		<?php $rl_section_level = ( 'h3' === $rl_level ) ? 'h2' : 'h2'; ?>
		<<?php echo esc_attr( $rl_section_level ); ?> class="rl-faq__heading">
			<?php echo esc_html( $rl_heading ); ?>
		</<?php echo esc_attr( $rl_section_level ); ?>>
	<?php endif; ?>

	<div class="rl-faq__list">
		<?php
		while ( $rl_faqs->have_posts() ) :
			$rl_faqs->the_post();

			$rl_question = get_the_title();
			$rl_short    = (string) get_post_meta( get_the_ID(), '_rl_short_answer', true );
			$rl_body     = trim( (string) apply_filters( 'the_content', get_the_content() ) );

			// Schema gets exactly what a reader sees: the direct answer plus
			// the elaboration, in that order.
			$rl_schema_text = trim( $rl_short . ' ' . wp_strip_all_tags( $rl_body ) );

			if ( ! empty( $atts['emit_schema'] ) ) {
				\RobertsLaw\Schema::add_faq( $rl_question, $rl_schema_text );
			}
			?>
			<article class="rl-faq__item">
				<<?php echo esc_attr( $rl_level ); ?> class="rl-faq__question">
					<?php echo esc_html( $rl_question ); ?>
				</<?php echo esc_attr( $rl_level ); ?>>

				<div class="rl-faq__answer">
					<?php if ( '' !== trim( $rl_short ) ) : ?>
						<p class="rl-faq__direct"><?php echo esc_html( $rl_short ); ?></p>
					<?php endif; ?>

					<?php echo wp_kses_post( $rl_body ); ?>
				</div>
			</article>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
