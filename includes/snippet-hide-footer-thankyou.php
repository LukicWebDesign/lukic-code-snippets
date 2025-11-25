<?php
/**
 * Snippet: Hide Footer Thank You
 * Description: Removes the "Thank you for creating with WordPress" message from the admin footer
 */

if ( ! function_exists( 'Lukic_hide_footer_thankyou_init' ) ) {
	/**
	 * Initialize the hide footer thank you functionality
	 */
	function Lukic_hide_footer_thankyou_init() {
		// Add filter to modify admin footer text
		add_filter( 'admin_footer_text', 'Lukic_hide_footer_thankyou', 99 );
	}
	Lukic_hide_footer_thankyou_init();

	/**
	 * Remove the "Thank you for creating with WordPress" message
	 *
	 * @param string $text The current admin footer text
	 * @return string Empty string to remove the text
	 */
	function Lukic_hide_footer_thankyou( $text ) {
		// Return empty string to remove the footer text
		return '';
	}
}
