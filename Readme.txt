=== Role Based Bulk Quantity Pricing ===
Contributors: kevinamorim
Tags: pricing, csv, roles, woocommerce, bulk
Requires at least: 5.5
Tested up to: 6.4
Requires PHP: 5.6
Stable tag: 1.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use a CSV file to set bulk quantity pricing by roles, for WooCommerce.

== Description ==
This plugin allows to easily upload a CSV file with bulk pricing for simple and variable products.

The plugin dashboard page lists all existing bulk pricing lines and allows to export them to a CSV file. 
For further help while creating the CSV lines you can export both the products list and the available roles list.

Some further customization is also possible by:

* Defining if we want to use roles or completely ignore them.
* Use the total cart quantity or the quantity by specific product on bulk calculations.
* Enable bulk pricing for guest (not logged in) users.
* Display product total on the product page.
* Display an additional label next to the price defining wether it includes taxes or not.
* Hide taxes on the product page for specific roles.
* Debug mode which displays further information.
  
== Installation ==
  
1. Upload the plugin folder to your /wp-content/plugins/ folder.
2. Go to the **Plugins** page and activate the plugin.
  
== Frequently Asked Questions ==
  
= How do I use this plugin? =
  
Navigate to the plugins dashboard to upload a CSV file with the pricing for your products.
Export the CSV file to get a template of the expected format for the CSV file.

Explore the configurations page to customize the plugin to your liking.

I will be releasing more in-depth documentation soon.
  
= How to uninstall the plugin? =
  
Simply deactivate and delete the plugin. 
  
== Screenshots ==
1. Plugin dashboard.
1. Plugin configuration page.
  
== Changelog ==
= 1.2.3 =
* Bug fixing
* Code cleanup

= 1.2.2 =
* Fix bug with total cart quantity.
* Fix deprecated warning.

= 1.2.1 =
* Bug fixed.
* Added hooks (check documentation).
* Code refactoring.
* Performance optimizations.

= 1.2.0 =
* Bug fixing.
* Improvements to UI.
* New import wizard.
* Added premium features to free version.
* Support for WordPress 6.4

= 1.1.7 =
* Security fixes.
* Bug fixing.
* Other improvements.

= 1.1.6 =
* Bug fixing.
* Add 'defer' strategy to admin scripts.
* Test compatibility with Wordpress 6.3.

= 1.1.5 =
* Fix fatal error when WooCommerce plugin is not installed.
* Other improvements.

= 1.1.4 =
* Translate missing strings to pt-PT.

= 1.1.3 =
* Security improvements.

= 1.1.2 =
* Update to Bootstrap version 5.3.0.
* Fix upload file locations for better support.

= 1.1.1 =
* Fix undefined variable notice when printing tax debug data.
* Fix tax logic for simple products.

= 1.1.0 =
* Remove remote calls to files like Bootstrap.
* Add data validation/sanitization.
* Escape echo contents.

= 1.0.0 =
* Plugin released.