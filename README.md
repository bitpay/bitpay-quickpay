> [!WARNING]
> **This plugin is no longer supported.**
>
> BitPay does not maintain this plugin anymore. It will not get updates, bug fixes or security fixes. This repository will be archived and become read-only.
>
> If you use this plugin, please move to a supported BitPay integration. You can find the current list at https://developer.bitpay.com/docs/bitpay-integration-guide.

## Build Status

[![Build Status](https://travis-ci.org/bitpay/bitpay-quickpay.svg?branch=master)](https://travis-ci.org/bitpay/bitpay-quickpay)

# Description

Embed a shortcode on any page or post to instantly accept Bitcoin payments.

# Quick Setup

This version requires the following

* A BitPay merchant account ([Test](http://test.bitpay.com) or [Production](http://www.bitpay.com))
* An API Token ([Test](https://test.bitpay.com/dashboard/merchant/api-tokens) or [Production](https://bitpay.com/dashboard/merchant/api-tokens)
	* When setting up your token, **uncheck** the *Require Authentication button*


# Plugin Fields

After the plugin is activated, BitPay QuickPay will appear in the left navigation of Wordpress


* **Merchant Tokens**
	* A ***development*** or ***production*** token will need to be set
* **Endpoint**
	* Choose **Test** or **Production**, depending on your current setup.  Your matching API Token must be set.

* **Currency**
	* Choose the currency to accept.  If no currency is set, **USD** will be the default. 
	
# How to use

In the settings page, there is a list of buttons to display.  Simply adjust the price for any button, and your code will automatically be generated.

Example: `[bitpayquickpay name ="paywithbitpaysupportedcurrencies" price="1.50"]`

Copy and paste this code into a post/page/widget to generate a button.



![](https://bitpay.com/cdn/en_US/bp-btn-pay-currencies.svg)