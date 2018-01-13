<?php
/*
Plugin Name:Ebook Downloader
Description: Add Ebook By admin and download after payment in paypal.
Version: 1.0
Author:Infoway LLC
Author URI:http://www.infoway.us
*/
ob_start();

/*
 * Define some basic plugin url and path
 */

global $wpdb,$table_prefix;

define('EBOOK_PLUGIN_PATH', dirname(__FILE__));
define('EBOOK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EBOOK_DOWNLOAD_TBL',$table_prefix.'downloader_info');

/**Table Creation When Plugin Is activated********/

function ebook_tbl_register_func()
{
    global $wpdb;
    
    $tbl_query = ' CREATE TABLE `'.EBOOK_DOWNLOAD_TBL.'`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `name` VARCHAR(50) NOT NULL ,
    `email` VARCHAR(100) NOT NULL ,
    `transac_id` TEXT NOT NULL ,
    `ebook_id` INT NOT NULL ,
    `download_link` TEXT NOT NULL ,
    `status` VARCHAR(20) NOT NULL ,
    `time` DATETIME NOT NULL 
    
    ) ENGINE = MYISAM ';
    $wpdb->query($tbl_query);
}

register_activation_hook(__FILE__,'ebook_tbl_register_func');
/*******Enqueue script for admin end*******************/
add_action('admin_enqueue_scripts','admin_js_func');

function admin_js_func()
{
    wp_register_script('admin-plugin-js',EBOOK_PLUGIN_URL.'admin/js/admin_plugin.js');
    wp_enqueue_script('admin-plugin-js');
}

/*******Enqueue script for front end*******************/
add_action('wp_enqueue_scripts','frontend_js_func');

function frontend_js_func()
{
    wp_enqueue_script('jquery');
    wp_register_script('frontend-plugin-js',EBOOK_PLUGIN_URL.'js/ebook_frontend.js');
    wp_enqueue_script('frontend-plugin-js');
    wp_register_style('ebook-css',EBOOK_PLUGIN_URL.'css/ebook.css');
    wp_enqueue_style('ebook-css');
}

function send_mail_func($to, $subject, $message) {



    $headers = "From: ".get_bloginfo("name") . ' <'.get_option('admin_email').'>' . "\r\n";

    $headers .= "MIME-Version: 1.0\r\n";

    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";



    wp_mail($to, $subject, $message, $headers);

}
function custom_file_upload($filename){
    $override['test_form'] = FALSE;
    $override['action'] = 'wp_handle_upload';
    if(isset ($filename['name']) && $filename['name'] !=''){
        
        $uploaded_file = wp_handle_upload($filename,$override);
        $url = $uploaded_file['url'];
        //$url=str_replace ( ABSPATH , get_option('siteurl').'/' ,$path);
        
    }
    return $url;
}

/****************Ebook Force Download Finction*/

function ebook_force_download($filename)
{

        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        /*if( $filename == "" ) 
        {
        echo "<html><title>eLouai's Download Script</title><body>ERROR: download file NOT SPECIFIED. USE force-download.php?file=filepath</body></html>";
        exit;
        } elseif ( ! file_exists( $filename ) ) 
        {
        echo "<html><title>eLouai's Download Script</title><body>ERROR: File not found. USE force-download.php?file=filepath</body></html>";
        exit;
        };*/

        switch( $file_extension )
        {
        case "pdf": $ctype="application/pdf"; break;
        case "exe": $ctype="application/octet-stream"; break;
        case "zip": $ctype="application/zip"; break;
        case "doc": $ctype="application/msword"; break;
        case "xls": $ctype="application/vnd.ms-excel"; break;
        case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
        case "gif": $ctype="image/gif"; break;
        case "png": $ctype="image/png"; break;
        case "jpeg":$ctype="image/jpg"; break;
        case "jpg": $ctype="image/jpg"; break;
        default: $ctype="application/force-download";
        }
        header('Content-Description: File Transfer');
            header("Content-Type: $ctype");
            header('Content-Disposition: attachment; filename='.basename($filename));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            ob_clean();
            flush();
            readfile($filename);
            exit;
}

