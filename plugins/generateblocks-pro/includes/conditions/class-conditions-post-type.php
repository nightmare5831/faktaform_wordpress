<?php
/**
 * Conditions Post Type Registration
 *
 * @package GenerateBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GenerateBlocks_Pro_Conditions_Post_Type
 */
class GenerateBlocks_Pro_Conditions_Post_Type {
	/**
	 * Instance.
	 *
	 * @access private
	 * @var object Instance
	 */
	private static $instance;

	/**
	 * Initiator.
	 *
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'init', [ $this, 'register_post_meta' ] );
	}

	/**
	 * Register the conditions post type.
	 */
	public function register_post_type() {
		$labels = [
			'name'               => __( 'Conditions', 'generateblocks-pro' ),
			'singular_name'      => __( 'Condition', 'generateblocks-pro' ),
			'menu_name'          => __( 'Conditions', 'generateblocks-pro' ),
			'add_new'            => __( 'Add New', 'generateblocks-pro' ),
			'add_new_item'       => __( 'Add New Condition', 'generateblocks-pro' ),
			'edit_item'          => __( 'Edit Condition', 'generateblocks-pro' ),
			'new_item'           => __( 'New Condition', 'generateblocks-pro' ),
			'view_item'          => __( 'View Condition', 'generateblocks-pro' ),
			'search_items'       => __( 'Search Conditions', 'generateblocks-pro' ),
			'not_found'          => __( 'No conditions found.', 'generateblocks-pro' ),
			'not_found_in_trash' => __( 'No conditions found in Trash.', 'generateblocks-pro' ),
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rest_base'           => 'gblocks-conditions',
			'supports'            => [ 'title' ],
		];

		register_post_type( 'gblocks_condition', $args );
	}

	/**
	 * Register the condition category taxonomy.
	 */
	public function register_taxonomy() {
		$labels = [
			'name'              => __( 'Condition Categories', 'generateblocks-pro' ),
			'singular_name'     => __( 'Condition Category', 'generateblocks-pro' ),
			'search_items'      => __( 'Search Categories', 'generateblocks-pro' ),
			'all_items'         => __( 'All Categories', 'generateblocks-pro' ),
			'parent_item'       => __( 'Parent Category', 'generateblocks-pro' ),
			'parent_item_colon' => __( 'Parent Category:', 'generateblocks-pro' ),
			'edit_item'         => __( 'Edit Category', 'generateblocks-pro' ),
			'update_item'       => __( 'Update Category', 'generateblocks-pro' ),
			'add_new_item'      => __( 'Add New Category', 'generateblocks-pro' ),
			'new_item_name'     => __( 'New Category Name', 'generateblocks-pro' ),
			'menu_name'         => __( 'Categories', 'generateblocks-pro' ),
		];

		$args = [
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => false,
			'show_in_menu'      => false,
			'show_admin_column' => false,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rest_base'         => 'condition-categories',
			'capabilities'      => [
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
			],
		];

		register_taxonomy( 'gblocks_condition_cat', [ 'gblocks_condition' ], $args );
	}

	/**
	 * Register post meta for conditions.
	 */
	public function register_post_meta() {
		register_post_meta(
			'gblocks_condition',
			'_gb_conditions',
			[
				'single'            => true,
				'type'              => 'object',
				'auth_callback'     => '__return_true',
				'sanitize_callback' => [ 'GenerateBlocks_Pro_Conditions', 'sanitize_conditions' ],
				'show_in_rest'      => [
					'schema' => [
						'type'       => 'object',
						'properties' => [
							'logic'  => [
								'type' => 'string',
								'enum' => [ 'AND', 'OR' ],
							],
							'groups' => [
								'type'  => 'array',
								'items' => [
									'type'       => 'object',
									'properties' => [
										'logic'      => [
											'type' => 'string',
											'enum' => [ 'AND', 'OR' ],
										],
										'conditions' => [
											'type'  => 'array',
											'items' => [
												'type'       => 'object',
												'properties' => [
													'type'     => [ 'type' => 'string' ],
													'rule'     => [ 'type' => 'string' ],
													'operator' => [ 'type' => 'string' ],
													'value' => [ 'type' => 'string' ],
												],
											],
										],
									],
								],
							],
						],
					],
				],
			]
		);

		register_rest_field(
			'gblocks_condition',
			'gbConditions',
			[
				'get_callback'    => function( $data ) {
					$conditions = get_post_meta( $data['id'], '_gb_conditions', true );
					return $conditions ? $conditions : [
						'logic' => 'OR',
						'groups' => [],
					];
				},
				'update_callback' => function( $value, $post ) {
					update_post_meta( $post->ID, '_gb_conditions', $value );
				},
				'schema'          => [
					'type'       => 'object',
					'properties' => [
						'logic'  => [
							'type' => 'string',
							'enum' => [ 'AND', 'OR' ],
						],
						'groups' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'logic'      => [
										'type' => 'string',
										'enum' => [ 'AND', 'OR' ],
									],
									'conditions' => [
										'type'  => 'array',
										'items' => [
											'type'       => 'object',
											'properties' => [
												'type'     => [ 'type' => 'string' ],
												'rule'     => [ 'type' => 'string' ],
												'operator' => [ 'type' => 'string' ],
												'value'    => [ 'type' => 'string' ],
											],
										],
									],
								],
							],
						],
					],
				],
			]
		);
	}
}

GenerateBlocks_Pro_Conditions_Post_Type::get_instance();
