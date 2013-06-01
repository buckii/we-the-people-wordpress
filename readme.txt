=== We The People ===
Contributors: buckeyeinteractive, stevegrunwell
Donate link: http://www.buckeyeinteractive.com/donations
Tags: We The People, petition, democracy, White House, America, Government
Requires at least: 3.5.1
Tested up to: 3.5.1
Stable tag: 1.0
License: GPLv2 or later

Easily embed White House petitions from We The People into your WordPress site via shortcodes and widgets.


== Description ==

In May 2013 the White House released an API for We The People, a petition application designed to give citizens a direct line to the White House. The White House has committed to issue an official, public response to petitions that cross a particular signature threshold.

This plugin allows WordPress site owners to search and embed petitions from We The People into WordPress. Perhaps you're writing an opinion piece on a petition and want real-time statistics on signatures and any response from the White House. Maybe you're a supporter of a particular issue and want to feature it on your sidebar to help promote awareness. The ease of WordPress with the totally customizable templates of the We The People plugin give you the power to share what's important to you.

There are a number of different ways to use the plugin:

**Shortcodes**

The simplest way to get started is through WordPress shortcodes. The syntax is as easy as: `[wtp-petition id="123"]`.

Petitions IDs aren't especially easy to uncover from the We The People site so the We The People plugin includes a TinyMCE button to help you. Clicking the "Insert We The People Petition" button will open an overlay that will let you search the We The People petitions by title to find your issue.

**Widget**

To add a We The People petition to a WordPress dynamic sidebar go to Appearance > Widgets and drag a "WTP Petition" widget into the desired sidebar. Like the TinyMCE button the widget allows you to search for your desired petition by title.

**`$we_the_people` global variable (advanced)**

If you're a developer and need more complete access to the We The People API you can use the `api()` method available through the `$we_the_people` global variable. The `api()` method accepts two arguments: the API method to call ('retrieve' or 'index' in version 1.0) and an array of arguments to pass to the API.

Documentation and code samples for the `$we_the_people->api()` method can be found on [this plugin's Github page](https://github.com/buckii/we-the-people-wordpress).


== Installation ==

1. Upload the '/we-the-people/' plugin directory to '/wp-content/plugins' or install it through the WordPress plugin manager
2. Activate the plugin
3. That's it!

== Frequently Asked Questions ==

= How can I change the templates used to display petitions? =

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

You can find more information regarding template customization (including action hooks and filters) [on the plugin's Github page](https://github.com/buckii/we-the-people-wordpress).

= How do I prevent We The People from loading the bundled scripts and styles? =

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

= Can visitors sign a petition using the plugin? =

At this time the We The People API is read-only, meaning your readers would need to visit https://petitions.whitehouse.gov in order to sign a petition. The White House plans to release a write API sometime in the near future at which point this plugin will be upgraded to enable this capability.


== Changelog ==

= 1.0 =
* First public version of the plugin

== Special Thanks ==

Special thanks goes out to the White House web team for building and opening the We The People API for the general public and for inviting Buckeye Interactive to the National Day of Civic Hacking to premiere this plugin.