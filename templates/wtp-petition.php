<?php
/**
 * The standard template for displaying a We The People petition
 *
 * @package We The People
 * @author Buckeye Interactive
 */

// Apply standard WordPress filters that we want to use
add_filter( 'wethepeople_petition_body', 'wpautop' );
add_filter( 'wethepeople_petition_body', 'wptexturize' );

// Uncomment the following line if you want to see the petition object during development
//printf( '<pre>%s</pre>', print_r( $petition, true ) );
?>

<div id="wtp-petition-<?php echo $petition->id; ?>" class="wtp-petition">
  <h2 class="petition-title"><?php echo $petition->title; ?></h2>
  <blockquote class="collapsed"><?php echo apply_filters( 'wethepeople_petition_body', $petition->body ); ?></blockquote>

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
