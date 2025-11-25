<?php
/**
 * Upload Limits Control Snippet
 *
 * Allows administrators to easily adjust PHP upload limits including:
 * - Maximum upload file size
 * - PHP execution time
 * - PHP memory limit
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lukic_Upload_Limits {
	/**
	 * Option name for storing settings
	 */
	private $option_name = 'Lukic_upload_limits';

	/**
	 * Current settings
	 */
	private $settings;

	/**
	 * Default settings
	 */
	private $defaults = array(
		'upload_max_filesize' => '10M',
		'max_execution_time'  => '300',
		'memory_limit'        => '256M',
	);

	/**
	 * Available file size options
	 */
	private $file_size_options = array(
		'2M'    => '2 MB',
		'5M'    => '5 MB',
		'10M'   => '10 MB',
		'20M'   => '20 MB',
		'50M'   => '50 MB',
		'100M'  => '100 MB',
		'200M'  => '200 MB',
		'500M'  => '500 MB',
		'1000M' => '1 GB',
	);

	/**
	 * Available memory limit options
	 */
	private $memory_limit_options = array(
		'64M'   => '64 MB',
		'128M'  => '128 MB',
		'256M'  => '256 MB',
		'512M'  => '512 MB',
		'1024M' => '1 GB',
		'2048M' => '2 GB',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize the snippet
		add_action( 'init', array( $this, 'init' ) );

		// Add submenu page
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 20 );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Apply upload limits
		add_action( 'admin_init', array( $this, 'apply_upload_limits' ) );

		// Add AJAX handlers
		add_action( 'wp_ajax_Lukic_refresh_php_settings', array( $this, 'ajax_refresh_php_settings' ) );
		add_action( 'wp_ajax_Lukic_test_upload', array( $this, 'ajax_test_upload' ) );
	}

	/**
	 * Initialize the snippet
	 */
	public function init() {
		// Get saved settings or use defaults
		$this->settings = get_option( $this->option_name, $this->defaults );
	}

	/**
	 * Add submenu page
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'lukic-code-snippets',
			__( 'Control Upload Limits', 'Lukic-code-snippets' ),
			__( 'Upload Limits', 'Lukic-code-snippets' ),
			'manage_options',
			'lukic-upload-limits',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting(
			'Lukic_upload_limits_group',
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);
	}

	/**
	 * Sanitize settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Sanitize upload_max_filesize
		if ( isset( $input['upload_max_filesize'] ) && array_key_exists( $input['upload_max_filesize'], $this->file_size_options ) ) {
			$sanitized['upload_max_filesize'] = $input['upload_max_filesize'];
		} else {
			$sanitized['upload_max_filesize'] = $this->defaults['upload_max_filesize'];
		}

		// Sanitize max_execution_time
		if ( isset( $input['max_execution_time'] ) ) {
			$sanitized['max_execution_time'] = absint( $input['max_execution_time'] );
		} else {
			$sanitized['max_execution_time'] = $this->defaults['max_execution_time'];
		}

		// Sanitize memory_limit
		if ( isset( $input['memory_limit'] ) && array_key_exists( $input['memory_limit'], $this->memory_limit_options ) ) {
			$sanitized['memory_limit'] = $input['memory_limit'];
		} else {
			$sanitized['memory_limit'] = $this->defaults['memory_limit'];
		}

		return $sanitized;
	}

	/**
	 * Apply upload limits
	 */
	public function apply_upload_limits() {
		// Only proceed if we're in the admin area
		if ( ! is_admin() ) {
			return;
		}

		// Get current settings
		$settings = $this->settings;

		// Try to set runtime values (will only work for some settings)
		@ini_set( 'max_execution_time', $settings['max_execution_time'] );
		@ini_set( 'memory_limit', $settings['memory_limit'] );

		// For Apache servers, try to update .htaccess
		if ( $this->is_apache_server() ) {
			$this->update_htaccess( $settings );
		}
	}

	/**
	 * Check if running on Apache server
	 */
	private function is_apache_server() {
		return ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false );
	}

	/**
	 * Update .htaccess file with PHP settings
	 */
	private function update_htaccess( $settings ) {
		// Get .htaccess file path
		$htaccess_file = ABSPATH . '.htaccess';

		// Check if file exists and is writable
		if ( ! file_exists( $htaccess_file ) || ! is_writable( $htaccess_file ) ) {
			return false;
		}

		// Get current .htaccess content
		$htaccess_content = file_get_contents( $htaccess_file );

		// Remove existing Lukic upload limits if they exist
		$pattern          = '/\# BEGIN Lukic Upload Limits.*?\# END Lukic Upload Limits\s*/s';
		$htaccess_content = preg_replace( $pattern, '', $htaccess_content );

		// Create new PHP limits section
		$new_limits  = "\n# BEGIN Lukic Upload Limits\n";
		$new_limits .= "<IfModule mod_php7.c>\n";
		$new_limits .= "  php_value upload_max_filesize {$settings['upload_max_filesize']}\n";
		$new_limits .= "  php_value post_max_size {$settings['upload_max_filesize']}\n";
		$new_limits .= "  php_value max_execution_time {$settings['max_execution_time']}\n";
		$new_limits .= "  php_value memory_limit {$settings['memory_limit']}\n";
		$new_limits .= "</IfModule>\n";
		$new_limits .= "<IfModule mod_php.c>\n";
		$new_limits .= "  php_value upload_max_filesize {$settings['upload_max_filesize']}\n";
		$new_limits .= "  php_value post_max_size {$settings['upload_max_filesize']}\n";
		$new_limits .= "  php_value max_execution_time {$settings['max_execution_time']}\n";
		$new_limits .= "  php_value memory_limit {$settings['memory_limit']}\n";
		$new_limits .= "</IfModule>\n";
		$new_limits .= "# END Lukic Upload Limits\n";

		// Add new limits to .htaccess
		$htaccess_content .= $new_limits;

		// Write updated content back to .htaccess
		return @file_put_contents( $htaccess_file, $htaccess_content );
	}

	/**
	 * Get current PHP settings
	 */
	private function get_current_php_settings() {
		return array(
			'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
			'post_max_size'       => ini_get( 'post_max_size' ),
			'max_execution_time'  => ini_get( 'max_execution_time' ),
			'memory_limit'        => ini_get( 'memory_limit' ),
		);
	}

	/**
	 * AJAX handler for refreshing PHP settings
	 */
	public function ajax_refresh_php_settings() {
		// Check nonce
		check_ajax_referer( 'Lukic_upload_test_nonce', 'nonce' );

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		// Get current PHP settings
		$current_settings = $this->get_current_php_settings();

		// Add timestamp
		$current_settings['timestamp'] = date( 'Y-m-d H:i:s' );

		// Send response
		wp_send_json_success( $current_settings );
	}

	/**
	 * AJAX handler for testing uploads
	 */
	public function ajax_test_upload() {
		// Check nonce
		check_ajax_referer( 'Lukic_upload_test_nonce', 'nonce' );

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		// Check if file was uploaded
		if ( ! isset( $_FILES['test_file'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No file was uploaded.', 'Lukic-code-snippets' ),
				)
			);
		}

		$file = $_FILES['test_file'];

		// Check for upload errors
		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			$error_message = $this->get_upload_error_message( $file['error'] );
			wp_send_json_error(
				array(
					'message'   => $error_message,
					'file_name' => sanitize_text_field( $file['name'] ),
					'file_size' => size_format( $file['size'] ),
					'status'    => 'error',
				)
			);
		}

		// File uploaded successfully
		wp_send_json_success(
			array(
				'message'   => __( 'File uploaded successfully! Your upload limits are working correctly.', 'Lukic-code-snippets' ),
				'file_name' => sanitize_text_field( $file['name'] ),
				'file_size' => size_format( $file['size'] ),
				'status'    => 'success',
			)
		);
	}

	/**
	 * Get upload error message
	 */
	private function get_upload_error_message( $error_code ) {
		switch ( $error_code ) {
			case UPLOAD_ERR_INI_SIZE:
				return __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'Lukic-code-snippets' );
			case UPLOAD_ERR_FORM_SIZE:
				return __( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'Lukic-code-snippets' );
			case UPLOAD_ERR_PARTIAL:
				return __( 'The uploaded file was only partially uploaded.', 'Lukic-code-snippets' );
			case UPLOAD_ERR_NO_FILE:
				return __( 'No file was uploaded.', 'Lukic-code-snippets' );
			case UPLOAD_ERR_NO_TMP_DIR:
				return __( 'Missing a temporary folder.', 'Lukic-code-snippets' );
			case UPLOAD_ERR_CANT_WRITE:
				return __( 'Failed to write file to disk.', 'Lukic-code-snippets' );
			case UPLOAD_ERR_EXTENSION:
				return __( 'A PHP extension stopped the file upload.', 'Lukic-code-snippets' );
			default:
				return __( 'Unknown upload error.', 'Lukic-code-snippets' );
		}
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get current settings
		$settings = $this->settings;

		// Get current PHP settings
		$current_php_settings = $this->get_current_php_settings();

		// Check if settings were saved
		$settings_saved = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true';

		// Prepare stats for header
		$stats = array(
			array(
				'count' => $current_php_settings['upload_max_filesize'],
				'label' => __( 'Upload Limit', 'Lukic-code-snippets' ),
			),
			array(
				'count' => $current_php_settings['memory_limit'],
				'label' => __( 'Memory Limit', 'Lukic-code-snippets' ),
			),
		);

		// Register and enqueue scripts
		wp_enqueue_script( 'jquery' );
		wp_register_script(
			'Lukic-upload-limits-js',
			plugin_dir_url( __DIR__ ) . 'assets/js/upload-limits.js',
			array( 'jquery' ),
			Lukic_SNIPPET_CODES_VERSION,
			true
		);

		wp_localize_script(
			'Lukic-upload-limits-js',
			'Lukic_upload_vars',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'Lukic_upload_test_nonce' ),
				'refreshing' => __( 'Refreshing...', 'Lukic-code-snippets' ),
			)
		);

		wp_enqueue_script( 'Lukic-upload-limits-js' );

		?>
		<div class="wrap Lukic-wrap">
			<?php
			// Include header
			// Header component is already loaded in main plugin file
			Lukic_display_header( __( 'Control Upload Limits', 'Lukic-code-snippets' ), $stats );
			?>
			
			<?php if ( $settings_saved ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved. Note that some hosting environments may restrict changing these values.', 'Lukic-code-snippets' ); ?></p>
				</div>
			<?php endif; ?>
			
			<?php if ( $this->is_apache_server() ) : ?>
				<div class="notice notice-success">
					<p><strong><?php esc_html_e( 'Apache Server Detected:', 'Lukic-code-snippets' ); ?></strong> <?php esc_html_e( 'This snippet can modify your .htaccess file to set PHP limits. After saving, you may need to reload your site for changes to take effect.', 'Lukic-code-snippets' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-warning">
					<p><strong><?php esc_html_e( 'Non-Apache Server Detected:', 'Lukic-code-snippets' ); ?></strong> <?php esc_html_e( 'Your server configuration may not allow changing these values via .htaccess. You may need to contact your hosting provider to change these limits.', 'Lukic-code-snippets' ); ?></p>
				</div>
			<?php endif; ?>
			
			<?php
			// Check if .htaccess is writable
			$htaccess_file     = ABSPATH . '.htaccess';
			$htaccess_writable = file_exists( $htaccess_file ) && is_writable( $htaccess_file );

			if ( $this->is_apache_server() && ! $htaccess_writable ) :
				?>
				<div class="notice notice-error">
					<p><strong><?php esc_html_e( 'Warning:', 'Lukic-code-snippets' ); ?></strong> <?php esc_html_e( 'Your .htaccess file is not writable. The plugin cannot automatically update your PHP limits.', 'Lukic-code-snippets' ); ?></p>
				</div>
			<?php endif; ?>
			
			<div class="Lukic-settings-container">
				<div class="Lukic-settings-section">
					<h2><?php esc_html_e( 'Current PHP Settings', 'Lukic-code-snippets' ); ?></h2>
					<p><?php esc_html_e( 'These are the actual values currently in use by PHP on your server.', 'Lukic-code-snippets' ); ?></p>
					<table class="widefat" id="current-php-settings-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Setting', 'Lukic-code-snippets' ); ?></th>
								<th><?php esc_html_e( 'Current Value', 'Lukic-code-snippets' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Upload Max Filesize', 'Lukic-code-snippets' ); ?></td>
								<td><code id="current-upload-max-filesize"><?php echo esc_html( $current_php_settings['upload_max_filesize'] ); ?></code></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Post Max Size', 'Lukic-code-snippets' ); ?></td>
								<td><code id="current-post-max-size"><?php echo esc_html( $current_php_settings['post_max_size'] ); ?></code></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Max Execution Time', 'Lukic-code-snippets' ); ?></td>
								<td><code id="current-max-execution-time"><?php echo esc_html( $current_php_settings['max_execution_time'] ); ?> <?php esc_html_e( 'seconds', 'Lukic-code-snippets' ); ?></code></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Memory Limit', 'Lukic-code-snippets' ); ?></td>
								<td><code id="current-memory-limit"><?php echo esc_html( $current_php_settings['memory_limit'] ); ?></code></td>
							</tr>
						</tbody>
					</table>
					<p class="description"><?php esc_html_e( 'Last updated: ', 'Lukic-code-snippets' ); ?><span id="last-updated-time"><?php echo date( 'Y-m-d H:i:s' ); ?></span></p>
					<p>
						<button type="button" id="refresh-php-settings" class="button button-secondary">
							<span class="dashicons dashicons-update" style="margin-top: 3px;"></span> 
							<?php esc_html_e( 'Refresh Values', 'Lukic-code-snippets' ); ?>
						</button>
					</p>
				</div>
				
				<div class="Lukic-settings-section">
					<h2><?php esc_html_e( 'Test Upload Limits', 'Lukic-code-snippets' ); ?></h2>
					<p><?php esc_html_e( 'Use this tool to test if your upload limits are working correctly.', 'Lukic-code-snippets' ); ?></p>
					
					<div id="upload-test-container">
						<div class="upload-test-form">
							<input type="file" id="test-upload-file" class="test-upload-input" />
							<button type="button" id="test-upload-button" class="button button-primary Lukic-button">
								<?php esc_html_e( 'Test Upload', 'Lukic-code-snippets' ); ?>
							</button>
						</div>
						
						<div id="upload-test-results" style="display: none;">
							<h3><?php esc_html_e( 'Test Results', 'Lukic-code-snippets' ); ?></h3>
							<div class="upload-test-message"></div>
							<table class="widefat">
								<tr>
									<th><?php esc_html_e( 'File Name', 'Lukic-code-snippets' ); ?></th>
									<td class="test-file-name"></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'File Size', 'Lukic-code-snippets' ); ?></th>
									<td class="test-file-size"></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Status', 'Lukic-code-snippets' ); ?></th>
									<td class="test-status"></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				
				<div class="Lukic-settings-section">
					<h2><?php esc_html_e( 'Adjust PHP Limits', 'Lukic-code-snippets' ); ?></h2>
					<form method="post" action="options.php" id="upload-limits-form">
						<?php
						settings_fields( 'Lukic_upload_limits_group' );
						?>
						
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="upload_max_filesize"><?php esc_html_e( 'Upload Max Filesize', 'Lukic-code-snippets' ); ?></label>
								</th>
								<td>
									<select name="<?php echo esc_attr( $this->option_name ); ?>[upload_max_filesize]" id="upload_max_filesize" class="regular-text">
										<?php foreach ( $this->file_size_options as $value => $label ) : ?>
											<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['upload_max_filesize'], $value ); ?>>
												<?php echo esc_html( $label ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Maximum allowed size for uploaded files.', 'Lukic-code-snippets' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="max_execution_time"><?php esc_html_e( 'Max Execution Time', 'Lukic-code-snippets' ); ?></label>
								</th>
								<td>
									<input type="number" name="<?php echo esc_attr( $this->option_name ); ?>[max_execution_time]" id="max_execution_time" class="regular-text" value="<?php echo esc_attr( $settings['max_execution_time'] ); ?>" min="30" max="3600" step="30">
									<p class="description"><?php esc_html_e( 'Maximum time in seconds a script is allowed to run before it is terminated.', 'Lukic-code-snippets' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="memory_limit"><?php esc_html_e( 'Memory Limit', 'Lukic-code-snippets' ); ?></label>
								</th>
								<td>
									<select name="<?php echo esc_attr( $this->option_name ); ?>[memory_limit]" id="memory_limit" class="regular-text">
										<?php foreach ( $this->memory_limit_options as $value => $label ) : ?>
											<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['memory_limit'], $value ); ?>>
												<?php echo esc_html( $label ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Maximum amount of memory a script may consume.', 'Lukic-code-snippets' ); ?></p>
								</td>
							</tr>
						</table>
						
						<div id="save-settings-container">
							<?php submit_button( __( 'Save Changes', 'Lukic-code-snippets' ), 'primary', 'submit', true, array( 'id' => 'save-settings-button' ) ); ?>
							<span id="settings-saving-indicator" style="display:none; margin-left: 10px;">
								<span class="spinner is-active" style="float:none; margin-top:0;"></span> 
								<?php esc_html_e( 'Saving and applying changes...', 'Lukic-code-snippets' ); ?>
							</span>
							<span id="settings-saved-indicator" style="display:none; margin-left: 10px; color: green;">
								<span class="dashicons dashicons-yes-alt"></span> 
								<?php esc_html_e( 'Changes saved and applied!', 'Lukic-code-snippets' ); ?>
							</span>
						</div>
					</form>
				</div>
				
				<div class="Lukic-settings-section">
					<h2><?php esc_html_e( 'Important Notes', 'Lukic-code-snippets' ); ?></h2>
					<ul class="ul-disc">
						<?php if ( $this->is_apache_server() ) : ?>
							<li><?php esc_html_e( '<strong>How This Works:</strong> On Apache servers, this snippet modifies your .htaccess file to set PHP limits. This is more reliable than using ini_set().', 'Lukic-code-snippets' ); ?></li>
							<li><?php esc_html_e( '<strong>After Saving:</strong> You may need to reload your site or wait a few minutes for the changes to take effect.', 'Lukic-code-snippets' ); ?></li>
							<li><?php esc_html_e( '<strong>If Not Working:</strong> Your server might be using PHP-FPM or another configuration that ignores .htaccess PHP settings.', 'Lukic-code-snippets' ); ?></li>
						<?php else : ?>
							<li><?php esc_html_e( '<strong>Server Limitation:</strong> Your server type (Nginx, IIS, etc.) does not support changing PHP settings via .htaccess.', 'Lukic-code-snippets' ); ?></li>
							<li><?php esc_html_e( '<strong>Alternative Solution:</strong> Contact your hosting provider to increase these limits or modify the server configuration files.', 'Lukic-code-snippets' ); ?></li>
						<?php endif; ?>
						<li><?php esc_html_e( '<strong>Memory & Execution Time:</strong> These settings can sometimes be changed at runtime and may work regardless of server type.', 'Lukic-code-snippets' ); ?></li>
						<li><?php esc_html_e( '<strong>Current Values:</strong> The "Current PHP Settings" table shows the actual values in use by PHP. After saving, refresh the page to see if your changes took effect.', 'Lukic-code-snippets' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
		
		<style>
			.Lukic-settings-section {
				background: #fff;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
				padding: 20px;
				margin-bottom: 20px;
			}
			.Lukic-settings-section h2 {
				margin-top: 0;
				padding-bottom: 10px;
				border-bottom: 1px solid #eee;
			}
			.ul-disc {
				list-style: disc;
				margin-left: 20px;
			}
			
			/* Custom button styles */
			.Lukic-button,
			.Lukic-settings-section .button-primary,
			#test-upload-button {
				background-color: var(--Lukic-primary, #00E1AF) !important;
				border-color: var(--Lukic-primary, #00E1AF) !important;
				color: #fff !important;
				text-shadow: none !important;
				box-shadow: none !important;
				transition: all 0.2s ease;
			}
			
			.Lukic-button:hover,
			.Lukic-settings-section .button-primary:hover,
			#test-upload-button:hover {
				background-color: var(--Lukic-primary-hover, #00c99e) !important;
				border-color: var(--Lukic-primary-hover, #00c99e) !important;
			}
			
			.Lukic-button:focus,
			.Lukic-settings-section .button-primary:focus,
			#test-upload-button:focus {
				box-shadow: 0 0 0 1px #fff, 0 0 0 3px var(--Lukic-primary-focus, rgba(0, 225, 175, 0.2)) !important;
			}
			
			#refresh-php-settings {
				background-color: #f7f7f7;
				border-color: #ccc;
				color: #555;
			}
			
			#refresh-php-settings:hover {
				background-color: #f0f0f0;
				border-color: #999;
			}
			
			/* Test upload form styling */
			.upload-test-form {
				display: flex;
				align-items: center;
				gap: 10px;
				margin-bottom: 20px;
			}
			
			.test-upload-input {
				flex: 1;
			}
			
			/* Table styling */
			.widefat {
				border-collapse: collapse;
				margin-top: 10px;
				margin-bottom: 20px;
				width: 100%;
			}
			
			.widefat th {
				font-weight: 600;
				text-align: left;
				padding: 8px 10px;
			}
			
			.widefat td {
				padding: 8px 10px;
			}
		</style>
		<?php
	}
}

// Initialize the snippet
$Lukic_upload_limits = new Lukic_Upload_Limits();
