<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
function NODS_admin_inline_js()
{
    // you can pass true to reload function to ignore the client cache and reload from the server
    echo "<script  type='text/javascript'>\n";
    echo 'setTimeout(function() { window.location.reload();  }, 2000);';
    echo "\n</script>";
}
add_action('admin_print_scripts', 'NODS_admin_inline_js');
?>

<div class="wrapper container-fluid settings_form">
    <div class="card">
        <h3 class="card-header">Shipit Settings</h3>
        <?php
        $result = json_decode(get_option("wc_shipit_uniwin_settings"));
        $button_name = isset($_POST['save']) ? sanitize_text_field($_POST['save']) : '';
        if (!empty($button_name)) {
            // Check for valid API credentials 
            $nonce = sanitize_text_field($_POST['admin_settings_nonce']);
            if (isset($nonce) && wp_verify_nonce($nonce, 'admin_settings_nonce')) {

                $form_input = [
                    'nonce' => sanitize_text_field($_POST['admin_settings_nonce']),
                    'unishipit_api_mode' => sanitize_text_field($_POST['unishipit_api_mode']),
                    'unishipit_api_secret' => sanitize_text_field($_POST['unishipit_api_secret']),
                    'unishipit_api_key' => sanitize_text_field($_POST['unishipit_api_key'])
                ];
                $r = NODS_api_is_valid($form_input);
                // If the API credentials are invalid, set API variables to empty strings and display an error 
                if ($r == "200") {
                    $api_valid = 1;
                    $api_key = sanitize_text_field($_POST['unishipit_api_key']);
                    $api_secret = sanitize_text_field($_POST['unishipit_api_secret']);
                    do_action('admin_update_notices', "Settings saved successfully!");
                    // Otherwise, set API variables accordingly and display a success message 
                } else {
                    $api_valid = 0;
                    $api_key = "";
                    $api_secret = "";
                    do_action('admin_notices', "Invalid API key!");
                }
                $api_url = NODS_Common_Functions::api_url(sanitize_text_field($_POST['unishipit_api_mode']));
                $data = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'api_key' => $api_key,
                    'secret_key' => $api_secret,
                    'mode' => sanitize_text_field($_POST['unishipit_api_mode']),
                    'api_is_valid' => $api_valid,
                    'api_url' => $api_url,
                    'name' => sanitize_text_field($_POST['unishipit_sender_name']),
                    'stree_address' => sanitize_text_field($_POST['unishipit_sender_street']),
                    'city' => sanitize_text_field($_POST['unishipit_sender_city']),
                    'country' => sanitize_text_field($_POST['unishipit_sender_country']),
                    'postcode' => sanitize_text_field($_POST['unishipit_sender_postcode']),
                    'vat' => sanitize_text_field($_POST['unishipit_sender_vat']),
                    'email' => sanitize_email($_POST['unishipit_sender_email']),
                    'phone' => sanitize_text_field($_POST['unishipit_sender_phone']),
                    'contents' => sanitize_text_field($_POST['unishipit_sender_contents']),
                );
                //Insert/Update form settings data
                if (empty($result)) {
                    add_option('wc_shipit_uniwin_settings', json_encode($data));
                } else {
                    update_option('wc_shipit_uniwin_settings', json_encode($data));
                }
            } else {
                do_action('admin_notices', "Nonce verification failed!");
            }

            do_action('admin_print_scripts');
        }
        ?>

        <?php
        //General settings data insert/upate
        $gs_save_button_name = isset($_POST['gs_save']) ? sanitize_text_field($_POST['gs_save']) : '';
        if (!empty($gs_save_button_name)) {

            $nonce = sanitize_text_field($_POST['admin_general_settings_nonce']);
            if (isset($nonce) && wp_verify_nonce($nonce, 'admin_general_settings_nonce')) {

                $data = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'auto_sync' => isset($_POST['unishipit_auto_sync']) ? 1 : 0,
                    'address_sync' => filter_input(INPUT_POST, 'unishipit_address_sync', FILTER_SANITIZE_STRING),
                    'pk_width' => filter_input(INPUT_POST, 'unishipit_width', FILTER_SANITIZE_NUMBER_INT),
                    'pk_length' => filter_input(INPUT_POST, 'unishipit_length', FILTER_SANITIZE_NUMBER_INT),
                    'pk_height' => filter_input(INPUT_POST, 'unishipit_height', FILTER_SANITIZE_NUMBER_INT),
                    'pk_parcel' => filter_input(INPUT_POST, 'unishipit_parcel', FILTER_SANITIZE_STRING),
                    'fragile' => isset($_POST['unishipit_fragile']) ? 1 : 0,
                    'return_label' => isset($_POST['unishipit_label']) ? 1 : 0,
                    'order_confirm_email' => isset($_POST['unishipit_order_confirmation']) ? 1 : 0,
                    'home_delivery' => isset($_POST['unishipit_home_delivery']) ? 1 : 0,
                    'hd_width' => filter_input(INPUT_POST, 'unishipit_hd_width', FILTER_SANITIZE_NUMBER_INT) ?? "",
                    'hd_length' => filter_input(INPUT_POST, 'unishipit_hd_length', FILTER_SANITIZE_NUMBER_INT) ?? "",
                    'hd_height' => filter_input(INPUT_POST, 'unishipit_hd_height', FILTER_SANITIZE_NUMBER_INT) ?? "",
                    'show_carrier' => isset($_POST['unishipit_show_carrier']) ? 1 : 0,
                    'add_track_to_email' => isset($_POST['unishipit_add_track_email']) ? 1 : 0,
                    'agent_style' => isset($_POST['unishipit_agent_style']) ? sanitize_text_field($_POST['unishipit_agent_style']) : "select",
                    'agent_thankyou_page' => isset($_POST['unishipit_agent_thankyou_page']) ? 1 : 0,
                );
                if (isset($result->api_key)) {
                    $generalset = get_option("wc_shipit_uniwin_general_settings");
                    if (!empty($generalset)) {
                        update_option('wc_shipit_uniwin_general_settings', json_encode($data));
                    } else {
                        add_option('wc_shipit_uniwin_general_settings', json_encode($data));
                    }
                    do_action('admin_update_notices', "Settings saved successfully!");
                } else {
                    do_action('admin_notices', "Please update the account settings first!.");
                }
            } else {
                do_action('admin_notices', "Error! Nonce verification failed!.");
            }
            do_action('admin_print_scripts');
        }
        ?>
        <?php
        //Mapping data insert/upate
        $shipit_uniwin_shipping_mapping_button_name = isset($_POST['shipit_uniwin_shipping_mapping']) ? sanitize_text_field($_POST['shipit_uniwin_shipping_mapping']) : '';
        if (!empty($shipit_uniwin_shipping_mapping_button_name)) {

            $nonce = sanitize_text_field($_POST['admin_shipping_settings_nonce']);
            if (isset($nonce) && wp_verify_nonce($nonce, 'admin_shipping_settings_nonce')) {
                global $wpdb;
                $tblname = $wpdb->prefix . 'shipit_uniwin_shipping_mapping';
                $datas = $_POST;
                foreach ($datas as $key => $inputs) {
                    if ($inputs != "_none" || $inputs != "admin_shipping_settings_nonce") {
                        $shiping_method_id = str_replace("shipit_uniwin_shipping_method_", "", $key);
                        $sqls = $wpdb->prepare("SELECT * FROM `$tblname` WHERE shipping_method = %s", $shiping_method_id);
                        $results = $wpdb->get_results($sqls);
                        if (empty($results) && !empty($inputs)) {
                            $data = array(
                                'created_at' => date('Y-m-d H:i:s'),
                                'shipping_method' => $shiping_method_id,
                                'service_id' => $inputs,
                            );
                            $wpdb->insert($tblname, $data);
                        } else {
                            if ($shiping_method_id != 0 && !empty($results)) {
                                $data = array(
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'service_id' => $inputs,
                                );
                                $wpdb->update($tblname, $data, array('id' => $results[0]->id));
                            }
                        }
                    }
                }
            } else {
                do_action('admin_notices', "Error! Nonce verification failed!");
            }
        }
        //Custom data insert/upate
        $shipping_custom_button_name = isset($_POST['shipit_uniwin_shipping_custom']) ? sanitize_text_field($_POST['shipit_uniwin_shipping_custom']) : '';
        if (!empty($shipping_custom_button_name)) {

            $nonce = sanitize_text_field($_POST['admin_customs_settings_nonce']);
            if (isset($nonce) && wp_verify_nonce($nonce, 'admin_customs_settings_nonce')) {
                $data = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'outside_eu_shipment' => isset($_POST['unishipit_outside_eu_shipment']) ? 1 : 0,
                    'product_name_desc' => isset($_POST['unishipit_product_name_desc']) ? 1 : 0,
                );
                if (isset($result->api_key)) {
                    $customsset = get_option("wc_shipit_uniwin_customs_settings");
                    if (!empty($customsset)) {
                        update_option('wc_shipit_uniwin_customs_settings', json_encode($data));
                    } else {
                        add_option('wc_shipit_uniwin_customs_settings', json_encode($data));
                    }
                    do_action('admin_update_notices', "Settings saved successfully!");
                } else {
                    do_action('admin_notices', "Please update the account settings first!.");
                }
            } else {
                do_action('admin_notices', "Error! Nonce verification failed!.");
            }
            do_action('admin_print_scripts');
        }
        //Print settings Insert/Update
        $shipping_printing_button_name = isset($_POST['shipit_uniwin_shipping_printing']) ? sanitize_text_field($_POST['shipit_uniwin_shipping_printing']) : '';
        if (!empty($shipping_printing_button_name)) {

            $nonce = sanitize_text_field($_POST['admin_print_settings_nonce']);
            if (isset($nonce) && wp_verify_nonce($nonce, 'admin_print_settings_nonce')) {
                $data = array(
                    'updated_at' => date('Y-m-d H:i:s'),
                    'print_shiping_label' => isset($_POST['uniwin_print_shiping_label']) ? 1 : 0,
                    'set_order_completed' => isset($_POST['unishipit_set_order_completed']) ? 1 : 0
                );
                if (isset($result->api_key)) {
                    $printset = get_option("wc_shipit_uniwin_print_settings");
                    if (!empty($printset)) {
                        update_option('wc_shipit_uniwin_print_settings', json_encode($data));
                    } else {
                        add_option('wc_shipit_uniwin_print_settings', json_encode($data));
                    }
                    do_action('admin_update_notices', "Settings saved successfully!");
                } else {
                    do_action('admin_notices', "Please update the account settings first!.");
                }
            } else {
                do_action('admin_notices', "Error! Nonce verification failed!");
            }
            do_action('admin_print_scripts');
        }
        //New user reg data isert/update
        $new_uniwin_user_button_name = isset($_POST['new_uniwin_user']) ? sanitize_text_field($_POST['new_uniwin_user']) : '';
        if (!empty($new_uniwin_user_button_name)) {

            $nonce = sanitize_text_field($_POST['admin_new_user_settings_nonce']);
            if (isset($nonce) && wp_verify_nonce($nonce, 'admin_new_user_settings_nonce')) {
                $newdata = [
                    "name" => sanitize_text_field($_POST['reg_uniwin_name']),
                    "email" => sanitize_text_field($_POST['reg_uniwin_email']),
                    "password" => sanitize_text_field($_POST['reg_uniwin_password']),
                    "phone" => "+" . sanitize_text_field($_POST['reg_uniwin_phone']),
                    "address" => sanitize_text_field($_POST['reg_uniwin_address']),
                    "postcode" => sanitize_text_field($_POST['reg_uniwin_postal']),
                    "city" => sanitize_text_field($_POST['reg_uniwin_city']),
                    "country" => sanitize_text_field($_POST['reg_uniwin_country']),
                    "isCompany" => false,
                    "contactPerson" => sanitize_text_field($_POST['reg_uniwin_name']),
                    "businessId" => sanitize_text_field($_POST['reg_uniwin_vat'])
                ];
                $r = NODS_new_user_registration($newdata);
                $new_user = json_decode($r['response']);
                if (isset($new_user['status']) && ($new_user['status'] == "0")) {
                    do_action('admin_notices', $r['error']['message']);
                    $data = array(
                        'updated_at' => date('Y-m-d H:i:s'),
                        'new_user' => json_encode($r['response'])
                    );
                } else {
                    $data = array(
                        'updated_at' => date('Y-m-d H:i:s'),
                        'name' => sanitize_text_field($_POST['reg_uniwin_name']),
                        'password' => sanitize_text_field($_POST['reg_uniwin_password']),
                        'stree_address' => sanitize_text_field($_POST['reg_uniwin_address']),
                        'city' => sanitize_text_field($_POST['reg_uniwin_city']),
                        'country' => sanitize_text_field($_POST['reg_uniwin_country']),
                        'postcode' => sanitize_text_field($_POST['reg_uniwin_postal']),
                        'vat' => sanitize_text_field($_POST['reg_uniwin_vat']),
                        'email' => sanitize_email($_POST['reg_uniwin_email']),
                        'phone' => sanitize_text_field($_POST['reg_uniwin_phone']),
                        'new_user' => json_encode($r['response'])
                    );
                }

                if (empty($result)) {
                    add_option('wc_shipit_uniwin_settings', json_encode($data));
                } else {
                    update_option('wc_shipit_uniwin_settings', json_encode($data));
                }

                if (isset($r['status']) && ($r['status'] == "1")) {
                    do_action('admin_update_notices', "Registration done successfully!");
                }
            } else {
                do_action('admin_notices', "Error! Nonce verification failed!");
            }
            do_action('admin_print_scripts');
        }
        ?>


        <div class="card-body">
            <!-- Tab links -->
            <div class="tabs">
                <button class="tab active" id="tab1">Account Settings</button>
                <button class="tab" id="tab2">General Settings</button>
                <button class="tab" id="tab3">Mapping</button>
                <button class="tab" id="tab4">Customs</button>
                <button class="tab" id="tab6">Print Settings</button>
            </div>
            <!-- Tab content -->
            <div id="content1" class="tab-content active">
                <!-- Registers a new user account to shipit.fi service. -->
                <p>Registers a new user account to shipit.fi service - <button id="open" class="button-primary">Register</button></p>
                <div>
                    <?php
                    if (!empty($result->new_user)) {
                        $new_user_data = json_decode($result->new_user, true);
                        if (isset($new_user_data['status']) && ($new_user_data['status'] == "1")) {
                    ?>
                            <p class="success">Credentials : key - <?php echo esc_html($new_user_data['credentials']['key']); ?> , secret - <?php echo esc_html($new_user_data['credentials']['secret']); ?> </p>
                        <?php
                        } else if (isset($new_user_data['status']) && ($new_user_data['status'] == "0")) {
                        ?>
                            <p class="error"><?php echo esc_html($new_user_data['error']['message']); ?> - <?php echo esc_html(implode(',', $new_user_data['errorbag'])); ?></p>
                    <?php
                        }
                    }
                    ?>
                </div>
                <div id="a">

                </div>
                <div class="register_modal" id="b">
                    <div class="uniwin_register_header">
                        <a href="#" class="cancel">X</a>
                    </div>
                    <form method="post" action="#" name="shipit_uniwin_new_user" id="shipit_uniwin_new_user">
                        <input type="hidden" name="admin_new_user_settings_nonce" value="<?php echo wp_create_nonce('admin_new_user_settings_nonce'); ?>">
                        <div class="uniwin_register_content">
                            <div class="container">
                                <table class="form-table">
                                    <tbody>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>Name</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input class="form-control" type="text" name="reg_uniwin_name" placeholder="Name" />
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>Email</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input type="email" class="form-control" autocomplete="false" placeholder="Email" name="reg_uniwin_email" />
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>Password</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input type="password" class="form-control" autocomplete="false" placeholder="Password" name="reg_uniwin_password" />
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>Phone</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input type="text" class="form-control" placeholder="phone" name="reg_uniwin_phone" />
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>Address</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input type="text" class="form-control" placeholder="address" name="reg_uniwin_address" />
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>Postcode</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input type="text" class="form-control" placeholder="postcode" name="reg_uniwin_postal" />
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>City</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input type="text" class="form-control" placeholder="city" name="reg_uniwin_city" />
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>Country</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <select name="reg_uniwin_country">
                                                    <option value="">Select country</option>
                                                    <?php
                                                    foreach (WC()->countries->get_countries() as $country_code => $label) {
                                                    ?>
                                                        <option value="<?php echo esc_attr($country_code); ?>"><?php echo esc_html($label); ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label>VAT Id</label>
                                            </th>
                                            <td class="forminp forminp-text">
                                                <input type="text" class="form-control" placeholder="vat id" name="reg_uniwin_vat" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="uniwin_register_footer">
                            <button class="cancel button-primary">Cancel</button>
                            <button name="new_uniwin_user" class="button-primary" type="submit" value="Save" id="new_uniwin_user">Save</button>
                        </div>
                    </form>
                </div>

                <!-- The form allows users to input various settings, such as API key, API secret, mode, and sender details (name, address, city, country, postcode, VAT ID, email, phone, and contents). The form also includes a save button to store the entered settings. -->
                <form action="#" method="post" name="shipit_uniwin_admin_settings" enctype="multipart/form-data" id="shipit_uniwin_admin_settings">
                    <input type="hidden" name="admin_settings_nonce" value="<?php echo wp_create_nonce('admin_settings_nonce'); ?>">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>API Key</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" class="form-control" id="unishipit_api_key" name="unishipit_api_key" placeholder="API Key" value="<?php echo isset($result->api_key) ? $result->api_key : "" ?>" />
                                    <?php
                                    if (!empty($new_user_data) && $new_user_data['status'] == 1) {
                                        if ($result && !empty($result->api_is_valid) == 0) { ?>
                                            <p class="error">Invalid API key credentials</p>
                                        <?php } elseif ($result && !empty($result->api_is_valid) == 1) { ?>
                                            <p class="success">Valid API key credentials</p>
                                    <?php }
                                    } ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>API secret</label>
                                </th>
                                <td class="forminp forminp-text">

                                    <input type="text" class="form-control" value="<?php echo isset($result->secret_key) ? $result->secret_key : "" ?>" name="unishipit_api_secret" placeholder="API Secret" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Mode</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <select name="unishipit_api_mode">
                                        <option value="production" <?php echo isset($result->mode) ? ("production" == $result->mode) ? "selected" : "" : "" ?>>
                                            Production
                                        </option>
                                        <option value="test" <?php echo isset($result->mode) ? ("test" == $result->mode) ? "selected" : "" : "selected" ?>>Test</option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Sender Details</label>
                                </th>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Name</label>
                                </th>
                                <td class="forminp forminp-text">

                                    <input type="text" class="form-control" value="<?php echo esc_attr(isset($result->name) ? $result->name : ""); ?>" name="unishipit_sender_name" placeholder="Name" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Street address</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" class="form-control" value="<?php echo esc_attr(isset($result->stree_address) ? $result->stree_address : ""); ?>" name="unishipit_sender_street" placeholder="Street address" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>City</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($result->city) ? $result->city : ""); ?>" class="form-control" name="unishipit_sender_city" placeholder="City" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Country</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <select name="unishipit_sender_country">
                                        <option value="">Select country</option>
                                        <?php
                                        foreach (WC()->countries->get_countries() as $country_code => $label) {
                                            $selected = "";
                                            if ($result) {
                                                $selected = isset($result->country) ? (($country_code == $result->country) ? "selected" : "")  : "";
                                            }
                                        ?>
                                            <option <?php echo  esc_html($selected); ?> value="<?php echo esc_attr($country_code); ?>"><?php echo esc_html($label); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Postcode</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($result->postcode) ? $result->postcode : ""); ?>" class="form-control" name="unishipit_sender_postcode" placeholder="Postcode" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Vat ID</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($result->vat) ? $result->vat : ""); ?>" class="form-control" name="unishipit_sender_vat" placeholder="Vat ID" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Email</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="email" value="<?php echo esc_attr(isset($result->email) ? $result->email : ""); ?>" class="form-control" name="unishipit_sender_email" placeholder="Email" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Phone</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($result->phone) ? $result->phone : ""); ?>" class="form-control" name="unishipit_sender_phone" placeholder="Phone" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Contents</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($result->contents) ? $result->contents : ""); ?>" class="form-control" name="unishipit_sender_contents" placeholder="Contents" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc"> <button type="submit" value="save" class="button-primary" id="shipit_uniwin_settings" name="save">Save</button></th>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>

            <div id="content2" class="tab-content">
                <?php $general_settings = json_decode(get_option("wc_shipit_uniwin_general_settings")); ?>
                <!-- The form allows users to input various settings, such as Auto sync and carrier agents and shipment width, height etc. The form also includes a save button to store the entered settings. -->
                <form action="#" method="post" name="shipit_uniwin_general_settings" id="shipit_uniwin_general_settings">
                    <input type="hidden" name="admin_general_settings_nonce" value="<?php echo wp_create_nonce('admin_general_settings_nonce'); ?>">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Automatic Sync </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" <?php echo isset($general_settings->auto_sync) ? ($general_settings->auto_sync == "1" ? "checked" : "") : "" ?> class="form-control" name="unishipit_auto_sync" placeholder="Automatic Sync" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Show carriers on checkout </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input id="unishipit_show_carrier" type="checkbox" class="form-control" <?php echo isset($general_settings->show_carrier) ? ($general_settings->show_carrier == "1" ? "checked" : "") : "" ?> name="unishipit_show_carrier" />
                                </td>
                            </tr>
                            <tr valign="top" class="carrier_agents_assistant">
                                <th scope="row" class="titledesc">
                                    <label> Carrier agents display style on checkout </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <select name="unishipit_agent_style">
                                        <option <?php echo isset($general_settings->agent_style) ? ($general_settings->agent_style == "select" ? "selected" : "") : "" ?> value="select">Select Box</option>
                                        <option <?php echo isset($general_settings->agent_style) ? ($general_settings->agent_style == "radio" ? "selected" : "") : "" ?> value="radio">Radio Button</option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top" class="carrier_agents_assistant">
                                <th scope="row" class="titledesc">
                                    <label> Show carrier agent on the Thank You page</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" class="form-control" <?php echo isset($general_settings->agent_thankyou_page) ? ($general_settings->agent_thankyou_page == "1" ? "checked" : "") : "" ?> name="unishipit_agent_thankyou_page" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Address Sync</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="radio" id="customRadioInline1" <?php echo isset($general_settings->address_sync) ? ($general_settings->address_sync == "shipping" ? "checked" : "") : "checked" ?> value="shipping" name="unishipit_address_sync" class="custom-control-input">
                                    <label class="custom-control-label" for="customRadioInline1">Shipping</label>
                                    <input type="radio" id="customRadioInline2" <?php echo isset($general_settings->address_sync) ? ($general_settings->address_sync == "billing" ? "checked" : "") : "" ?> value="billing" name="unishipit_address_sync" class="custom-control-input">
                                    <label class="custom-control-label" for="customRadioInline2">Billing</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="titledesc">
                                    <label> Add tracking link to the order completed email </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" <?php echo isset($general_settings->add_track_to_email) ? ($general_settings->add_track_to_email == "1" ? "checked" : "") : "" ?> class="form-control" name="unishipit_add_track_email" />
                                </td>
                            </tr>


                            <tr>
                                <th scope="row" class="titledesc">Default package size ( cm ) & parcels</th>
                            </tr>
                            <tr valign="top unishipit_package_size">

                                <th scope="row" class="titledesc">
                                    <label> Width</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" class="form-control" value="<?php echo esc_attr(isset($general_settings->pk_width) ? ($general_settings->pk_width ? $general_settings->pk_width : "10") : "10"); ?>" name="unishipit_width" placeholder="Width" />
                                </td>
                            </tr>
                            <tr valign="top unishipit_package_size">
                                <th scope="row" class="titledesc">
                                    <label>Length</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" class="form-control" value="<?php echo esc_attr(isset($general_settings->pk_length) ? ($general_settings->pk_length ? $general_settings->pk_length : "15") : "15"); ?>" name="unishipit_length" placeholder="Length">
                                </td>
                            </tr>
                            <tr valign="top unishipit_package_size">
                                <th scope="row" class="titledesc">
                                    <label>Height</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" class="form-control" value="<?php echo esc_attr(isset($general_settings->pk_height) ? ($general_settings->pk_height ? $general_settings->pk_height : "1") : "1"); ?>" name="unishipit_height" placeholder="Height">
                                </td>
                            </tr>
                            <tr valign="top unishipit_package_size">
                                <th scope="row" class="titledesc">
                                    <label>Parcel</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($general_settings->pk_parcel) ? ($general_settings->pk_parcel ? $general_settings->pk_parcel : "1") : "1"); ?>" class="form-control" name="unishipit_parcel" placeholder="Parcel">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Addons </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" id="customRadioInline3" <?php echo isset($general_settings->fragile) ? ($general_settings->fragile == "1" ? "checked" : "") : "" ?> name="unishipit_fragile" class="custom-control-input">
                                    <label class="custom-control-label" for="customRadioInline3">Fragile</label>
                                    <input type="checkbox" id="customRadioInline4" <?php echo isset($general_settings->return_label) ? ($general_settings->return_label == "1" ? "checked" : "") : "" ?> name="unishipit_label" class="custom-control-input">
                                    <label class="custom-control-label" for="customRadioInline4">Return Label</label>
                                    <input type="checkbox" id="customRadioInline6" <?php echo isset($general_settings->order_confirm_email) ? ($general_settings->order_confirm_email == "1" ? "checked" : "") : "" ?> name="unishipit_order_confirmation" class="custom-control-input">
                                    <label class="custom-control-label" for="customRadioInline6">Send order confirmation Email</label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label>Home Delivery </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" id="customRadioInline7" <?php echo isset($general_settings->home_delivery) ? ($general_settings->home_delivery == "1" ? "checked" : "") : "" ?> name="unishipit_home_delivery" class="custom-control-input">
                                </td>
                            </tr>
                            <tr valign="top" class="unishipit-home-delivery">
                                <th scope="row" class="titledesc">
                                    <label>Dimensions LxWxH (cm)</label>
                                </th>
                            </tr>
                            <tr valign="top" class="unishipit-home-delivery">
                                <th scope="row" class="titledesc">
                                    <label>Width</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" class="form-control" value="<?php echo esc_attr(isset($general_settings->hd_width) ? ($general_settings->hd_width ? $general_settings->hd_width : "") : ""); ?>" name="unishipit_hd_width" placeholder="Width" />
                                </td>
                            </tr>
                            <tr valign="top" class="unishipit-home-delivery">
                                <th scope="row" class="titledesc">
                                    <label>Length</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($general_settings->hd_length) ? ($general_settings->hd_length ? $general_settings->hd_length : "") : ""); ?>" class="form-control" name="unishipit_hd_length" placeholder="Length">
                                </td>
                            </tr>
                            <tr valign="top" class="unishipit-home-delivery">
                                <th scope="row" class="titledesc">
                                    <label>Height</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" value="<?php echo esc_attr(isset($general_settings->hd_height) ? ($general_settings->hd_height ? $general_settings->hd_height : "") : ""); ?>" class="form-control" name="unishipit_hd_height" placeholder="Height">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="titledesc"><button type="submit" class="button-primary" data-tabid="tab2" value="save" name="gs_save">Save</button></th>
                            </tr>
                        </tbody>
                    </table>

                </form>
            </div>

            <div id="content3" class="tab-content">
                <!-- The form shows shipping methods from woocommerce shipping methods for mapping concept -->
                <h2>Shipping Methods</h2>
                <p>By linking shipping methods to Shipit services, you eliminate the need to manually select the shipping service for orders.</p>
                <form method="post" action="#">
                    <input type="hidden" name="admin_shipping_settings_nonce" value="<?php echo wp_create_nonce('admin_shipping_settings_nonce'); ?>">
                    <?php
                    $all_zones = shipit_uniwin_get_all_shipping_zones();
                    if (!empty($all_zones)) {
                        foreach ($all_zones as $zone) {
                            $zone_id = $zone->get_id();
                            $zone_name = $zone->get_zone_name();
                            $zone_shipping_methods = $zone->get_shipping_methods();
                            $zone_locations = $zone->get_zone_locations();
                    ?>
                            <h2><?php echo $zone_name; ?></h2>
                            <table class="form-table">
                                <tbody>
                                    <?php
                                    global $wpdb;
                                    $tblname = $wpdb->prefix . 'shipit_uniwin_shipping_mapping';
                                    $options = array('_none' => esc_html('- No Shipit method - ', 'nordic-shipping'));

                                    $default = key($options);
                                    foreach ($zone_shipping_methods as $index => $method) {
                                        $zone_code = $zone_locations[0]->code;
                                        if ($zone_code == "FI" || $zone_code == "SE") {
                                            if ($zone_code == "FI") {
                                                foreach (NODS_Shipment_Functions::nods_fi_services() as $service_code => $service_title) {
                                                    $options[$service_code] = NODS_Shipment_Functions::shipment_service_title($service_code, TRUE, "FI");
                                                }
                                            } else if ($zone_code == "SE") {
                                                foreach (NODS_Shipment_Functions::nods_se_services() as $service_code => $service_title) {
                                                    $options[$service_code] = NODS_Shipment_Functions::shipment_service_title($service_code, TRUE, "SE");
                                                }
                                            }
                                            $method_is_enabled = $method->is_enabled();
                                            $method_user_title = $method->get_title(); // e.g. whatever you renamed "Flat Rate" into
                                            if ($method_is_enabled) { ?>
                                                <tr valign="top">
                                                    <th scope="row" class="titledesc">
                                                        <label for="shipit_uniwin_shipping_method_<?php echo $index; ?>"><?php echo esc_html($method_user_title); ?></label>
                                                    </th>
                                                    <?php if ($method) { ?>
                                                        <td class="forminp forminp-select">
                                                            <select name="shipit_uniwin_shipping_method_<?php echo $index; ?>" id="shipit_uniwin_shipping_method_<?php echo $index; ?>">
                                                                <?php
                                                                foreach ($options as $key => $opt) {
                                                                    $shipping_mapped = $wpdb->get_var(
                                                                        $wpdb->prepare(
                                                                            "SELECT service_id FROM `$tblname` WHERE shipping_method = %s",
                                                                            $index
                                                                        )
                                                                    );
                                                                    $selected = ($shipping_mapped) ? ($shipping_mapped == $key) ? "selected" : "" : "";
                                                                    echo "<option value='" . esc_attr($key) . "' " . esc_html($selected) . ">" . esc_html($opt) . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                    <?php } ?>
                                                </tr>
                                        <?php }
                                        } ?>

                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php
                        } ?>
                        <button class="button-primary" type="submit" value="Save" name="shipit_uniwin_shipping_mapping">Save</button>
                    <?php } ?>
                </form>
            </div>
            <div id="content4" class="tab-content">
                <?php $customs_settings = json_decode(get_option("wc_shipit_uniwin_customs_settings")); ?>
                <form action="#" method="post">
                    <input type="hidden" name="admin_customs_settings_nonce" value="<?php echo wp_create_nonce('admin_customs_settings_nonce'); ?>">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row" class="titledesc">
                                    <label> Enable customs documents outside the EU </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" <?php echo isset($customs_settings->outside_eu_shipment) ? ($customs_settings->outside_eu_shipment == "1" ? "checked" : "") : "" ?> class="form-control" name="unishipit_outside_eu_shipment" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="titledesc">
                                    <label> Utilize the product name as the description for customs purposes </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" <?php echo isset($customs_settings->product_name_desc) ? ($customs_settings->product_name_desc == "1" ? "checked" : "") : "" ?> class="form-control" name="unishipit_product_name_desc" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="button-primary" type="submit" value="save" name="shipit_uniwin_shipping_custom">Save</button>
                </form>
            </div>
            <div id="content6" class="tab-content">
                <?php $print_settings = json_decode(get_option("wc_shipit_uniwin_print_settings")); ?>
                <form action="#" method="post">
                    <input type="hidden" name="admin_print_settings_nonce" value="<?php echo wp_create_nonce('admin_print_settings_nonce'); ?>">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row" class="titledesc">
                                    <label>
                                        After shipment creation, print shipping label in PDF
                                    </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" <?php echo isset($print_settings->print_shiping_label) ? ($print_settings->print_shiping_label == "1" ? "checked" : "") : "" ?> class="form-control" name="uniwin_print_shiping_label" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" class="titledesc">
                                    <label>
                                        Set the order as completed after printing the shipping label
                                    </label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="checkbox" <?php echo isset($print_settings->set_order_completed) ? ($print_settings->set_order_completed == "1" ? "checked" : "") : "" ?> class="form-control" name="unishipit_set_order_completed" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="button-primary" type="submit" value="save" name="shipit_uniwin_shipping_printing">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>