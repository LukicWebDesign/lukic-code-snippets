# Lukic Code Snippets

**Contributors:** Lukic Milos  
**Tags:** snippets, code, optimization, admin, seo  
**Requires at least:** 5.8  
**Tested up to:** 6.6  
**Stable tag:** 2.7.3  
**Requires PHP:** 7.4  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

A collection of useful code snippets for WordPress to enhance functionality, security, and performance.

## Description

Lukic Code Snippets provides a centralized way to manage and activate useful code snippets for your WordPress site. Instead of adding code to your theme's functions.php file, you can simply toggle snippets on and off from the plugin settings.

**Features include:**
*   **Admin Interface**: Customizations for the WordPress dashboard.
*   **Content Management**: Tools to manage posts, pages, and media.
*   **SEO & Performance**: Snippets to improve site speed and search engine visibility.
*   **Security**: Hardening measures like disabling XML-RPC and hiding WP version.
*   **Utility**: Helper tools for maintenance mode, fluid typography, and more.

## Installation

1.  Upload the plugin files to the `/wp-content/plugins/lukic-code-snippets` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Navigate to 'Code Snippets' in the admin menu to configure and activate snippets.

## Frequently Asked Questions

### Will my snippets be lost if I uninstall the plugin?

By default, the plugin preserves your settings and data. You can choose to delete all data upon uninstallation in the "Plugin Settings" tab.

## Changelog

### 2.7.3
*   Fix: Resolved missing success notification when adding redirects.
*   Fix: Fixed JavaScript error related to tab initialization in Redirect Manager.
*   Fix: Fixed double confirmation dialog when deleting redirects.
*   Improvement: Updated styling for Edit and Delete buttons in Redirect Manager.

### 2.7.2
*   Improvement: Enhanced Maintenance Mode settings UI with 2-column layout for color pickers.
*   Improvement: Added width customization for color picker inputs in Maintenance Mode.
*   Fix: Resolved issue with Maintenance Mode preview background image not loading on initial page load.
*   Fix: Fixed color picker script loading issue on Maintenance Mode settings page.

### 2.7.1
*   Fix: Fixed "Save Settings" button styling on Content Order Settings page.

### 2.7.0
*   New: Added "Disable File Editing" snippet.
*   New: Added "Plugin Reordering" snippet.
*   Improvement: Refined Media Replacement UI.

### 2.4.0
*   New: Added "Admin Menu Organizer" snippet to reorder, rename, and hide admin menu items.
*   New: Added "Hide Author Slugs" snippet to improve security by masking author URLs.
*   Improvement: Enhanced UI for Admin Menu Organizer with drag-and-drop and auto-save.

### 2.3.1
*   Fix: Resolved issue where ACF columns were displayed on incorrect post types. Now respects ACF field group location rules.

### 2.3.0
*   New: Centralized snippet registry supplies metadata (names, tags, files) to every UI.
*   New: Added Maintenance Mode switch in the settings page.
*   Improvement: Maintenance Mode now respects the new switch on save.
*   Improvement: Switched all admin pages to a single text-domain loader.
*   Improvement: Refined the Asset Manager to register/cache-bust every CSS/JS asset.
*   Improvement: Security Headers settings UI now uses the shared design system.
*   Improvement: Unified DataTables loading for Image Attributes Editor, Meta Tags Editor and DB Tables Manager.
*   Fix: Maintenance Mode toggle state now persists correctly.
*   Fix: Image Attributes Editor and Meta Tags Editor no longer throw warnings.

### 2.2.1
*   New: Added Settings page for Content Order snippet.
*   New: Frontend ordering can now be enabled/disabled per post type.
*   Improvement: Content Order snippet now respects custom ordering on frontend.
*   Improvement: Localized all external CDN dependencies.
*   Security: Enhanced privacy and GDPR compliance by eliminating all external CDN requests.

### 2.2.0
*   New: Added comprehensive Database Tables Manager.
*   New: Table Row Editing System.
*   New: Advanced Search & Filtering.
*   Improvement: Enhanced UI/UX for Database Tables Manager.
*   Fix: Resolved intermittent modal loading issues.

### 2.1.0
*   New: Added Auto-Save functionality for snippet toggles.
*   New: Implemented an intelligent page-refresh system.
*   New: Added visual feedback for auto-saves.

### 2.0.0
*   Major Architectural Overhaul.
*   New: Implemented a centralized CSS Framework.
*   New: Created a reusable component-based CSS system.
*   New: Developed a new `Lukic_Asset_Manager` class.
*   New: Introduced a reusable `Lukic_display_header()` component.

### 1.6.0
*   Added Security Headers Manager snippet.
*   Added Limit Revisions snippet.
*   Added Image Sizes Panel snippet.
*   Added Redirect Manager snippet.
*   Added Custom Database Tables Manager snippet.

### 1.5.0
*   Added Bulk Edit functionality to Image Attributes Editor.
*   Added Edit column with blue buttons.

### 1.4.0
*   Added "Disable All Updates" snippet.

### 1.3.0
*   Added tag filter buttons.
*   Improved admin interface styling.

### 1.2.0
*   Added Word Counter feature.

### 1.1.0
*   Added Custom Login URL feature.

### 1.0.0
*   Initial release.
