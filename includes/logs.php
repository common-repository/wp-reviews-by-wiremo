<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Added message in to log file
 * @param $message
 */
function wrmr_wh_logs($message) {
    $upload_dir = wp_upload_dir();
    $logfile = $upload_dir["basedir"].'/wrmr-' . date('M-Y') . '.log';
    if(file_put_contents($logfile,$message."\n",FILE_APPEND)) {

    }
}