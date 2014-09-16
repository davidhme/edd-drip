=== Easy Digital Downloads - Drip ===
Contributors: davidhme, fatcatapps
Donate link: 
Tags: drip, marketing automation, email, email marketing, edd, easy digital downloads, getdrip, 

Author URI: http://fatcatapps.com/
Plugin URI: http://fatcatapps.com/edd-drip/
Requires at least: 3.9.1
Tested up to: 4.0
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy Digital Downloads - Drip integrates the Easy Digital Downloads (EDD) shopping cart with the Drip email marketing automation tool.

== Description ==
Built  for our own use at [FatcatApps.com](http://fatcatapps.com/), Easy Digital Downloads - Drip integrates two of our favorite tools: [Easy Digital Downloads](https://easydigitaldownloads.com/) and [Drip](https://www.getdrip.com/).

= Features =
**1. Purchase Tracking**
When a customer checks out, the following event will be fired:
`Made a purchase`

The plugin also tracks the following properties:

* `value` (Price of the product bought)
* `product_name` (Name of the product bought)
* `price_name` (The price_name [if you're using variable pricing])

All in all, the API call to Drip will look like this:
`{ "events": [{ "email": {email}, "action": "Made a purchase", "properties": { "value": {price}, "product_name": {name}, "price_name": {price_name} } }] }`

**2. Refund Tracking**
When a customer refunds (payment status = "Refunded", the following event will be fired:
`Refunded`

The plugin also tracks the following properties:

* `value` (Price of the product bought)
* `product_name` (Name of the product bought)
* `price_name` (Name of the price_name [if you're using variable pricing])

All in all, the API call to Drip will look like this:
`{ "events": [{ "email": {email}, "action": "Refunded", "properties": { "value": {price}, "product_name": {name}, "price_name": {price_name} } }] }`

**3. Lifetime Value (LTV) Tracking**
This plugin tracks your customer's lifetime value in a custom field called `lifetime_value`.

If a customer makes a purchase:
`lifetime_value+={price}`

If a customer refunds:
`lifetime_value-={price}`


For more information, [please refer to this blog post](http://fatcatapps.com/edd-drip/).

= Setup =
Please [go here](http://fatcatapps.com/edd-drip/#setup) to learn how to set up this plugin correctly.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Downloads -> Settings -> Extensions -> Drip Settings and enter your "Drip API Key" and "Drip Account ID".

== Changelog ==

= 1.1 =

* Fixed a bug: price_name wasn't tracked correctly


= 1.0.1 =

* Fixed typo in readme.txt


= 1.0 =

* Initial release



`<?php code(); // goes in backticks ?>`