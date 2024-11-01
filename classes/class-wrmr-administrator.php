<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



function sanitize_text_or_array_field_wrm($array_or_string) {
    if( is_string($array_or_string) ){
        $array_or_string = sanitize_text_field($array_or_string);
    }elseif( is_array($array_or_string) ){
        foreach ( $array_or_string as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = sanitize_text_or_array_field_wrm($value);
            }
            else {
                $value = sanitize_text_field( $value );
            }
        }
    }

    return $array_or_string;
}

/**
 * Wiremo WrmrAdministatorSettings.
 *
 * Create settings for plugin
 *
 * @class    WrmrAdministatorSettings
 * @package  Wiremo/Classes
 * @category Class
 * @author   Wiremo
 */

if(!class_exists("WrmrAdministatorSettings")) {
    class WrmrAdministatorSettings {
        public function __construct() {
            add_action('plugins_loaded', array($this,'wrmr_load_textdomain'));
            add_action( 'admin_enqueue_scripts',array($this,'wrmr_fonts_admin'));
            add_action('wp_enqueue_scripts', array($this,'wrmr_user_scripts'));
            add_action('admin_head', array($this,'wrmr_editor_style'));
            add_action("admin_menu", array($this,'add_wrmr_menu_item'));
            add_action("admin_init", array($this,'wrmr_display_panel_fields'));
            add_action('wp_ajax_importWrStatistics', array($this,'importWrStatistics'));
            add_action('wp_ajax_nopriv_importWrStatistics', array($this,'importWrStatistics'));
        }

        //Load text domain plugin
        public function wrmr_load_textdomain() {
            load_plugin_textdomain('wiremo');
        }

        //Added fonts for admin settings
        public function wrmr_fonts_admin() {
            wp_enqueue_style('wrmr-admin-fonts', plugins_url('../assets/css/admin-fonts.css', __FILE__), '1.1', 'screen');
        }

        //Added user scripts
        public function wrmr_user_scripts() {
            wp_enqueue_style('wrmr-font-awesome', plugins_url('../assets/css/font-awesome.min.css', __FILE__), '1.1', 'screen');
            wp_enqueue_style('wrmr-style', plugins_url('../assets/css/style.css', __FILE__), '2.5', true);
            wp_enqueue_script('wrmr-script-users', plugins_url('../assets/js/scripts.js', __FILE__), array('jquery'), '1.7', true);
        }

        public function wrmr_editor_style() {
            echo '<style>.mce-wr-icon-editor button i.mce-ico {filter:grayscale(1);-webkit-grayscale(1);}</style>';
        }

        //Added settings page
        public function wrmr_settings_page() {
            if (current_user_can('administrator')) {
                ?>
                <div class="wrap wiremo-full-container">
                    <div style="display:none" id="nonceimportWrStatistics"><?php echo wp_create_nonce('importWrStatistics');?></div>
                    <div style="display:none" id="noncewrmr_add_api_key"><?php echo wp_create_nonce('wrmr_add_api_key');?></div>
                    <div style="display:none" id="noncewrmr_add_register_hook"><?php echo wp_create_nonce('wrmr_add_register_hook');?></div>
                    <div style="display:none" id="noncewrmr_get_site_id"><?php echo wp_create_nonce('wrmr_get_site_id');?></div>
                    <div style="display:none" id="noncewrmr_no_validate_site"><?php echo wp_create_nonce('wrmr_no_validate_site');?></div>
                    <div style="display:none" id="noncewrmr_validate_site"><?php echo wp_create_nonce('wrmr_validate_site');?></div>
                    <div class="wiremo-container">
                        <h1><?php echo __("Wiremo – Customer Reviews for WordPress","wiremo"); ?></h1>
                        <?php
                        $site_id = esc_attr(get_option("wrmr-site-id"));
                        $api_key = esc_attr(get_option("wrmr-api-key"));
                        $register_hooks = esc_attr(get_option("wrmr-register-hooks"));
                        if (isset($site_id) && !empty($site_id) && isset($api_key) && !empty($api_key) && isset($register_hooks) && !empty($register_hooks)) {
                            include_once( plugin_dir_path( __FILE__ ) . '../includes/user-settings.php' );
                        } else {
                            include_once( plugin_dir_path( __FILE__ ) . '../includes/user-connect.php' );
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
        }

        //Add menu item in dashboard
        public function add_wrmr_menu_item() {
            if (current_user_can('administrator')) {
                add_menu_page("Wiremo for WP", "Wiremo for WP", "moderate_comments", "wr-settings", array($this,'wrmr_settings_page'),"dashicons-admin-generic");
            }
        }

        //Sync reviews statistic default
        public function wrmr_sync_reviews_default() {
            add_option("wrmr-default-review-total", true);
        }

        /**
         * @param $url = url API
         * Call API to receive widget settings
         */
        public function wrmr_get_widget_settings($url) {
            $response = wp_remote_get( $url);
            if( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                echo wrmr_wh_logs("----------Log: Get widget settings error (star color, star style, star size, language): ".$error_message ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                return $error_message;
            }
            else {
                $data = json_decode($response["body"]);
                if(!empty($data)) {
                    echo wrmr_wh_logs("----------Log: Success received widget settings (star color, star style, star size, language) ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                }
                else {
                    echo wrmr_wh_logs("----------Log: Result  = ".$response["body"]." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                }
                return $data;
            }
        }

        // function to create default settings for widget lite
        public function wrmr_widget_lite_default() {
            $wr_site_id = get_option("wrmr-site-id");
            $wr_url = WRMR_URLAPP."/v1/sites/".$wr_site_id;
            $wr_settings_response = $this->wrmr_get_widget_settings($wr_url);
            if(isset($wr_settings_response->options)) {
                $wr_star_color = $wr_settings_response->options->starColor;
                $wr_widget_lang = $wr_settings_response->options->language;
                $wr_widget_hover = ($wr_settings_response->options->ratingDetails == true) ? true : false;
                $wr_star_style = $wr_settings_response->options->starStyle;
                $wr_star_size = $wr_settings_response->options->starSize;
                $wr_schema_type = $wr_settings_response->options->schemaType;
                $wr_text_font = $wr_settings_response->options->textFont;
                (get_option("wrmr-widget-star-color")) ? update_option("wrmr-widget-star-color",$wr_star_color) : add_option("wrmr-widget-star-color",$wr_star_color);
                (get_option("wrmr-widget-language")) ? update_option("wrmr-widget-language",$wr_widget_lang) : add_option("wrmr-widget-language",$wr_widget_lang);
                (get_option("wrmr-widget-hover")) ? update_option("wrmr-widget-hover",$wr_widget_hover) : add_option("wrmr-widget-hover",$wr_widget_hover);
                (get_option("wrmr-widget-star-style")) ? update_option("wrmr-widget-star-style",$wr_star_style) : add_option("wrmr-widget-star-style",$wr_star_style);
                (get_option("wrmr-widget-star-size")) ? update_option("wrmr-widget-star-size",$wr_star_size) : add_option("wrmr-widget-star-size",$wr_star_size);
                (get_option("wrmr-widget-schema-type")) ? update_option("wrmr-widget-schema-type",$wr_schema_type) : add_option("wrmr-widget-schema-type",$wr_schema_type);
                (get_option("wrmr-widget-text-font")) ? update_option("wrmr-widget-text-font",$wr_text_font) : add_option("wrmr-widget-text-font",$wr_text_font);
                add_option("wrmr-default-widget-lite", true);
            }
        }

        //function to show widget lite shortcode in settings plugin
        public function display_wrmr_short_lite() {
            if (current_user_can('administrator')) {
                if(shortcode_exists('wr-widget-lite')) {
                    $wr_short_lite = '[wr-widget-lite data-type="1" source=""]';
                }
                else {
                    $wr_short_lite = '';
                }
                ?>
                <input size="37" type="text" readonly name="wr-short-widget-lite" id="wr-short-widget-lite" value='<?php echo $wr_short_lite; ?>'/>
                <?php
            }
        }

        //function to show widget review shortcode in settings plugin.
        public function display_wrmr_short_review() {
            if (current_user_can('administrator')) {
                if(shortcode_exists('wr-widget-reviews')) {
                    $wr_short_review = '[wr-widget-reviews source=""]';
                }
                else {
                    $wr_short_review = '';
                }
                ?>
                <input size="37" type="text" readonly name="wr-short-widget-review" id="wr-short-widget-review" value='<?php echo $wr_short_review; ?>'/>
                <?php
            }
        }

        public function wrmr_hide_mini_widget() {
            if (current_user_can('administrator')) {
                ?>
                <input name="wrmr_hide_mini_widget" id="wiremo_hide_mini_widget" type="checkbox"
                                                                                            value="1" <?php checked('1', get_option('wrmr_hide_mini_widget')); ?> />
                <?php
            }
        }

        //  Ajax function to import each reviews statistic
        public function importWrStatistics() {
            if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'importWrStatistics')){
                wp_die('Request not trusted');
            }
            if (isset($_POST["id"]) && !empty($_POST["id"])) {
                $wr_apikey = get_option("wrmr-api-key");
                $wr_id = sanitize_text_field($_POST["id"]);
                $wr_identifiers = sanitize_text_or_array_field_wrm($_POST["identifiers"]);
                $wr_id = (int)$wr_id;
                $wr_site_id = esc_attr(get_option("wrmr-site-id"));
                $wr_post_full_path = get_page_uri($wr_id) ;
                $wr_post_path = str_replace(home_url(),"",$wr_post_full_path);
                $wr_post_path = "/".$wr_post_path."/";
                
                $wr_identifiers_arr = array();

                delete_post_meta_by_key("wrmr-review-total");
                delete_post_meta_by_key("wrmr-review-average");
                delete_post_meta_by_key("wrmr-review-count");
                
                for($i=0;$i<count($wr_identifiers);$i++) {
                    $wr_identifiers_arr[] = $wr_identifiers[$i]["identifier"];
                }

                $wr_url_api = WRMR_URLAPP . "/v1/ecommerce/statistics/sync";
                $post_fields = array(
                        "apiKey" => $wr_apikey,
                        "identifiers" => $wr_identifiers_arr
                );
                $response = wp_remote_post( $wr_url_api, array(
                        'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
                        'method' => 'POST',
                        'body' => json_encode($post_fields)
                    )
                );
                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    echo wrmr_wh_logs("----------Log: POST request to sync statistic error: ".$error_message ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                } else {
                    $wr_data = json_decode($response["body"]);
                    for($i=0;$i<count($wr_data);$i++) {
                        $identifier = $wr_data[$i]->identifier;
                        if(is_numeric($identifier)) {
                            $wr_id = $identifier;
                        }
                        else {
                            $wr_post_id = url_to_postid(home_url()."/".$identifier);
                            $wr_id = $wr_post_id;
                        }
                        $stats = $wr_data[$i]->stats;
                        $wr_count_reviews = $stats->count;
                        $wr_rating_sum = $stats->ratingSum;
                        $wr_rating_sum_1 = $stats->detailedData[0]->ratingSum;
                        $wr_rating_sum_2 = $stats->detailedData[1]->ratingSum;
                        $wr_rating_sum_3 = $stats->detailedData[2]->ratingSum;
                        $wr_rating_sum_4 = $stats->detailedData[3]->ratingSum;
                        $wr_rating_sum_5 = $stats->detailedData[4]->ratingSum;
                        $wr_average_rating = ($wr_rating_sum !=0) ? ($wr_rating_sum_1+$wr_rating_sum_2+$wr_rating_sum_3+$wr_rating_sum_4+$wr_rating_sum_5)/$wr_count_reviews : 0;
                        if ((int)$wr_average_rating != $wr_average_rating) {
                            $wr_average_rating = number_format($wr_average_rating, 2);
                        }
                        if($wr_id == 0) {
                            if (!update_option("wrmr-tot-".$identifier,json_encode($stats))) {
                                add_option("wrmr-tot-".$identifier,json_encode($stats));
                            }
                            if (!update_option("wrmr-avg-".$identifier,$wr_average_rating)) {
                                add_option("wrmr-avg-".$identifier,$wr_average_rating);
                            }
                            if (!update_option("wrmr-cnt-".$identifier,$wr_count_reviews)) {
                                add_option("wrmr-cnt-".$identifier,$wr_count_reviews);
                            }
                        }
                        else {
                            $post_type = get_post_type($wr_id);
                            if($post_type != "product") {
                                if (!add_post_meta($wr_id, 'wrmr-review-total', json_encode($stats), true)) {
                                    update_post_meta($wr_id, 'wrmr-review-total', json_encode($stats));
                                }
                                if (!add_post_meta($wr_id, 'wrmr-review-average', $wr_average_rating, true)) {
                                    update_post_meta($wr_id, 'wrmr-review-average', $wr_average_rating);
                                }
                                if (!add_post_meta($wr_id, 'wrmr-review-count', $wr_count_reviews, true)) {
                                    update_post_meta($wr_id, 'wrmr-review-count', $wr_count_reviews);
                                }
                            }
                        }
                    }
                }
            }
            $result = new stdClass();
            $result->success = "ok";
            echo json_encode($result);
            die();
        }

        //Sync (import review statistic from wiremo API)
        public function wrmr_import_review_statistic() {
            $api_key = get_option("wrmr-api-key");
            ?>
            <script>
                (function ($) {
                    $(window).load(function () {
                        var objIdentifiers = {};
                        var idsPosts = [];
                        var totalPosts;
                        var postCount = 1;
                        var wr_continue = true;

                        $(document).on("click", ".import-from-wr", function () {
                            var arrIdentifiers = [];
                            var limitRequest = <?php echo WRMR_LIMIT_REQ; ?>;
                            var page;
                            var step = "step0";
                            var currPage = 0;
                            $(this).bootstrapBtn('loading');
                            $("#wr-import-bar").css("display", "block");
                            $(".widget-import .tooltip").remove();
                            $.ajax({
                                type: 'GET',
                                url: "<?php echo WRMR_URLAPP.'/v1/sites/identifiers/all/?apiKey='.$api_key; ?>",
                                success: function (response) {
                                    if(response.length == 0) {
                                        setTimeout(function () {
                                            $(".notification-import.from-wr").html("<?php echo __("You don't have any reviews","wiremo-widget"); ?>");
                                            $(".notification-import.from-wr").removeClass("hidden").addClass("show");
                                            location.reload(true);
                                        }, 1000);
                                    }
                                    if(response.length % limitRequest == 0) {
                                        page = response.length  / limitRequest;
                                    }
                                    else {
                                        page = (Math.round(response.length  / limitRequest)) + 1;
                                    }
                                    for(var j=1;j<=page;j++) {
                                        idsPosts.push(j);
                                    }
                                    totalPosts = idsPosts.length;
                                    for(var i=0;i<response.length;i++) {
                                        if(i % limitRequest == 0){
                                            currPage = currPage + 1;
                                            step = "step"+currPage;
                                            arrIdentifiers = [];
                                        }
                                        arrIdentifiers.push(response[i]);
                                        objIdentifiers[step] = arrIdentifiers;
                                    }
                                    if (idsPosts != 0) {
                                        $("#wr-import-bar").progressbar();
                                        $("#wr-import-bar-percent").html("0%");
                                    }
                                    wrImportStatistics(idsPosts.shift());
                                },
                                error: function (error) {
                                    console.log(error);
                                }
                            });
                            return false;
                        });
                        function wrImportUpdateStatus() {
                            $("#wr-import-bar").progressbar("value", (postCount / totalPosts) * 100);
                            $("#wr-import-bar-percent").html(Math.round(( postCount / totalPosts ) * 1000) / 10 + "%");
                            postCount = postCount + 1;
                        }

                        function wrImportFinishUp() {
                            $(".import-from-wr").bootstrapBtn('reset');
                            $(".notification-import.from-wr").html("<?php echo __("Your reviews statistic successful imported from wiremo","wiremo"); ?>");
                            $(".notification-import.from-wr").removeClass("hidden").addClass("show");
                            setTimeout(function () {
                                $(".notification-import.from-wr").removeClass("show").addClass("hidden");
                                $(".notification-import.from-wr").html("");
                                location.reload(true);
                            }, 1000);
                        }

                        function wrImportStatistics(id) {
                            $.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: {action: 'importWrStatistics',nonce: document.getElementById('nonceimportWrStatistics').innerText, id: id,identifiers:objIdentifiers["step"+id]},
                                success: function (response) {
                                    if (response.success) {
                                        wrImportUpdateStatus();
                                    }
                                    else {
                                        wrImportUpdateStatus();
                                    }
                                    if (idsPosts.length && wr_continue) {
                                        wrImportStatistics(idsPosts.shift());
                                    }
                                    else {
                                        wrImportFinishUp();
                                    }
                                },
                                error: function () {
                                    wrImportUpdateStatus();
                                    if (idsPosts.length && wr_continue) {
                                        wrImportStatistics(idsPosts.shift());
                                    }
                                    else {
                                        wrImportFinishUp();
                                    }
                                }
                            });
                        }
                    });
                })(jQuery);
            </script>
            <?php
        }

        //Sync wiremo with Wordpress (use once)
        public function display_wrmr_sync_reviews() {
            if (current_user_can('administrator')) {
                $wr_default_review_total = get_option("wrmr-default-review-total");
                if (empty($wr_default_review_total)) {
                    // Sync reviews statistic default
                    $this->wrmr_sync_reviews_default();
                }
                $wr_default_widget_lite = get_option("wrmr-default-widget-lite");
                if(empty($wr_default_widget_lite)) {
                    // Added default settings for widget lite
                    $this->wrmr_widget_lite_default();
                }
                $this->wrmr_import_review_statistic();
                ?>
                <button data-toggle="tooltip" data-placement="top" title="Use this button to sync Wiremo with Wordpress if you imported reviews via CSV or imported reviews manually.
Use this button if you Disabled/Enabled Wiremo plugin for some reason." class="import-from-wr btn btn-primary"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i>  Loading"><?php echo __("Sync Wiremo","wiremo"); ?>
                </button>
                <div id="wr-import-bar">
                    <div id="wr-import-bar-percent"></div>
                </div>
                <div class="notification-import from-wr hidden alert alert-success"></div>
                <?php
            }
        }

        //Display panel fields with options
        public function wrmr_display_panel_fields() {
            if (current_user_can('administrator')) {

                add_settings_section("section-wrmr", "", null, "wrmr-general-options");

                add_settings_field("wwrmr-shortcode-widget-lite", __("Shortcode widget lite","wiremo"), array($this,'display_wrmr_short_lite'), "wrmr-general-options", "section-wrmr");
                add_settings_field("wrmr-shortcode-widget-review", __("Shortcode widget review","wiremo"), array($this,'display_wrmr_short_review'), "wrmr-general-options", "section-wrmr");
                add_settings_field("wrmr_hide_mini_widget", __("Hide stars when no reviews","wiremo"), array($this,'wrmr_hide_mini_widget'), "wrmr-general-options", "section-wrmr");
                add_settings_field("wrmr-sync-reviews", __("Sync Wiremo with Wordpress (use once)","wiremo"), array($this,'display_wrmr_sync_reviews'), "wrmr-general-options", "section-wrmr", array('class' => 'widget-import'));

                register_setting("section-wrmr", "wrmr-sync-reviews");
                register_setting("section-wrmr", "wrmr_hide_mini_widget");
            }
        }
    }
    // initialise class WrmrAdministatorSettings
    add_action( 'init', 'wrmr_init_administator_settings' );
    if(!function_exists("wrmr_init_administator_settings")) {
        function wrmr_init_administator_settings() {
            global $wr_administator_settings;
            $wr_administator_settings = new WrmrAdministatorSettings();
            if(is_admin()) {
                if(isset($_GET["page"]) && !empty($_GET["page"])) {
                    if (sanitize_text_field($_GET["page"]) == "wr-settings"):
                        if(!function_exists("wrmr_style_admin")) {
                            function wrmr_style_admin() {
                                wp_enqueue_style('wrmr-font-awesome', plugins_url('../assets/css/font-awesome.min.css', __FILE__), array(), '1.0', 'screen');
                                wp_enqueue_style('wrmr-bootstrap-style', plugins_url('../assets/css/bootstrap.min.css', __FILE__), array(), '4.4.1', 'screen');
                                wp_enqueue_style('wrmr-admin-jquery-ui', plugins_url('../assets/css/admin-jquery-ui.css', __FILE__), array(), '1.0', 'screen');
                                wp_enqueue_style('wrmr-admin-style', plugins_url('../assets/css/admin-style.css', __FILE__), array(), '1.2.8.0', 'screen');

                                wp_enqueue_script('jquery-ui-datepicker');
                                wp_enqueue_script('jquery-ui-progressbar');
                                wp_enqueue_script('wrmr-popper-js', plugins_url('../assets/js/popper.min.js', __FILE__), array(), '1.12.9', true);
                                wp_enqueue_script('wrmr-bootstrap-js', plugins_url('../assets/js/bootstrap.min.js', __FILE__), array('jquery', 'wrmr-popper-js'), '4.4.1', true);
                                wp_enqueue_script('wrmr-noconflict-js', plugins_url('../assets/js/noconflict.js', __FILE__), array('jquery'), '1.1', true);
                                wp_enqueue_script('wrmr-script-admin-js', plugins_url('../assets/js/admin.js', __FILE__), array('jquery'), '1.2.8.0', true);
                            }
                        }
                        add_action('admin_enqueue_scripts', 'wrmr_style_admin');
                    endif;
                }
            }
        }
    }
}


