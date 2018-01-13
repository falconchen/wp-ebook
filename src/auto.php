<?php
/**
 *
 * Description:
 * Author: falcon
 * Date: 17/12/28
 * Time: 下午6:49
 *
 */

if ( !defined('ABSPATH') ) {
    /** Set up WordPress environment */
    require_once('wp-load.php');
}
set_post_thumbnail_size( 350, 224, true );

$content = file_get_contents('http://www.mingwatch.com');
preg_match_all(
    '#<div class="article-box"><a href=".*/article/(.*)/" target="blank"><img src="(.*)"/>#iUs',
            $content,
            $matches);



//var_dump($matches);exit;
//$content = file_get_contents('http://www.mingwatch.com');
//
//preg_match_all('#/article/(.*?)/#',$content,$matches);
//
//print_r(array_unique($matches[0]));
//

//http://www.mingwatch.com/article/a-symbol-of-revolution-cartier-tank/
$slugs = array_reverse( $matches[1] );
$images = array_reverse($matches[2]);

foreach($slugs as $k=>$slug) {
    $post =array( 'post_author' => 1, 'post_status' => 'publish',);
    $content = file_get_contents('http://www.mingwatch.com/article/'. $slug  .'/');


    preg_match('#<div class="entry-content">(.*)<footer class="entry\-footer">#iUs',$content,$match);
    $post['post_content'] = str_replace("</div><!-- .entry-content -->",'',$match[1]);

    preg_match('#<h1 class="entry-title">(.*)</h1>#iUs',$content,$match);
    $post['post_title'] = $match[1];


    $post_id = wp_insert_post($post,true);

    wp_set_post_categories( $post_id, array( rand(6,8) ) ); //设置分类
    $attach_id = fetch_image_to_local($images[$k]);

    set_post_thumbnail($post_id, $attach_id);
    echo "finish:". $post['post_title'];

    echo "\n";

}





/*

$post = array(
    'post_title'=>$wp_item->title,
    'post_date'=>$wp_item->post_date,
    'post_type'=>($wp_item->categories != FALSE) ? 'post': (is_event_sid($wp_item->sid)?'event':'gallery'),
    'post_author' => 1, //默认的发布作者
    'post_content'=>$wp_item->post_content

);


$post_id = wp_insert_post($post,true);

if( is_wp_error($post_id) ) {
    $wp_err = $post_id;
    MpwLogger::write(sprintf("[警告] 文章 [ %s ] 插入失败，原因：%s",$wp_item->title,$wp_err->get_error_message()));

}
if($wp_item->categories != FALSE){
    wp_set_post_categories( $post_id, $wp_item->categories ); //设置分类

*/
function fetch_image_to_local($img_url, $title="",$content="") {

    $basename = basename($img_url);
    if(strpos($img_url,'?')) {
        $basename = md5($basename);
    }
    $wp_upload_dir = wp_upload_dir() ;
    $oscpress_data_dir = $wp_upload_dir['basedir'] . '/oscpress';

    if(file_exists($oscpress_data_dir.'/'.$basename) ){
        $basename = date('ymdHis_',current_time( 'timestamp' )). $basename;
        //return false;
    }


    if(!is_dir($oscpress_data_dir)){
        @mkdir($oscpress_data_dir,0755);
    }
    if(!strpos($basename,'.')){$basename .= '.jpg';}

    $filename = 'oscpress/'. $basename;

    $path = $oscpress_data_dir . '/'. $basename;

    if(!file_put_contents($path,file_get_contents($img_url))) {
        return false;
    }
    $url = $wp_upload_dir['baseurl'] . '/' .$filename;
    $image_type = wp_check_filetype_and_ext($path, $filename, null);

    $attachment = array(
        'post_mime_type' => $image_type['type'],
        'guid' => $url,
        'post_parent' => 0,
        'post_title' => $title,
        'post_content' => $content,
    );

    $thumbnail_id = wp_insert_attachment($attachment,$filename,0);
    if (!is_wp_error($thumbnail_id)) {
        require_once ABSPATH . WPINC .'/post.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata($thumbnail_id, wp_generate_attachment_metadata($thumbnail_id, $path));
        update_post_meta($thumbnail_id,'_via_bind',1);
        return $thumbnail_id;
    }else{
        return false;
    }


}
