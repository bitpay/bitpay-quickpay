<?php
/*
 * Plugin Name: BitPay QuickPay
 * Description: Create BitPay payment buttons with a shortcode
 * Version: 1.0.0.0
 * Author: Joshua Lewis - sales-engineering@bitpay.com
 * Author URI: https://www.bitpay.com
 */

add_action('admin_menu', 'bitpayquickpay_setup_menu');
add_action('wp_enqueue_scripts', 'enable_bitpayquickpay_js');
add_action('admin_enqueue_scripts', 'admin_enable_bitpayquickpay_js');

function enable_bitpayquickpay_js()
{
    wp_enqueue_script('bitpayquickpay-js', plugins_url('/js/bitpayquickpay_js.js', __FILE__));
}
function admin_enable_bitpayquickpay_js()
{
    wp_enqueue_script('amin-bitpayquickpay-js', plugins_url('/js/admin_bitpayquickpay_js.js', __FILE__));
}

function bitpayquickpay_setup_menu()
{
    add_menu_page('BitPay QuickPay Setup', 'BitPay QuickPay', 'manage_options', 'bitpay-quickpay', 'load_options');
}

function bitpayquickpay_register_settings()
{
    add_option('bitpayquickpay_option_dev_token');
    register_setting('bitpayquickpay_options_group', 'bitpayquickpay_option_dev_token', 'bitpayquickpay_callback');

    add_option('bitpayquickpay_option_prod_token');
    register_setting('bitpayquickpay_options_group', 'bitpayquickpay_option_prod_token', 'bitpayquickpay_callback');

    add_option('bitpayquickpay_option_endpoint');
    register_setting('bitpayquickpay_options_group', 'bitpayquickpay_option_endpoint', 'bitpayquickpay_callback');

    add_option('bitpayquickpay_option_currency');
    register_setting('bitpayquickpay_options_group', 'bitpayquickpay_option_currency', 'bitpayquickpay_callback');

}
add_action('admin_init', 'bitpayquickpay_register_settings');

#create the shortcode
function getBitPayQuickPayData($atts)
{
    $buttonStyle = $atts['name'];
    $buttonPrice = $atts['price'];
    return getBitPayQuickPayBrands($buttonStyle, $buttonPrice);

}
add_shortcode('bitpayquickpay', 'getBitPayQuickPayData');

#brand returned from API
function getBitPayQuickPayBrands($name_only = false, $price = false)
{

    require_once 'classes/Buttons.php';
    $buttonObj = new Buttons;
    $buttons = json_decode($buttonObj->getButtons());
    if (!$name_only) { #get the images

        $brand = '';
        foreach ($buttons->data as $key => $b):

            $names = preg_split('/(?=[A-Z])/', $b->name);
            $names = implode(" ", $names);
            $names = ucwords($names);
            $shortcode_name = strtolower($b->name);

            $brand .= '<figure style="float:left;"><figcaption style="text-align:left;"><b>' . $names . '</b><p>' . $b->description . '</p></figcaption>';
            $brand .= '<input class="bp_input" style="margin-bottom: 17px;height: 35px;" onkeyup = "generateBPQPCode(this.value,\'' . $shortcode_name . '\');" placeholder="Enter the amount" id = "gen_' . $shortcode_name . '"type="text" size="20"><img src="//' . $b->url . '" style="width:150px;padding:1px;display: block;"></figure>';
            $brand .= '</figure>';
        endforeach;

        return $brand;
    } else {
        foreach ($buttons->data as $key => $b):
            $shortcode_name = strtolower($b->name);
            if ($shortcode_name == $name_only && $price != false):
                $env = 'test';

                if (get_option('bitpayquickpay_option_endpoint') == 'production'):
                    $env = 'production';
                endif;
                $post_url = get_home_url() . '/wp-json/bitpayquickpay/pay';
                return "<img onclick = \"showBpQp('$env','$post_url','$price');\" src ='//" . $b->url . "'>";
            endif;

        endforeach;

    }

}
add_action('rest_api_init', function () {
    register_rest_route('bitpayquickpay', '/pay', array(
        'methods' => 'POST,GET',
        'callback' => 'bitpayquickpay_pay',
    ));

});
function bitpayquickpay_pay(WP_REST_Request $request)
{
    $data = $request->get_params();
    $price = $data['price'];
    #include the bp classes
    require 'classes/Config.php';
    require 'classes/Client.php';
    require 'classes/Item.php';
    require 'classes/Invoice.php';

    #create the invoice
    $env = 'test';
    $bitpay_token = get_option('bitpayquickpay_option_dev_token');

    if (get_option('bitpayquickpay_option_endpoint') == 'production'):
        $env = 'production';
        $bitpay_token = get_option('bitpayquickpay_option_prod_token');

    endif;
    $config = new Configuration($bitpay_token, $env);
    //sample values to create an item, should be passed as an object'
    $params = new stdClass();
    $params->extension_version = getBitpayQuickpayInfo();
    $params->price = $price;
    $params->currency = get_option('bitpayquickpay_option_currency');

    if (empty(get_option('bitpayquickpay_option_currency'))):
        $params->currency = 'USD';
    endif;

    $item = new Item($config, $params);
    $invoice = new Invoice($item);
    //this creates the invoice with all of the config params from the item
    $invoice->createInvoice();
    $invoiceData = json_decode($invoice->getInvoiceData());

    return $invoiceData;

}

