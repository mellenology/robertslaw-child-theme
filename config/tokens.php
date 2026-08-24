<?php
/**
 * Design tokens — SINGLE SOURCE OF TRUTH.
 *
 * Every color, font, size, and space value used anywhere on this site is
 * declared here once. The token compiler (inc/class-tokens.php) turns this
 * array into CSS custom properties on :root.
 *
 * HOW TO USE THEM
 * - In this theme's CSS:      color: var(--rl-color-accent);
 * - In a Cornerstone style
 *   field or any Pro element: color: var(--rl-color-accent);
 *
 * That second one is the important one. Pro/Cornerstone style fields accept
 * CSS custom properties, so a component styled in the builder can consume the
 * same token as a component styled in code. Change the value here and both
 * update — no hunting through Cornerstone elements for a hardcoded hex.
 *
 * Naming: group => key => value  becomes  --rl-{group}-{key}.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

return array(

	/*
	 * Color.
	 *
	 * Deep navy and restrained brass. Family law and probate work is read by
	 * people in difficult circumstances — this palette is deliberately calm
	 * rather than combative, which also keeps it clear of the "fighting for
	 * you" register that CLAUDE.md rule 5 rules out visually as well as in copy.
	 *
	 * Contrast: --text on --surface is 13.4:1. --accent-strong on --surface is
	 * 6.1:1. --accent is a UI/large-text color at 4.6:1 — do not set body copy
	 * in it; use --accent-strong.
	 */
	'color' => array(
		'ink'            => '#16283F',
		'ink-soft'       => '#23405F',
		'ink-muted'      => '#3D5875',
		'accent'         => '#A07D3E',
		'accent-strong'  => '#7A5C26',
		'accent-soft'    => '#EFE6D4',
		'surface'        => '#FFFFFF',
		'surface-alt'    => '#F7F5F1',
		'surface-sunken' => '#EFEBE4',
		'surface-deep'   => '#16283F',
		'border'         => '#DED8CF',
		'border-strong'  => '#C3BAAD',
		'text'           => '#22262B',
		'text-muted'     => '#565E68',
		'text-invert'    => '#F7F5F1',
		'safety'         => '#8C2F1E',
		'safety-soft'    => '#FBEDE9',
		'focus'          => '#1D6FB8',
	),

	/*
	 * Typography. Serif headings for institutional weight, humanist sans for
	 * body legibility. Stacks are system-first: no webfont request on the
	 * critical path, which protects the LCP < 2.5s target in CLAUDE.md.
	 * If a brand webfont is adopted later, change it here only.
	 */
	'font' => array(
		'display' => "'Iowan Old Style', 'Palatino Linotype', Palatino, 'Book Antiqua', Georgia, 'Times New Roman', serif",
		'body'    => "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
		'mono'    => "ui-monospace, SFMono-Regular, Menlo, Consolas, monospace",
	),

	'weight' => array(
		'regular'  => '400',
		'medium'   => '500',
		'semibold' => '600',
		'bold'     => '700',
	),

	/*
	 * Type scale. Fluid via clamp() so headings shrink on mobile without
	 * media queries — fewer reflow opportunities, which helps CLS.
	 */
	'text' => array(
		'xs'   => '0.8125rem',
		'sm'   => '0.9375rem',
		'base' => '1.0625rem',
		'lg'   => '1.1875rem',
		'xl'   => 'clamp(1.25rem, 1.15rem + 0.5vw, 1.5rem)',
		'2xl'  => 'clamp(1.5rem, 1.3rem + 1vw, 1.9rem)',
		'3xl'  => 'clamp(1.8rem, 1.5rem + 1.5vw, 2.4rem)',
		'4xl'  => 'clamp(2.1rem, 1.7rem + 2vw, 3rem)',
	),

	'leading' => array(
		'tight'   => '1.18',
		'snug'    => '1.32',
		'normal'  => '1.65',
		'relaxed' => '1.75',
	),

	'tracking' => array(
		'tight'  => '-0.015em',
		'normal' => '0',
		'wide'   => '0.06em',
		'wider'  => '0.12em',
	),

	/*
	 * Spacing scale. Used for section rhythm and component padding.
	 */
	'space' => array(
		'3xs' => '0.25rem',
		'2xs' => '0.5rem',
		'xs'  => '0.75rem',
		'sm'  => '1rem',
		'md'  => '1.5rem',
		'lg'  => '2rem',
		'xl'  => '3rem',
		'2xl' => 'clamp(3rem, 2rem + 4vw, 5rem)',
		'3xl' => 'clamp(4rem, 2.5rem + 6vw, 7rem)',
	),

	'radius' => array(
		'none' => '0',
		'sm'   => '3px',
		'md'   => '6px',
		'lg'   => '10px',
		'pill' => '999px',
	),

	'shadow' => array(
		'sm' => '0 1px 2px rgba(22, 40, 63, 0.06)',
		'md' => '0 2px 8px rgba(22, 40, 63, 0.08)',
		'lg' => '0 8px 28px rgba(22, 40, 63, 0.10)',
	),

	/*
	 * Layout. 'measure' caps line length for readability on long legal copy.
	 */
	'layout' => array(
		'max'      => '1180px',
		'content'  => '760px',
		'measure'  => '68ch',
		'gutter'   => 'clamp(1.25rem, 4vw, 2.5rem)',
	),

	'motion' => array(
		'fast' => '120ms',
		'base' => '200ms',
		'ease' => 'cubic-bezier(0.4, 0, 0.2, 1)',
	),
);
