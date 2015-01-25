<?php
/**
 * Plugin Name: We The People
 * Plugin URI: https://petitions.whitehouse.gov/
 * Description: Display White House petitions on your WordPress site
 * Version: 2.0
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

require_once dirname( __FILE__ ) . '/inc/plugin-options.php';
require_once dirname( __FILE__ ) . '/inc/template-tags.php';
require_once dirname( __FILE__ ) . '/inc/widget.php';
require_once dirname( __FILE__ ) . '/inc/wtp-entity.class.php';

class WeThePeople_Plugin {

  /**
   * The API endpoint (with trailing slash) for all API requests.
   */
  const API_ENDPOINT = 'https://api.whitehouse.gov/v1/';

  /**
   * The URL for API key registration.
   */
  const API_KEY_REGISTRATION_URL = 'http://www.whitehouse.gov/webform/apply-access-we-people-write-api';

  /**
   * The current plugin version.
   */
  const PLUGIN_VERSION = '2.0';

  /**
   * Codes to indicate success/errors with signature requests.
   */
  const SIGNATURE_STATUS_CODE_ERROR = 'err';
  const SIGNATURE_STATUS_CODE_SUCCESS = 'success';

  /**
   * The query var used to indicate whether or not a signature was submitted successfully.
   */
  const SIGNATURE_STATUS_QUERY_VAR = 'wtp-signature-%s';

  /**
   * The amount of time (in seconds) transient data should live before it's purged.
   */
  const TRANSIENT_EXPIRES = MINUTE_IN_SECONDS;

  /**
   * The amount of time (in seconds) the long-term transient should live before it's purged.
   */
  const TRANSIENT_LT_EXPIRES = DAY_IN_SECONDS;

  /**
   * @var string $api_key The We The People API key.
   */
  public $api_key;

  /**
   * @var string $shortcode_name The name of the shortcode to register (defaults to 'wtp-petition').
   */
  public $shortcode_name;

  /**
   * @var string $templates_path The system path to the plugins' templates/ directory.
   */
  public $template_path;

  /**
   * Class constructor.
   *
   * @since 1.0
   */
  public function __construct() {
    $this->template_path = trailingslashit( dirname( __FILE__ ) ) . 'templates/';

    // Register our shortcode
    $this->shortcode_name = apply_filters( 'wethepeople_shortcode_name', 'wtp-petition' );
    add_shortcode( $this->shortcode_name, array( &$this, 'petition_shortcode' ) );

    // Register the plugin settings page
    new WeThePeople_Plugin_Options;

    // Register/enqueue scripts and styles
    $this->register_scripts();
    $this->register_styles();

    // Register our TinyMCE button
    if ( ( current_user_can('edit_posts') || current_user_can('edit_pages') ) && get_user_option( 'rich_editing' ) ) {
      add_action( 'admin_head-post.php', array( &$this, 'add_plugin_url_to_global_variable' ) );
      add_action( 'admin_head-post-new.php', array( &$this, 'add_plugin_url_to_global_variable' ) );
      add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_plugin' ) );
      add_filter( 'mce_buttons_2', array( $this, 'add_tinymce_buttons' ) );
    }

    // Ajax hooks
    add_action( 'wp_ajax_wtp_petition_search', array( &$this, 'tinymce_ajax_petition_search' ) );
    add_action( 'wp_ajax_wtp_petition_signature', array( &$this, 'sign_petition' ) );
    add_action( 'wp_ajax_nopriv_wtp_petition_signature', array( &$this, 'sign_petition' ) );
  }

  /**
   * Put the plugin URL in a global JS variable so it's available for our TinyMCE widget.
   *
   * @since 2.0
   */
  public function add_plugin_url_to_global_variable() {
    printf( '<script type="text/javascript">var WeThePeople = { plugin_url: "%s" }</script>' . PHP_EOL, plugins_url( '/', __FILE__ ) );
  }

  /**
   * Add the Petition button to TinyMCE.
   *
   * This method should be called via the 'mce_buttons' (or 'mce_buttons_#') WordPress filter.
   *
   * @param array $buttons The registered TinyMCE buttons.
   * @return array The modified $buttons array.
   *
   * @since 1.0
   */
  public function add_tinymce_buttons( $buttons ) {
    array_push( $buttons, 'separator', 'wethepeople' );
    return $buttons;
  }

  /**
   * Make a call to the We The People API.
   *
   * This method acts as a controller, farming out the work to more specialized API methods.
   *
   * @param string $action The API method (retrieve|index) to call.
   * @param array  $args   Arguments to pass to the API call.
   * @return stdClass The response from the API method's call.
   *
   * @since 1.0
   */
  public function api( $action, $args = array() ) {
    $method = sprintf( 'api_%s_action', strtolower( trim( $action ) ) );
    if ( ! method_exists( $this, $method ) ) {
      $this->error( sprintf( __( 'Class method %s does not exist', 'we-the-people' ), $method ) );
    }
    return call_user_func( array( $this, $method ), $args );
  }

  /**
   * Display a petition.
   *
   * This plugin will detect the appropriate template file to load using the following priorities:
   * 1. wtp-petition-{id}.php (child theme)
   * 2. wtp-petition.php (child theme)
   * 3. wtp-petition-{id}.php (parent theme)
   * 4. wtp-petition.php (parent theme)
   * 5. templates/wtp-petition.php (plugin)
   *
   * @param object $petition An API response for a single petition.
   * @param array  $data     Additional data to pass to the template file.
   * @return mixed (str|void depending on $echo).
   *
   * @since 1.0
   */
  public function display_petition( $petition, $data = array() ) {
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
    $signature = ( isset( $data['signature'] ) ? $data['signature'] : false );

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
   * Retrieve the We The People API key.
   *
   * @return string The API key.
   *
   * @since 2.0
   */
  public function get_api_key() {
    if ( $this->api_key === null ) {
      if ( defined( 'WTP_API_KEY' ) && WTP_API_KEY ) {
        $this->api_key = WTP_API_KEY;
      } else {
        $this->api_key = wethepeople_get_option( 'api_key', false );
      }
    }
    return $this->api_key;
  }

  /**
   * Handler for the [wtp-petition] shortcode.
   *
   * @param array  $atts    Attributes passed in the shortcode call.
   * @param string $content Content to be added above the petition information.
   * @return string The [wtp-petition] shortcode.
   *
   * @since 1.0
   */
  public function petition_shortcode( $atts, $content = '' ) {
    $defaults = array(
      'id' => false,
      'signature' => false
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
   * Register our TinyMCE plugin.
   *
   * This should be called via the 'mce_external_plugins' filter.
   *
   * @param array $plugins Plugins registered within TinyMCE.
   * @return array Registered plugins, including "wethepeople".
   *
   * @since 1.0
   */
  public function register_tinymce_plugin( $plugins ) {
    $plugins['wethepeople'] = plugins_url( 'assets/dist/js/tinymce.js?' . self::PLUGIN_VERSION, __FILE__ );
    return $plugins;
  }

  /**
   * Sign a petition through the WTP API.
   *
   * @since 2.0
   */
  public function sign_petition() {
    try {

      // An API key is required to sign petitions
      if ( ! $api_key = $this->get_api_key() ) {
        throw new Exception( __( 'A valid API key is required to sign petitions', 'we-the-people' ) );
      }

      // We can't do anything without a petition ID
      if ( ! isset( $_POST['petition_id'] ) || ! $_POST['petition_id'] ) {
        throw new Exception( __( 'A petition ID is required', 'we-the-people' ) );
      }

      // Required fields
      if ( ! isset( $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['zip'] ) ) {
        throw new Exception( __( 'Required fields are missing from the $_POST object', 'we-the-people' ) );
      }

      // Build our request body
      $data = array(
        'petition_id' => preg_replace( '/[^A-Z0-9]/i', '', $_POST['petition_id'] ),
        'first_name' => filter_var( $_POST['first_name'], FILTER_SANITIZE_STRING ),
        'last_name' => filter_var( $_POST['last_name'], FILTER_SANITIZE_STRING ),
        'email' => filter_var( $_POST['email'], FILTER_SANITIZE_EMAIL ),
        'zip' => preg_replace( '/[^0-9-]/i', '', $_POST['zip'] )
      );

      foreach ( $data as $k => $v ) {
        if ( ! $v ) {
          throw new Exception( sprintf( __( 'Field %s is required', 'we-the-people' ), $k ) );
        }
      }

      // Assemble our request
      $request_uri = trailingslashit( self::API_ENDPOINT ) . 'signatures?api_key=' . $api_key;
      $params = array(
        'headers' => array(
          'Accept' => 'application/json',
          'Content-Type' => 'application/json',
        ),
        'body' => json_encode( $data )
      );

      $response = wp_remote_post( $request_uri, $params );

      // Check it for errors
      if ( is_wp_error( $response ) ) {
        throw new Exception( $response->get_error_message() );

      } elseif ( isset( $response['response']['code'] ) && $response['response']['code'] != 200 ) {
        throw new Exception( sprintf( __( 'API endpoint returned an unexpected status code of "%s: %s"', 'we-the-people' ),
          $response['response']['code'], $response['response']['message']
        ) );
      }

      $status = self::SIGNATURE_STATUS_CODE_SUCCESS;

    } catch ( Exception $e ) {
      $this->error( sprintf( __( 'There was a problem signing the petition: %s', 'we-the-people' ), $e->getMessage() ) );
      $status = self::SIGNATURE_STATUS_CODE_ERROR;
    }

    // Issue a response
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['actually_ajax'] ) ) {
      print $status;

    // We're actually on admin-ajax.php and it's not an Ajax call - send the user back
    } else {
      wp_safe_redirect( add_query_arg( sprintf( self::SIGNATURE_STATUS_QUERY_VAR, $data['petition_id'] ), $status, wp_get_referer() ) );
    }
    exit;
  }

  /**
   * Ajax handler for a petition search.
   *
   * @since 1.0
   */
  public function tinymce_ajax_petition_search() {
    // If we have something in $_POST['term'] search the API for titles with that string
    $args = array();
    if ( isset( $_POST['term'] ) && $_POST['term'] ) {
      $args['title'] = $_POST['term'];
    }
    if ( isset( $_POST['activeOnly'] ) && intval( $_POST['activeOnly'] ) ) {
      $args['status'] = 'open';
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
          if ( $petition->status == 'responded' ) {
            $status = sprintf( '<span class="petition-status petition-status-responded">%s</span>', __( '(Responded)', 'we-the-people' ) );
          } elseif ( $petition->status == 'pending response' ) {
            $status = sprintf( '<span class="petition-status petition-status-pending-response">%s</span>', __( '(Pending response)', 'we-the-people' ) );
          } elseif ( $petition->status == 'closed' ) {
            $status = sprintf( '<span class="petition-status petition-status-closed">%s</span>', __( '(Closed)', 'we-the-people' ) );
          } else {
            $status = '';
          }

          printf(
            '<li><a href="%s" title="%s" data-petition-id="%s">%s</a> %s<span class="signature-count">%s</span></li>',
            $petition->url,
            esc_attr( trim( strip_tags( $petition->body ) ) ),
            $petition->id,
            $petition->title,
            $status,
            sprintf( __( '%s signatures', 'we-the-people' ), number_format_i18n( $petition->signatureCount ) )
          );
        }
        echo '</ul>';
        break;

      default:
        echo json_encode( $response );
        break;
    }
    exit;
  }

  /**
   * Handle requests for the API index action.
   *
   * Documentation for the API can be found at: https://petitions.whitehouse.gov/developers.
   *
   * @param array $args Arguments to pass to the API call.
   * @return object The response from make_api_call().
   *
   * @since 1.0
   */
  protected function api_index_action( $args = array() ) {
    return $this->make_api_call( sprintf( 'petitions.json?%s', http_build_query( $args ) ) );
  }

  /**
   * Handle requests for the API retrieve action.
   *
   * Currently the only argument retrieve takes is an ID so $args['id'] should always be set.
   *
   * @param array $args Arguments to pass to the API call.
   * @return We_The_People_Entity object.
   *
   * @since 1.0
   */
  protected function api_retrieve_action( $args = array() ) {
    $id = ( isset( $args['id'] ) ? $args['id'] : null );
    $response = $this->make_api_call( sprintf( 'petitions/%s.json', $id ) );
    return new We_The_People_Entity( ( $response && is_array( $response ) ? current( $response ) : '' ) );
  }

  /**
   * Log an error.
   *
   * @param string $message The error message to log.
   * @return bool.
   *
   * @since 1.0
   */
  protected function error( $message ) {
    return error_log( sprintf( 'WeThePeople: %s', $message ) );
  }

  /**
   * Make the actual call to the API endpoint and return the results as a PHP object.
   *
   * This method uses the WordPress Transient API to save API responses for TRANSIENT_EXPIRES in
   * the database.
   *
   * During development the API was somewhat flaky. In order to prevent an error we're also keeping
   * a long-term transient in the database with a higher expiration time (TRANSIENT_LT_EXPIRES).
   * It's probably better to have data that's up to 1hr out-of-date rather than nothing at all.
   *
   * @param string $call The assembled API call.
   * @return object The API response.
   *
   * @since 1.0
   */
  protected function make_api_call( $call, $args = array() ) {
    $request_uri = trailingslashit( self::API_ENDPOINT ) . $call;
    $hash = md5( $request_uri );
    $transient_name = sprintf( 'wtp-%s', $hash );
    $lt_transient_name = sprintf( 'wtp-lt-%s', $hash );

    // If we have a matching [short-term] transient return that instead
    if ( $data = get_transient( $transient_name ) ) {
      return $data;
    }

    // If we're still in at this point then we need to actually make an API call
    $response = wp_remote_get( $request_uri );
    if ( is_wp_error( $response ) ) {

      // An error? Maybe we still have some data in a long-term transient
      if ( $data = get_transient( $lt_transient_name ) ) {
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
      return false;
    }

    // Save the response body as a transient
    $body = json_decode( $response['body'], false );

    // Catch when the service is unavailable ::cough::government shutdown::cough::
    if ( isset( $body->metadata->responseInfo->status ) && $body->metadata->responseInfo->status == 500 ) {
      $this->error( sprintf( __( 'The We The People site is currently unavailable: %s', 'we-the-people' ),
        $body->metadata->responseInfo->developerMessage
      ) );
      return false;
    }
    set_transient( $transient_name, $body->results, self::TRANSIENT_EXPIRES );
    set_transient( $lt_transient_name, $body->results, self::TRANSIENT_LT_EXPIRES );
    return $body->results;
  }

  /**
   * Register plugin JavaScript.
   *
   * @global $pagenow
   *
   * @since 1.0
   */
  protected function register_scripts() {
    global $pagenow;

    wp_register_script( 'we-the-people', plugins_url( 'assets/dist/js/we-the-people.js', __FILE__ ), array( 'jquery' ), self::PLUGIN_VERSION, true );
    $localization = array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'i18n' => array(
        'less' => __( '(less)', 'we-the-people' ),
        'more' => __( '(more)', 'we-the-people' ),
        'signatureError' => __( 'There was an error submitting your signature for this petition!', 'we-the-people' ),
        'signatureSuccess' => __( 'Your signature has been submitted! You should receive a confirmation email from We The People shortly!', 'we-the-people' )
      ),
      'signatureStatus' => array(
        'success' => self::SIGNATURE_STATUS_CODE_SUCCESS,
        'error' => self::SIGNATURE_STATUS_CODE_ERROR
      )
    );
    wp_localize_script( 'we-the-people', 'WeThePeople', $localization );
    wp_register_script( 'we-the-people-admin', plugins_url( 'assets/dist/js/admin.js', __FILE__ ), array( 'jquery' ), self::PLUGIN_VERSION, true );

    if ( ! is_admin() && apply_filters( 'wethepeople_load_scripts', true ) ) {
      wp_enqueue_script( 'we-the-people' );
    } elseif ( is_admin() && $pagenow == 'widgets.php' ) {
      wp_enqueue_script( 'we-the-people-admin' );
    }
  }

  /**
   * Register plugin styles.
   *
   * If a CSS file that matches the theme name exists in assets/dist/css, it will automatically be loaded.
   *
   * @global $pagenow
   *
   * @since 1.0
   */
  protected function register_styles() {
    global $pagenow;

    $hook = 'we-the-people';
    wp_register_style( 'we-the-people', plugins_url( 'assets/dist/css/we-the-people.css', __FILE__ ), null, self::PLUGIN_VERSION, 'all' );
    wp_register_style( 'we-the-people-admin', plugins_url( 'assets/dist/css/admin.css', __FILE__ ), null, self::PLUGIN_VERSION, 'all' );

    // Attempt to load theme-specific stylesheet fixes
    $template = get_option( 'template' );
    if ( file_exists( sprintf( '%s/assets/dist/css/%s.css', dirname( __FILE__ ), $template ) ) ) {
      $hook = sprintf( 'we-the-people-%s', $template );
      wp_register_style( $hook, plugins_url( sprintf( 'assets/dist/css/%s.css', $template ), __FILE__ ), array( 'we-the-people' ), self::PLUGIN_VERSION, 'all' );
    }

    if ( ! is_admin() && apply_filters( 'wethepeople_load_styles', true ) ) {
      wp_enqueue_style( $hook );
    } elseif ( is_admin() && $pagenow == 'widgets.php' ) {
      wp_enqueue_style( 'we-the-people-admin' );
    }
  }

}

/**
 * Create an instance of WeThePeople_Plugin and store it in the globals $GLOBALS['we-the-people']
 *
 * @since 1.0
 */
function wethepeople_init() {
  $GLOBALS['we-the-people'] = new WeThePeople_Plugin;
  load_plugin_textdomain( 'we-the-people', null, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'wethepeople_init' );