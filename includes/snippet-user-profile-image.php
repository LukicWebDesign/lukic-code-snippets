<?php
/**
 * User Profile Image
 *
 * Allow users to upload custom avatars from the media library instead of using Gravatar.
 *
 * @package Lukic_Code_Snippets
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue media scripts for profile page
 *
 * @param string $hook Current admin page hook.
 */
function lukic_user_profile_image_enqueue( $hook ) {
	if ( 'profile.php' !== $hook && 'user-edit.php' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	
	wp_enqueue_script( 
		'lukic-user-profile-image', 
		Lukic_SNIPPET_CODES_PLUGIN_URL . 'assets/js/user-profile-image.js', 
		array( 'jquery' ), 
		Lukic_SNIPPET_CODES_VERSION, 
		true 
	);
	
	// Localize script for translations if needed, but for now we hardcoded strings in JS or can use wp_localize_script
	wp_localize_script( 'lukic-user-profile-image', 'lukic_profile_image', array(
		'title' => __( 'Select Profile Picture', 'lukic-code-snippets' ),
		'button' => __( 'Use this image', 'lukic-code-snippets' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'lukic_user_profile_image_enqueue' );

/**
 * Resolve a user ID from the values accepted by get_avatar().
 *
 * @param mixed $id_or_email User object, ID, email address, or comment object.
 * @return int User ID, or 0 when unavailable.
 */
function lukic_get_avatar_user_id( $id_or_email ) {
	if ( is_numeric( $id_or_email ) ) {
		return (int) $id_or_email;
	}

	if ( $id_or_email instanceof WP_User ) {
		return (int) $id_or_email->ID;
	}

	if ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) {
		return (int) $user->ID;
	}

	if ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
		return (int) $id_or_email->user_id;
	}

	if ( is_object( $id_or_email ) && ! empty( $id_or_email->comment_author_email ) && ( $user = get_user_by( 'email', $id_or_email->comment_author_email ) ) ) {
		return (int) $user->ID;
	}

	return 0;
}

/**
 * Get a usable avatar attachment URL, including SVG attachments.
 *
 * @param int          $attachment_id Avatar attachment ID.
 * @param string|array $size          Requested image size.
 * @return string Avatar URL, or empty string.
 */
function lukic_get_profile_avatar_url( $attachment_id, $size = 'thumbnail' ) {
	$image = wp_get_attachment_image_src( $attachment_id, $size );

	if ( $image && ! empty( $image[0] ) ) {
		return $image[0];
	}

	if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
		$url = wp_get_attachment_url( $attachment_id );
		return $url ? $url : '';
	}

	return '';
}

/**
 * Build the avatar class string while preserving classes passed to get_avatar().
 *
 * @param int   $size Avatar size.
 * @param array $args get_avatar() arguments.
 * @return string Avatar classes.
 */
function lukic_get_profile_avatar_classes( $size, $args = array() ) {
	$classes = array( 'avatar', 'avatar-' . absint( $size ), 'photo' );

	if ( ! empty( $args['class'] ) ) {
		$extra_classes = is_array( $args['class'] ) ? $args['class'] : preg_split( '/\s+/', (string) $args['class'] );
		$classes       = array_merge( $classes, $extra_classes );
	}

	$classes = array_filter( array_map( 'sanitize_html_class', $classes ) );

	return implode( ' ', array_unique( $classes ) );
}

/**
 * Add avatar field to user profile
 *
 * @param WP_User $user User object.
 */
function lukic_user_profile_image_field( $user ) {
	$avatar_id = get_user_meta( $user->ID, 'lukic_user_avatar', true );
	$image_url = '';
	if ( $avatar_id ) {
		$image_url = lukic_get_profile_avatar_url( (int) $avatar_id, 'thumbnail' );
	}
	?>
	<h3><?php esc_html_e( 'User Profile Image', 'lukic-code-snippets' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="lukic-user-avatar"><?php esc_html_e( 'Profile Picture', 'lukic-code-snippets' ); ?></label></th>
			<td>
				<div class="lukic-avatar-wrapper">
					<img id="lukic-user-avatar-preview" src="<?php echo esc_url( $image_url ); ?>" style="max-width: 96px; height: auto; display: <?php echo $image_url ? 'block' : 'none'; ?>; margin-bottom: 10px; border-radius: 50%;">
					<input type="hidden" name="lukic_user_avatar" id="lukic-user-avatar-id" value="<?php echo esc_attr( $avatar_id ); ?>">
					<button type="button" class="button" id="lukic-upload-avatar-button"><?php esc_html_e( 'Upload Image', 'lukic-code-snippets' ); ?></button>
					<button type="button" class="button" id="lukic-remove-avatar-button" style="display: <?php echo $image_url ? 'inline-block' : 'none'; ?>;"><?php esc_html_e( 'Remove', 'lukic-code-snippets' ); ?></button>
					<p class="description"><?php esc_html_e( 'Upload a custom profile picture to replace Gravatar.', 'lukic-code-snippets' ); ?></p>
				</div>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'lukic_user_profile_image_field' );
add_action( 'edit_user_profile', 'lukic_user_profile_image_field' );

/**
 * Save avatar field
 *
 * @param int $user_id User ID.
 */
function lukic_save_user_profile_image( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	
	if ( isset( $_POST['lukic_user_avatar'] ) ) {
		update_user_meta( $user_id, 'lukic_user_avatar', sanitize_text_field( $_POST['lukic_user_avatar'] ) );
	}
}
add_action( 'personal_options_update', 'lukic_save_user_profile_image' );
add_action( 'edit_user_profile_update', 'lukic_save_user_profile_image' );

/**
 * Filter get_avatar to display custom image
 *
 * @param string $avatar      Image tag for the user's avatar.
 * @param mixed  $id_or_email The user object, ID, or email address.
 * @param int    $size        Square avatar width and height in pixels.
 * @param string $default     URL for the default image or a default type.
 * @param string $alt         Alternative text to use in the avatar image tag.
 * @param array  $args        Arguments passed to get_avatar().
 * @return string Filtered avatar.
 */
function lukic_filter_get_avatar( $avatar, $id_or_email, $size, $default, $alt, $args = array() ) {
	$user_id = lukic_get_avatar_user_id( $id_or_email );

	if ( $user_id ) {
		$custom_avatar_id = get_user_meta( $user_id, 'lukic_user_avatar', true );
		if ( $custom_avatar_id ) {
			$avatar_classes = lukic_get_profile_avatar_classes( $size, $args );

			if ( 'image/svg+xml' === get_post_mime_type( $custom_avatar_id ) ) {
				$custom_avatar_url = lukic_get_profile_avatar_url( (int) $custom_avatar_id, array( $size, $size ) );

				if ( $custom_avatar_url ) {
					return sprintf(
						'<img alt="%1$s" src="%2$s" class="%3$s" height="%4$d" width="%4$d" loading="lazy" decoding="async" />',
						esc_attr( $alt ),
						esc_url( $custom_avatar_url ),
						esc_attr( $avatar_classes ),
						absint( $size )
					);
				}
			}

			$custom_avatar  = wp_get_attachment_image( $custom_avatar_id, array( $size, $size ), false, array( 'alt' => $alt, 'class' => $avatar_classes ) );

			if ( $custom_avatar ) {
				return $custom_avatar;
			}

			$custom_avatar_url = lukic_get_profile_avatar_url( (int) $custom_avatar_id, array( $size, $size ) );

			if ( $custom_avatar_url ) {
				return sprintf(
					'<img alt="%1$s" src="%2$s" class="%3$s" height="%4$d" width="%4$d" loading="lazy" decoding="async" />',
					esc_attr( $alt ),
					esc_url( $custom_avatar_url ),
					esc_attr( $avatar_classes ),
					absint( $size )
				);
			}
		}
	}

	return $avatar;
}
add_filter( 'get_avatar', 'lukic_filter_get_avatar', 99, 6 );

/**
 * Filter get_avatar_url to return custom image URL
 *
 * @param string $url         The URL of the avatar.
 * @param mixed  $id_or_email The user object, ID, or email address.
 * @param array  $args        Arguments for the avatar.
 * @return string Filtered avatar URL.
 */
function lukic_filter_get_avatar_url( $url, $id_or_email, $args ) {
	$user_id = lukic_get_avatar_user_id( $id_or_email );

	if ( $user_id ) {
		$custom_avatar_id = get_user_meta( $user_id, 'lukic_user_avatar', true );
		if ( $custom_avatar_id ) {
			$size             = isset( $args['size'] ) ? array( absint( $args['size'] ), absint( $args['size'] ) ) : 'thumbnail';
			$custom_avatar_url = lukic_get_profile_avatar_url( (int) $custom_avatar_id, $size );

			if ( $custom_avatar_url ) {
				return $custom_avatar_url;
			}
		}
	}

	return $url;
}
add_filter( 'get_avatar_url', 'lukic_filter_get_avatar_url', 99, 3 );
