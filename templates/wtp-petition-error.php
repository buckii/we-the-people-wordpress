<?php
/**
 * Error message to display when a petition is unavailable
 *
 * @package We The People
 * @author Buckeye Interactive
 */

// Extract the widget arguments
if ( $widget_args ) {
  extract( $widget_args );
}

$heading = __( 'Petition unavailable', 'we-the-people' );

?>

<div <?php $petition->petition_class( 'wtp-petition wtp-petition-error clearfix' ); ?>>
<?php if ( isset( $before_title ) ) : ?>
  <?php echo $before_title . $heading . $after_title; ?>
<?php else : ?>
  <h2 class="petition-title"><?php echo $heading; ?></h2>
<?php endif; ?>
  <p class="wtp-error"><?php _e( 'This petition is currently unavailable due to an error retrieving data from <a href="http://petitions.whitehouse.gov/" rel="external">We The People</a>.', 'we-the-people' ); ?></p>
</div><!-- .wtp-petition-error -->
