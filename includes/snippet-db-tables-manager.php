<?php
/**
 * Snippet: Custom Database Tables Manager
 * Description: View and manage custom database tables in your WordPress installation
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Lukic_DB_Tables_Manager {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Add submenu page under the main plugin menu
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 20 );

		// Register AJAX handlers for database operations
		add_action( 'wp_ajax_Lukic_get_table_structure', array( $this, 'ajax_get_table_structure' ) );
		add_action( 'wp_ajax_Lukic_get_table_data', array( $this, 'ajax_get_table_data' ) );
		add_action( 'wp_ajax_Lukic_export_table', array( $this, 'ajax_export_table' ) );
		add_action( 'wp_ajax_Lukic_update_table_row', array( $this, 'ajax_update_table_row' ) );
		add_action( 'wp_ajax_Lukic_get_table_row', array( $this, 'ajax_get_table_row' ) );
		add_action( 'wp_ajax_Lukic_search_table_data', array( $this, 'ajax_search_table_data' ) );

		// Localize scripts for the admin page
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_admin_scripts' ) );
	}

	/**
	 * Add submenu page
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'lukic-code-snippets', // Parent slug
			__( 'DB Tables Manager', 'Lukic-code-snippets' ),
			__( 'DB Tables Manager', 'Lukic-code-snippets' ),
			'manage_options',
			'lukic-db-tables-manager',
			array( $this, 'display_admin_page' )
		);
	}

	/**
	 * Localize admin scripts with necessary data.
	 */
	public function localize_admin_scripts( $hook ) {

		// Check if we are on the correct page
		if ( ! isset( $_GET['page'] ) || 'lukic-db-tables-manager' !== $_GET['page'] ) {
			return;
		}

		wp_localize_script(
			'Lukic-db-tables',
			'LukicDBManager',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'Lukic_db_tables_nonce' ),
				'strings' => array(
					'loading'       => __( 'Loading...', 'Lukic-code-snippets' ),
					'error'         => __( 'Error occurred', 'Lukic-code-snippets' ),
					'exportSuccess' => __( 'Export successful!', 'Lukic-code-snippets' ),
					'noData'        => __( 'No data found', 'Lukic-code-snippets' ),
				),
			)
		);
	}

	/**
	 * Display the admin page
	 */
	public function display_admin_page() {
		// Get tables information
		$tables_info = $this->get_tables_info();

		// Include the header partial
		// Header component is already loaded in main plugin file

		// Prepare stats for header
		$stats = array(
			array(
				'count' => count( $tables_info['custom'] ),
				'label' => __( 'Custom Tables', 'Lukic-code-snippets' ),
			),
			array(
				'count' => count( $tables_info['wordpress'] ),
				'label' => __( 'WP Core Tables', 'Lukic-code-snippets' ),
			),
		);

		?>
		<div class="wrap Lukic-settings-wrap">
			<?php Lukic_display_header( __( 'Database Tables Manager', 'Lukic-code-snippets' ), $stats ); ?>
			
			<div class="Lukic-settings-intro">
				<p><?php esc_html_e( 'View and manage custom database tables created by plugins and themes.', 'Lukic-code-snippets' ); ?></p>
			</div>
			
			<div class="Lukic-settings-container">
				<div class="Lukic-settings-main">
					<div class="Lukic-tab-container">
						<div class="Lukic-tab-nav">
							<button class="Lukic-tab-button active" data-tab="custom-tables"><?php esc_html_e( 'Custom Tables', 'Lukic-code-snippets' ); ?></button>
							<button class="Lukic-tab-button" data-tab="wp-tables"><?php esc_html_e( 'WordPress Tables', 'Lukic-code-snippets' ); ?></button>
							<button class="Lukic-tab-button" data-tab="all-tables"><?php esc_html_e( 'All Tables', 'Lukic-code-snippets' ); ?></button>
						</div>
						
						<div class="Lukic-tab-content active" id="custom-tables">
							<?php $this->render_tables_list( $tables_info['custom'] ); ?>
						</div>
						
						<div class="Lukic-tab-content" id="wp-tables">
							<?php $this->render_tables_list( $tables_info['wordpress'] ); ?>
						</div>
						
						<div class="Lukic-tab-content" id="all-tables">
							<?php $this->render_tables_list( $tables_info['all'] ); ?>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Modal for table details -->
			<div id="Lukic-table-modal" class="Lukic-modal">
				<div class="Lukic-modal-content">
					<span class="Lukic-modal-close">&times;</span>
					<h2 id="Lukic-table-name"></h2>
					
					<div class="Lukic-tab-container">
						<div class="Lukic-tab-nav">
							<button class="Lukic-tab-button active" data-tab="structure"><?php esc_html_e( 'Structure', 'Lukic-code-snippets' ); ?></button>
							<button class="Lukic-tab-button" data-tab="data"><?php esc_html_e( 'Data', 'Lukic-code-snippets' ); ?></button>
						</div>
						
						<div class="Lukic-tab-content active" id="structure">
							<div id="structure-content">
								<div class="Lukic-loading"><?php esc_html_e( 'Loading structure...', 'Lukic-code-snippets' ); ?></div>
							</div>
						</div>
						
						<div class="Lukic-tab-content" id="data">
							<div class="Lukic-data-controls">
								<div class="Lukic-search-controls">
									<input type="text" id="table-search" placeholder="<?php esc_attr_e( 'Search in table...', 'Lukic-code-snippets' ); ?>" />
									<button class="button search-table"><?php esc_html_e( 'Search', 'Lukic-code-snippets' ); ?></button>
									<button class="button clear-search" style="display: none;"><?php esc_html_e( 'Clear', 'Lukic-code-snippets' ); ?></button>
								</div>
								<div class="Lukic-data-pagination">
									<button class="button prev-page" disabled><?php esc_html_e( 'Previous', 'Lukic-code-snippets' ); ?></button>
									<span class="pagination-info"></span>
									<button class="button next-page"><?php esc_html_e( 'Next', 'Lukic-code-snippets' ); ?></button>
								</div>
								<button class="button export-table"><?php esc_html_e( 'Export to CSV', 'Lukic-code-snippets' ); ?></button>
							</div>
							<div id="data-content">
								<div class="Lukic-loading"><?php esc_html_e( 'Loading data...', 'Lukic-code-snippets' ); ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Modal for editing table rows -->
			<div id="Lukic-edit-modal" class="Lukic-modal">
				<div class="Lukic-modal-content">
					<span class="Lukic-modal-close Lukic-edit-close">&times;</span>
					<h2><?php esc_html_e( 'Edit Row', 'Lukic-code-snippets' ); ?></h2>
					<form id="Lukic-edit-form">
						<div id="edit-form-fields">
							<!-- Form fields will be populated dynamically -->
						</div>
						<div class="Lukic-edit-actions">
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Update Row', 'Lukic-code-snippets' ); ?></button>
							<button type="button" class="button Lukic-edit-cancel"><?php esc_html_e( 'Cancel', 'Lukic-code-snippets' ); ?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<style>
			.Lukic-tab-container {
				margin-top: 20px;
			}
			
			.Lukic-tab-nav {
				display: flex;
				border-bottom: 1px solid #ccc;
				margin-bottom: 20px;
			}
			
			.Lukic-tab-button {
				background: none;
				border: none;
				padding: 10px 15px;
				cursor: pointer;
				border-bottom: 3px solid transparent;
				margin-bottom: -1px;
				font-weight: 600;
			}
			
			.Lukic-tab-button:focus {
				outline: none;
				box-shadow: none;
			}
			
			.Lukic-tab-button.active {
				border-bottom-color: #00E1AF;
				color: #00E1AF;
			}
			
			.Lukic-tab-content {
				display: none;
			}
			
			.Lukic-tab-content.active {
				display: block;
			}
			
			.Lukic-modal {
				display: none;
				position: fixed;
				z-index: 9999;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
				overflow: auto;
				background-color: rgba(0,0,0,0.4);
			}
			
			.Lukic-modal-content {
				background-color: #fefefe;
				margin: 5% auto;
				padding: 20px;
				border: 1px solid #888;
				border-radius: 5px;
				box-shadow: 0 4px 8px rgba(0,0,0,0.1);
			}
			
			.Lukic-modal-close {
				color: #aaa;
				float: right;
				font-size: 28px;
				font-weight: bold;
				cursor: pointer;
			}
			
			.Lukic-modal-close:hover {
				color: #00E1AF;
			}
			
			.Lukic-loading {
				text-align: center;
				padding: 20px;
				font-style: italic;
				color: #666;
			}
			
			.Lukic-data-controls {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 20px;
				padding: 15px;
				background: #f9f9f9;
				border: 1px solid #e1e1e1;
				border-radius: 4px;
				flex-wrap: wrap;
				gap: 15px;
			}
			
			.Lukic-search-controls {
				display: flex;
				align-items: center;
				gap: 10px;
			}
			
			.Lukic-search-controls input {
				padding: 8px 12px;
				border: 1px solid #ddd;
				border-radius: 4px;
				min-width: 250px;
				font-size: 14px;
			}
			
			.Lukic-search-controls .button {
				background: var(--Lukic-primary, #00E1AF);
				border-color: var(--Lukic-primary, #00E1AF);
				color: white;
				border-radius: 4px;
				padding: 8px 16px;
				font-weight: 600;
				text-shadow: none;
				box-shadow: none;
			}
			
			.Lukic-search-controls .button:hover {
				background: var(--Lukic-primary-dark, #00c49a);
				border-color: var(--Lukic-primary-dark, #00c49a);
			}
			
			.Lukic-search-controls .clear-search {
				background: #666;
				border-color: #666;
			}
			
			.Lukic-search-controls .clear-search:hover {
				background: #555;
				border-color: #555;
			}
			
			.Lukic-data-pagination {
				display: flex;
				align-items: center;
				gap: 10px;
			}
			
			.Lukic-data-pagination .button {
				background: var(--Lukic-primary, #00E1AF);
				border-color: var(--Lukic-primary, #00E1AF);
				color: white;
				border-radius: 4px;
				padding: 6px 12px;
				font-weight: 600;
				text-shadow: none;
				box-shadow: none;
			}
			
			.Lukic-data-pagination .button:hover:not(:disabled) {
				background: var(--Lukic-primary-dark, #00c49a);
				border-color: var(--Lukic-primary-dark, #00c49a);
			}
			
			.Lukic-data-pagination .button:disabled {
				background: #ccc;
				border-color: #ccc;
				color: #666;
				cursor: not-allowed;
			}
			
			.export-table {
				background: var(--Lukic-primary, #00E1AF) !important;
				border-color: var(--Lukic-primary, #00E1AF) !important;
				color: white !important;
				border-radius: 4px !important;
				padding: 8px 16px !important;
				font-weight: 600 !important;
				text-shadow: none !important;
				box-shadow: none !important;
			}
			
			.export-table:hover {
				background: var(--Lukic-primary-dark, #00c49a) !important;
				border-color: var(--Lukic-primary-dark, #00c49a) !important;
			}
			
			/* Fix DataTables select dropdown */
			.dataTables_length select {
				min-width: 80px !important;
				padding: 4px 25px 4px 8px !important;
				margin: 0 5px !important;
				border: 1px solid #ddd !important;
				border-radius: 4px !important;
				background: white !important;
			}
			
			/* Fix DataTables controls spacing */
			.dataTables_wrapper .dataTables_length,
			.dataTables_wrapper .dataTables_filter {
				margin-bottom: 15px;
			}
			
			/* Style View Details buttons in main table */
			.view-table {
				background: var(--Lukic-primary, #00E1AF) !important;
				border-color: var(--Lukic-primary, #00E1AF) !important;
				color: white !important;
				border-radius: 4px !important;
				padding: 6px;
				font-weight: 600 !important;
				text-shadow: none !important;
				box-shadow: none !important;
			}
			
			.view-table:hover {
				background: var(--Lukic-primary-dark, #00c49a) !important;
				border-color: var(--Lukic-primary-dark, #00c49a) !important;
			}
			
			.Lukic-data-table {
				width: 100%;
				border-collapse: collapse;
			}
			
			.Lukic-data-table th {
				background-color: #f5f5f5;
				font-weight: 600;
			}
			
			.Lukic-data-table th, 
			.Lukic-data-table td {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			.Lukic-data-table tr:hover {
				background-color: #f9f9f9;
			}
			
			.Lukic-edit-actions {
				margin-top: 20px;
				text-align: right;
			}
			
			.Lukic-edit-actions .button {
				margin-left: 10px;
			}
			
			#edit-form-fields {
				max-height: 400px;
				overflow-y: auto;
			}
			
			.Lukic-field-group {
				margin-bottom: 15px;
			}
			
			.Lukic-field-group label {
				display: block;
				font-weight: 600;
				margin-bottom: 5px;
			}
			
			.Lukic-field-group input,
			.Lukic-field-group textarea {
				width: 100%;
				padding: 8px;
				border: 1px solid #ddd;
				border-radius: 4px;
			}
			
			.Lukic-field-group textarea {
				min-height: 60px;
				resize: vertical;
			}
			
			.Lukic-edit-btn {
				background: #1e3a8a !important;
				border-color: #1e3a8a !important;
				color: white !important;
				border-radius: 4px !important;
				padding: 4px 10px !important;
				font-size: 12px !important;
				font-weight: 600 !important;
				text-shadow: none !important;
				box-shadow: none !important;
				margin-left: 5px;
			}
			
			.Lukic-edit-btn:hover {
				background: #1e40af !important;
				border-color: #1e40af !important;
			}
			
			/* Style modal buttons */
			.Lukic-edit-actions .button-primary {
				background: var(--Lukic-primary, #00E1AF) !important;
				border-color: var(--Lukic-primary, #00E1AF) !important;
				color: white !important;
				border-radius: 4px !important;
				padding: 8px 16px !important;
				font-weight: 600 !important;
				text-shadow: none !important;
				box-shadow: none !important;
			}
			
			.Lukic-edit-actions .button-primary:hover {
				background: var(--Lukic-primary-dark, #00c49a) !important;
				border-color: var(--Lukic-primary-dark, #00c49a) !important;
			}
			
			.Lukic-edit-cancel {
				background: #666 !important;
				border-color: #666 !important;
				color: white !important;
				border-radius: 4px !important;
				padding: 8px 16px !important;
				font-weight: 600 !important;
				text-shadow: none !important;
				box-shadow: none !important;
			}
			
			.Lukic-edit-cancel:hover {
				background: #555 !important;
				border-color: #555 !important;
			}
			
			/* Search highlighting */
			mark {
				background-color: #ffeb3b;
				padding: 1px 2px;
				border-radius: 2px;
			}
		</style>
		<?php
	}

	/**
	 * Render tables list
	 */
	private function render_tables_list( $tables ) {
		if ( empty( $tables ) ) {
			echo '<p>' . __( 'No tables found.', 'Lukic-code-snippets' ) . '</p>';
			return;
		}

		?>
		<table class="wp-list-table widefat fixed striped Lukic-tables-list">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Table Name', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Engine', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Rows', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Size', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'Lukic-code-snippets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tables as $table ) : ?>
				<tr>
					<td><?php echo esc_html( $table['name'] ); ?></td>
					<td><?php echo esc_html( $table['engine'] ); ?></td>
					<td><?php echo number_format( $table['rows'] ); ?></td>
					<td><?php echo esc_html( $table['size'] ); ?></td>
					<td>
						<button class="button view-table" data-table="<?php echo esc_attr( $table['name'] ); ?>">
							<?php esc_html_e( 'View Details', 'Lukic-code-snippets' ); ?>
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Get information about all database tables
	 */
	private function get_tables_info() {
		global $wpdb;

		// Get all tables
		$tables = $wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );

		if ( empty( $tables ) ) {
			return array(
				'all'       => array(),
				'wordpress' => array(),
				'custom'    => array(),
			);
		}

		// WordPress core tables (using prefix)
		$wp_core_tables = array(
			$wpdb->prefix . 'commentmeta',
			$wpdb->prefix . 'comments',
			$wpdb->prefix . 'links',
			$wpdb->prefix . 'options',
			$wpdb->prefix . 'postmeta',
			$wpdb->prefix . 'posts',
			$wpdb->prefix . 'termmeta',
			$wpdb->prefix . 'terms',
			$wpdb->prefix . 'term_relationships',
			$wpdb->prefix . 'term_taxonomy',
			$wpdb->prefix . 'usermeta',
			$wpdb->prefix . 'users',
		);

		// For multisite installations
		if ( is_multisite() ) {
			$wp_core_tables = array_merge(
				$wp_core_tables,
				array(
					$wpdb->prefix . 'blogs',
					$wpdb->prefix . 'blog_versions',
					$wpdb->prefix . 'signups',
					$wpdb->prefix . 'site',
					$wpdb->prefix . 'sitemeta',
					$wpdb->prefix . 'registration_log',
				)
			);
		}

		$all_tables       = array();
		$wordpress_tables = array();
		$custom_tables    = array();

		foreach ( $tables as $table ) {
			$table_info = array(
				'name'   => $table['Name'],
				'engine' => $table['Engine'],
				'rows'   => $table['Rows'],
				'size'   => $this->format_size( $table['Data_length'] + $table['Index_length'] ),
			);

			$all_tables[] = $table_info;

			if ( in_array( $table['Name'], $wp_core_tables ) ) {
				$wordpress_tables[] = $table_info;
			} else {
				$custom_tables[] = $table_info;
			}
		}

		return array(
			'all'       => $all_tables,
			'wordpress' => $wordpress_tables,
			'custom'    => $custom_tables,
		);
	}

	/**
	 * Format byte size to human-readable format
	 */
	private function format_size( $bytes ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

		$bytes = max( $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );

		$bytes /= pow( 1024, $pow );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}

	/**
	 * AJAX handler for getting table structure
	 */
	public function ajax_get_table_structure() {
		// Security check
		check_ajax_referer( 'Lukic_db_tables_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions.', 'Lukic-code-snippets' ) );
		}

		$table = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
		global $wpdb;
		$table = $this->validate_table_name( $table );
		if ( ! $table ) {
			wp_send_json_error( __( 'Invalid table specified.', 'Lukic-code-snippets' ) );
		}

		// Get table structure
		$columns = $wpdb->get_results( "DESCRIBE `{$table}`" );

		if ( empty( $columns ) ) {
			wp_send_json_error( __( 'Could not retrieve table structure.', 'Lukic-code-snippets' ) );
		}

		ob_start();
		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Field', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Type', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Null', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Key', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Default', 'Lukic-code-snippets' ); ?></th>
					<th><?php esc_html_e( 'Extra', 'Lukic-code-snippets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $columns as $column ) : ?>
				<tr>
					<td><?php echo esc_html( $column->Field ); ?></td>
					<td><?php echo esc_html( $column->Type ); ?></td>
					<td><?php echo esc_html( $column->Null ); ?></td>
					<td><?php echo esc_html( $column->Key ); ?></td>
					<td><?php echo is_null( $column->Default ) ? '<em>NULL</em>' : esc_html( $column->Default ); ?></td>
					<td><?php echo esc_html( $column->Extra ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html' => $html,
			)
		);
	}

	/**
	 * AJAX handler for getting table data
	 */
	public function ajax_get_table_data() {
		// Security check
		check_ajax_referer( 'Lukic_db_tables_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions.', 'Lukic-code-snippets' ) );
		}

		$table    = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
		$page     = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 20;
		global $wpdb;
		$table = $this->validate_table_name( $table );
		if ( ! $table ) {
			wp_send_json_error( __( 'Invalid table specified.', 'Lukic-code-snippets' ) );
		}

		// Get total rows count
		$total_rows = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );

		// Calculate offset
		$offset = ( $page - 1 ) * $per_page;

		// Get data with pagination
		$rows = $wpdb->get_results( "SELECT * FROM `{$table}` LIMIT {$offset}, {$per_page}", ARRAY_A );

		if ( $rows === null ) {
			wp_send_json_error( __( 'Error retrieving data from table.', 'Lukic-code-snippets' ) . ' ' . $wpdb->last_error );
		}

		if ( empty( $rows ) ) {
			ob_start();
			?>
			<div class="notice notice-info">
				<p><?php esc_html_e( 'This table does not contain any data.', 'Lukic-code-snippets' ); ?></p>
			</div>
			<?php
			$html = ob_get_clean();

			wp_send_json_success(
				array(
					'html'    => $html,
					'total'   => 0,
					'pages'   => 0,
					'current' => 1,
				)
			);
		}

		// Calculate total pages
		$total_pages = ceil( $total_rows / $per_page );

		// Get table structure to identify primary key
		$columns     = $wpdb->get_results( "DESCRIBE `{$table}`" );
		$primary_key = null;
		foreach ( $columns as $column ) {
			if ( $column->Key === 'PRI' ) {
				$primary_key = $column->Field;
				break;
			}
		}

		ob_start();
		?>
		<table class="wp-list-table widefat fixed striped Lukic-data-table">
			<thead>
				<tr>
					<?php foreach ( array_keys( $rows[0] ) as $column ) : ?>
					<th><?php echo esc_html( $column ); ?></th>
					<?php endforeach; ?>
					<th><?php esc_html_e( 'Actions', 'Lukic-code-snippets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row_index => $row ) : ?>
				<tr>
					<?php foreach ( $row as $column => $value ) : ?>
					<td><?php echo is_null( $value ) ? '<em>NULL</em>' : esc_html( substr( maybe_serialize( $value ), 0, 100 ) ); ?></td>
					<?php endforeach; ?>
					<td>
						<?php if ( $primary_key && isset( $row[ $primary_key ] ) ) : ?>
						<button class="button Lukic-edit-btn edit-row" 
								data-table="<?php echo esc_attr( $table ); ?>" 
								data-pk="<?php echo esc_attr( $primary_key ); ?>" 
								data-pk-value="<?php echo esc_attr( $row[ $primary_key ] ); ?>">
							<?php esc_html_e( 'Edit', 'Lukic-code-snippets' ); ?>
						</button>
						<?php else : ?>
						<em><?php esc_html_e( 'No PK', 'Lukic-code-snippets' ); ?></em>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'    => $html,
				'total'   => $total_rows,
				'pages'   => $total_pages,
				'current' => $page,
			)
		);
	}

	/**
	 * AJAX handler for exporting table data to CSV
	 */
	public function ajax_export_table() {
		// Security check
		check_ajax_referer( 'Lukic_db_tables_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions.', 'Lukic-code-snippets' ) );
		}

		$table = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
		global $wpdb;
		$table = $this->validate_table_name( $table );
		if ( ! $table ) {
			wp_send_json_error( __( 'Invalid table specified.', 'Lukic-code-snippets' ) );
		}

		// Get all data from the table
		$rows = $wpdb->get_results( "SELECT * FROM `{$table}`", ARRAY_A );

		if ( empty( $rows ) ) {
			wp_send_json_error( __( 'No data to export.', 'Lukic-code-snippets' ) );
		}

		// Generate CSV content
		$csv = array();

		// Add headers
		$headers = array_keys( $rows[0] );
		$csv[]   = '"' . implode( '","', $headers ) . '"';

		// Add rows
		foreach ( $rows as $row ) {
			$csv_row = array();
			foreach ( $row as $value ) {
				$csv_row[] = '"' . str_replace( '"', '""', $value ) . '"';
			}
			$csv[] = implode( ',', $csv_row );
		}

		$csv_content = implode( "\n", $csv );

		wp_send_json_success(
			array(
				'filename' => sanitize_file_name( $table ) . '-export-' . date( 'Y-m-d' ) . '.csv',
				'content'  => $csv_content,
			)
		);
	}

	/**
	 * AJAX handler for getting a single table row for editing
	 */
	public function ajax_get_table_row() {
		// Security check
		check_ajax_referer( 'Lukic_db_tables_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions.', 'Lukic-code-snippets' ) );
		}

		$table         = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
		$primary_key   = isset( $_POST['primary_key'] ) ? sanitize_text_field( $_POST['primary_key'] ) : '';
		$primary_value = isset( $_POST['primary_value'] ) ? sanitize_text_field( $_POST['primary_value'] ) : '';
		global $wpdb;
		$table = $this->validate_table_name( $table );
		if ( ! $table ) {
			wp_send_json_error( __( 'Invalid table specified.', 'Lukic-code-snippets' ) );
		}

		$primary_key = $this->validate_column_name( $primary_key );
		if ( ! $primary_key ) {
			wp_send_json_error( __( 'Invalid primary key.', 'Lukic-code-snippets' ) );
		}

		if ( empty( $primary_value ) ) {
			wp_send_json_error( __( 'Missing required parameters.', 'Lukic-code-snippets' ) );
		}

		// Get table structure
		$columns      = $wpdb->get_results( "DESCRIBE `{$table}`" );
		$column_names = wp_list_pluck( $columns, 'Field' );
		if ( ! in_array( $primary_key, $column_names, true ) ) {
			wp_send_json_error( __( 'Invalid primary key.', 'Lukic-code-snippets' ) );
		}

		// Get the specific row
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE `{$primary_key}` = %s",
				$primary_value
			),
			ARRAY_A
		);

		if ( ! $row ) {
			wp_send_json_error( __( 'Row not found.', 'Lukic-code-snippets' ) );
		}

		wp_send_json_success(
			array(
				'row'         => $row,
				'columns'     => $columns,
				'primary_key' => $primary_key,
			)
		);
	}

	/**
	 * AJAX handler for updating a table row
	 */
	public function ajax_update_table_row() {
		// Security check
		check_ajax_referer( 'Lukic_db_tables_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions.', 'Lukic-code-snippets' ) );
		}

		$table         = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
		$primary_key   = isset( $_POST['primary_key'] ) ? sanitize_text_field( $_POST['primary_key'] ) : '';
		$primary_value = isset( $_POST['primary_value'] ) ? sanitize_text_field( $_POST['primary_value'] ) : '';
		$row_data      = isset( $_POST['row_data'] ) ? $_POST['row_data'] : array();
		global $wpdb;
		$table = $this->validate_table_name( $table );
		if ( ! $table ) {
			wp_send_json_error( __( 'Invalid table specified.', 'Lukic-code-snippets' ) );
		}

		$primary_key = $this->validate_column_name( $primary_key );
		if ( ! $primary_key || empty( $primary_value ) || empty( $row_data ) ) {
			wp_send_json_error( __( 'Missing required parameters.', 'Lukic-code-snippets' ) );
		}

		$columns      = $wpdb->get_results( "DESCRIBE `{$table}`" );
		$column_names = wp_list_pluck( $columns, 'Field' );
		// Sanitize the row data
		$update_data   = array();
		$update_format = array();
		foreach ( $row_data as $column => $value ) {
			$column = $this->validate_column_name( $column );
			if ( ! $column || ! in_array( $column, $column_names, true ) ) {
				continue;
			}

			// Handle NULL values
			if ( $value === 'NULL' || $value === '' ) {
				$update_data[ $column ] = null;
				$update_format[]        = '%s';
			} else {
				$update_data[ $column ] = sanitize_textarea_field( $value );
				$update_format[]        = '%s';
			}
		}

		// Remove primary key from update data (shouldn't be updated)
		unset( $update_data[ $primary_key ] );

		if ( empty( $update_data ) ) {
			wp_send_json_error( __( 'No data to update.', 'Lukic-code-snippets' ) );
		}

		// Update the row
		$result = $wpdb->update(
			$table,
			$update_data,
			array( $primary_key => $primary_value ),
			$update_format,
			array( '%s' )
		);

		if ( $result === false ) {
			wp_send_json_error( __( 'Failed to update row.', 'Lukic-code-snippets' ) . ' ' . $wpdb->last_error );
		}

		wp_send_json_success( __( 'Row updated successfully.', 'Lukic-code-snippets' ) );
	}

	/**
	 * AJAX handler for searching table data
	 */
	public function ajax_search_table_data() {

		// Security check
		check_ajax_referer( 'Lukic_db_tables_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions.', 'Lukic-code-snippets' ) );
		}

		$table       = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
		$search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( $_POST['search_term'] ) : '';
		$page        = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$per_page    = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 20;
		global $wpdb;
		$table = $this->validate_table_name( $table );
		if ( ! $table ) {
			wp_send_json_error( __( 'Invalid table specified.', 'Lukic-code-snippets' ) );
		}

		// Get table structure to build search query
		$columns = $wpdb->get_results( "DESCRIBE `{$table}`" );

		if ( empty( $columns ) ) {
			wp_send_json_error( __( 'Could not retrieve table structure.', 'Lukic-code-snippets' ) );
		}

		// Build WHERE clause for search
		$where_conditions = array();
		$search_value     = '%' . $wpdb->esc_like( $search_term ) . '%';

		foreach ( $columns as $column ) {
			$where_conditions[] = "`{$column->Field}` LIKE %s";
		}

		$where_clause = '';
		if ( ! empty( $search_term ) && ! empty( $where_conditions ) ) {
			$where_clause = 'WHERE (' . implode( ' OR ', $where_conditions ) . ')';
		}

		// Prepare search values array
		$search_values = array();
		if ( ! empty( $search_term ) ) {
			$search_values = array_fill( 0, count( $where_conditions ), $search_value );
		}

		// Get total rows count for search
		$count_query = "SELECT COUNT(*) FROM `{$table}` {$where_clause}";
		if ( ! empty( $search_values ) ) {
			$total_rows = $wpdb->get_var( $wpdb->prepare( $count_query, $search_values ) );
		} else {
			$total_rows = $wpdb->get_var( $count_query );
		}

		// Calculate offset
		$offset = ( $page - 1 ) * $per_page;

		// Get data with search and pagination
		$data_query = "SELECT * FROM `{$table}` {$where_clause} LIMIT {$offset}, {$per_page}";
		if ( ! empty( $search_values ) ) {
			$rows = $wpdb->get_results( $wpdb->prepare( $data_query, $search_values ), ARRAY_A );
		} else {
			$rows = $wpdb->get_results( $data_query, ARRAY_A );
		}

		if ( $rows === null ) {
			wp_send_json_error( __( 'Error retrieving data from table.', 'Lukic-code-snippets' ) . ' ' . $wpdb->last_error );
		}

		if ( empty( $rows ) ) {
			ob_start();
			?>
			<div class="notice notice-info">
				<p>
				<?php
				if ( ! empty( $search_term ) ) {
					/* translators: %s: The search term entered by the user */
					printf( __( 'No results found for "%s".', 'Lukic-code-snippets' ), esc_html( $search_term ) );
				} else {
					esc_html_e( 'This table does not contain any data.', 'Lukic-code-snippets' );
				}
				?>
				</p>
			</div>
			<?php
			$html = ob_get_clean();

			wp_send_json_success(
				array(
					'html'        => $html,
					'total'       => 0,
					'pages'       => 0,
					'current'     => 1,
					'search_term' => $search_term,
				)
			);
		}

		// Get table structure to identify primary key
		$primary_key = null;
		foreach ( $columns as $column ) {
			if ( $column->Key === 'PRI' ) {
				$primary_key = $column->Field;
				break;
			}
		}

		// Calculate total pages
		$total_pages = ceil( $total_rows / $per_page );

		ob_start();
		?>
		<table class="wp-list-table widefat fixed striped Lukic-data-table">
			<thead>
				<tr>
					<?php foreach ( array_keys( $rows[0] ) as $column ) : ?>
					<th><?php echo esc_html( $column ); ?></th>
					<?php endforeach; ?>
					<th><?php esc_html_e( 'Actions', 'Lukic-code-snippets' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row_index => $row ) : ?>
				<tr>
					<?php foreach ( $row as $column => $value ) : ?>
					<td>
						<?php
						$display_value = is_null( $value ) ? '<em>NULL</em>' : esc_html( substr( maybe_serialize( $value ), 0, 100 ) );

						// Highlight search term if present
						if ( ! empty( $search_term ) && ! is_null( $value ) ) {
							$display_value = str_ireplace(
								esc_html( $search_term ),
								'<mark>' . esc_html( $search_term ) . '</mark>',
								$display_value
							);
						}

						echo wp_kses_post( $display_value );
						?>
					</td>
					<?php endforeach; ?>
					<td>
						<?php if ( $primary_key && isset( $row[ $primary_key ] ) ) : ?>
						<button class="button Lukic-edit-btn edit-row" 
								data-table="<?php echo esc_attr( $table ); ?>" 
								data-pk="<?php echo esc_attr( $primary_key ); ?>" 
								data-pk-value="<?php echo esc_attr( $row[ $primary_key ] ); ?>">
							<?php esc_html_e( 'Edit', 'Lukic-code-snippets' ); ?>
						</button>
						<?php else : ?>
						<em><?php esc_html_e( 'No PK', 'Lukic-code-snippets' ); ?></em>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'        => $html,
				'total'       => $total_rows,
				'pages'       => $total_pages,
				'current'     => $page,
				'search_term' => $search_term,
			)
		);
	}

	/**
	 * Validate provided table name against allowed characters and existence.
	 *
	 * @param string $table Raw table name.
	 * @return string|false
	 */
	private function validate_table_name( $table ) {

		$table = trim( $table );
		if ( $table === '' || ! preg_match( '/^[A-Za-z0-9_]+$/', $table ) ) {
			return false;
		}

		global $wpdb;
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		return $exists ? $table : false;
	}

	/**
	 * Validate a column/identifier name.
	 *
	 * @param string $identifier Raw identifier.
	 * @return string|false
	 */
	private function validate_column_name( $identifier ) {

		$identifier = trim( $identifier );
		if ( $identifier === '' || ! preg_match( '/^[A-Za-z0-9_]+$/', $identifier ) ) {
			return false;
		}

		return $identifier;
	}
}

// Initialize the DB Tables Manager
new Lukic_DB_Tables_Manager();
