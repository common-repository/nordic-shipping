<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * This code retrieves the  country and  method instance ID for a WooCommerce order*
 **/
$order = wc_get_order($post->ID);

$_country = $order->get_billing_country();

foreach ($order->get_shipping_methods() as $_method) {
    $_method_instance_id = $_method->get_instance_id(); // The instance ID
}

/*  It also checks if the Shipit Uniwin checkout is enabled and retrieves the carrier agent data if available. Finally, it calls a function to get the shipment data for the order. */
$agent_data = "";
$agent_meta_data = get_post_meta($post->ID, '_shipit_uniwin_carrier_agent_data', true);
if (!empty($agent_meta_data)) {
    $agent_data = json_decode($agent_meta_data, true);
}
$shipment_info = NODS_Shipment_Functions::NODS_get_shipment_data($post->ID);

?>

<div class="form-field">
    <input type="hidden" name="mv_other_meta_field_nonce" value="<?php echo wp_create_nonce("meta_box"); ?>">
    <label><?php echo esc_html__("Country", 'nordic-'); ?></label>
    <select name="uniwin_shipit_country" id="metabox_nods_uniwin_shipit_country">
        <option value="">Select country</option>
        <?php foreach (WC()->countries->get_countries() as $country_code => $label) {
            $selected = ($_country) ? (($country_code == $_country) ? "selected" : "")  : "";
        ?>
            <option value="<?php echo $country_code; ?>" <?php echo $selected; ?>><?php echo esc_html($label); ?></option>
        <?php
        }
        ?>
    </select>
</div>

<div class="form-field">
    <label><?php echo esc_html__("service", 'nordic-'); ?></label>
    <div id="nods_uniwin_shipit_service_woo_odetail">
        <?php
        if (!empty($_method_instance_id)) {
            global $wpdb;
            $tblname = $wpdb->prefix . 'shipit_uniwin_shipping_mapping';
            $sql = $wpdb->prepare("SELECT * FROM `$tblname` WHERE shipping_method = %s", $_method_instance_id);
            $result = $wpdb->get_results($sql);
            if (!empty($agent_data)) {
                echo NODS_Shipment_Functions::shipit_uniwin_shipment_service_select_datas('uniwin_shipit_service', $agent_data['serviceId'], true, $_country);
            } else if (!empty($result)) {
                echo NODS_Shipment_Functions::shipit_uniwin_shipment_service_select_datas('uniwin_shipit_service', $result[0]->service_id, true, $_country);
            } else {
                echo NODS_Shipment_Functions::shipit_uniwin_shipment_service_select_datas('uniwin_shipit_service', "", true, $_country);
            }
        } else {
            echo NODS_Shipment_Functions::shipit_uniwin_shipment_service_select_datas('uniwin_shipit_service', "", true, $_country);
        }
        ?>
    </div>
    <input type="hidden" value="<?php echo (!empty($agent_data)) ? $agent_data['id'] : "" ?>" name="uniwin_shipit_pickup" />
</div>
<div class="form-field">
    <input type="hidden" name="shipit_uniwin_service_price" />
    <label><?php echo esc_html__("Weight (kg)", 'nordic-'); ?></label>
    <input type="text" name="uniwin_shipit_weight" class="form-control uniwin_shipit_weight" value="<?php echo $weight; ?>" />
</div>
<div class="form-field">
    <label><?php echo esc_html__("Dimensions L×W×H (cm)", 'nordic-'); ?></label>
</div>
<div class="shipit_uniwin_dimenstions">
    <div class="form-field">
        <input type="text" name="uniwin_shipit_length" value="15" class="form-control" />
    </div>
    <div class="form-field">
        <input type="text" name="uniwin_shipit_width" value="10" class="form-control" />
    </div>
    <div class="form-field">
        <input type="text" name="uniwin_shipit_height" value="1" class="form-control" />
    </div>
</div>

<div class="form-field">
    <label><?php echo esc_html__("Parcels", 'nordic-'); ?></label>
    <input type="text" name="uniwin_shipit_parcel" class="form-control" value="1" />
</div>
<div class="form-field">
    <label><?php echo esc_html__("Additional services", 'nordic-'); ?></label>
</div>
<div class="form-field">
    <input type="checkbox" class="form-control" name="uniwin_shipit_fragile" value="1" /> <?php echo esc_html__("Fragile", 'nordic-'); ?>
</div>
<div class="form-field">
    <input type="checkbox" class="form-control" name="uniwin_shipit_label" value="1" /> <?php echo esc_html__("Return Label", 'nordic-'); ?>
</div>
<div class="form-field">
    <input type="checkbox" class="form-control" name="uniwin_shipit_order_confirm" value="1" /> <?php echo esc_html__("Send order confirmation Email", 'nordic-'); ?>
</div>
<input type="hidden" id="is_shipit_info" value="<?php echo (!empty($shipment_info)) ? 1 : 0; ?>" />
<input type="hidden" class="nordic-shipping-post-id" value="<?php echo $post->ID; ?>" />
<div class="shipit_uniwin_dimenstions">
    <div class="form-field">
        <button class="button button-primary" value="save" name="uniwin_shipit_save" id="uniwin_shipit_save"><?php echo esc_html__("Process", 'nordic-'); ?></button>
    </div>
    <div class="form-field">
        <a href="#" id="shipit-uniwin-fetch-prices"><?php echo esc_html__("Fetch prices", 'nordic-'); ?></a>
    </div>
