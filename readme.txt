=== Easy Digital Downloads - Drip ===
Contributors: davidhme, fatcatapps
Donate link: 
Tags: drip, marketing automation, email, email marketing, edd, easy digital downloads, getdrip, 

Author URI: http://fatcatapps.com/
Plugin URI: http://fatcatapps.com/edd-drip
Requires at least: 3.9.1
Tested up to: 4.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy Digital Downloads - Drip integrates the Easy Digital Downloads (EDD) shopping cart with the Drip email marketing automation tool.

== Description ==
Built  for our own use at [FatcatApps.com](http://fatcatapps.com/), Easy Digital Downloads - Drip integrates two of our favorite tools: [Easy Digital Downloads]()https://easydigitaldownloads.com/) and [Drip](https://www.getdrip.com/).

= Features =
**1. Purchase Tracking**
When a customer checks out, the following event will be fired:
`Made a purchase`

The plugin also tracks the following properties:

* `value` (Price of the product bought)
* `product_name` (Name of the product bought)
* `price_name` (Name of the price_name [if you're using variable pricing])

All in all, the API call to Drip will look like this:
`{ "events": [{ "email": {email}, "action": "Made a purchase", "properties": { "value": {price}, "product_name": {name} "price_name": {price_name} } }] }`

**2. Refund Tracking**
When a customer refunds (payment status = "Refunded", the following event will be fired:
`Refunded`

The plugin also tracks the following properties:

* `value` (Price of the product bought)
* `product_name` (Name of the product bought)
* `price_name` (Name of the price_name [if you're using variable pricing])

All in all, the API call to Drip will look like this:
`{ "events": [{ "email": {email}, "action": "Refunded", "properties": { "value": {price}, "product_name": {name} "price_name": {price_name} } }] }`

**3. Lifetime Value (LTV) Tracking**
This plugin tracks your customer's lifetime value in a custom field called `lifetime_value`.

If a customer makes a purchase:
`lifetime_value+={price}`

If a customer refunds:
`lifetime_value-={price}`


For more information, [please refer to this blog post](http://fatcatapps.com/edd-drip).

= Roadmap / Contributors welcome =

We're pretty happy with this first version. A possible feature we may add in the future is cart abandonment tracking. If you are interested in improving this plugin / contributing code please [contact us](http://fatcatapps.com/contact/).

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In your sidebar, select 'Opt In Forms -> Add New' to create a new table

== Changelog ==

= 1.0 =

* Initial release



`<?php code(); // goes in backticks ?>`