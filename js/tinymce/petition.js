/**
 * "Insert Petition" TinyMCE plugin
 *
 * @package We The People
 * @author Buckeye Interactive
 */
/* jslint browser: true, white: true */
/* global tinymce: true */

( function () {
  "use strict";

  tinymce.create( 'tinymce.plugins.wethepeople', {

    /**
     * Initialize the TinyMCE plugin
     * @param object ed The TinyMCE editor instance
     * @param str url The URL of this directory
     * @return void
     */
    init : function( ed, url ) {
      // Register our weThePeopleDialog command (which we'll trigger with the button later)
      ed.addCommand( 'weThePeopleDialog', function () {
        ed.windowManager.open({
          file: url + '/petition.php',
          width: 500,
          height: 430,
          inline: true
        });
      });

      // Add the button to TinyMCE
      ed.addButton( 'wethepeople', {
        cmd: 'weThePeopleDialog',
        title : 'Insert We The People petition',
        image : url + '/insert-petition.png'
      });
    },

    /**
     * Set the plugin information
     * @return object
     */
    getInfo : function() {
      return {
        longname : 'We The People Petition',
        author : 'Buckeye Interactive',
        authorurl : 'http://www.buckeyeinteractive.com',
        infourl : 'http://wordpress.org/plugins/we-the-people/',
        version : '1.0'
      };
    }

  });

  // Instantiate our TinyMCE plugin
  tinymce.PluginManager.add( 'wethepeople', tinymce.plugins.wethepeople );
}());