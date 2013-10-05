<?php
/**
 * The standard template for displaying a We The People petition as a widget
 *
 * @package We The People
 * @author Buckeye Interactive
 */

// Apply standard WordPress filters that we want to use
add_filter( 'wethepeople_petition_body', 'wpautop' );
add_filter( 'wethepeople_petition_body', 'wptexturize' );

// Extract the widget arguments
extract( $widget_args );
?>

<div id="wtp-petition-<?php echo $petition->id; ?>" <?php $petition->petition_class( 'wtp-petition clearfix' ); ?>>
  <?php echo $before_title . $petition->title . $after_title; ?>
  <blockquote><?php echo apply_filters( 'wethepeople_petition_body', $petition->body ); ?></blockquote>

<?php if ( $petition->status == 'responded' ) : ?>

  <p class="responded">
    <a href="<?php echo $petition->response->url; ?>" title="<?php esc_attr( __( "Read the White House's response to this petition", 'we-the-people' ) ); ?>" rel="external">
      <?php _e( 'The White House has issued a response to this petition.', 'we-the-people' ); ?>
    </a>
  </p>

<?php else : ?>

  <p class="petition-status">
    <?php printf( __( '%d signatures still needed by %s', 'we-the-people' ), $petition->signaturesNeeded, date_i18n( get_option( 'date_format' ), $petition->deadline ) ); ?>
    <a href="<?php echo $petition->url; ?>" title="<?php echo esc_attr( __( 'Sign this petition on We The People', 'we-the-people' ) ); ?>" class="sign-btn" rel="external">
      <?php _e( 'Sign this petition', 'we-the-people' ); ?>
    </a>
  </p>

<?php endif; ?>
</div><!-- #wtp-petition-<?php echo $petition->id; ?> -->