=== We The People ===
Contributors: BuckeyeInteractive, stevegrunwell
Donate link: http://www.buckeyeinteractive.com/donations
Tags: We The People, petition, democracy, White House, America, Government
Requires at least: 3.5.1
Tested up to: 4.1
Stable tag: 2.0
License: GPLv2 or later

Easily embed White House petitions from We The People into your WordPress site via shortcodes and widgets.


== Description ==

In May 2013 the White House released an API for We The People, a petition application designed to give citizens a direct line to the White House. The White House has committed to issue an official, public response to petitions that cross a particular signature threshold.

This plugin allows WordPress site owners to search and embed petitions from We The People into WordPress. Perhaps you're writing an opinion piece on a petition and want real-time statistics on signatures and any response from the White House. Maybe you're a supporter of a particular issue and want to feature it on your sidebar to help promote awareness. The ease of WordPress with the totally customizable templates of the We The People plugin give you the power to share what's important to you.

= Special thanks =

Special thanks goes out to the White House web team for building and opening the We The People API for the general public and for inviting Buckeye Interactive to the National Day of Civic Hacking to premiere this plugin.


== Installation ==

1. Upload the '/we-the-people/' plugin directory to '/wp-content/plugins' or install it through the WordPress plugin manager
2. Activate the plugin
3. That's it!

= API keys =

Version 2.0 of the plugin introduced the ability to sign petitions via the We The People Write API. In order to enable this feature within the plugin, it's necessary to [acquire an API key from We The People](http://www.whitehouse.gov/webform/apply-access-we-people-write-api), then enter in the We The People WordPress settings page (Settings > We The People). Alternatively, you may add the API key to your wp-config.php file by adding the following code above the "stop editing" comment:

    // We The People API
    define('WTP_API_KEY', 'your-api-key');

**Note:** If you go the wp-config.php route, the plugin settings page will disappear as the API key is the only plugin option at this time.


== Usage ==

The plugin includes a number of different ways to embed petitions from We The People:

= Shortcodes =

The simplest way to get started is through WordPress shortcodes. The syntax is as easy as: `[wtp-petition id="123"]`.

Petitions IDs aren't especially easy to uncover from the We The People site so the We The People plugin includes a TinyMCE button to help you. Clicking the !["Insert We The People Petition"](assets/img/insert-petition.png) button will open an overlay that will let you search the We The People petitions by title to find your issue. You may also limit your search results to open petitions.

= Widget =

To add a We The People petition to a WordPress dynamic sidebar go to Appearance > Widgets and drag a "WTP Petition" widget into the desired sidebar. Like the TinyMCE button the widget allows you to search for your desired petition by title and show only open petitions.

= $GLOBALS['we-the-people'] global variable (advanced) =

If you're a developer and need more complete access to the We The People API you can use the `api()` method available through the `$GLOBALS['we-the-people']` global variable. The `api()` method accepts two arguments: the API method to call ('retrieve' or 'index' in version 1.*) and an array of arguments to pass to the API.

**Note:** Before version 2.0, the plugin used a global `$we_the_people` variable. If you did customizations to We The People templates you'll want to be sure to update this reference.

Full API documentation is available [on the We The People API page](https://petitions.whitehouse.gov/developers). More developer-oriented documentation can be found in [this plugin's GitHub repository](https://github.com/buckii/we-the-people-wordpress).

= Styling the petition display =

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


== Frequently Asked Questions ==

= How do I prevent We The People from loading the bundled scripts and styles? =

The We The People stylesheet and JavaScript file are enqueued in typical WordPress fashion when the plugin is loaded on WordPress `init`. Dropping this function in your WordPress theme should prevent the default We The People assets from loading:

    /**
     * Prevent We The People from loading scripts and styles.
     */
    function mytheme_disable_wtp_scripts_styles() {
      wp_dequeue_script( 'we-the-people' );
      wp_dequeue_style( 'we-the-people' );
    }
    add_action( 'init', 'mytheme_disable_wtp_scripts_styles' );

Since version 2.0, there are also two filters that can also be used to disable the scripts/styles:

    add_filter( 'wethepeople_load_scripts', '__return_false' );
    add_filter( 'wethepeople_load_styles', '__return_false' );

= Can visitors sign a petition using the plugin? =

Version 2.0 of the plugin uses the new Write API to enable you to collect signatures on We The People petitions. In order to activate this functionality, you'll need to [apply for a We The People API key](#) and add it to WordPress either through the plugin settings page (Settings > We The People) or in your wp-config.php file (see ["API Keys"](#api-keys) for more information).

= I just upgraded and am getting errors about a $we_the_people variable being undefined =

Version 2.0 of the plugin replaced the global `$we_the_people` variable with the cleaner `$GLOBALS['we-the-people']`. You can safely replace instances of the former with the latter, though it's unnecessary to have a `global $GLOBALS['we-the-people']` declaration in your theme. If this change affects you, a simple (temporary) fix would be to add the following to your theme's functions.php file:

    // Alias $we_the_people to $GLOBALS['we-the-people']
    global $we_the_people;
    $we_the_people = $GLOBALS['we-the-people']


== Upgrade Notice ==

= 2.0 =
* Enable your visitors to sign petitions without leaving your site!


== Changelog ==

= 2.0 =
* Leverage the new write API, enabling sites with a valid API key to sign petitions
* **Breaking change:** Removed references to global `$we_the_people` variable, opting instead for `$GLOBALS['we-the-people']`
* Better compatibility with WordPress 3.9+ and the TinyMCE changes it brought with it
* Added ability to limit petition search results to open petitions
* Added `wethepeople_load_scripts` and `wethepeople_load_styles` filters to easily stop We The People from loading assets in custom themes.
* WTP will now load additional stylesheets to fix display issues with TwentyTwelve, TwentyThirteen, TwentyFourteen, and TwentyFifteen.

= 1.1 =
* Make sure the petitions won't be totally broken in the event that We The People becomes unavailable

= 1.0 =
* First public version of the plugin