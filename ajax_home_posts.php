<?php
/*
Plugin Name: DBC Home Page Posts
Description: Retrieves all posts for home page content on Eardish.com.
Author: Mike Rayter
License: GPL2 intellectual property rights license
*/

global $wpdb;

function get_homepage_posts(){

$type = $_POST['type'];
$cat = '';

define('WP_USE_THEMES', false);

        $postType = 'post';
        switch($type){
            case 'allBtn':
                $cat = "";
                break;
            case 'newsBtn':
                $cat = "news";
                break;
            case 'videoBtn':
                $cat = "video";
                break;
            case 'imagesBtn':
                $cat = "";                            
                break;
        }
        
            $data = array();
            $ttlRes = array();
            $list = array(
                    'category_name'=>$cat,
                    'post_type'=>$postType,
                    'order'=>'ASC',
                    'posts_per_page'=>-1,
                    'category__not_in'=>array(1,24,25,20,21,22,23)
                    );
            
            
            if($type == 'imagesBtn'){                
                $list['meta_key'] = '_thumbnail_id';
            }
            
            $loop = new WP_Query($list);
            
            while( $loop->have_posts() ) : $loop->the_post();
              $data['isVid'] = 0;
             if($type != 'imagesBtn'){    
                $cat = get_the_category($loop->post->ID);    
                
                $data['isVid'] = $cat[0]->name == 'video' ? 1 : 0;        
             }
                
                $data['date'] = get_the_date();
                $data['title'] =  get_the_title();
                $data['excerpt'] = get_the_excerpt();
                $data['content'] = get_the_content();
                $data['image'] = wp_get_attachment_url(get_post_thumbnail_id($loop->post->ID));
                    
                $data['isImg'] = $type == 'imagesBtn' ? 1 : 0;                
                $ttlRes[] = $data;                    
            endwhile;
                $ttlHtml = array();
                foreach($ttlRes as $val){                        
                            $date = $val['date'];
                            $title = $val['title'];
                            $excerpt = $val['excerpt'];
                            $image = $val['image'];
                            $isVid = $val['isVid'];
                            $isImg = $val['isImg'];
                            $content = $val['content'];
                            
                            if($isImg){        
                                $html = '<a href="'.$image.'" rel="prettyPhoto" class="linkBox" border="0" title="'.$title.'"><img src="'.$image.'" width="184" height="162"></a>';                                
                            }else{
                                $excerpt = $isVid ? '<a href='.$content.' rel="prettyPhoto" border="0" title="'.$title.'">'.$title.'</a>' : $excerpt;    
                                $html = '<div class="blogListBox"><div class="blogListTxt"><div class="blogDate">';
                                $html .=$date.'</div>';
                                $html .='<div class="blogListTitle">';
                                $html .='<span class="blogTtl">'.$title.'</span>';
                                $html .='</div><div class="blogExcerpt">'.$excerpt.'</div>';
                                $html .='</div><div class="blogListImg"><img src="'.$image.'" width="184" height="162"></div></div>';
                            }
                            $ttlHtml[] = $html;
                        }
                
                
                
                
            if(count($ttlHtml)){
                echo json_encode($ttlHtml);
                die();
            }else{
                echo 'none';
            }
}

/**
* action hook for wp-e-commerce to provide our own AJAX cart updates
*/
function theme_cart_update($txt) {
    $cart_count = wpsc_cart_item_count();
    $total = wpsc_cart_item_quantity(); //wpsc_cart_total_widget();
        
    echo <<<HTML
        
        pid = jQuery('input[name="pidSelected"]').val();        
        varAdded = jQuery('#'+pid).prev().find('.added');        
        jQuery(varAdded).fadeIn(500).delay(1000).fadeOut(2000);    
        jQuery("#theme-checkout-count").html("$cart_count");
        jQuery('.variation').each(function(){
        jQuery(this).val('');
        });        
        
            
        
HTML;
}

function theme_cart_update2(){
    $prodid = get_latest_prodid();
    
}

function get_latest_prodid(){
    $res = $wpdb->get_results("SELECT prodid FROM wp_wpsc_cart_contents ORDER BY id DESC LIMIT 1");
    return $res[0]->prodid;
}

function get_cart_item($arr) {
     echo $arr['purchase_id'];    
} 
 
add_action('wpsc_alternate_cart_html', 'theme_cart_update');


add_action( 'wp_ajax_nopriv_dbc_getPosts','get_homepage_posts');
add_action('wp_ajax_dbc_getPosts','get_homepage_posts');
