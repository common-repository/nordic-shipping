<?php
if (!defined('ABSPATH')) exit;
class NODS_Common_Functions
{
    /**
     * get settings table name
     */

    public static function api_url($mode)
    {
        if ($mode == "test") {
            return "https://apitest.shipit.ax/v1/";
        } else {
            return "https://api.shipit.ax/v1/";
        }
    }

    /**
     * Get sender address from settings
     */

    public static function NODS_get_sender_address()
    {
        $result = json_decode(get_option("wc_shipit_uniwin_settings"));
        return $result;
    }

    /**
     * Check api is valid or not
     */
    public static function check_api_is_valid()
    {
        $get_api = json_decode(get_option("wc_shipit_uniwin_settings"));
        $result = isset($get_api->api_is_valid) ? $get_api->api_is_valid  : 0;
        return $result;
    }

    /**
     * Get api url from settings
     */
    public static function NODS_get_api_url()
    {
        $result = "";
        $get_api = json_decode(get_option("wc_shipit_uniwin_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($get_api->api_url) ? $get_api->api_url : "";
        }
        return $result;
    }

    /**
     * Get checkout agents enabled from settings
     */
    public static function NODS_get_checkout_is_enabled()
    {
        $result = "";
        $generalset = json_decode(get_option("wc_shipit_uniwin_general_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($generalset->show_carrier) ? $generalset->show_carrier : 0;
        }
        return $result;
    }

    /**
     * Check - Add tracking link to the order completed email enabled
     */
    public static function NODS_add_track_url_to_email()
    {
        $result = 0;
        $generalset = json_decode(get_option("wc_shipit_uniwin_general_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($generalset->add_track_to_email) ? $generalset->add_track_to_email : 0;
        }
        return $result;
    }

    /**
     * Check - Outside EU shipment enabled.
     */
    public static function outside_eu_enabled()
    {
        $result = 0;
        $customsettings = json_decode(get_option("wc_shipit_uniwin_customs_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($customsettings->outside_eu_shipment) ? $customsettings->outside_eu_shipment : 0;
        }
        return $result;
    }

    /**
     * Check - product name as description enabled
     */
    public static function product_name_desc()
    {
        $result = 0;
        $customsettings = json_decode(get_option("wc_shipit_uniwin_customs_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($customsettings->product_name_desc) ? $customsettings->product_name_desc : 0;
        }
        return $result;
    }


    /**
     * Get api key from settings
     */
    public static function NODS_get_api_key()
    {
        $result = "";
        $settings = json_decode(get_option("wc_shipit_uniwin_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($settings->api_key) ? $settings->api_key : "";
        }
        return $result;
    }

    /**
     * Get api secret key from settings
     */

    public static function NODS_get_api_secret_key()
    {
        $result = "";
        $settings = json_decode(get_option("wc_shipit_uniwin_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($settings->secret_key) ? $settings->secret_key : "";
        }
        return $result;
    }


    /**
     * Check printing document enabled in from settings
     */

    public static function printing_shiping_label()
    {
        $result = 0;
        $print = json_decode(get_option("wc_shipit_uniwin_print_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($print->print_shiping_label) ? $print->print_shiping_label : 0;
        }
        return $result;
    }

    /**
     * Check update order status to completed is enabled in from settings
     */

    public static function update_order_to_completed()
    {
        $result = 0;
        $print = json_decode(get_option("wc_shipit_uniwin_print_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($print->set_order_completed) ? $print->set_order_completed : 0;
        }
        return $result;
    }


    /**
     * get carrier agent style
     */

    public static function NODS_get_carrier_agent_style()
    {
        $result = "";
        $general_settings = json_decode(get_option("wc_shipit_uniwin_general_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($general_settings->agent_style) ? $general_settings->agent_style : "";
        }
        return $result;
    }


    /**
     * show agent on thankyou page
     */

    public static function show_agent_on_tq_page()
    {
        $result = 0;
        $general_settings = json_decode(get_option("wc_shipit_uniwin_general_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = isset($general_settings->agent_thankyou_page) ? $general_settings->agent_thankyou_page : 0;
        }
        return $result;
    }


    /**
     * Get settings info
     */
    public static function NODS_get_settings_info()
    {
        $result = "";
        $general_settings = json_decode(get_option("wc_shipit_uniwin_general_settings"));
        if (self::check_api_is_valid() == 1) {
            $result = $general_settings;
        }
        return $result;
    }

    /**
     * Parse number
     */
    public static function shipit_uniwin_return_parse_number($value, $integer = false)
    {
        $value = floatval(str_replace(',', '.', trim($value)));

        if ($integer) {
            $value = intval(round($value));
        }

        return $value;
    }


    /**
     * Price number formate
     */
    public static function shipit_uniwin_price_formate($value)
    {
        return number_format(floatval($value), 2, ',', ' ');
    }


    /**
     * Calculate checksum
     */
    public static function calculate_checksum($data)
    {
        $checksum = "";
        if (self::check_api_is_valid() == 1) {
            $key = self::NODS_get_api_secret_key();
            $checksum = hash("sha512", (json_encode($data) . $key));
        }
        return $checksum;
    }

    /**
     * Curl header
     */

    public static function curl_header($checksum)
    {
        if (self::check_api_is_valid() == 1) {
            $api_key = self::NODS_get_api_key();
            return array(
                "Content-Type" => "application/json",
                "X-SHIPIT-KEY" => $api_key,
                "X-SHIPIT-CHECKSUM" => $checksum
            );
        }
    }


    /**
     * Curl Post, Put, Get method
     */
    public static function curl_all_method($method, $checksum, $type, $postdata = NULL)
    {

        $api_url = self::NODS_get_api_url(); // Get api url
        $url = $api_url . $method;
        $header = self::curl_header($checksum);
        if ($type == "POST") {
            $args = array(
                'method' => 'POST',
                'headers' => $header,
                'body' => $postdata
            );
            $response = wp_remote_request($url, $args);
        } else if ($type == "PUT") {
            $args = array(
                'method' => 'PUT',
                'headers' => $header,
                'body' => $postdata
            );
            $response =  wp_remote_request($url, $args);
        } else if ($type == "GET") {
            $args = array(
                'headers' => $header
            );
            $response = wp_remote_get($url, $args);
        }
        if (is_array($response) && !is_wp_error($response)) {
            // Get the response body
            $body = wp_remote_retrieve_body($response);
            // Decode the JSON response
            $decoded_data = json_decode($body);
            // Check if decoding was successful
            if ($decoded_data !== null) {
                return $decoded_data;
            }
        }
    }

    /**
     * Product weight calculation
     */

    public static function weight_calculation($oid)
    {
        $order = wc_get_order($oid);
        $items = $order->get_items();
        $item_weight = 0.0;
        foreach ($items as $item) {
            $product = $item->get_product();
            $item_weight = $item_weight + !empty($product->get_weight()) ? $product->get_weight() : 0;
        }

        $weight_unit = get_option('woocommerce_weight_unit');
        if ($weight_unit == "kg") {
            $weight = $item_weight;
        } else if ($weight_unit == "g") {
            $weight = $item_weight / 1000;
        } else if ($weight_unit == "lbs") {
            $weight = $item_weight * 0.45359237;
        } else if ($weight_unit == "oz") {
            $weight = $item_weight * 0.0283495;
        }
        return $weight;
    }

    /**
     * Remove any spaces and dashes from the phone number to avoid validation errors
     */
    public static function check_phone_no($phone)
    {
        $phone = preg_replace('/\s/', '', $phone);
        $phone = str_replace('-', '', $phone);

        return $phone;
    }

    /**
     * Get  phone number from order
     */
    public static function shipit_uniwin_get_shipping_phone($order)
    {
        $_phone = get_post_meta($order->get_id(), '_shipping_phone', true);

        if (!empty($_phone)) {
            return $_phone;
        }

        return $order->get_billing_phone();
    }

    /**
     * Get  email id from order
     */
    public static function shipit_uniwin_get_shipping_email($order)
    {
        $_email = get_post_meta($order->get_id(), '_shipping_email', true);

        if (!empty($_email)) {
            return $_email;
        }

        return $order->get_billing_email();
    }

    /**
     * Fetch  label
     */
    public static function NODS_get_printing_label_data($url)
    {
        $url = str_replace('http://', 'https://', $url);

        $args = [
            'sslverify' => false,
        ];
        $args['headers'] = array(
            'Authorization' => 'Basic ' . base64_encode('shipit' . ':' . 'tulossa'),
        );
        $response = wp_remote_get($url, $args);
        if (!is_wp_error($response)) {
            return wp_remote_retrieve_body($response);
        }
        return FALSE;
    }

    /**
     * Get  label document URL
     */
    public static function document_url($orderid, $id)
    {
        return home_url() . '/wp-content/uploads/shipit-uniwin/' . $id . '.pdf';
    }

    /**
     * Mark order as completed after printing  label
     */
    public static function set_order_as_completed($order_id)
    {

        $order = wc_get_order($order_id);
        if ($order && !$order->has_status('refunded')) {
            $order->update_status('completed', esc_html__('Order status changed by Shipit after printing the  label.', 'nordic-'), true);
            do_action('woocommerce_order_edit_status', $order->get_id(), 'completed');
        }
    }
}
