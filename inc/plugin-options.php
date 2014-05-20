<?php
/**
 * Theme options page
 *
 * @package We The People
 * @author Buckeye Interactive
 */

class WeThePeople_Plugin_Options {

  /**
   * The name of our options array in the wp_options table
   */
  const OPTION_NAME = 'wethepeople_options';

  /**
   * Register our options page
   *
   * @since 2.0
   */
  public function __construct() {
    if ( is_admin() && ! defined( 'WTP_API_KEY' ) ) {
      add_action( 'admin_menu', array( &$this, 'add_options_page' ) );
      add_action( 'admin_init', array( &$this, 'page_init' ) );
    }
  }

  /**
   * Register the theme options page within WordPress
   *
   * @since 2.0
   */
  public function add_options_page() {
    add_options_page(
      __( 'We The People', 'we-the-people' ),
      __( 'We The People', 'we-the-people' ),
      'manage_options',
      'we-the-people',
      array( &$this, 'create_options_page' )
    );
  }

  /**
   * Generate the options page markup
   *
   * @since 2.0
   */
  public function create_options_page() {
    print '<div class="wrap">';
    screen_icon();
    printf( '<h2>%s</h2>', __( 'We The People', 'we-the-people' ) );
    print '<form method="post" action="options.php">';
    settings_fields( 'wethepeople_options' );
    do_settings_sections( 'we-the-people' );
    submit_button();
    print '</form>';
    print '</div>';
  }

  /**
   * Initialize the options page
   *
   * @since 2.0
   */
  public function page_init() {
    register_setting( 'wethepeople_options', 'wethepeople_options' );

    add_settings_section(
      'wethepeople_options_api',
      __( 'Write API', 'we-the-people' ),
      array( &$this, 'print_api_section_info' ),
      'we-the-people'
    );
    add_settings_field(
      'wethepeople_options_api_key',
      __( 'API key', 'we-the-people' ),
      array( &$this, 'create_textfield' ),
      'we-the-people',
      'wethepeople_options_api',
      array( 'code' => true, 'name' => 'api_key' )
    );
  }

  /**
   * Create an input[type="text"] element
   *
   * Accepted keys in $args:
   * code (bool) Apply pre-formatted code styling?
   * default (str) The default value
   * name - The key within the OPTION_NAME options array
   *
   * @param array $args
   *
   * @since 2.0
   */
  public function create_textfield( $args = array() ) {
    $settings = get_option( self::OPTION_NAME );
    $classes = ( isset( $args['code'] ) && $args['code'] ? 'regular-text code' : '' );
    $default = ( isset( $args['default'] ) ? $args['default'] : '' );
    printf( '<input name="%s[%s] type="text" value="%s" class="%s" />', self::OPTION_NAME, $args['name'], ( isset( $settings[ $args['name'] ] ) ? $settings[ $args['name'] ] : $default ), $classes );
  }

  /**
   * Print instructions before the fields for the wethepeople_options_api settings section
   *
   * @since 2.0
   */
  public function print_api_section_info() {
    printf( '<p>%s</p>',
      sprintf(
        __( 'In order for guests to sign petitions from your site you\'ll need to apply for an API key through <a href="%s" rel="external" target="_blank">We The People</a> website.', 'we-the-people' ),
        WeThePeople_Plugin::API_KEY_REGISTRATION_URL
      )
    );
  }

}

/**
 * Get an option from our theme options array
 *
 * @param str $key The option key
 * @param str $default The default value to return (if $key is not null)
 * @return mixed The value of $key if $key is defined, otherwise an array of all the theme options
 *
 * @since 2.0
 */
function wethepeople_get_option( $key = null, $default = null ) {
  $return = $options = get_option( WeThePeople_Plugin_Options::OPTION_NAME );
  if ( $key ) {
    $return = ( isset( $options[ $key ] ) ? $options[ $key ] : $default );
  }
  return $return;
}