add_action('init', 'paypal_process_func');



function paypal_process_func(){

    global $wpdb, $table_prefix;

   

if (isset($_GET['ipn']) && $_GET['ipn'] == 'true') {





$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {

$value = urlencode(stripslashes($value));

$req .= "&$key=$value";

}

// post back to PayPal system to validate

$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";

$header .= "Content-Type: application/x-www-form-urlencoded\r\n";

$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";




$fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);



if (!$fp) {


}else if($_POST['payment_status'] == 'Completed'){

	$custom = $_POST['custom'];

        $transaction_id = $_POST['txn_id'];

        $custom_val = explode('#', $custom);

$ebook_id = $custom_val['0'];                                                                    

$name = $custom_val['1'];                                                                

$email = $custom_val['2'];                                                                    

$price = $custom_val['3'];       

$download_duration = get_option('download_duration');

$download_url = get_post_meta($ebook_id,'_ebook_url',true);


$download_link = get_bloginfo('siteurl').'/ebooks/?download='.base64_encode($transaction_id);



            $insert_arr = array(
                'name'=>$name,
                'email' => $email,
                'transac_id' => $transaction_id,
                'ebook_id' => $ebook_id,
                'download_link' => $download_url,
                'status' => '0',
                'time' => date('Y-m-d h:i:s',time()+(86400*$download_duration))
                );
            
            $wpdb->insert($table_prefix.'downloader_info',$insert_arr);


/************For Admin************/
    $to = get_bloginfo('admin_email');
    
    $msg = '<p>Thank You For Purchasing this Ebook.</p>';
    
    
    $msg .= '<p>Paypal Transacion Id: '.$transaction_id.' </p>';   

    $msg .= '<p>Please Click <a href="'.$download_link.'" > Here </a> To Download The Ebook</p>';
    $msg .= '<p>This Download Link will Expire After '.$download_duration.' Days</p>';
    $sub = 'Thank You For Purchasing Ebook';
    

    
    

    /*shoot to Purchaser*/
send_mail_func($email,$sub,$msg);

} else {

fputs($fp, $header.$req);

while (!feof($fp)) {

$res = fgets($fp, 1024);

if (strcmp($res, "VERIFIED") == 0) {

send_mail_func(get_option('admin_email'), 'Payment success', 'success');

// PAYMENT VALIDATED & VERIFIED!








} else if (strcmp($res, "INVALID") == 0) {



// PAYMENT INVALID & INVESTIGATE MANUALY!

send_mail_func(get_option('admin_email'), 'Payment Invalid', 'invalid');

}

}

fclose($fp);

}

}

}

if(isset($_GET['download']) && $_GET['download']!= '')
{
    $transac_id = base64_decode($_GET['download']);
   
    global $wpdb,$table_prefix;
    
    $query = 'SELECT * FROM '.$table_prefix.'downloader_info WHERE transac_id="'.$transac_id.'"';
    
    $download_info = $wpdb->get_results($query);
    
    $download_exp_time = $download_info[0]->time;
    $current_time = date('Y-m-d h:i:s');
    $download_duration = get_option('download_duration');
    if($download_info[0]->status == 0 && $current_time <= $download_exp_time )
    {
        $update_arr = array('status'=> 1);
        $where = array('transac_id' => $transac_id);
        
        $wpdb->update($table_prefix.'downloader_info',$update_arr,$where);
        ebook_force_download($download_info[0]->download_link);
        
    }elseif($download_info[0]->status == 1 &&  $current_time <= $download_exp_time )
    {
        echo 'You Have Already Download this Ebook';
    }elseif($download_info[0]->status == 0 && $current_time >= $download_exp_time )
    {
	/*echo $current_time.'~'.$download_exp_time;*/
        echo 'Your Download link Has Been expire';
        
    }    
    
    
}    
require_once(EBOOK_PLUGIN_PATH.'/load.php');
require_once(EBOOK_PLUGIN_PATH.'/ebook_shortcode.php');
?>