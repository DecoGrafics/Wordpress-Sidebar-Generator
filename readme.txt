=== Plugin Name ===
Contributors: _smartik_
Donate link: https://paypal.me/zerowp
Tags: sidebar, widget, generator, custom, unlimited, sidebars, widgets, conditions, replace, manage, smk, smk sidebar generator
Requires at least: 4.7
Tested up to: 4.8
Stable tag: 4.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Generate an unlimited number of sidebars and assign them to any page, using the conditional options, without touching a single line of code.

== Description ==
This plugin generates as many sidebars as you need. Then allows you to place them on any page you wish. "Sidebar Generator and Manager by ZeroWP", formerly known as "SMK Sidebar Generator", is a new version that comes with many improvements. The version 4.x includes bug fixes and a lot more options and freedom. Now it's possible to assign a sidebar to any page(cpt, taxonomies, 404 page, search result, etc.). Also, developers have the option to extend the plugin using the new filter and action hooks. Enjoy this plugin that it's free forever.

#### Source:
* Development branch: https://github.com/ZeroWP/Wordpress-Sidebar-Generator
* Issue tracker: https://github.com/ZeroWP/Wordpress-Sidebar-Generator/issues

#### Features:
* Create as many sidebars as you want. Unlimited, really!
* Replace static sidebars that are defined in current theme or other plugins without touching a single line of code.
* Use conditions to replace the static sidebars. Using conditions gives you the possibility to decide exactly how the replacements occurs.
* Drag to sort sidebar order and conditions.
* Possibility to show the generated sidebars using a simple shortcode.
* Possibility to show the generated sidebars using the WP function `dynamic_sidebar` or its alias 'smk_sidebar`.
* Lightweight. It does not inject any scripts or styles on front end and most of the code is loaded only in back-end when needed.

== Installation ==
1. Upload the `smk-sidebar-generator` folder to the `/wp-content/plugins/` directory
2. Activate the "Sidebar Generator and Manager" plugin through the 'Plugins' menu in WordPress
3. Configure the plugin from "Sidebars" page using the link from admin menu on the left side.

== Screenshots ==
1. Admin panel

== Changelog ==
= 4.0 =
* New: Introduced a new, improved UI.
* Bug fix: Incorect shortcode display. Added square brackets and the missing `id=""` attribute.
* Bug fix: PHP 5.3 compatibility issue.
* Improvement: "Add new" button has been moved down right before the sidebars list.
* Improvement: Added a notice where the sidebars list is empty.
* Improvement: Implemented a tooltip plugin to provide help tips easier.
* Improvement: Implemented select2 plugin. Now the interface is more user friendly.
* Improvement: The plugin has been moved from a subpage to a top level admin page.

= 3.1 =
* Added localization support(if you want to translate it in your language, create a pull requests on Github).
* Added shortcode with ID to each sidebar.

= 3.0 =
* **Complete rewrite from scratch.** The plugin now allows to create an unlimited number of sidebars without the need to touch a single line of code in your theme.
* Now you can use conditions to apply the sidebar on any page, post ar CPT you wish. _Soon will be added support for taxonomies, 404 page and other(please suggest)_.
* The widgets now use the theme style and tags. That means the newly generated sidebars will look good on any theme, no need for additional styling.
* Modular code. You can create and register your own conditions. That's mainly not required but can be handy for some developers.

= 2.3.2 =
* Quick fix UI. When a new sidebar is created, it display an incorect info and it was fixed only after page refresh.
* Removed unused files, since version 3.0 is on development `smk_sidebar_metabox.php` was removed, as it was never used and is not required for the next versions.

= 2.3.1 =
* Quick fix for shortcode smk_sidebar ID. Shortcode did not work because the ID was not set correctly.
* Added new tab "How to use?" and links to docs.

= 2.3 =
* **Added import/export functions.**
* Changes to `smk_sidebar` shortcode. Previously to get a sidebar required only an integer number, now you can get any sidebar using the shortcode just giving the id, even if the sidebar is not generated using Sidebar Generator plugin.
* Added plugin version to enqueued scripts and style.

= 2.2 =
* Confirm sidebar remove.
* Bug fix: Sidebars could not be added when all previous sidebars were removed.
* Bug fix: Fixed ajax name validation.

= 2.1.1 =
* enqueue styles and scripts only on plugin page, not on all WP dashboard pages.
* `admin_enqueue_scripts` make use of `SMK_SBG_URI` constant.

= 2.1 =
* `smk_get_all_sidebars()` function is included in plugin. Anyways, you must include it in your theme `functions.php`, because if you'll deactivate the plugin it will return a fatal error.

= 2.0 = 
* Initial release

