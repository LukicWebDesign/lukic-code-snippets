<?php
/**
 * Maintenance Mode snippet
 *
 * Enables a customizable maintenance mode for the website
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Lukic_Maintenance_Mode {
	/**
	 * Default settings for maintenance mode
	 */
	private $defaults;
	/**
	 * Constructor
	 */
	public function __construct() {

		$this->defaults = self::get_default_settings();
		// Add submenu page under the main plugin menu
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 20 );

		// Register maintenance mode settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Display maintenance mode for visitors
		add_action( 'template_redirect', array( $this, 'display_maintenance_mode' ) );

		// Enqueue admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Get default settings array
	 *
	 * @return array
	 */
	public static function get_default_settings() {

		return array(
			'title'              => 'Website Under Maintenance',
			'subtitle'           => 'We\'ll be back soon',
			'message'            => 'We\'re currently working on making some improvements to the website. Please check back soon.',
			'background_image'   => '',
			'title_font_size'    => '36px',
			'subtitle_font_size' => '24px',
			'message_font_size'  => '16px',
			'title_color'        => '#ffffff',
			'subtitle_color'     => '#ffffff',
			'message_color'      => '#ffffff',
			'overlay_color'      => 'rgba(0, 0, 0, 0.7)',
			'exclude_ips'        => '',
			'exclude_paths'      => '/wp-admin/,/wp-login.php',
			'enabled'            => true,
		);
	}
	/**
	 * Add submenu page
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'lukic-code-snippets', // Parent slug
			__( 'Maintenance Mode', 'Lukic-code-snippets' ),
			__( 'Maintenance Mode', 'Lukic-code-snippets' ),
			'manage_options',
			'lukic-maintenance-mode',
			array( $this, 'display_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting(
			'Lukic_maintenance_mode',
			'Lukic_maintenance_mode_options',
			array( $this, 'sanitize_settings' )
		);
	}

	/**
	 * Sanitize settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized_input = array();

		// Text fields
		$sanitized_input['title']    = sanitize_text_field( $input['title'] ?? $this->defaults['title'] );
		$sanitized_input['subtitle'] = sanitize_text_field( $input['subtitle'] ?? $this->defaults['subtitle'] );
		$sanitized_input['message']  = wp_kses_post( $input['message'] ?? $this->defaults['message'] );

		// Background image
		$sanitized_input['background_image'] = esc_url_raw( $input['background_image'] ?? $this->defaults['background_image'] );

		// Font sizes (ensure they have valid CSS units)
		$sanitized_input['title_font_size']    = $this->sanitize_css_size( $input['title_font_size'] ?? $this->defaults['title_font_size'] );
		$sanitized_input['subtitle_font_size'] = $this->sanitize_css_size( $input['subtitle_font_size'] ?? $this->defaults['subtitle_font_size'] );
		$sanitized_input['message_font_size']  = $this->sanitize_css_size( $input['message_font_size'] ?? $this->defaults['message_font_size'] );

		// Colors (ensure they are valid hex colors or rgba)
		$sanitized_input['title_color']    = $this->sanitize_color( $input['title_color'] ?? $this->defaults['title_color'] );
		$sanitized_input['subtitle_color'] = $this->sanitize_color( $input['subtitle_color'] ?? $this->defaults['subtitle_color'] );
		$sanitized_input['message_color']  = $this->sanitize_color( $input['message_color'] ?? $this->defaults['message_color'] );
		$sanitized_input['overlay_color']  = $this->sanitize_color( $input['overlay_color'] ?? $this->defaults['overlay_color'] );

		// IP addresses and paths to exclude
		$sanitized_input['exclude_ips']   = sanitize_textarea_field( $input['exclude_ips'] ?? $this->defaults['exclude_ips'] );
		$sanitized_input['exclude_paths'] = sanitize_textarea_field( $input['exclude_paths'] ?? $this->defaults['exclude_paths'] );

		$sanitized_input['enabled'] = isset( $input['enabled'] ) ? 1 : 0;
		return $sanitized_input;
	}
	/**
	 * Sanitize CSS size value
	 */
	private function sanitize_css_size( $size ) {
		// Strip all characters except numbers, dots, and valid CSS units
		$size = trim( $size );
		if ( preg_match( '/^(\d*\.?\d+)(px|em|rem|%|vw|vh)$/', $size ) ) {
			return $size;
		}

		// If the size doesn't have a valid format, ensure it has 'px' at the end
		$size = preg_replace( '/[^0-9.]/', '', $size );
		return $size ? $size . 'px' : $this->defaults['title_font_size'];
	}

	/**
	 * Sanitize color value
	 */
	private function sanitize_color( $color ) {
		// Check if it's a hex color
		if ( preg_match( '/^#([a-fA-F0-9]{3}){1,2}$/', $color ) ) {
			return $color;
		}

		// Check if it's an rgba color
		if ( preg_match( '/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(?:,\s*[\d.]+\s*)?\)$/', $color ) ) {
			return $color;
		}

		// Default color if invalid
		return $this->defaults['title_color'];
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on our settings page
		if ( ! isset( $_GET['page'] ) || 'lukic-maintenance-mode' !== $_GET['page'] ) {
			return;
		}

		// Enqueue WordPress color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Enqueue WordPress media uploader
		wp_enqueue_media();

		// Enqueue custom admin script
		wp_enqueue_script(
			'Lukic-maintenance-mode-admin',
			plugin_dir_url( __DIR__ ) . 'assets/js/maintenance-mode-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			Lukic_SNIPPET_CODES_VERSION,
			true
		);

		// Enqueue custom admin styles
		wp_enqueue_style(
			'Lukic-maintenance-mode-admin-styles',
			plugin_dir_url( __DIR__ ) . 'assets/css/maintenance-mode-admin.css',
			array(),
			Lukic_SNIPPET_CODES_VERSION . '.' . time()
		);
	}

	/**
	 * Check if maintenance mode is currently active
	 *
	 * @return bool True if maintenance mode is active, false otherwise
	 */
	private function is_maintenance_mode_active() {

		return $this->is_snippet_enabled() && $this->is_toggle_enabled();
	}

	/**
	 * Check if snippet is enabled from main settings
	 */
	private function is_snippet_enabled() {

		$options = get_option( 'Lukic_snippet_codes_options', array() );
		return isset( $options['maintenance_mode'] ) && (int) $options['maintenance_mode'] === 1;
	}

	/**
	 * Check if maintenance mode switch is enabled in settings
	 */
	private function is_toggle_enabled() {

		$options = get_option( 'Lukic_maintenance_mode_options', array() );
		if ( isset( $options['enabled'] ) ) {
			return (bool) $options['enabled'];
		}
		return isset( $this->defaults['enabled'] ) ? (bool) $this->defaults['enabled'] : false;
	}
	/**
	 * Display settings page
	 */
	public function display_settings_page() {
		// Get saved options with defaults
		$options = wp_parse_args(
			get_option( 'Lukic_maintenance_mode_options', array() ),
			$this->defaults
		);

		// Default background image
		$default_bg = plugin_dir_url( __DIR__ ) . 'assets/images/maintenance-bg.svg';

		// If no background image is set, use the default
		if ( empty( $options['background_image'] ) ) {
			$options['background_image'] = $default_bg;
		}

		// Include the header partial
		// Header component is already loaded in main plugin file

		// Prepare stats for header
		$status = $this->is_maintenance_mode_active() ? __( 'Active', 'Lukic-code-snippets' ) : __( 'Inactive', 'Lukic-code-snippets' );
		$stats  = array(
			array(
				'count' => $status,
				'label' => __( 'Status', 'Lukic-code-snippets' ),
			),
		);
		?>
		<div class="wrap Lukic-settings-wrap">
			<?php Lukic_display_header( __( 'Maintenance Mode Settings', 'Lukic-code-snippets' ), $stats ); ?>
			
			<div class="Lukic-settings-intro">
				<p><?php esc_html_e( 'Customize how your maintenance mode page will look. Maintenance Mode will display when this snippet is enabled and the switch below is turned on.', 'Lukic-code-snippets' ); ?></p>
			</div>
			
			<div class="Lukic-settings-container">
				<div class="Lukic-settings-main">
					<form method="post" action="options.php" class="Lukic-maintenance-form">
						<?php settings_fields( 'Lukic_maintenance_mode' ); ?>
						
						<div class="Lukic-settings-section">
							<h2><?php esc_html_e( 'Maintenance Mode Status', 'Lukic-code-snippets' ); ?></h2>
							<div class="Lukic-field-row Lukic-field-row--inline">
								<div class="Lukic-field-text">
									<p><?php esc_html_e( 'Toggle maintenance mode on or off. Visitors will see your maintenance page only when this switch is enabled and the snippet is active.', 'Lukic-code-snippets' ); ?></p>
									<?php
									if ( ! $this->is_snippet_enabled() ) :
										?>
										<p class="description"><?php esc_html_e( 'Note: The Maintenance Mode snippet must be enabled on the main Snippets page.', 'Lukic-code-snippets' ); ?></p>
									<?php endif; ?>
								</div>
								<label class="Lukic-switch">
									<input type="checkbox" id="Lukic_maintenance_enabled" name="Lukic_maintenance_mode_options[enabled]" value="1" <?php checked( ! empty( $options['enabled'] ) ); ?>>
									<span class="Lukic-slider"></span>
								</label>
							</div>
						</div>
						
						<div class="Lukic-settings-section">
							<h2><?php esc_html_e( 'Content Settings', 'Lukic-code-snippets' ); ?></h2>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_title">
									<?php esc_html_e( 'Title', 'Lukic-code-snippets' ); ?>
								</label>
								<input type="text" id="Lukic_maintenance_title" name="Lukic_maintenance_mode_options[title]" value="<?php echo esc_attr( $options['title'] ); ?>" class="regular-text">
							</div>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_subtitle">
									<?php esc_html_e( 'Subtitle', 'Lukic-code-snippets' ); ?>
								</label>
								<input type="text" id="Lukic_maintenance_subtitle" name="Lukic_maintenance_mode_options[subtitle]" value="<?php echo esc_attr( $options['subtitle'] ); ?>" class="regular-text">
							</div>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_message">
									<?php esc_html_e( 'Message', 'Lukic-code-snippets' ); ?>
								</label>
								<textarea id="Lukic_maintenance_message" name="Lukic_maintenance_mode_options[message]" rows="5" class="large-text"><?php echo esc_textarea( $options['message'] ); ?></textarea>
							</div>
						</div>
						
						<div class="Lukic-settings-section">
							<h2><?php esc_html_e( 'Background Image', 'Lukic-code-snippets' ); ?></h2>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_bg_image">
									<?php esc_html_e( 'Background Image', 'Lukic-code-snippets' ); ?>
								</label>
								<div class="Lukic-media-field">
									<input type="hidden" id="Lukic_maintenance_bg_image" name="Lukic_maintenance_mode_options[background_image]" value="<?php echo esc_attr( $options['background_image'] ); ?>">
									<div class="Lukic-media-preview">
										<img src="<?php echo esc_url( $options['background_image'] ); ?>" alt="<?php esc_html_e( 'Background Image Preview', 'Lukic-code-snippets' ); ?>">
									</div>
									<div class="Lukic-media-buttons">
										<button type="button" class="button Lukic-upload-image"><?php esc_html_e( 'Upload Image', 'Lukic-code-snippets' ); ?></button>
										<button type="button" class="button Lukic-reset-image" data-default="<?php echo esc_attr( $default_bg ); ?>"><?php esc_html_e( 'Reset to Default', 'Lukic-code-snippets' ); ?></button>
									</div>
								</div>
							</div>
						</div>
						
						<div class="Lukic-settings-section">
							<h2><?php esc_html_e( 'Styling Options', 'Lukic-code-snippets' ); ?></h2>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_title_font_size">
									<?php esc_html_e( 'Title Font Size', 'Lukic-code-snippets' ); ?>
								</label>
								<input type="text" id="Lukic_maintenance_title_font_size" name="Lukic_maintenance_mode_options[title_font_size]" value="<?php echo esc_attr( $options['title_font_size'] ); ?>" class="small-text">
								<p class="description"><?php esc_html_e( 'e.g. 36px, 2.5em, 5vw', 'Lukic-code-snippets' ); ?></p>
							</div>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_subtitle_font_size">
									<?php esc_html_e( 'Subtitle Font Size', 'Lukic-code-snippets' ); ?>
								</label>
								<input type="text" id="Lukic_maintenance_subtitle_font_size" name="Lukic_maintenance_mode_options[subtitle_font_size]" value="<?php echo esc_attr( $options['subtitle_font_size'] ); ?>" class="small-text">
								<p class="description"><?php esc_html_e( 'e.g. 24px, 1.5em, 3vw', 'Lukic-code-snippets' ); ?></p>
							</div>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_message_font_size">
									<?php esc_html_e( 'Message Font Size', 'Lukic-code-snippets' ); ?>
								</label>
								<input type="text" id="Lukic_maintenance_message_font_size" name="Lukic_maintenance_mode_options[message_font_size]" value="<?php echo esc_attr( $options['message_font_size'] ); ?>" class="small-text">
								<p class="description"><?php esc_html_e( 'e.g. 16px, 1em, 2vw', 'Lukic-code-snippets' ); ?></p>
							</div>
							
							<div class="Lukic-grid-2-col">
								<div class="Lukic-field-row">
									<label for="Lukic_maintenance_title_color">
										<?php esc_html_e( 'Title Color', 'Lukic-code-snippets' ); ?>
									</label>
									<input type="text" id="Lukic_maintenance_title_color" name="Lukic_maintenance_mode_options[title_color]" value="<?php echo esc_attr( $options['title_color'] ); ?>" class="Lukic-color-picker">
								</div>
								
								<div class="Lukic-field-row">
									<label for="Lukic_maintenance_subtitle_color">
										<?php esc_html_e( 'Subtitle Color', 'Lukic-code-snippets' ); ?>
									</label>
									<input type="text" id="Lukic_maintenance_subtitle_color" name="Lukic_maintenance_mode_options[subtitle_color]" value="<?php echo esc_attr( $options['subtitle_color'] ); ?>" class="Lukic-color-picker">
								</div>
								
								<div class="Lukic-field-row">
									<label for="Lukic_maintenance_message_color">
										<?php esc_html_e( 'Message Color', 'Lukic-code-snippets' ); ?>
									</label>
									<input type="text" id="Lukic_maintenance_message_color" name="Lukic_maintenance_mode_options[message_color]" value="<?php echo esc_attr( $options['message_color'] ); ?>" class="Lukic-color-picker">
								</div>
								
								<div class="Lukic-field-row">
									<label for="Lukic_maintenance_overlay_color">
										<?php esc_html_e( 'Overlay Color', 'Lukic-code-snippets' ); ?>
									</label>
									<input type="text" id="Lukic_maintenance_overlay_color" name="Lukic_maintenance_mode_options[overlay_color]" value="<?php echo esc_attr( $options['overlay_color'] ); ?>" class="Lukic-color-picker">
									<p class="description"><?php esc_html_e( 'Background overlay color and opacity', 'Lukic-code-snippets' ); ?></p>
								</div>
							</div>
						</div>
						
						<div class="Lukic-settings-section">
							<h2><?php esc_html_e( 'Advanced Settings', 'Lukic-code-snippets' ); ?></h2>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_exclude_ips">
									<?php esc_html_e( 'Excluded IP Addresses', 'Lukic-code-snippets' ); ?>
								</label>
								<textarea id="Lukic_maintenance_exclude_ips" name="Lukic_maintenance_mode_options[exclude_ips]" rows="3" class="large-text"><?php echo esc_textarea( $options['exclude_ips'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Enter IP addresses to exclude from maintenance mode, one per line. Your current IP is: ', 'Lukic-code-snippets' ); ?><code><?php echo esc_html( $_SERVER['REMOTE_ADDR'] ); ?></code></p>
							</div>
							
							<div class="Lukic-field-row">
								<label for="Lukic_maintenance_exclude_paths">
									<?php esc_html_e( 'Excluded Paths', 'Lukic-code-snippets' ); ?>
								</label>
								<textarea id="Lukic_maintenance_exclude_paths" name="Lukic_maintenance_mode_options[exclude_paths]" rows="3" class="large-text"><?php echo esc_textarea( $options['exclude_paths'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Enter URL paths to exclude from maintenance mode, one per line or comma-separated (e.g., /wp-admin/, /wp-login.php)', 'Lukic-code-snippets' ); ?></p>
							</div>
						</div>
						
						<div class="Lukic-submit-container">
							<?php submit_button( __( 'Save Changes', 'Lukic-code-snippets' ), 'primary Lukic-btn Lukic-btn--primary', 'submit', false ); ?>
						</div>
					</form>
				</div>
				
				<div class="Lukic-settings-preview">
					<div class="Lukic-settings-section">
						<h2><?php esc_html_e( 'Live Preview', 'Lukic-code-snippets' ); ?></h2>
						<div class="Lukic-maintenance-preview" id="Lukic-maintenance-preview">
							<div class="Lukic-preview-background" style="background-image: url('<?php echo esc_url( $options['background_image'] ); ?>');">
								<div class="Lukic-preview-overlay"></div>
								<div class="Lukic-preview-content">
									<h1 class="Lukic-preview-title"><?php echo esc_html( $options['title'] ); ?></h1>
									<h2 class="Lukic-preview-subtitle"><?php echo esc_html( $options['subtitle'] ); ?></h2>
									<div class="Lukic-preview-message"><?php echo wp_kses_post( $options['message'] ); ?></div>
								</div>
							</div>
						</div>
						<p class="description"><?php esc_html_e( 'Live preview of your maintenance mode page. The preview updates as you change settings.', 'Lukic-code-snippets' ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display maintenance mode
	 */
	public function display_maintenance_mode() {
		// Check if user is logged in and can manage options
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! $this->is_toggle_enabled() ) {
			return;
		}

		// Get options
		$options = wp_parse_args(
			get_option( 'Lukic_maintenance_mode_options', array() ),
			$this->defaults
		);

		// Check for excluded IPs
		$excluded_ips = array_filter( array_map( 'trim', explode( "\n", $options['exclude_ips'] ) ) );
		if ( ! empty( $excluded_ips ) && in_array( $_SERVER['REMOTE_ADDR'], $excluded_ips ) ) {
			return;
		}

		// Check for excluded paths
		$current_path   = $_SERVER['REQUEST_URI'];
		$excluded_paths = array_filter( array_map( 'trim', preg_split( '/[\n,]+/', $options['exclude_paths'] ) ) );

		foreach ( $excluded_paths as $path ) {
			if ( ! empty( $path ) && strpos( $current_path, $path ) === 0 ) {
				return;
			}
		}

		// Default background image
		$default_bg = plugin_dir_url( __DIR__ ) . 'assets/images/maintenance-bg.svg';

		// If no background image is set, use the default
		if ( empty( $options['background_image'] ) ) {
			$options['background_image'] = $default_bg;
		}

		// Send 503 status code
		status_header( 503 );
		header( 'Content-Type: text/html; charset=utf-8' );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $options['title'] ); ?> - <?php bloginfo( 'name' ); ?></title>
			<style>
				html, body {
					margin: 0;
					padding: 0;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					line-height: 1.5;
					height: 100%;
					width: 100%;
				}
				
				.Lukic-maintenance-mode {
					display: flex;
					flex-direction: column;
					align-items: center;
					justify-content: center;
					height: 100%;
					width: 100%;
					position: relative;
					overflow: hidden;
					text-align: center;
					background-image: url('<?php echo esc_url( $options['background_image'] ); ?>');
					background-size: cover;
					background-position: center;
					background-repeat: no-repeat;
				}
				
				.Lukic-overlay {
					position: absolute;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					background-color: <?php echo esc_attr( $options['overlay_color'] ); ?>;
					z-index: 1;
				}
				
				.Lukic-content {
					position: relative;
					z-index: 2;
					max-width: 800px;
					padding: 30px;
				}
				
				.Lukic-title {
					font-size: <?php echo esc_attr( $options['title_font_size'] ); ?>;
					color: <?php echo esc_attr( $options['title_color'] ); ?>;
					margin: 0 0 20px;
				}
				
				.Lukic-subtitle {
					font-size: <?php echo esc_attr( $options['subtitle_font_size'] ); ?>;
					color: <?php echo esc_attr( $options['subtitle_color'] ); ?>;
					margin: 0 0 30px;
					font-weight: normal;
				}
				
				.Lukic-message {
					font-size: <?php echo esc_attr( $options['message_font_size'] ); ?>;
					color: <?php echo esc_attr( $options['message_color'] ); ?>;
					margin: 0;
				}
				
				/* Responsive adjustments */
				@media (max-width: 768px) {
					.Lukic-title {
						font-size: calc(<?php echo esc_attr( str_replace( 'px', '', $options['title_font_size'] ) ); ?>px * 0.8);
					}
					
					.Lukic-subtitle {
						font-size: calc(<?php echo esc_attr( str_replace( 'px', '', $options['subtitle_font_size'] ) ); ?>px * 0.8);
					}
					
					.Lukic-message {
						font-size: calc(<?php echo esc_attr( str_replace( 'px', '', $options['message_font_size'] ) ); ?>px * 0.8);
					}
				}
			</style>
		</head>
		<body>
			<div class="Lukic-maintenance-mode">
				<div class="Lukic-overlay"></div>
				<div class="Lukic-content">
					<h1 class="Lukic-title"><?php echo esc_html( $options['title'] ); ?></h1>
					<h2 class="Lukic-subtitle"><?php echo esc_html( $options['subtitle'] ); ?></h2>
					<div class="Lukic-message"><?php echo wp_kses_post( $options['message'] ); ?></div>
				</div>
			</div>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Lifecycle hook: called when snippet is activated.
	 */
	public static function activate_snippet() {

		$options = get_option( 'Lukic_maintenance_mode_options', null );
		if ( ! is_array( $options ) ) {
			update_option( 'Lukic_maintenance_mode_options', self::get_default_settings() );
		}
	}

	/**
	 * Lifecycle hook: called when snippet is deactivated.
	 */
	public static function deactivate_snippet() {

		$options = get_option( 'Lukic_maintenance_mode_options', array() );
		if ( isset( $options['enabled'] ) && (int) $options['enabled'] === 1 ) {
			$options['enabled'] = 0;
			update_option( 'Lukic_maintenance_mode_options', $options );
		}
	}
}

// Initialize the maintenance mode
new Lukic_Maintenance_Mode();
