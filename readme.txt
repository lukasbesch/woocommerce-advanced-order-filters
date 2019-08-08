=== WooCommerce Additional Order Filters ===
Contributors: skyverge, beka.rice, lukasbesch  
Tags: woocommerce, orders, filter orders, coupons, shipping method 
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=connect@lukasbesch.com&item_name=Donation+WooCommerce+Advanced+Order+Filters  
Requires at least: 3.8
Tested up to: 5.2
WC Requires at least: 2.2
WC tested up to: 3.6
Stable Tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds custom filtering to the orders screen to allow filtering by coupon, shipping method or attribute used.

 
== Description ==
 
Does exactly what you think :) This plugin adds some new filtering options to the orders screen. This allows you to filter your orders by:
* the coupon used within the order.
* shipping method
* attributes in bought products

== Installation ==

1. Be sure you're running WooCommerce 2.2+ in your shop.
2. Upload the entire `woocommerce-advanced-order-filters` folder to the `/wp-content/plugins/` directory, or upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress

You can now filter your orders in your shop by visiting **WooCommerce &gt; Orders** in your admin.

== Frequently Asked Questions ==
= Why don't all of my coupons show up in the dropdown? =
Only coupons with a status of "published" will be shown here. If your coupon is set up as draft, it won't be used for filtering.

= I don't see a dropdown at all! What gives? =
The coupon filtering dropdown will only show if coupons are present in your shop and published :).

= Can I filter for orders that have used more than one coupon? =
Sorry, this functionality isn't available. This plugin is simply meant to be an easy way to check for orders that contain a particular coupon. If an order uses more than one coupon, it will still be included when filtering, so long as the coupon you're filtering for was used.

= Can I translate this in my language? =
Yep! There's only one string to translate, but you can do so :). The coupon names are pulled directly from your coupon list, so they can be translated there.

The text domain to use is `wc-advanced-order-filters`.

= This is handy! Can I contribute? =
Yes you can! Join in on our [GitHub repository](https://github.com/lukasbesch/woocommerce-advanced-order-filters/) and submit a pull request :)

== Screenshots ==
1. The new coupon filter added to the Orders page

== Changelog ==

**2019.08.07 - version 1.2.0**
* Forked from [woocommerce-filter-orders](https://github.com/bekarice/woocommerce-filter-orders)
* Filter by shipping method
* Filter by attribute pa_popup-store

**2017.06.27 - version 1.1.0**
 * Feature: Add support for GitHub Updater
 * Misc: Code cleanup

**2015.07.27 - version 1.0.1**
 * Misc: WooCommerce 2.4 compatibility

**2015.03.06 - version 1.0.0**
 * Initial Release
