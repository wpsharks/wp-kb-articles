=== WP KB Articles ===

Stable tag: 150113
Requires at least: 4.1
Tested up to: 4.2-alpha
Text Domain: wp-kb-articles

License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Contributors: WebSharks, JasWSInc
Donate link: http://www.websharks-inc.com/r/wp-theme-plugin-donation/
Tags: kb article, kb articles, ecommerce, e-commerce, post type, post types, utilities, posts, pages

Create KB Articles! This plugin adds a new Post Type.

== Description ==

This plugin is VERY simple; NO configuration options necessary.

This plugin adds a new Post Type. This plugin makes it SUPER easy to create KB Articles (as a separate Post Type in WordPress). This is a very lightweight plugin (for now). In the future we may add some additional functionality for KB Article integrations w/ other plugins.

After installing this plugin, create a new KB Article (find menu item on the left in your Dashboard). KB Articles are just like any other Post, except they have a different classification so that themes/plugins may identify KB Articles and/or separate them from other Posts.

== Frequently Asked Questions ==

= Who can manage KB Articles in the Dashboard? =

By default, only WordPress® Administrators can manage (i.e. create/edit/delete/manage) KB Articles. Editors and Authors can create/edit/delete their own KB Articles, but permissions are limited for Editors/Authors. If you would like to give other WordPress Roles the Capabilities required, please use a plugin like [Enhanced Capability Manager](http://wordpress.org/extend/plugins/capability-manager-enhanced/).

Add the following Capabilities to the additional Roles that should be allowed to manage KB Articles.

	$caps = array
			(
				'edit_kb_articles',
				'edit_others_kb_articles',
				'edit_published_kb_articles',
				'edit_private_kb_articles',
				'publish_kb_articles',
				'delete_kb_articles',
				'delete_private_kb_articles',
				'delete_published_kb_articles',
				'delete_others_kb_articles',
				'read_private_kb_articles'
			);

NOTE: There are also some WordPress filters integrated into the code for this plugin, which can make permissions easier to deal with in many cases. You can have a look at the source code and determine how to proceed on your own; if you choose this route.

== Installation ==

= WP KB Articles is Very Easy to Install =

1. Upload the `/wp-kb-articles` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress®.
3. Create KB Articles in WordPress® (see: **Dashboard -› KB Articles**).

== License ==

Copyright: © 2013 [WebSharks, Inc.](http://www.websharks-inc.com/bizdev/) (coded in the USA)

Released under the terms of the [GNU General Public License](http://www.gnu.org/licenses/gpl-2.0.html).

= Credits / Additional Acknowledgments =

* Software designed for WordPress®.
	- GPL License <http://codex.wordpress.org/GPL>
	- WordPress® <http://wordpress.org>
* Some JavaScript extensions require jQuery.
	- GPL-Compatible License <http://jquery.org/license>
	- jQuery <http://jquery.com/>
* CSS framework and some JavaScript functionality provided by Bootstrap.
	- GPL-Compatible License <http://getbootstrap.com/getting-started/#license-faqs>
	- Bootstrap <http://getbootstrap.com/>
* Icons provided by Font Awesome.
	- GPL-Compatible License <http://fortawesome.github.io/Font-Awesome/license/>
	- Font Awesome <http://fortawesome.github.io/Font-Awesome/>

== Changelog ==

= v150113 =

* Rewriting with OOP design.
* Bringing plugin up-to-date w/ WordPress 4.1 compat.
* Adding GitHub integration for KB articles sourced from a GitHub repo.
* Adding a new shortcode: `[kb_articles_list /]`.
* Adding new menu page w/ config options in Dashboard.

= v131121 =

* Updating readme file and adding license file.

= v131113 =

* Initial release.