<?php
/**
 * "WTP Petition" widget
 *
 * @package We The People
 * @author Buckeye Interactive
 */

class WeThePeople_Plugin_Widget extends WP_Widget {

  /**
   * Widget constructor
   *
   * @see WP_Widget::__construct()
   *
   * @since 1.0
   */
  public function __construct() {
    parent::__construct( 'wtp', __( 'WTP Petition', 'we-the-people' ),
      array( 'description' => __( 'Embed a petition from We The People', 'we-the-people' ) ),
      array( 'width' => 350 )
    );
  }

  /**
   * Front-end display of widget.
   *
   * @param array $args Widget arguments.
   * @param array $instance Saved values from database.
   * @return void
   *
   * @see WP_Widget::widget()
   *
   * @since 1.0
   */
  public function widget( $args, $instance ) {
    if ( ! $GLOBALS['we-the-people'] instanceof WeThePeople_Plugin ) {
      $GLOBALS['we-the-people'] = new WeThePeople_Plugin;
    }

    if ( isset( $instance['petition_id'] ) && $instance['petition_id'] ) {
      if ( $petition = $GLOBALS['we-the-people']->api( 'retrieve', array( 'id' => $instance['petition_id'] ) ) ) {
        echo $args['before_widget'];
        $GLOBALS['we-the-people']->display_petition( $petition, array( 'echo' => true, 'widget' => true, 'widget_args' => $args ) );
        echo $args['after_widget'];
      }
    }
  }

  /**
   * Back-end widget form.
   *
   * @param array $instance Previously saved values from database.
   * @return void
   *
   * @see WP_Widget::form()
   *
   * @since 1.0
   */
  public function form( $instance ) {
    print '<p>';
    printf(
      '<label for="%s">%s</label>',
      $this->get_field_id( 'petition_id' ),
      __( 'Petition ID:', 'we-the-people' )
    );
    printf(
      '<input name="%s" id="%s" type="text" class="wtp-petition-id widefat" value="%s" />',
      $this->get_field_name( 'petition_id' ),
      $this->get_field_id( 'petition_id' ),
      ( isset( $instance['petition_id'] ) ? esc_attr( $instance['petition_id'] ) : '' )
    );
    print '</p>';

    // Search form
    printf(
      '<p><strong>%s</strong></p>',
      __( "Don't know your petition ID?", 'we-the-people' )
    );
    print '<p>';
    printf(
      '<label for="%s">%s</label>',
      $this->get_field_id( 'petition-search-term' ),
      __( 'Search petitions:', 'we-the-people' )
    );
    printf(
      '<input name="%s" id="%s" type="text" class="wtp-petition-search widefat" placeholder="%s" />',
      $this->get_field_name( 'petition-search-term' ),
      $this->get_field_id( 'petition-search-term' ),
      esc_attr( __( 'e.g. Guns, taxes, etc.', 'we-the-people' ) )
    );
    print '</p><p>';
    print '<input name="petition-search-only-active" id="petition-search-only-active" type="checkbox" />';
    printf(
      '<label for="petition-search-only-active" class="inline">%s</label>',
      __( 'Limit search results to active petitions?', 'we-the-people' )
    );
    print '</p>';
    print '<div class="wtp-search-results"></div>';
  }

  /**
   * Sanitize widget form values as they are saved.
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   * @return array Updated safe values to be saved.
   * @see WP_Widget::update()
   * @since 1.0
   */
  public function update( $new_instance, $old_instance ) {
    return array(
      'petition_id' => preg_replace( '/[^A-Z0-9]/i', '', $new_instance['petition_id'] )
    );
  }

}

/**
 * Register our widget on widgets_init
 * @uses register_widget
 * @since 1.0
 */
function wethepeople_register_widget() {
  register_widget( 'WeThePeople_Plugin_Widget' );
}
add_action( 'widgets_init', 'wethepeople_register_widget' );