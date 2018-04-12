# Gravity Forms Payment Continue #
**Contributors:** eclev91, travislopes  
**Tags:** gravity forms, paypal  
**Requires at least:** 4.7.4  
**Requires PHP:** 5.4  
**Tested up to:** 4.9.5  
**Stable tag:** 1.1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Exposes the PayPal URL needed to complete payments for PayPal-powered Gravity Forms.

## Description ##

Users not completing payment on your PayPal-powered Gravity Forms? This must-have plugin exposes the PayPal payment URL for an entry. Use it via the merge tag in your notification emails, and copy it directly from an entry for use everywhere else---drip campaigns, personal follow-ups, everywhere!

Features:
* Use the `{payment_url}` merge tag in your notifications and confirmations
* Grab the PayPal URL from the entry details page

## Installation ##

1. Upload the plugin to the `/wp-content/plugins/` directory or via Plugins -> Add New
2. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions ##

### What about other payment gateways? ###

All other official Gravity Forms payment add-ons work on-site rather than off-site. Therefore, abandoned forms using these add-ons should be covered by the [Gravity Forms Partial Entries Add-On](http://www.gravityforms.com/add-ons/partial-entries/).

## Screenshots ##

### 1. Grab the URL from the entry details page ###
![Grab the URL from the entry details page](http://ps.w.org/gravity-forms-payment-continue/assets/screenshot-1.png)


## Changelog ##

### 1.0 ###
* Launch!

### 1.1.0 ###
* Added entry meta so payment URL is available through [Gravity Forms REST API](https://www.gravityhelp.com/documentation/article/api-functions)
