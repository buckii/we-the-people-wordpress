/**
 * Scripts for the We The People plugin
 *
 * @package We The People
 * @author Buckeye Interactive
 */

jQuery( function ( $ ) {
  "use strict";

  /**
   * Truncate an embedded petition to the first paragraph and hide the others behind an a.more
   */
  $('body').find( '.wtp-petition blockquote' ).each( function () {
    var self = $(this);
    self.find( 'p:not(:first)' ).wrapAll( '<div class="extended" />' );
    self.append( '<a href="#" class="toggle more" role="button">' + WeThePeople.i18n.more + '</a>' ).addClass( 'collapsed' );
  });

  /**
   * Scripting for the more/less toggle
   */
  $('body').on( 'click', '.wtp-petition a.toggle', function ( e ) {
    var self = $(this);
    e.preventDefault();
    self.siblings( '.extended' ).slideToggle( 200, function () {
      self.text( ( self.hasClass( 'more' ) ? WeThePeople.i18n.less : WeThePeople.i18n.more ) ).toggleClass( 'more less' );
    });
    return false;
  });

});