<?php
/**
 * Plugin Name: We The People
 * Plugin URI: https://petitions.whitehouse.gov/
 * Description: Display White House petitions on your WordPress site
 * Version: 1.1
 * Author: Buckeye Interactive
 * Author URI: http://www.buckeyeinteractive.com
 * License: GPLv2 or later
 *
 * This plugin makes use of the White House's We The People API.
 * API Documentation can be found at https://petitions.whitehouse.gov/developers
 *
 * @package We The People
 * @author Buckeye Interactive
 */

require_once dirname( __FILE__ ) . '/plugin-options.php';
require_once dirname( __FILE__ ) . '/widget.php';
require_once dirname( __FILE__ ) . '/wtp-entity.class.php';

class WeThePeople_Plugin {

  /**
   * The API endpoint (with trailing slash) for all API requests
   */
  const API_ENDPOINT = 'https://api.whitehouse.gov/v1/';

  /**
   * The current plugin version
   */
  const PLUGIN_VERSION = '1.0';

  /**
   * The amount of time (in seconds) transient data should live before it's purged
   */
  const TRANSIENT_EXPIRES = 60;

  /**
   * The amount of time (in seconds) the long-term transient should live before it's purged
   */
  const TRANSIENT_LT_EXPIRES = 86400;

  /**
   * @var str $shortcode_name The name of the shortcode to register (defaults to 'wtp-petition')
   */
  public $shortcode_name;

  /**
   * Class constructor
   * @return void
   * @uses add_action()
   * @uses add_filter()
   * @uses add_shortcode()
   * @since 1.0
   */
  public function __construct() {
    // Register our shortcode
    $this->shortcode_name = apply_filters( 'wethepeople_shortcode_name', 'wtp-petition' );
    add_shortcode( $this->shortcode_name, array( &$this, 'petition_shortcode' ) );

    // Register/enqueue scripts and styles
    $this->register_scripts();
    $this->register_styles();

    // Register our TinyMCE button
    if ( ( current_user_can('edit_posts') || current_user_can('edit_pages') ) && get_user_option( 'rich_editing' ) ) {
      add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_plugin' ) );
      add_filter( 'mce_buttons_2', array( $this, 'add_tinymce_buttons' ) );
    }

    // Ajax hooks
    add_action( 'wp_ajax_wtp_petition_search', array( &$this, 'tinymce_ajax_petition_search' ) );
  }

  /**
   * Add the Petition button to TinyMCE
   * This method should be called via the 'mce_buttons' (or 'mce_buttons_#') WordPress filter
   * @param array $buttons The TinyMCE buttons
   * @return array
   * @since 1.0
   */
  public function add_tinymce_buttons( $buttons ) {
    array_push( $buttons, 'separator', 'wethepeople' );
    return $buttons;
  }

