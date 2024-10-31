<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<div id="wc-backbone-modal-dialog-carrier-agent">
    <div class="wc-backbone-modal" id="shipit-uniwin-carrier-agent">
        <div class="wc-backbone-modal-content" tabindex="0">
            <section class="wc-backbone-modal-main" role="main">
                <header class="wc-backbone-modal-header">
                    <h1>Carrier Agent</h1>
                </header>
                <article style="max-height: 286.5px;">
                    <div id="shipit-uniwin-carrieragent">
                        <?php if (!empty($methods)) {
                            if ($methods['country'] == "FI") {
                                $options = array(
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
                                    "dpd.dpdbaltprivpallet" => "DPD Baltic - DPD Private Pallet",
                                );
                            } else if ($methods['country'] == "SE") {
                                $options = array(
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
                                );
                            } else {
                                $options = array(
                                    'sbtlfirrex.sbtlfirrex' => "DB Schenker",
                                    'posti.po2103' => "Postipaketti",
                                    'posti.po2331' => "Baltian Postipaketti",
                                    'posti.po2351' => "PickUp Parcel",
                                    'posti.po2711eepp' => "Parcel Connect (EE)",
                                    'posti.po2711ltpp' => "Parcel Connect (LT)",
                                    'posti.po2711lvpp' => "Parcel Connect (LV)",
                                    'mh.mh10' => "Bussipaketti",
                                    'mh.mh20' => "Pikapaketti",
                                    'mh.mh80' => "Lähellä-paketti",
                                    'mh.mh84' => 'XXS-paketti',
                                    'mh.mh95' => 'Euroopan Lähellä-paketti',
                                    'plscm.p19fi' => 'Postnord MyPack',
                                    'plscm.p19fidpd' => 'Postnord MyPack DPD',
                                    'ups.accesp' => 'UPS Express Access Point',
                                    'ups.accespstd' => 'UPS Access Point Standard',
                                    'omni.omnice' => 'Omniva Pickup Point Delivery (EE)',
                                    'omni.omniparcelmachine' => 'Omniva Parcelmachine (EE)',
                                    'dpd.dindpdbaltpickup' => 'DPD Parcelmachine and Pickup Point (EE)',
                                );
                            }
                        ?>
                            <form id="shipit-uniwin-carrieragent-form">
                                <input type="hidden" name="mv_other_meta_field_nonce" value="<?php echo wp_create_nonce("meta_box"); ?>">
                                <input type="hidden" class="nordic-shipping-post-id" value="<?php echo $post->ID; ?>" />
                                <div class="search-params" style="display: inline-flex;">
                                    <div class="form-input">
                                        <select name="suca_shipping_method" id="suca_shipping_method">
                                            <?php foreach ($methods['shipping_methods']['pickup_ids'] as $_method) {
                                                $title = $options[$_method];
                                                if (!empty($title)) {
                                            ?>
                                                    <option value="<?php echo esc_attr($_method); ?>"><?php echo esc_html($title); ?></option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                    <div class="form-input">
                                        <input type="text" name="suca_postal_code" value="<?php echo esc_html($methods['default_postcode']); ?>" placeholder="<?php esc_html_e('Postcode', 'nordic-'); ?>" />
                                    </div>
                                    <div class="form-input">
                                        <input type="hidden" name="suca_country" value="<?php echo esc_html($methods['country']); ?>" />
                                        <input type="hidden" name="suca_instance_id" value="<?php echo esc_html($methods['shipping_methods']['instance_id']); ?>" />
                                        <input type="button" value="<?php esc_html_e('Search', 'nordic-'); ?>" class="button" id="suca_agent_search" />
                                    </div>
                                </div>
                                <?php if (!empty($methods['agents'])) { ?>
                                    <div id="suca_fetched_agent_data" class="form-input" style="margin-top: 12px;">
                                        <select name="suca_agent_id">
                                            <?php
                                            foreach ($methods['agents'] as $akey => $agent) {
                                            ?>
                                                <option value="<?php echo esc_html($akey); ?>"><?php echo esc_html($agent); ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <?php echo $methods['agent_data']; ?>
                                        <input type="button" id="suca_save" class="button button-primary" name="suca_save" value="<?php esc_html_e('Save Carrier Agent', 'nordic-'); ?>" />
                                    </div>
                                <?php } ?>
                            </form>
                        <?php

                            if ($methods['datas'] != "") {
                                if ($methods['datas'] == "Nodata") {
                                    echo "Now pickup points currently unavailable for this agent!. please try with another agent.";
                                } else if ($methods['datas'] == "Error") {
                                    echo "Invalid Data!";
                                }
                            }
                        } ?>
                    </div>
                </article>
            </section>
        </div>
    </div>
</div>