<?php
/**
 * The petition signature template
 *
 * Required fields: first_name, last_name, email, postal_code, petition_id, api_key
 *
 * @package We The People
 * @author Buckeye Interactive
 */

$current_user = wp_get_current_user();

if ( wethepeople_signature_submitted( $petition_id ) ) : ?>

  <div class="wtp-signature-success">
    <p><?php _e( 'Your signature has been submitted! You should receive a confirmation email from We The People shortly!', 'we-the-people' ); ?></p>
  </div>

<?php else : ?>

  <?php if ( wethepeople_signature_error( $petition_id ) ) : ?>

    <div class="wtp-signature-error">
      <p><?php _e( 'There was a problem submitting your signature for this petition!', 'we-the-people' ); ?></p>
    </div><!-- .wtp-signature-error -->

  <?php endif; ?>

  <form method="post" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" id="wtp-petition-signature-<?php echo $petition_id; ?>" class="wtp-petitions-signature">
    <h3><?php _e( 'Sign this petition', 'we-the-people' ); ?></h3>

    <ul class="wtp-petition-signature-fields">
      <li>
        <label for="wtp-petition-<?php echo $petition_id; ?>-first_name">
          <?php _e( 'First name', 'we-the-people' ); ?>
          <span class="required">*</span>
        </label>
        <input name="first_name" id="wtp-petition-<?php echo $petition_id; ?>-first_name" type="text" value="<?php echo $current_user->user_firstname; ?>" required />
      </li>
      <li>
        <label for="wtp-petition-<?php echo $petition_id; ?>-last_name">
          <?php _e( 'Last name', 'we-the-people' ); ?>
          <span class="required">*</span>
        </label>
        <input name="last_name" id="wtp-petition-<?php echo $petition_id; ?>-last_name" type="text" value="<?php echo $current_user->user_lastname; ?>" required />
      </li>
      <li>
        <label for="wtp-petition-<?php echo $petition_id; ?>-email">
          <?php _e( 'Email address', 'we-the-people' ); ?>
          <span class="required">*</span>
        </label>
        <input name="email" id="wtp-petition-<?php echo $petition_id; ?>-email" type="email" value="<?php echo $current_user->user_email; ?>" required />
      </li>
      <li>
        <label for="wtp-petition-<?php echo $petition_id; ?>-zip">
          <?php _e( 'Postal code', 'we-the-people' ); ?>
          <span class="required">*</span>
        </label>
        <input name="zip" id="wtp-petition-<?php echo $petition_id; ?>-zip" type="text" value="" required />
      </li>
    </ul>

    <p class="form-submit">
      <input name="submit" type="submit" value="<?php _e( 'Sign petition', 'we-the-people' ); ?>" />
      <input name="petition_id" type="hidden" value="<?php echo $petition_id; ?>" />
      <input name="action" type="hidden" value="wtp_petition_signature" />
    </p>
  </form><!-- #wtp-petition-signature-<?php echo $petition_id; ?> -->

<?php endif; ?>