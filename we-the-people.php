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
   * The current plugin version
   */
  const PLUGIN_VERSION = '1.0';

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

    // Regsiter/enqueue scripts and styles
    $this->register_scripts();
    $this->register_styles();
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
      $this->error( sprintf( __( 'Class method %s does not exist', 'we-the-people' ), $method ) );
    }
    return call_user_func( array( $this, $method ), $args );
  }

  /**
   * Display a petition
   *
   * This plugin will detect the appropriate template file to load using the following priorities:
   * 1. wtp-petition-{id}.php (child theme)
   * 2. wtp-petition.php (child theme)
   * 3. wtp-petition-{id}.php (parent theme)
   * 4. wtp-petition.php (parent theme)
   * 5. templates/wtp-petition.php (plugin)
   *
   * @param object $petition An API response for a single petition
   * @param bool $echo Should the output be printed directly or returned as a string
   * @return mixed (str|void depending on $echo)
   * @uses locate_template()
   * @since 0.1
   */
  public function display_petition( $petition, $echo=false ) {
    if ( ! $petition || ! isset( $petition->id ) ) {
      $this->error( __( 'Invalid petition object', 'we-the-people' ) );
    }
    $contents = null;

    // Load the appropriate template file
    $templates = array(
      sprintf( 'wtp-petition-%d.php', $petition->id ),
      'wtp-petition.php'
    );
    if ( ! $template_file = locate_template( $templates, false ) ) {
      $template_file = dirname( __FILE__ ) . '/templates/wtp-petition.php';
    }

    // Echo or load the file in the output buffer
    if ( $echo ) {
      include $template_file;
    } else {
      ob_start();
      include $template_file;
      $contents = ob_get_contents();
      ob_end_clean();
    }
    return $contents;
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
    return $this->display_petition( current( $response ), false );
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
      return;

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

  /**
   * Register plugin JavaScript
   * @return void
   * @uses is_admin()
   * @uses wp_enqueue_script()
   * @uses wp_register_script()
   * @since 1.0
   */
  protected function register_scripts() {
    wp_register_script( 'we-the-people', plugins_url( 'js/we-the-people.js', __FILE__ ), array( 'jquery' ), self::PLUGIN_VERSION, true );
    $localization = array(
      'i18n' => array(
        'less' => __( '(less)', 'we-the-people' ),
        'more' => __( '(more)', 'we-the-people' )
      )
    );
    wp_localize_script( 'we-the-people', 'WeThePeople', $localization );

    if ( ! is_admin() ) {
      wp_enqueue_script( 'we-the-people' );
    }
  }

  /**
   * Register plugin styles
   * @return void
   * @uses is_admin()
   * @uses wp_enqueue_style()
   * @uses wp_register_style()
   */
  protected function register_styles() {
    wp_register_style( 'we-the-people', plugins_url( 'css/we-the-people.css', __FILE__ ), null, self::PLUGIN_VERSION, 'all' );

    if ( ! is_admin() ) {
      wp_enqueue_style( 'we-the-people' );
    }
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