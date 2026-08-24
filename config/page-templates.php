<?php
/**
 * Page blueprints — SINGLE SOURCE OF TRUTH for page composition.
 *
 * One entry per page describing the exact ordered stack of sections, rows,
 * columns, and components, with the copy already placed. This is the layout
 * itself, held as data rather than as a builder document, which means:
 *
 *   - `bin/build-tco.php` serialises it to a Themeco `.tco` template
 *   - `RobertsLaw\Page_Template::render()` renders it directly in PHP
 *   - it can be reviewed in a diff before anyone opens Cornerstone
 *
 * STRUCTURE
 *
 *   'page-key' => array(
 *       'review_required' => bool   Any copy below still needs attorney review.
 *       'sections' => array(
 *           array(
 *               'label'      => string   Section name shown in the builder.
 *               'background' => surface|alt|sunken|deep|accent-soft
 *               'spacing'    => sm|md|lg|xl
 *               'width'      => contained|full
 *               'rows'       => array(
 *                   array(
 *                       'columns' => array(
 *                           array(
 *                               'width'      => '1/1'|'1/2'|'1/3'|'2/3'|'1/4'|'3/4'
 *                               'components' => array(
 *                                   array( 'component' => slug, 'atts' => array() ),
 *                               ),
 *                           ),
 *                       ),
 *                   ),
 *               ),
 *           ),
 *       ),
 *   )
 *
 * COPY PROVENANCE
 *
 * Every block of prose carries a 'source' note in the surrounding comment:
 *
 *   APPROVED  — verbatim or lightly trimmed from content/website-copy.md
 *   DRAFT     — written here, NOT reviewed, must not publish as-is
 *   BLOCKED   — cannot be written until a client decision lands
 *
 * CLAUDE.md: "If a page needs copy that isn't in those files, draft it and flag
 * it for review. Never publish placeholder copy as if it were final." Anything
 * marked DRAFT is exactly that — a starting point for Joni to edit, not
 * finished copy. Legal specifics are only asserted where the source documents
 * already assert them; where a target question has no sourced answer, the slot
 * says so rather than inventing Tennessee law.
 *
 * @package RobertsLaw
 */

defined( 'ABSPATH' ) || exit;

