<?php
/**
 * Plugin Name: Lukic Code Snippets
 * Plugin URI: #
 * Description: A collection of useful code snippets for WordPress
 * Version: 2.7.1
 * Author: Milos Lukic
 * Author URI: #
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: lukic-code-snippets
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'Lukic_SNIPPET_CODES_VERSION', '2.7.1' );
define( 'Lukic_SNIPPET_CODES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'Lukic_SNIPPET_CODES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/**
 * The core plugin class
 */
class Lukic_Snippet_Codes {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Load dependencies.
		$this->load_dependencies();

		// Initialize core components.
		$settings      = new Lukic_Snippet_Codes_Settings();
		$asset_manager = new Lukic_Asset_Manager();

		// Localization.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		// Load activated snippets.
		add_action( 'plugins_loaded', array( $this, 'load_snippets' ) );
	}

	/**
	 * Load the required dependencies
	 */
	private function load_dependencies() {
		// Core classes.
		require_once Lukic_SNIPPET_CODES_PLUGIN_DIR . 'includes/class-settings.php';
		require_once Lukic_SNIPPET_CODES_PLUGIN_DIR . 'includes/components/class-asset-manager.php';
		require_once Lukic_SNIPPET_CODES_PLUGIN_DIR . 'includes/snippets/class-snippet-registry.php';
		require_once Lukic_SNIPPET_CODES_PLUGIN_DIR . 'includes/snippets/class-snippet-lifecycle.php';

		// Utilities.
		require_once Lukic_SNIPPET_CODES_PLUGIN_DIR . 'includes/utilities/class-helpers.php';

		// Components.
		require_once Lukic_SNIPPET_CODES_PLUGIN_DIR . 'includes/components/header.php';
	}

	/**
	 * Load plugin text domain for translations
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'lukic-code-snippets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Get total number of available snippets
	 *
	 * @return int Total number of snippets
	 */
	public static function get_total_snippets_count() {
		return count( Lukic_Snippet_Registry::get_snippets() );
	}

	/**
	 * Get all available snippets
	 *
	 * @return array Array of available snippets
	 */
	public static function get_available_snippets() {
		$available = array();
		foreach ( Lukic_Snippet_Registry::get_snippets() as $snippet_id => $snippet ) {
			$available[ $snippet_id ] = array(
				'file' => $snippet['file'],
				'name' => $snippet['name'],
			);
		}

		return $available;
	}

	/**
	 * Load activated snippets
	 */
	public function load_snippets() {
		// Get plugin options.
		$options = get_option( 'Lukic_snippet_codes_options', array() );

		// Get available snippets.
		$snippets = self::get_available_snippets();

		// Loop through snippets and load activated ones.
		foreach ( $snippets as $snippet_id => $snippet_data ) {
			if ( isset( $options[ $snippet_id ] ) && 1 === (int) $options[ $snippet_id ] ) {
				$snippet_file = Lukic_SNIPPET_CODES_PLUGIN_DIR . 'includes/' . $snippet_data['file'];
				if ( file_exists( $snippet_file ) ) {
					require_once $snippet_file;
				}
			}
		}
	}
}

// Initialize the plugin.
new Lukic_Snippet_Codes();
