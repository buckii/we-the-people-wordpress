![We The People](assets/banner-1544x500.png)
# We The People WordPress Plugin

Easily embed White House petitions from We The People into your WordPress site via shortcodes and widgets.

## Using the plugin

### Shortcodes

`[petition id="123"]` or `[petition id="123"]Lorem ipsum sit dolor[/petition]`

#### Shortcode attributes

* `id` (str, required) The We The People petition ID

* `cta` (int) Show the "Sign this petition" call-to-action? (1 = show, 0 = hide)

### Widget

### `$we_the_people` global variable (advanced)

If you're a developer and need more complete access to the We The People API you can use the `api()` method available through the `$we_the_people` global variable. The `api()` method accepts two arguments: the API method to call ('retrieve' or 'index' in version 1.0) and an array of arguments to pass to the API.

#### Examples

##### Retrieving a single petition with ID '123abc'

    // equivalent to https://api.whitehouse.gov/v1/petitions/123abc.json
    $petition = $we_the_people->api( 'retrieve', array( 'id' => '123abc' ) );

##### Searching for open petitions with "war" in the title

    // equivalent to https://api.whitehouse.gov/v1/petitions.json?title=war&status=open
    $petition = $we_the_people->api( 'index', array( 'title' => 'war', 'status' => 'open' ) );

Full API documentation is available [on the We The People API page](https://petitions.whitehouse.gov/developers).

## Styling the petition display

We The People has a built-in petition template but makes it easy to override in your theme. It may be easiest to copy templates/wtp-petition.php into your theme to get started but a very basic custom template might look something like this:

    <div id="wtp-petition-<?php echo $petition->id; ?>" class="wtp-petition">
      <h2 class="petition-title"><?php echo $petition->title; ?></h2>
      <p>
        <strong><?php printf( __( '%d signatures.', 'my-theme' ), $petition->signaturesNeeded ); ?></strong>
        <a href="<?php echo $petition->url; ?>" title="<?php echo esc_attr( __( 'Sign this petition on We The People', 'we-the-people' ) ); ?>" class="sign-btn" rel="external"><?php _e( 'Sign this petition' ); ?></a>
      </p>
    </div><!-- #wtp-petition-<?php echo $petition->id; ?> -->

We The People uses the following order to determine which template to use when displaying a petition:

1. wtp-petition-{id}.php (child theme)
2. wtp-petition.php (child theme)
3. wtp-petition-{id}.php (parent theme)
4. wtp-petition.php (parent theme)
5. templates/wtp-petition.php (plugin)

## Actions and Filters

The We The People WordPress plugin has been built with a number of actions and filters to let you easily control the default petition display without having to build your own.

If you're new to WordPress actions and/or filters it's highly recommended that you [read the documentation on these topics in the WordPress Codex](http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters).

#### Filters

##### `wethepeople_petition_body`

This filter is run on the actual body of the We The People petition before display.

###### Arguments

* `$content` (str) The body of the petition

###### Return value

String (a modified version of `$content`).

###### Example:

    function insert_preamble( $content ) {
      return '<p class="preamble">We petition the Obama Administration to&hellip;</p>' . $content;
    }
    add_filter( 'wethepeople_petition_body', 'insert_preamble' );

## Frequently Asked Questions

### How do I prevent We The People from loading the bundled scripts and styles?

The We The People stylesheet and JavaScript file are enqueued in typical WordPress fashion when the plugin is loaded on WordPress `init`. Dropping this function in your WordPress theme should prevent the default We The People assets from loading:

    /**
     * Prevent We The People from loading scripts and styles
     * @uses wp_dequeue_script()
     * @uses wp_dequeue_style()
     */
    function mytheme_disable_wtp_scripts_styles() {
      wp_dequeue_script( 'we-the-people' );
      wp_dequeue_style( 'we-the-people' );
    }
    add_action( 'init', 'mytheme_disable_wtp_scripts_styles' );

## Roadmap/To-do

### Version 1.0

* A flexible templating system
* Better error handling
* Documentation (this doc + WordPress.org README)
* ~~An intuitive way to add shortcodes to the WordPress editor~~
* ~~Caching via the [WordPress Transients API](http://codex.wordpress.org/Transients_API)~~

### Version 2.0

* Once the write API becomes available make it easy for visitors to sign a petition

## Special Thanks

* [Tony Todoroff](http://www.georgetodoroff.com/) for the WordPress.org banners