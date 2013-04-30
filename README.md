My-Shortcodes
=============

WordPress plugin that allows you to add custom code as a shortcode to be used within your pages or posts.

=== My Shortcodes ===
Contributors: Desertsnowman
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=PA8U68HRBXTEU&lc=ZA&item_name=my-shortcodes&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted

Tags: shortcode, My Shortcodes, shortcode builder, custom shortcode, custom code, widgets, custom widget, caldera engine lite, caldera, caldera engine
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to add custom code as a shortcode to be used within your pages or posts.

== Description ==

Build custom shortcode elements or download and install shortcodes made by other My Shortcodes users.

Highly flexible shortcode builder environment. dedicated areas for template view, javascript input, custom PHP library, external/CDN css and javascript sources.
This enables you to render the page or posts with the requires scripts and styles to be placed where it belongs. not all in the shortcode replace area.

IMPORTANT: version 2 is not compatible with 1.9.2 exported .CE files. If you have exported and saved .ce shortcodes. Please import them before upgrading. once upgraded, the shortcodes will be converted. you can then export the converted elements to .MSC files for safe keeping.

[Documentation](http://myshortcodes.cramer.co.za/documentation/) * please not these are outdated, but will be improved in the coming weeks.

== Installation ==

1. Upload `my-shortcodes` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the My Shortcodes menu item and start creating your own shortcodes.

== Screenshots ==

1. You admin panel. Manage, edit, activate & export elements and shortcodes.
2. View a summary of your element for easy reference.
3. Element settings - with live preview.
4. Add external and WordPress provided librries for CSS and Javascript.
5. Clean editor with custom syntax hightlighting on built in tags like [loop] and [if] and your attriburtes in {{att}} tags.
6. Create dedicated widgets, with custom config panels for your attributes.

== Frequently Asked Questions ==

Please ask questions in the support forum.

== Upgrade Notice ==

IMPORTANT: version 2 is not compatible with 1.9.2 exported .CE files. If you have exported and saved .ce shortcodes. Please import them before upgrading. once upgraded, the shortcodes will be converted. you can then export the converted elements to .msc files for safe keeping.

Overwrite the old.
navigate to the plugin in admin
Click upgrade elements

== Changelog ==

= 2.0 =
* Complete rebuild of the core system
* Complete editor overhaul to be inline with the more advanced Caldera Engine (pro version)
* Added dedicated widget element types
* Improved shortcode insert builder
* Dramatic overhaul in the shortcode detection and element insertion on page renders.
* Improved chaching of scripts and codes.

= 1.9.2 =
* Added in a check to see if a cache file actually does exists prior to trying to delete it. sigh. sorry I missed this. But its fixed now.

= 1.9.1 =
* CSS is now saved to a cache file and linked in the headers. Going to let it run for a while to see stability. Once no reported problems, I adding it to JavaScript as well.
* Added a better instanceID. Using the shortcode multiple time on a page or post will each get a unique value for {{_id_}} dynamic tag. Great for giving elements a unique ID.
* Added a check to not include code from the PHP tab multiple times. This should prevent the "Fatal error: Cannot redeclare" PHP error.
* Took the jQuery document.ready() out of the javascript output to remove the jQuery requirement.
* Unfortunately, I could not get Multi-site compatability in yet (not really complex, just a lot of restructuring.), but that is scheduled for the next update. Sorry :(

= 1.9 =
* html is properly escaped before going into the code editor so using <textarea> tags wont break it.

= 1.8 =
* Fixed a bug that prevented CSS and JS from rendering if a page with a shortcode is placed as the home page.

= 1.7 =
* Fixed a bug that messed up utf-8 characters.

= 1.6 =
* Description text for inserting a shortcode without any attributes. changed to sound less like an error.
* Corrected an error that prevented running shortcodes on the home page

= 1.5 =
* Corrected a bug that prevented PHP from rendering on some pages.
* Fixed a bug that allowed prevented the footer scripts from running.

= 1.4 =
* Added correction to the attribute access in CSS and Javascript.

= 1.3 =
* Improved the shortcode detection to load all scripts.
* Added another screenshot that explains the rendering process.
* Added a tab in admin that links to the explain screen.
* Added PHP execution in javascript and CSS tabs. (Yup, dynamic CSS and javascript!).
* Added an con to the admin menu item.

= 1.2 =
* Added an insert shortcode button to the post editor.

= 1.1 =
* Added attribute codes to javascript panel so you can use attributes in output scripts as well.
* Fixed a bug that reverted the external javascript library to be placed in footer on edit.

= 1.0 =
* Initial Release
