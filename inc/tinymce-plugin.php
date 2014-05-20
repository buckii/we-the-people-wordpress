<?php
/**
 * "Insert Petition" TinyMCE plugin template
 *
 * @package We The People
 * @author Buckeye Interactive
 *
 * @todo Load scripts + styles via wp_head()
 */

// Attempt to load the WordPress environment by parsing our current URL to get to the root
// This is admittedly hackish and should be made more dependable
$config = str_replace( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '', __FILE__ ) . '/wp-config.php';
if ( file_exists( $config ) ) {
  require_once $config;
} else {
  throw new Exception( 'Unable to load the WordPress environment' );
}

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php _e( 'Insert a petition', 'we-the-people' ); ?></title>
<link href="../assets/dist/css/admin.css?<?php echo time(); ?>" type="text/css" rel="stylesheet" media="all" />
</head>

<body id="wtp-petition" class="wp-core-ui">
  <form id="wtp-insert-petition" action="?" onsubmit="javascript:wethepeople.insert();">
    <label for="petition-id"><?php _e( 'Petition ID:', 'we-the-people' ); ?></label>
    <input name="petition-id" id="petition-id" type="text" class="wtp-petition-id" />
    <?php if ( $GLOBALS['we-the-people']->get_api_key() ): ?>
      <label for="petition-enable-signature-form" class="inline">
        <input name="petition-enable-signature-form" id="petition-enable-signature-form" type="checkbox" />
        <?php _e( 'Enable signature form?', 'we-the-people' ); ?>
      </label>
    <?php endif; ?>

    <p><?php _e( "Don't know your petition ID? Search We The People:", 'we-the-people' ); ?></p>
    <label for="petition-search-term"><?php _e( 'Search term:', 'we-the-people' ); ?></label>
    <input name="petition-search-term" id="petition-search-term" type="text" class="wtp-petition-search" placeholder="<?php echo esc_attr( __( 'e.g. Guns, taxes, etc.', 'we-the-people' ) ); ?>" />
    <label for="petition-search-only-active" class="inline">
      <input name="petition-search-only-active" id="petition-search-only-active" type="checkbox" />
      <?php _e( 'Limit search results to active petitions?', 'we-the-people' ); ?>
    </label>
    <div id="search-results" class="wtp-search-results"></div>

    <div class="mceActionPanel">
      <input id="insert" type="submit" value="<?php echo esc_attr( __( 'Insert', 'we-the-people' ) ); ?>" />
      <input name="cancel" id="cancel" type="button" value="<?php echo esc_attr( __( 'Cancel', 'we-the-people' ) ); ?>" onclick="tinyMCEPopup.close();" />
    </div><!-- .mceActionPanel -->
  </form>

<script type="text/javascript" src="<?php echo includes_url( '/js/jquery/jquery.js' ); ?>"></script>
<script type="text/javascript" src="<?php echo includes_url( '/js/tinymce/tiny_mce_popup.js' ); ?>"></script>
<script type="text/javascript">
  var ajaxurl = ajaxurl || '<?php echo admin_url( 'admin-ajax.php' ); ?>';
</script>
<script type="text/javascript" src="../assets/dist/js/admin.js"></script>
<script type="text/javascript">
  var wethepeople = {
    editor: null,
    init: function ( ed ) {
      editor: ed,
      tinyMCEPopup.resizeToInnerSize();
    },
    insert: function insertPetition() {
      var shortcode_atts = {
        id: jQuery( '#petition-id' ).val(),
        signature: ( jQuery( '#petition-enable-signature-form' ).is( ':checked' ) ? '1' : '0' )
      },
      shortcode = '[<?php echo apply_filters( 'wethepeople_shortcode_name', 'wtp-petition' ); ?>',
      prop;

      // Iterate through our shortcode attributes
      for ( prop in shortcode_atts ) {
        if ( shortcode_atts.hasOwnProperty( prop ) ) {
          shortcode += ' ' + prop + '="' + shortcode_atts[ prop ] + '"';
        }
      }
      shortcode += ']';

      tinyMCEPopup.execCommand( 'mceReplaceContent', false, shortcode );
      tinyMCEPopup.close();
    }
  };

  // Setup our wethepeople object
  tinyMCEPopup.onInit.add( wethepeople.init, wethepeople );
</script>
</body>
</html>