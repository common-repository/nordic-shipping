<?php
// DON'T CALL THE FILE DIRECTLY USE THIS.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * common functionality
 */
include_once(plugin_dir_path(__FILE__) . 'common_functions.php');

/**
 * shipment functionality
 */
include_once(plugin_dir_path(__FILE__) . 'shipment_function.php');

class NODS_Settings
{

    //Initialize admin settings
    public function __construct()
    {
        global $wpdb;
        $this->shipit_uniwin_admin_settings();
    }

    public function shipit_uniwin_admin_settings()
    {
        /**
         * Show menu in the wordpress admin panel
         */

        add_action("admin_menu", "shipit_uniwin_menu");
        if (!function_exists('shipit_uniwin_menu')) {
            function shipit_uniwin_menu()
            {
                add_menu_page(
                    "Nordic Shipping", //page_title
                    "Nordic Shipping", //menu_title
                    "manage_options", //capability
                    "nordic-shipping-settings", //menu_slug
                    "shipit_uniwin_settings_function", //function - Load the setting form in the admin panel.
                    plugin_dir_url(__FILE__) . "images/shipit.png" //icon_url
                );
            }
        }


        /**
         * Load all the css and scripts in the wordpress admin panel 
         */

        add_action('init', 'shipit_uniwin_admin_css_scripts');
        if (!function_exists('shipit_uniwin_admin_css_scripts')) {
            function shipit_uniwin_admin_css_scripts()
            {
                wp_enqueue_style("shipit-uniwin-admin-css", plugin_dir_url(__FILE__) . 'css/nordic-shipping-admin.css', array(), '');

                wp_enqueue_script("shipit-uniwin-jquery-validator-js", plugin_dir_url(__FILE__) . 'js/jquery.validate.min.js', array('jquery'), true);

                wp_enqueue_script("shipit-uniwin-admin-js", plugin_dir_url(__FILE__) . 'js/nordic-shipping-admin.js', array('jquery'), true);

                wp_localize_script('shipit-uniwin-admin-js', 'sitesettings', array('ajaxurl' => admin_url('admin-ajax.php')));
            }
        }

        if (!function_exists('NODS_popup_inline_js')) {
            function NODS_popup_inline_js()
            {
                echo "<script type='text/javascript'>\n";
                echo 'jQuery("#wc-backbone-modal-dialog").hide();';
                echo 'jQuery("#wc-backbone-modal-dialog-carrier-agent").hide();';
                echo "\n</script>";
            }
        }
        add_action('admin_footer', 'NODS_popup_inline_js');


        /**
         * Update shipit info in woocommerce orders table in admin panel
         */
        add_filter('manage_edit-shop_order_columns', 'shipit_uniwin_custom_shop_order_column', 20);
        function shipit_uniwin_custom_shop_order_column($columns)
        {
            $reordered_columns = array();
            // Inserting columns to a specific location
            foreach ($columns as $key => $column) {
                $reordered_columns[$key] = $column;
                if ($key ==  'order_status') {
                    // Inserting after "Status" column
                    $reordered_columns['Shipit Shipment Details'] = esc_html__('Shipit Shipment Details', 'nordic-shipping');
                    $reordered_columns['Carrier Agent'] = esc_html__('Carrier Agent', 'nordic-shipping');
                    $reordered_columns['Shipit Sync'] = esc_html__('Shipit Sync', 'nordic-shipping');
                }
            }
            return $reordered_columns;
        }

        /**
         * Show shipit info in woocommerce orders table in admin panel
         */

        add_filter('manage_shop_order_posts_custom_column', 'shipit_uniwin_custom_orders_list_column', 20, 2);
        function shipit_uniwin_custom_orders_list_column($column, $post_id)
        {
            $order = wc_get_order($post_id);
            $response_id = get_post_meta($post_id, "wc_shipit_uniwin_shipment_response_id");

            switch ($column) {
                case 'Shipit Shipment Details':
                    if (!empty($response_id)) {
                        $show_response = get_post_meta($post_id, "wc_shipit_uniwin_shipment_response_" . $response_id[0]);
                        if (!empty($show_response)) {
                            $response = $show_response[0];
                            $jsondata = json_decode($response['shipit_response'], true);
                            if (isset($jsondata['status']) && ($jsondata['status'] == 1)) {
                                echo '<p>Status : <b>Success</b></p>';
                                if (!empty($response['carrier_name'])) {
                                    echo '<p>Selected Shipment : <b>' . esc_html($response['carrier_name']) . '</b></p>';
                                }
                                echo '<p>Tracking No: <b>' . $jsondata['trackingNumber'] . '</b></p>';
                                if (isset($jsondata['proforma']) && !empty($jsondata['proforma'])) {
                                    $custom_docs = get_post_meta($post_id, 'shipit_uniwin_custom_docs');
                                    if (!empty($custom_docs)) {
                                        $custom_docs_key = "shipit_uniwin_shipping_label_data_" . $custom_docs[0];
?>
                                        <p><?php echo $order->get_meta($custom_docs_key) ? '<a class="button button-primary" download="' . $post_id . '-customDocs" href="data:application/pdf;base64,' . $order->get_meta($custom_docs_key) . '">Customs Docs</a>' : ''; ?></p>
                <?php
                                    }
                                }
                                echo '<a class="button button-primary" target="_blank" href="' . $jsondata['freightDoc'][0] . '"><b>Label</b></a>';
                                echo '<a class="button button-primary" style="margin-left: 16px;" target="_blank" href="' . esc_html($jsondata['trackingUrls'][0]) . '"><b>Track</b></a>';
                            } else {
                                echo '<p>Status : <b>Fail</b></p>';
                                if (!empty($jsondata['error'])) {
                                    echo '<p>Error : <b>' . esc_html($jsondata['error']['message']) . '</b></p>';
                                }
                                echo !empty($jsondata['errorbag']) ? '<p>' . implode(',', $jsondata['errorbag']) . '</p>' : "";
                            }
                        }
                    }
                    break;
                case 'Carrier Agent':
                    $agent_meta_data = get_post_meta($post_id, '_shipit_uniwin_carrier_agent_data');
                    if (!empty($agent_meta_data)) {
                        $agent_data = json_decode($agent_meta_data[0], true);
                        $address_name = $agent_data['carrier'] . " - " . html_entity_decode($agent_data['name'], ENT_QUOTES, 'UTF-8') . " , " . $agent_data['address1'];
                        //echo "<img width='40px' src='" . esc_html($agent_data['carrierLogo']) . "'/>";
                        echo "<p>" . $address_name . "," . esc_html($agent_data['city']) . "," . esc_html($agent_data['zipcode']) . "</p>";
                    }
                    break;
                case 'Shipit Sync':
                    $added = !empty($show_response) ? 1 : 0;
                    echo "<button class='button button-primary shipit-uniwin-auto-sync' type='button' data-added='" . $added . "' data-id='" . $post_id . "'>Sync</button>";
                    break;
            }
        }



        /**
         * Load the setting form in the admin panel.
         */

        if (!function_exists('shipit_uniwin_settings_function')) {
            function shipit_uniwin_settings_function()
            {
                include_once(plugin_dir_path(__FILE__) . "settings_form.php");
            }
        }

        /**
         * Check if given api key is valid or not  using curl method while submit the address settings form
         */
        function NODS_api_is_valid($data)
        {

            $api_url = NODS_Common_Functions::api_url($data['unishipit_api_mode']);
            $checksum = hash("sha512", json_encode(array()) . $data['unishipit_api_secret']);
            $url =  $api_url . 'list-methods';
            $args = array(
                'headers' => array(
                    "X-SHIPIT-KEY" => $data['unishipit_api_key'],
                    "X-SHIPIT-CHECKSUM" => $checksum
                )
            );
            $response = wp_remote_get($url, $args);
            $http_code = wp_remote_retrieve_response_code($response);
            return $http_code;
        }

        /**
         * Check if given api key is valid or not  using curl method while submit the address settings form
         */
        function NODS_new_user_registration($newdata)
        {
            $method =  'register';
            $key = "6Mt1yIapz33QzddyYFKKUYGXEGZopmTX";
            $checksum = hash("sha512", (json_encode($newdata) . $key));
            $api_url = "https://apitest.shipit.ax/v1/"; // Get api url
            $url = $api_url . $method;
            $api_key = "NsN/ufJmMOUJSarH";
            $response = wp_remote_request(
                $url,
                array(
                    'body'    => json_encode($newdata), // Assuming you are sending JSON data
                    'headers' => array(
                        "Content-Type" => "application/json",
                        "X-SHIPIT-KEY" => $api_key,
                        "X-SHIPIT-CHECKSUM" => $checksum
                    ),
                    'method'    => 'PUT'
                )
            );
            $http_code = wp_remote_retrieve_response_code($response);
            if (is_array($response) && !is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
            } else {
                $body = $response;
            }
            return array("http_code" => $http_code, "response" => $body);
        }

        /**
         * Adding Meta container admin shop_order pages.
         */

        add_action('add_meta_boxes', 'shipit_uniwin_order_add_meta_boxes');
        if (!function_exists('shipit_uniwin_order_add_meta_boxes')) {
            function shipit_uniwin_order_add_meta_boxes()
            {
                add_meta_box('mv_other_fields', esc_html__('Nordic Shipping', 'nordic-shipping'), 'shipit_uniwin_add_other_fields_for_packaging', 'shop_order', 'side', 'core');
            }
        }


        /**
         * Adding Meta field in the meta container admin shop_order pages
         */

        if (!function_exists('shipit_uniwin_add_other_fields_for_packaging')) {
            function shipit_uniwin_add_other_fields_for_packaging()
            {
                global $post;
                if (NODS_Common_Functions::NODS_get_api_key()) {
                    //Calculate weight based on weight unit
                    $weight = NODS_Common_Functions::weight_calculation($post->ID);

                    include_once(plugin_dir_path(__FILE__) . "meta_box_html.php");
                } else {
                    echo "<h2 class='error'>Please update the api credentials in settings tab.<a href='" . home_url() . "/wp-admin/admin.php?page=shipit-settings'>-> Go to settings tab</a></h2>";
                }
            }
        }

        /**
         * Save the data of the Meta field
         */

        add_action('save_post', 'shipit_uniwin_save_wc_order_meta_box_fields', 10, 1);
        if (!function_exists('shipit_uniwin_save_wc_order_meta_box_fields')) {

            function shipit_uniwin_save_wc_order_meta_box_fields($post_id)
            {

                $uniwin_shipit_save_button_name = isset($_POST['uniwin_shipit_save']) ? sanitize_text_field($_POST['uniwin_shipit_save']) : '';
                if (!empty($uniwin_shipit_save_button_name)) {
                    // Check if our nonce is set.
                    if (!isset($_POST['mv_other_meta_field_nonce'])) {
                        return $post_id;
                    }
                    $nonce = sanitize_text_field($_POST['mv_other_meta_field_nonce']);

                    //Verify that the nonce is valid.
                    if (!wp_verify_nonce($nonce, 'meta_box')) {
                        return $post_id;
                    }
                    // Check the user's permissions.
                    if ('page' == sanitize_text_field($_POST['post_type'])) {

                        if (!current_user_can('edit_page', $post_id)) {
                            return $post_id;
                        }
                    } else {
                        if (!current_user_can('edit_post', $post_id)) {
                            return $post_id;
                        }
                    }
                    // --- Its safe for us to save the data ! --- //


                    //create shipment api 
                    $order = wc_get_order($post_id);
                    $shipment_array = [
                        'oid' => $post_id,
                        'uniwin_shipit_country' => sanitize_text_field($_POST['uniwin_shipit_country']),
                        'uniwin_shipit_parcel' => sanitize_text_field($_POST['uniwin_shipit_parcel']),
                        'uniwin_shipit_weight' => sanitize_text_field($_POST['uniwin_shipit_weight']),
                        'uniwin_shipit_width' => sanitize_text_field($_POST['uniwin_shipit_width']),
                        'uniwin_shipit_height' => sanitize_text_field($_POST['uniwin_shipit_height']),
                        'uniwin_shipit_length' => sanitize_text_field($_POST['uniwin_shipit_length']),
                        'uniwin_shipit_service' => sanitize_text_field($_POST['uniwin_shipit_service']),
                        'uniwin_shipit_label' => isset($_POST['uniwin_shipit_label']) ? sanitize_text_field($_POST['uniwin_shipit_label']) : "",
                        'uniwin_shipit_order_confirm' => isset($_POST['uniwin_shipit_order_confirm']) ? sanitize_text_field($_POST['uniwin_shipit_order_confirm']) : "",
                        'uniwin_shipit_fragile' => isset($_POST['uniwin_shipit_fragile']) ? sanitize_text_field($_POST['uniwin_shipit_fragile']) : "",
                        'uniwin_shipit_pickup' => sanitize_text_field($_POST['uniwin_shipit_pickup'])
                    ];

                    $response = (array) NODS_Shipment_Functions::create_shipment($shipment_array);
                    // Sanitize user input and update the field in the database.
                    //$carrier_name = NODS_Shipment_Functions::all_shipit_services
                    $carrier_name = NODS_Shipment_Functions::nods_service_carrier_title(sanitize_text_field($_POST['uniwin_shipit_service']), sanitize_text_field($_POST['uniwin_shipit_country']));
                    $data = array(
                        'package_data' => json_encode($shipment_array),
                        'created_at' => date('Y-m-d H:i:s'),
                        'order_id' => $post_id,
                        'shipit_response' => json_encode($response),
                        'status' => $response['status'],
                        'carrier_name' => $carrier_name
                    );
                    //Add shipment response to order meta.
                    $unique_key = 1;
                    if (!empty(get_post_meta($post_id, "wc_shipit_uniwin_shipment_response_id"))) {

                        $unique_key = get_post_meta($post_id, "wc_shipit_uniwin_shipment_response_id")[0] + 1;
                        update_post_meta($post_id, 'wc_shipit_uniwin_shipment_response_id', $unique_key);
                    } else {
                        add_post_meta($post_id, 'wc_shipit_uniwin_shipment_response_id', $unique_key, true);
                    }

                    add_post_meta($post_id, 'wc_shipit_uniwin_shipment_response_' . $unique_key, $data, true);
                    if ($response['status'] == 1) {

                        $labels = array();
                        $printing_shiping_label = NODS_Common_Functions::printing_shiping_label();

                        if ($printing_shiping_label == 1) {
                            // Freight docs
                            $pritinglabels = $response['freightDoc'];
                            foreach ($pritinglabels as $url) {
                                $label = NODS_Common_Functions::NODS_get_printing_label_data($url);
                                if ($label) {
                                    $labels[] = array(
                                        'type' => 'label',
                                        'contents' => $label,
                                    );
                                }
                            }
                            // Proforma
                            if (isset($response['proforma']) && !empty($response['proforma'])) {
                                $proforma = NODS_Common_Functions::NODS_get_printing_label_data($response['proforma']);
                                if ($proforma) {
                                    $labels[] = array(
                                        'type' => 'proforma',
                                        'contents' => $proforma,
                                    );
                                }
                            }
                        }

                        if (!empty($labels)) {
                            // Save labels to the filesystem
                            $i = 1;
                            $documents = array();
                            foreach ($labels as $label) {
                                $document_id = sprintf(
                                    esc_html("%s-%d"),
                                    esc_html($response['orderId']),
                                    esc_html($i)
                                );
                                $key = 'shipit_uniwin_shipping_label_data_' . $document_id;
                                if ($label['type'] == "proforma") {
                                    $order->update_meta_data('shipit_uniwin_custom_docs', $document_id);
                                } else {
                                    $order->update_meta_data('shipit_uniwin_shipping_label', $document_id);
                                }

                                $order->update_meta_data($key, base64_encode($label['contents']));
                                $order->save();
                                // NODS_Common_Functions::NODS_save_printing_label_document($label['contents'], $document_id);                              
                                $documents[] = array(
                                    'id' => $document_id,
                                    'type' => $label['type'],
                                );
                                $i++;
                            }
                        }
                        $update_order_to_completed = NODS_Common_Functions::update_order_to_completed();
                        if ($update_order_to_completed == 1) {
                            // update order status
                            NODS_Common_Functions::set_order_as_completed($post_id);
                        }
                        // Print shipping label immediately after creating it
                        if ($printing_shiping_label == 1) {
                            // $redirect = NODS_Common_Functions::document_url($post_id, $documents[0]['id']);
                            // header('Location:' . $redirect);
                            // exit();
                        }
                    }
                }
            }
        }

        /**
         * shipit service price
         */

        add_action('wp_ajax_NODS_shipit_services_price', 'NODS_shipit_services_price');
        add_action('wp_ajax_nopriv_NODS_shipit_services_price', 'NODS_shipit_services_price');

        if (!function_exists('NODS_shipit_services_price')) {
            function NODS_shipit_services_price()
            {

                /**
                 * Fetching prices for services
                 */
                if (!current_user_can('manage_woocommerce')) {
                    die('Permission denied');
                }
                $nonce = sanitize_text_field($_POST['nonce']);

                if (isset($nonce) && wp_verify_nonce($nonce, 'meta_box')) {
                    // Nonce verification succeeded, process form data
                    $data = array(
                        'order_id' => sanitize_text_field($_POST['order_id']),
                        'weight' => sanitize_text_field($_POST['weight']),
                        'height' => sanitize_text_field($_POST['height']),
                        'width' => sanitize_text_field($_POST['width']),
                        'length' => sanitize_text_field($_POST['length']),
                        'parcels' => sanitize_text_field($_POST['parcels']),
                        'fragile' => !empty(sanitize_text_field($_POST['fragile'])) ? true : false
                    );

                    $methods = NODS_Shipment_Functions::shipment_service_price($data);

                    include 'admin_price_html.php';

                    die;
                } else {
                    // Nonce verification failed, handle the error
                    die('Nonce verification failed');
                }
            }
        }




        /**
         * shipit agent retrive
         */

        add_action('wp_ajax_NODS_shipit_agent_data', 'NODS_shipit_agent_data');
        add_action('wp_ajax_nopriv_NODS_shipit_agent_data', 'NODS_shipit_agent_data');

        if (!function_exists('NODS_shipit_agent_data')) {
            function NODS_shipit_agent_data()
            {

                /**
                 * Fetching prices for services
                 */
                if (!current_user_can('manage_woocommerce')) {
                    die('Permission denied');
                }

                $nonce = sanitize_text_field($_POST['nonce']);


                if (isset($nonce) && wp_verify_nonce($nonce, 'meta_box')) {

                    $data = array(
                        'order_id' => sanitize_text_field($_POST['order_id']),
                        'service_id' => sanitize_text_field($_POST['service_id']),
                        'postal_code' => sanitize_text_field($_POST['postal_code']),
                        'country' => sanitize_text_field($_POST['country']),
                        'instance_id' => sanitize_text_field($_POST['instance_id']),
                    );
                    $methods = NODS_Shipment_Functions::shipment_carrier_agent_data($data);

                    include 'carrier_agent_html.php';

                    die;
                } else {
                    die('Error: Nonce verification failed!');
                }
            }
        }


        /**
         * save agent data that changed by admin in the popup
         */

        add_action('wp_ajax_NODS_save_shipit_agent_data', 'NODS_save_shipit_agent_data');
        add_action('wp_ajax_nopriv_NODS_save_shipit_agent_data', 'NODS_save_shipit_agent_data');
        if (!function_exists('NODS_save_shipit_agent_data')) {
            function NODS_save_shipit_agent_data()
            {

                /**
                 * Fetching prices for services
                 */
                if (!current_user_can('manage_woocommerce')) {
                    die('Permission denied');
                }

                $nonce = sanitize_text_field($_POST['nonce']);

                if (isset($nonce) && wp_verify_nonce($nonce, 'meta_box')) {
                    $agent_data = [
                        'order_id' => sanitize_text_field($_POST['order_id']),
                        'agent_id' => sanitize_text_field($_POST['agent_id']),
                        'agent_data_id' => sanitize_text_field($_POST['agent_data_id']),
                        'agent_data_name' => htmlentities(sanitize_text_field($_POST['agent_data_name']), ENT_QUOTES, 'UTF-8'),
                        'agent_data_address1' => sanitize_text_field($_POST['agent_data_address1']),
                        'agent_data_zipcode' => sanitize_text_field($_POST['agent_data_zipcode']),
                        'agent_data_city' => sanitize_text_field($_POST['agent_data_city']),
                        'agent_data_countryCode' => sanitize_text_field($_POST['agent_data_countryCode']),
                        'agent_data_serviceId' => sanitize_text_field($_POST['agent_data_serviceId']),
                        'agent_data_carrier' => sanitize_text_field($_POST['agent_data_carrier']),
                        'agent_data_carrierLogo' => sanitize_text_field($_POST['agent_data_carrierLogo'])
                    ];

                    $response = NODS_Shipment_Functions::shipment_carrier_agent_save_data($agent_data);
                    wp_send_json($response);
                } else {
                    die('Nonce verification failed!');
                }
            }
        }



        /**
         * List the available services based on sender and receiver address
         */

        add_action('wp_ajax_NODS_shipit_services_for_customer', 'NODS_shipit_services_for_customer');
        add_action('wp_ajax_nopriv_NODS_shipit_services_for_customer', 'NODS_shipit_services_for_customer');

        if (!function_exists('NODS_shipit_services_for_customer')) {

            function NODS_shipit_services_for_customer()
            {
                $data = array(
                    'post_id' => sanitize_text_field($_POST['post_id']),
                    'weight' => sanitize_text_field($_POST['weight']),
                    'length' => sanitize_text_field($_POST['length']),
                    'height' => sanitize_text_field($_POST['height']),
                    'width' => sanitize_text_field($_POST['width'])
                );
                $response = NODS_Shipment_Functions::receiver_shipment_service_list($data);
                wp_send_json($response);
            }
        }


        /**
         * Error Notification
         */
        add_action('admin_notices', 'NODS_my_error_notice', 10, 1);
        function NODS_my_error_notice($msg)
        {
            if (!empty($msg)) { ?>
                <div class=" error notice is-dismissible">
                    <p><?php echo $msg; ?></p>
                </div>
            <?php
            }
        }



        /**
         * Update Notification
         */
        add_action('admin_update_notices', 'NODS_my_update_notice', 10, 1);
        function NODS_my_update_notice($msg)
        {
            if (!empty($msg)) {
            ?>
                <div class="updated notice is-dismissible">
                    <p><?php echo $msg; ?></p>
                </div>
            <?php
            }
        }



        /**
         * Get all shipping zone for mapping shipment service
         */

        function shipit_uniwin_get_all_shipping_zones()
        {
            $data_store = WC_Data_Store::load('shipping-zone');
            $raw_zones = $data_store->get_zones();
            if (!empty($raw_zones)) {
                foreach ($raw_zones as $raw_zone) {
                    $zones[] = new WC_Shipping_Zone($raw_zone);
                }
                return $zones;
            }
        }

        /**
         * Get all shipping methods for mapping shipment service
         */

        add_filter('woocommerce_shipping_methods', 'NODS_add_Shipit_shipping');
        function NODS_add_Shipit_shipping($methods)
        {
            $methods['Shipit_shipping'] = 'Shipit_shipping_Method';
            return $methods;
        }

        /**
         * show shipit agent serivce option in shipping method.
         */

        add_action('woocommerce_shipping_init', 'Shipit_shipping_method');
        function Shipit_shipping_method()
        {
            class Shipit_shipping_Method extends WC_Shipping_Method
            {

                public function __construct($instance_id = 0)
                {
                    $selected_sender_country = "";
                    $hideclass_cond_fi = "wc-enhanced-select nods_shipit_service_ids_in_woos hide";
                    $hideclass_cond_se = "wc-enhanced-select nods_shipit_service_ids_in_woos hide";
                    $result = json_decode(get_option("wc_shipit_uniwin_settings"));
                    if (!empty($result)) {
                        if ($result->country == "FI" || $result->country == "SE") {
                            $selected_sender_country = $result->country;
                        }
                        if ($selected_sender_country == "FI") {
                            $hideclass_cond_fi = "wc-enhanced-select nods_shipit_service_ids_in_woos";
                        } else if ($selected_sender_country == "SE") {
                            $hideclass_cond_se = "wc-enhanced-select nods_shipit_service_ids_in_woos";
                        }
                    }
                    $this->id = 'Shipit_shipping';
                    $this->instance_id = absint($instance_id);
                    $this->domain = 'Shipit_shipping';
                    $this->method_title = esc_html__('Shipit shipping', 'nordic-shipping');
                    $this->title = esc_html__('Shipit shipping', 'nordic-shipping');
                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal',
                    );

                    $cost_desc = esc_html__('Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'woo-carrier-agents') . '<br/><br/>' . esc_html__('Use <code>[qty]</code> for the number of items.', 'woo-carrier-agents');

                    $get_checkout_is_enabled = NODS_Common_Functions::NODS_get_checkout_is_enabled();
                    if ($get_checkout_is_enabled == 1) {

                        $this->instance_form_fields = array(
                            'title' => array(
                                'title'       => esc_html__('Title', 'nordic-shipping'),
                                'type'        => 'text',
                                'description' => esc_html__('This controls the title which the user sees during checkout.', 'nordic-shipping'),
                                'default'     => 'Shipit Shipping',
                                'desc_tip'    => true,
                            ),
                            'tax_status' => array(
                                'title'         => esc_html__('Tax Status', 'nordic-shipping'),
                                'type'             => 'select',
                                'class'         => 'wc-enhanced-select',
                                'default'         => 'taxable',
                                'options'        => array(
                                    'taxable'     => esc_html__('Taxable', 'nordic-shipping'),
                                    'none'         => _x('None', 'Tax status', 'nordic-shipping')
                                )
                            ),
                            'cost' => array(
                                'title'         => esc_html__('Cost', 'nordic-shipping'),
                                'type'             => 'text',
                                'placeholder'    => '0',
                                'description'    => $cost_desc,
                                'default'        => '',
                                'desc_tip'        => true,
                                'class'     => 'wpa-field-fixed-rate',
                            ),
                            'country_region' => array(
                                'title'         => esc_html__('Shipping from', 'nordic-shipping'),
                                'type'             => 'select',
                                'class'         => 'wc-enhanced-select nods_shipit_country_in_woos',
                                'default'         => $selected_sender_country,
                                'options'        => array(
                                    '' => 'Select Country',
                                    'FI' => 'Finland',
                                    'SE' => 'Sweden'
                                )
                            ),
                            'service_ids' => array(
                                'title'         => esc_html__('Available shipping services for the above sender country', 'nordic-shipping'),
                                'type'             => 'multiselect',
                                'class'         => $hideclass_cond_fi,
                                'default'         => '',
                                'options'        => array(
                                    "mh.mh3050" => "Matkahuolto - Jakopakett",
                                    "mh.mh80" => "Matkahuolto - Lähellä-paketti",
                                    "mh.mh84" => "Matkahuolto - XXS-Paketti",
                                    "mh.mh34" => "Matkahuolto - Kotijakelu",
                                    "mh.mh97" => "Matkahuolto - Euroopan Kotijakelu (Home delivery to europe)",
                                    "mh.mh96" => "Matkahuolto - Euroopan Jakopaketti (door delivery for companies)",
                                    "mh.mh95" => "Matkahuolto - Euroopan Lähellä-paketti (delivery to pickup points in Europe)",
                                    "glsfi.glsfiebp" => "GLS - EuroBusinessParcel",
                                    "posti.po2103" => "Posti - Postipaketti",
                                    "posti.po2104" => "Posti - Kotipaketti",
                                    "posti.po2461" => "Posti - Pikkupaketti",
                                    "posti.po2711" => "Posti - Parcel Connect",
                                    "posti.po2102" => "Posti - Express",
                                    "posti.itpr" => "Posti - Priority",
                                    "posti.po2017" => "Posti - EMS",
                                    "posti.it14i" => "Posti - Express (international)",
                                    "posti.po2351" => "Posti - PickUp Parcel",
                                    "posti.po2331" => "Posti - Postal Parcel Baltics",
                                    "itellalog.pof1" => "Posti - Freight",
                                    "posti.po2711ee" => "Itella - Parcel Connect Baltics",
                                    "posti.it14iee" => "Itella - Posti Business Day Parcel Baltics",
                                    "posti.itky14iee" => "Itella - Posti Business Day Pallet Baltics",
                                    "jakeluyhtio_suomi.pienpaketti" => "Jakeluyhtiö Suomi (JYS) - Pienpaketti",
                                    "sbtlfirrex.sbtlfirrex" => "DB Schenker - Noutopistepaketti",
                                    "kl.klgrp" => "DB Schenker - DB SCHENKERsystem",
                                    "sbtlfiexp.sbtlfiexp" => "DB Schenker - DB SCHENKERparcel",
                                    "ups.upsexpdtp" => "UPS - Expedited",
                                    "ups.upsexpp" => "UPS - Express",
                                    "ups.upssavp" => "UPS - Express Saver",
                                    "ups.upsstdp" => "UPS - Standard",
                                    "plscm.p19fi" => "Postnord - MyPack",
                                    "plscm.p17fidpd" => "Postnord - MyPack Home(for Europe)", // siva changes
                                    "plscm.p17fi" => "Postnord - MyPack Home",
                                    "fedex.fdxiep" => "Fedex - Economy",
                                    "fedex.fdxipp" => "Fedex - Express",
                                    "omni.omnice" => "Omniva - Pickup Point Delivery",
                                    "omni.omniparcelmachine" => "Omniva - Omniva Parcelmachine",
                                    "omni.omniqk" => "Omniva - Courier Delivery",
                                    "omni.omnixn" => "Omniva - International Maxi Letter",
                                    "dpd.dindpdbaltpickup" => "DPD Baltic - DPD Parcelmachine and Pickup Point",
                                    "dpd.dpdeeclassic" => "DPD Baltic - DPD Classic",
                                    "dpd.dpdbaltpriv" => "DPD Baltic - DPD Private",
                                    "dpd.dpdeeclassicpallet" => "DPD Baltic - DPD Classic Pallet",
                                    "dpd.dpdbaltprivpallet" => "DPD Baltic - DPD Private Pallet"
                                )
                            ),
                            'se_service_ids' => array(
                                'title'         => esc_html__('Available shipping services for the above sender country', 'nordic-shipping'),
                                'type'             => 'multiselect',
                                'class'         => $hideclass_cond_se,
                                'default'         => '',
                                'options'        => array(
                                    "airmee.airmee" => "Airmee - Airmee",
                                    "airmee.airmeelo" => "Airmee - Airmee Locker",
                                    "airmee.airmeepo" => "Airmee - Airmee Point",
                                    "bcmse.bcmseblp" => "Citymail - Citymail Brevlådepaket",
                                    'bcmse.bcmseblpprio' => "Citymail - Citymail Brevlådepaket Priority",
                                    'budbee.budbeebox' => "Budbee - Budbee Box",
                                    "budbee.budbeedlvday" => "Budbee - Budbee Hemleverans Dagtid",
                                    "budbee.budbeedlvevn" => "Budbee - Budbee Flex",
                                    "budbee.budbeeexpress" => "Budbee - Budbee Express Delivery",
                                    "budbee.budbeesmd" => "Budbee - Budbee Same Day",
                                    "budbee.budbeestd" => "Budbee - Budbee",
                                    "cg.cgb" => "Bussgods - Bussgods",
                                    "dhlroad.aex" => "DHL Freight - DHL Paket",
                                    "dhlroad.apc" => "DHL Freight - DHL Parcel Connect",
                                    "dhlroad.asp2" => "DHL Freight - DHL Pall",
                                    "dhlroad.aspo" => "DHL Freight - DHL Service Point",
                                    "dhlroad.aspor" => "DHL Freight - DHL Service Point Return",
                                    "dhlroad.aswh2" => "DHL Freight - DHL Home Delivery",
                                    "fedex.fdxiep" => "FedEx - International Economy",
                                    "fedex.fdxiepf" => "FedEx - International Economy Freight",
                                    "fedex.fdxipd" => "FedEx - International Priority Docs",
                                    "fedex.fdxipp" => "FedEx - International Priority",
                                    "instabox.instaboxstd" => "Instabox - Instabox",
                                    "pbrev.pua" => "Postnord Brev - PostNord - Varubrev",
                                    "plab.p17" => "Postnord Sweden - MyPack Home",
                                    "plab.p18" => "Postnord Sweden - PostNord Parcel",
                                    "plab.p19" => "Postnord Sweden - MyPack Collect",
                                    "plab.p52" => "Postnord Sweden - Postnord Pallet Sverige",
                                    "pbrev.p34" => "PostNord - Spårbart brev utrikes",
                                    "pnl.pnl330" => "Bring - Bring Business Parcel",
                                    "pnl.pnl340" => "Bring - Bring PickUp Parcel",
                                    "posti.po2351" => "Posti - PickUp Parcel",
                                    "sbtl.bhp" => "DB Schenker - DB SCHENKERparcel Ombud",
                                    "sbtl.bpa" => "DB Schenker - DB SCHENKERparcel",
                                    "sbtl.bphdap" => "DB Schenker - DB SCHENKERparcel Hem dag med kvittens (Paket)",
                                    "sbtl.bphdp" => "DB Schenker - DB SCHENKERparcel Hem dag utan kvittens",
                                    "sbtl.bphkap" => "DB Schenker - DB SCHENKERparcel Hem kväll med kvittens",
                                )
                            ),
                        );
                    } else {
                        $this->instance_form_fields = array(
                            'title' => array(
                                'title'       => esc_html__('Title', 'nordic-shipping'),
                                'type'        => 'text',
                                'description' => esc_html__('This controls the title which the user sees during checkout.', 'nordic-shipping'),
                                'default'     => 'Shipit Shipping',
                                'desc_tip'    => true,
                            ),
                            'tax_status' => array(
                                'title'         => esc_html__('Tax Status', 'nordic-shipping'),
                                'type'             => 'select',
                                'class'         => 'wc-enhanced-select',
                                'default'         => 'taxable',
                                'options'        => array(
                                    'taxable'     => esc_html__('Taxable', 'nordic-shipping'),
                                    'none'         => _x('None', 'Tax status', 'nordic-shipping')
                                )
                            ),
                            'cost' => array(
                                'title'         => esc_html__('Cost', 'nordic-shipping'),
                                'type'             => 'text',
                                'placeholder'    => '0',
                                'description'    => $cost_desc,
                                'default'        => '',
                                'desc_tip'        => true,
                                'class'     => 'wpa-field-fixed-rate',
                            ),
                        );
                    }
                    $this->enabled = $this->get_option('enabled');
                    $this->title = $this->get_option('title');
                    $this->tax_status = $this->get_option('tax_status');
                    $this->cost = $this->get_option('cost');

                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                public function calculate_shipping($package = array())
                {
                    $this->add_rate(array(
                        'id'    => $this->id . $this->instance_id,
                        'label' => $this->title,
                        'cost'  => $this->cost,
                    ));
                }
            }
        }


        /**
         * This function is useful for retrieving the name of a shipping method based on its ID.*
         */

        function NODS_shipping_name_by_id($chosen_shipping_methods)
        {
            if (preg_match("/\d+/", $chosen_shipping_methods, $instance_id)) {
                $shipping_method = WC_Shipping_Zones::get_shipping_method($instance_id[0]);
                return $shipping_method->get_title();
            }
            return null;
        }



        /**
         * The is a custom function for WooCommerce that adds carrier agents on the checkout page. It first checks if the checkout feature is enabled and then proceeds to check if the current page is the cart page. If it is, the function returns and does not proceed further.  Next, it retrieves the chosen shipping method and its instance ID, method ID, and shipping method instance. It then fetches the service IDs from the shipping method instance settings.  Using the customer's shipping postcode and country, the function sends a request to the API to get the locations of the carrier agents. It then creates a list of options for the carrier agents based on the response received. Finally, it displays the carrier agents as either a dropdown list (select) or radio buttons, depending on the agent style specified in the settings. The function also adds hidden input fields for each carrier agent's location details.This function is hooked to the 'woocommerce_after_shipping_rate' action, which means it will be executed after the shipping rate is displayed on the checkout page.*
         */

        add_action('woocommerce_after_shipping_rate', 'NODS_add_carrier_agents_on_checkout', 20, 2);

        function NODS_add_carrier_agents_on_checkout($method, $index)
        {
            $get_checkout_is_enabled = NODS_Common_Functions::NODS_get_checkout_is_enabled();
            if ($get_checkout_is_enabled == 1) {
                // Targeting checkout page only:
                if (is_cart()) return; // Exit on cart page

                $instance_id = "";
                $method_id = "";
                $shipping_method_instance = "";

                $chosen_shipping_methods = WC()->session->get("chosen_shipping_methods");
                if ($chosen_shipping_methods[0] === $method->id) {
                    $instance_id = $method->instance_id;
                    $method_id   = $method->method_id; // The shipping method slug
                    $shipping_method_instance = "woocommerce_" . $method_id . "_" . $instance_id . "_settings";
                    $service_ids = get_option($shipping_method_instance);

                    global $woocommerce;
                    if (isset($service_ids['service_ids']) || isset($service_ids['se_service_ids'])) {
                        $choosen_service_ids = "";
                        if (!empty($service_ids['se_service_ids'])) {
                            if (count($service_ids['se_service_ids']) > 0) {
                                $choosen_service_ids = $service_ids['se_service_ids'];
                            }
                        } else  if (!empty($service_ids['service_ids'])) {
                            if (count($service_ids['service_ids']) > 0) {
                                $choosen_service_ids = $service_ids['service_ids'];
                            }
                        }

                        if (!empty($choosen_service_ids)) {
                            $postcode = $woocommerce->customer->get_shipping_postcode();
                            $country = $woocommerce->customer->get_shipping_country();
                            if (!empty($postcode) && !empty($country)) {
                                $body = [
                                    'postcode' => $postcode,
                                    'country' => $country,
                                    'serviceId' => $choosen_service_ids,
                                    'type' => "PICKUP"
                                ];
                                $api_url = 'agents';
                                $checksum = NODS_Common_Functions::calculate_checksum($body);
                                $postdata = json_encode($body);
                                $response = (array) NODS_Common_Functions::curl_all_method($api_url, $checksum, "POST", $postdata);
                                if ($response['status'] == 1 && isset($response['locations'])) {
                                    $locationarray = (array) $response['locations'];
                                    $options = [];
                                    foreach ($locationarray as $locations) {
                                        $location = (array) $locations;
                                        $address_name = $location['carrier'] . " - " . $location['name'] . " , " . $location['address1'];
                                        $options[$location['id']] = $address_name;
                                        foreach ($location as $key => $loc) {
                                            if ($key == "openingHours") {
                                                $loc = (array) $loc;
                                                break;
                                            }
                                            $hidden_name = "carrier_agents:" . $instance_id . ":" . $location['id'] . ":" . $key;
                                            echo "<input type='hidden' name='" . $hidden_name . "' value='" . $loc . "'/>";
                                        }
                                    }
                                }
                            }

                            $field_name = "carrier_agents:" . $instance_id;
                            $agent_style = NODS_Common_Functions::NODS_get_carrier_agent_style();
                            echo '<div class="carrier_agents">';
                            woocommerce_form_field(
                                $field_name,
                                array(
                                    'type'          => ($agent_style == "select") ? "select" : "radio",
                                    'class'         => array('carrier_agents form-row-wide wc-enhanced-select'),
                                    'required'    => true,
                                    'options'     => $options
                                ),
                            );
                            echo '</div>';
                        }
                    }
                }
            }
        }

        /**
         * Save carrier agent data and auto sync in shipment
         */

        add_action('woocommerce_checkout_order_processed', 'NODS_save_shipit_carrier_agent_data', 10, 3);
        function NODS_save_shipit_carrier_agent_data($order_id, $posted_data, $order)
        {

            $order = wc_get_order($order_id);
            $get_checkout_is_enabled = NODS_Common_Functions::NODS_get_checkout_is_enabled();
            if ($get_checkout_is_enabled == 1) {
                $shipping_methods = $order->get_shipping_methods();

                if (empty($shipping_methods)) {
                    return;
                }

                $shipping_method = reset($shipping_methods);
                $instance_id = FALSE;
                if (is_a($shipping_method, 'WC_Order_Item_Shipping')) {
                    if (method_exists($shipping_method, 'get_instance_id')) {
                        $instance_id = $shipping_method->get_instance_id();
                    } else if (method_exists($shipping_method, 'get_method_id')) { // WooCommerce < 3.4
                        $shipping_rate_ids = explode(':', $shipping_method->get_method_id());
                        $shipping_rate_id = $shipping_rate_ids[0];
                        $instance_id = $shipping_rate_ids[1];
                    }
                }

                // No instance ID found, abort
                if (!$instance_id) {
                    return;
                }

                $agent_id = $_REQUEST['carrier_agents:' . $instance_id];
                $agent_data_id = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":id"];
                $agent_data_name = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":name"];
                $agent_data_address1 = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":address1"];
                $agent_data_zipcode = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":zipcode"];
                $agent_data_city = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":city"];
                $agent_data_countryCode = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":countryCode"];
                $agent_data_serviceId = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":serviceId"];
                $agent_data_carrier = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":carrier"];
                $agent_data_carrierLogo = $_REQUEST['carrier_agents:' . $instance_id . ":" . $agent_id . ":carrierLogo"];
                $agent_data = array(
                    "id" => $agent_data_id,
                    "name" => htmlentities($agent_data_name, ENT_QUOTES, 'UTF-8'),
                    "address1" => $agent_data_address1,
                    "zipcode" => $agent_data_zipcode,
                    "city" => $agent_data_city,
                    "countryCode" => $agent_data_countryCode,
                    "serviceId" => $agent_data_serviceId,
                    "carrier" => $agent_data_carrier,
                    "carrierLogo" => $agent_data_carrierLogo

                );
                if (!empty($agent_id)) {
                    update_post_meta($order_id, '_shipit_uniwin_carrier_agent_id', $agent_id);
                    update_post_meta($order_id, "_shipit_uniwin_carrier_agent_data", json_encode($agent_data));
                }

                // AUTO SYNC
                $settings = NODS_Common_Functions::NODS_get_settings_info();

                if (isset($settings->auto_sync) && ($settings->auto_sync == 1)) {
                    // get shipping service
                    global $wpdb;
                    $tblname = $wpdb->prefix . 'shipit_uniwin_shipping_mapping';
                    $shipping_mapped = $wpdb->get_var($wpdb->prepare("SELECT service_id FROM `" . $tblname . "` WHERE shipping_method='$instance_id'"));

                    if (!empty($shipping_mapped)) {
                        //Calculate weight based on weight unit
                        $weight = NODS_Common_Functions::weight_calculation($order_id);
                        $postdata['uniwin_shipit_country'] = $posted_data['shipping_country'];
                        $postdata['uniwin_shipit_parcel'] = $settings->pk_parcel;
                        $postdata['uniwin_shipit_weight'] = !empty($weight) ? $weight : 0.6;
                        $postdata['uniwin_shipit_width'] = $settings->pk_width;
                        $postdata['uniwin_shipit_height'] = $settings->pk_height;
                        $postdata['uniwin_shipit_length'] = $settings->pk_length;
                        //$postdata['uniwin_shipit_service'] = $shipping_mapped;
                        $postdata['uniwin_shipit_service'] = $agent_data_serviceId;
                        $postdata['uniwin_shipit_label'] = ($settings->return_label == 1) ? true : false;
                        $postdata['uniwin_shipit_fragile'] = ($settings->fragile == 1) ? true : false;
                        $postdata['uniwin_shipit_order_confirm'] = ($settings->order_confirm_email == 1) ? true : false;
                        $postdata['uniwin_shipit_pickup'] = $agent_id;
                        $postdata["oid"] = $order_id;
                        $response = (array) NODS_Shipment_Functions::create_shipment($postdata);

                        // Sanitize user input and update the field in the database.
                        // $carrier_name = NODS_Shipment_Functions::all_shipit_services($shipping_mapped);

                        $carrier_name = NODS_Shipment_Functions::nods_service_carrier_title($agent_data_serviceId, $posted_data['shipping_country']);

                        $data = array(
                            'package_data' => json_encode(array("country" => $posted_data['shipping_country'], "service" => $postdata['uniwin_shipit_service'], "weight" => $postdata['uniwin_shipit_weight'], "length" => $postdata['uniwin_shipit_length'], "width" => $postdata['uniwin_shipit_width'], "height" => $postdata['uniwin_shipit_height'], "parcels" => $postdata['uniwin_shipit_parcel'], "fragile" => isset($postdata['uniwin_shipit_fragile']) ? 1 : 0, "label" => isset($postdata['uniwin_shipit_label']) ? 1 : 0, "order_email" => isset($postdata['uniwin_shipit_order_confirm']) ? 1 : 0)),
                            'created_at' => date('Y-m-d H:i:s'),
                            'order_id' => $order_id,
                            'shipit_response' => json_encode($response),
                            'status' => $response['status'],
                            'carrier_name' => $carrier_name
                        );
                        //Add shipment response to order meta.
                        $unique_key = 1;
                        if (!empty(get_post_meta($order_id, "wc_shipit_uniwin_shipment_response_id"))) {
                            $unique_key = get_post_meta($order_id, "wc_shipit_uniwin_shipment_response_id")[0] + 1;
                            update_post_meta($order_id, 'wc_shipit_uniwin_shipment_response_id', $unique_key);
                        } else {
                            add_post_meta($order_id, 'wc_shipit_uniwin_shipment_response_id', $unique_key, true);
                        }
                        add_post_meta($order_id, 'wc_shipit_uniwin_shipment_response_' . $unique_key, $data, true);
                        if ($response['status'] == 1) {
                            $labels = array();
                            $printing_shiping_label = NODS_Common_Functions::printing_shiping_label();

                            if ($printing_shiping_label == 1) {
                                // Freight docs
                                $pritinglabels = $response['freightDoc'];
                                foreach ($pritinglabels as $url) {
                                    $label = NODS_Common_Functions::NODS_get_printing_label_data($url);
                                    if ($label) {
                                        $labels[] = array(
                                            'type' => 'label',
                                            'contents' => $label,
                                        );
                                    }
                                }
                                // Proforma
                                if (isset($response['proforma']) && !empty($response['proforma'])) {
                                    $proforma = NODS_Common_Functions::NODS_get_printing_label_data($response['proforma']);
                                    if ($proforma) {
                                        $labels[] = array(
                                            'type' => 'proforma',
                                            'contents' => $proforma,
                                        );
                                    }
                                }
                            }

                            if (!empty($labels)) {
                                // Save labels to the filesystem
                                $i = 1;
                                $documents = array();
                                foreach ($labels as $label) {
                                    $document_id = sprintf(
                                        esc_html("%s-%d"),
                                        esc_html($response['orderId']),
                                        esc_html($i)
                                    );
                                    $key = 'shipit_uniwin_shipping_label_data_' . $document_id;
                                    if ($label['type'] == "proforma") {
                                        $order->update_meta_data('shipit_uniwin_custom_docs', $document_id);
                                    } else {
                                        $order->update_meta_data('shipit_uniwin_shipping_label', $document_id);
                                    }

                                    $order->update_meta_data($key, base64_encode($label['contents']));
                                    $order->save();
                                    // NODS_Common_Functions::NODS_save_printing_label_document($label['contents'], $document_id);                              
                                    $documents[] = array(
                                        'id' => $document_id,
                                        'type' => $label['type'],
                                    );
                                    $i++;
                                }
                            }

                            $update_order_to_completed = NODS_Common_Functions::update_order_to_completed();
                            if ($update_order_to_completed == 1) {
                                $order_status = NODS_Common_Functions::set_order_as_completed($order_id);
                            }
                        }
                    }
                }
            }
        }

        /**
         * This code adds an action to the WooCommerce thank you page and displays the carrier agent information if enabled. It first checks if the checkout feature is enabled and if the carrier agent information should be displayed on the thank you page. If both conditions are met, it retrieves the agent data from the order metadata and formats it into an HTML string to display on the page.
         */

        add_action('woocommerce_thankyou', 'shipit_uniwin_show_carrier_agent_tq_page', 10, 2);
        function shipit_uniwin_show_carrier_agent_tq_page($order_id)
        {
            $get_checkout_is_enabled = NODS_Common_Functions::NODS_get_checkout_is_enabled();
            if ($get_checkout_is_enabled == 1) {
                $show_agent = NODS_Common_Functions::show_agent_on_tq_page();
                if ($show_agent == 1) {
                    $agent = get_post_meta($order_id, '_shipit_uniwin_carrier_agent_data');
                    if (!empty($agent)) {
                        $agent_data = json_decode($agent[0], true);
                        $address_name = $agent_data['carrier'] . " - " . html_entity_decode($agent_data['name'], ENT_QUOTES, 'UTF-8') . " , " . $agent_data['address1'];
                        $html = "<div class='show_carrier_agent_text'><h4>Pickup Location : </h4>";
                        $html .= "<p>" . $address_name . "</p></div>";
                        echo wp_kses($html, array('div', 'h4', 'p'));
                    }
                }
            }
        }


        /**
         * Attach shipment tracking URL to email.
         */

        add_action('woocommerce_email_order_meta', 'NODS_attach_tracking_url_to_email', 10, 4);

        function NODS_attach_tracking_url_to_email($order, $sent_to_admin = false, $plain_text = false, $email = null)
        {
            $add_to_email = NODS_Common_Functions::NODS_add_track_url_to_email();
            // Targetting specific email notifications
            $email_ids = array('customer_completed_order');
            if ($add_to_email == 1 && isset($email->id)) {
                // Checks if a value exists in an array
                if (in_array($email->id, $email_ids)) {
                    $shipment_info = NODS_Shipment_Functions::NODS_get_shipment_data($order->get_id());
                    if (!empty($shipment_info)) {
                        $sinfo = get_post_meta($order->get_id(), "wc_shipit_uniwin_shipment_response_" . $shipment_info[0])[0];
                        if ($sinfo["status"] == 1) {
                            $shipit_response = json_decode($sinfo["shipit_response"], true);
                            if (!empty($shipit_response['trackingUrls'][0])) {
                                if ($plain_text) {
                                    echo sprintf(
                                        esc_html("You can track your order at %s with tracking code %s.\n", 'nordic-shipping'),
                                        esc_html($shipit_response['trackingUrls'][0]),
                                        esc_html($shipit_response['trackingNumber'])
                                    );
                                } else {
                                    echo '<h2>' . esc_html_e('Tracking', 'nordic-shipping') . '</h2>';
                                    echo '<p>' . sprintf(esc_html_e('You can track your order <a href="%s">here</a> with tracking code %s.', 'nordic-shipping'), $shipit_response['trackingUrls'][0], $shipit_response['trackingNumber']) . '</p>';
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * This code adds an action to save custom fields for a product post type. It checks if the outside EU option is enabled and if it is, it saves the values for three custom fields: tariff code, custom description, and custom origin.
         */

        add_action('woocommerce_product_options_shipping', 'NODS_add_tariff_orgint_custom_fields');

        function NODS_add_tariff_orgint_custom_fields()
        {
            $check_outside_eu = NODS_Common_Functions::outside_eu_enabled();
            if ($check_outside_eu == 1) {
                woocommerce_wp_text_input(array(
                    'id'          => '_NODS_shipit_uniwin_tariff_code',
                    'value'       => get_post_meta(get_the_ID(), '_NODS_shipit_uniwin_tariff_code', true),
                    'label'       => esc_html__('HS tariff code', 'nordic-shipping'),
                    'placeholder' => 'Tariff code',
                    'desc_tip'    => true,
                    'description' => esc_html__('Tariff code, 6 - 10 digits', 'woocommerce'),
                    'type'        => 'text',
                ));

                woocommerce_wp_text_input(array(
                    'id'          => '_NODS_shipit_uniwin_custom_description',
                    'value'       => get_post_meta(get_the_ID(), '_NODS_shipit_uniwin_custom_description', true),
                    'label'       => esc_html__('Customs description', 'nordic-shipping'),
                    'placeholder' => '',
                    'desc_tip'    => true,
                    'description' => esc_html__('Description of the product for the customs.', 'woocommerce'),
                    'type'        => 'text',
                ));

                $selected_country = get_post_meta(get_the_ID(), '_NODS_shipit_uniwin_custom_orgin', true);

                if (!$selected_country) {
                    $selected_country = 'FI';
                }
            ?>

                <p class="form-field _wc_shipit_uniwin_custom_orgin_field ">
                    <label for="_wc_shipit_uniwin_custom_orgin"><?php esc_html_e('Country of origin', 'nordic-shipping'); ?></label>
                    <select name="_wc_shipit_uniwin_custom_orgin" style="width: 50%;" data-placeholder="<?php esc_attr_e('Choose a country&hellip;', 'woocommerce'); ?>">
                        <?php foreach (WC()->countries->get_countries() as $country_code => $label) { ?>
                            <option value="<?php echo $country_code; ?>" <?php selected($country_code, $selected_country); ?>><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </p> <?php
                    }
                }

                /**
                 * This code adds an action to save custom fields for a product post type. It checks if the outside EU option is enabled and if it is, it saves the values for three custom fields: tariff code, custom description, and custom origin.
                 */

                add_action('save_post_product', 'NODS_save_tariff_orgint_custom_fields', 10, 3);

                function NODS_save_tariff_orgint_custom_fields($post_id, $post, $update)
                {

                    $check_outside_eu = NODS_Common_Functions::outside_eu_enabled();
                    if ($check_outside_eu == 1) {
                        // Check if this is a product post type
                        if ('product' === $post->post_type) {
                            // Perform your custom actions here
                            // For example, you can retrieve the product data and perform operations on it
                            $product = wc_get_product($post_id);
                            // Check if the product object exists and is valid
                            if ($product) {
                                if (isset($_POST['_NODS_shipit_uniwin_tariff_code'])) {
                                    $value1 = sanitize_text_field($_POST['_NODS_shipit_uniwin_tariff_code']);
                                    update_post_meta($post_id, '_NODS_shipit_uniwin_tariff_code', $value1);
                                }

                                if (isset($_POST['_NODS_shipit_uniwin_custom_description'])) {
                                    $value2 = sanitize_text_field($_POST['_NODS_shipit_uniwin_custom_description']);
                                    update_post_meta($post_id, '_NODS_shipit_uniwin_custom_description', $value2);
                                }

                                if (isset($_POST['_NODS_shipit_uniwin_custom_orgin'])) {
                                    $value3 = sanitize_text_field($_POST['_NODS_shipit_uniwin_custom_orgin']);
                                    update_post_meta($post_id, '_NODS_shipit_uniwin_custom_orgin', $value3);
                                }
                            }
                        }
                    }
                }

                /**
                 * Manual sync from order list in sync button
                 * 
                 */
                add_action('wp_ajax_NODS_shipit_sync', 'NODS_shipit_sync');
                function NODS_shipit_sync()
                {
                    $order_id = sanitize_text_field($_POST['order_id']);
                    $order = wc_get_order($order_id);
                    $order_data = $order->get_data(); // The Order data

                    $shipping_methods = $order->get_shipping_methods();
                    if (empty($shipping_methods)) {
                        // The text for the note
                        $note = esc_html("Shipit sync is failed because there is no shipping method for this order!");
                        // Add the note
                        $order->add_order_note($note);
                        echo 'failed';
                    }

                    $shipping_method = reset($shipping_methods);
                    $instance_id = FALSE;
                    if (is_a($shipping_method, 'WC_Order_Item_Shipping')) {
                        if (method_exists($shipping_method, 'get_instance_id')) {
                            $instance_id = $shipping_method->get_instance_id();
                        } else if (method_exists($shipping_method, 'get_method_id')) { // WooCommerce < 3.4
                            $shipping_rate_ids = explode(':', $shipping_method->get_method_id());
                            $shipping_rate_id = $shipping_rate_ids[0];
                            $instance_id = $shipping_rate_ids[1];
                        }
                    }

                    // No instance ID found, abort
                    if (!$instance_id) {
                        // The text for the note
                        $note = esc_html("Shipit sync is failed because there is no shipping method instance id for this order!");
                        // Add the note
                        $order->add_order_note($note);
                        echo 'failed';
                        exit();
                    }
                    // get shipping service
                    global $wpdb;
                    $tblname = $wpdb->prefix . 'shipit_uniwin_shipping_mapping';
                    $shipping_mapped = $wpdb->get_var($wpdb->prepare("SELECT service_id FROM `" . $tblname . "` WHERE shipping_method='$instance_id'"));
                    $settings = NODS_Common_Functions::NODS_get_settings_info();
                    $agent_id = get_post_meta($order_id, '_shipit_uniwin_carrier_agent_id');

                    $agent_data = "";
                    $selected_service_id = "";
                    $agent_meta_data = get_post_meta($order_id, '_shipit_uniwin_carrier_agent_data', true);
                    if (!empty($agent_meta_data)) {
                        $agent_data = json_decode($agent_meta_data, true);
                    }
                    if(!empty($agent_data)){
                        $selected_service_id = $agent_data['serviceId'];
                    }
                    

                    if (!empty($shipping_mapped) || !empty($selected_service_id)) {
                        $mserviceid = !empty($selected_service_id) ? $selected_service_id : $shipping_mapped;
                        //Calculate weight based on weight unit
                        $weight = NODS_Common_Functions::weight_calculation($order_id);
                        $odata['uniwin_shipit_country'] = $order_data['shipping']['country'];
                        $odata['uniwin_shipit_parcel'] = $settings->pk_parcel;
                        $odata['uniwin_shipit_weight'] = !empty($weight) ? $weight : 0.6;
                        $odata['uniwin_shipit_width'] = $settings->pk_width;
                        $odata['uniwin_shipit_height'] = $settings->pk_height;
                        $odata['uniwin_shipit_length'] = $settings->pk_length;
                        $odata['uniwin_shipit_service'] = $mserviceid;
                        $odata['uniwin_shipit_label'] = ($settings->return_label == 1) ? true : false;
                        $odata['uniwin_shipit_fragile'] = ($settings->fragile == 1) ? true : false;
                        $odata['uniwin_shipit_order_confirm'] = ($settings->order_confirm_email == 1) ? true : false;
                        $odata['uniwin_shipit_pickup'] = !empty($agent_id) ? $agent_id[0] : "";
                        $odata["oid"] = $order_id;
                        $response = (array) NODS_Shipment_Functions::create_shipment($odata);
                        
                        // Sanitize user input and update the field in the database.
                        //$carrier_name = NODS_Shipment_Functions::all_shipit_services($shipping_mapped);
                        $carrier_name = NODS_Shipment_Functions::nods_service_carrier_title($mserviceid, $order_data['shipping']['country']);
                        $data = array(
                            'package_data' => json_encode(array("country" => $order_data['shipping']['country'], "service" => $odata['uniwin_shipit_service'], "weight" => $odata['uniwin_shipit_weight'], "length" => $odata['uniwin_shipit_length'], "width" => $odata['uniwin_shipit_width'], "height" => $odata['uniwin_shipit_height'], "parcels" => $odata['uniwin_shipit_parcel'], "fragile" => isset($odata['uniwin_shipit_fragile']) ? 1 : 0, "label" => isset($odata['uniwin_shipit_label']) ? 1 : 0, "order_email" => isset($odata['uniwin_shipit_order_confirm']) ? 1 : 0)),
                            'created_at' => date('Y-m-d H:i:s'),
                            'order_id' => $order_id,
                            'shipit_response' => json_encode($response),
                            'status' => $response['status'],
                            'carrier_name' => $carrier_name
                        );

                        //Add shipment response to order meta.
                        $unique_key = 1;
                        if (!empty(get_post_meta($order_id, "wc_shipit_uniwin_shipment_response_id"))) {
                            $unique_key = get_post_meta($order_id, "wc_shipit_uniwin_shipment_response_id")[0] + 1;
                            update_post_meta($order_id, 'wc_shipit_uniwin_shipment_response_id', $unique_key);
                        } else {
                            add_post_meta($order_id, 'wc_shipit_uniwin_shipment_response_id', $unique_key, true);
                        }
                        add_post_meta($order_id, 'wc_shipit_uniwin_shipment_response_' . $unique_key, $data, true);
                        if ($response['status'] == 1) {
                            $labels = array();
                            $printing_shiping_label = NODS_Common_Functions::printing_shiping_label();

                            if ($printing_shiping_label == 1) {
                                // Freight docs
                                $pritinglabels = $response['freightDoc'];
                                foreach ($pritinglabels as $url) {
                                    $label = NODS_Common_Functions::NODS_get_printing_label_data($url);
                                    if ($label) {
                                        $labels[] = array(
                                            'type' => 'label',
                                            'contents' => $label,
                                        );
                                    }
                                }
                                // Proforma
                                if (isset($response['proforma']) && !empty($response['proforma'])) {
                                    $proforma = NODS_Common_Functions::NODS_get_printing_label_data($response['proforma']);
                                    if ($proforma) {
                                        $labels[] = array(
                                            'type' => 'proforma',
                                            'contents' => $proforma,
                                        );
                                    }
                                }
                            }

                            if (!empty($labels)) {
                                // Save labels to the filesystem
                                $i = 1;
                                $documents = array();
                                foreach ($labels as $label) {
                                    $document_id = sprintf(
                                        esc_html("%s-%d"),
                                        esc_html($response['orderId']),
                                        esc_html($i)
                                    );
                                    $key = 'shipit_uniwin_shipping_label_data_' . $document_id;
                                    if ($label['type'] == "proforma") {
                                        $order->update_meta_data('shipit_uniwin_custom_docs', $document_id);
                                    } else {
                                        $order->update_meta_data('shipit_uniwin_shipping_label', $document_id);
                                    }

                                    $order->update_meta_data($key, base64_encode($label['contents']));
                                    $order->save();
                                    //NODS_Common_Functions::NODS_save_printing_label_document($label['contents'], $document_id);
                                    $documents[] = array(
                                        'id' => $document_id,
                                        'type' => $label['type'],
                                    );
                                    $i++;
                                }
                            }
                            $update_order_to_completed = NODS_Common_Functions::update_order_to_completed();
                            if ($update_order_to_completed == 1) {
                                // update order status
                                NODS_Common_Functions::set_order_as_completed($order_id);
                            }
                            // Print shipping label immediately after creating it
                            if ($printing_shiping_label == 1) {
                                // $redirect = NODS_Common_Functions::document_url($post_id, $documents[0]['id']);
                                // header('Location:' . $redirect);
                                // exit();
                            }
                            echo 'success';
                            exit();
                        } else {
                            echo 'failed';
                            exit();
                        }
                    } else {
                        // The text for the note
                        $note = esc_html__("Shipit sync is failed because there is no mapping method! or no pickup points are added");
                        // Add the note
                        $order->add_order_note($note);
                        echo 'failed';
                        exit();
                    }
                }

                add_action('wp_ajax_NODS_get_carrier_agents_based_on_country', 'NODS_get_carrier_agents_based_on_country');
                function NODS_get_carrier_agents_based_on_country()
                {
                    $_country = $_POST["country_id"];
                    $html = NODS_Shipment_Functions::shipit_uniwin_shipment_service_select_datas('uniwin_shipit_service', "", true, $_country);
                    echo $html;
                    exit();
                }


                add_filter('woocommerce_admin_order_data_after_order_details', 'NODS_hide_custom_fields_from_order_page');

                function NODS_hide_custom_fields_from_order_page()
                {
                    global $post;

                    // Array of meta keys for the custom fields you want to hide
                    $meta_keys_to_hide = array('shipit_uniwin_shipping_label', 'wc_shipit_uniwin_shipment_response_id');

                    echo '<style type="text/css">';
                    foreach ($meta_keys_to_hide as $meta_key) {
                        echo ".order_data_column .meta-box-order-data ." . sanitize_html_class($meta_key) . " { display: none !important; }";
                    }
                    echo '</style>';
                }
            }
        }

        new NODS_Settings;