</div>

<?php

if (!empty($shipment_info)) {
?>
    <div class="col-md-12 form-group">
        <ul class="order_notes">
            <?php
            $shipment_response_id = $shipment_info[0];
            for ($n = $shipment_response_id; $n >= 1; $n--) {
                //for ($i = 1; $i <= $shipment_response_id; $i++) {
                $sinfo = get_post_meta($post->ID, "wc_shipit_uniwin_shipment_response_" . $n)[0];
                if (!empty($sinfo)) {
                    $jsondata = json_decode($sinfo['shipit_response'], true);
            ?>
                    <li class="note system-note">
                        <?php if (isset($jsondata['status']) && ($jsondata['status'] == 1)) { ?>
                            <div class="note_content shipit_uniwin_success">
                                <p>Status : <b>Success</b> </p>
                                <?php if (!empty($sinfo['carrier_name']) && ($sinfo['carrier_name'] != " - ")) { ?>
                                    <p>Created Shipit <b><?php echo esc_html($sinfo['carrier_name']); ?></b> Shipment </p>
                                <?php } ?>
                                <p>Tracking No : <b><?php echo esc_html($jsondata['trackingNumber']); ?></b></p>
                                <div class="row">
                                    <?php
                                    $printing_shiping_label = NODS_Common_Functions::printing_shiping_label();
                                    if ($printing_shiping_label == 1) {
                                        $docs_label = get_post_meta($post->ID, 'shipit_uniwin_shipping_label');
                                        if (!empty($docs_label)) {
                                            $doc_key = "shipit_uniwin_shipping_label_data_" . explode('-', $docs_label[0])[0] . '-1';
                                    ?>
                                            <div class="col-md-3">
                                                <p><?php echo $order->get_meta($doc_key) ? '<a download="' . $post->ID . '-label" href="data:application/pdf;base64,' . $order->get_meta($doc_key) . '">Label</a>' : '<a target="_blank" href="' . esc_url($jsondata['freightDoc'][0]) . '">Label</a>'; ?></p>
                                            </div>
                                    <?php
                                        }
                                    }

                                    ?>

                                    <?php if (isset($jsondata['proforma']) && !empty($jsondata['proforma'])) {
                                        $custom_docs = get_post_meta($post->ID, 'shipit_uniwin_custom_docs');
                                        if (!empty($custom_docs)) {
                                            $custom_docs_key = "shipit_uniwin_shipping_label_data_" . $custom_docs[0];
                                            //$custom_docs_url = NODS_Common_Functions::document_url($post->ID, $custom_docs[0]);
                                    ?>
                                            <div class="col-md-3">
                                                <p><?php echo $order->get_meta($custom_docs_key) ? '<a download="' . $post->ID . '-customDocs" href="data:application/pdf;base64,' . $order->get_meta($custom_docs_key) . '">Customs Docs</a>' : ''; ?></p>
                                            </div>
                                    <?php }
                                    } ?>
                                    <div class="col-md-3">
                                        <p><a target="_blank" href="<?php echo esc_url($jsondata['trackingUrls'][0]); ?>">Track</a></p>
                                    </div>
                                </div>
                            </div>
                            <p class="meta">
                                <abbr class="exact-date">
                                    <?php echo esc_html($sinfo['created_at']); ?></abbr>
                            </p>
                        <?php } else { ?>
                            <div class="note_content shipit_uniwin_error">
                                <p>Status : <b>Fail</b> </p>
                                <?php if (!empty($jsondata['error'])) { ?>
                                    <p><?php echo $jsondata['error']['message']; ?></p>
                                <?php } ?>
                                <?php echo !empty($jsondata['errorbag']) ? '<p>' . esc_html(implode(',', $jsondata['errorbag'])) . '</p>' : ""; ?>
                            </div>
                            <p class="meta">
                                <abbr class="exact-date">
                                    <?php echo esc_html($sinfo['created_at']); ?></abbr>
                            </p>
                        <?php } ?>
                    </li>
            <?php   }
            }
            ?>
        </ul>

    </div>

<?php } ?>
<?php
if (!empty($agent_data)) {

    $address_name = $agent_data['carrier'] . " - " . html_entity_decode($agent_data['name'], ENT_QUOTES, 'UTF-8') . " , " . $agent_data['address1'];
    //$html .= "<img src='" . esc_url($agent_data['carrierLogo']) . "'/>";
    $html = "<div class='show_carrier_agent_text'><h4>Carrier Agent (Chosen by customer) : </h4>";
    $html .= "<p>" . $address_name . "," . esc_html($agent_data['city']) . "," . esc_html($agent_data['zipcode']) . "</p></div>";
    $html .= "<a href='#' id='shipit_uniwin_agents'>Change</a>";
    echo wp_kses_post($html);
} else {
    $html = "<a href='#' id='shipit_uniwin_agents'>Add Carrier agent</a>";
    echo wp_kses_post($html);
}
?>

<?php include("admin_price_html.php");

include("carrier_agent_html.php");



?>