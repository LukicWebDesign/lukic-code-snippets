<?php
/**
 * Template for New Snippets - Lukic Code Snippets
 * 
 * Copy this template when creating new snippet files.
 * It follows the new framework patterns and best practices.
 * 
 * @package Lukic_Snippet_Codes
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for this snippet
define('Lukic_SNIPPET_NAME_OPTION', 'Lukic_snippet_name_settings');

/**
 * Add submenu page for this snippet
 */
function Lukic_snippet_name_menu() {
    add_submenu_page(
        'Lukic-code-snippets',
        __('Snippet Name Settings', 'Lukic-code-snippets'),
        __('Snippet Name', 'Lukic-code-snippets'),
        'manage_options',
        'Lukic-snippet-name',
        'Lukic_snippet_name_page'
    );
}
add_action('admin_menu', 'Lukic_snippet_name_menu');

/**
 * Register settings for this snippet
 */
function Lukic_snippet_name_register_settings() {
    register_setting('Lukic_snippet_name_group', Lukic_SNIPPET_NAME_OPTION, array(
        'sanitize_callback' => 'Lukic_snippet_name_sanitize_options'
    ));
}
add_action('admin_init', 'Lukic_snippet_name_register_settings');

/**
 * Sanitize options
 */
function Lukic_snippet_name_sanitize_options($options) {
    return Lukic_Helpers::sanitize_options($options);
}

/**
 * Display the settings page
 */