return array(

	/* =====================================================================
	 * HOME  —  /
	 * Copy status: APPROVED (content/website-copy.md, section 1B)
	 * ================================================================== */
	'home' => array(
		'review_required' => false,
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'xl',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'hero',
										'atts'      => array(
											// H1 left empty so it inherits from config/pages.php.
											'heading'      => '',
											'eyebrow'      => 'Memphis, Tennessee',
											// APPROVED. The most important block on the site —
											// self-contained, factual, entity-dense. This is what
											// an AI assistant lifts when asked to name a Memphis
											// attorney or mediator.
											'answer'       => 'Joni K. Roberts Law and Mediation Office is a Memphis, Tennessee law firm handling family law, probate and estate planning, and general civil litigation, with mediation offered as an alternative to litigation across all three areas. Attorney Joni K. Roberts has practiced law in Tennessee since 2004 and has represented clients in General Sessions, Circuit, Chancery, Probate, and Federal District Court, as well as on appeal to the United States Court of Appeals for the Sixth Circuit. The firm serves clients in Memphis and Shelby County.',
											'show_actions' => 'true',
											'variant'      => 'default',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'What the firm does',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '2/3',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// APPROVED — website-copy.md 1B.
											'content' => '<h2>What does Joni K. Roberts Law and Mediation Office do?</h2>'
												. '<p>The firm practices in three areas: family law, probate and estate planning, and general civil litigation. Joni K. Roberts handles matters both as an advocate in court and as a neutral mediator, which means clients can pursue either a negotiated resolution or litigation depending on what the situation requires.</p>'
												. '<h2>Why work with an attorney who also mediates?</h2>'
												. '<p>Joni K. Roberts practices as an advocate in court and also serves as a neutral mediator, which means she has seen the same kinds of disputes resolve on both paths. Clients get a candid read on whether a matter is likely to settle or likely to be tried, rather than an assessment shaped by a single practice model.</p>',
										),
									),
								),
							),
							array(
								'width'      => '1/3',
								'components' => array(
									array(
										'component' => 'contact-card',
										'atts'      => array(
											'heading'      => 'Speak with the office',
											'show_address' => 'true',
											'show_hours'   => 'true',
											'layout'       => 'stacked',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Practice areas',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'service-grid',
										'atts'      => array(
											'heading' => 'Legal Services',
											// All silos on the home page; hubs filter to their own.
											'silo'    => '',
											'limit'   => '9',
											'columns' => '3',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Family law detail',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// APPROVED — website-copy.md 1B. The 60/90-day waiting
											// period and the income-shares reference are the kind of
											// concrete jurisdictional facts that get retrieved and
											// cited, which is why they lead rather than trail.
											'content' => '<h2>What family law matters does the firm handle?</h2>'
												. '<p>Joni K. Roberts handles divorce, parenting time and parenting plans, child support, post-divorce modification, adoption, paternity, and domestic violence protection orders in Memphis and Shelby County.</p>'
												. '<ul>'
												. '<li><strong>Divorce.</strong> Representation in contested and uncontested divorce in Tennessee, including property division, alimony, and grounds. Tennessee requires a 60-day waiting period for divorcing couples without minor children and 90 days for couples with minor children.</li>'
												. '<li><strong>Parenting time.</strong> Tennessee courts issue a permanent parenting plan setting each parent&rsquo;s residential schedule and decision-making authority. The firm helps parents propose, negotiate, and modify these plans.</li>'
												. '<li><strong>Child support.</strong> Calculated under the Tennessee Child Support Guidelines using both parents&rsquo; incomes and the number of days each parent spends with the child.</li>'
												. '<li><strong>Adoption.</strong> Step-parent, relative, and agency adoptions, including termination of parental rights where required.</li>'
												. '<li><strong>Paternity.</strong> Establishing or disputing parentage, and the parenting and support orders that follow.</li>'
												. '<li><strong>Domestic violence.</strong> Petitions for orders of protection in Shelby County, and defense against petitions.</li>'
												. '</ul>'
												. '<h2>What probate and estate planning work does the firm do?</h2>'
												. '<p>Joni K. Roberts handles wills, trusts, estates, conservatorships, and guardianships, and has served as Guardian ad Litem in Tennessee probate court.</p>'
												. '<h2>What is mediation, and how is it different from going to court?</h2>'
												. '<p>Mediation is a voluntary, informal process in which a neutral third party helps disputing parties negotiate their own settlement. Unlike a judge, a mediator does not decide the case or rule on the issues &mdash; the parties keep control of the outcome.</p>',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Attorney',
				'background' => 'sunken',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'attorney-card',
										'atts'      => array(
											// APPROVED — condensed from website-copy.md 3B.
											'summary'          => 'Joni K. Roberts is an attorney in Memphis, Tennessee, admitted to the Tennessee bar in 2004. Her practice covers family law, probate law, and general civil litigation, and she also serves as a neutral mediator.',
											'photo'            => '',
											'show_credentials' => 'true',
											'show_courts'      => 'true',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Consultation',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// APPROVED — website-copy.md 1B.
											'content' => '<h2>How do I schedule a consultation?</h2>'
												. '<p>Call (901) 800-2948, email jkroberts@robertslawandmediation.com, or complete the consultation form on this page. Submitting the form does not create an attorney-client relationship.</p>',
										),
									),
									array(
										'component' => 'disclaimer',
										'atts'      => array( 'variant' => 'form' ),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'xl',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											// APPROVED, results language removed. The source draft
											// ended "and a favorable resolution", which implies an
											// outcome — CLAUDE.md rule 5. Trimmed to "peace of mind".
											'heading'       => 'Ready to take the next step in resolving your legal matters?',
											'body'          => 'Whether you are navigating family law or estate planning issues, seeking mediation, or facing another legal question, the office is here to help you understand your options. Contact us to schedule a consultation.',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * ABOUT  —  /about/
	 * Copy status: APPROVED (content/website-copy.md, section 2B)
	 * ================================================================== */
	'about' => array(
		'review_required' => false,
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'breadcrumbs',
										'atts'      => array( 'emit_schema' => 'true' ),
									),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// APPROVED — website-copy.md 2B.
											'answer'       => 'Joni K. Roberts Law and Mediation Office is a Memphis, Tennessee law firm founded by attorney Joni K. Roberts, who has been licensed to practice in Tennessee since 2004. The firm focuses on mediation, estate planning, and general civil matters, and offers both litigation representation and neutral mediation services.',
											'show_actions' => 'true',
											'variant'      => 'plain',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Body',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '2/3',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// APPROVED — website-copy.md 2B, with the 2A narrative
											// folded in. Note "handles" throughout: the source draft
											// said "We specialize in a range of legal services",
											// which RPC 7.4 restricts absent certification.
											'content' => '<h2>Who founded Joni K. Roberts Law and Mediation Office?</h2>'
												. '<p>Attorney Joni K. Roberts founded the firm. She earned her J.D. from the Cecil C. Humphreys School of Law in 2003 and was admitted to the Tennessee bar in 2004. She has since practiced in the state and federal trial courts serving Memphis and Shelby County, and has handled an appeal before the United States Court of Appeals for the Sixth Circuit.</p>'
												. '<h2>What makes a firm that does both litigation and mediation different?</h2>'
												. '<p>Most attorneys do one or the other. Joni K. Roberts practices as an advocate in court and serves as a neutral mediator, which means she has seen how the same dispute plays out on both paths. Clients get a realistic assessment of whether their matter is likely to settle or likely to be tried, rather than a recommendation shaped by a single practice model.</p>'
												. '<h2>What areas of law does the firm handle?</h2>'
												. '<p>Family law, estate planning and probate, and general civil matters, with mediation available across all three.</p>'
												. '<h2>What courts does the firm practice in?</h2>'
												. '<p>General Sessions Court, Circuit Court, Chancery Court, Probate Court, and Federal District Court, plus appellate work before the United States Court of Appeals for the Sixth Circuit.</p>'
												. '<h2>How do I contact the firm?</h2>'
												. '<p>Call (901) 800-2948 or email jkroberts@robertslawandmediation.com. The office is located in Memphis, Tennessee.</p>',
										),
									),
								),
							),
							array(
								'width'      => '1/3',
								'components' => array(
									array(
										'component' => 'attorney-card',
										'atts'      => array(
											'summary'          => '',
											'photo'            => '',
											'show_credentials' => 'true',
											'show_courts'      => 'false',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											'heading'       => 'Discuss your situation with the office',
											'body'          => 'Call or email to arrange a consultation. Submitting a form or sending an email does not create an attorney-client relationship.',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * ATTORNEY PROFILE  —  /joni-k-roberts/
	 * Copy status: APPROVED (content/website-copy.md, section 3B)
	 *
	 * The architecture doc calls this the page most likely to be cited when an
	 * assistant is asked to name a Memphis attorney or mediator. Name,
	 * credentials, and phone number must match the home page and the schema
	 * exactly — they do, because all three read from config/firm.php.
	 * ================================================================== */
	'joni-k-roberts' => array(
		'review_required' => false,
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'breadcrumbs',
										'atts'      => array( 'emit_schema' => 'true' ),
									),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => 'Attorney and Mediator',
											// APPROVED — website-copy.md 3B.
											'answer'       => 'Joni K. Roberts is an attorney in Memphis, Tennessee, admitted to the Tennessee bar in 2004. Her practice covers family law, probate law, and general civil litigation, and she also serves as a neutral mediator. She has represented clients in General Sessions, Circuit, Chancery, Probate, and Federal District Court, and argued a successful appeal before the United States Court of Appeals for the Sixth Circuit in a First Amendment civil rights case.',
											'show_actions' => 'true',
											'variant'      => 'plain',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Practice and courts',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '2/3',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// APPROVED — website-copy.md 3B.
											//
											// "over twenty years of practice" is in the source
											// document and follows arithmetically from the 2004
											// admission date, so it is kept. Note it is the ONLY
											// duration claim on the site; CLAUDE.md rule 2 forbids
											// adding years-of-experience claims not in the sources.
											'content' => '<h2>What is Joni K. Roberts&rsquo; experience?</h2>'
												. '<p>Licensed in Tennessee since 2004 &mdash; over twenty years of practice across family law, probate, and general civil litigation, in both state trial courts and federal court.</p>'
												. '<h2>What does Joni K. Roberts practice?</h2>'
												. '<ul>'
												. '<li><strong>Family law:</strong> adoptions, child support, divorce, post-divorce matters, and parenting time</li>'
												. '<li><strong>Probate law:</strong> conservatorships, guardianships, estates, trusts, and wills</li>'
												. '<li><strong>Civil litigation:</strong> general civil matters in state and federal court</li>'
												. '</ul>'
												. '<h2>What courts has Joni K. Roberts appeared in?</h2>'
												. '<p>General Sessions Court, Circuit Court, Chancery Court, Probate Court, and Federal District Court, as well as the United States Court of Appeals for the Sixth Circuit.</p>'
												. '<h2>What appointments and pro bono work has she done?</h2>'
												. '<p>Ms. Roberts has been appointed Guardian <em>ad Litem</em> in Tennessee probate court and has served as <em>pro bono</em> counsel on a variety of matters through Memphis Area Legal Services.</p>'
												. '<h2>Where did Joni K. Roberts go to law school?</h2>'
												. '<p>She earned her J.D. from the Cecil C. Humphreys School of Law at the University of Memphis in 2003, where she served as Law Review Research Editor and received the CALI Award in International Law. She also holds an M.A.T. from the University of Memphis (1995) and a B.A. from the University of California, Davis (1989).</p>',
										),
									),
								),
							),
							array(
								'width'      => '1/3',
								'components' => array(
									array(
										'component' => 'attorney-card',
										'atts'      => array(
											'summary'          => '',
											'photo'            => '',
											'show_credentials' => 'true',
											'show_courts'      => 'false',
										),
									),
									array(
										'component' => 'contact-card',
										'atts'      => array(
											'heading'      => 'Contact',
											'show_address' => 'true',
											'show_hours'   => 'true',
											'layout'       => 'stacked',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											'heading'       => 'Speak with Joni K. Roberts',
											'body'          => '',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * FAMILY LAW — silo hub  —  /family-law/
	 * Copy status: DRAFT. The answer block is assembled from facts already
	 * asserted in content/website-copy.md 1B; the FAQ answers are not written
	 * here because they are legal substance, and CLAUDE.md requires those to be
	 * drafted and flagged rather than invented. Add them as FAQ entries.
	 * ================================================================== */
	'family-law' => array(
		'review_required' => true,
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'breadcrumbs',
										'atts'      => array( 'emit_schema' => 'true' ),
									),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// DRAFT — recombined from approved facts in 1B.
											'answer'       => 'Joni K. Roberts Law and Mediation Office handles family law matters in Memphis and Shelby County, Tennessee, including divorce, parenting time and parenting plans, child support, post-divorce modification, adoption, paternity, and orders of protection. Attorney Joni K. Roberts has practiced in Tennessee since 2004 and offers mediation as an alternative to litigation in family matters.',
											'show_actions' => 'true',
											'variant'      => 'default',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Family law services',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'service-grid',
										'atts'      => array(
											'heading' => 'Family law matters the firm handles',
											'silo'    => 'family-law',
											'limit'   => '8',
											'columns' => '3',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Overview',
				'background' => 'surface',
				'spacing'    => 'md',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// DRAFT — the H2s are the target questions from
											// content/site-architecture.md. The first answer draws
											// only on facts already in the approved copy. The
											// remaining two need Joni's input: one is a fee
											// statement (a CLAUDE.md stop-and-ask item) and one
											// is legal advice.
											'content' => '<h2>What does a family law attorney do?</h2>'
												. '<p>A family law attorney represents people in matters involving marriage, children, and the financial consequences of separation &mdash; divorce, parenting plans, child support, adoption, paternity, and protection orders. Joni K. Roberts handles these matters in Memphis and Shelby County both as an advocate in court and as a neutral mediator.</p>'
												. '<h2>How much does a family lawyer cost in Memphis?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; fee structure, and whether consultations are free or paid. Fee statements are on the CLAUDE.md stop-and-ask list. Cost is the single most common unanswered question in legal search, so this is worth answering even in general terms.]</p>'
												. '<h2>Do I need a lawyer for an uncontested divorce in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; this is legal advice and must be written or approved by Joni. Reference points already on the site: Tennessee&rsquo;s 60-day waiting period without minor children and 90 days with them.]</p>',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'FAQ',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'faq',
										'atts'      => array(
											'heading'       => 'Family law questions',
											'group'         => 'divorce',
											'heading_level' => 'h2',
											'emit_schema'   => 'true',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											'heading'       => 'Talk through your family law matter',
											'body'          => 'Call or email the office to arrange a consultation.',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * DIVORCE  —  /divorce/
	 * Copy status: DRAFT. Highest-volume page on the site; the architecture
	 * doc gives it a 1,200-1,800 word target and six target questions. Only the
	 * waiting-period fact is sourced — the rest is flagged.
	 * ================================================================== */
	'divorce' => array(
		'review_required' => true,
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'breadcrumbs',
										'atts'      => array( 'emit_schema' => 'true' ),
									),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// DRAFT — leads with the sourced jurisdictional fact,
											// which is exactly the kind of concrete detail that
											// gets retrieved and cited.
											'answer'       => 'Joni K. Roberts Law and Mediation Office represents clients in contested and uncontested divorce in Memphis and Shelby County, Tennessee, including property division, alimony, and grounds for divorce. Tennessee requires a 60-day waiting period for divorcing couples without minor children and 90 days for couples with minor children. Divorce mediation is available as an alternative to a contested trial.',
											'show_actions' => 'true',
											'variant'      => 'default',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Body',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '2/3',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// DRAFT. H2s are verbatim target questions from the
											// architecture doc — keep them phrased as questions,
											// because that is how people type them.
											//
											// Each answer must open with one direct sentence before
											// elaborating. Do NOT let an answer start with
											// "It depends" — that is the one opening that
											// guarantees the passage is never quoted.
											'content' => '<h2>How long does a divorce take in Tennessee?</h2>'
												. '<p>Tennessee requires a waiting period of 60 days for divorcing couples without minor children and 90 days for couples with minor children, measured from the date the complaint is filed. [NEEDS ATTORNEY INPUT &mdash; expand on what lengthens a contested case in Shelby County.]</p>'
												. '<h2>What are the grounds for divorce in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; irreconcilable differences and the fault grounds. Legal substance; must be written or approved by Joni.]</p>'
												. '<h2>Is Tennessee a 50/50 state?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; equitable distribution rather than community property. High-volume query with a widespread misconception behind it, so worth answering precisely.]</p>'
												. '<h2>Do I have to go to court for a divorce in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; and link through to divorce mediation, which is the firm&rsquo;s genuine differentiator on this question.]</p>'
												. '<h2>Can I get a divorce without my spouse agreeing?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; legal substance.]</p>'
												. '<h2>How much does a divorce cost in Memphis?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; fee statement, CLAUDE.md stop-and-ask item.]</p>',
										),
									),
								),
							),
							array(
								'width'      => '1/3',
								'components' => array(
									array(
										'component' => 'contact-card',
										'atts'      => array(
											'heading'      => 'Discuss your divorce',
											'show_address' => 'false',
											'show_hours'   => 'true',
											'layout'       => 'stacked',
										),
									),
									array(
										'component' => 'attorney-card',
										'atts'      => array(
											'summary'          => '',
											'photo'            => '',
											'show_credentials' => 'false',
											'show_courts'      => 'false',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'FAQ',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'faq',
										'atts'      => array(
											'heading'       => 'Divorce questions',
											'group'         => 'divorce',
											'heading_level' => 'h2',
											'emit_schema'   => 'true',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											'heading'       => 'Considering a divorce?',
											'body'          => 'Call or email to talk through your options, including whether mediation may suit your situation.',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * PARENTING TIME  —  /parenting-time/
	 * Copy status: DRAFT.
	 *
	 * Naming resolved per the architecture doc: URL and title tag carry "child
	 * custody" because that is what people search; the H1 and body use
	 * "parenting time" because that is what Tennessee courts and the client
	 * actually say. Both audiences served — do not "fix" this to match.
	 * ================================================================== */
	'parenting-time' => array(
		'review_required' => true,
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array( 'component' => 'breadcrumbs', 'atts' => array( 'emit_schema' => 'true' ) ),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// DRAFT — built on the sourced parenting-plan fact.
											'answer'       => 'Tennessee courts issue a permanent parenting plan setting each parent\'s residential schedule and decision-making authority. Joni K. Roberts Law and Mediation Office helps parents in Memphis and Shelby County propose, negotiate, and modify these plans, and offers parenting plan mediation as an alternative to a contested hearing.',
											'show_actions' => 'true',
											'variant'      => 'default',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Body',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '2/3',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// DRAFT. H2s are the target questions from the
											// architecture doc.
											'content' => '<h2>What is a permanent parenting plan in Tennessee?</h2>'
												. '<p>A permanent parenting plan is the court order that sets each parent\'s residential schedule with the child and allocates decision-making authority between the parents. [NEEDS ATTORNEY INPUT &mdash; expand: what the plan must contain, and how it is entered.]</p>'
												. '<h2>What is the difference between custody and parenting time?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; this question is the reason the page carries both terms, so the answer matters. Explain the terminology shift plainly.]</p>'
												. '<h2>How is custody decided in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; best-interest factors. Legal substance.]</p>'
												. '<h2>Can a parent move out of state with a child?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; parental relocation. Legal substance.]</p>'
												. '<h2>At what age can a child choose which parent to live with in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; high-volume query with a widespread misconception behind it. Worth answering precisely.]</p>',
										),
									),
								),
							),
							array(
								'width'      => '1/3',
								'components' => array(
									array(
										'component' => 'contact-card',
										'atts'      => array(
											'heading'      => 'Discuss your parenting plan',
											'show_address' => 'false',
											'show_hours'   => 'true',
											'layout'       => 'stacked',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'FAQ',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'faq',
										'atts'      => array(
											'heading'       => 'Parenting time questions',
											'group'         => 'parenting-time',
											'heading_level' => 'h2',
											'emit_schema'   => 'true',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											'heading'       => 'Questions about a parenting plan?',
											'body'          => '',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * MEDIATION — silo hub  —  /mediation/
	 *
	 * BLOCKED. The Rule 31 credential claims were removed sitewide, so this
	 * page must describe the service without implying a Rule 31 listing. Under
	 * RPC 7.1 the test is not only what is said but whether the communication
	 * omits a fact necessary to keep it from being materially misleading.
	 *
	 * The structure below is safe to build now; the copy slot stays empty until
	 * the framing decision lands. Do NOT fill it by paraphrasing the old copy.
	 * ================================================================== */
	'mediation' => array(
		'review_required' => true,
		'blocked'         => 'Rule 31 framing fix. See the FLAG in content/website-copy.md section 4B.',
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array( 'component' => 'breadcrumbs', 'atts' => array( 'emit_schema' => 'true' ) ),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// DRAFT, deliberately conservative: describes what
											// mediation is and that the firm offers it, with no
											// statement or implication about Rule 31 listing.
											'answer'       => 'Joni K. Roberts Law and Mediation Office offers mediation in Memphis and Shelby County, Tennessee as an alternative to litigation, including divorce mediation, parenting plan mediation, property division mediation, and general family dispute resolution. Mediation is a voluntary, informal process in which a neutral third party helps the parties negotiate their own settlement rather than having a judge decide the outcome.',
											'show_actions' => 'true',
											'variant'      => 'default',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Mediation services',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'service-grid',
										'atts'      => array(
											'heading' => 'Types of mediation the firm offers',
											'silo'    => 'mediation',
											'limit'   => '6',
											'columns' => '3',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'How mediation works',
				'background' => 'surface',
				'spacing'    => 'md',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// BLOCKED — awaiting the framing decision.
											'content' => '<p>[BLOCKED &mdash; awaiting the Rule 31 framing decision. The recommended approach in content/website-copy.md is to frame this as general client education, with an explicit line such as &ldquo;Tennessee courts often order parties to attend Rule 31 mediation. Here is what that process involves.&rdquo; That keeps the search value and removes the implication that the firm holds a Rule 31 listing. Do not fill this in by paraphrasing the removed copy.]</p>',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											'heading'       => 'Considering mediation?',
											'body'          => 'Call or email the office to ask whether mediation may suit your situation.',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * MEDIATION FAQ  —  /mediation-faq/
	 *
	 * BLOCKED on the same framing fix, plus the ten additional questions listed
	 * in content/website-copy.md. Highest AI-retrieval potential on the site.
	 *
	 * Answers live as FAQ entries in the 'mediation' group, not in this
	 * blueprint — that keeps the visible answer and the FAQPage schema text
	 * identical by construction.
	 * ================================================================== */
	'mediation-faq' => array(
		'review_required' => true,
		'blocked'         => 'Rule 31 framing fix; ten additional questions; verify the Rule 31 training figures against current ADR Commission standards ("46 hours" in the source is likely a typo for 40).',
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array( 'component' => 'breadcrumbs', 'atts' => array( 'emit_schema' => 'true' ) ),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// DRAFT — states the process and the compel/settle
											// distinction, both sourced, without asserting anything
											// about the firm's own listings.
											'answer'       => 'Mediation in Tennessee is a confidential, voluntary process in which a neutral mediator helps the parties negotiate their own settlement rather than having a judge decide the outcome. A Tennessee court can order parties to attend mediation, but no party can be forced to settle.',
											'show_actions' => 'false',
											'variant'      => 'plain',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Questions',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '2/3',
								'components' => array(
									array(
										'component' => 'faq',
										'atts'      => array(
											'heading'       => '',
											'group'         => 'mediation',
											'heading_level' => 'h2',
											'emit_schema'   => 'true',
										),
									),
									// Recency and named authorship are both AI-retrieval signals
									// on legal content. The architecture doc asks for this
									// explicitly on this page.
									array(
										'component' => 'last-reviewed',
										'atts'      => array(
											'date'        => '',
											'show_author' => 'true',
										),
									),
								),
							),
							array(
								'width'      => '1/3',
								'components' => array(
									array(
										'component' => 'contact-card',
										'atts'      => array(
											'heading'      => 'Ask about mediation',
											'show_address' => 'false',
											'show_hours'   => 'true',
											'layout'       => 'stacked',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * PROBATE AND ESTATE PLANNING — silo hub  —  /probate-estate-planning/
	 * Copy status: DRAFT.
	 *
	 * The architecture doc calls this the most underbuilt opportunity on the
	 * site: competition in Memphis is thinner than family law, and the Guardian
	 * ad Litem appointments are a genuine, verifiable differentiator here.
	 * ================================================================== */
	'probate-estate-planning' => array(
		'review_required' => true,
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array( 'component' => 'breadcrumbs', 'atts' => array( 'emit_schema' => 'true' ) ),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// DRAFT — every fact here is asserted in the approved
											// copy, including the Guardian ad Litem appointments.
											'answer'       => 'Joni K. Roberts Law and Mediation Office handles wills, trusts, estates, conservatorships, and guardianships in Memphis and Shelby County, Tennessee. Attorney Joni K. Roberts has been appointed Guardian ad Litem in Tennessee probate court and has represented clients in Probate Court since being admitted to the Tennessee bar in 2004.',
											'show_actions' => 'true',
											'variant'      => 'default',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Probate services',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'service-grid',
										'atts'      => array(
											'heading' => 'Probate and estate planning matters',
											'silo'    => 'probate',
											'limit'   => '6',
											'columns' => '3',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Overview',
				'background' => 'surface',
				'spacing'    => 'md',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// DRAFT. H2s are the target questions from the
											// architecture doc.
											'content' => '<h2>How long does probate take in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; legal substance.]</p>'
												. '<h2>What happens if someone dies without a will in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; intestate succession. Heavily searched and thinly answered by authoritative Tennessee sources, so this one is worth real depth.]</p>'
												. '<h2>Do I need a will in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; legal substance.]</p>'
												. '<h2>How much does probate cost in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; fee statement, CLAUDE.md stop-and-ask item.]</p>',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'FAQ',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'faq',
										'atts'      => array(
											'heading'       => 'Probate questions',
											'group'         => 'probate',
											'heading_level' => 'h2',
											'emit_schema'   => 'true',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Closing CTA',
				'background' => 'deep',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'cta-band',
										'atts'      => array(
											'heading'       => 'Planning an estate, or settling one?',
											'body'          => 'Call or email the office to discuss wills, trusts, probate administration, conservatorships, or guardianships.',
											'show_schedule' => 'true',
											'variant'       => 'deep',
										),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * CONTACT  —  /contact/
	 *
	 * BLOCKED on the street address and office hours. The structure is here and
	 * correct; the contact card and the schema will fill themselves in the
	 * moment those values are entered under Roberts Law → Firm Details.
	 * Until then they render as nothing rather than as a guess.
	 * ================================================================== */
	'contact' => array(
		'review_required' => false,
		'blocked'         => 'Street address and office hours outstanding from the client. Required for the page copy, PostalAddress schema, openingHoursSpecification, and Google Business Profile.',
		'sections'        => array(

			array(
				'label'      => 'Hero',
				'background' => 'alt',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array( 'component' => 'breadcrumbs', 'atts' => array( 'emit_schema' => 'true' ) ),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// APPROVED phrasing, drawn from website-copy.md 1B.
											'answer'       => 'Joni K. Roberts Law and Mediation Office is located in Memphis, Tennessee and serves clients in Memphis and Shelby County. Call (901) 800-2948, email jkroberts@robertslawandmediation.com, or complete the consultation form on this page. Submitting the form does not create an attorney-client relationship.',
											'show_actions' => 'true',
											'variant'      => 'plain',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Details and form',
				'background' => 'surface',
				'spacing'    => 'lg',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/2',
								'components' => array(
									array(
										'component' => 'contact-card',
										'atts'      => array(
											'heading'      => 'Office details',
											'show_address' => 'true',
											'show_hours'   => 'true',
											'layout'       => 'stacked',
										),
									),
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// The embedded map is deferred deliberately: an iframe
											// map is a heavy third-party embed and a real LCP/CLS
											// cost, and it cannot be placed accurately until the
											// street address exists. Add it with loading="lazy"
											// and explicit dimensions once the address lands.
											'content' => '<p>[MAP EMBED &mdash; blocked on the street address. Add with loading="lazy" and explicit width/height so it does not shift layout.]</p>',
										),
									),
								),
							),
							array(
								'width'      => '1/2',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											// Form fields are listed verbatim from
											// content/website-copy.md. Build with the site's form
											// plugin (Cornerstone Forms or Gravity Forms) — the
											// theme does not ship a form handler, because form
											// submissions on a law firm site carry confidentiality
											// obligations that belong in a maintained plugin rather
											// than in theme code.
											'content' => '<h2>Request a consultation</h2>'
												. '<p>[FORM &mdash; build in the site\'s form plugin with these fields, per content/website-copy.md: First Name; Last Name; Email Address; Phone Number; Preferred Method of Contact; Case Type / Service Inquiry (dropdown); Brief Case Description; How Did You Hear About Us?; Preferred Consultation Date/Time; Privacy Notice / Consent Checkbox; CAPTCHA or security question; Submit.]</p>',
										),
									),
									array(
										'component' => 'disclaimer',
										'atts'      => array( 'variant' => 'form' ),
									),
								),
							),
						),
					),
				),
			),
		),
	),

	/* =====================================================================
	 * ORDERS OF PROTECTION  —  /orders-of-protection/    (Phase 2)
	 *
	 * SENSITIVE PAGE. Someone reading this may be in immediate danger, and the
	 * page serves safety before marketing.
	 *
	 * The safety block is FIRST, before the H1 — that ordering is the point,
	 * and RobertsLaw\Guards injects it automatically even if this blueprint is
	 * edited. Testimonials are suppressed on this page at the render layer, so
	 * they cannot be added back by accident.
	 * ================================================================== */
	'orders-of-protection' => array(
		'review_required' => true,
		'sections'        => array(

			array(
				'label'      => 'Safety',
				'background' => 'surface',
				'spacing'    => 'sm',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'safety-exit',
										'atts'      => array( 'position' => 'inline' ),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Hero',
				'background' => 'surface',
				'spacing'    => 'md',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array( 'component' => 'breadcrumbs', 'atts' => array( 'emit_schema' => 'true' ) ),
									array(
										'component' => 'hero',
										'atts'      => array(
											'heading'      => '',
											'eyebrow'      => '',
											// DRAFT. Kept plain and practical. No results language,
											// nothing that reads as marketing to someone in crisis.
											'answer'       => 'Joni K. Roberts Law and Mediation Office represents petitioners seeking orders of protection in Shelby County, Tennessee, and people responding to a petition filed against them. Attorney Joni K. Roberts has practiced in Tennessee since 2004.',
											'show_actions' => 'true',
											'variant'      => 'plain',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Body',
				'background' => 'surface',
				'spacing'    => 'md',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'rich-text',
										'atts'      => array(
											'content' => '<h2>How do I get an order of protection in Shelby County?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; the practical filing steps in Shelby County. Write this as plainly as possible: name the courthouse, say what to bring, and say what happens on the same day. Someone reading this may be deciding whether to leave the house.]</p>'
												. '<h2>How long does an order of protection last in Tennessee?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; ex parte duration and the hearing that follows. Legal substance.]</p>'
												. '<h2>What if a petition has been filed against me?</h2>'
												. '<p>[NEEDS ATTORNEY INPUT &mdash; the firm also defends against petitions, per the approved copy. Say so plainly; respondents search for this and find almost nothing written for them.]</p>',
										),
									),
								),
							),
						),
					),
				),
			),

			array(
				'label'      => 'Contact',
				'background' => 'alt',
				'spacing'    => 'md',
				'width'      => 'contained',
				'rows'       => array(
					array(
						'columns' => array(
							array(
								'width'      => '1/1',
								'components' => array(
									array(
										'component' => 'contact-card',
										'atts'      => array(
											'heading'      => 'Speak with the office',
											'show_address' => 'false',
											'show_hours'   => 'true',
											'layout'       => 'inline',
										),
									),
								),
							),
						),
					),
				),
			),
			// No CTA band and no testimonials on this page, by design.
		),
	),
);
