=== Fit My Sidebar ===
Contributors: Relevad
Tags: widget, widgets, admin, show, hide, sidebar, content, plugin, content, filter, widget logic, widget context
Requires at least: 3.1
Tested up to: 4.1.1
Stable tag: 0.9.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simply hide widgets for specific content lengths.

== Description ==

Allows individualized configuration to show/hide sidebar widgets based on an estimate of content length using images and text. User can configure up to 4 different measurements (short, medium, long, longer) with any desired row length of content. 

Afterward configure each widget in your sidebars between those 4 options or any/all pages which is the default.

Based on Display Widgets by sswells http://strategy11.com/display-widgets/

Requirements:

 * PHP version >= 5.3.0 (Dependent on 5.3 functionality. Plugin will not work without 5.3 or higher)

== Installation ==

1. Upload the 'fit-my-sidebar' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the 4 different categories of content length (short, medium, long, longer)
4. Configure the 4 different content length estimate parameters (chars-per-row, pixels-per-row, rows-for-featured, rows-per-img)
5. Go to the 'Widgets' menu and show the options panel for the widget you would like to hide.
6. Select any of the content length parameters from the dropdown.


== Frequently Asked Questions ==

= Why aren't the options showing up on my widget? =

This is a known limitation. Widgets written in the pre-2.8 format don't work the same way, and don't have the hooks. Sorry.

= My widgets aren't showing when I activate =

With some plugins and themes, you may need to adjust when the widget checking starts. You can add this to your theme functions.php or a new plugin.

add_filter('fms_callback_trigger', 'fms_callback_trigger');
function fms_callback_trigger(){
    return 'wp_head'; //change to: plugins_loaded, after_setup_theme, wp_loaded, wp_head, or a hook of your choice
}

= This is hard to configure =

Try using the debug shortcode [fitmysidebar-debug] on a page, that should make it easier to tweak toward the best looking  values.

= Why can't I use this on my home page or categories page? =

This widget is specifically made to deal with content length as it varies from page to page. If you want specific lengths on specific categories or templates then please use Display Widgets by sswells http://strategy11.com/display-widgets/

= Something's not working or I found a bug. What do I do? =

First, please make sure that all Relevad Plugins (including Fit-My-Sidebar) are updated to the latest version.
If updating does not resolve your issue please contact plugins AT relevad DOT com
or
find this plugin on wordpress.org and contact us through the support tab.

== Screenshots ==

1. Admin Config page
2. Config (Debug) assist pannel
3. The extra widget options added.

== Changelog ==

= 0.9.1 =

* Added warning message and prevented activation of plugin for versions < php 5.3.0

= 0.9 =
* Initial release

