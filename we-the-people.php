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
   * The API endpoint (with trailing slash) for all API requests
   */
  const API_ENDPOINT = 'https://api.whitehouse.gov/v1/petitions/';

  /**
   * The amount of time (in seconds) transient data should live before it's purged
   */
  const TRANSIENT_EXPIRES = 60;

  /**
   * Class constructor
   * @uses add_shortcode()
   * @since 1.0
   */
  public function __construct() {
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
   * Handler for the [petition] shortcode
   * @param array $atts Attributes passed in the shortcode call
   * @param str $content Content to be added above the petition information
   * @return str
   * @uses shortcode_atts()
   * @since 1.0
   *
   * @todo Load a template rather than just spitting out a print_r()
   */
  public function petition_shortcode( $atts, $content='' ) {
    $defaults = array(
      'id' => false
    );
    $atts = shortcode_atts( $defaults, $atts );
    if ( ! $atts['id'] ) {
      $this->error( __( 'Invalid petition ID', 'we-the-people' ) );
      return;
    }

    $response = $this->api( 'retrieve', $atts );
    if ( empty( $response ) ) {
      $this->error( sprintf( __( 'API response for petition %s came back empty', 'we-the-people' ), $atts['id'] ) );
      return;
    }

    return print_r( current( $response ), true );
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
   * Log an error
   * @param str $message The error message to log
   * @return void
   * @since 1.0
   *
   * @todo Write some better error reporting
   */
  protected function error( $message ) {
    trigger_error( sprintf( 'WeThePeople: %s', $message ), E_USER_NOTICE );
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
    $request_uri = sprintf( '%s%s.json', self::API_ENDPOINT, $call );
    $hash = md5( $request_uri ); // Transient keys should be < 45 chars

    // If we have matching transient data return that instead
    if ( $data = get_transient( $hash ) ) {
      return $data;
    }

    // If we're still in at this point then we need to actually make an API call
    $response = wp_remote_get( $request_uri );

    if ( is_wp_error( $response ) ) {
        $this->error( $response->get_error_message() );
    } elseif ( isset( $response['response']['code'] ) && $response['response']['code'] != 200 ) {
      $this->error( sprintf( __( 'API endpoint returned an unexpected status code of "%s %s"', 'we-the-people' ),
        $response['response']['code'], $response['response']['message']
      ) );
    }

    // Save the response body as a transient
    $body = json_decode( $response['body'], false );
    set_transient( $hash, $body->results, self::TRANSIENT_EXPIRES );

    return $body->results;
  }

}

/**
 * Create an instance of WeThePeople_Plugin and store it in the global $we_the_people
 * @global $we_the_people
 * @return bool
 * @since 1.0
 */
function wethepeople_init() {
  global $we_the_people;
  $we_the_people = new WeThePeople_Plugin;
  return true;
}
add_action( 'init', 'wethepeople_init' );