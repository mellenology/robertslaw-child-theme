<?php
/**
 * Breadcrumbs.
 *
 * The trail is derived from the silo parents declared in config/pages.php, so
 * it always matches the architecture rather than the page tree someone happened
 * to build in WordPress.
 *
 * @package RobertsLaw
 * @var array $atts Resolved component attributes.
 */

defined( 'ABSPATH' ) || exit;

$rl_trail = \RobertsLaw\SEO::breadcrumb_trail();

if ( count( $rl_trail ) < 2 ) {
	return;
}

if ( ! empty( $atts['emit_schema'] ) ) {
	\RobertsLaw\Schema::set_breadcrumbs( $rl_trail );
}

$rl_last = count( $rl_trail ) - 1;
?>
<nav id="<?php echo esc_attr( $atts['id'] ); ?>"
	class="<?php echo \RobertsLaw\Components::root_class( 'breadcrumbs', $atts ); ?>"
	style="<?php echo esc_attr( $atts['style'] ); ?>"
	aria-label="<?php esc_attr_e( 'Breadcrumb', 'robertslaw' ); ?>">
	<ol class="rl-breadcrumbs__list">
		<?php foreach ( $rl_trail as $rl_index => $rl_crumb ) : ?>
			<li class="rl-breadcrumbs__item">
				<?php if ( $rl_index === $rl_last ) : ?>
					<span aria-current="page"><?php echo esc_html( $rl_crumb['name'] ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( $rl_crumb['url'] ); ?>"><?php echo esc_html( $rl_crumb['name'] ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
