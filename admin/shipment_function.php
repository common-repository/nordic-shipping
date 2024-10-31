<?php
if (!defined('ABSPATH')) exit;
class NODS_Shipment_Functions
{

    /**
     * Create shipment api using curl method
     */
    public static function create_shipment($data)
    {

        $method =  'shipment';
        $sender = NODS_Common_Functions::NODS_get_sender_address();
        $check_outside_eu = NODS_Common_Functions::outside_eu_enabled();
        $order = wc_get_order($data['oid']);
        // Set shipping names
        $receiver_name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        $contact_name = '';
        if ($order->get_shipping_company()) {
            $receiver_name = $order->get_shipping_company();
            $contact_name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        }
        $error = "";
        $body = [
            "sender" => [
                "name" => $sender->name,
                "email" => $sender->email,
                "phone" => "+" . $sender->phone,
                "address" => $sender->stree_address,
                "city" => $sender->city,
                "postcode" => $sender->postcode,
                "country" => $sender->country,
                "isCompany" => true,
                "contactPerson" => $sender->name,
                "vatNumber" => $sender->vat
            ],
            "receiver" => [
                "name" => $receiver_name,
                "email" => NODS_Common_Functions::shipit_uniwin_get_shipping_email($order),
                "phone" => NODS_Common_Functions::check_phone_no(NODS_Common_Functions::shipit_uniwin_get_shipping_phone($order)),
                "address" => implode(' ', [$order->get_shipping_address_1(), $order->get_shipping_address_2()]),
                "city" => $order->get_shipping_city(),
                "postcode" => $order->get_shipping_postcode(),
                "country" => $data['uniwin_shipit_country'],
                'isCompany' => !!$order->get_shipping_company(),
                'contactPerson' => $contact_name,
            ],
            "parcels" => [
                [
                    'copies' => $data['uniwin_shipit_parcel'],
                    "weight" => $data['uniwin_shipit_weight'],
                    "width" => $data['uniwin_shipit_width'],
                    "height" => $data['uniwin_shipit_height'],
                    "length" => $data['uniwin_shipit_length'],
                    "type" => "PACKAGE",
                    "contents" => $sender->contents
                ]
            ],
            "serviceId" => $data['uniwin_shipit_service'],
            "type" => "MERCHANDISE",
            "contents" => $sender->contents,
            "returnFreightDoc" => !empty($data['uniwin_shipit_label']) ? true : false,
            "fragile" => !empty($data['uniwin_shipit_fragile']) ? true : false,
            "sendOrderConfirmationEmail" => !empty($data['uniwin_shipit_order_confirm']) ? true : false,
            "freeText" => sprintf('#%s', $order->get_order_number()),
            "reference" => sprintf('#%s', $order->get_order_number()),
            "resellerId" => 46,
        ];
        if (!empty($data['uniwin_shipit_pickup'])) {
            $body["pickupId"] =  $data['uniwin_shipit_pickup'];
        }


        if ($check_outside_eu == 1) {

            $body['proforma'] = [
                "invoiceNumber" => (int) $data['oid'],
                "invoiceCurrency" => $order->get_currency(),
                "invoiceSubTotal" => (float) $order->get_total() - ($order->get_shipping_total() + $order->get_shipping_tax()),
                "freightCharges" => (float) $order->get_total_shipping() + $order->get_shipping_tax(),
                "invoiceTotal" => (float) $order->get_total()
            ];
            $body['valueAmount'] = (float) $order->get_total();
            // Add items
            $body['items'] = [];
            foreach ($order->get_items() as $item) {

                $customs = self::NODS_get_custom_data($order, $item);


                $total = $item->get_total('edit') + $item->get_total_tax('edit');
                $unit_price = round(($total / $item->get_quantity()), 2);

                if ($customs !== false) {
                    $body['items'][] = [
                        'description' => $customs['description'],
                        'quantity' => $item->get_quantity(),
                        'unitValue' => $unit_price,
                        'currency' => $order->get_currency(),
                        'unitWeight' => $customs['unit_weight'],
                        'hsTariffCode' => $customs['tariff_code'],
                        'countryOfOrigin' => $customs['origin'],
                    ];
                }
            }
        }

        $checksum = NODS_Common_Functions::calculate_checksum($body);
        $postdata = json_encode($body);
        $r = NODS_Common_Functions::curl_all_method($method, $checksum, "PUT", $postdata);
        return $r;
    }

