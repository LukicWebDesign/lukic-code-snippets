<?php
/**
 * Snippet: Show Featured Images in Admin Tables
 * Description: Adds a featured image column to admin tables for posts, pages, and custom post types
 */

if ( ! function_exists( 'Lukic_show_featured_images_init' ) ) {
	/**
	 * Initialize the featured image column functionality
	 */
	function Lukic_show_featured_images_init() {
		// For post types
		add_action( 'current_screen', 'Lukic_setup_featured_image_columns' );

		// Add styles for the image column
		add_action( 'admin_head', 'Lukic_featured_image_column_style' );
	}
	Lukic_show_featured_images_init();

	/**
	 * Setup the featured image columns based on current screen
	 */
	function Lukic_setup_featured_image_columns() {
		$screen = get_current_screen();

		// Debug info
		error_log( 'Lukic Featured Images: Current screen is ' . $screen->id );

		// Handle post types (only on list screens, not edit screens)
		if ( $screen->base === 'edit' && post_type_supports( $screen->post_type, 'thumbnail' ) ) {
			error_log( 'Lukic Featured Images: Adding column for post type ' . $screen->post_type );

			// Add the column (priority 5 to ensure it's early in the list)
			add_filter( 'manage_' . $screen->post_type . '_posts_columns', 'Lukic_add_featured_image_column', 5 );

			// Fill the column
			add_action( 'manage_' . $screen->post_type . '_posts_custom_column', 'Lukic_show_featured_image_column_content', 10, 2 );
		}

		// Handle taxonomies
		if ( $screen->base === 'edit-tags' ) {
			error_log( 'Lukic Featured Images: Adding column for taxonomy ' . $screen->taxonomy );

			// Add the column
			add_filter( 'manage_edit-' . $screen->taxonomy . '_columns', 'Lukic_add_featured_image_column', 5 );

			// Fill the column
			add_filter( 'manage_' . $screen->taxonomy . '_custom_column', 'Lukic_show_taxonomy_featured_image_column_content', 10, 3 );
		}
	}

	/**
	 * Add featured image column
	 */
	function Lukic_add_featured_image_column( $columns ) {
		// New approach - insert after ID column if it exists, otherwise after checkbox
		$new_columns = array();

		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;

			// Add our column after the ID column if it exists
			if ( $key === 'Lukic_id' ) {
				$new_columns['Lukic_featured_image'] = __( 'Image', 'Lukic-code-snippets' );
			}
		}

		// If there's no ID column, add after checkbox
		if ( ! isset( $columns['Lukic_id'] ) ) {
			if ( isset( $columns['cb'] ) ) {
				// Get checkbox
				$cb = $new_columns['cb'];
				unset( $new_columns['cb'] );

				// Rebuild columns with our column after checkbox
				$new_columns = array(
					'cb'                     => $cb,
					'Lukic_featured_image' => __( 'Image', 'Lukic-code-snippets' ),
				) + $new_columns;
			} else {
				// If no checkbox either, add to beginning
				$new_columns = array( 'Lukic_featured_image' => __( 'Image', 'Lukic-code-snippets' ) ) + $new_columns;
			}
		}

		return $new_columns;
	}

	/**
	 * Display the featured image for post types
	 */
	function Lukic_show_featured_image_column_content( $column_name, $post_id ) {
		if ( 'Lukic_featured_image' === $column_name ) {
			if ( has_post_thumbnail( $post_id ) ) {
				$thumbnail = get_the_post_thumbnail( $post_id, array( 80, 80 ) );
				echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">';
				echo wp_kses_post( $thumbnail );
				echo '</a>';

				// Debug
				error_log( 'Lukic Featured Images: Displaying thumbnail for post ID ' . $post_id );
			} else {
				$no_image_url = plugins_url( 'assets/icons/no_image.svg', __DIR__ );
				echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">';
				echo '<img src="' . esc_url( $no_image_url ) . '" alt="No image" class="Lukic-no-image-placeholder" />';
				echo '</a>';
				error_log( 'Lukic Featured Images: No thumbnail for post ID ' . $post_id . ', using placeholder' );
			}
		}
	}

	/**
	 * Display the featured image for taxonomies
	 */
	function Lukic_show_taxonomy_featured_image_column_content( $content, $column_name, $term_id ) {
		if ( 'Lukic_featured_image' === $column_name ) {
			// Try common term meta fields
			$image_field_names = array(
				'thumbnail_id',
				'image',
				'term_image',
				'featured_image',
				'category_image',
				'tax_image',
			);

			foreach ( $image_field_names as $field ) {
				$meta_value = get_term_meta( $term_id, $field, true );
				if ( ! empty( $meta_value ) && is_numeric( $meta_value ) ) {
					$image = wp_get_attachment_image( $meta_value, array( 50, 50 ) );
					if ( $image ) {
						return '<a href="' . esc_url( get_edit_term_link( $term_id ) ) . '">' . $image . '</a>';
					}
				}
			}

			$no_image_url = plugins_url( 'assets/icons/no_image.svg', __DIR__ );
			return '<a href="' . esc_url( get_edit_term_link( $term_id ) ) . '"><img src="' . esc_url( $no_image_url ) . '" width="50" height="50" alt="No image" class="Lukic-no-image-placeholder" /></a>';
		}

		return $content;
	}

	/**
	 * Add custom styling for the featured image column
	 */
	function Lukic_featured_image_column_style() {
		echo '<style>
            /* Featured Image Column Styles */
            .column-Lukic_featured_image {
                width: 80px !important;
                text-align: center !important;
                vertical-align: middle !important;
            }
            .column-Lukic_featured_image img {
                max-width: 80px;
                height: auto;
                display: block;
                margin: 0 auto;
                border-radius: 3px;
                border: 1px solid #ddd;
                background: #f9f9f9;
                padding: 2px;
            }
            .column-Lukic_featured_image .Lukic-no-image {
                color: #ddd;
                font-size: 20px;
                display: inline-block;
                height: 50px;
                line-height: 50px;
            }
            .column-Lukic_featured_image .Lukic-no-image-placeholder {
                opacity: 0.5;
            }
            .column-Lukic_featured_image a:focus {
                box-shadow: none;
            }
            /* Ensure column visibility */
            .widefat .column-Lukic_featured_image {
                display: table-cell !important;
            }
        </style>';
	}
}