function BitPayGetCurrencies()
{
    $currencies = ["USD", "EUR", "GBP", "JPY", "CAD", "AUD", "CNY", "CHF", "SEK", "NZD", "KRW"];
    return $currencies;

}

function getBitpayQuickpayInfo()
{
    $plugin_data = get_file_data(__FILE__, array('Version' => 'Version', 'Plugin_Name' => 'Plugin Name'), false);
    $plugin_name = $plugin_data['Plugin_Name'];
    $plugin_name = str_replace(" ", "_", $plugin_name);
    $plugin_version = $plugin_name . '_' . $plugin_data['Version'];
    return $plugin_version;
}

function load_options()
{

    #this page creates the admin settings
    echo '<h3>Customize your BitPay QuickPay shortcodes.</h3>';
    echo '<p>Setup your environment, and preview the buttons that will be added with the shortcode</p>';

    ?>
<div>
    <form method="post" action="options.php">
        <?php settings_fields('bitpayquickpay_options_group');?>

        <table cellpadding="5">

            <tr align="left">
                <th scope="row"><label for="bitpayquickpay_option_dev_token">Development Token</label></th>
                <td><input type="text" size="80" id="bitpayquickpay_option_dev_token"
                        name="bitpayquickpay_option_dev_token"
                        value="<?php echo get_option('bitpayquickpay_option_dev_token'); ?>" />
                </td>
            </tr>

            <tr align="left">
                <td>&nbsp</td>
                <td>
                    <em>Your <b>development</b>merchant token. <a href="https://test.bitpay.com/dashboard/merchant/api-tokens" target="_blank">Create one
                            here</a> and <b>uncheck</b> `Require Authentication`.</em>
                </td>
            </tr>

            <tr align="left">
                <th scope="row"><label for="bitpayquickpay_option_prod_token">Production Token</label></th>
                <td><input type="text" size="80" id="bitpayquickpay_option_prod_token"
                        name="bitpayquickpay_option_prod_token"
                        value="<?php echo get_option('bitpayquickpay_option_prod_token'); ?>" /></td>
            </tr>

            <tr align="left">
                <td>&nbsp</td>
                <td>
                    <em>Your <b>production</b> merchant token. <a
                            href="https://www.bitpay.com/dashboard/merchant/api-tokens" target="_blank">Create one
                            here</a> and <b>uncheck</b> `Require Authentication`.</em>
                </td>
            </tr>

            <tr align="left">
                <th scope="row"><label for="bitpayquickpay_option_endpoint">Endpoint</label></th>
                <td>
                    <select class="select " name="bitpayquickpay_option_endpoint" id="bitpayquickpay_option_endpoint">
                        <option value="production"
                            <?php if (get_option('bitpayquickpay_option_endpoint') == 'production'): echo 'selected';endif;?>>
                            Production</option>
                        <option value="test"
                            <?php if (get_option('bitpayquickpay_option_endpoint') == 'test'): echo 'selected';endif;?>>
                            Test</option>
                    </select>
                </td>
            </tr>

            <tr align="left">
                <td>&nbsp</td>
                <td>
                    <em>Select <b>Test</b> for testing the plugin, <b>Production</b> when you are ready to go live.</em>
                </td>
            </tr>


            <tr align="left">
                <th scope="row"><label for="bitpayquickpay_option_currency">Currency</label></th>
                <td>
                    <select class="select " name="bitpayquickpay_option_currency" id="bitpayquickpay_option_currency">

                        <?php $currencies = BitPayGetCurrencies();?>
                        <?php foreach ($currencies as $v) {?>
                        <option value="<?php echo $v; ?>"
                            <?php if ($v == get_option('bitpayquickpay_option_currency')): echo 'selected';endif;?>>
                            <?php echo $v; ?></option>
                        <?php }?>

                    </select>
                </td>
            </tr>

            <tr align="left">
                <td>&nbsp</td>
                <td>
                    <em>Select <b>Test</b> for testing the plugin, <b>Production</b> when you are ready to go live.</em>
                </td>
            </tr>




        </table>
        <?php submit_button();?>
    </form>
</div>
<?php

    $images = getBitPayQuickPayBrands();
    echo '<hr>';
    echo '<div style = "width:100%;">';
    echo '<h4>BitPay QuickPay Shortcode Options</h4>';
    echo '<p>Use the <b>shortcode</b> tag to embed anywhere.</p>';
    echo '<p>You must pass the "name" and "price" as an option to pre-fill the invoice, ie <em>[bitpayquickpay name ="paywithbitpaybutton" price="25"]</em> to load one of the buttons below.</p>';
    echo '<p>Adjust the price for any button below to have your code automatically generated.</p>';
    echo '<div style = "width:100%;clear:both;margin-top:40px;margin-bottom:40px;"></div>';

    echo '<div style = "width:100%;">';
    echo '<div style = "width:45%;float:left;">';
    echo $images;
    echo '</div>';

    #vertical div
    echo '<div id = "vline" class = "vline" style = "float:left;height:550px;border-left-width:1px;border-left-style:solid;border-left-color:#ddd;"></div>';
    echo '<span style = "margin-left:20px;"><b>Copy and paste the code below to a post/page/widget.</b><br><br></span>';
    echo '<div style =" width:45%;float:left;margin-left:20px;background-color:#ffffff;padding:20px;">';
    echo '<span id = "generated_code"><b>your generated code will appear here</b></span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

}
?>
