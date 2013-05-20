/**
 * "Insert Petition" TinyMCE plugin
 *
 * @package We The People
 * @author Buckeye Interactive
 */

( function () {
  tinymce.create('tinymce.plugins.wethepeople', {
    init : function( ed, url ) {

      ed.addCommand( 'weThePeopleDialog', function () {
        ed.windowManager.open({
          file: url + '/petition.php',
          width: 600,
          height: 500,
          inline: true
        });
      });

      ed.addButton( 'wethepeople', {
        cmd: 'weThePeopleDialog',
        title : 'We The People Petition',
        image : url + '/img/tinymce-icon.png'
      });
    },

    /**
     * Set the plugin information
     * @todo Put the plugin URL in place for infourl
     */
    getInfo : function() {
      return {
        longname : 'We The People Petition',
        author : 'Buckeye Interactive',
        authorurl : 'http://www.buckeyeinteractive.com',
        infourl : 'http://www.buckeyeinteractive.com',
        version : '1.0'
      };
    }

  });

  tinymce.PluginManager.add( 'wethepeople', tinymce.plugins.wethepeople );
})();