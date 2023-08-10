<?php

/**
 * Plugin Name: CardNet Gateway for Woocommerce
 * Plugin URI: https://cardnet.com.do/
 * Description: Use CardNET Dominicana services and accept credit card payments in your WordPress / WooCommerce store.
 * Version: 1.0.4
 * Author: CardNET Services
 * Contributors: JG, LE & RF
 * Requires at least: 4.3
 * Tested up to: 5.5.3
 *
 * Text Domain: woo-cardnet-payment
 * Domain Path: /languages/
 * WC requires at least: 4.1
 * WC tested up to: 4.8.0
 * 
 * @package WC CardNet Payment Gateway
 * @author RF & RAF
 */

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('plugins_loaded', 'init_wc_gateway_cardnet', 0);
}


if (function_exists('cardnet_super_crypt')) :
    new Exception("Error cardnet_super_crypt exists woo-cardnet-gateway", 1);
endif;

function cardnet_super_crypt($string, $action = 'e')
{
    // you may change these values to your own
    $secret_key = 'KAJSHASBNANMNMnmnmas';
    $secret_iv = 'KAJSHASBNANMNMnmnmas';

    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'e') {
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
    } else if ($action == 'd') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function init_wc_gateway_cardnet()
{

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    load_plugin_textdomain('woo-cardnet-payment', false, dirname(plugin_basename(__FILE__)) . '/languages');


    class wc_cardnet_funcs_cs
    {
        public function __construct()
        {
            global $woocommece;


            $this->lang = [
                'es' => 'ESP',
                'en' => 'ING'
            ];

            if (get_option('woocommerce_currency') == 'USD') {
                add_filter('woocommerce_cart_totals_order_total_html',  array($this, 'custom_total_message_html'), 10, 1);
            }

            add_action('wp',  array($this, 'cardnet_wp'), 10, 1);
            add_action('wp_enqueue_scripts',  array($this, 'plugin_scripts'), 0);
            add_action('admin_enqueue_scripts',  array($this, 'plugin_scripts'), 0);

            add_shortcode('cardnet_woocommerce',  array($this, 'cardnet_woocommerce_process'));
            add_shortcode('cardnet_badget',  array($this, 'cardnet_badget_process'));
            add_action('woocommerce_admin_order_data_after_billing_address',    array($this, 'woo_display_order_data_in_admin'));
        }
        /**
         * receipt_page
         **/


        private function encryptAndEncode($mtype, $mnumber, $mterminal, $tid, $ammount, $tax)
        {
            //The encryption required by CardNet is MD5
            $mixofdata = $mtype . $mnumber . $mterminal . $tid . $ammount . $tax;
            $result = md5($mixofdata);
            return $result;
        }

        public function exchange_to_DOP($amount, $dolar = null, $currencyCode)
        {

            if ($currencyCode == 'USD') {
                return  number_format(round($amount * $dolar, 3), 2);
            } else {
                return  number_format(round($amount, 3), 2);
            }
        }

        /**
         * Generate the form with the params
         **/

        public function get_order_total_DOP($order)
        {
            $total = $order->get_total();
            $currencyCode = get_woocommerce_currency(); //DOP

            if ($currencyCode == 'DOP') {
                return number_format(round($total, 3), 2);
            } else {
                return $this->exchange_to_DOP($total, $this->dolar, $currencyCode);
            }
        }


        /**
         * Get client IP Address
         **/
        // Function to get the client ip address
        function get_client_ip_server()
        {
            $ipaddress = '';
            if (isset($_SERVER['HTTP_CLIENT_IP']))
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            else if (isset($_SERVER['HTTP_X_FORWARDED']))
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            else if (isset($_SERVER['HTTP_FORWARDED']))
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            else if (isset($_SERVER['REMOTE_ADDR']))
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            else
                $ipaddress = 'UNKNOWN';

            return $ipaddress;
        }

        public function generate_cardnet_form($order_id, $gateway)
        {
            global $woocommerce;
            $order_id = cardnet_super_crypt($order_id, 'd');
            $order_id = explode('ORDERID-',  $order_id);

            if (!is_numeric($order_id[1])) return;

            $order_id = $order_id[1];

            $order = new WC_Order($order_id);
            $gateway = $gateway->settings;
            $gateway_url = 'https://lab.cardnet.com.do/authorize';

            if ($gateway['mode'] == 'live') {
                $gateway_url = 'https://ecommerce.cardnet.com.do/authorize';
                //change gateway url 
            }

            $time_stamp = date("ymdHis");
            $merchantType = $gateway['MCC_id'];
            $currencyCode = 214;

            $orderTotal = $this->get_order_total_DOP($order); //$order->get_total();
            $taxAmount  = floatval(str_replace(",", "", $orderTotal)) * floatval($gateway['tax_percentage']);

            $taxAmount = number_format((float)$taxAmount, 2, '.', '');
            $taxAmount = str_replace('.', '', $taxAmount);

            $orderTotal = str_replace('.', '', $orderTotal);
            $orderTotal = str_replace(',', '', $orderTotal);



            //Form Post Params
            //Important: The order of the following parameters are ESSENTIAL for the encryption to work.
            $arrayParams['TransactionType']          = '0200';
            $arrayParams['AcquiringInstitutionCode'] = $gateway['acquiring_code'];
            $arrayParams['BatchId']                  = '';
            $arrayParams['Amount']                   = $orderTotal;
            $arrayParams['Tax']                      = $taxAmount;
            $arrayParams['CurrencyCode']             = $currencyCode; //RD= 214, US=840 


            $transID = rand(100000, 999999);
            //Debe generar un MD5 con los valores de los campos MerchantType, MerchantNumber, MerchantTerminal, TransactionId, Amount, Tax
            $arrayParams['KeyEncriptionKey']         = $this->encryptAndEncode($merchantType,  $gateway['merchant_id'], $gateway['merchant_terminal'], $transID, $orderTotal, $taxAmount);
            $arrayParams['MerchantName']             =  $gateway['merchant_name'];
            $arrayParams['MerchantNumber']           = $gateway['merchant_id'];
            $arrayParams['MerchantTerminal']         =  $gateway['merchant_terminal'];
            $arrayParams['MerchantType']             = $merchantType;

            $arrayParams['OrdenID']                  = $order_id;
            $arrayParams['Ipclient']                 = $this->get_client_ip_server();
            $arrayParams['loteid']                 = "001";
            $arrayParams['seqid']                 = "001";




            $arrayParams['TransactionId']            = $transID;

            $arrayParams['ReturnUrl']                =  $gateway['page_result'] . '?cardnet_data=' .  cardnet_super_crypt('ORDERID-' . $order_id);

            $arrayParams['CancelUrl']                =   $gateway['page_result'] . '?cardnet_data=' .  cardnet_super_crypt('ORDERID-' . $order_id) . '&cancel=1';

            $arrayParams['PageLanguaje']             = $this->lang[explode('-', get_bloginfo('language'))[0]];



            /**3DS fields */

            $arrayParams['3DS_email']                 = $order->get_billing_email();
            $arrayParams['3DS_mobilePhone']           = $order->get_billing_phone();
            $arrayParams['3DS_billAddr_line1']        = $order->get_billing_address_1();
            $arrayParams['3DS_billAddr_line2']        = $order->get_billing_address_2();
            $arrayParams['3DS_billAddr_city']         = $order->get_billing_city();
            $arrayParams['3DS_billAddr_country']      = $order->get_billing_country();
            $arrayParams['3DS_billAddr_state']        = $order->get_billing_state();

            $arrayParams['3DS_shipAddr_line1']        = $order->get_billing_address_1();
            $arrayParams['3DS_shipAddr_line2']        = $order->get_billing_address_2();
            $arrayParams['3DS_shipAddr_city']         = $order->get_billing_city();
            $arrayParams['3DS_shipAddr_country']      =  $order->get_billing_country();
            $arrayParams['3DS_shipAddr_state']        = $order->get_billing_state();


            $cardnet_arg_array = array();
            foreach ($arrayParams as $key => $value) {
                $cardnet_arg_array[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }

            $redirectingMessage = $gateway['redirecting_message'];


            echo  '<form action="' . esc_url($gateway_url) . '" method="post" id="cardnet_payment_form">
                    ' . implode("\n", $cardnet_arg_array) . '  </form>';
?>
            <h2 style="text-align:center;font-family: Arial, sans-serif; margin: 4rem 0;"><?php echo $redirectingMessage; ?></h2>
            <script>
                document.getElementById('cardnet_payment_form').submit();
            </script>
        <?php
            die;
        }

        public  function general_admin_notice()
        {


            echo '<div class="notice notice-warning is-dismissible">
                     <p>' . __('You must place the shortcode <code> [cardnet_badget /] </code>, in the footer of your template, to validate CardNet Payments.', 'woo-cardnet-payment') . '</p>
                 </div>';
        }
        public function woo_display_order_data_in_admin($order)
        {
            $shipping_method  = $order->get_shipping_method();
            $gateway_data  = get_post_meta($order->get_id(), '_cardnet_payment_data')[0];


            $arr_fields = [];

            if (!empty($gateway_data)) {

                $arr_fields['_cardnet_payment_data'] = __('Datos de Transacción Azul', 'woo-cardnet-payment');
                $gateway_data = maybe_unserialize($gateway_data);

                foreach ((array) $gateway_data as $key => $field) {

                    if (empty($field)) continue;

                    $key = ucfirst($key);

                    if ($key == 'ResponseCode') {
                        $field .= ' - ' . $this->cardnet_status($field);
                    }

                    echo "<p><strong>$key</strong><br> $field</p>";
                }
            }
        }
        public function cardnet_status($response_code)
        {
            switch ($response_code) {
                case "00":
                    $responsetext = __("Successful Payment", 'woo-cardnet-payment');
                    break;
                case "01":
                case "02":
                case "05":
                case "08":
                    $responsetext = __("Declined, Must Call Bank.", 'woo-cardnet-payment');
                    break;

                case "03":
                    $responsetext = __("The business is invalid.", 'woo-cardnet-payment');
                    break;

                case "04":
                    $responsetext = __("Suspicious Credit Card. Call your bank.", 'woo-cardnet-payment');
                    break;

                case "07":
                    $responsetext = __("Credit card declined. Call your bank.", 'woo-cardnet-payment');
                    break;

                case "10":
                    $responsetext = __("The approval was for a partial amount, you must call the Bank.", 'woo-cardnet-payment');
                    break;

                case "11":
                    $responsetext = __("VIP Approved.", 'woo-cardnet-payment');
                    break;

                case "12":
                    $responsetext = __("The transaction is invalid. Call the merchant.", 'woo-cardnet-payment');
                    break;

                case "13":
                    $responsetext = __("The amount is invalid. Call the merchant.", 'woo-cardnet-payment');
                    break;

                case "14":
                case "42":
                    $responsetext = __("The account is invalid. Call the Bank.", 'woo-cardnet-payment');
                    break;

                case "22":
                case "23":
                case "24":
                case "25":
                case "26":
                case "27":
                case "28":
                case "29":
                case "30":
                case "31":
                case "32":
                case "33":
                case "34":
                case "35":
                case "36":
                case "37":
                case "38":
                case "41":
                case "43":
                    $responsetext = __("Unapproved Transaction.", 'woo-cardnet-payment');
                    break;

                case "39":
                    $responsetext = __("The card is invalid.", 'woo-cardnet-payment');
                    break;

                case "51":
                    $responsetext = __("Declined, insufficient funds.", 'woo-cardnet-payment');
                    break;

                case "54":
                    $responsetext = __("Declined, expired credit card.", 'woo-cardnet-payment');
                    break;

                case "62":
                    $responsetext = __("Declined, Credit card not active.", 'woo-cardnet-payment');
                    break;

                case "98":
                    $responsetext = __("Declined, Exceeds Cash Limit.", 'woo-cardnet-payment');
                    break;

                case "99":
                    $responsetext = __("CVV or CVC error.", 'woo-cardnet-payment');
                    break;

                default:
                    # code...
                    $responsetext = __("An error has occurred, you must call the merchant or the Bank.", 'woo-cardnet-payment');
                    break;
            }
            return  $responsetext;
        }

        public  function tpl_table($order_id, $status, $data)
        {

            //$view_order_link =  wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount'));
            $status_img = ($status == 'pago_exitoso') ? 'success-icon.svg' : 'warning.svg';
            $status_title = ($status == 'pago_exitoso') ? __('Successful Payment', 'woo-cardnet-payment') : __('Wrong Payment', 'woo-cardnet-payment');
            $arr_exclude_fields = [
                'AuthHash', 'Itbis',  'DateTime'
            ];

            $view_order_link =  wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount'));
            $order = wc_get_order($order_id);

        ?>
            <div class="payment-success-cardnet-details">
                <div class="wrap-icon">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/' . $status_img; ?>" alt="Status Payment" width="50" />
                    <h4 class="cardnet-title"><?php echo $status_title; ?></h4>
                </div>

                <div class="wrap-table">
                    <table class="table">
                        <?php foreach ($data as $key => $val) :


                            if (empty($val) or in_array($key, $arr_exclude_fields) !== false) continue;

                            $include_fields = ['ResponseCode',  'OrdenID', 'TransactionID',  'AuthorizationCode',  'CreditCardNumber'];

                            if (in_array($key, $include_fields) === false) continue;

                            if ($key == 'ResponseCode') {
                                $val = $val . ' - ' . $this->cardnet_status($val);
                            }

                            if ($key === 'Amount') {
                                $amount_dop = $val / 100;

                                if (get_option('woocommerce_dollar_price', 1) !== '0') {
                                    $amount_dollar = $amount_dop / get_option('woocommerce_dollar_price', 1);

                                    $val = wc_price($amount_dollar) . ' (Equivale a RD$' . number_format($amount_dop) . ')';
                                } else {
                                    $val = wc_price($val / 100);
                                }
                            }
                        ?>
                            <tr>
                                <td class="td-label"><?php echo $key ?></td>
                                <td class="td-value"><?php echo $val ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                </div>
                <div class="wrap-see-order">

                    <div>
                        <a href="<?php echo $view_order_link; ?>" class="btn-see-order"><?php _e('See Order', 'woo-cardnet-payment') ?></a><br />
                        <?php if ($status != 'pago_exitoso') : ?>
                            <a href="<?php echo $order->get_checkout_payment_url(); ?>" class="btn-see-order"><?php _e('Retry Payment', 'woo-cardnet-payment') ?></a><br />
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-two"><?php _e('Continue Shopping', 'woo-cardnet-payment') ?></a>
                </div>
                <div class="cardnet-badget">
                    <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/badget-cardnet-clear.svg'; ?>" alt="Cardnet Badget" width="200" />
                </div>
            </div>
        <?php
        }


        public  function cardnet_badget_process()
        {

            if (is_admin()) return;


            ob_start();
        ?>
            <aside class="cardnet-badget-footer">
                <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/images/badget-cardnet-clear.svg'; ?>" alt="Cardnet Badget Footer" width="200" />
            </aside>
        <?php
            $output_string = ob_get_contents();
            ob_end_clean();
            return $output_string;
        }
        public  function cardnet_woocommerce_process($atts)
        {


            if (!isset($_GET['OrderNumber'])) return;

            global $woocommerce;

            ob_start();

            if (isset($_GET['OrderNumber'])) {

                $order_id = explode('ORDERID-', cardnet_super_crypt($_GET['OrderNumber'], 'd'))[1];

                $order = wc_get_order($order_id);

                if (!$order) return;

                $status_pay =     get_post_meta($order_id, 'cardnet_pay_session', true);
                update_post_meta($order_id, '_cardnet_payment_data', maybe_serialize($_GET));
            }



            if (isset($_GET['ResponseCode']) and $_GET['ResponseCode'] == '00') {

                if ($status_pay == 'done') {
                    update_post_meta($order_id, '_billing_payment_status', 1); //  Pagado = 1,  Cancelado = 2, Rechazado = 3
                    // Mark as completed

                    $order->payment_complete();
                    WC()->cart->empty_cart();
                    $order->update_status('completed', __('Completed by user on Cardnet Payment.', 'woo-cardnet-payment'));
                    $order->add_order_note(sprintf(__('CardNET payment completed successfully. The approval number is %s. And the transaction ID is %s for card %s', 'woo-cardnet-payment'),  sanitize_text_field($_GET['AuthorizationCode']),  sanitize_text_field($_GET['TransactionID']),  sanitize_text_field($_GET['CreditCardNumber'])));
                    delete_post_meta($order_id, 'cardnet_pay_session');
                }

                echo $this->tpl_table($order_id, 'pago_exitoso', $_GET);
            } else if (!empty($_GET['Cancel']) and isset($_GET['Cancel'])) {

                WC()->cart->empty_cart();

                if ($order_id) {

                    $order = wc_get_order($order_id);

                    if ($status_pay == 'done') {

                        $order->update_status('cancelled', __('Order canceled by user in Cardnet Payment.', 'woo-cardnet-payment'));
                        delete_post_meta($order_id, 'cardnet_pay_session');
                    }
                } else {

                    _e('Wrong request.', 'woo-cardnet-payment');
                    return;
                }

                echo $this->tpl_table($order_id, 'pago_cancelado', $_GET);
            } else {


                if ($status_pay == 'done') {

                    update_post_meta($order_id, '_billing_payment_status', 3); // Pagado = 1,  Cancelado = 2, Rechazado = 3
                    WC()->cart->empty_cart();
                    delete_post_meta($order_id, 'cardnet_pay_session');
                }


                echo $this->tpl_table($order_id, 'pago_erroneo', $_GET);
            }
        ?>
        <?php
            $output_string = ob_get_contents();
            ob_end_clean();
            return $output_string;
        }

        public function plugin_scripts()
        {
            $plugin_name = 'woo_cardnet_gateway';
            /** CSS **/

            wp_register_style($plugin_name . '-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');

            //wp_register_script($plugin_name.'-js', plugin_dir_url(__FILE__) . 'assets/js/main.js', '', '', true);

            wp_enqueue_style($plugin_name . '-css');
        }
        public function check_if_has_cardnet_badget()
        {
            ob_start();
            get_footer();
            $footer = ob_get_clean();

            return has_shortcode($footer, 'cardnet_badget');
        }
        public function cardnet_wp()
        {

            $gateway = WC()->payment_gateways->payment_gateways()['wc_gateway_cardnet'];
            $this->dolar = $gateway->settings['dolar'];
            $this->page_result = $gateway->settings['page_result'];

            /*   if (!$this->check_if_has_cardnet_badget()) {
                add_action('admin_notices',  array($this, 'general_admin_notice'));
            }*/

            if (isset($_GET['cardnet_data'])) {

                $_POST['OrderNumber'] = sanitize_text_field($_GET['cardnet_data']);

                if (isset($_GET['cancel']) and $_GET['cancel'] == 1) {
                    $_POST['Cancel'] = 1;
                }
                wp_safe_redirect($this->page_result . '?' . http_build_query($_POST));
                die;
            }

            if (isset($_GET['cardnet_gtw']) and $_GET['cardnet_gtw'] == 'done' and !empty($_GET['order_id'])) {
                return $this->generate_cardnet_form(esc_html($_GET['order_id']), $gateway);
            }
        }
        public function get_value_dollar($total_org)
        {
            $total =   $this->calc_dollar($this->dolar, $total_org);
            $convert = sprintf(__('Equivalent to: <strong style="color: #00c0f3;">%s </strong> <small>Current dollar rate: <strong>%s</strong></small>', 'woo-cardnet-payment'), 'RD$' . $total, 'RD$' . $this->dolar);
            $total_org = wc_price($total_org);
            $total_org .= '<div class="cardnet-dollar-price" style="line-height:1.2;font-size:14px;">' . $convert . '</div>';
            return $total_org;
        }
        public function calc_dollar($dollar, $total)
        {
            $total =  str_replace(array("RD&#36;", ','), '', strip_tags($total));
            $total_convert = number_format($dollar * $total, 2);
            return $total_convert;
        }

        public function custom_total_message_html($value)
        {
            $value = $this->get_value_dollar(WC()->cart->total);

            return $value;
        }
    }
    new wc_cardnet_funcs_cs;

    class wc_gateway_cardnet extends WC_Payment_Gateway
    {
        public function __construct()
        {
            global $woocommerce;

            $this->id                    = 'wc_gateway_cardnet';
            $this->method_title          = __('Payments with CardNet', 'woo-cardnet-payment');
            $this->icon                  = apply_filters('wc_gateway_cardnet_icon', 'logo.png');
            $this->has_fields            = true;
            $this->method_description    = __('Payments with CardNet', 'woo-cardnet-payment');

            // Load form fields.

            $this->init_form_fields();

            // Load settings.
            $this->init_settings();

            // User variables
            $this->title                         = $this->settings['title'];
            $this->description                   = $this->settings['description'];
            // $this->private_key                   = $this->settings['private_key'];
            $this->merchant_name                 = $this->settings['merchant_name'];
            $this->mode                          = $this->settings['mode'];
            $this->merchant_id               = $this->settings['merchant_id'];
            $this->merchant_terminal             = $this->settings['merchant_terminal'];
            $this->page_result                   = $this->settings['page_result'];
            $this->dolar                    = $this->settings['dolar'];


            if (isset($this->settings['MCC_id'])) {
                $this->MCC_id                        = $this->settings['MCC_id'];
            }
            //$this->tax_percentage                = $this->settings['tax_percentage'];
            $this->redirecting_message           = $this->settings['redirecting_message'];
            //$this->fallback_currency_multiplier  = $this->settings['fallback_currency_multiplier'];
            if (isset($this->settings['acquiring_code'])) {
                $this->acquiring_code                = $this->settings['acquiring_code'];
            }


            $this->notify_url   = str_replace('https:', 'http:', add_query_arg('wc-api', 'wc_gateway_cardnet', home_url('/')));


            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            //  add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

        }

        public function pages()
        {
            $pages = get_pages();
            $pages_arr = [];
            foreach ((array) $pages as $pag) {
                $pages_arr[get_permalink($pag->ID)] =  $pag->post_title;
            }
            return  $pages_arr;
        }

        /**
         * get_icon function.
         *
         * @access public
         * @return string
         */
        public function get_icon()
        {
            global $woocommerce;

            $icon = '';
            if ($this->icon) {
                // default behavior
                $icon = '<img src="' . plugins_url('assets/images/' . $this->icon, __FILE__)  . '" alt="' . $this->title . '" />';
            } elseif ($this->cardtypes) {

                // display icons for the selected card types
                foreach ($this->cardtypes as $cardtype) {
                    if (file_exists(plugin_dir_path(__FILE__) . '/assets/images/card-' . strtolower($cardtype) . '.png')) {
                        $icon .= '<img src="' . $this->force_ssl(plugins_url('/assets/images/card-' . strtolower($cardtype) . '.png', __FILE__)) . '" alt="' . strtolower($cardtype) . '" />';
                    }
                }
            }

            return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
        }

        /**
         * Admin Panel Options
         *
         * @since 1.0.0
         */
        public function admin_options()
        {
        ?>
            <h3><?php _e('CardNET Payment Gateway', 'woo-cardnet-payment'); ?></h3>
            <p><?php _e('CardNET Payment Gateway. After a successful transaction, the order will update in WooCommerce to Processing (adding a note to the order with the Approval Number, the transaction ID and the masked credit card).', 'woo-cardnet-payment'); ?></p>
            <table class="form-table">
                <?php

                // Generate the HTML For the settings form.
                $this->generate_settings_html(); ?>
            </table>
            <!--/.form-table-->
            <p><?php _e('<br> <hr> <br>
        <div>
        Shop Currency Currently: ' . get_woocommerce_currency() . '
        </div>
                <div style="float:right;text-align:right;">
                    Made by Cardnet
                    </a>
                </div>', 'woo-cardnet-payment'); ?></p>
<?php
        } // End admin_options()

        /**
         * Initialise Gateway Settings Form Fields
         */
        public function init_form_fields()
        {
            $pages = $this->pages();

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable / Disable', 'woo-cardnet-payment'),
                    'type' => 'checkbox',
                    'label' => __('Activate Payments with CardNET', 'woo-cardnet-payment'),
                    'default' => 'yes'
                ),

                'insignia_footer_color' => array(
                    'title' => __('Badge Theme in Footer', 'woo-cardnet-payment'),
                    'type' => 'select',
                    'options' => array(
                        'normal' => 'Normal',
                        'white' => 'White'
                    ),
                    'description' => __('Indicate the color theme of the badge in the footer.', 'woo-cardnet-payment'),
                    'default' => 'normal'
                ),
                'mode' => array(
                    'title' => __('Mode', 'woo-cardnet-payment'),
                    'type' => 'select',
                    'options' => array(
                        'test' => 'Test / Development Server',
                        'live' => 'Live / Production Server'
                    ),
                    'description' => __('Select Test or Production Mode', 'woo-cardnet-payment'),
                    'default' => 'test'
                ),
                'title' => array(
                    'title' => __('Title', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('Control the title that the user sees during checkout.', 'woo-cardnet-payment'),
                    'default' => 'Pagos con CardNet',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title' => __('Description', 'woo-cardnet-payment'),
                    'type' => 'textarea',
                    'description' => __('Control the description that the user sees during checkout.', 'woo-cardnet-payment'),
                    'default' => "Pague con su tarjeta de crédito a través de la página de pago de CardNet.",
                    'desc_tip'    => true,
                ),
                'redirecting_message' => array(
                    'title' => __('Redirection Message', 'woo-cardnet-payment'),
                    'type' => 'textarea',
                    'description' => __('Message that is displayed once the buyer completes the order and is redirected to CardNet.', 'woo-cardnet-payment'),
                    'default' => "Gracias por su orden. Ahora estamos redirigiendo a la página de pago de CardNet para finalizar su pedido.",
                    'desc_tip'    => true,
                ),
                'acquiring_code' => array(
                    'title' => __('Institution Code', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('Please enter the InstitutionCode provided by CardNET.', 'woo-cardnet-payment'),
                    'default' => '349'
                ),
                'merchant_name' => array(
                    'title' => __('Merchant Name', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('Please enter the MerchantName provided by CardNET.', 'wc-carnet-paymentgateway'),
                    'default' => 'COMERCIO PARA REALIZAR PRUEBAS DO'
                ),
                'merchant_id' => array(
                    'title' => __('Merchant ID / Number', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('Please enter the Merchant Terminal provided by CardNET.', 'woo-cardnet-payment'),
                    'default' => '349000000'
                ),
                'MCC_id' => array(
                    'title' => __('MCC ID', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('Please enter the MCC or MerchantType provided by CardNET.', 'woo-cardnet-payment'),
                    'default' => '7997'
                ),
                'merchant_terminal' => array(
                    'title' => __('Merchant Terminal', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('Please enter the Merchant Terminal provided by CardNET.', 'woo-cardnet-payment'),
                    'default' => '58585858'
                ),
                'page_result' => array(
                    'title' => __('Results Page', 'woo-cardnet-payment'),
                    'type' => 'select',
                    'options' =>  $pages,
                    'desc_tip'    => true,
                    'description' => __('Indicates the page to which Cardnet will return the payment information. The page must have the shortcode: [cardnet_woocommerce /]', 'woo-cardnet-payment'),
                ),
                /*'cancel_url' => array(
                    'title' => __('Cancel URL', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('URL to redirect if user cancels payment on CardNET payment page.', 'woo-cardnet-payment'),
                    'desc_tip'    => true,
                    'default' => home_url('/my-account/orders/')
                ),*/
                /*'tax_percentage' => array(
                    'title' => __('Impuestos', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'description' => __('El porcentaje para calcular el impuesto del total de la orden. Ejemplo: 0.18', 'woo-cardnet-payment'),
                    'default' => '0.18'
                )*/
                'dolar' => array(
                    'title' => __('Dollar rate', 'woo-cardnet-payment'),
                    'type' => 'text',
                    'desc_tip'    => true,
                    'description' => __('If the currency of the store is in dollars, then enter the value of the dollar to calculate. This value will be multiplied by the total of the order.', 'woo-cardnet-payment'),
                    'default' => '58'
                )



            );
        } // End init_form_fields()

        /**
         * Not payment fields, but show the description of the payment.
         **/
        public function payment_fields()
        {
            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }
        }



        /**
         * Process the payment and return the result
         **/
        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);



            $process_url = $this->get_return_url($order);


            if ($order->get_payment_method() == 'wc_gateway_cardnet') {
                update_post_meta($order_id, 'cardnet_pay_session', 'done');

                $process_url = get_site_url() . '?cardnet_gtw=done&order_id=' . cardnet_super_crypt('ORDERID-' . $order_id);
            }

            // Return thankyou redirect
            return array(
                'result'    => 'success',
                'redirect'  => $process_url
            );
        }



        private function force_ssl($url)
        {
            if ('yes' == get_option('woocommerce_force_ssl_checkout')) {
                $url = str_replace('http:', 'https:', $url);
            }

            return $url;
        }
    }

    /**
     * Add the gateway to WooCommerce
     **/
    function add_cardnet_gateway($methods)
    {
        $methods[] = 'wc_gateway_cardnet';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'add_cardnet_gateway');
}
