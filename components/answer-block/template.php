<?php
/**
 * Answer Block.
 *
 * The passage an AI assistant lifts. Self-contained, factual, entity-dense.
 * Rendered as a <p> rather than a blockquote so extraction tools read it as
 * body prose, which is what it is.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_answer = trim( (string) ( $atts['answer'] ?? '' ) );

if ( '' === $rl_answer ) {
	return;
}

$rl_words = str_word_count( wp_strip_all_tags( $rl_answer ) );
?>
<div id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'answer-block', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">
	<p class="rl-answer-block__text"><?php echo wp_kses_post( $rl_answer ); ?></p>

	<?php
	/*
	 * Editor-only advisory. content/website-copy.md targets 25-50 words:
	 * long enough to be a real answer, short enough to survive extraction
	 * intact. Visitors never see this.
	 */
	if ( current_user_can( 'edit_posts' ) && ( $rl_words < 20 || $rl_words > 60 ) ) :
		?>
		<p class="rl-editor-note">
			<?php
			printf(
				/* translators: %d: word count. */
				esc_html__( 'Editor note: %d words. Target 25-50 — this is the passage assistants quote.', 'robertslaw' ),
				(int) $rl_words
			);
			?>
		</p>
	<?php endif; ?>
</div>
