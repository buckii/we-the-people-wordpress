<?php
/**
 * Plugin Name: We The People
 * Plugin URI: https://petitions.whitehouse.gov/
 * Description: Display White House petitions on your WordPress site
 * Version: 1.0
 * Author: Buckeye Interactive
 * Author URI: http://www.buckeyeinteractive.com
 * License: GPLv2 or later
 *
 * @package We The People
 * @author Buckeye Interactive
 */

class WeThePeople_Plugin {

  /**
   * @var str $api_endpoint The API endpoint (with trailing slash) for all API requests
   */
  protected $api_endpoint;

  /**
   * Class constructor
   * @uses add_shortcode()
   * @since 1.0
   */
  public function __construct() {
    // Set class properties
    $this->api_endpoint = 'https://api.whitehouse.gov/v1/petitions/';

    // Register our shortcode
    add_shortcode( 'petition', array( &$this, 'petition_shortcode' ) );
  }

  /**
   * Make an API call
   * This method acts as a controller, farming out the work to more specialized API methods
   * @param str $action The API method (retrieve|index) to call
   * @param array $args Arguments to pass to the API call
   * @return object
   * @since 1.0
   */
  public function api( $action, $args=array() ) {
    $method = sprintf( 'api_%s_action', strtolower( trim( $action ) ) );
    if ( ! method_exists( $this, $method ) ) {
      $this->error( sprintf( 'Class method %s does not exist', $method ) );
    }
    return call_user_func( array( $this, $method ), $args );
  }

  /**
   * Log an error
   * @param str $message The error message to log
   * @return void
   * @since 1.0
   */
  public function error( $message ) {
    trigger_error( sprintf( 'WeThePeople: %s', $message ), E_USER_NOTICE );
  }

  /**
   * Handler for the [petition] shortcode
   * @param array $atts Attributes passed in the shortcode call
   * @param str $content Content to be added above the petition information
   * @return str
   * @uses shortcode_atts()
   * @since 1.0
   */
  public function petition_shortcode( $atts, $content='' ) {
    $defaults = array(
      'id' => false
    );
    $atts = shortcode_atts( $defaults, $atts );

    if ( $atts['id'] ) {
      $response = $this->api( 'retrieve', $atts );
    } else {
      $this->error( __( 'Invalid petition ID', 'we-the-people' ) );
    }
    return print_r( $response, true );
  }

  /**
   * Handle requests for the API retrieve action
   * @param array $args Arguments to pass to the API call
   * @param array $args Arguments to pass to the API call
   * @return object
   * @since 1.0
   */
  protected function api_retrieve_action( $args=array() ) {
    $id = ( isset( $args['id'] ) ? $args['id'] : null );
    return $this->make_api_call( $id );
  }

  /**
   * Make the actual call to the API endpoint and return the results as a PHP object
   * @param str $call The assembled API call
   * @return object
   * @uses is_wp_error()
   * @uses wp_remote_get()
   * @since 1.0
   */
  protected function make_api_call( $call ) {
    $response = wp_remote_get( sprintf( '%s%s.json', $this->api_endpoint, $call ) );

    if ( is_wp_error( $response ) ) {
        $this->error( $response->get_error_message() );
    } elseif ( isset( $response['response']['code'] ) && $response['response']['code'] != 200 ) {
      $this->error( sprintf( __( 'API endpoint returned an unexpected status code of "%s %s"', 'we-the-people' ),
        $response['response']['code'], $response['response']['message']
      ) );
    }
    return json_decode( $response['body'], false );
  }

}

/**
 * Create an instance of WeThePeople_Plugin and store it in the global $we_the_people
 * @global $we_the_people
 * @return bool
 */
function wethepeople_init() {
  global $we_the_people;
  $we_the_people = new WeThePeople_Plugin;
  return true;
}
add_action( 'init', 'wethepeople_init' );