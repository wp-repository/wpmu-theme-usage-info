=== WPMU Theme Info ===
Contributors: DeannaS, kgraeme, MadtownLems
Tags: WPMU, Wordpress Mu, Wordpress Multiuser, Theme Stats 
Requires at least: 3.0
Tested up to: 3.1.1
Stable tag: trunk



Provides info to site admins and users on popularity of themes. 

== Description ==
Included files:

* cets\_theme\_info.php
* cets\_theme\_info folder - lib folder - tablesort.js

WPMU has two ways to activate themes either sitewide, or on a blog-by-blog basis. But, there's no convenient way built-in to know which themes are actually being used, or by whom. This plugin addresses that issue by creating a "Theme Usage Info" sub-menu of the Site Admin menu. Included on the page are two tables of data - one of themes currently being used, and one of themes not currently being used. The currently used themes table provides information on how many blogs are using the theme, which blogs are using it, and whether or not the theme is currently activated site-wide. The table of unused themes provides information on whether the theme is currently activated sitewide.

In addition, site admins can choose to provide this information to their users via a toggle on the administration page.

If enabled, users will be able to view data on theme usage in Appearance -> themes for every theme except the currently activated theme. A single line of text is added just before the activate link indicating how many blogs are currently using the theme. When clicked, a scrolling list of themes is displayed in a thickbox:

Thanks go out to <a href="http://wpmututorials.com/plugins/wordpress-mu-theme-stats/">Ron and Andrea</a> for their prior work in this area.


== Installation ==

1. Place the cets\_theme\_info.php file and directory in the wp-content/mu-plugins folder.
1. Go to site admin -> Theme Usage Info to view information and configure user access.

== Screenshots ==

1. Adminstrator view of list of used and unused themes.
2. Administrator view of list toggle controls for user access.
3. User view of theme popularity information.
4. User view of blogs using theme.

== Frequently Asked Questions ==


== Changelog ==
1.1 Adding Show/Hide blogs on the administrative page.
