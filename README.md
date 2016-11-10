[![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange.svg)](https://raw.githubusercontent.com/BoldGrid/boldgrid-staging/master/LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-5.3%2B-blue.svg)](https://php.net)

# BoldGrid Staging #
**Contributors:** imh_brad, joemoto, rramo012, timph
**Tags:** inspiration,customization,build,create,design,staging
**Requires at least:** 4.3
**Tested up to:** 4.6.1
**License:** GPLv2 or later
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

BoldGrid Staging is a standalone plugin to allow use of a staged website while keeping a live website intact during development.

## Description ##

BoldGrid Staging is a standalone plugin to allow use of a staged website while keeping a live website intact during development.

## Requirements ##

* PHP 5.3 or higher.

## Installation ##

1. Upload the entire boldgrid-staging folder to the /wp-content/plugins/ directory.

2. Activate the plugin through the Plugins menu in WordPress.

## Changelog ##

### 1.3.2 In progress ###
* Bug fix:		JIRA WPB-2507	Hide staging menus when customizing active site.
* Bug fix:		JIRA WPB-2602	Parent pages don't list staging pages.
* New feature:	JIRA WPB-2603	Add initial version of Inspirations survey.

### 1.3.1 ###
* Misc:			JIRA WPB-2503	Added plugin requirements to readme.txt file.
* Update:		JIRA WPB-2563	Convert Attribution page to use custom post type.
* Bug fix:		JIRA WPB-2548	Fix sloppy post name issues when copying to staging (and vice versa).

### 1.3 ###
* Update:						Bump version.

### 1.2.7 ###
*Update:		JIRA WPB-2491	Add 'Customize > Active Theme' navigation to Inspirations.

### 1.2.6 ###
* Bug fix:		JIRA WPB-2437	Fixing issue with compiling staging colors.

### 1.2.5 ###
* Update:		JIRA WPB-2437	Button.css needs to compile after updates and work with staging.
* Bug fix:		JIRA WPB-2424	'Deploy Staging' button appears when clicking on active theme.
* Bug fix:		JIRA WPB-2459	Undefined index error - class-boldgrid-staging-theme.php.

### 1.2.4 ###
* Bug fix:		JIRA WPB-2425	Link to Staging tutorials needs to be removed.

### 1.2.3 ###
* Bug fix:		JIRA WPB-2075	If themes are lazy loaded, staging button does not appear on later themes.

### 1.2.2 ###
* Misc:			JIRA WPB-2344	Updated readme.txt for Tested up to 4.6.1.
* Bug fix:		JIRA WPB-2336	Load BoldGrid settings from the correct WP option (site/blog).
* Update:		JIRA WPB-2378	Set the version constant from plugin file.
* Bug fix:						If a post type isn't found, then don't query.

### 1.2.1 ###
* Misc:			JIRA WPB-2256	Updated readme.txt for Tested up to: 4.6.
* Rework:		JIRA WPB-1825	Formatting.
* Bug fix:		JIRA WPB-2235	Only send staged site promotion to active if was built with Inspirations.

### 1.2 ###
* Bug fix:		JIRA WPB-2124	Customizer's list of pages to add to a menu are always active pages.
* Bug fix:		JIRA WPB-2134	Staging's boldgrid_attribution option and 'Uninitialized string offset' Notice.

### 1.1.3 ###
* New feature:	JIRA WPB-2037	Added capability for auto-updates by BoldGrid API response.
* Testing:		JIRA WPB-2046	Tested on WordPress 4.5.3.

### 1.1.2 ###
* New feature:	JIRA WPB-1865	Add active / staging navigation to BoldGrid Inspirations Cart.
* Update:		JIRA WPB-1884	Passed WordPress 4.5.1 testing.
* Bug fix:		JIRA WPB-1863	BoldGrid Cart does not look for watermarked images used within staged pages.
* Bug fix:		JIRA WPB-1888	Staging options missing from Dashboard > Settings > Reading.
* Bug fix:		JIRA WPB-1887	Copy to Staging link does not have correct cursor.
* Bug fix:		JIRA WPB-1892	Page template not saving correctly for staged pages.
* Bug fix:		JIRA WPB-1898	When no menu is assigned to a location, active pages show in menu on staged site.
* Bug fix:		JIRA WPB-1899	Warnings and Notices thrown when using BoldGrid Inspirations start over.

### 1.1.1 ###
* Bug fix:		JIRA WPB-1834	Delete BoldGrid Staging Attribution page when starting over.

### 1.1.0.1 ###
* Bug fix:		JIRA WPB-1816	Fixed update class interference with the Add Plugins page.

### 1.1 ###
* Bug fix:		JIRA WPB-1809	Fixed undefined index "action" for some scenarios.  Optimized update class and addressed CodeSniffer items.

### 1.0.9 ###
* Misc:			JIRA WPB-1361	Added license file.
* Bug fix:		JIRA WPB-1723	Switching between Staging and Active in the customizer loads wrong content.

### 1.0.8 ###
* Bug fix:		JIRA WPB-1604	Updated some CSS for standards.

### 1.0.7 ###
* Bug fix:		JIRA WPB-1634	Now does not enable staging theme on install; use the enable link.
* Rework:		JIRA WPB-1620	Updated require and include statements for standards.
* New feature:	JIRA WPB-1538	Added a way to set staging site's homepage.

### 1.0.6 ###
* New feature:	JIRA WPB-1580	Added feedback when deploying a staged site.
* New feature:	JIRA WPB-1572	Added staging switch for static gridblocks

### 1.0.5.1 ###
* Bug fix:		JIRA WPB-1553	Fixed PHP version check condition (<5.3).

### 1.0.5 ###
* Bug fix:		JIRA WPB-1553	Added support for __DIR__ in PHP <=5.2.
* New feature	JIRA WPB-1542	Manage menu assignment within editor.
* Misc:			JIRA WPB-1468	Updated readme.txt for Tested up to: 4.4.1

### 1.0.4 ###
* Bug fix:		JIRA WPB-1426	When installing into staging, with active site, active widgets are overwritten..
* Bug fix:		JIRA WPB-1452	Upon deploying staging, new active sites lost Social Media icons.
* Bug fix:		JIRA WPB-1450	Staging pages show in search results when searing on the front end.

### 1.0.3 ###
* Bug fix:		JIRA WPB-1422	Customize links showing in wrong menu...
* New feature	JIRA WPB-1439	Tie into Theme Framework's filter for creating attribution page link and fix it for Staging.
* Bug fix:		JIRA WPB-1428   Live Preview of staged theme throws JS error.
* Bug fix:		JIRA WPB-1446	Properly rename a staged page when it ends with '-staging-(a number)'

### 1.0.2 ###
* New feature:	JIRA WPB-1363	Updated readme.txt for WordPress standards.
* Bug Fix:		JIRA WPB-1396	Call to action disappears when customizing staging.
* Bug Fix:		JIRA WPB-1389	Setting Launched staging theme mods to allow theme to trigger activation

### 1.0.1 ###
* Rework:						Created class Boldgrid_Staging_Dashboard_Menus and reorganized related code.
* Bug Fix:		JIRA WPB-1383   Fixing issue that prevented color palettes from updating on staging deploy.

### 1.0 ###
* Initial public release.

## Upgrade Notice ##

### 1.0.1 ###
Users should upgrade to version 1.0.1 to receive a fix for color palettes in staging deployment.