    /**
     * Get customs information for product in a order
     */
    public static function NODS_get_custom_data($order, $item)
    {
        $product = false;
        if (is_callable(array($item, 'get_product'))) {
            $product = $item->get_product();
        }

        if ($product) {
            $product_id = $product->get_id();

            // If product is variation, info must be get from the parent product
            if ('variation' === $product->get_type()) {
                $product_id = $product->get_parent_id();
            }

            $desc = get_post_meta($product_id, '_NODS_shipit_uniwin_custom_description', true);
            if (NODS_Common_Functions::product_name_desc() == 1) {
                $desc = $product->get_name();
            }
            return array(
                'tariff_code' => get_post_meta($product_id, '_NODS_shipit_uniwin_tariff_code', true),
                'description' => $desc,
                'origin' => get_post_meta($product_id, '_NODS_shipit_uniwin_custom_orgin', true),
                'unit_weight' => floatval($product->get_weight())
            );
        }

        return false;
    }

    /**
     * shipit service price
     */
    public static function shipment_service_price($data)
    {

        $order_id = sanitize_text_field($data['order_id']);
        $weight = NODS_Common_Functions::shipit_uniwin_return_parse_number($data['weight']);
        $height = NODS_Common_Functions::shipit_uniwin_return_parse_number($data['height']);
        $width = NODS_Common_Functions::shipit_uniwin_return_parse_number($data['width']);
        $length = NODS_Common_Functions::shipit_uniwin_return_parse_number($data['length']);
        $parcels = NODS_Common_Functions::shipit_uniwin_return_parse_number($data['parcels'], true);
        $fragile = !empty($data['fragile']) ? true : false;

        $order = wc_get_order($order_id);
        if (!$order) {
            die('Order not found with ID ' . $order_id);
        }

        $postcode = $order->get_shipping_postcode();
        $country = $order->get_shipping_country();
        $company_receiver = !!$order->get_shipping_company();

        $sender = NODS_Common_Functions::NODS_get_sender_address();

        $methods = "";

        if (!empty($sender)) {
            $body = [
                'sender' => [
                    "postcode" => $sender->postcode,
                    "country" => $sender->country,
                ],
                'receiver' => [
                    'postcode' => $postcode,
                    'country' => $country,
                ],
                'parcels' => [
                    [
                        'type' => 'PACKAGE',
                        'length' => $length,
                        'width' => $width,
                        'height' => $height,
                        'weight' => $weight,
                        'copies' => $parcels,
                    ]
                ],
                'companyIsReceiving' => $company_receiver,
                'fragile' => $fragile,
                'retrievePickupLocations' => false,
            ];
            $api_url = 'shipping-methods';
            $checksum = NODS_Common_Functions::calculate_checksum($body);
            $postdata = json_encode($body);
            $response = (array) NODS_Common_Functions::curl_all_method($api_url, $checksum, "POST", $postdata);
            if ($response['status'] == 1) {
                $methods = (array) $response['methods'];
            }
        }

        return array("postcode" => $postcode, "country" => $country, "width" => $width, "height" => $height, "length" => $length, "weight" => $weight, "fragile" => $fragile, "parcels" => $parcels, "methods" => $methods);
    }

