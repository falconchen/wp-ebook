<?php
add_action('init','create_ebook_post_type_func');

function create_ebook_post_type_func()
{
    $labels = array(
    'name' => _x('Ebooks', 'post type general name', 'your_text_domain'),
    'singular_name' => _x('Ebool', 'post type singular name', 'your_text_domain'),
    'add_new' => _x('Add New', 'course', 'your_text_domain'),
    'add_new_item' => __('Add New Ebook', 'your_text_domain'),
    'edit_item' => __('Edit Ebook', 'your_text_domain'),
    'new_item' => __('New Ebook', 'your_text_domain'),
    'all_items' => __('All Ebook', 'your_text_domain'),
    'view_item' => __('View Ebook', 'your_text_domain'),
    'search_items' => __('Search Ebook', 'your_text_domain'),
    'not_found' =>  __('No Ebook found', 'your_text_domain'),
    'not_found_in_trash' => __('No ebook found in Trash', 'your_text_domain'), 
    'parent_item_colon' => '',
    'menu_name' => __('Ebooks', 'your_text_domain')
    );    
    
     $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' =>true,
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
  ); 
  register_post_type('ebook', $args);
}

add_action('add_meta_boxes','ebook_metabox_func');
add_action('save_post','ebook_save_meatabox_func',1,2);

function ebook_metabox_func()
{
    
    add_meta_box('Ebook_price',' Add Ebook Price','ebook_meta_box_html','ebook','normal','default');
    add_meta_box('Ebook_file','Add Ebook','ebook_file_upload','ebook','normal','default');
}

function ebook_meta_box_html()
{
     wp_nonce_field( basename( __FILE__ ), 'ebook_price_field' );
    global $post;
    $ebook_price = get_post_meta($post->ID,'_ebook_price',true);
    
  ?>
<label>Add Ebook Price:</label><input type="text" name="ebook_price" value="<?= $ebook_price;?>" />
  <?php
}

function ebook_file_upload()
{
    wp_nonce_field( basename( __FILE__ ), 'ebook_url_field' );
    global $post;
    $ebook = get_post_meta($post->ID,'_ebook_url',true);
    
    
  ?>
    <input type="file" name="ebook_file" />
    <table class="widefat">
     <thead>   
      <tr>  
        <th>Sl No.</th>
        <th>Ebook</th>
      </tr>
     </thead>
      <tr>
          <td>1</td>
          <td><a target="_blank" href="<?= $ebook;?>"><?= basename($ebook);?></a></td>
      </tr>
      
    </table>
  <?php
}

function ebook_save_meatabox_func($post_id,$post)
{
    $ebook_price = $_POST['ebook_price'];
    $ebook_file = $_FILES['ebook_file'];
    
    if ( !isset( $_POST['ebook_price_field'] ) || !wp_verify_nonce( $_POST['ebook_price_field'], basename( __FILE__ ) ) )
            return $post_id;
    
    if ( !isset( $_POST['ebook_url_field'] ) || !wp_verify_nonce( $_POST['ebook_url_field'], basename( __FILE__ ) ) )
            return $post_id;
    
    $ebook = get_post_meta($post->ID,'_ebook_url',true);
    
    if($ebook_file['name']!= '')
    {
      $ebook =  custom_file_upload($ebook_file);
    }
    
    update_post_meta($post->ID,'_ebook_price',$ebook_price);
    update_post_meta($post->ID,'_ebook_url',$ebook);
    
    
    
}
?>