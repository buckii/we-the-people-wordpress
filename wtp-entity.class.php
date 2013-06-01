<?php
/**
 * We_The_People_Entity model
 * By returning petitions as instances of this model we're able to add useful template methods
 *
 * @package We The People
 * @author Buckeye Interactive
 */

class We_The_People_Entity {

  /**
   * @var str $css_classes CSS classes to apply to this entity
   */
  public $css_classes;

  /**
   * Instantiate the object and save the API response properties to properties of this class
   * @param object $api_response The API response to be cast as a We_The_People_Entity
   * @return void
   * @since 1.1
   */
  public function __construct( $api_response ) {

    // Assign the methods from the StdClass object to this object
    $props = get_object_vars( $api_response );
    foreach ( $props as $k=>$v ) {
      $this->$k = $v;
    }

    $this->css_classes = array();
  }

  /**
   * Catch empty properties
   * @return void
   * @since 1.1
   */
  public function __get( $prop ) {
    return;
  }

  /**
   * Shortcut for `echo $obj->get_classes` *including* the class attribute name
   * @return void
   * @since 1.1
   */
  public function petition_class( $additional='' ) {
    printf( 'class="%s"', $this->get_classes( $additional, false ) );
  }

  /**
   * Get a list of classnames for this entity
   * @param str $additional User-provided classnames to include
   * @param bool $array Should this be returned as an array or a string (true = array, false = string )
   * @return mixed (array|str) Dependent upon the $array argument
   * @uses sanitize_title_with_dashes()
   * @since 1.1
   */
  public function get_classes( $additional='', $array=true ) {

    // If we don't already have our system-defined CSS classes we'll need to generate them
    if ( ! $this->css_classes ) {
      $classes = array();

      // If we have an issues property we want to turn these into classes
      if ( isset( $this->issues ) && is_array( $this->issues ) ) {
        foreach ( $this->issues as $issue ) {
          $classes[] = sprintf( 'issue-%s', sanitize_title_with_dashes( $issue->name ) );
          $classes[] = sprintf( 'issue-%d', $issue->id );
        }
      }

      // Save these to our class property
      $this->css_classes = $classes;
    }

    // Merge the user-provided classes
    $class_names = array_merge( $this->css_classes, explode( ' ', $additional ) );
    $class_names = array_filter( array_map( 'trim', $class_names ) );

    // Create a string
    return implode( ' ', $class_names );
  }

}