  /**
   * Make a call to the We The People API
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
   * @param array $data Additional data to pass to the template file
   * @return mixed (str|void depending on $echo)
   * @uses locate_template()
   * @since 1.0
   */
  public function display_petition( $petition, $data=array() ) {
    $contents = null;

    // Service unavailable and/or invalid petition
    if ( ! $petition || ! isset( $petition->id ) ) {
      $templates = array(
        sprintf( 'wtp-petition-error-%s.php', $petition->id ),
        'wtp-petition-error.php'
      );

    // Widgets
    } elseif ( isset( $data['widget'] ) && $data['widget'] ) {
      $templates = array(
        sprintf( 'wtp-petition-widget-%s.php', $petition->id ),
        'wtp-petition-widget.php'
      );

    // Actual content, not a sidebar
    } else {
      $templates = array(
        sprintf( 'wtp-petition-%s.php', $petition->id ),
        'wtp-petition.php'
      );
    }

    // Locate the template or default to system templates
    if ( ! $template_file = locate_template( $templates, false, false ) ) {
      $template_file = dirname( __FILE__ ) . '/templates/' . end( $templates );
    }

    // We need to make sure these are available to the template when we load it
    $widget_args = ( isset( $data['widget_args'] ) ? $data['widget_args'] : array() );

    // Echo or load the file in the output buffer
    if ( isset( $data['echo'] ) && $data['echo'] ) {
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
   * Handler for the [wtp-petition] shortcode
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

    // The ID attribute is required
    if ( ! $atts['id'] ) {
      $this->error( __( 'Invalid petition ID', 'we-the-people' ) );
      return;
    }

    $response = $this->api( 'retrieve', array( 'id' => $atts['id'] ) );
    if ( empty( $response ) ) {
      $this->error( sprintf( __( 'API response for petition %s came back empty', 'we-the-people' ), $atts['id'] ) );
      return;
    }
    return $this->display_petition( $response, $atts );
  }

  /**
   * Register our TinyMCE plugin
   * This should be called via the 'mce_external_plugins' filter
   * @param array $plugins Plugins registered within TinyMCE
   * @return array
   * @uses plugins_url()
   * @since 1.0
   */
  public function register_tinymce_plugin( $plugins ) {
    $plugins['wethepeople'] = plugins_url( 'js/tinymce/petition.js?' . self::PLUGIN_VERSION, __FILE__ );
    return $plugins;
  }

  /**
   * Ajax handler for a petition search
   * @return void
   * @since 1.0
   */
  public function tinymce_ajax_petition_search() {
    // If we have something in $_POST['term'] search the API for titles with that string
    $args = array();
    if ( isset( $_POST['term'] ) && $_POST['term'] ) {
      $args['title'] = $_POST['term'];
    }
    $response =  $this->api( 'index', $args );

    if ( ! $response ) {
      die( __( 'Unable to connect to We The People', 'we-the-people' ) );
    }

    // Determine how we're going to display the results
    switch ( $_POST['format'] ) {
      case 'ul':
        echo '<ul>';
        foreach ( $response as $petition ) {
          printf( '<li><a href="%s" data-petition-id="%s">%s</a> <span class="signature-count">%s</span></li>',
            $petition->url, $petition->id, $petition->title, sprintf( __( '%d signatures', 'we-the-people' ), $petition->signatureCount )
          );
        }
        echo '</ul>';
        break;

      default:
        echo json_encode( $response );
        break;
    }
    die(); // Necessary to close the Ajax connection
  }

  /**
   * Handle requests for the API index action
   * Documentation for the API can be found at: https://petitions.whitehouse.gov/developers
   * @param array $args Arguments to pass to the API call
   * @return object
   * @since 1.0
   */
  protected function api_index_action( $args=array() ) {
    return $this->make_api_call( sprintf( 'petitions.json?%s', http_build_query( $args ) ) );
  }

  /**
   * Handle requests for the API retrieve action
   * Currently the only argument retrieve takes is an ID so $args['id'] should always be set
   * @param array $args Arguments to pass to the API call
   * @return object
   * @since 1.0
   */
  protected function api_retrieve_action( $args=array() ) {
    $id = ( isset( $args['id'] ) ? $args['id'] : null );
    $response = $this->make_api_call( sprintf( 'petitions/%s.json', $id ) );
    return new We_The_People_Entity( ( $response && is_array( $response ) ? current( $response ) : '' ) );
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
    error_log( sprintf( 'WeThePeople: %s', $message ) );
  }

  /**
   * Make the actual call to the API endpoint and return the results as a PHP object
   *
   * This method uses the WordPress Transient API to save API responses for TRANSIENT_EXPIRES in the database.
   * During development the API was somewhat flaky. In order to prevent an error we're also keeping a long-term
   * transient in the database with a higher expiration time (TRANSIENT_LT_EXPIRES). It's probably better to have
   * data that's up to 1hr out-of-date rather than nothing at all.
   *
   * @param str $call The assembled API call
   * @return object
   * @uses get_transient()
   * @uses is_wp_error()
   * @uses set_transient()
   * @uses trailingslashit()
   * @uses wp_remote_get()
   * @since 1.0
   */
  protected function make_api_call( $call ) {
    $request_uri = trailingslashit( self::API_ENDPOINT ) . $call;
    $hash = md5( $request_uri ); // Transient keys should be < 45 chars
    $lt_hash = 'lt-' . $hash;

    // If we have a matching [short-term] transient return that instead
    if ( $data = get_transient( $hash ) ) {
      return $data;
    }

    // If we're still in at this point then we need to actually make an API call
    $response = wp_remote_get( $request_uri );
    if ( is_wp_error( $response ) ) {

      // An error? Maybe we still have some data in a long-term transient
      if ( $data = get_transient( $lt_hash ) ) {
        return $data;
      }

      // No long-term transients
      $this->error( $response->get_error_message() );
      return false;

    // It wasn't a WP_Error but the response doesn't look right...
    } elseif ( isset( $response['response']['code'] ) && $response['response']['code'] != 200 ) {
      $this->error( sprintf( __( 'API endpoint returned an unexpected status code of "%s: %s"', 'we-the-people' ),
        $response['response']['code'], $response['response']['message']
      ) );
    }

    // Save the response body as a transient
    $body = json_decode( $response['body'], false );

    // Catch when the service is unavailable ::cough::shutdown::cough::
    if ( isset( $body->metadata->responseInfo->status ) && $body->metadata->responseInfo->status == 500 ) {
      $this->error( sprintf( __( 'The We The People site is currently unavailable: %s', 'we-the-people' ),
        $body->metadata->responseInfo->developerMessage
      ) );
      return false;
    }
    set_transient( $hash, $body->results, self::TRANSIENT_EXPIRES );
    set_transient( $lt_hash, $body->results, self::TRANSIENT_LT_EXPIRES );
    return $body->results;
  }

  /**
   * Register plugin JavaScript
   * @global $pagenow
   * @return void
   * @uses is_admin()
   * @uses plugins_url()
   * @uses wp_enqueue_script()
   * @uses wp_localize_script()
   * @uses wp_register_script()
   * @since 1.0
   */
  protected function register_scripts() {
    global $pagenow;

    wp_register_script( 'we-the-people', plugins_url( 'js/we-the-people.js', __FILE__ ), array( 'jquery' ), self::PLUGIN_VERSION, true );
    $localization = array(
      'i18n' => array(
        'less' => __( '(less)', 'we-the-people' ),
        'more' => __( '(more)', 'we-the-people' )
      )
    );
    wp_localize_script( 'we-the-people', 'WeThePeople', $localization );
    wp_register_script( 'we-the-people-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), self::PLUGIN_VERSION, true );

    if ( ! is_admin() ) {
      wp_enqueue_script( 'we-the-people' );
    } elseif ( is_admin() && $pagenow == 'widgets.php' ) {
      wp_enqueue_script( 'we-the-people-admin' );
    }
  }

  /**
   * Register plugin styles
   * @global $pagenow
   * @return void
   * @uses is_admin()
   * @uses plugins_url()
   * @uses wp_enqueue_style()
   * @uses wp_register_style()
   * @since 1.0
   */
  protected function register_styles() {
    global $pagenow;

    wp_register_style( 'we-the-people', plugins_url( 'css/we-the-people.css', __FILE__ ), null, self::PLUGIN_VERSION, 'all' );
    wp_register_style( 'we-the-people-admin', plugins_url( 'css/admin.css', __FILE__ ), null, self::PLUGIN_VERSION, 'all' );

    if ( ! is_admin() ) {
      wp_enqueue_style( 'we-the-people' );
    } elseif ( is_admin() && $pagenow == 'widgets.php' ) {
      wp_enqueue_style( 'we-the-people-admin' );
    }
  }

}

/**
 * Create an instance of WeThePeople_Plugin and store it in the global $we_the_people
 * @global $we_the_people
 * @return bool
 * @uses load_plugin_textdomain()
 * @since 1.0
 */
function wethepeople_init() {
  global $we_the_people;
  $we_the_people = new WeThePeople_Plugin;
  load_plugin_textdomain( 'we-the-people', null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  return true;
}
add_action( 'init', 'wethepeople_init' );