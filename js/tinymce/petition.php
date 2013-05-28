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
<title><?php _e( 'Insert petition', 'we-the-people' ); ?></title>
<script type="text/javascript" src="<?php echo includes_url( '/js/jquery/jquery.js' ); ?>"></script>
<script language="javascript" type="text/javascript" src="<?php echo includes_url( '/js/tinymce/tiny_mce_popup.js' ); ?>"></script>
<style type="text/css" src="<?php echo includes_url( '/js/tinymce/themes/advanced/skins/wp_theme/dialog.css' ); ?>"></style>
<link href="petition.css?<?php echo time(); ?>" type="text/css" rel="stylesheet" media="all" />
<style type="text/css">
  #petition-search-form.loading { background: url('<?php echo includes_url( 'images/wpspin.gif' ); ?>') right center no-repeat; }
</style>
</head>

<body id="wtp-petition" class="wp-core-ui">
  <form id="wtp-insert-petition" action="?" onsubmit="javascript:wethepeople.insert();">
    <h3><?php _e( 'Insert a petition', 'we-the-people' ); ?></h3>
    <?php /*<ul id="tabs">
      <li><a href="#tab-basic" accesskey="1" class="current"><?php _e( 'Basic', 'we-the-people' ); ?></a></li>
      <li><a href="#tab-advanced" accesskey="2"><?php _e( 'Advanced', 'we-the-people' ); ?></a></li>
    </ul>*/ ?>
    <div class="wrap">
      <div id="tab-basic" class="tab">
        <?php /*<h3><?php _e( 'Select your petition', 'we-the-people' ); ?></h3>*/ ?>

        <label for="petition-id"><?php _e( 'Petition ID:', 'we-the-people' ); ?></label>
        <input name="petition-id" id="petition-id" type="text" />

        <p><?php _e( "Don't know your petition ID? Search We The People:", 'we-the-people' ); ?></p>
        <label for="petition-search-term"><?php _e( 'Search term:', 'we-the-people' ); ?></label>
        <input name="petition-search-form" id="petition-search-form" type="text" placeholder="<?php echo esc_attr( __( 'e.g. Guns, taxes, etc', 'we-the-people' ) ); ?>" />
        <div id="search-results"></div>
      </div><!-- #tab-basic -->

      <?php /*<div id="tab-advanced" class="tab">
        <h3><?php _e( 'Advanced settings', 'we-the-people' ); ?></h3>

        <label for="petition-intro"><?php _e( 'Preamble', 'we-the-people' ); ?></label>
        <textarea name="petition-intro" id="petition-intro" rows="4" cols="40"></textarea>
      </div><!-- #tab-advanced -->*/ ?>
    </div><!-- .wrap.tabbed -->

    <div class="mceActionPanel">
      <input id="insert" type="submit" value="<?php echo esc_attr( __( 'Insert', 'we-the-people' ) ); ?>" />
      <input name="cancel" id="cancel" type="button" value="<?php echo esc_attr( __( 'Cancel', 'we-the-people' ) ); ?>" onclick="tinyMCEPopup.close();" />
    </div><!-- .mceActionPanel -->
  </form>

<script type="text/javascript">
  /**
   * @todo Refactor this
   */
  var ajaxurl = ajaxurl || '<?php echo admin_url( 'admin-ajax.php' ); ?>';

  function wethepeople_petition_search() {
    "use strict";
    var $ = jQuery,
    data = {
      action: 'wtp_petition_search',
      format: 'ul',
      term: $('#petition-search-form').val()
    };

    $.post( ajaxurl, data, function ( response ) {
      $('#search-results').html( response );
      $('#petition-search-form').removeClass( 'loading' );
    });
    return;
  }

  function wethepeople_init() {
    "use strict";
    var $ = jQuery,
    tabs = $('#tabs'),
    tabbedArea = $('.tabbed');

    /* Tabbed navigation */
    tabs.show();
    tabbedArea.find( '.tab:not(:first)' ).hide();

    tabs.on( 'click', 'a', function ( e ) {
      var self = $(this);
      e.preventDefault();

      tabs.find( 'a.current' ).removeClass( 'current' );
      self.addClass( 'current' );
      tabbedArea.find( '.tab:visible' ).hide();
      $( self.attr( 'href' ) ).show();
      return false;
    });

    /* Petition search */
    $('#petition-search-form').on( 'keyup', function () {
      var self = $(this);
      if ( self.val().length >= 3 ) {
        self.addClass( 'loading' );
        wethepeople_petition_search();
      }
      return true;
    });

    $('#search-results').on( 'click', 'a', function ( e ) {
      e.preventDefault();
      $('#petition-id').val( $(this).data( 'petition-id' ) );
      return false;
    });
  }

  var wethepeople = {
    editor: null,
    init: function ( ed ) {
      editor: ed,
      tinyMCEPopup.resizeToInnerSize();
    },
    insert: function insertPetition() {
      var shortcode_atts = {
        id: jQuery( '#petition-id' ).val()
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

  // This will let us actually use jQuery event listeners
  tinyMCEPopup.executeOnLoad( 'wethepeople_init()' );
  wethepeople_init();

</script>
</body>
</html>