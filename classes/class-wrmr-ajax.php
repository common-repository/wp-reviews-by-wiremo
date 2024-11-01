<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Wiremo WRMR_AJAX.
 *
 * Ajax Event Handler.
 *
 * @class    WRMR_AJAX
 * @package  Wiremo/Classes
 * @category Class
 * @author   Wiremo
 */

class WRMR_AJAX {
    //initialise constructor
    public function __construct() {
        add_action('wp_head', array($this,'wrmr_ajaxurl'));
        add_action('wp_ajax_wrmr_oauth_user', array($this,'wrmr_oauth_user'));
        add_action('wp_ajax_nopriv_wrmr_oauth_user', array($this,'wrmr_oauth_user'));
        add_action('wp_ajax_wrmr_get_site_id', array($this,'wrmr_get_site_id'));
        add_action('wp_ajax_nopriv_wrmr_get_site_id', array($this,'wrmr_get_site_id'));
        add_action('wp_ajax_wrmr_auto_register', array($this,'wrmr_auto_register'));
        add_action('wp_ajax_nopriv_wrmr_auto_register', array($this,'wrmr_auto_register'));
        add_action('wp_ajax_wrmr_validate_site', array($this,'wrmr_validate_site'));
        add_action('wp_ajax_nopriv_wrmr_validate_site', array($this,'wrmr_validate_site'));
        add_action('wp_ajax_wrmr_no_validate_site', array($this,'wrmr_no_validate_site'));
        add_action('wp_ajax_nopriv_wrmr_no_validate_site', array($this,'wrmr_no_validate_site'));
        add_action('wp_ajax_wrmr_add_api_key', array($this,'wrmr_add_api_key'));
        add_action('wp_ajax_nopriv_wrmr_add_api_key', array($this,'wrmr_add_api_key'));
        add_action('wp_ajax_wrmr_add_register_hook', array($this,'wrmr_add_register_hook'));
        add_action('wp_ajax_nopriv_wrmr_add_register_hook', array($this,'wrmr_add_register_hook'));
    }
    //default ajax global
    public function wrmr_ajaxurl() {
        $text_font_option = get_option("wrmr-widget-text-font");
        $wrmr_text_font = empty($text_font_option) ? "Open+Sans" : str_replace(' ', '+', $text_font_option);
        wp_enqueue_style( 'wiremo-google-fonts', "https://fonts.googleapis.com/css?family=$wrmr_text_font:400,500,600,700", false ); 
        ?>
        <script type="text/javascript">
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        </script>
        <?php
    }

