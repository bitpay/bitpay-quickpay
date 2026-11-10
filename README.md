> [!WARNING]
> **This repository is archived and no longer maintained by BitPay.**
>
> It is read-only and will not receive updates, bug fixes, or security fixes. Please do not start new integrations from this code.
>
> For currently supported integrations, see [eCommerce Plugins](https://developer.bitpay.com/docs/plugins) and [Libraries](https://developer.bitpay.com/docs/libraries). If you already use this code, we recommend moving to a supported integration.

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