=== WooCommerce Prices ===
Contributors: sydcode
Donate link: 
Tags: woocommerce, e-commerce, ecommerce, shop, shopping, store, prices, sales, edit, editor, manage
Requires at least: 3.3
Tested up to: 3.8.1
Stable tag: 1.0.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows for custom editing of product prices in WooCommerce.

== Description ==

This plugin allows for custom editing of product prices in WooCommerce. This version only supports simple and external/affiliate products, but support for variable products will be added to a future version.

This is not an official WooCommerce plugin. Please do not bother the nice WooThemes people with support questions. All questions and suggestions should be posted in the plugin forum.

Thanks to Anthony for sponsoring this plugin.

== Installation ==

1. Upload the `woocommerce-prices` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit the prices using the link in the products menu.

= Action Descriptions =

= New Regular Price =
Change the regular price to an amount or a percentage of the regular price.
= Increase Regular Price =
Increase the regular price by an amount or a percentage of the regular price.
= Decrease Regular Price =
Decrease the regular price by an amount or a percentage of the regular price.

= New Sale Price =
Change the sale price to an amount or a percentage of the sale price. Sale dates are optional and have no effect when left empty.
= New Sale Discount =
Change the sale price to an amount or a percentage off the regular price. Sale dates are optional and have no effect when left empty.
= Increase Sale Price =
Increase the sale price by an amount or a percentage of the sale price.
= Decrease Sale Price =
Decrease the sale price by an amount or a percentage of the sale price.

= New Sale From =
Change the date when the sale starts with the calendar.
= Increase Sale From =
Increase the date when the sale starts by a number of days.
= Decrease Sale From =
Decrease the date when the sale starts by a number of days.

= New Sale To =
Change the date when the sale ends with the calendar.
= Increase Sale To =
Increase the date when the sale ends by a number of days.
= Decrease Sale To =
Decrease the date when the sale ends by a number of days.

== Screenshots ==

1. Main Panel

2. Action Menu

== Frequently Asked Questions ==

= 1. What are "New Sale Price" and "New Sale Discount"? =

The "New Sale Price" action changes the sale price to an amount or a percentage of the current sale price. This action is typically used for adjusting sale prices. The "New Sale Discount" action changes the sale price to an amount or a percentage off the regular price. This is handy for creating sales like "20% off". The "Sale From" and "Sale To" dates are optional for both actions and have no effect when left empty.

= 2. Why don't variable products appear in the table? =

This version only supports simple and external/affiliate products, but support for variable products will be added to a future version.

= 3. Does the value need a minus symbol before it? =

No.  Use positive numbers or zero, or an empty value to clear the results.

== Upgrade Notice ==

No upgrade notices.

== Changelog ==

= 1.0.1 =
* Fixed currency format bug

= 1.0.0 =
* First release