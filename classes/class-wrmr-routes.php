<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Wiremo WrmrRoutes.
 *
 * Create routes
 *
 * @class    WrmrRoutes
 * @package  Wiremo/Classes
 * @category Class
 * @author   Wiremo
 */

if(!class_exists("WrmrRoutes")) {
    class WrmrRoutes {
        public $data = array();
        public function __construct(){
            add_action('rest_api_init', array($this,'register_wrmr_routes'));
        }

        /**
         * @param $request This function accept a rest request to process data
         */
        public function wrmr_update_statistics_rating($request) {
            if (isset($request['data'])) {
                $response = $request['data'];
                $wr_site_id = esc_attr(get_option("wrmr-site-id"));
                $wr_api_key = esc_attr(get_option("wrmr-api-key"));
                $result = new stdClass();
                if (isset($response)) {
                    if ($response["type"] == "review") {
                        if ($wr_site_id == $response["siteId"] && $wr_api_key == $response["key"]) {
                            $wr_post_id = $response["identifier"];
                            $wr_post_id = url_to_postid(home_url()."/".$wr_post_id);
                            $wr_review_response = $response["data"];
                            $wr_count_review = $wr_review_response["count"];
                            $wr_rating_sum = $wr_review_response["ratingSum"];
                            $wr_rating_star = ($wr_rating_sum != 0) ? (($wr_rating_sum / ($wr_count_review * 5)) * 100) : 0;
                            $wr_rating_sum_1 = $wr_review_response["detailedData"][0]["ratingSum"];
                            $wr_rating_sum_2 = $wr_review_response["detailedData"][1]["ratingSum"];
                            $wr_rating_sum_3 = $wr_review_response["detailedData"][2]["ratingSum"];
                            $wr_rating_sum_4 = $wr_review_response["detailedData"][3]["ratingSum"];
                            $wr_rating_sum_5 = $wr_review_response["detailedData"][4]["ratingSum"];
                            $wr_average_rating = ($wr_rating_sum !=0) ? ($wr_rating_sum_1+$wr_rating_sum_2+$wr_rating_sum_3+$wr_rating_sum_4+$wr_rating_sum_5)/$wr_count_review : 0;
                            if((int)$wr_average_rating != $wr_average_rating) {
                                $wr_average_rating = number_format($wr_average_rating,2);
                            }
                            $wr_review_total = json_encode($response["data"]);
                            if($wr_post_id == 0) {
                                if(is_numeric($response["identifier"])) {
                                    $post_type = get_post_type($response["identifier"]);
                                    if($post_type != "product") {
                                        if (!update_option("wrmr-tot-".$response["identifier"],$wr_review_total)) {
                                            add_option("wrmr-tot-".$response["identifier"],$wr_review_total);
                                        }
                                        if (!update_option("wrmr-avg-".$response["identifier"],$wr_average_rating)) {
                                            add_option("wrmr-avg-".$response["identifier"],$wr_average_rating);
                                        }
                                        if (!update_option("wrmr-cnt-".$response["identifier"],$wr_count_review)) {
                                            add_option("wrmr-cnt-".$response["identifier"],$wr_count_review);
                                        }
                                    }
                                }
                            }
                            else {
                                if(!get_post_meta($wr_post_id,"wiremo-review-total")) {
                                    $post_type = get_post_type($wr_post_id);
                                    if($post_type != "product") {
                                        if (!add_post_meta($wr_post_id, 'wrmr-review-total', $wr_review_total, true)) {
                                            update_post_meta($wr_post_id, 'wrmr-review-total', $wr_review_total);
                                            $result->success = "ok";
                                        }
                                        if (!add_post_meta($wr_post_id, 'wrmr-review-average', $wr_average_rating, true)) {
                                            update_post_meta($wr_post_id, 'wrmr-review-average', $wr_average_rating);
                                            $result->success = "ok";
                                        }
                                        if (!add_post_meta($wr_post_id, 'wrmr-review-count', $wr_count_review, true)) {
                                            update_post_meta($wr_post_id, 'wrmr-review-count', $wr_count_review);
                                            $result->success = "ok";
                                        }
                                    }
                                }
                            }
                        } else {
                            $result->error = "Incorrect siteId and apiKey";
                            echo wrmr_wh_logs("----------Log: Routes wordpress error: ".$result->error ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                        }
                    } else if ($response["type"] == "template") {
                        if ($wr_site_id == $response["siteId"] && $wr_api_key == $response["key"]) {
                            $wr_template_options = $response["data"];
                            if (isset($wr_template_options["starColor"]) && isset($wr_template_options["language"])) {
                                ($wr_template_options["hover"] == true) ? update_option("wrmr-widget-hover", true) : update_option("wrmr-widget-hover", false);
                                (get_option("wrmr-widget-star-color")) ? update_option("wrmr-widget-star-color", $wr_template_options["starColor"]) : add_option("wrmr-widget-star-color", $wr_template_options["starColor"]);
                                (get_option("wrmr-widget-language")) ? update_option("wrmr-widget-language", $wr_template_options["language"]) : add_option("wrmr-widget-language", $wr_template_options["language"]);
                                if(!update_option("wrmr-widget-star-style",$wr_template_options["starStyle"])) {
                                    add_option("wrmr-widget-star-style",$wr_template_options["starStyle"]);
                                }
                                if(!update_option("wrmr-widget-star-size",$wr_template_options["starSize"])) {
                                    add_option("wrmr-widget-star-size",$wr_template_options["starSize"]);
                                }
                                if(!update_option("wrmr-widget-schema-type",$wr_template_options["schemaType"])) {
                                    add_option("wrmr-widget-schema-type",$wr_template_options["schemaType"]);
                                }
                                if(!update_option("wrmr-widget-text-font",$wr_template_options["textFont"])) {
                                    add_option("wrmr-widget-text-font",$wr_template_options["textFont"]);
                                }
                            } else {
                                $result->error = "Object syntax error";
                                echo wrmr_wh_logs("----------Log: Routes wordpress error: ".$result->error ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                            }
                        } else {
                            $result->error = "Incorrect siteId and apiKey";
                            echo wrmr_wh_logs("----------Log: Routes wordpress error: ".$result->error ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                        }
                    } else {
                        $result->error = "You need to specified type";
                        echo wrmr_wh_logs("----------Log: Routes wordpress error: ".$result->error ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                    }
                } else {
                    $result->error = "You have an error";
                    echo wrmr_wh_logs("----------Log: Routes wordpress error: ".$result->error ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                }

            }
            return rest_ensure_response($result);
        }

        public function wrmr_convert_identifiers($identifiers,$type) {
            if(is_array($identifiers) && $type == "identifier") {
                for($i=0;$i<count($identifiers);$i++) {
                    $item = new StdClass();
                    $url = get_permalink($identifiers[$i]);
                    if($url != false) {
                        $path = str_replace(home_url(),"",$url);
                        $title = get_the_title($identifiers[$i]);
                        $path_key = (string)$identifiers[$i];
                        $item->$path_key = new stdClass();
                        $item->$path_key->identifier = $identifiers[$i];
                        $item->$path_key->path = $path;
                        $item->$path_key->title = $title;
                        $this->data[] = $item;
                    }
                }
            }
            else if(is_array($identifiers) && $type == "url") {
                for($i=0;$i<count($identifiers);$i++) {
                    $item = new StdClass();
                    $id = url_to_postid(home_url()."/".$identifiers[$i]);
                    if($id != 0) {
                        $post_type = get_post_type($id);
                        $url = get_permalink($id);
                        $path = str_replace(home_url(),"",$url);
                        $title = get_the_title($id);
                        $path_key = (string)$identifiers[$i];
                        $item->$path_key = new StdClass();
                        if($post_type != "product") {
                            $item->$path_key->identifier = $path;
                        }
                        else {
                            $item->$path_key->identifier = (string)$id;
                        }
                        $item->$path_key->path = $path;
                        $item->$path_key->title = $title;
                        $this->data[] = $item;
                    }
                }
            }
        }

        public function wrmr_import_identifiers($request) {
            if(isset($request['data'])) {
                $result = array();
                $response = $request['data']['response'];
                if(isset($response) && !empty($response)) {
                    $api_key = get_option("wrmr-api-key");
                    if($api_key == $request['data']['apiKey']) {
                        $identifiers = array();
                        $urls = array();
                        for($i=0;$i<count($response);$i++) {
                            if($response[$i]["type"] == "identifier") {
                                $identifiers = $response[$i]["data"];
                            }
                            else {
                                $urls = $response[$i]["data"];
                            }
                        }
                        if(count($identifiers) > 0) {
                            $this->wrmr_convert_identifiers($identifiers,"identifier");
                        }
                        if(count($urls) > 0) {
                            $this->wrmr_convert_identifiers($urls,"url");
                        }
                        $result = $this->data;
                    }
                    else {
                        echo wrmr_wh_logs("----------Log: Routes wordpress Incorrect apikey ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                    }
                }
            }
            return rest_ensure_response($result);
        }

        //Register wiremo routes
        public function register_wrmr_routes() {
            // Registered routes to receive request from wiremo.
            register_rest_route('wiremo/v1', '/hook-wp', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this,'wrmr_update_statistics_rating'),
                ),
            ));
            register_rest_route('wiremo/v1', '/import-wp', array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this,'wrmr_import_identifiers'),
                ),
            ));
        }
    }
    // initialise class WrmrRoutes
    add_action( 'init', 'wrmr_init_routes' );
    if(!function_exists("wrmr_init_routes")) {
        function wrmr_init_routes() {
            global $wr_routes;
            $wr_routes = new WrmrRoutes();
        }
    }
}