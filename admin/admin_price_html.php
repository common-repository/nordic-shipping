<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<div id="wc-backbone-modal-dialog">
    <div class="wc-backbone-modal" id="shipit-uniwin-prices-modal">
        <div class="wc-backbone-modal-content" tabindex="0">
            <section class="wc-backbone-modal-main" role="main">
                <header class="wc-backbone-modal-header">
                    <div style="height: 32px;">
                        <h1 style="float: left;padding: 0px;">Shipit prices</h1>
                        <button style="float: right;" type="button" class="button button-primary uniwin_close_price_model">Close</button>
                    </div>

                </header>
                <article style="max-height: 286.5px;">
                    <div id="shipit-uniwin-prices">
                        <input type="hidden" name="mv_other_meta_field_nonce" value="<?php echo wp_create_nonce("meta_box"); ?>">
                        <input type="hidden" class="nordic-shipping-post-id" value="<?php echo $post->ID; ?>" />
                        <?php if (!empty($methods)) {
                        ?>
                            <table id="shipit-uniwin-delivery-info-table">
                                <tr>
                                    <th><?php echo esc_html__("Destination", 'nordic-'); ?></th>
                                    <td><?php echo esc_html($methods['country']); ?> <?php echo $methods['postcode']; ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__("Weight and parcels", 'nordic-'); ?></th>
                                    <td><?php
                                        printf(
                                            esc_html(__('%.2f kg - %d parcels', 'nordic-')),
                                            esc_html($methods['weight']),
                                            esc_html($methods['parcels'])
                                        ); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__("Dimensions - L, W, H in (cm)", 'nordic-'); ?></th>
                                    <td><?php
                                        printf(
                                            esc_html(__('%.2f &times; %.2f &times %.2f', 'nordic-')),
                                            esc_html($methods['length']),
                                            esc_html($methods['width']),
                                            esc_html($methods['height'])
                                        ); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo esc_html__("Fragile", 'nordic-'); ?></th>
                                    <td><?php echo $methods['fragile'] ? esc_html_e('Yes', 'nordic-') : esc_html_e('No', 'nordic-'); ?></td>
                                </tr>
                            </table>
                            <table id="shipit-uniwin-prices-table">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html__(" method", 'nordic-'); ?></th>
                                        <th class="align-right"><?php esc_html_e('Price (incl. VAT)', 'nordic-'); ?></th>
                                    </tr>
                                </thead>
                                <?php foreach ($methods['methods'] as $methodArray) {
                                    $method = (array) $methodArray;
                                ?>
                                    <tbody>
                                        <td><?php echo
                                            sprintf(
                                                esc_html('%s %s'),
                                                esc_html($method['carrier']),
                                                esc_html($method['serviceName'])
                                            ); ?></td>
                                        <td class="align-right"><?php echo NODS_Common_Functions::shipit_uniwin_price_formate($method['price']); ?></td>
                                    </tbody>
                                <?php } ?>
                                <?php if (empty($methods)) { ?>
                                    <tr>
                                        <td colspan="2"><?php esc_html_e('No  methods found with given details', 'nordic-'); ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } else { ?>
                            <h3 style="text-align: center;" class="shipit_uniwin_loding_text"><?php esc_html_e('Please wait!, price calculation processing!', 'nordic-'); ?></h3>
                        <?php } ?>
                    </div>
                </article>
            </section>
        </div>
    </div>
</div>