<?php
/**
 * Prose.
 *
 * Body copy. The page's single H1 lives in the hero, so headings here start at
 * H2 — a stray H1 in prose is the most common way a page ends up with two.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_content = (string) ( $atts['content'] ?? '' );

if ( '' === trim( $rl_content ) ) {
	return;
}

/*
 * Demote any H1 that made it into body copy rather than rendering a second one.
 * Silently correcting content is normally the wrong instinct, but heading order
 * is structural rather than editorial: nothing an author meant to say is lost,
 * and a duplicate H1 breaks both the outline and the "one H1 per page" rule in
 * CLAUDE.md.
 */
if ( false !== stripos( $rl_content, '<h1' ) ) {
	$rl_content = preg_replace( '/<(\/?)h1(\s|>)/i', '<$1h2$2', $rl_content );

	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<p class="rl-editor-note">%s</p>',
			esc_html__( 'Editor note: an H1 in this prose block was rendered as an H2. The page H1 belongs to the hero — change it in the source content.', 'robertslaw' )
		);
	}
}

// Shortcodes are expanded so [rl_firm field="phone_display"] resolves inside
// body copy, which is how a phone number in prose stays tied to config/firm.php.
$rl_content = do_shortcode( $rl_content );
?>
<div id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'rich-text', $atts, array( 'rl-rich-text--' . sanitize_html_class( $atts['measure'] ?? 'default' ) ) ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">
	<?php echo wp_kses_post( $rl_content ); ?>
</div>
