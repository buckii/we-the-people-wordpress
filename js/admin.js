/**
 * Admin scripts for the We The People plugin
 *
 * @package We The People
 * @author Buckeye Interactive
 */
/* jslint browser: true, white: true */
/* global jQuery: true, ajaxurl: true */

var WeThePeopleCache = {};

/**
 * Fire off an ajax request for the wtp_petition_search and handle the results
 * @global ajaxurl
 * @param object searchInput A jQuery object representing the search input
 * @param object results A jQuery object to have its .html() set to contain the results
 * @return void
 */
function wethepeople_petition_search( searchInput, results ) {
  "use strict";
  var data = {
    action: 'wtp_petition_search',
    format: 'ul',
    term: searchInput.val()
  };
  searchInput.addClass( 'loading' );

  // Attempt to pull the data from the cache
  if ( WeThePeopleCache[ data.term ] ) {
    results.html( WeThePeopleCache[ data.term ] );
    searchInput.removeClass( 'loading' );

  } else {
    jQuery.post( ajaxurl, data, function ( response ) {
      results.html( response );
      searchInput.removeClass( 'loading' );
      WeThePeopleCache[ data.term ] = response;
    });
  }
  return;
}

jQuery( function ( $ ) {
  "use strict";

  $('body').on( 'keyup', 'input.wtp-petition-search', function () {
    var self = $(this),
    results = self.parents( 'form' ).find( '.wtp-search-results' );
    if ( self.val().length >= 3 ) {
      wethepeople_petition_search( self, results );
    } else if ( self.val().length === 0 ) {
      results.html( '' );
    }
    return true;
  });

  // Clicking a search result should populate the petition ID input
  $('body').on( 'click', '.wtp-search-results a', function ( e ) {
    var self = $(this);
    e.preventDefault();
    self.parents( 'form' ).find( 'input.wtp-petition-id' ).val( self.data( 'petition-id' ) );
    return false;
  });
});