<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!defined("WP_UNINSTALL_PLUGIN"))
    exit();
global $wpdb;
$api_key = get_option("wrmr-api-key");
if($api_key) {
    $url_api = "https://wapi.wiremo.co";
    $url = $url_api."/v1/sites/identifiers/all/?apiKey=".$api_key;
    $response = wp_remote_get( $url);
    if( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        return $error_message;
    }
    else {
        $data = json_decode($response["body"]);
        for($i=0;$i<count($data);$i++) {
            $wr_post_id = url_to_postid(home_url()."/".$data[$i]->identifier);
            if($wr_post_id == 0) {
                if(get_option("wrmr-tot-".$data[$i]->identifier)) {
                    delete_option("wrmr-tot-".$data[$i]->identifier);
                }
                if(get_option("wrmr-avg-".$data[$i]->identifier)) {
                    delete_option("wrmr-avg-".$data[$i]->identifier);
                }
                if(get_option("wrmr-cnt-".$data[$i]->identifier)) {
                    delete_option("wrmr-cnt-".$data[$i]->identifier);
                }
            }
        }
    }
    $wrmr_url_delete = $url_api."/v1/ecommerce/uninstall";
    $wrmr_post_fields = array();
    $wrmr_post_fields["apiKey"] = $api_key;
    $wrmr_post_fields["type"] = "wordpress";
    $wrmr_response = wp_remote_post( $wrmr_url_delete, array(
            'method' => 'POST',
            'body' => $wrmr_post_fields
        )
    );

    if ( is_wp_error( $wrmr_response ) ) {
        $wrmr_error_message = $wrmr_response->get_error_message();
        return $wrmr_error_message;
    }
}
(get_option("wrmr-site-id")) ? delete_option("wrmr-site-id") : "";
(get_option("wrmr-api-key")) ? delete_option("wrmr-api-key") : "";
(get_option("wrmr-register-hooks")) ? delete_option("wrmr-register-hooks") : "";
(get_option("wrmr_hide_mini_widget")) ? delete_option("wrmr_hide_mini_widget") : "";
(get_option("wrmr-display-import")) ? delete_option("wrmr-display-import") : "";
(get_option("wrmr-widget-star-color")) ? delete_option("wrmr-widget-star-color") : "";
(get_option("wrmr-widget-star-style")) ? delete_option("wrmr-widget-star-style") : "";
(get_option("wrmr-widget-star-size")) ? delete_option("wrmr-widget-star-size") : "";
(get_option("wrmr-widget-language")) ? delete_option("wrmr-widget-language") : "";
(get_option("wrmr-widget-hover")) ? delete_option("wrmr-widget-hover") : "";
(get_option("wrmr-default-review-total")) ? delete_option("wrmr-default-review-total") : "";
(get_option("wrmr-default-widget-lite")) ? delete_option("wrmr-default-widget-lite") : "";
(get_option("wrmr-widget-schema-type")) ? delete_option("wrmr-widget-schema-type") : "";
(get_option("wrmr-widget-text-font")) ? delete_option("wrmr-widget-text-font") : "";
$wpdb->query('DELETE FROM `'.$wpdb->prefix.'postmeta` WHERE `meta_key`="wrmr-review-total" OR `meta_key`="wrmr-review-average" OR `meta_key`="wrmr-review-count"');