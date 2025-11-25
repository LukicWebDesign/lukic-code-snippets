<?php
/**
 * Central registry for all snippets and their metadata.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lukic_Snippet_Registry {
	/**
	 * Category metadata keyed by slug.
	 *
	 * @var array
	 */
	private static $categories = array(
		'admin'       => array(
			'name' => 'Admin Interface',
			'icon' => 'dashicons-admin-appearance',
		),
		'content'     => array(
			'name' => 'Content Management',
			'icon' => 'dashicons-admin-post',
		),
		'utility'     => array(
			'name' => 'Utility',
			'icon' => 'dashicons-admin-tools',
		),
		'media'       => array(
			'name' => 'Media Management',
			'icon' => 'dashicons-admin-media',
		),
		'seo'         => array(
			'name' => 'SEO & Performance',
			'icon' => 'dashicons-chart-line',
		),
		'security'    => array(
			'name' => 'Security',
			'icon' => 'dashicons-shield',
		),
		'development' => array(
			'name' => 'Development',
			'icon' => 'dashicons-editor-code',
		),
	);

	/**
	 * Snippet metadata keyed by snippet slug.
	 *
	 * @var array
	 */
	private static $snippets = array(
		'site_visibility'         => array(
			'file'        => 'snippet-site-visibility.php',
			'name'        => 'Admin Bar Site Visibility Indicator',
			'category'    => 'admin',
			'tags'        => array( 'admin bar', 'indicator', 'search engines' ),
			'description' => 'Adds a colored indicator in the admin bar to show whether your site is visible to search engines or not.',
		),
		'classic_editor'          => array(
			'file'        => 'snippet-classic-editor.php',
			'name'        => 'Enable Classic Editor',
			'category'    => 'content',
			'tags'        => array( 'editor', 'content' ),
			'description' => 'Restores the classic WordPress editor and the Edit Post screen, making it look like it did before WordPress 5.0.',
		),
		'wider_admin_menu'        => array(
			'file'        => 'snippet-wider-admin-menu.php',
			'name'        => 'Wider Admin Menu',
			'category'    => 'admin',
			'tags'        => array( 'menu', 'ui', 'interface' ),
			'description' => 'Makes the WordPress admin menu wider for better readability of longer menu items.',
		),
		'svg_upload'              => array(
			'file'        => 'snippet-svg-upload.php',
			'name'        => 'SVG Upload Support',
			'category'    => 'media',
			'tags'        => array( 'svg', 'upload' ),
			'description' => 'Enables SVG file uploads in the WordPress media library with basic sanitization.',
		),
		'post_duplicator'         => array(
			'file'        => 'snippet-post-duplicator.php',
			'name'        => 'Post & Page Duplicator',
			'category'    => 'content',
			'tags'        => array( 'posts', 'pages', 'duplicator' ),
			'description' => 'Adds a "Duplicate" link to posts, pages and custom post types in admin lists.',
		),
		'show_ids'                => array(
			'file'        => 'snippet-show-ids.php',
			'name'        => 'Show IDs in Admin Tables',
			'category'    => 'admin',
			'tags'        => array( 'admin tables', 'ids', 'posts', 'pages' ),
			'description' => 'Displays IDs for posts, pages, categories, tags and other taxonomies in admin tables.',
		),
		'featured_images'         => array(
			'file'        => 'snippet-featured-images.php',
			'name'        => 'Show Featured Images in Admin Tables',
			'category'    => 'admin',
			'tags'        => array( 'admin tables', 'featured images' ),
			'description' => 'Shows featured images in admin tables for posts, pages and custom post types.',
		),
		'clean_dashboard'         => array(
			'file'        => 'snippet-clean-dashboard.php',
			'name'        => 'Clean Dashboard',
			'category'    => 'admin',
			'tags'        => array( 'dashboard', 'widgets', 'cleanup' ),
			'description' => 'Removes unnecessary widgets from the WordPress dashboard for a cleaner interface.',
		),
		'hide_admin_bar'          => array(
			'file'        => 'snippet-hide-admin-bar.php',
			'name'        => 'Hide Admin Bar on Frontend',
			'category'    => 'admin',
			'tags'        => array( 'admin bar', 'frontend' ),
			'description' => 'Hides the WordPress admin bar on the frontend of your site for all users.',
		),
		'media_replace'           => array(
			'file'        => 'snippet-media-replace.php',
			'name'        => 'Media Replacement',
			'category'    => 'media',
			'tags'        => array( 'media', 'replace' ),
			'description' => 'Replace media files while maintaining the same ID, filename and publish date - keeping all existing links intact.',
		),
		'content_order'           => array(
			'file'        => 'snippet-content-order.php',
			'name'        => 'Content Order',
			'category'    => 'content',
			'tags'        => array( 'content', 'order' ),
			'description' => 'Allows you to reorder the content of your posts and pages.',
		),
		'show_acf_columns'        => array(
			'file'        => 'snippet-acf-columns.php',
			'name'        => 'Show ACF Fields in Admin Tables',
			'category'    => 'admin',
			'tags'        => array( 'admin tables', 'acf', 'custom fields' ),
			'description' => 'Adds columns to the admin tables for posts, pages and custom post types to display the values of Advanced Custom Fields (ACF) fields.',
		),
		'media_size_column'       => array(
			'file'        => 'snippet-media-size-column.php',
			'name'        => 'Media Size Column',
			'category'    => 'media',
			'tags'        => array( 'media', 'size' ),
			'description' => 'Adds a column to the media library to display the file size of each media item.',
		),
		'hide_wp_version'         => array(
			'file'        => 'snippet-hide-wp-version.php',
			'name'        => 'Hide WP Version',
			'category'    => 'seo',
			'tags'        => array( 'wp version', 'hide' ),
			'description' => 'Enhance security by hiding the WordPress version number from your site\'s source view, thwarting targeted attacks.',
		),
		'disable_xmlrpc'          => array(
			'file'        => 'snippet-disable-xmlrpc.php',
			'name'        => 'Disable XML-RPC',
			'category'    => 'seo',
			'tags'        => array( 'xml-rpc', 'disable' ),
			'description' => 'Increase security by disabling XML-RPC to prevent external applications from interfacing with your WordPress site, reducing vulnerability to attacks.',
		),
		'custom_taxonomy_filters' => array(
			'file'        => 'snippet-custom-taxonomy-filters.php',
			'name'        => 'Show Custom Taxonomy Filters',
			'category'    => 'admin',
			'tags'        => array( 'taxonomies', 'filters' ),
			'description' => 'Shows additional filter dropdowns on list tables for hierarchical and non-hierarchical custom taxonomies. Works for both default and custom post types.',
		),
		'hide_admin_notices'      => array(
			'file'        => 'snippet-hide-admin-notices.php',
			'name'        => 'Hide Admin Notices',
			'category'    => 'admin',
			'tags'        => array( 'notices', 'cleanup', 'interface' ),
			'description' => 'Hide unnecessary admin notices and notifications in the WordPress dashboard, creating a cleaner interface with all notices accessible through a dedicated panel in the admin bar.',
		),
		'hide_footer_thankyou'    => array(
			'file'        => 'snippet-hide-footer-thankyou.php',
			'name'        => 'Hide Footer Thank You',
			'category'    => 'admin',
			'tags'        => array( 'footer', 'interface' ),
			'description' => 'Removes the "Thank you for creating with WordPress" message from the admin footer for a cleaner, more professional admin interface.',
		),
		'fluid_typography'        => array(
			'file'        => 'snippet-fluid-typography.php',
			'name'        => 'Fluid Typography Calculator',
			'category'    => 'utility',
			'tags'        => array( 'typography', 'calculator' ),
			'description' => 'Calculates the optimal font sizes for your website based on the screen size.',
		),
		'maintenance_mode'        => array(
			'file'        => 'snippet-maintenance-mode.php',
			'name'        => 'Maintenance Mode',
			'category'    => 'utility',
			'tags'        => array( 'maintenance', 'mode' ),
			'description' => 'Displays a customizable maintenance mode page to visitors while you work on your site. Administrators can still access the site normally.',
			'lifecycle'   => array(
				'activate'   => array( 'Lukic_Maintenance_Mode', 'activate_snippet' ),
				'deactivate' => array( 'Lukic_Maintenance_Mode', 'deactivate_snippet' ),
			),
			'cleanup'     => array(
				'options' => array( 'Lukic_maintenance_mode_options' ),
			),
		),
		'last_login'              => array(
			'file'        => 'snippet-last-login.php',
			'name'        => 'Last Login User',
			'category'    => 'utility',
			'tags'        => array( 'login', 'user' ),
			'description' => 'Adds a "Last login" column to the users table showing when each user last logged in. For users who have never logged in, it displays "No data".',
		),
		'search_by_slug'          => array(
			'file'        => 'snippet-search-by-slug.php',
			'name'        => 'Search Posts by Slug',
			'category'    => 'content',
			'tags'        => array( 'search', 'posts', 'slug' ),
			'description' => 'Enhances WordPress admin search to include post slugs in search results for both regular posts and custom post types. Supports multilingual websites by filtering results for the current language only.',
		),
		'show_template'           => array(
			'file'        => 'snippet-show-template.php',
			'name'        => 'Show Current Template',
			'category'    => 'admin',
			'tags'        => array( 'template', 'development', 'admin bar' ),
			'description' => 'Displays the current template file name in the admin bar, helping developers identify which template file is being used on each page. Includes detailed information on hover.',
		),
		'custom_login_url'        => array(
			'file'        => 'snippet-custom-login-url.php',
			'name'        => 'Custom Login URL',
			'category'    => 'security',
			'tags'        => array( 'login', 'security' ),
			'description' => 'Allows you to set a custom login URL for your WordPress site.',
			'cleanup'     => array(
				'options' => array( 'Lukic_custom_login_url' ),
			),
		),
		'word_counter'            => array(
			'file'        => 'snippet-word-counter.php',
			'name'        => 'Word Counter',
			'category'    => 'admin',
			'tags'        => array( 'content', 'word count', 'analysis' ),
			'description' => 'Add a word counter tool to analyze text for words, characters, sentences, and paragraphs.',
		),
		'disable_all_updates'     => array(
			'file'        => 'snippet-disable-all-updates.php',
			'name'        => 'Disable All Updates',
			'category'    => 'security',
			'tags'        => array( 'updates', 'disable' ),
			'description' => 'Disable all WordPress updates, including core, plugins, and themes.',
		),
		'image_attributes_editor' => array(
			'file'        => 'snippet-image-attributes-editor.php',
			'name'        => 'Image Attributes Editor',
			'category'    => 'development',
			'tags'        => array( 'image', 'attributes' ),
			'description' => 'Edit image attributes such as alt text, title, and description.',
		),
		'meta_tags_editor'        => array(
			'file'        => 'snippet-meta-tags-editor.php',
			'name'        => 'Meta Tags Editor',
			'category'    => 'seo',
			'tags'        => array( 'meta tags', 'editor' ),
			'description' => 'Edit meta titles and descriptions for all pages, posts, custom post types, and taxonomies. Compatible with Yoast SEO and Rank Math plugins.',
		),
		'db_tables_manager'       => array(
			'file'        => 'snippet-db-tables-manager.php',
			'name'        => 'Custom Database Tables Manager',
			'category'    => 'development',
			'tags'        => array( 'database', 'tables' ),
			'description' => 'Create and manage custom database tables in your WordPress site without writing SQL code.',
		),
		'redirect_manager'        => array(
			'file'        => 'snippet-redirect-manager.php',
			'name'        => 'Redirect Manager',
			'category'    => 'seo',
			'tags'        => array( 'redirects', 'manager' ),
			'description' => 'Create and manage 301, 302, 307, and 308 redirects for your website. Tracks redirect usage and provides a clean interface for managing URL redirections.',
			'cleanup'     => array(
				'options' => array( 'Lukic_redirect_track_hits', 'Lukic_redirect_log_last_access' ),
				'tables'  => array( 'lukic_redirects' ),
			),
		),
		'image_sizes_panel'       => array(
			'file'        => 'snippet-image-sizes-panel.php',
			'name'        => 'Image Sizes Panel',
			'category'    => 'media',
			'tags'        => array( 'media', 'images', 'sizes' ),
			'description' => 'Displays available image sizes with dimensions in the sidebar when viewing a single image in the WordPress admin dashboard.',
		),
		'limit_revisions'         => array(
			'file'        => 'snippet-limit-revisions.php',
			'name'        => 'Limit Revisions',
			'category'    => 'utility',
			'tags'        => array( 'revisions', 'database', 'optimization' ),
			'description' => 'Prevent database bloat by limiting the number of revisions to keep for post types supporting revisions. Configure limits per post type.',
			'cleanup'     => array(
				'options' => array( 'Lukic_limit_revisions_options' ),
			),
		),
		'security_headers'        => array(
			'file'        => 'snippet-security-headers.php',
			'name'        => 'Security Headers Manager',
			'category'    => 'security',
			'tags'        => array( 'security', 'headers', 'protection' ),
			'description' => 'Enhance your site security by managing HTTP security headers like Content-Security-Policy, X-Frame-Options, and HSTS. Includes presets and testing tools.',
			'cleanup'     => array(
				'options' => array( 'Lukic_security_headers' ),
			),
		),
		'upload_limits'           => array(
			'file'        => 'snippet-upload-limits.php',
			'name'        => 'Control Upload Limits',
			'category'    => 'utility',
			'tags'        => array( 'uploads', 'php', 'performance' ),
			'description' => 'Easily adjust PHP upload limits including maximum file size, execution time, and memory limit to improve handling of large files and resource-intensive operations.',
			'cleanup'     => array(
				'options' => array( 'Lukic_upload_limits' ),
			),
		),
		'hide_author_slugs'       => array(
			'file'        => 'snippet-hide-author-slugs.php',
			'name'        => 'Hide Author Slugs',
			'category'    => 'security',
			'tags'        => array( 'security', 'author', 'slugs' ),
			'description' => 'Protects author usernames by encrypting URL slugs and securing REST API endpoints.',
		),
		'admin_menu_organizer'    => array(
			'file'        => 'snippet-admin-menu-organizer.php',
			'name'        => 'Admin Menu Organizer',
			'category'    => 'admin',
			'tags'        => array( 'admin', 'menu', 'organizer' ),
			'description' => 'Reorder, rename, hide, and reorganize admin menu items using a drag-and-drop interface.',
		),
		'disable_file_editing'    => array(
			'file'        => 'snippet-disable-file-editing.php',
			'name'        => 'Disable File Editing',
			'category'    => 'security',
			'tags'        => array( 'security', 'editor', 'disable' ),
			'description' => 'Disable the theme and plugin file editor in WordPress admin to prevent unauthorized code changes and reduce security risks from compromised accounts.',
		),
		'user_profile_image'      => array(
			'file'        => 'snippet-user-profile-image.php',
			'name'        => 'User Profile Image',
			'category'    => 'media',
			'tags'        => array( 'avatar', 'profile', 'media', 'user' ),
			'description' => 'Allow users to upload custom avatars from the media library instead of using Gravatar.',
		),
		'show_active_plugins_first' => array(
			'file'        => 'snippet-show-active-plugins-first.php',
			'name'        => 'Show Active Plugins First',
			'category'    => 'admin',
			'tags'        => array( 'plugins', 'order', 'admin' ),
			'description' => 'Show active plugins at the top of plugins list separated from inactive plugins for easier management.',
		),
		'disable_comments'        => array(
			'file'        => 'snippet-disable-comments.php',
			'name'        => 'Disable Comments',
			'category'    => 'utility',
			'tags'        => array( 'comments', 'disable', 'spam', 'cleanup' ),
			'description' => 'Remove comment functionality across your WordPress site, helping reduce spam, moderation workload, and database clutter. Keeps WooCommerce product reviews intact.',
		),

	);

	/**
	 * Get all snippets.
	 *
	 * @return array
	 */
	public static function get_snippets() {
		$localized = array();

		foreach ( self::$snippets as $snippet_id => $snippet ) {
			$localized[ $snippet_id ] = self::localize_snippet( $snippet );
		}

		return $localized;
	}

	/**
	 * Get a specific snippet definition.
	 *
	 * @param string $snippet_id
	 * @return array|null
	 */
	public static function get_snippet( $snippet_id ) {
		if ( ! isset( self::$snippets[ $snippet_id ] ) ) {
			return null;
		}

		return self::localize_snippet( self::$snippets[ $snippet_id ] );
	}

	/**
	 * Get category map.
	 *
	 * @return array
	 */
	public static function get_categories() {
		$categories = array();

		foreach ( self::$categories as $category_id => $category ) {
			$categories[ $category_id ] = array(
				'name' => __( $category['name'], 'Lukic-code-snippets' ),
				'icon' => $category['icon'],
			);
		}

		return $categories;
	}

	/**
	 * Get snippets grouped by category.
	 *
	 * @return array
	 */
	public static function get_snippets_by_category() {
		$grouped = array();

		foreach ( self::get_snippets() as $snippet_id => $snippet ) {
			$category = $snippet['category'];
			if ( ! isset( $grouped[ $category ] ) ) {
				$grouped[ $category ] = array();
			}

			$grouped[ $category ][ $snippet_id ] = array(
				'name' => $snippet['name'],
				'tags' => isset( $snippet['tags'] ) ? $snippet['tags'] : array(),
			);
		}

		return $grouped;
	}

	/**
	 * Get aggregated cleanup metadata for uninstall routines.
	 *
	 * @return array
	 */
	public static function get_cleanup_items() {
		$cleanup = array(
			'options' => array(),
			'tables'  => array(),
		);

		foreach ( self::$snippets as $snippet ) {
			if ( isset( $snippet['cleanup']['options'] ) && is_array( $snippet['cleanup']['options'] ) ) {
				$cleanup['options'] = array_merge( $cleanup['options'], $snippet['cleanup']['options'] );
			}

			if ( isset( $snippet['cleanup']['tables'] ) && is_array( $snippet['cleanup']['tables'] ) ) {
				$cleanup['tables'] = array_merge( $cleanup['tables'], $snippet['cleanup']['tables'] );
			}
		}

		$cleanup['options'] = array_values( array_unique( $cleanup['options'] ) );
		$cleanup['tables']  = array_values( array_unique( $cleanup['tables'] ) );

		return $cleanup;
	}

	/**
	 * Prepare snippet data with localized strings.
	 *
	 * @param array $snippet
	 * @return array
	 */
	private static function localize_snippet( $snippet ) {
		$snippet['name'] = __( $snippet['name'], 'Lukic-code-snippets' );

		if ( isset( $snippet['description'] ) ) {
			$snippet['description'] = __( $snippet['description'], 'Lukic-code-snippets' );
		}

		if ( isset( $snippet['tags'] ) && is_array( $snippet['tags'] ) ) {
			$snippet['tags'] = array_map(
				function ( $tag ) {
					return __( $tag, 'lukic-code-snippets' );
				},
				$snippet['tags']
			);
		}

		return $snippet;
	}
}
