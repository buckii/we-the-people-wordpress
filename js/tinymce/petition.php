<?php
/**
 * "Insert Petition" TinyMCE plugin template
 *
 * @package We The People
 * @author Buckeye Interactive
 *
 * @todo Load scripts + styles via wp_head()
 * @todo Advanced settings
 * @todo Search the We The People petitions to find an ID
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
<style type="text/css">

body {
  min-width: 0;
}

#tabs {
  padding: 15px 15px 3px;
  background-color: #f1f1f1;
  border-bottom: 1px solid #dfdfdf;
  margin: 0;
}
#tabs li {
  display: inline;
}
#tabs a.current {
  background-color: #fff;
  border-color: #dfdfdf;
  border-bottom-color: #fff;
  color: #d54e21;
}
#tabs a {
  color: #2583AD;
  padding: 6px;
  border-width: 1px 1px 0;
  border-style: solid solid none;
  border-color: #f1f1f1;
  text-decoration: none;
}
#tabs a:hover {
  color: #d54e21;
}
.wrap h2 {
  border-bottom-color: #dfdfdf;
  color: #555;
  margin: 5px 0;
  padding: 0;
  font-size: 18px;
}
#user_info {
  right: 5%;
  top: 5px;
}
h3 {
  font-size: 1.1em;
  margin-top: 10px;
  margin-bottom: 0px;
}
.tab {
  margin: 0;
  padding: 5px 20px 10px;
  background-color: #fff;
  border-left: 1px solid #dfdfdf;
  border-bottom: 1px solid #dfdfdf;
}
* html {
      overflow-x: hidden;
      overflow-y: scroll;
  }
#flipper div p {
  margin-top: 0.4em;
  margin-bottom: 0.8em;
  text-align: justify;
}



</style>

</head>

<body id="wtp-petition" class="wp-core-ui">
  <form id="wtp-insert-petition" action="?" onsubmit="javascript:wethepeople.insert();">
    <h2><?php _e( 'Insert a petition', 'we-the-people' ); ?></h2>
    <ul id="tabs">
      <li><a href="#tab-basic" accesskey="1" class="current"><?php _e( 'Basic', 'we-the-people' ); ?></a></li>
      <li><a href="#tab-advanced" accesskey="2"><?php _e( 'Advanced', 'we-the-people' ); ?></a></li>
    </ul>
    <div class="wrap tabbed">
      <div id="tab-basic" class="tab">
        <h3><?php _e( 'Select your petition', 'we-the-people' ); ?></h3>

        <label for="petition-id"><?php _e( 'Petition ID:', 'we-the-people' ); ?></label>
        <input name="petition-id" id="petition-id" type="text" />

        <p><?php _e( "Don't know your petition ID? Search We The People:", 'we-the-people' ); ?></p>
        <label for="petition-search-term"><?php _e( 'Search term:', 'we-the-people' ); ?></label>
        <input name="petition-search-form" id="petition-search-form" type="text" placeholder="<?php echo esc_attr( __( 'e.g. Guns, taxes, etc', 'we-the-people' ) ); ?>" />
      </div><!-- #tab-basic -->

      <div id="tab-advanced" class="tab">
        <h3><?php _e( 'Advanced settings', 'we-the-people' ); ?></h3>
        <textarea name="petition-intro" id="petition-intro" rows="4" cols="40"></textarea>
      </div><!-- #tab-advanced -->
    </div><!-- #flipper -->

    <input id="insert" type="submit" class="button-primary" value="<?php echo esc_attr( __( 'Insert', 'we-the-people' ) ); ?>" />
    <input name="cancel" id="cancel" type="button" value="<?php echo esc_attr( __( 'Cancel', 'we-the-people' ) ); ?>" onclick="tinyMCEPopup.close();" />
  </form>

<script type="text/javascript">

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
  }

  var wethepeople = {
    editor: null,
    init: function ( ed ) {
      editor: ed,
      tinyMCEPopup.resizeToInnerSize();
    },
    insert: function insertPetition() {
      var content = jQuery( '#petition-intro' ).val(),
      shortcode_atts = {
        id: jQuery( '#petition-id' ).val()
      },
      shortcode = '[petition',
      prop;

      // Iterate through our shortcode attributes
      for ( prop in shortcode_atts ) {
        if ( shortcode_atts.hasOwnProperty( prop ) ) {
          shortcode += ' ' + prop + '="' + shortcode_atts[ prop ] + '"';
        }
      }

      // The presence of #petition-intro content will determine how we close this
      shortcode += ']' + ( content ? content + '[/petition]' : '' );

      tinyMCEPopup.execCommand( 'mceReplaceContent', false, shortcode );
      tinyMCEPopup.close();
    }
  };

  // Setup our wethepeople object
  tinyMCEPopup.onInit.add( wethepeople.init, wethepeople );

  // This will let us actually use jQuery event listeners
  tinyMCEPopup.executeOnLoad( 'wethepeople_init()' );

</script>
</body>
</html>