    public static function shipment_carrier_agent_data($data)
    {
        $order_id = $data['order_id'];
        $order = wc_get_order($order_id);
        $shipping_methods = $order->get_shipping_methods();
        $default_shipping_method = FALSE;
        if (!empty($shipping_methods)) {
            $shipping_method = reset($shipping_methods);
            if (!empty($shipping_method) && is_a($shipping_method, 'WC_Order_Item_Shipping')) {
                $default_shipping_method = $shipping_method->get_method_id();
            }
        }
        $options = [];
        $html = '';
        if (isset($data['service_id']) && !empty($data['service_id'])) {
            $body = [
                'postcode' => $data['postal_code'],
                'country' => $data['country'],
                'serviceId' => array($data['service_id']),
                'type' => "PICKUP"
            ];
            $instance_id = $data['instance_id'];
            $api_url = 'agents';

            $checksum = NODS_Common_Functions::calculate_checksum($body);
            $postdata = json_encode($body);
            $response = (array) NODS_Common_Functions::curl_all_method($api_url, $checksum, "POST", $postdata);

            if ($response['status'] == 1 && isset($response['locations'])) {
                $locationsArray = (array) $response['locations'];
                if (!empty($response['locations'])) {
                    $data_success = "";
                    foreach ($locationsArray as $locations) {
                        $location = (array) $locations;
                        $address_name = $location['carrier'] . " - " . $location['name'] . " , " . $location['address1'];
                        $options[$location['id']] = $address_name;
                        foreach ($location as $key => $loc) {
                            $hidden_name = "carrier_agents:" . $instance_id . ":" . $location['id'] . ":" . $key;
                            if ($key == "openingHours") {
                                $loc = (array) $loc;
                                $html .= "<input type='hidden' name='" . $hidden_name . "' value='" . json_encode($loc) . "'/>";
                            } else {
                                $html .= "<input type='hidden' name='" . $hidden_name . "' value='" . $loc . "'/>";
                            }
                        }
                    }
                } else {
                    $data_success = "Nodata";
                }
            } else {
                $data_success = "Error";
            }
        }

        return array('order' => $order, 'default_shipping_method' => $default_shipping_method, 'default_postcode' => $order->get_shipping_postcode(), 'country' => $order->get_shipping_country(), 'shipping_methods' =>  self::NODS_get_shipping_method_instances(), "agents" => $options, "agent_data" => $html, "datas" => $data_success);
    }

    public static function shipment_carrier_agent_save_data($data)
    {
        $order_id = $data['order_id'];
        $agent_id = $data['agent_id'];
        $agent_data = array(
            "id" => $data['agent_data_id'],
            "name" => htmlentities($data['agent_data_name'], ENT_QUOTES, 'UTF-8'),
            "address1" => $data['agent_data_address1'],
            "zipcode" => $data['agent_data_zipcode'],
            "city" => $data['agent_data_city'],
            "countryCode" => $data['agent_data_countryCode'],
            "serviceId" => $data['agent_data_serviceId'],
            "carrier" => $data['agent_data_carrier'],
            "carrierLogo" => $data['agent_data_carrierLogo']

        );
        if (!empty($agent_id)) {
            update_post_meta($order_id, '_shipit_uniwin_carrier_agent_id', $agent_id);
            update_post_meta($order_id, "_shipit_uniwin_carrier_agent_data", json_encode($agent_data));
            $response = array("result" => "success", "html" => "Agent data saved successfully!");
        } else {
            $response = array("result" => "error", "html" => "Can't able to save the agent data!");
        }
        return $response;
    }

    /**
     * Get a list of all shipping method instances which support carrier agents
     */
    public static function NODS_get_shipping_method_instances()
    {
        $shipping_zones = array(new WC_Shipping_Zone(0));
        $shipping_zones = array_merge($shipping_zones, WC_Shipping_Zones::get_zones());


        $instances = array();
        $pickup_ids = "";
        $instance_id = "";
        foreach ($shipping_zones as $shipping_zone) {
            if (is_array($shipping_zone) && isset($shipping_zone['zone_id'])) {
                $shipping_zone = WC_Shipping_Zones::get_zone($shipping_zone['zone_id']);
            } else if (!is_object($shipping_zone)) {
                // Skip
                continue;
            }

            foreach ($shipping_zone->get_shipping_methods() as $instance_id => $shipping_method) {
                if (is_object($shipping_method)) {
                    if ($shipping_method->id == "Shipit_shipping") {
                        $instance_id = $shipping_method->instance_id;
                        if (!empty($shipping_method->instance_settings['se_service_ids'])) {
                            $pickup_ids = $shipping_method->instance_settings['se_service_ids'];
                        } else {
                            $pickup_ids = $shipping_method->instance_settings['service_ids'];
                        }
                    }
                }
            }
        }
        return array('instance_id' => $instance_id, 'pickup_ids' => $pickup_ids);
    }