    /**
     * Check if hosts name registered in Wiremo
     * @param $url_site
     * @param $url_api
     */
    public function wrmr_check_site($urlSite,$urlApi,$apiKey) {
        $post_fields = array();
        $post_fields["url"] = $urlSite;
        $post_fields["apiKey"] = $apiKey;
        $response = wp_remote_post( $urlApi, array(
                'method' => 'POST',
                'body' => $post_fields
            )
        );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return $error_message;
        } else {
            $data = json_encode($response);
            return $data;
        }
    }
    //get site id from API
    public function wrmr_get_site_id() {
        if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wrmr_get_site_id')){
            wp_die('Request not trusted');
        }
        $wrmr_error_connect_message = 'We were able to connect your Wiremo account but <strong>'.home_url().'</strong> isn’t configured under your account.<br>
        Do you want us to update your domain to <strong>'.home_url().'</strong> ?<br>
        <a class="connect-yes-wrmr" href="#">Yes</a> / <a class="connect-no-wrmr" href="#">No</a>
        ';
        $urlApi = WRMR_URLAPP . "/v1/ecommerce/checkSite";
        $urlSite = home_url();
        global $siteId, $apiKey, $registerHooks;
        $siteId = esc_attr(get_option("wrmr-site-id"));
        $apiKey = sanitize_text_field($_POST["apiKey"]);
        $registerHooks = esc_attr(get_option("wrmr-register-hooks"));
        if (isset($siteId) && !empty($siteId) && isset($apiKey) && !empty($apiKey) && isset($registerHooks) && !empty($registerHooks)) {
            include_once( plugin_dir_path( __FILE__ ) . '../includes/user-settings.php' );
        } else {
            $error_message = array();
            $response_data = json_decode($this->wrmr_check_site($urlSite, $urlApi, $apiKey));
            $data = json_decode($response_data->body);
            $code_error = $response_data->response;
            if (isset($data->siteId) && !empty($data->siteId) && $code_error->code == 200) {
                (get_option("wrmr-site-id")) ? update_option("wrmr-site-id", $data->siteId) : add_option("wrmr-site-id", $data->siteId);
                $error_message["success"] = true;
            } else if ($code_error->code == 404) {
                $error_message["success"] = false;
                $error_message["message"] = $wrmr_error_connect_message;
            } else {
                $error_message["success"] = false;
                $error_message["message"] = $wrmr_error_connect_message;
            }
            echo json_encode($error_message);
        }
        die();
    }

    public function wrmr_auto_register() {
        $wrmr_host_url = home_url();
        $wrmr_error_empty_host = 'We already have this email registered, please, go to your Wiremo Dashboard -> Install Wiremo and enter <strong>' . $wrmr_host_url . '</strong> in Step 1 then click Next.<br>
        Then come back and click on “Connect your Wiremo account” and the plugin will do the magic!<br> 
        If you have any issue just <a target="_blank" href="https://wiremo.co/contact-us/">contact our support team</a>.';
        $wrmr_error_user_exists = 'We already have this domain registered under another account.<br> 
        Please, use the already created account to connect Wiremo plugin to your Dashboard.<br> 
        If you have any issue just <a target="_blank" href="https://wiremo.co/contact-us/">contact our support team</a>.';
        if (isset($_POST["firstName"]) && isset($_POST["lastName"]) && isset($_POST["email"])) {
            $first_name = sanitize_text_field($_POST["firstName"]);
            $last_name = sanitize_text_field($_POST["lastName"]);
            $email = sanitize_text_field($_POST["email"]);
            $urlSite = home_url();
            $urlApi = WRMR_URLAPP . "/v1/ecommerce/autoRegister";
            $post_fields = array();
            $post_fields["name"] = $first_name;
            $post_fields["surname"] = $last_name;
            $post_fields["email"] = $email;
            $post_fields["host"] = $urlSite;
            $response = wp_remote_post($urlApi, array(
                    'method' => 'POST',
                    'body' => $post_fields
                )
            );

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo wrmr_wh_log("----------Request url route: /v1/ecommerce/autoRegister return error " . esc_attr($error_message) . " error ---------- date = " . date("d-M-Y") . " time = " . date("H:i:s"));
            } else {
                $data_request = json_decode($response["body"]);
                $response_message = array();
                $response_code = $response["response"]["code"];
                if ($response_code == 400) {
                    $response_message["message"] = $data_request->error;
                    echo json_encode($response_message);
                    die();
                }
                if ($data_request->success == true) {
                    $siteId = $data_request->data->siteId;
                    $apiKey = $data_request->data->apiKey;
                    $response_message["success"] = true;
                    $response_message["message"] = "success";
                    $response_message["apiKey"] = $data_request->data->apiKey;
                    (get_option("wrmr-site-id")) ? update_option("wrmr-site-id", $siteId) : add_option("wrmr-site-id", $siteId);
                    (get_option("wrmr-api-key")) ? update_option("wrmr-api-key", $apiKey) : add_option("wrmr-api-key", $apiKey);
                } else {
                    $response_message["success"] = false;
                    if ($data_request->data->msg == 1) {
                        $response_message["message"] = $wrmr_error_empty_host;
                    } else if ($data_request->data->msg == 2) {
                        $response_message["message"] = $wrmr_error_user_exists;
                    }
                }
                echo json_encode($response_message);
            }
        }
        die();
    }

    //Added API key
    public function wrmr_add_api_key() {
        if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wrmr_add_api_key')){
            wp_die('Request not trusted');
        }
        if (isset($_POST["apiKey"]) && !empty($_POST["apiKey"])) {
            $api_key = sanitize_text_field($_POST["apiKey"]);
            echo $api_key;
            (get_option("wrmr-api-key")) ? update_option("wrmr-api-key", $api_key) : add_option("wrmr-api-key", $api_key);
            echo wrmr_wh_logs("----------Log: Received api key ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
        }
        die();
    }

    public function wrmr_add_register_hook() {
        if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wrmr_add_register_hook')){
            wp_die('Request not trusted');
        }
        if (isset($_POST["apiKey"]) && !empty($_POST["apiKey"])) {
            $url_site = home_url();
            $api_key = sanitize_text_field($_POST["apiKey"]);
            $site_id = esc_attr(get_option("wrmr-site-id"));
            $url_api = WRMR_URLAPP . "/v1/ecommerce/" . $site_id . "/register";
            $post_fields = array();
            $post_fields["apiKey"] = $api_key;
            $post_fields["type"] = "wordpress";
            $post_fields["url"] = $url_site;
            $post_fields["restUrl"] = get_rest_url();
            $response = wp_remote_post( $url_api, array(
                    'method' => 'POST',
                    'body' => $post_fields
                )
            );

            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                echo wrmr_wh_logs("----------Log: Registered hooks error: ".$error_message ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
            } else {
                $data = json_decode($response["body"]);
                if(!empty($data)) {
                    (get_option("wrmr-register-hooks")) ? update_option("wrmr-register-hooks", true) : add_option("wrmr-register-hooks", true);
                    echo wrmr_wh_logs("----------Log: Registered hooks ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                }
                echo $response["body"];
            }
        }
        die();
    }

    public function wrmr_validate_site() {
        if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wrmr_validate_site')){
            wp_die('Request not trusted');
        }
        $wrmr_error_validate = "Something went wrong please try again or <a target='_blank' href='https://wiremo.co/contact-us/'>contact our support team</a>";
        $urlApi = WRMR_URLAPP."/v1/ecommerce/validateSite";
        $apikey = sanitize_text_field($_POST["apiKey"]);
        $url = sanitize_text_field($_POST["url"]);
        if(!empty($apikey) && !empty($url)) {
            $post_fields = array();
            $post_fields["url"] = home_url();
            $post_fields["apiKey"] = $apikey;
            $error_data = array();
            $response = wp_remote_post( $urlApi, array(
                    'method' => 'POST',
                    'body' => $post_fields
                )
            );

            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $error_data["success"] = false;
                $error_data["message"] = $error_message;
            } else {
                $data_response =  $response;
                $data_body = json_decode($response["body"]);
                $code_error = $data_response["response"];
                if($code_error["code"] == 200) {
                    (get_option("wrmr-site-id")) ? update_option("wrmr-site-id", $data_body->siteId) : add_option("wrmr-site-id", $data_body->siteId);
                    $error_data["success"] = true;
                    $error_data["message"] = "success";
                }
                else {
                    $error_data["success"] = false;
                    $error_data["message"] = $wrmr_error_validate;
                }
            }
            echo json_encode($error_data);
        }
        die();
    }

    public function wrmr_no_validate_site() {
        if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wrmr_no_validate_site')){
            wp_die('Request not trusted');
        }
        $wrmr_error_message = 'OK, no problems!<br>
        Go to your Wiremo Dashboard -> Install Wiremo and enter <strong>'.home_url().'</strong> in Step 1 then click “Next” button and follow the wizard.<br>
        Then come back to your WordPress dashboard and click on “Connect your Wiremo account” and the plugin will do the magic!<br>
        If you have any issue just <a target="_blank" href=\'https://wiremo.co/contact-us/\'>contact our support team</a>';
        echo $wrmr_error_message;
        die();
    }

    //Authentication user with iframe
    public function wrmr_oauth_user() {
        include plugin_dir_path( __FILE__ ) . '../includes/oauth-user.php';
        die();
    }
}

// initialise class WRMR_AJAX
add_action( 'init', 'wrmr_init_ajax' );
if(!function_exists("wrmr_init_ajax")) {
    function wrmr_init_ajax() {
        global $wr_ajax;
        $wr_ajax = new WRMR_AJAX();
    }
}