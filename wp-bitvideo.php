<?php
/*
* Plugin Name: BitVideo
*
* Description: Share More.
*
* Author: bitvideo
* Author URI: https://www.bitwall.io
* Plugin URI: https://www.bitwall.io
* Version: 0.1
*/



/*******************************************************************************
** bitvideoPageMenu()
**
** Setup the plugin options menu
**
** @since 0.1
*******************************************************************************/
function bitvideoMenu() {
    if (is_admin()) {
        register_setting('bitvideo_options', 'bitvideo_options');
    }
}


/*******************************************************************************
** checkbox_init()
**
** Adds a metabox to post, page, and event
**
** @since 0.1
********************************************************************************/
function bv_checkbox_init(){
    $post_types = array ( 'post', 'page', 'event' );

    foreach($post_types as $post_type)
    {
        add_meta_box("bitvideo", "bitvideo", "bitvideo", $post_type, "normal", "high");
    }
}

function bitvideo(){
    global $post;
    $custom = get_post_custom($post->ID);
    $bv_field_id = $custom["bv_field_id"][0];
    ?>

    <label>Add bitvideo Paywall</label>
    <?php 
        $bv_field_id_value = get_post_meta($post->ID, 'bv_field_id', true);
        $bv_field_message = get_post_meta($post->ID, 'bv_field_message', true);
        if($bv_field_id_value == "yes") $bv_field_id_checked = 'checked="checked"'; 
    ?>
    <input type="checkbox" name="bv_field_id" value="yes" <?php echo $bv_field_id_checked; ?> />
    <br>
    <br>
    <label>Share Message</label>
    <input type="text" name="bv_field_message" value="<?php echo $bv_field_message; ?>" />

    <?php

}

// Save Meta Details
function bv_save_details(){
    global $post;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post->ID;
    }

    update_post_meta($post->ID, "bv_field_message", $_POST["bv_field_message"]);
    update_post_meta($post->ID, "bv_field_id", $_POST["bv_field_id"]);
}




/*******************************************************************************
** the_posts()
**
** Add bitvideo to footer of tagged pages
**
** @since 0.1
*******************************************************************************/

function bv_the_posts($posts) {
    add_action('wp_footer', 'bv_add_to_footer'); 
    add_action('wp_head', 'bv_add_to_head');
    return $posts; 
}

function bv_add_to_head(){
    global $posts; 
    
    
    $ray = array(); 
    foreach ($posts as $p) {
        $ray[] = $p->ID;
    }
    
    if (count($ray) < 1) {
        return; 
    }
    $ids = implode(',',$ray);
    
    $myPosts = get_posts(array(
        'post_type' => 'any',
        'post__in' => $ray,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'field_id',
                'value' => '',
                'compare' => '!='
            )
        )
    ));
    
    if (empty($myPosts)) {
        return; 
    }

    if (is_front_page()){
        return;
    }   


    foreach ($posts as $p) {

        $my_post_content = apply_filters( 'the_content', $p->post_content );
        
        $my_title = get_the_title($p->ID);

        $my_excerpt = wp_trim_words( $my_post_content, 190, '...' );


        $pattern = '#(?<=(?:v|i)=)[a-zA-Z0-9-]+(?=&)|(?<=(?:v|i)\/)[^&\n]+|(?<=embed\/)[^"&\n]+|(?<=‌​(?:v|i)=)[^&\n]+|(?<=youtu.be\/)[^&\n]+#';

        preg_match_all($pattern, $my_post_content, $matches);

        foreach ($matches as $match) {
            if(!empty($match[0])){
                $search = array('?feature=oembed', '?rel=0');
                $img = 'http://img.youtube.com/vi/'.str_replace($search,'', $match[0]).'/0.jpg';
                echo '<meta property="og:image" content="'.$img.'">';
                echo '<meta property="og:title" content="'.$my_title.'">';
                echo '<meta name="twitter:card" content="summary_large_image">';
                echo '<meta name="twitter:image:src" content="'.$img.'">';
                echo '<meta name="twitter:title" content="'.$my_title.'">';
                echo '<meta name="twitter:description" content="'.$my_excerpt.'">';
            }
            break;
        }

    }

}


function bv_add_to_footer() {
    global $posts; 
    
    
    $ray = array(); 
    foreach ($posts as $p) {
        $ray[] = $p->ID;
    }
    
    if (count($ray) < 1) {
        return; 
    }
    $ids = implode(',',$ray);
    
    $myPosts = get_posts(array(
        'post_type' => 'any',
        'post__in' => $ray,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'field_id',
                'value' => '',
                'compare' => '!='
            )
        )
    ));
    
    if (empty($myPosts)) {
        return; 
    }

    if (is_front_page()){
        return;
    }   


    foreach ($posts as $p) {

        $field_message = get_post_meta($p->ID, 'field_message', true);
        if($field_message){
            echo '<script src="//dpq2jbebhl4ml.cloudfront.net/assets/javascripts/videoWidget_1.8.js" id="bitwallVideoScript" data-message="'.$field_message.'"></script>';
        } else {
            echo '<script src="//dpq2jbebhl4ml.cloudfront.net/assets/javascripts/videoWidget_1.8.js" id="bitwallVideoScript" data-message="'.get_the_title($p->ID).'"></script>';

        }
    }
}



/*******************************************************************************
** initbitvideo()
**
** Constructor
**
** @since 0.1
*******************************************************************************/
function initbitvideo() {
    add_action('admin_menu', 'bitvideoMenu');
    add_action("admin_init", "bv_checkbox_init");
    add_action('save_post', 'bv_save_post');
    add_action('save_post', 'bv_save_details');
    add_action('the_posts', 'bv_the_posts');    
}

add_action('init', 'initbitvideo', 1);



?>