    /**
     * List the available services based on sender and receiver address
     */
    public static function receiver_shipment_service_list($data)
    {
        $sender = NODS_Common_Functions::NODS_get_sender_address();

        if (!empty($sender)) {
            $order = new WC_Order($data['post_id']);
            $shipping_address = $order->get_address('shipping');
            $weight = $data['weight'];
            $length = ($data['length']) ? $data['length'] : 15;
            $height = ($data['height']) ? $data['height'] : 1;
            $width = ($data['width']) ? $data['width'] : 10;
            $url =  'shipping-methods';
            $body = [
                "sender" => [
                    "postcode" => $sender->postcode,
                    "country" => $sender->country
                ],
                "receiver" => [
                    "postcode" => $shipping_address['postcode'],
                    "country" => $shipping_address['country']
                ],
                "parcels" => [
                    [
                        "type" => "PACKAGE",
                        "length" => $length,
                        "width" => $width,
                        "height" => $height,
                        "weight" => ($weight > 0.0) ? $weight : 0.6,
                    ]
                ],
                "retrievePickupLocations" => false,
                "maxPickupLocations" => 6
            ];
            $checksum = NODS_Common_Functions::calculate_checksum($body);
            $postdata = json_encode($body);
            $r = NODS_Common_Functions::curl_all_method($url, $checksum, "POST", $postdata);
            $html = "";
            if ($r['status'] == 1) {
                $html .= '<option value="" data-price="" selected> Select service </option>';
                foreach ($r['methods'] as $methods) {
                    $html .= '<option value="' . $methods['serviceId'] . '" data-price="' . $methods['price'] . '">' . $methods['carrier'] . " - " . $methods['serviceName'] . '</option>';
                }
            } else {
                $html = "<option value=''> No service available</option>";
            }
            $response = array("result" => "success", "html" => $html);
        } else {
            $response = array("result" => "error", "html" => "Please update the shipit settings!");
        }

        return $response;
    }

