=== Easy Digital Downloads - Drip ===
Contributors: davidhme, fatcatapps, cssimmon
Donate link: 
Tags: drip, marketing automation, email, email marketing, edd, easy digital downloads, getdrip, cart abandonment

Author URI: http://fatcatapps.com/
Plugin URI: http://fatcatapps.com/edd-drip/
Requires at least: 3.9.1
Tested up to: 4.9
Stable tag: 1.4.1
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

**3. Cart Abandonment Tracking**
When a payment's status has been "pending" for at least 30 minutes, or when a payment's status changes to "abandoned", the following event will be fired:
`Abandoned cart`

The plugin also tracks the following properties:

* `value` (Price of the product)
* `product_name` (Name of the product)
* `price_name` (Name of the price_name [if you're using variable pricing])

All in all, the API call to Drip will look like this:
`{ "events": [{ "email": {email}, "action": "Refunded", "properties": { "value": {price}, "product_name": {name}, "price_name": {price_name} } }] }`

**4. Lifetime Value (LTV) Tracking**
This plugin tracks your customer's lifetime value in a custom field called `lifetime_value`.

If a customer makes a purchase:
`lifetime_value+={price}`

If a customer refunds:
`lifetime_value-={price}`


For more information, [please refer to this blog post](http://fatcatapps.com/edd-drip/).

= Contributors Welcome =
Thanks to [Chris Simmons from WP BackItUp](https://www.wpbackitup.com/) and [Phil Derksen from WP Simple Pay for Stripe](https://wpsimplepay.com/) for contributing.

Do you want to see further improvements? Please consider contributing. You can submit a pull request here: https://github.com/davidhme/edd-drip

= Setup =
Please [go here](http://fatcatapps.com/edd-drip/#setup) to learn how to set up this plugin correctly.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Downloads -> Settings -> Extensions -> Drip Settings and enter your "Drip API Key" and "Drip Account ID".

== Changelog ==

= 1.4.2 -

* Fix: Bug for settings on EDD>=2.5 where drip api key and account are lost whenever extension settings in other sections are saved.

* Fix: Bug with with `value` property in custom event `payment processed` not being integer.  Drip requires this property to be an integer for some reason or the event will not be processed.

* Fix: Drip settings were being updated with null when EDD extension settings were saved.  Updates were required to support EDD > 2.5.

* Update: Added support for free downloads when edd bypass modal is turned on. When bypass modal is on the cart items are empty so subscriber was not being added.

* Update: Added price_id property to 'Made a Purchase' event

* Update: Tested with WordPress 4.9

= 1.4.1 -

* Fix: A bug in 1.4.0 removed `price_name` from the `Made a purchase` event

* Fix: `first_name` custom field didn't get saved.

= 1.4.0 = 

* Added first name to drip properties: This is useful for outbound emails vs full name

* Added is_renewal to event: This is useful for segmenting renewal customers from new purchases.


= 1.3.1 =

* Removed quantity value for the "Made a Purchase" event

= 1.3 =

* Changed behavoir of "Abandoned" - event. "Abandoned Cart" will be triggered if the payment status is pending for at least 30 minutes.


= 1.2.0.2 =

* Fix: Purchases weren't tracked properly with some payment gateways (PayPal)

= 1.2.0.1 =

* Fix: Only trigger the "Made a Purchase" event when the payment status is "complete"

* New feature: Fire an event when EDD payment status = "Abandoned"


= 1.1 =

* Fixed a bug: price_name wasn't tracked correctly


= 1.0.1 =

* Fixed typo in readme.txt


= 1.0 =

* Initial release
