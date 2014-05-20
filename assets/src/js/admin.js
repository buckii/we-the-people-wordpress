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
 *
 * @global ajaxurl
 * @param object searchInput A jQuery object representing the search input
 * @param object results A jQuery object to have its .html() set to contain the results
 * @param object args Additional arguments to pass
 * @return void
 */
function wethepeople_petition_search( searchInput, results, args ) {
  "use strict";
  var data = {
    action: 'wtp_petition_search',
    format: 'ul',
    term: searchInput.val()
  },
  key;

  // Merge additional arguments
  if ( typeof args === 'object' ) {
    jQuery.extend( data, args );
  }

  searchInput.addClass( 'loading' );

  // Build our cache key
  key = data.term + ( data.hasOwnProperty( 'activeOnly' ) ? 'ActiveOnly=' + data.activeOnly : '' );

  // Attempt to pull the data from the cache
  if ( WeThePeopleCache[ key] ) {
    results.html( WeThePeopleCache[ key ] );
    searchInput.removeClass( 'loading' );

  } else {
    jQuery.post( ajaxurl, data, function ( response ) {
      results.html( response );
      searchInput.removeClass( 'loading' );
      WeThePeopleCache[ key ] = response;
    });
  }
  return;
}

jQuery( function ( $ ) {
  "use strict";

  $('body').on( 'keyup', 'input.wtp-petition-search', function () {
    var self = $(this),
    results = self.parents( 'form' ).find( '.wtp-search-results' ),
    activeOnly = self.parents( 'form' ).find( 'input[name="petition-search-only-active"]' ),
    args = {
      // $.extend won't preserve booleans, so we'll use something that PHP will be able to work with even after mutation
      activeOnly: ( activeOnly.length === 1 && activeOnly.prop( 'checked' ) ? 1 : 0 )
    };

    if ( self.val().length >= 3 ) {
      wethepeople_petition_search( self, results, args );
    } else if ( self.val().length === 0 ) {
      results.html( '' );
    }
    return true;
  });
  $('body').on( 'change', 'input[name="petition-search-only-active"]', function () {
    $(this).parents( 'form' ).find( 'input.wtp-petition-search' ).trigger( 'keyup' );
  });

  // Clicking a search result should populate the petition ID input
  $('body').on( 'click', '.wtp-search-results a', function ( e ) {
    var self = $(this);
    e.preventDefault();
    self.parents( 'form' ).find( 'input.wtp-petition-id' ).val( self.data( 'petition-id' ) );
    return false;
  });
});