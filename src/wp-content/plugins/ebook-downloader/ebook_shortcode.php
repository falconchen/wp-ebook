<?php

add_shortcode('ebook_downloader', 'ebook_downloader_func');

function ebook_downloader_func() {
    /*
     * Get All the Ebook From The database
     */
    global $wpdb, $table_prefix;
    $ebook_args = array(
        'numberposts' => -1,
        'post_type' => 'ebook',
        'order' => 'DESC',
        'post_status' => 'publish'
    );

    $ebook_arr = get_posts($ebook_args);
    $str = '';
    if (isset($_GET['ebookid']) && $_GET['ebookid'] != '') {

        if (isset($_POST['ebook_pay'])) {
            $name = $_POST['p_name'];
            $email = $_POST['p_email'];
            $ebook_id = $_POST['ebook_id'];
            $paypal_type = get_option('paypal_type');
            $live_id = get_option('live_id');
            $sandbox_id = get_option('sandbox_id');
            $price = get_post_meta($ebook_id, '_ebook_price', true);
            
            if ($paypal_type == 'sandbox') {
                $action = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';
                $username = $sandbox_id;
            } else {
                $action = 'https://www.paypal.com/cgi-bin/webscr?';
                $username = $live_id;
            }

            $custom = $ebook_id . '#' . $name . '#' . $email . '#' . $price;

            $query_string = 'cmd=_xclick&business=' . urlencode($username) . '&item_name=' . urlencode('Ebook Download') . '&item_number=1&custom=' . urlencode($custom) . '&amount=' . urlencode($price) . '&no_shipping=0&no_note=1&currency_code=' . urlencode('USD') . '&return=' . urlencode(get_permalink()) . '?action=success&cancel_return=' . urlencode(get_permalink()) . '?payment=false&notify_url=' . urlencode(get_permalink()) . '?ipn=true';

            wp_redirect($action . $query_string);
            exit;
        }

        $str.= '<div id="ebook_msg" style="display:none;"></div>';
        $str.= '<form name="paypal_form" method="post" action="" onsubmit="return paypal_form_validation();">';
        $str.= '<div><label>Enter Your Name:</label> <input type="text" name="p_name" id="p_name" value="" /></div>';
        $str.= '<div><label>Enter Your Email:</label><input type="text" name="p_email" id="p_email" value="" /></div>';
        $str.= '<input type="hidden" name="ebook_id" value="' . base64_decode($_GET['ebookid']) . '" />';
        $str.= '<div><input type="submit" name="ebook_pay" value="Pay" /></div>';
        $str.= '</form>';
    } else {

        $str .= '<div class="list_ebook_con">';
        foreach ($ebook_arr as $ebook) {
            $ebook_price = get_post_meta($ebook->ID, '_ebook_price', true);
            $ebook_file = get_post_meta($ebook->ID, '_ebook_url', true);
            $thumbnail_id = get_post_thumbnail_id($ebook->ID);
            $img = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
            $str .= '<div class="list_ebook">';
            $str .= '<div class="list_ebook_img"><img src="' . $img[0] . '" /></div>';
            $str .= '<div style="padding:10px 5px; color:#00437F;">Ebook Name :' . $ebook->post_title . '</div>';
            $str .= '<div style="padding:0px 5px; color:#00437F;">Price: &dollar;' . $ebook_price . '</div>';
            $str .= '<div style="padding:5px 5px; color:#00437F;">Download: <a href="' . get_permalink() . '?ebookid=' . base64_encode($ebook->ID) . '"><img src="' . EBOOK_PLUGIN_URL . 'icon/pdf.png" /> </a></div>';
            $str .= '</div>';
        }
    }

    $str.= '</div>';
    return $str;
}

?>