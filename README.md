# WPMU Theme Info [![Build Status](https://travis-ci.org/wp-repository/wpmu-theme-usage-info.png?branch=master)](https://travis-ci.org/wp-repository/wpmu-theme-usage-info)
__Provides info to network admins and users on the popularity of themes.__

## Details
[Homepage][1.1] | [WordPress.org][1.2]

| WordPress					| Version			| *		| Development				|					|
| ----:						| :----				| :---: | :----						| :----				|
| Requires at least:		| __3.4__			| *		| [GitHub-Repository][1.3]	| [Translate][1.7]	|
| Tested up to:				| __3.5.1__			| *		| [Issue-Tracker][1.4]		| [WordPress.org-SVN][1.6] |
| Current stable release:	| __[1.9][1.5]__	| *		| Current dev version:		| [2.0-dev][1.8]	|

[1.1]: https://github.com/wp-repository/wpmu-theme-usage-info
[1.2]: http://wordpress.org/extend/plugins/wpmu-theme-usage-info/
[1.3]: https://github.com/wp-repository/wpmu-theme-usage-info
[1.4]: https://github.com/wp-repository/wpmu-theme-usage-info/issues
[1.5]: https://github.com/wp-repository/wpmu-theme-usage-info/archive/1.9.zip
[1.6]: http://plugins.trac.wordpress.org/browser/wpmu-theme-usage-info/
[1.7]: https://translate.foe-services.de/projects/cets-theme-info
[1.8]: https://github.com/wp-repository/wpmu-theme-usage-info/archive/master.zip

### Description
WordPress Multisite has two ways to activate themes either sitewide, or on a blog-by-blog basis. But, there's no convenient way built-in to know which 
themes are actually being used, or by whom. This plugin addresses that issue by creating a "Theme Usage Info" sub-menu of the Network Admin theme menu. 
Included on the page are two tables of data - one of themes currently being used, and one of themes not currently being used. The currently used themes 
table provides information on how many blogs are using the theme, which blogs are using it, and whether or not the theme is currently activated site-wide. 
The table of unused themes provides information on whether the theme is currently activated sitewide.

In addition, network admins can choose to provide this information to their users via a toggle on the administration page.

If enabled, users will be able to view data on theme usage in Appearance -> themes for every theme except the currently activated theme. A single line 
of text is added just before the activate link indicating how many blogs are currently using the theme. When clicked, a scrolling list of themes is displayed in a thickbox:

Thanks go out to <a href="http://wpmututorials.com/plugins/wordpress-mu-theme-stats/">Ron and Andrea</a> for their prior work in this area.


## Development
### Developers
| Name					| GitHub				| WordPress.org			| Web									| Status				|
| :----					| :----					| :----					| :----									| ----:					|
| Kevin Graeme			| -						| [kgraeme][2.1.2]		| -										| Inactive				|
| Deanna Schneider		| -						| [deannas][2.2.2]		| http://deannaschneider.wordpress.com/ | Inactive				|
| Jason Lemahieu		| [MadtownLems][2.3.1]	| [MadtownLems][2.3.2]	| http://www.jasonlemahieu.com/			| Inactive				|
| Christian Foellmann	| [cfoellmann][2.4.1]	| [cfoellmann][2.4.2]	| http://www.foe-services.de			| Current maintainer	|

[2.1.2]: http://profiles.wordpress.org/kgraeme/
[2.2.2]: http://profiles.wordpress.org/DeannaS/
[2.3.1]: https://github.com/MadtownLems
[2.3.2]: http://profiles.wordpress.org/MadtownLems/
[2.4.1]: https://github.com/cfoellmann
[2.4.2]: http://profiles.wordpress.org/cfoellmann


## License
__[GPLv2 or later](http://www.gnu.org/licenses/gpl-2.0.html)__

	WPMU Theme Info

	Copyright (C) 2009 - 2013 Board of Regents of the University of Wisconsin System
	Cooperative Extension Technology Services
	University of Wisconsin-Extension

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


## Changelog
* __2.0-dev__ _[future plans/roadmap][4.1]_
	* added build testing via travis-ci.org
	* added custom unit tests @TODO
	* added Spanish translation by Eduardo Larequi (https://github.com/elarequi)
	* TBD
* __1.9__
	* moved development to GitHub
	* full translation support
	* German language support
	* UI polished with tabs and functioning table-sorting
* __1.8__
	* Added a check for making sure the Theme Files are present, and will display a message if there is a site using a theme that no longer exists
* __1.7__
	* Updated for 3.4. Because of the massive Theme info API change with 3.4, this plugin now REQURES 3.4* to function
* __1.2 - 1.6__
	* Unkown exactly because I wasn't maintaining this plugin for public use for these versions, but the following occurred in here:
	* Properly enqueue scripts
	* Updated for 3.1.  Then later dropped support for anything below 3.4
	* fixed lots of notices and warnings
	* properly store data after the 3.3 add_site_option changes
* __1.1__
	* Adding Show/Hide blogs on the administrative page.

[4.1]: https://github.com/wp-repository/wpmu-theme-usage-info/issues