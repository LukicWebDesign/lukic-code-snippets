# Lukic Code Snippets - Final Compliance Testing Plan

This document provides a step-by-step checklist to ensure that all refactoring efforts (nonce sanitization, JSON parsing, and inline scripts/styles conversion) have NOT broken the functionality of the plugin.

## Phase 1: General Plugin Health & Settings Interface
- [ ] **Activation check:** Go to the Plugins page, Deactivate "Lukic Code Snippets", and Reactivate it. Ensure no FATAL errors occur.
- [ ] **Main Menu Load:** Click on the "Lukic Code Snippets" menu link in the sidebar. Ensure the page styling loads cleanly with no broken CSS.
- [ ] **Search Functionality:** Use the search bar on the settings page to find a snippet (e.g., "Login"). The list should filter immediately as you type.
- [ ] **Tag Filtering:** Click on a tag button (e.g., "Active" or "Security"). Only the relevant snippets should be visible.
- [ ] **Auto-Save Toggle:** Toggle any snippet ON. Wait 1-2 seconds, then refresh the page. Ensure the snippet remains toggled ON (this confirms the JSON POST sanitization is working).

## Phase 2: Documentation Page
- [ ] **Layout Check:** Go to the "Documentation" submenu. Verify the snippets are displayed nicely in a two-column grid.
- [ ] **Documentation Search:** Use the search box on the Documentation page and verify that it correctly filters the grid items.

## Phase 3: High-Impact Snippet Interfaces
*Since we moved a lot of inline CSS/JS from these specific files to enqueue hooks, testing their interfaces is crucial.*
- [ ] **Admin Menu Organizer:** Enable the snippet, go to its settings page, test dragging and dropping a few menu items, and save the order. Ensure the WP Admin sidebar updates to reflect your custom order.
- [ ] **Database Tables Manager:** Enable the snippet, go to its interface, and verify the tables are listed with styling. Try clicking "Optimize" on a small table to ensure the AJAX call triggers successfully.
- [ ] **Redirect Manager:** Enable the snippet. Add a fake redirect, visually check that the list updates and looks styled correctly. Ensure the redirect actually functions on the front end.
- [ ] **Upload Limits:** Enable the snippet, set a limit (e.g., 2MB). Go to **Media > Add New** and confirm the "Maximum upload file size" text reflects your custom limit.

## Phase 4: Nonce & Action Security Checks
*We updated 17 instances of nonce verification to use strict sanitization. We must trigger these actions to ensure valid requests aren't being blocked.*
- [ ] **Post Duplicator:** Go to the Posts or Pages list. Hover over an item and click "Duplicate". Ensure the draft copy is created successfully.
- [ ] **Image Attributes Editor:** Go to the Media Library (List View mode). Try performing a bulk update or quick edit on an image's Alt Text or Title.
- [ ] **Content Order:** If you have this feature enabled, go to the Posts list and drag-and-drop a row to change its order. Refresh the page to ensure the new order was saved.
- [ ] **Security Headers:** Go to this snippet's interface and click the "Test Headers" button. Ensure an AJAX success or failure message appears correctly.

## Phase 5: Error Logging (Final Step)
- [ ] Enable `WP_DEBUG` and `WP_DEBUG_LOG` in your `wp-config.php` temporarily.
- [ ] Click randomly across all the plugin's settings pages and custom interfaces for a couple of minutes.
- [ ] Check `wp-content/debug.log` to ensure the plugin has not generated any new PHP Warnings, Notices, or Errors.
