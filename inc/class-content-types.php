<?php
/**
 * Registers post types and taxonomies from config/content-types.php.
 *
 * Also renders and saves the meta boxes for their fields, including the
 * testimonial consent gate.
 *
 * @package RobertsLaw
 */

namespace RobertsLaw;

defined( 'ABSPATH' ) || exit;

class Content_Types {

	/**
	 * Hook up registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'admin_init', array( __CLASS__, 'seed_terms' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
	}

	/**
	 * Register everything declared in config.
	 *
	 * @return void
	 */
	public static function register() {
		$config = Config::load( 'content-types' );

		foreach ( $config['post_types'] ?? array() as $slug => $def ) {
			register_post_type( $slug, self::post_type_args( $def ) );
		}

		foreach ( $config['taxonomies'] ?? array() as $slug => $def ) {
			register_taxonomy( $slug, $def['object_types'] ?? array(), self::taxonomy_args( $def ) );
		}
	}

	/**
	 * Build register_post_type() args from a config entry.
	 *
	 * @param array $def Config entry.
	 * @return array
	 */
	private static function post_type_args( array $def ) {
		$singular = $def['labels_singular'] ?? 'Item';
		$plural   = $def['labels_plural'] ?? 'Items';

		return array(
			'labels'       => array(
				'name'               => $plural,
				'singular_name'      => $singular,
				'add_new_item'       => sprintf( 'Add New %s', $singular ),
				'edit_item'          => sprintf( 'Edit %s', $singular ),
				'new_item'           => sprintf( 'New %s', $singular ),
				'view_item'          => sprintf( 'View %s', $singular ),
				'search_items'       => sprintf( 'Search %s', $plural ),
				'not_found'          => sprintf( 'No %s yet', strtolower( $plural ) ),
				'not_found_in_trash' => sprintf( 'No %s in the trash', strtolower( $plural ) ),
				'menu_name'          => $plural,
			),
			'public'       => $def['public'] ?? false,
			'show_ui'      => $def['show_ui'] ?? true,
			'show_in_rest' => $def['show_in_rest'] ?? false,
			'show_in_menu' => true,
			'menu_icon'    => $def['menu_icon'] ?? 'dashicons-admin-post',
			'menu_position' => $def['menu_position'] ?? 25,
			'supports'     => $def['supports'] ?? array( 'title', 'editor' ),
			'taxonomies'   => $def['taxonomies'] ?? array(),
			'has_archive'  => false,
			'rewrite'      => false,
			'query_var'    => false,
			'capability_type' => 'post',
		);
	}

	/**
	 * Build register_taxonomy() args from a config entry.
	 *
	 * @param array $def Config entry.
	 * @return array
	 */
	private static function taxonomy_args( array $def ) {
		$singular = $def['labels_singular'] ?? 'Term';
		$plural   = $def['labels_plural'] ?? 'Terms';

		return array(
			'labels'            => array(
				'name'          => $plural,
				'singular_name' => $singular,
				'menu_name'     => $plural,
			),
			'public'            => $def['public'] ?? false,
			'show_ui'           => $def['show_ui'] ?? true,
			'show_in_rest'      => $def['show_in_rest'] ?? false,
			'hierarchical'      => $def['hierarchical'] ?? true,
			'show_admin_column' => true,
			'rewrite'           => false,
			'query_var'         => false,
		);
	}

	/**
	 * Create the default silo and FAQ-group terms once.
	 *
	 * @return void
	 */
	public static function seed_terms() {
		if ( get_option( 'robertslaw_terms_seeded' ) ) {
			return;
		}

		$config = Config::load( 'content-types' );

		foreach ( $config['taxonomies'] ?? array() as $taxonomy => $def ) {
			foreach ( $def['default_terms'] ?? array() as $slug => $name ) {
				if ( ! term_exists( $slug, $taxonomy ) ) {
					wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
				}
			}
		}

		update_option( 'robertslaw_terms_seeded', ROBERTSLAW_VERSION );
	}

	/**
	 * Add one meta box per post type that declares meta.
	 *
	 * @return void
	 */
	public static function add_meta_boxes() {
		$config = Config::load( 'content-types' );

		foreach ( $config['post_types'] ?? array() as $slug => $def ) {
			if ( empty( $def['meta'] ) ) {
				continue;
			}

			add_meta_box(
				'rl-fields-' . $slug,
				sprintf( '%s Details', $def['labels_singular'] ?? 'Entry' ),
				array( __CLASS__, 'render_meta_box' ),
				$slug,
				'normal',
				'high',
				array( 'fields' => $def['meta'], 'post_type' => $slug )
			);
		}
	}

	/**
	 * Render the fields declared in config for this post type.
	 *
	 * @param \WP_Post $post Current post.
	 * @param array    $box  Meta box args.
	 * @return void
	 */
	public static function render_meta_box( $post, $box ) {
		$fields    = $box['args']['fields'] ?? array();
		$post_type = $box['args']['post_type'] ?? '';

		wp_nonce_field( 'rl_save_meta', 'rl_meta_nonce' );

		if ( 'rl_testimonial' === $post_type ) {
			printf(
				'<div class="notice notice-warning inline" style="margin:0 0 16px;padding:10px 12px"><p style="margin:0"><strong>%s</strong><br>%s</p></div>',
				esc_html__( 'This entry will not render on the site until every required consent box below is checked.', 'robertslaw' ),
				esc_html(
					sprintf(
						/* translators: %s: ethics counsel phone number. */
						__( 'RPC 1.6 covers information relating to the representation, and in family law that can include the bare fact that someone was a client. Board ethics counsel: %s.', 'robertslaw' ),
						Firm::get( 'ethics_counsel_phone' )
					)
				)
			);
		}

		echo '<table class="form-table" role="presentation"><tbody>';

		foreach ( $fields as $key => $field ) {
			$meta_key = '_rl_' . $key;
			$value    = get_post_meta( $post->ID, $meta_key, true );
			$required = ! empty( $field['required'] );

			printf(
				'<tr><th scope="row"><label for="%1$s">%2$s%3$s</label></th><td>',
				esc_attr( $meta_key ),
				esc_html( $field['label'] ?? $key ),
				$required ? ' <span style="color:#b32d2e">*</span>' : ''
			);

			if ( 'boolean' === ( $field['type'] ?? 'string' ) ) {
				printf(
					'<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s> %3$s</label>',
					esc_attr( $meta_key ),
					checked( $value, '1', false ),
					esc_html__( 'Yes', 'robertslaw' )
				);
			} else {
				printf(
					'<input type="text" class="large-text" id="%1$s" name="%1$s" value="%2$s">',
					esc_attr( $meta_key ),
					esc_attr( $value )
				);
			}

			if ( ! empty( $field['help'] ) ) {
				printf( '<p class="description">%s</p>', esc_html( $field['help'] ) );
			}

			echo '</td></tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Persist the declared fields.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_meta( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['rl_meta_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rl_meta_nonce'] ) ), 'rl_save_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$def = Config::get( 'content-types.post_types.' . $post->post_type, array() );

		foreach ( $def['meta'] ?? array() as $key => $field ) {
			$meta_key = '_rl_' . $key;

			if ( 'boolean' === ( $field['type'] ?? 'string' ) ) {
				$value = isset( $_POST[ $meta_key ] ) ? '1' : '';
			} else {
				$value = isset( $_POST[ $meta_key ] )
					? sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) )
					: '';
			}

			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}
}