    /**
     * shipit service in select option - shown in admin order detail meta box
     */
    public static function shipit_uniwin_shipment_service_select_datas($field_name = 'uniwin_shipit_service', $default_value = '', $include_blank = FALSE, $country)
    {
        $html = "";
        $html .= '<select name="' . $field_name . '">';
        if ($include_blank) {
            $html .= '<option value="">No Shipit method</option>';
        }
        if ($country == "FI") {
            $country_services = self::nods_fi_carrier_agents();
        } else if ($country == "SE") {
            $country_services = self::nods_se_carrier_agents();
        } else {
            $country_services = self::all_carriers_agents();
        }
        foreach ($country_services as $carrier_id => $carrier) {
            if (empty($carrier['services'])) {
                continue;
            }
            $html .= '<optgroup label="' . $carrier['title'] . '">';
            foreach ($carrier['services'] as $service_id => $carrier_title) {
                $selected = "";
                if ($service_id == $default_value) {
                    $selected = "selected";
                }
                $html .= '<option value="' . $service_id . '" ' . $selected . '>' . $carrier_title . '</option>';
            }
            $html .= ' </optgroup>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Shipment services
     */
    public static function nods_fi_services()
    {
        $services = array();

        foreach (self::nods_fi_carrier_agents() as $carrier_id => $carrier) {
            $services = array_merge($services, $carrier['services']);
        }

        return $services;
    }

    public static function nods_se_services()
    {
        $services = array();

        foreach (self::nods_se_carrier_agents() as $carrier_id => $carrier) {
            //   print_r($services);
            $services = array_merge($services, $carrier['services']);
        }
        return $services;
    }

    public static function services()
    {
        $services = array();

        foreach (self::all_carriers_agents() as $carrier_id => $carrier) {
            $services = array_merge($services, $carrier['services']);
        }

        return $services;
    }

    public static function nods_fi_carrier_agents()
    {
        return array(
            "mh" => array(
                "title" => "Matkahuolto",
                "services" => array(
                    "mh.mh3050" => "Jakopakett",
                    "mh.mh80" => "Lähellä-paketti",
                    "mh.mh84" => "XXS-Paketti",
                    "mh.mh34" => "Kotijakelu",
                    "mh.mh97" => "Euroopan Kotijakelu (Home delivery to europe)",
                    "mh.mh96" => "Euroopan Jakopaketti (door delivery for companies)",
                    "mh.mh95" => "Euroopan Lähellä-paketti (delivery to pickup points in Europe)",
                )
            ),
            "glsfi" => array(
                "title" => "GLS",
                "services" => array(
                    "glsfi.glsfiebp" => "EuroBusinessParcel",
                )
            ),
            "posti" => array(
                "title" => "Posti",
                "services" => array(
                    "posti.po2103" => "Postipaketti",
                    "posti.po2104" => "Kotipaketti",
                    "posti.po2461" => "Pikkupaketti",
                    "posti.po2711" => "Parcel Connect",
                    "posti.po2102" => "Express",
                    "posti.itpr" => "Priority",
                    "posti.po2017" => "EMS",
                    "posti.it14i" => "Express (international)",
                    "posti.po2351" => "PickUp Parcel",
                    "posti.po2331" => "Postal Parcel Baltics",
                    "itellalog.pof1" => "Freight",
                )
            ),
            "itella" => array(
                "title" => "Itella",
                "services" => array(

                    "posti.po2711ee" => "Parcel Connect Baltics",
                    "posti.it14iee" => "Posti Business Day Parcel Baltics",
                    "posti.itky14iee" => "Posti Business Day Pallet Baltics",
                )
            ),
            "jakeluyhtio_suomi" => array(
                "title" => "Jakeluyhtiö Suomi (JYS)",
                "services" => array(
                    "jakeluyhtio_suomi.pienpaketti" => "Pienpaketti",
                )
            ),
            "sbtlfirrex" => array(
                "title" => "DB Schenker",
                "services" => array(
                    "sbtlfirrex.sbtlfirrex" => "Noutopistepaketti",
                    "kl.klgrp" => "DB SCHENKERsystem",
                    "sbtlfiexp.sbtlfiexp" => "DB SCHENKERparcel",
                )
            ),
            "ups" => array(
                "title" => "UPS",
                "services" => array(
                    "ups.upsexpdtp" => "Expedited",
                    "ups.upsexpp" => "Express",
                    "ups.upssavp" => "Express Saver",
                    "ups.upsstdp" => "Standard",
                )
            ),
            "plscm" => array(
                "title" => "Postnord",
                "services" => array(
                    "plscm.p19fi" => "MyPack",
                    "plscm.p17fidpd" => "MyPack Home(for Europe)", // siva changes
                    "plscm.p17fi" => "MyPack Home",
                )
            ),
            "fedex" => array(
                "title" => "Fedex",
                "services" => array(
                    "fedex.fdxiep" => "Economy",
                    "fedex.fdxipp" => "Express",
                )
            ),
            "omni" => array(
                "title" => "Omniva",
                "services" => array(
                    "omni.omnice" => "Pickup Point Delivery",
                    "omni.omniparcelmachine" => "Omniva Parcelmachine",
                    "omni.omniqk" => "Courier Delivery",
                    "omni.omnixn" => "International Maxi Letter",
                )
            ),
            "dpd" => array(
                "title" => "DPD Baltic",
                "services" => array(
                    "dpd.dindpdbaltpickup" => "DPD Parcelmachine and Pickup Point",
                    "dpd.dpdeeclassic" => "DPD Classic",
                    "dpd.dpdbaltpriv" => "DPD Private",
                    "dpd.dpdeeclassicpallet" => "DPD Classic Pallet",
                    "dpd.dpdbaltprivpallet" => "DPD Private Pallet"
                )
            )
        );
    }

    public static function nods_se_carrier_agents()
    {
        return array(
            "airmee" => array(
                'title' => 'Airmee',
                'services' => array(
                    "airmee.airmee" => "Airmee",
                    "airmee.airmeelo" => "Airmee Locker",
                    "airmee.airmeepo" => "Airmee Point",
                ),
            ),
            "bcmse" => array(
                "title" => "Citymail",
                "services" => array(
                    "bcmse.bcmseblp" => "Citymail Brevlådepaket",
                    'bcmse.bcmseblpprio' => "Citymail Brevlådepaket Priority",
                ),
            ),
            "budbee" => array(
                "title" => "Budbee",
                "services" => array(
                    'budbee.budbeebox' => "Budbee Box",
                    "budbee.budbeedlvday" => "Budbee Hemleverans Dagtid",
                    "budbee.budbeedlvevn" => "Budbee Flex",
                    "budbee.budbeeexpress" => "Budbee Express Delivery",
                    "budbee.budbeesmd" => "Budbee Same Day",
                    "budbee.budbeestd" => "Budbee",
                ),
            ),
            "cg" => array(
                "title" => "Bussgods",
                "services" => array(
                    "cg.cgb" => "Bussgods",
                ),
            ),
            "dhlroad" => array(
                "title" => "DHL Freight",
                "services" => array(
                    "dhlroad.aex" => "DHL Paket",
                    "dhlroad.apc" => "DHL Parcel Connect",
                    "dhlroad.asp2" => "DHL Pall",
                    "dhlroad.aspo" => "DHL Service Point",
                    "dhlroad.aspor" => "DHL Service Point Return",
                    "dhlroad.aswh2" => "DHL Home Delivery",
                ),
            ),
            "fedex" => array(
                "title" => "FedEx",
                "services" => array(
                    "fedex.fdxiep" => "International Economy",
                    "fedex.fdxiepf" => "International Economy Freight",
                    "fedex.fdxipd" => "International Priority Docs",
                    "fedex.fdxipp" => "International Priority",
                ),
            ),
            "instabox" => array(
                "title" => "Instabox",
                "services" => array(
                    "instabox.instaboxstd" => "Instabox",
                ),
            ),
            "pbrev" => array(
                "title" => "Postnord Brev",
                "services" => array(
                    "pbrev.pua" => "PostNord - Varubrev",
                    "pbrev.p34" => "PostNord - Spårbart brev utrikes",
                ),
            ),
            "plab" => array(
                "title" => "Postnord Sweden",
                "services" => array(
                    "plab.p17" => "MyPack Home",
                    "plab.p18" => "PostNord Parcel",
                    "plab.p19" => "MyPack Collect",
                    "plab.p52" => "Postnord Pallet Sverige",
                ),
            ),
            "pnl" => array(
                "title" => "Bring",
                "services" => array(
                    "pnl.pnl330" => "Bring Business Parcel",
                    "pnl.pnl340" => "Bring PickUp Parcel",
                ),
            ),
            "posti" => array(
                "title" => "Posti",
                "services" => array(
                    "posti.po2351" => "PickUp Parcel",
                ),
            ),
            "sbtl" => array(
                "title" => "DB Schenker",
                "services" => array(
                    "sbtl.bhp" => "DB SCHENKERparcel Ombud",
                    "sbtl.bpa" => "DB SCHENKERparcel",
                    "sbtl.bphdap" => "DB SCHENKERparcel Hem dag med kvittens (Paket)",
                    "sbtl.bphdp" => "DB SCHENKERparcel Hem dag utan kvittens",
                    "sbtl.bphkap" => "DB SCHENKERparcel Hem kväll med kvittens",
                ),
            ),
        );
    }

    /**
     * Shipment all carriers agents
     */
    public static function all_carriers_agents()
    {
        return array(
            'mh' => array(
                'title' => 'Matkahuolto',
                'services' => array(
                    'mh.mh80' => 'Lähellä-paketti',
                    'mh.mh10' => 'Bussipaketti',
                    'mh.mh20' => 'Pikapaketti',
                    'mh.mh3050' => 'Jakopaketti',
                    'mh.mh34' => 'Kotijakelu',
                    'mh.mh84' => 'XXS-paketti',
                    'mh.mh97' => 'Euroopan Kotijakelu',
                    'mh.mh96' => 'Euroopan Jakopaketti',
                    'mh.mh95' => 'Euroopan Lähellä-paketti',
                    'mh.mh34_bike' => 'Pyöräpaketti Kotijakelu',
                    'mh.mh3050_bike' => 'Pyöräpaketti Jakopaketti',
                ),
            ),
            'glsfi' => array(
                'title' => 'GLS',
                'services' => array(
                    'glsfi.glsfiebp' => 'EuroBusinessParcel',
                ),
            ),
            'itellalog' => array(
                'title' => 'Posti',
                'services' => array(),
            ),
            'posti' => array(
                'title' => 'Posti',
                'services' => array(
                    'posti.po2103' => 'Postipaketti',
                    'posti.po2104' => 'Kotipaketti',
                    'posti.po2461' => 'Pikkupaketti',
                    'posti.po2711' => 'Parcel Connect',
                    'posti.po2351' => 'PickUp Parcel',
                    'posti.po2331' => 'Baltian Postipaketti',
                    'posti.po2102' => 'Express',
                    'posti.itpr' => 'Priority',
                    'posti.po2017' => 'EMS',
                    'posti.it14i' => 'Express (international)',
                    'itellalog.pof1' => 'Freight',
                ),
            ),
            'kl' => array(
                'title' => 'DB Schenker',
                'services' => array(),
            ),
            'sbtlfiexp' => array(
                'title' => 'DB Schenker',
                'services' => array(),
            ),
            'sbtlfirrex' => array(
                'title' => 'DB Schenker',
                'services' => array(
                    'sbtlfirrex.sbtlfirrex' => 'Noutopistepaketti',
                    'kl.klgrp' => 'DB SCHENKERsystem',
                    'sbtlfiexp.sbtlfiexp' => 'DB SCHENKERparcel',
                ),
            ),
            'netlux' => array(
                'title' => 'Netlux',
                'services' => array(
                    'netlux.netluxparcel' => 'Parcel',
                    'netlux.netluxpallet' => 'Pallet',
                ),
            ),
            'fiuge' => array(
                'title' => 'Fiuge',
                'services' => array(
                    'fiuge.exprs' => 'Express',
                ),
            ),
            'wolt' => [
                'title' => 'Wolt',
                'services' => [
                    'wolt.wolt' => 'Wolt',
                ],
            ],
            'jakeluyhtio_suomi' => [
                'title' => 'Jakeluyhtiö Suomi',
                'services' => [
                    'jakeluyhtio_suomi.pienpaketti' => 'Pienpaketti',
                ],
            ],
            /*
			'shipit' => array(
				'title' => 'Shipit',
				'services' => array(
					'shipit.shipitexpsv' => 'Shipit Express Saver',
					'shipit.shipitstd' => 'Shipit Standard',
				),
			),
			*/
            'ups' => array(
                'title' => 'UPS',
                'services' => array(
                    'ups.upsexpdtp' => 'Expedited',
                    'ups.upsexpp' => 'Express',
                    'ups.upssavp' => 'Express Saver',
                    'ups.upsstdp' => 'Standard',
                    'ups.accesp' => 'Express Access Point',
                    'ups.accespstd' => 'Access Point Standard',
                ),
            ),
            'plscm' => array(
                'title' => 'Postnord',
                'services' => array(
                    'plscm.p19fi' => 'MyPack',
                    'plscm.p19fidpd' => 'MyPack DPD',
                    'plscm.p17fi' => 'MyPack Home',
                    'plscm.p17fidpd' => 'MyPack Home DPD',
                    'plscm.p18fi' => 'Parcel',
                    'plscm.p18fidpd' => 'Parcel DPD',
                ),
            ),
            'fedex' => array(
                'title' => 'Fedex',
                'services' => array(
                    'fedex.fdxiep' => 'Economy',
                    'fedex.fdxipp' => 'Express',
                ),
            ),
            'itella_ee' => [
                'title' => 'Itella (EE)',
                'services' => [
                    'posti.po2351ee' => 'PickUp Parcel',
                    'posti.po2352ee' => 'Home Parcel',
                    'posti.po2711ee' => 'Parcel Connect Home Delivery',
                    'posti.po2711eepp' => 'Parcel Connect Pickup Points',
                    'posti.it14iee' => 'Business Day Parcel Baltics',
                    'posti.itky14iee' => 'Business Day Pallet Baltics',
                ]
            ],
            'itella_lt' => [
                'title' => 'Itella (LT)',
                'services' => [
                    'posti.po2711lt' => 'Parcel Connect Home Delivery',
                    'posti.po2711ltpp' => 'Parcel Connect Pickup Points',
                ]
            ],
            'itella_lv' => [
                'title' => 'Itella (LV)',
                'services' => [
                    'posti.po2711lv' => 'Parcel Connect Home Delivery',
                    'posti.po2711lvpp' => 'Parcel Connect Pickup Points',
                ]
            ],
            'omni' => [
                'title' => 'Omniva (EE)',
                'services' => [
                    'omni.omnice' => 'Pickup Point Delivery',
                    'omni.omniparcelmachine' => 'Parcelmachine',
                    'omni.omniqk' => 'Courier Delivery',
                    'omni.omnixn' => 'International Maxi Letter',
                ]
            ],
            'dpd' => [
                'title' => 'DPD Baltic (EE)',
                'services' => [
                    'dpd.dindpdbaltpickup' => 'Parcelmachine and Pickup Point',
                    'dpd.dpdeeclassic' => 'Classic',
                    'dpd.dpdbaltpriv' => 'Private',
                    'dpd.dpdeeclassicpallet' => 'Classic Pallet',
                    'dpd.dpdbaltprivpallet' => 'Private Pallet',
                ]
            ],
        );
    }


    /**
     * Title for shipment service
     */
    public static function shipment_service_title($service_code, $with_carrier_title = FALSE, $country_code)
    {
        if ($country_code == "FI") {
            $services = self::nods_fi_services();
        } else if ($country_code == "SE") {
            $services = self::nods_se_services();
        } else {
            $services = self::services();
        }
        $countrycode = $country_code;
        if (isset($services[$service_code])) {
            if (!$with_carrier_title) {
                return $services[$service_code];
            } else {
                return sprintf("%s - %s", self::shipment_serivice_carrier_title($service_code, $countrycode), $services[$service_code]);
            }
        }

        return FALSE;
    }

    /**
     * Title for carrier
     */
    public static function shipment_serivice_carrier_title($service_code, $countrycode)
    {
        if ($countrycode == "FI") {
            $carriers = self::nods_fi_carrier_agents();
        } else if ($countrycode == "SE") {
            $carriers = self::nods_se_carrier_agents();
        } else {
            $carriers = self::all_carriers_agents();
        }
        foreach ($carriers as $carrier_id => $carrier) {
            foreach ($carrier['services'] as $service_id => $service) {
                if ($service_code == $service_id) {
                    return $carrier['title'];
                }
            }
        }
        return false;
    }

    public static function nods_service_carrier_title($service_code, $countrycode)
    {
        if ($countrycode == "FI") {
            $carriers = self::nods_fi_carrier_agents();
        } else if ($countrycode == "SE") {
            $carriers = self::nods_se_carrier_agents();
        } else {
            $carriers = self::all_carriers_agents();
        }
        foreach ($carriers as $carrier_id => $carrier) {
            foreach ($carrier['services'] as $service_id => $service) {
                if ($service_code == $service_id) {
                    return $carrier['title'] . " - " . $service;
                }
            }
        }
        return false;
    }



    /**
     * Get shipit service name by service id
     */
    public static function all_shipit_services($id = NULL)
    {
        $method = "list-methods";
        $checksum = NODS_Common_Functions::calculate_checksum(array());
        $result = NODS_Common_Functions::curl_all_method($method, $checksum, "GET", NULL);
        if (!empty($result)) {
            if ($id) {
                $keys = array_search($id, array_column($result, 'serviceId'));
                $carrier_name = $result[$keys]->carrier . ' - ' . $result[$keys]->name;
                return $carrier_name;
            } else {
                return $result;
            }
        }
    }


    /**
     * get shipment data based on order id
     */
    public static function NODS_get_shipment_data($oid)
    {
        $result = get_post_meta($oid, "wc_shipit_uniwin_shipment_response_id");
        return $result;
    }
}
