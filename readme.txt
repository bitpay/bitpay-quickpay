=== BitPay QuickPay ===
Contributors: bitpay
Tags: bitcoin, bitcoin cash, payments, bitpay, cryptocurrency, payment gateway
Requires at least: 4.9
Tested up to: 5.0.4
Requires PHP: 5.5
Stable tag: 1.0.1.3
License: MIT License (MIT)
License URI: https://github.com/bitpay/bitpay-quickpay/blob/master/LICENSE

The most secure and fastest way to accept crypto payments (Bitcoin, Bitcoin Cash, etc).

== Description ==

== BitPay QuickPay ==

= Key features =

* Accept bitcoin and bitcoin cash payments from payment protocol compatible wallets
* No e-commerce setup required to integrate directly on your Wordpress site.
* Create a custom shortcode and embed in any page/post/widget
* Price in your local currency
* Get settled via Bank transfer (EUR, USD, GBP or any of the supported [fiat currencies](https://bitpay.com/docs/settlement)), BTC, BCH or stable coins (GUSD, USDC)
* By design, chargebacks are not possible with cryptocurrency payments
* Have an overview of all your bitcoin and bitcoin cash payments in your BitPay merchant account at https://bitpay.com/dashboard
* Refund your customers in bitcoin or bitcoin cash in your BitPay merchant dashboard at https://bitpay.com/dashboard/payments
* A CSS file is included to further customize the plugin to your site design

= Customer journey =

1. A user has a few items to sell, but doesnt want to go through the setup of an ecommerce store, or they want to add a quick way to donate with cryptocurrency.
2. The user installs BitPay QuickPay, and adds their API token (after creating a merchant account at https://www.bitpay.com)
3. The user creates a shortcode in the plugin, adding a price and optional description.
4. The shortcode is auto-generated as the information is filled out .
5. The user copies and pastes this code into any page/post/widget, and a button is automatically generated.
6. A BitPay invoice is generated, the customer selects one of the supported cryptocurrency to complete the payment. The invoice will display an amount to pay in the selected cryptocurrency, at an exchange rate locked for 15 minutes.
7. The customer completes the payment using a compatible wallet within the 15 min window.
8. Once the transaction is fully confirmed on the blockchain, BitPay notifies the user and the corresponding amount is credited to the BitPay merchant account minus our 1% processing fee - thus $99 USD in this example.

== Installation ==

= Requirements =

* A BitPay merchant account ([Test](http://test.bitpay.com) and [Production](http://www.bitpay.com))

= Plugin installation =

1. Get started by signing up for a [BitPay merchant account](https://bitpay.com/dashboard/signup)
2. Look for the BitPay QuickPay plugin via the [Wordpress Plugin Manager](https://codex.wordpress.org/Plugins_Add_New_Screen). From your Wordpress admin panel, go to Plugins > Add New > Search plugins and type **BitPay**
3. Select **BitPay QuickPay** and click on **Install Now** and then on **Activate Plugin**

After the plugin is activated, BitPay QuickPay will appear in left navigation.

= Plugin configuration =

After you have installed the BitPay QuickPay plugin, the configuration steps are:

1. Create an API token from your BitPay merchant dashboard:
	* Login to your BitPay merchant account and go to the [API token settings](https://bitpay.com/dashboard/merchant/api-tokens)
	* Click on the **Add new token** button: indicate a token label (for instance: Woocommerce), uncheck "Require Authentication" and click on the **Add Token** button
	* Copy the token value
2. Log in to your WordPress admin panel, select BitPay QuickPay to show the configuration section
	* Paste the token value into the appropriate field: **Development Token** for token copied from the sandbox environment (test.bitpay.com) and **Production Token** for token copied from the live environment (bitpay.com)
	* Select the endpoint - Test or Production
	* Select the currency
	* Add a custom (optional) Thank You message to show after a payment is made
	* Click "Save changes" at the bottom of the page

== Frequently Asked Questions ==

= How do I pay a BitPay invoice? =
You can pay a BitPay invoice with one of the compatible wallets. You can either scan the QR code, click on the "open in wallet" button or copy/paste the payment URL via a compatible wallet.

More information about paying a BitPay invoice can be found [here.](https://support.bitpay.com/hc/en-us/articles/115005559826-How-do-I-pay-a-BitPay-merchant-without-a-bitcoin-address-)

= Does BitPay have a test environment? =
Yes, you can create an account on BitPay's sandbox environment to process payments on testnet. You will also need to setup a wallet on testnet to make test transactions. More information about the test environment can be found [here.](https://bitpay.com/docs/testing)

= The BitPay plugin does not work =
If BitPay invoices are not created, please check the following:

* The minimum invoice amount is $1 USD. Please make sure you are trying to create a BitPay invoice for $1 USD or more (or your currency equivalent).
* Check your current approved processing limits in your [BitPay merchant account](https://bitpay.com/dashboard/verification)

= I need support from BitPay =
When contacting BitPay support, please describe your issue and attach screenshots and any server logs.

You can contact our support team via the following form https://bitpay.com/request-help/wizard

== Screenshots ==

1. BitPay merchant dashboard - create a new POS token
2. BitPay QuickPay settings
3. Creating a custom shortcode
4. Embedding the shortcode in a page
5. Frontend view of the html generated by the shortcode
6. BitPay hosted invoice - cryptocurrency selected
7. BitPay hosted invoice - Customer clicked on the "open in wallet", this opens the compatible wallet installed on the device which automatically retrieves the payment information.
8. The customer confirmed the payment via his compatible wallet. The BitPay invoice is then marked as paid, and the status will be sent via email.


== Changelog ==

= 1.0.1.4 =
* cUrl updates

= 1.0.1.3 =
* Initialize admin buttons with default value

= 1.0.1.2 =
* Performance tweaks when loading buttons
