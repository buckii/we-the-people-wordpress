![We The People](assets/banner-1544x500.png)
# We The People WordPress Plugin

In May 2013 the White House released an API for We The People, a petition application designed to give citizens a direct line to the White House. The White House has committed to issue an official, public response to petitions that cross a particular signature threshold.

This plugin allows WordPress site owners to search and embed petitions from We The People into WordPress. Perhaps you're writing an opinion piece on a petition and want real-time statistics on signatures and any response from the White House. Maybe you're a supporter of a particular issue and want to feature it on your sidebar to help promote awareness. The ease of WordPress with the totally customizable templates of the We The People plugin give you the power to share what's important to you.

## Using the plugin

### Shortcodes

The simplest way to get started is through WordPress shortcodes. The syntax is as easy as: `[wtp-petition id="123"]`.

Petitions IDs aren't especially easy to uncover from the We The People site so the We The People plugin includes a TinyMCE button to help you. Clicking the ["Insert We The People Petition" button](js/tinymce/insert-petition.png) will open an overlay that will let you search the We The People petitions by title to find your issue.

### Widget

To add a We The People petition to a WordPress dynamic sidebar go to Appearance > Widgets and drag a "WTP Petition" widget into the desired sidebar. Like the TinyMCE button the widget allows you to search for your desired petition by title.

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

**Shortcodes:**

1. wtp-petition-{id}.php (child theme)
2. wtp-petition.php (child theme)
3. wtp-petition-{id}.php (parent theme)
4. wtp-petition.php (parent theme)
5. templates/wtp-petition.php (plugin)

**Widgets:**

1. wtp-petition-widget-{id}.php (child theme)
2. wtp-petition-widget.php (child theme)
3. wtp-petition-widget-{id}.php (parent theme)
4. wtp-petition-widget.php (parent theme)
5. templates/wtp-petition-widget.php (plugin)

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

###### Example

    function insert_preamble( $content ) {
      return '<p class="preamble">We petition the Obama Administration to&hellip;</p>' . $content;
    }
    add_filter( 'wethepeople_petition_body', 'insert_preamble' );

##### `wethepeople_shortcode_name`

The plugin has intentionally avoided a `[petition]` shortcode in favor of `[wtp-shortcode]` to reduce the risk of conflicting with other `[petition]` shortcodes that may be registered in another plugin or theme. Returning a string at this filter will allow you to override the default shortcode name of `wtp-petition`.

**Note:** This will not update existing post content - if you change the shortcode name and find yourself with a bunch of `[wtp-petition]` shortcodes appearing throughout the site these will need to be manually updated to match the new shortcode name. In extreme cases you may just want to double-register the shortcode ( `add_shortcode( 'my-petition-shortcode-name', array( 'WeThePeople_Plugin', 'petition_shortcode' ) );`) and forgo the `wethepeople_shortcode_name` filter.

###### Arguments

* `$shortcode_name` (str) The current shortcode name ('wtp-petition')

###### Return value

String (the desired shortcode name)

###### Example

    /**
     * Change the [wtp-petition] shortcode to something easier to remember like [petition]
     * @return str
     */
    function change_wtp_petition_shortcode_name() {
      return 'petition';
    }
    add_filter( 'wethepeople_shortcode_name', 'change_wtp_petition_shortcode_name' );

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

### Can visitors sign a petition using the plugin?

At this time the We The People API is read-only, meaning your readers would need to visit https://petitions.whitehouse.gov in order to sign a petition. The White House plans to release a write API sometime in the near future at which point this plugin will be upgraded to enable this capability.

## Changelog

### Version 1.0

* First public release of the plugin, coordinated with the National Day of Civic Hacking at the White House on June 1, 2013.

## Roadmap/To-do

### Version 2.0

* Once the write API becomes available make it easy for visitors to sign a petition

## Special Thanks

* The White House for putting together the We The People API and inviting Buckeye Interactive to participate in the National Day of Civic Hacking.
* [Tony Todoroff](http://www.georgetodoroff.com/) for the WordPress.org banners and TinyMCE icon.