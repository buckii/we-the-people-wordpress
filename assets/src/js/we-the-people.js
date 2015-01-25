/**
 * Scripts for the We The People plugin
 *
 * @package We The People
 * @author Buckeye Interactive
 */
/* jslint browser: true, white: true */
/* global jQuery: true, WeThePeople: true */

jQuery( function ( $ ) {
  "use strict";

  var body = $('body');

  /** Truncate an embedded petition to the first paragraph and hide the others behind an a.more */
  body.find( '.wtp-petition blockquote' ).each( function () {
    var self = $(this);
    if ( self.find( 'p' ).length > 1 ) {
      self.find( 'p:not(:first)' ).wrapAll( '<div class="extended" />' );
      self.append( '<p class="toggle-btn"><a href="#" class="toggle more" role="button">' + WeThePeople.i18n.more + '</a></p>' ).addClass( 'collapsed' );
    }
  });

  /** Scripting for the more/less toggle */
  body.on( 'click', '.wtp-petition a.toggle', function ( e ) {
    var self = $(this),
    bq = self.parents( 'blockquote' );
    e.preventDefault();
    bq.find( '.extended' ).slideToggle( 200, function () {
      bq.toggleClass( 'collapsed expanded' );
      self.text( ( self.hasClass( 'more' ) ? WeThePeople.i18n.less : WeThePeople.i18n.more ) ).toggleClass( 'more less' );
    });
    return false;
  });

  /** Ajax-powered signature forms */
  body.on( 'submit', 'form.wtp-petitions-signature', function ( e ) {
    var form = $(this),
    data = form.serializeArray();
    e.preventDefault();

    // Add an additional key that tells our handler we're *actually* Ajax
    data.push( { name: 'actually_ajax', value: true } );

    $.post( WeThePeople.ajaxurl, data, function ( response ) {
      if ( response === WeThePeople.signatureStatus.success ) {
        form.fadeOut( 200, function () {
          form.html( '<div class="wtp-signature-success"><p>' + WeThePeople.i18n.signatureSuccess + '</p></div>' ).fadeIn( 200 );
        });
      } else {
        form.before( '<div class="wtp-signature-error"><p>' + WeThePeople.i18n.signatureError + '</p></div>' );
      }
    });
  });

});