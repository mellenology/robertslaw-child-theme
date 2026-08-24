<?php
/**
 * Safety Exit.
 *
 * For /orders-of-protection/ and any other page flagged 'sensitive' in
 * config/pages.php. CLAUDE.md: someone reading that page may be in immediate
 * danger, and the page should serve safety before marketing.
 *
 * Three things, all required:
 *   1. The National Domestic Violence Hotline, as a real click-to-call link.
 *   2. An honest warning that browsing history may be visible to others.
 *   3. A quick-exit control.
 *
 * The warning is deliberately worded so it does not overpromise. A quick-exit
 * button cannot erase browser history, and telling someone in danger that it
 * can would be worse than not offering one — so the copy says what it does and
 * points to private browsing and a safer device.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_position = ( 'fixed' === ( $atts['position'] ?? 'inline' ) ) ? 'fixed' : 'inline';
?>
<aside id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'safety-exit', $atts, array( 'rl-safety-exit--' . $rl_position ) ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>"
	role="region"
	aria-label="<?php esc_attr_e( 'Safety information', 'robertslaw' ); ?>">

	<div class="rl-safety-exit__inner">

		<p class="rl-safety-exit__hotline">
			<strong><?php esc_html_e( 'In immediate danger? Call 911.', 'robertslaw' ); ?></strong>
			<?php
			printf(
				/* translators: 1: hotline name, 2: hotline phone link. */
				esc_html__( '%1$s, free and confidential, 24 hours a day: %2$s', 'robertslaw' ),
				esc_html( \RobertsLaw\Firm::get( 'dv_hotline_name' ) ),
				'<a class="rl-safety-exit__tel" href="' . esc_url( \RobertsLaw\Firm::get( 'dv_hotline_href' ) ) . '">'
					. esc_html( \RobertsLaw\Firm::get( 'dv_hotline_display' ) ) . '</a>'
			);
			?>
		</p>

		<p class="rl-safety-exit__warning">
			<?php
			esc_html_e(
				'Someone else may be able to see the pages you visit, even after you close this one. Leaving this page does not remove it from your browser history. If you are worried about being monitored, use a private or incognito window, or a device the other person cannot reach — a library computer or a friend\'s phone.',
				'robertslaw'
			);
			?>
		</p>

		<p class="rl-safety-exit__actions">
			<?php
			/*
			 * A real link, not a bare button: if JavaScript does not run, this
			 * still navigates away. The script upgrades it to also open a
			 * decoy tab and replace this page in the history stack.
			 */
			?>
			<a class="rl-safety-exit__button"
				href="https://www.weather.com/"
				rel="noopener noreferrer"
				data-rl-quick-exit>
				<?php esc_html_e( 'Leave this site quickly', 'robertslaw' ); ?>
			</a>
			<span class="rl-safety-exit__hint">
				<?php esc_html_e( 'or press the Escape key three times', 'robertslaw' ); ?>
			</span>
		</p>

	</div>
</aside>
