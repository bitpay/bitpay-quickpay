<?php
/*
 * Plugin Name: BitPay QuickPay
 * Description: Create BitPay payment buttons with a shortcode.  <a href ="admin.php?page=bitpay-quickpay">Configure</a>
 * Version: 2.1.2001
 * Author: BitPay
 * Author URI: mailto:integrations@bitpay.com?subject=BitPay QuickPay
 */
if (!defined('ABSPATH')): exit;endif;

add_action('admin_menu', 'bitpayquickpay_setup_menu');
add_action('wp_enqueue_scripts', 'enable_bitpayquickpay_js');
add_action('admin_enqueue_scripts', 'admin_enable_bitpayquickpay_js');

#autoloader
function BPQP_autoloader($class)
{
    if (strpos($class, 'BPC_') !== false):
        if (!class_exists('BitPayLib/' . $class, false)):
            #doesnt exist so include it
            include 'BitPayLib/' . $class . '.php';
        endif;
    endif;
}
spl_autoload_register('BPQP_autoloader');

#custom css
add_action('init', 'bitpay_quickpay_css');
function bitpay_quickpay_css()
{
    wp_register_style('bpqp', plugins_url('/css/bitpayquickpay.css', __FILE__), false, '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'bpqp_enqueue_style');
function bpqp_enqueue_style()
{
    wp_enqueue_style('bpqp');
}

add_action('admin_notices', 'bitpayquickpay_check_token');
function bitpayquickpay_check_token()
{
    if ($_GET['page'] == 'bitpay-quickpay' && $_GET['settings-updated'] == 'true' && is_admin()) {
        $bitpay_token = get_option('bitpayquickpay_option_dev_token');
        $env = 'test';
        if (get_option('bitpayquickpay_option_endpoint') == 'production'):
            $env = 'production';
            $bitpay_token = get_option('bitpayquickpay_option_prod_token');
        endif;
        $config = new BPC_Configuration($bitpay_token, $env);
        if (empty($bitpay_token)): ?>
    <!--
    <div class="error notice is-dismissible">
        <p>
            <?php _e('There is no token set for your <b>' . strtoupper($env) . '</b> environment.  <b>BitPay</b> will not function if this is not set.');?>
        </p>
    </div>
    -->
    <?php
##check and see if the token is valid
        else:
            if (isset($_GET['settings-updated']) && !empty($bitpay_token) && !empty($env)) {
                if (!BPQP_checkBitPayToken($bitpay_token, $env)): ?>
	        <div class="error notice">
	            <p>
	                <?php _e('The token for <b>' . strtoupper($env) . '</b> is invalid.  Please verify your settings.');?>
	            </p>
	        </div>
	    <?php else: ?>
    <div class="notice notice-success is-dismissible">
            <p>
                <?php _e('Your settings have been saved.');?>
            </p>
        </div>
   <?php endif;
        }

        endif;
    }
}

function BPQP_checkBitPayToken($bitpay_token, $bitpay_checkout_endpoint)
{
    #we're going to see if we can create an invoice
    $config = new BPC_Configuration($bitpay_token, $bitpay_checkout_endpoint);
    //sample values to create an item, should be passed as an object'
    $params = new stdClass();
    $params->extension_version = BPC_getBitpayQuickpayInfo();
    $params->price = '10.00';
    $params->currency = 'USD'; //set as needed

    $item = new BPC_Item($config, $params);
    $invoice = new BPC_Invoice($item);

    //this creates the invoice with all of the config params from the item
    $invoice->BPC_createInvoice();
    $invoiceData = json_decode($invoice->BPC_getInvoiceData());
    //now we have to append the invoice transaction id for the callback verification
    $invoiceID = $invoiceData->data->id;
    if (empty($invoiceID)):
        return false;
    else:
        return true;
    endif;
}

function enable_bitpayquickpay_js()
{
    wp_enqueue_script('remote-bitpayquickpay-js', 'https://bitpay.com/bitpay.min.js', null, null, true);
    wp_enqueue_script('bitpayquickpay-js', plugins_url('/js/bitpayquickpay_js.js', __FILE__));
    ?>
    <script type = "text/javascript">
    var payment_status = null;
    window.addEventListener("message", function(event) {
        payment_status = event.data.status;
        if(payment_status == 'paid'){
            BPQPshowMessage()
        }
    }, false);
    </script>

    <?php
}
function admin_enable_bitpayquickpay_js()
{
    wp_enqueue_script('amin-bitpayquickpay-js', plugins_url('/js/admin_bitpayquickpay_js.js', __FILE__));
}

function bitpayquickpay_setup_menu()
{
    add_menu_page('BitPay QuickPay Setup', 'BitPay QuickPay', 'manage_options', 'bitpay-quickpay', 'bpqp_load_options');
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

    add_option('bitpayquickpay_option_message');
    register_setting('bitpayquickpay_options_group', 'bitpayquickpay_option_message', 'bitpayquickpay_callback');

    add_option('bitpayquickpay_option_default_amount');
    register_setting('bitpayquickpay_options_group', 'bitpayquickpay_option_default_amount', 'bitpayquickpay_callback');

}
add_action('admin_init', 'bitpayquickpay_register_settings');

#create the shortcode
function getBitPayQuickPayData($atts)
{
    $buttonStyle = $atts['name'];
    $buttonPrice = $atts['price'];
    $buttonDesc = $atts['description'];
    $buttonOverride = $atts['allow_override'];

    return getBitPayQuickPayBrands($buttonStyle, $buttonPrice, $buttonDesc, $buttonOverride);

}
add_shortcode('bitpayquickpay', 'getBitPayQuickPayData');

#brand returned from API
function getBitPayQuickPayBrands($name_only = false, $price = false, $d = false, $bto = false)
{

    $buttonObj = new BPC_Buttons;
    $buttons = json_decode($buttonObj->BPC_getButtons());
    $default_amount = get_option('bitpayquickpay_option_default_amount');
    if (!$name_only) { #get the images
    if (is_admin() && $_GET['page'] == 'bitpay-quickpay'):
            $brand = '';
            $looper = 0;
            foreach ($buttons->data as $key => $b):

                $names = preg_split('/(?=[A-Z])/', $b->name);
                $names = implode(" ", $names);
                $names = ucwords($names);
                if (strpos($names, "Donate") === 0):
                    continue;
                endif;
                $shortcode_name = strtolower($b->name);

                $brand .= '<figure style="float:left;"><figcaption style="text-align:left;"><b>' . $names . '</b><p>' . $b->description . '</p></figcaption>';
                $brand .= '<input class="bp_input" style="margin-bottom: 17px;height: 35px;" onkeyup = "BPQP_Clean(this.value,\'' . $shortcode_name . '\');" placeholder="Enter the amount" id = "gen_' . $shortcode_name . '" type="text" size="20" value = "' . $default_amount . '">';
                $brand .= '<input type="checkbox" style = "margin-left:10px;" id = "chk_' . $shortcode_name . '"> Allow users to change amount<br>
		            ';
                $brand .= '<input class="bp_input" style="margin-bottom: 17px;height: 35px;" placeholder="Description (optional)" id = "desc_' . $shortcode_name . '"type="text" size="30">';
                $brand .= '<img src="//' . $b->url . '" style="width:150px;padding:1px;display: block;">';
                $brand .= '<br><button class = "button button-secondary" onclick = "generateBPQPCode(\'' . $shortcode_name . '\')">Create Code</button>';
                $brand .= '<hr style = "margin-top:40px;">';

                $brand .= '</figure>';
               
                echo '<script type = "text/javascript">';
                echo 'setDefaultCode("' . $looper . '");';
                echo '</script>';
                $looper++;
            endforeach;

            return $brand;
        endif;
    } else {
        foreach ($buttons->data as $key => $b):
            $shortcode_name = strtolower($b->name);
            if ($shortcode_name == $name_only && $price != false):
                $env = 'test';
                if (get_option('bitpayquickpay_option_endpoint') == 'production'):
                    $env = 'production';
                endif;
                $post_url = get_home_url() . '/wp-json/bitpayquickpay/pay';
                $btn_id = 'btnPay_' . uniqid();
                $btn = '<div class = "bpQp">';

                $type = 'hidden';
                if ($bto == true) {
                    $type = 'text';
                }
                $btn .= '<input class="bp_input" id = "' . $btn_id . '" style="margin-bottom: 17px;height: 35px;" placeholder="Enter the amount" value = "' . $price . '" type="' . $type . '" size="20" onkeyup = "BPQPFrontend_Clean(this.value,\'' . $btn_id . '\')">';
                $btn .= '<div class = "bpqpMsg">' . get_option('bitpayquickpay_option_message') . '</div>';
                $btn .= '<input type = "hidden" id = "desc_' . $btn_id . '" value = "' . $d . '">';
                $btn .= "<button class = 'bpqpButton' onclick = \"showBpQp('$env','$post_url','$btn_id');\"><img src ='//" . $b->url . "'></button>";
                $btn .= '</div>';
                return $btn;

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
    $description = $data['description'];

    #create the invoice
    $env = 'test';
    $bitpay_token = get_option('bitpayquickpay_option_dev_token');

    if (get_option('bitpayquickpay_option_endpoint') == 'production'):
        $env = 'production';
        $bitpay_token = get_option('bitpayquickpay_option_prod_token');

    endif;
    $config = new BPC_Configuration($bitpay_token, $env);
    //sample values to create an item, should be passed as an object'
    $params = new stdClass();
    $params->extension_version = BPC_getBitpayQuickpayInfo();
    $params->price = $price;
    $params->redirectURL = $data['redirectUrl'];
    $params->currency = get_option('bitpayquickpay_option_currency');

    if (empty(get_option('bitpayquickpay_option_currency'))):
        $params->currency = 'USD';
    endif;
    if ($description != '') {
        $params->itemDesc = $description;
    }

    $item = new BPC_Item($config, $params);
    $invoice = new BPC_Invoice($item);
    //this creates the invoice with all of the config params from the item
    $invoice->BPC_createInvoice();
    $invoiceData = json_decode($invoice->BPC_getInvoiceData());

    return $invoiceData;

}

function BitPayQPGetCurrencies()
{
    $currencies = ["USD", "EUR", "GBP", "JPY", "CAD", "AUD", "CNY", "CHF", "SEK", "NZD", "KRW"];
    return $currencies;
}

function BPC_getBitpayQuickpayInfo()
{
    $plugin_data = get_file_data(__FILE__, array('Version' => 'Version', 'Plugin_Name' => 'Plugin Name'), false);
    $plugin_name = $plugin_data['Plugin_Name'];
    $plugin_name = str_replace(" ", "_", $plugin_name);
    $plugin_version = $plugin_name . '_' . $plugin_data['Version'];
    return $plugin_version;
}


add_action('wp_footer', 'bitpay_chrome_extension');
function bitpay_chrome_extension() {
  echo '<div id = "bpDiv123119" style = "clear:both;"></div>';
}

function bpqp_load_options()
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
                    <em>Your <b>development</b> merchant token. <a href="https://test.bitpay.com/dashboard/merchant/api-tokens" target="_blank">Create one
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

                        <?php $currencies = BitPayQPGetCurrencies();?>
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
                    <em>Set yout payment currency</em>
                </td>
            </tr>


            <tr align="left">
                <th scope="row"><label for="bitpayquickpay_option_default_amount">Default Amount</label></th>
                <td>
                <input type="text" size="80" onkeyup = "BPQP_CleanDefault(this.value)"  id="bitpayquickpay_option_default_amount"
                        name="bitpayquickpay_option_default_amount"
                        value="<?php echo get_option('bitpayquickpay_option_default_amount'); ?>" />

                </td>

            </tr>

            <tr align="left">
                <td>&nbsp</td>
                <td>
                    <em>Set a default amount for the autogenerator</em>
                </td>
            </tr>


            <tr align="left">
                <th scope="row"><label for="bitpayquickpay_option_message">Thank You Message</label></th>
                <td><input placeholder = "Optional message after a payment is made" type="text" size="80" id="bitpayquickpay_option_message"
                        name="bitpayquickpay_option_message"
                        value="<?php echo get_option('bitpayquickpay_option_message'); ?>" />
                </td>
            </tr>

            <tr align="left">
                <td>&nbsp</td>
                <td>
                    <em>This message will appear after a successful payment (optional)</em>
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

    echo '<div style = "width:100%;clear:both;margin-top:20px;margin-bottom:20px;"></div>';
    echo '<div style = "width:100%;">';
    echo '<div style = "width:45%;float:left;">';
    echo '<span style = "margin-left:20px;"><b>1) Adjust the price for any button below to have your code automatically generated.</b><br><br></span>';
    echo $images;
    echo '</div>';

    #vertical div
    echo '<div id = "vline" class = "vline" style = "float:left;height:550px;border-left-width:1px;border-left-style:solid;border-left-color:#ddd;"></div>';
    echo '<span style = "margin-left:20px;"><b>2) Copy and paste the code below to a <a href="edit.php?post_type=post">Post</a> or <a href="edit.php?post_type=page">Page</a></b><br><br></span>';
    echo '<div style =" width:45%;float:left;margin-left:20px;background-color:#ffffff;padding:20px;">';
    echo '<span id = "generated_code"><b>your generated code will appear here</b></span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
