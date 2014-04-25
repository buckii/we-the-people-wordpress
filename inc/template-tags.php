<?php
/**
 * Template tags for We The People
 *
 * @package We The People
 * @author Buckeye Interactive
 */

/**
 * Load the We The People petition signature form
 *
 * @global $petition
 * @param str $petition_id The petition ID
 * @return str
 *
 * @since 2.0
 */
function wethepeople_get_signature_form( $petition_id = false ) {
  global $petition;

  // Don't load the form if an API key hasn't been provided
  if ( ! wethepeople_get_option( 'api_key' ) ) {
    if ( current_user_can( 'manage_options' ) ) {
      return sprintf( '<p class="wtp-error">%s</p>',
        sprintf(
          __( 'In order to sign petitions you must <a href="%s" rel="external">register for an API key</a> with We The People.', 'we-the-people' ),
          self::API_KEY_REGISTRATION_URL
        )
      );

    } else {
      return;
    }
    return ( current_user_can( 'manage_options' ) ? sprintf( '<p class="wtp-error">%s</p>', __( ) ) : '' );
  }

  // Default the $petition->id
  if ( ! $petition_id ) {
    $petition_id = $petition->id;
  }

  // Locate templates
  if ( ! $template_file = locate_template( array( 'wtp-signature-form.php' ), false, false ) ) {
    $template_file = $GLOBALS['we-the-people']->template_path . 'wtp-signature-form.php';
  }
  ob_start();
  include $template_file;
  $form = ob_get_contents();
  ob_end_clean();

  return $form;
}

/**
 * Shortcut for `echo wethpeople_get_signature_form( $petition_id )`
 */
function wethepeople_signature_form( $petition_id = false ) {
  echo wethepeople_get_signature_form( $petition_id );
}