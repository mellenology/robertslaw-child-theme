<?php
/**
 * Legal Disclaimer.
 *
 * The text comes from config/compliance.php and is deliberately not editable
 * in the builder. CLAUDE.md rule 6: the disclaimer appears on every page and
 * the exact wording must not be paraphrased.
 *
 * The footer prints this automatically. Use the component only to place it
 * somewhere additional, such as beside a contact form.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_variant = (string) ( $atts['variant'] ?? 'default' );
$rl_text    = \RobertsLaw\Compliance::disclaimer( $rl_variant );

if ( '' === trim( $rl_text ) ) {
	return;
}
?>
<aside id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'disclaimer', $atts, array( 'rl-disclaimer--' . sanitize_html_class( $rl_variant ) ) ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>">
	<p class="rl-disclaimer__text"><?php echo esc_html( $rl_text ); ?></p>
</aside>