function Lukic_snippet_name_page() {
    // Check user capabilities
    if (!Lukic_Helpers::user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Handle form submission
    if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'Lukic_snippet_name_nonce')) {
        $options = array(
            'enable_feature' => isset($_POST['enable_feature']) ? 1 : 0,
            'custom_setting' => sanitize_text_field($_POST['custom_setting'] ?? '')
        );
        
        update_option(Lukic_SNIPPET_NAME_OPTION, $options);
        
        // Display success notice
        echo wp_kses_post(Lukic_Helpers::generate_admin_notice(
            __('Settings saved successfully!', 'Lukic-code-snippets'),
            'success'
        ));
    }
    
    // Get current options
    $options = get_option(Lukic_SNIPPET_NAME_OPTION, array());
    $enable_feature = isset($options['enable_feature']) ? $options['enable_feature'] : 0;
    $custom_setting = isset($options['custom_setting']) ? $options['custom_setting'] : '';
    
    // Prepare header statistics
    $stats = array(
        array('count' => $enable_feature ? 'ON' : 'OFF', 'label' => 'Status'),
        array('count' => get_current_blog_id(), 'label' => 'Site ID')
    );
    ?>
    
    <div class="wrap Lukic-container">
        <?php Lukic_display_header(__('Snippet Name Settings', 'Lukic-code-snippets'), $stats); ?>
        
        <div class="Lukic-card">
            <div class="Lukic-card__header">
                <h3 class="Lukic-card__title"><?php esc_html_e('Configuration', 'Lukic-code-snippets'); ?></h3>
            </div>
            <div class="Lukic-card__body">
                <form method="post" action="">
                    <?php echo wp_kses(Lukic_Helpers::get_nonce_field('Lukic_snippet_name_nonce'), array('input' => array('type' => array(), 'name' => array(), 'value' => array()))); ?>
                    
                    <!-- Enable Feature Toggle -->
                    <div style="margin-bottom: var(--Lukic-space-6);">
                        <label style="display: flex; align-items: center; gap: var(--Lukic-space-2); cursor: pointer;">
                            <input type="checkbox" name="enable_feature" value="1" <?php checked($enable_feature, 1); ?>>
                            <span style="font-weight: var(--Lukic-font-medium);">
                                <?php esc_html_e('Enable This Feature', 'Lukic-code-snippets'); ?>
                            </span>
                            <?php echo wp_kses_post(Lukic_Helpers::get_status_badge($enable_feature)); ?>
                        </label>
                        <p style="margin-top: var(--Lukic-space-2); color: var(--Lukic-gray-600); font-size: var(--Lukic-text-sm);">
                            <?php esc_html_e('Check this to enable the snippet functionality.', 'Lukic-code-snippets'); ?>
                        </p>
                    </div>
                    
                    <!-- Custom Setting Input -->
                    <div style="margin-bottom: var(--Lukic-space-6);">
                        <label for="custom_setting" style="display: block; margin-bottom: var(--Lukic-space-2); font-weight: var(--Lukic-font-medium);">
                            <?php esc_html_e('Custom Setting', 'Lukic-code-snippets'); ?>
                        </label>
                        <input type="text" 
                               id="custom_setting" 
                               name="custom_setting" 
                               class="Lukic-input"
                               value="<?php echo esc_attr($custom_setting); ?>"
                               placeholder="<?php esc_attr_e('Enter custom value...', 'Lukic-code-snippets'); ?>">
                        <p style="margin-top: var(--Lukic-space-2); color: var(--Lukic-gray-600); font-size: var(--Lukic-text-sm);">
                            <?php esc_html_e('Configure your custom setting here.', 'Lukic-code-snippets'); ?>
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="Lukic-flex Lukic-gap-4">
                        <button type="submit" name="submit" class="Lukic-btn Lukic-btn--primary">
                            <?php esc_html_e('Save Settings', 'Lukic-code-snippets'); ?>
                        </button>
                        <button type="button" class="Lukic-btn Lukic-btn--secondary" onclick="location.reload();">
                            <?php esc_html_e('Reset Form', 'Lukic-code-snippets'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Information Card -->
        <div class="Lukic-card" style="margin-top: var(--Lukic-space-6);">
            <div class="Lukic-card__header">
                <h3 class="Lukic-card__title"><?php esc_html_e('About This Feature', 'Lukic-code-snippets'); ?></h3>
            </div>
            <div class="Lukic-card__body">
                <p><?php esc_html_e('This snippet provides [describe functionality here]. When enabled, it will [explain what happens].', 'Lukic-code-snippets'); ?></p>
                
                <div class="Lukic-notification Lukic-notification--info" style="margin-top: var(--Lukic-space-4);">
                    <strong><?php esc_html_e('Note:', 'Lukic-code-snippets'); ?></strong>
                    <?php esc_html_e('Add any important notes or warnings about this feature here.', 'Lukic-code-snippets'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
}

/**
 * Main functionality - This is where you implement the actual feature
 */
function Lukic_snippet_name_functionality() {
    $options = get_option(Lukic_SNIPPET_NAME_OPTION, array());
    
    // Only proceed if feature is enabled
    if (!isset($options['enable_feature']) || !$options['enable_feature']) {
        return;
    }
    
    // Implement your functionality here
    // Examples:
    
    // Add action hooks
    // add_action('wp_head', 'Lukic_snippet_name_wp_head');
    
    // Add filter hooks  
    // add_filter('the_content', 'Lukic_snippet_name_filter_content');
    
    // Enqueue scripts/styles (handled by Asset Manager)
    // The Asset Manager will automatically handle this based on page detection
    
    Lukic_Helpers::debug_log('Snippet Name functionality initialized', 'SnippetName');
}

/**
 * Example WordPress head action
 */
function Lukic_snippet_name_wp_head() {
    $options = get_option(Lukic_SNIPPET_NAME_OPTION, array());
    $custom_setting = isset($options['custom_setting']) ? $options['custom_setting'] : '';
    
    if (!empty($custom_setting)) {
        echo "<!-- Snippet Name: " . esc_html($custom_setting) . " -->\n";
    }
}

/**
 * Example content filter
 */
function Lukic_snippet_name_filter_content($content) {
    $options = get_option(Lukic_SNIPPET_NAME_OPTION, array());
    
    // Your content modification logic here
    
    return $content;
}

/**
 * Initialize the snippet functionality
 */
add_action('init', 'Lukic_snippet_name_functionality');

/**
 * Cleanup on plugin deactivation (optional)
 */
function Lukic_snippet_name_cleanup() {
    // Clean up any data, transients, etc. when plugin is deactivated
    delete_option(Lukic_SNIPPET_NAME_OPTION);
    Lukic_Helpers::debug_log('Snippet Name cleanup completed', 'SnippetName');
}

// Uncomment if you need cleanup on deactivation
// register_deactivation_hook(Lukic_SNIPPET_CODES_PLUGIN_DIR . 'Lukic-code-snippets.php', 'Lukic_snippet_name_cleanup');
