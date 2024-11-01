<?php
/*

**************************************************************************

Plugin Name: Wiremo – Customer Reviews for WordPress
Plugin URI: https://wiremo.co/
Description: Wiremo is a standalone review platform that collects and displays review related widgets on your website.
Version: 1.2.24
Author: Wiremo
Author URI: https://wiremo.co
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wiremo

**************************************************************************
 Copyright (C) 2016-2022 Wiremo

Wiremo – Customer Reviews for WordPress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Wiremo – Customer Reviews for WordPress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function appsero_init_tracker_wp_reviews_by_wiremo() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once __DIR__ . '/appsero/src/Client.php';
    }

    $client = new Appsero\Client( '5dda3916-8969-497c-8b76-b4215ac72870', 'Wiremo - Customer reviews for WordPress', __FILE__ );

    $client->insights()->init();

}

appsero_init_tracker_wp_reviews_by_wiremo();


define("WRMR_URLAPP", "https://wapi.wiremo.co");
define("WRMR_URLWIDGET", "https://wapi.wiremo.co/v2/script");
define("WRMR_POSTS_PER_PAGE", 100000);
define("WRMR_LIMIT_REQ", 50);


include dirname( __FILE__ ).'/includes/logs.php';
include dirname( __FILE__ ).'/classes/class-wrmr-ajax.php';
include dirname( __FILE__ ).'/classes/class-wrmr-administrator.php';

$site_id = esc_attr(get_option("wrmr-site-id"));
$api_key = esc_attr(get_option("wrmr-api-key"));
$register_hooks = esc_attr(get_option("wrmr-register-hooks"));

if (isset($site_id) && !empty($site_id) && isset($api_key) && !empty($api_key) && isset($register_hooks) && !empty($register_hooks)) {
    include dirname( __FILE__ ).'/classes/class-wrmr-routes.php';
    include dirname( __FILE__ ).'/classes/class-wrmr-shortcodes.php';
    
    add_action('init', 'wrmr_shortcode_button_init');

    require_once plugin_dir_path( __FILE__ ) . 'src/initBlocks.php';
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wrmr_action_links' );

if(!function_exists("wrmr_action_links")) {
    function wrmr_action_links( $links ) {
        $action_links = array(
            'settings' => '<a href="' . admin_url( 'admin.php?page=wr-settings' ) . '" aria-label="' . esc_attr__( 'View Wiremo settings', 'wiremo' ) . '">' . esc_html__( 'Settings', 'wiremo' ) . '</a>',
        );
        return array_merge( $action_links, $links );
    }
}

function wrmr_shortcode_button_init() {

    //Abort early if the user will never see TinyMCE
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
        return;

    //Add a callback to regiser our tinymce plugin
    add_filter("mce_external_plugins", "wrmr_register_tinymce_plugin");

    // Add a callback to add our button to the TinyMCE toolbar
    add_filter('mce_buttons', 'wrmr_add_tinymce_button');
}

//This callback registers our plug-in
function wrmr_register_tinymce_plugin($plugin_array) {
    $plugin_array['wiremo_button'] = plugins_url('assets/js/shortcode.js', __FILE__);
    return $plugin_array;
}

//This callback adds our button to the toolbar
function wrmr_add_tinymce_button($buttons) {
    //Add the button ID to the $button array
    $buttons[] = "wiremo_button";
    return $buttons;
}




function admin_footer_text_wrm_wp ( $footer_text ) {
              $current_screen = get_current_screen();
              $is_wiremo_screen = ( $current_screen && false !== strpos( $current_screen->id, 'wr-settings' ) );

              if ( $is_wiremo_screen ) {


   $footer_text = '



                          <div class="rate">
                           <span class="rate" style="margin-top:12px;">Enjoyed Wiremo? Please, select a rating and leave a review</span>
                           <input type="radio" id="star5" name="rate" value="5" />
                           <label for="star5" title="Rate us with 5 stars" onclick="window.open(\'https://wiremo.co/getreviews.php?rating=5\')">5 stars</label>
                           <input type="radio" id="star4" name="rate" value="4" />
                           <label for="star4" title="Rate us with 4 stars" onclick="window.open(\'https://wiremo.co/getreviews.php?rating=4\')">4 stars</label>
                           <input type="radio" id="star3" name="rate" value="3" />
                           <label for="star3" title="Rate us with 3 stars" onclick="window.open(\'https://wiremo.co/getreviews.php?rating=3\')">3 stars</label>
                           <input type="radio" id="star2" name="rate" value="2" />
                           <label for="star2" title="Rate us with 2 stars" onclick="window.open(\'https://wiremo.co/getreviews.php?rating=2\')">2 stars</label>
                           <input type="radio" id="star1" name="rate" value="1"/>
                           <label for="star1" title="Rate us with 1 star" onclick="window.open(\'https://wiremo.co/getreviews.php?rating=1\')">1 star</label>
                         </div>';
              }

              return $footer_text;
      }

add_filter('admin_footer_text', 'admin_footer_text_wrm_wp');
