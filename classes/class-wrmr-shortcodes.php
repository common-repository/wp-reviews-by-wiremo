<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Wiremo WrmrShortcodes.
 *
 * Create shortcodes
 *
 * @class    WrmrShortcodes
 * @package  Wiremo/Classes
 * @category Class
 * @author   Wiremo
 */

if(!class_exists("WrmrShortcodes")) {
    class WrmrShortcodes {
        public $_data = array();
        public $post_id;
        public $custom_identifiers;
        public $schema_reviews;
        public $widget_lite_params;
        public function __construct() {
            add_shortcode('wr-widget-lite', array($this,'wrmr_widget_lite_func'));
            add_shortcode('wr-widget-reviews', array($this,'wrmr_widget_reviews_func'));
        }

        public function wrmr_convert_path_to_id($paths) {
            $post_ids = array();
            if(is_array($paths)) {
                for($i=0;$i<count($paths);$i++) {
                    $wr_post_id = url_to_postid(home_url()."/".$paths[$i]);
                    if($wr_post_id == 0) {
                        $wr_post_id = $paths[$i];
                    }
                    $post_ids[] = $wr_post_id;
                }
            }
            $post_ids = array_unique($post_ids);
            return $post_ids;
        }

        public function wrmr_convert_paths_to_array($paths) {
            if($paths == "*") {
                $source_exploded = $this->getAllPaths();
            }
            else if (strpos($paths, ',') !== false){
                $source_exploded = explode(",",$paths);
                for($i=0;$i<count($source_exploded);$i++) {
                    $source_exploded[$i] = $source_exploded[$i];
                }
            } else {
                if(!isset($source_exploded)) {
                    $source_exploded= array();
                }
                array_push($source_exploded, $paths);
            }

            return $source_exploded;
        }

        public function get_reviews_translation($count) {
            global $wr_translations;
            if($count == 1) {
                return $wr_translations["Review"];
            }
            if(10 < $count && $count < 15) {
                return $wr_translations["Reviews-11-14"];
            }
            $d = $count % 10;
            if($d == 0) {
                return $wr_translations["Reviews-5-9"];
            }
            if($d == 1) {
                return $wr_translations["Reviews-5-9"];
            }
            if($d < 5) {
                return $wr_translations["Reviews-2-4"];
            }
            return $wr_translations["Reviews-5-9"];
        }

        public function wr_show_widget_lite_by_type($count,$text,$type) {
            $html = '';
            switch($type) {
                case '1' : $html .= '<a style="text-decoration: none" id="customer-reviews-count" data-box="customer-reviews" href="'.get_the_permalink().'#wiremo-widget">'.$count.' '.$text.'</a>';
                    break;
                case '2' : $html .= '<a style="text-decoration: none" id="customer-reviews-count" data-box="customer-reviews" href="'.get_the_permalink().'#wiremo-widget">('.$count.')</a>';
                    break;
                case '3' : $html .= '';
                    break;
                default : $html .= '<a style="text-decoration: none" id="customer-reviews-count" data-box="customer-reviews" href="'.get_the_permalink().'#wiremo-widget">'.$count.' '.$text.'</a>';
            }
            return $html;
        }

        public function wr_validate_data_type($data_type) {
            switch ($data_type) {
                case '1' : $data_attribute = '1';
                    break;
                case '2' : $data_attribute = '2';
                    break;
                case '3' : $data_attribute = '3';
                    break;
                default : $data_attribute = '1';
            }
            return $data_attribute;
        }

        /**
         * @param $atts = atributes of shortcode
         * Create widget lite shortcode
         */
        public function wrmr_widget_lite_func($atts) {
            global $post,$wr_translations;
            $wr_atributes = shortcode_atts( array(
                'source' => '',
                'data-type' => '1'
            ), $atts );

            $wr_content = '';
            $wr_post_id = $post->ID;
            $wr_post_full_path = get_the_permalink($post->ID);
            $wr_post_path = str_replace(home_url(),"",$wr_post_full_path);
            $wr_arr_paths = array();
            if(isset($wr_atributes["source"]) && !empty($wr_atributes["source"])) {
                $source = $wr_atributes["source"];
                $wr_arr_paths = $this->wrmr_convert_paths_to_array($source);
                if(strpos($source,',') !== false) {
                    $wr_arr_paths[] = $wr_post_path;
                }
            }
            else {
                $source = $wr_post_path;
                $wr_arr_paths[] = $wr_post_path;
            }
            if(isset($wr_atributes["data-type"]) && !empty($wr_atributes["data-type"])) {
                $wr_data_type = $wr_atributes["data-type"];
            } else {
                $wr_data_type = '1';
            }
            $wr_data_type = $this->wr_validate_data_type($wr_data_type);
            $wr_post_ids = $this->wrmr_convert_path_to_id($wr_arr_paths);
            $wr_count_review = 0;
            $wr_rating1_count = 0;
            $wr_rating2_count = 0;
            $wr_rating3_count = 0;
            $wr_rating4_count = 0;
            $wr_rating5_count = 0;
            $wr_rating_sum = 0;
            for($i=0;$i<count($wr_post_ids);$i++) {
                if(is_numeric($wr_post_ids[$i])) {
                    if(get_post_meta($wr_post_ids[$i], "wrmr-review-total", true)) {
                        $wr_review_total = get_post_meta($wr_post_ids[$i], "wrmr-review-total", true);
                        $wr_total_rating = json_decode($wr_review_total);
                        $wr_count_review = $wr_count_review + $wr_total_rating->count;
                        $wr_count_review_single = $wr_total_rating->count;
                        $wr_rating_sum = $wr_rating_sum + $wr_total_rating->ratingSum;
                        $wr_rating1_count = $wr_rating1_count + $wr_total_rating->detailedData[0]->count;
                        $wr_rating2_count = $wr_rating2_count + $wr_total_rating->detailedData[1]->count;
                        $wr_rating3_count = $wr_rating3_count + $wr_total_rating->detailedData[2]->count;
                        $wr_rating4_count =  $wr_rating4_count + $wr_total_rating->detailedData[3]->count;
                        $wr_rating5_count =  $wr_rating5_count + $wr_total_rating->detailedData[4]->count;
                    }
                    if(get_post_meta($wr_post_ids[$i], "wiremo-review-total", true)) {
                        $wr_review_total = get_post_meta($wr_post_ids[$i], "wiremo-review-total", true);
                        $wr_total_rating = json_decode($wr_review_total);
                        $wr_count_review = $wr_count_review + $wr_total_rating->count;
                        $wr_count_review_single = $wr_total_rating->count;
                        $wr_rating_sum = $wr_rating_sum + $wr_total_rating->ratingSum;
                        $wr_rating1_count = $wr_rating1_count + $wr_total_rating->detailedData[0]->count;
                        $wr_rating2_count = $wr_rating2_count + $wr_total_rating->detailedData[1]->count;
                        $wr_rating3_count = $wr_rating3_count + $wr_total_rating->detailedData[2]->count;
                        $wr_rating4_count =  $wr_rating4_count + $wr_total_rating->detailedData[3]->count;
                        $wr_rating5_count =  $wr_rating5_count + $wr_total_rating->detailedData[4]->count;
                    }
                }
                else {
                    if(get_option("wrmr-tot-".$wr_post_ids[$i])) {
                        $wr_review_total = get_option("wrmr-tot-".$wr_post_ids[$i]);
                        $wr_total_rating = json_decode($wr_review_total);
                        $wr_count_review = $wr_count_review + $wr_total_rating->count;
                        $wr_count_review_single = $wr_total_rating->count;
                        $wr_rating_sum = $wr_rating_sum + $wr_total_rating->ratingSum;
                        $wr_rating1_count = $wr_rating1_count + $wr_total_rating->detailedData[0]->count;
                        $wr_rating2_count = $wr_rating2_count + $wr_total_rating->detailedData[1]->count;
                        $wr_rating3_count = $wr_rating3_count + $wr_total_rating->detailedData[2]->count;
                        $wr_rating4_count =  $wr_rating4_count + $wr_total_rating->detailedData[3]->count;
                        $wr_rating5_count =  $wr_rating5_count + $wr_total_rating->detailedData[4]->count;
                    }
                }
            }

            $wr_rating1_percent = ($wr_rating1_count != 0) ? (($wr_rating1_count / $wr_count_review) * 100) : 0;
            $wr_rating2_percent = ($wr_rating2_count != 0) ? (($wr_rating2_count / $wr_count_review) * 100) : 0;
            $wr_rating3_percent = ($wr_rating3_count != 0) ? (($wr_rating3_count / $wr_count_review) * 100) : 0;
            $wr_rating4_percent = ($wr_rating4_count != 0) ? (($wr_rating4_count / $wr_count_review) * 100) : 0;
            $wr_rating5_percent = ($wr_rating5_count != 0) ? (($wr_rating5_count / $wr_count_review) * 100) : 0;

            $wr_rating_star = ($wr_rating_sum != 0) ? (($wr_rating_sum / ($wr_count_review * 5)) * 100) : 0;

            $wr_star_color = get_option("wrmr-widget-star-color");
            $wr_widget_lang = get_option("wrmr-widget-language");
            $wr_widget_hover = get_option("wrmr-widget-hover");
            $wr_widget_star_style = get_option("wrmr-widget-star-style");
            $wr_widget_star_size = get_option("wrmr-widget-star-size");

            if(!isset($wr_count_review)) {
                $wr_count_review = 0;
            }

            $wr_star_size = 20;
            switch($wr_widget_star_size) {
                case "0":
                    $wr_star_size = 16;
                    break;
                case "1":
                    $wr_star_size = 20;
                    break;
                case "2":
                    $wr_star_size = 24;
                    break;
                default:
                    $wr_star_size = 20;
            }
            $wr_count_review_text = array(
                "16" => "14px",
                "20" => "18px",
                "24" => "24px"
            );
            $wr_count_review_padding = array(
                "16" => "6px",
                "20" => "8px",
                "24" => "10px"
            );
            include dirname(__DIR__).'/lang/wrmr-widget-'.$wr_widget_lang.'.php';

            if($wr_widget_star_style == 1) {
                $wr_star_style_class = " fill-wr-style";
            } else {
                $wr_star_style_class = " stroke-wr-style";
            }

            $wr_trans_reviews = '';
            if($wr_count_review == 0) {
                $wr_trans_reviews = $wr_translations["Reviews-5-9"];
            }
            else {
                $wr_trans_reviews = $this->get_reviews_translation($wr_count_review);
            }
            $wr_content_rating_details = '';
            if(!is_single() && !is_page()) {
                if(get_option("wrmr-widget-schema-type") == "ecommerce") {
                    $this->_data[] = $this->wrmr_create_single_post_schema($wr_post_ids,$wr_post_id,"Product");
                }
                else {
                    $this->_data[] = $this->wrmr_create_single_post_schema($wr_post_ids,$wr_post_id,"Organization");
                }
                add_action("wp_footer",array($this,'wrmr_loop_schema'));
            }
            $wrmr_svg_stroke = '<svg style="width:'.$wr_star_size.'px !important;height:'.$wr_star_size.'px !important;" xmlns="http://www.w3.org/2000/svg" height="'.$wr_star_size.'" viewBox="0 0 1792 1792"><path d="M1201 1004l306-297-422-62-189-382-189 382-422 62 306 297-73 421 378-199 377 199zm527-357q0 22-26 48l-363 354 86 500q1 7 1 20 0 50-41 50-19 0-40-12l-449-236-449 236q-22 12-40 12-21 0-31.5-14.5t-10.5-35.5q0-6 2-20l86-500-364-354q-25-27-25-48 0-37 56-46l502-73 225-455q19-41 49-41t49 41l225 455 502 73q56 9 56 46z"/></svg>';
            $wrmr_svg_shape = '<svg style="width:'.$wr_star_size.'px !important;height:'.$wr_star_size.'px !important;" xmlns="http://www.w3.org/2000/svg" height="'.$wr_star_size.'" viewBox="0 0 1792 1792"><path d="M1728 647q0 22-26 48l-363 354 86 500q1 7 1 20 0 21-10.5 35.5t-30.5 14.5q-19 0-40-12l-449-236-449 236q-22 12-40 12-21 0-31.5-14.5t-10.5-35.5q0-6 2-20l86-500-364-354q-25-27-25-48 0-37 56-46l502-73 225-455q19-41 49-41t49 41l225 455 502 73q56 9 56 46z"/></svg>';
            $wrmr_svg = ($wr_widget_star_style == 0) ? $wrmr_svg_stroke : $wrmr_svg_shape;
            $text_font_option = get_option("wrmr-widget-text-font");
            $wrmr_text_font = $text_font_option ? $text_font_option : "Open Sans";
            if(isset($wr_widget_hover) && !empty($wr_widget_hover)) {
                $wr_content_rating_details .= '<div class="widget-lite-score-wp">';
                $wr_content_rating_details .= '<table class="widget-lite-score-detailed">';
                $wr_content_rating_details .= '<tbody>';
                $wr_content_rating_details .= '<tr><td>5 '.$wr_translations["stars"].'</td><td><div class="score-gray-bar"><div class="score-active-bar" style="width:'.round($wr_rating5_percent).'%; background-color:'.$wr_star_color.';"></div></div></td><td>'.round($wr_rating5_percent).'%</td></tr>';
                $wr_content_rating_details .= '<tr><td>4 '.$wr_translations["star234"].'</td><td><div class="score-gray-bar"><div class="score-active-bar" style="width:'.round($wr_rating4_percent).'%; background-color: '.$wr_star_color.';"></div></div></td><td>'.round($wr_rating4_percent).'%</td></tr>';
                $wr_content_rating_details .= '<tr><td>3 '.$wr_translations["star234"].'</td><td><div class="score-gray-bar"><div class="score-active-bar" style="width:'.round($wr_rating3_percent).'%; background-color:'.$wr_star_color.';"></div></div></td><td>'.round($wr_rating3_percent).'%</td></tr>';
                $wr_content_rating_details .= '<tr><td>2 '.$wr_translations["star234"].'</td><td><div class="score-gray-bar"><div class="score-active-bar" style="width:'.round($wr_rating2_percent).'%; background-color:'.$wr_star_color.';"></div></div></td><td>'.round($wr_rating2_percent).'%</td></tr>';
                $wr_content_rating_details .= '<tr><td>1 '.$wr_translations["star"].'</td><td><div class="score-gray-bar"><div class="score-active-bar" style="width:'.round($wr_rating1_percent).'%; background-color:'.$wr_star_color.';"></div></div></td><td>'.round($wr_rating1_percent).'%</td></tr>';
                $wr_content_rating_details .= '</tbody>';
                $wr_content_rating_details .= '</table>';
                $wr_content_rating_details .= '</div>';
            }
            else {
                $wr_content_rating_details = '';
            }
            $wr_widget_lite_text = $this->wr_show_widget_lite_by_type($wr_count_review,$wr_trans_reviews,$wr_data_type);
            $wr_content .= '<div id="widget-lite-short-wp">';
            $wr_content .= '<div class="widget-lite">';
            $wr_content .= '<div class="widget-lite-container" style="font-family:'.$wrmr_text_font.',sans-serif;">';
            $wr_content .= '<div class="floating__rating '.$wr_star_style_class.'" customizable="" customizetype="floatingContainer">';
            $wr_content .= '<div style="height:'.$wr_star_size.'px;font-size:'.$wr_star_size.'px;line-height:'.$wr_star_size.'px;" class="floating__rating--stroke" customizable="" customizetype="floatingStar">';
            $wr_content .= $wrmr_svg.$wrmr_svg.$wrmr_svg.$wrmr_svg.$wrmr_svg;
            $wr_content .= '</div>';
            $wr_content .= '<div class="floating__rating--fill" customizable="" customizetype="floatingStar" style="position:relative;z-index:2;fill:'.$wr_star_color.'; width:'.$wr_rating_star.'%; overflow: hidden;height:'.$wr_star_size.'px;font-size:'.$wr_star_size.'px;line-height:'.$wr_star_size.'px;">';
            $wr_content .= $wrmr_svg.$wrmr_svg.$wrmr_svg.$wrmr_svg.$wrmr_svg;
            $wr_content .= '</div>';
            $wr_content .= '</div>';
            $wr_content .= $wr_content_rating_details;
            $wr_content .= '<div style="height:'.$wr_count_review_text[$wr_star_size].';font-size:'.$wr_count_review_text[$wr_star_size].';line-height:'.$wr_count_review_text[$wr_star_size].';padding-left:'.$wr_count_review_padding[$wr_star_size].';padding-top:3px;" class="widget-lite-count">';
            $wr_content .= $wr_widget_lite_text;
            $wr_content .= '</div>';
            $wr_content .= '</div>';
            $wr_content .= '</div>';
            $wr_content .= '</div>';

            if(!get_option("wrmr_hide_mini_widget")) {
                return $wr_content;
            }
            else {
                if($wr_count_review != 0) {
                    return $wr_content;
                }
                else {
                    return '';
                }
            }
        }

        public function wrmr_loop_schema() {
            $wiremo_json_ld = '<script type="application/ld+json">' . json_encode($this->_data) . '</script>';
            wp_scripts()->add_inline_script('wiremo_json_ld', $wiremo_json_ld, 'after');
        }

        public function wrmr_trim_excerpt($length,$wr_post_content) {
            $explicit_excerpt = $wr_post_content;
            if ( '' == $explicit_excerpt ) {
                $text = get_the_excerpt('');
                $text = apply_filters('the_content', $text);
                $text = str_replace(']]>', ']]>', $text);
            }
            else {
                $text = apply_filters('the_content', $explicit_excerpt);
            }
            $text = strip_shortcodes( $text ); // optional
            $text = strip_tags($text);
            $excerpt_length = $length;
            $words = explode(' ', $text, $excerpt_length + 1);
            if (count($words)> $excerpt_length) {
                array_pop($words);
                //array_push($words, '[&hellip;]');
                $text = implode(' ', $words);
                $text = apply_filters('the_excerpt',$text);
            }
            return $text;
        }

        /**
         * @param $url = url Api
         * @return mixed
         */
        public function wrmr_generate_schema_reviews($url) {
            $response = wp_remote_get( $url);
            if(is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                echo wrmr_wh_logs("----------Log: Generate schema reviews error: ".$error_message." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                return $error_message;
            }
            else {
                $data = $response["body"];
                return $data;
            }
        }


        /**
         * @param $post_ids
         * @param $id
         * @param $type
         * @return array
         * Create schema for single post or page
         */
        public function wrmr_create_single_post_schema($post_ids,$id,$type) {
            $rating_value = 0;
            $rating_count = 0;
            $count_reviews = 0;
            if(is_array($post_ids)) {
                for($i=0;$i<count($post_ids);$i++) {
                    if(is_numeric($post_ids[$i])) {
                        if(get_post_meta($post_ids[$i],"wrmr-review-average",true)) {
                            $wr_review_total = get_post_meta($post_ids[$i], "wrmr-review-total", true);
                            $wr_total_rating = json_decode($wr_review_total);
                            $count_reviews = $count_reviews + 1;
                            $rating_value = $rating_value + $wr_total_rating->ratingSum;
                            $rating_count = $rating_count + $wr_total_rating->count;
                        }
                        if(get_post_meta($post_ids[$i],"wiremo-review-average",true)) {
                            $wr_review_total = get_post_meta($post_ids[$i], "wiremo-review-total", true);
                            $wr_total_rating = json_decode($wr_review_total);
                            $count_reviews = $count_reviews + 1;
                            $rating_value = $rating_value + $wr_total_rating->ratingSum;
                            $rating_count = $rating_count + $wr_total_rating->count;
                        }
                    }
                    else {
                        if(get_option("wrmr-tot-".$post_ids[$i])) {
                            $wr_review_total = get_option("wrmr-tot-".$post_ids[$i]);
                            $wr_total_rating = json_decode($wr_review_total);
                            $count_reviews = $count_reviews + 1;
                            $rating_value = $rating_value + $wr_total_rating->ratingSum;
                            $rating_count = $rating_count + $wr_total_rating->count;
                        }
                    }
                }
            }
            if($rating_value > 0 && $count_reviews > 0) {
                $rating_value = $rating_value / $rating_count;
            }
            $site_name      = get_bloginfo( 'name' );
            $schema          = array();
            $schema["@context"] = "https://schema.org/";
            $schema['@type'] = $type;
            if($type == "Organization") {
                $schema['@id']   = get_the_permalink($id)."#organization";
                $schema['name']  = $site_name;
            }
            else {
                $schema['@id']   = get_the_permalink($id);
                $schema['name']  = get_the_title($id);
            }
            $schema['url']   = get_the_permalink($id);
            if ( $rating_count !=0) {
                $schema['aggregateRating'] = array(
                    '@type'       => 'AggregateRating',
                    'ratingValue' => round($rating_value,2),
                    'reviewCount' => $rating_count,
                    'worstRating' =>  '1',
                    'bestRating' => '5'
                );
            }
            return $schema;
        }


        /**
         * @param $reviews_data
         * @param $type_schema_name
         * @return array
         */
        public function wrmr_create_result_review($reviews_data,$type_schema_name) {
            $result_schema = array();
            for ($i = 0; $i < count($reviews_data); $i++) {
                $result_schema[] = array(
                    "@type" => "Review",
                    "@id" => get_the_permalink($this->post_id)."#".$reviews_data[$i]->_id,
                    "datePublished" => $reviews_data[$i]->dateTime,
                    "description" => $reviews_data[$i]->message,
                    "itemReviewed" => array(
                        "@type" => $type_schema_name,
                        "name" => $reviews_data[$i]->title
                    ),
                    "reviewRating" => array(
                        "@type" => "Rating",
                        "ratingValue" => $reviews_data[$i]->rating
                    ),
                    "author" => array(
                        "@type" => "Person",
                        "name" => $reviews_data[$i]->userId->name
                    )
                );
            }
            return $result_schema;
        }

        //Append JSON LD schema in single page
        public function wrmr_single_post_schema() {
            global $post;
            $wr_post_full_path = get_page_uri($this->post_id) ;
            $wr_post_path = str_replace(home_url(),"",$wr_post_full_path);
            $wr_post_path = "/".$wr_post_path."/";
            $total_schema = array();
            $post_ids = array();
            //Get post ids by pathname
            if(is_array($this->custom_identifiers)) {
                for($j=0;$j<count($this->custom_identifiers);$j++) {
                    $wr_post_id = url_to_postid($this->custom_identifiers[$j]);
                    if($wr_post_id == 0) {
                        $wr_post_id = $this->custom_identifiers[$j];
                    }
                    $post_ids[] = $wr_post_id;
                }
            }
            $post_ids = array_unique($post_ids);

            if(get_option("wrmr-widget-schema-type") == "ecommerce") {
                $schema_post = $this->wrmr_create_single_post_schema($post_ids,$this->post_id,"Product");
                $type_schema_name = "Product";
            }
            else {
                $schema_post = $this->wrmr_create_single_post_schema($post_ids,$this->post_id,"Organization");
                $type_schema_name = "Organization";
            }
            if(is_array($this->custom_identifiers)) {
                $wr_filter = new stdClass();
                $wr_filter->siteId = esc_attr(get_option("wrmr-site-id"));
                if($this->widget_lite_params == "*") {
                    $wr_filter->identifier = $this->widget_lite_params;
                }
                else {
                    $wr_filter->identifier = implode(",",$this->custom_identifiers);
                    $wr_filter->identifier = str_replace(home_url(),"",$wr_filter->identifier );
                }
                $wr_filter_encoded = json_encode($wr_filter);
                $wr_url = WRMR_URLAPP . "/v1/reviews?filter=" . $wr_filter_encoded;
                $schema_reviews = json_decode($this->wrmr_generate_schema_reviews($wr_url));
                $this->schema_reviews[] = $this->wrmr_create_result_review($schema_reviews->data,$type_schema_name);
            }
            $total_schema = array();
            $data = array(
                "@context" => "https://schema.org/",
                "@graph" => $this->schema_reviews
            );
            $total_schema["@graph"][] = $data;
            $total_schema["@graph"][] = $schema_post;
            echo '<script type="application/ld+json">' . json_encode($total_schema) .'</script>';
        }

        public function getAllPaths() {
            global $wpdb;
            $paths = array();
            $api_key = get_option("wrmr-api-key");
            $url = WRMR_URLAPP."/v1/sites/identifiers/all/?apiKey=".$api_key;
            $response = wp_remote_get( $url);
            if( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                echo wrmr_wh_logs("----------Log: Get all identifiers from API error: ".$error_message ." ---------- date = ".date("d-M-Y")." time = ".date("H:i:s"));
                return $error_message;
            }
            else {
                $data = json_decode($response["body"]);
                for($i=0;$i<count($data);$i++) {
                    $paths[] = $data[$i]->identifier;
                }
            }
            return $paths;
        }

        public function wrmr_widget_reviews_func($atts) {
            global $post;
            $source_exploded = array();
            $wr_atributes = shortcode_atts( array(
                'source' => ''
            ), $atts );
            $wr_post_full_path = get_the_permalink($post->ID);
            $wr_post_path = str_replace(home_url(),"",$wr_post_full_path);

            if(isset($wr_atributes["source"]) && !empty($wr_atributes["source"])) {
                $source = $wr_atributes["source"];
                $source_status = true;
            }
            else {
                $source_status = false;
            }

            if($source_status == true) {
                if($source == "*") {
                    $source_encoded = $source;
                    $source_exploded = $this->getAllPaths();
                }
                else if(strpos($paths, ',') !== false) {
                    $source_exploded = explode(",",$source);
                    array_push($source_exploded,$wr_post_path);
                    $source_imploded = implode(",",$source_exploded);
                    $source_encoded = rawurlencode($source_imploded);
                } else {
                    if(!isset($source_exploded)) {
                        $source_exploded = array();
                    }
                    array_push($source_exploded, $paths);
                    $source_encoded = $source;
                }
            }
            else {
                $source_exploded = array($wr_post_path);
                $source_encoded = rawurlencode($wr_post_path);
            }

            $structure_permalink = get_option("permalink_structure");
            if ($structure_permalink == ''){
                $str_permalink = 'plainPermalink';
            }else{
                $str_permalink = 'notRelevant';
            }

            $this->post_id = $post->ID;
            $this->custom_identifiers = $source_exploded;
            $this->widget_lite_params = $source_encoded;
            add_action("wp_footer",array($this,'wrmr_single_post_schema'));
            $wr_content = '';
            $wr_content .= '<div id="wiremo-widget" data-platform="wordpress"></div>';
            $wr_content .= '<script>!function(){var e=window.wiremo_config?new window.wiremo_config:{},t=Object.assign({reviewSource:"'.$source_encoded.'",permalinkStructure:"'.$str_permalink.'",identifier:"'.rawurlencode($wr_post_path).'"},e),n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src="'.WRMR_URLWIDGET.'?k='.get_option("wrmr-site-id").'&w="+encodeURIComponent(JSON.stringify(t));var o=document.getElementsByTagName("script")[0];o.parentNode.insertBefore(n,o)}();</script>';
            return $wr_content;
        }
    }

    // initialise class WiremoShortcodes
    add_action( 'init', 'wrmr_init_shortcodes' );
    if(!function_exists("wrmr_init_shortcodes")) {
        function wrmr_init_shortcodes() {
            global $wr_shortcodes;
            $wr_shortcodes = new WrmrShortcodes();
        }
    }
}