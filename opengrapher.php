<?php
/**
 * @package OpenGrapher
 */
/*
Plugin Name: Opengrapher
Plugin URI: http://gunnertech.com/2012/02/opengrapher-wordpress-plugin-that-adds-and-tracks-social-sharing
Description: This plugin will help you set default, site-wide and page-specific settings for the OpenGraph Protocol
Version: 0.0.7
Author: Gunner Technology
Author URI: http://gunnertech.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define('OPENGRAPHER_VERSION', '0.0.7');
define('OPENGRAPHER_PLUGIN_URL', plugin_dir_url( __FILE__ ));

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

add_action('wp_head', 'opengrapher_scripts');
add_action('wp_head', 'opengrapher_styles');
add_action('wp_head','opengrahper_print_meta', 11);  

add_filter('the_content', 'opengrapher_the_content', 20); 

add_shortcode('opengrapher_like_button', 'do_opengrapher_like_button');

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';
	

function opengrahper_print_meta() { 
  foreach(opengrapher_properties() as $p): $content = opengrapher_value($p); if($content): ?>
    <?php if(strpos($p,"fb_") === 0): ?>
      <meta property="fb:<?php echo str_replace("fb_","",$p) ?>" content="<?php echo $content ?>" />
    <?php else: ?>
      <?php if($p == 'video'): ?>
        <meta property="og:video" content="<?php echo $content ?>" />
        <?php if(!opengrapher_value('video:height')): ?>
          <meta property="og:video:height" content="360" />
        <?php endif; ?>
        <?php if(!opengrapher_value('video:width')): ?>
          <meta property="og:video:width" content="480" />
        <?php endif; ?>
        <?php if(!opengrapher_value('video:type')): ?>
          <meta property="og:video:type" content="application/x-shockwave-flash" />
        <?php endif; ?>
        <?php if(!opengrapher_value('image',true)): ?>
          <meta property="og:image" content="<?php echo OPENGRAPHER_PLUGIN_URL ?>play.gif" />
        <?php endif; ?>
      <?php else: ?>
        <meta property="og:<?php echo $p ?>" content="<?php echo $content ?>"/>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; endforeach; ?>
<?php }

function opengrapher_properties() {
  return array('title','type','url','image','site_name','description','video','video:height','video:type','video:width','fb_app_id','fb_page_id','fb_admins');
}

function opengrapher_value($property,$even_if_admin=false) {
  
  global $post;
  
  if(is_category()) {
    $category = get_category(get_query_var('cat'));
    switch ($property) {
      case 'title':
        $value = $category->name;
        break;
      case 'type':
        $value = 'article';
        break;
      case 'url':
        $value = get_category_link( $category->term_id );
        break;
      case 'video:width':
        if(opengrapher_value('video',true)) {
          $value = '480';
        }
        break;
      case 'video:height':
        if(opengrapher_value('video',true)) {
          $value = '360';
        }
        break;
      case 'video:type':
        if(opengrapher_value('video',true)) {
          $value = 'application/x-shockwave-flash';
        }
        break;
      case 'image':
        $value = get_option('opengrapher_'.$property);
        break;
      case 'site_name':
        $value = get_bloginfo( 'name' );
        break;
      case 'description':
        $value = substr ( $category->category_description , 0, 200 );
        
        break;
      default:
        return null;
        break;
    }
    return is_string($value) ? strip_shortcodes(strip_tags(preg_replace('/\n/',' ',esc_attr($value)))) : $value;
  }
  
  $opengrapher_meta = opengrahper_get_post_meta();
  
  $value = isset($opengrapher_meta['opengrapher_'.$property]) ? $opengrapher_meta['opengrapher_'.$property] : null;
  
  
  if(!$value && (!is_admin() || $even_if_admin)) {
    if($property == 'image' && isset($post)) {
      $images =& get_children('post_type=attachment&post_mime_type=image&post_parent=' . get_the_ID() );
      if(is_array($images)){
        if($image = array_shift($images)) {
          if($url = wp_get_attachment_thumb_url($image->ID)) {
            $value = $url;
          }
        }
      }
    }
    
    if(!$value) {
      $value = get_option('opengrapher_'.$property);
    }
  }
  
  if(is_admin() && $property == 'image' && !isset($opengrapher_meta['opengrapher_'.$property])) {
    $value = false;
  }
  
  
  if(!$value && (!is_admin() || $even_if_admin)) {
    switch ($property) {
      case 'title':
        $value = get_the_title();
        if(!$value) {
          $value = strip_shortcodes(trim(wp_title( '', false, '' )));
          $value .= " " . strip_shortcodes(trim(get_bloginfo( 'name' )));
          $value = trim($value);
        }
        break;
      case 'type':
        if(!is_home() && !is_front_page()) {
          $value = 'article';
        } else {
          $value = 'website';
        }
        break;
      case 'url':
        $value = get_permalink();
        break;
      case 'video:width':
        if(opengrapher_value('video',true)) {
          $value = '480';
        }
        break;
      case 'video:height':
        if(opengrapher_value('video',true)) {
          $value = '360';
        }
        break;
      case 'video:type':
        if(opengrapher_value('video',true)) {
          $value = 'application/x-shockwave-flash';
        }
        break;
      case 'image':
        if(!$value && !is_admin()) {
          $value = get_option('opengrapher_'.$property);
        }
        break;
      case 'site_name':
        $value = get_bloginfo( 'name' );
        break;
      case 'description':
        if(is_home() || is_front_page()) {
          $value = esc_attr(trim(get_bloginfo( 'description' )));
        } else if(is_singular() && has_excerpt()) {
          $value = esc_attr(trim(get_the_excerpt()));
        } else {
          $value = esc_attr(trim(get_bloginfo( 'description' )));
        }
        
        break;
      default:
        $value = "";
        break;
    }
  }
  
  if($value && ($property == 'image' || $property == 'url') && strpos($value,"http://") === false) {
    $value = site_url().$value;
  }
  
  if($property == 'image' && $value == 'none') {
    $value = false;
  }
  
  
  $value = is_string($value) ? strip_shortcodes(strip_tags(preg_replace('/\n/',' ',esc_attr($value)))) : "";
  
  return $value;
}

function opengrahper_get_post_meta($reload=false) {
  global $wp_query, $post, $opengrapher_meta;
	
	$meta = $opengrapher_meta;
	if($reload) {
	  if($wp_query->queried_object) {
	    $meta = get_post_meta($wp_query->queried_object->ID,'_opengrapher_meta',TRUE);
	  } elseif(is_search()) {
	    $meta = get_post_meta(get_option('page_on_front'),'_opengrapher_meta',TRUE);
	  } else {
	    $meta = get_post_meta($post->ID,'_opengrapher_meta',TRUE);
	  }
	} else {
	  if(isset($opengrapher_meta)) {
	    $meta = $opengrapher_meta;
	  } else {
	    if(isset($wp_query->queried_object) && isset($wp_query->queried_object->ID)) {
  	    $meta =  get_post_meta($wp_query->queried_object->ID,'_opengrapher_meta',TRUE);
  	  } elseif(is_search() || is_category()) {
  	    $meta = get_post_meta(get_option('page_for_posts'),'_opengrapher_meta',TRUE);
  	  } elseif(isset($post->ID)) {
  	    $meta = get_post_meta($post->ID,'_opengrapher_meta',TRUE);
  	  }
	  }
	}
	return $meta;
}


function opengrapher_the_content($content) {
  if(is_front_page() && get_option('opengrapher_display_on_home_page','on') != 'on') {
    return $content;
  }
	if(
		(is_single() && get_option('opengrapher_display_on_individual_posts','on') == 'on') ||
		(is_page() && get_option('opengrapher_display_on_individual_pages','on') == 'on') 
	) {
		if(get_option('opengrapher_display_above_content','on')) {
			$content = opengrapher_get_fbml($content).$content;
		}
		if(get_option('opengrapher_display_below_content','on')) {
			$content .= opengrapher_get_fbml($content);
		}
	}
	return $content;
}

function opengrapher_styles() { ?>
  <style>
    .social-buttons li {
      float: left;
      list-style-type: none;
      margin-left: 10px;
    }
    
    .social-buttons li:first-child {
      margin-left: 0;
    }
    
    .social-buttons li.linked-in-button {
      margin-right: 10px;
    }
    
    .social-buttons:before, .social-buttons:after { content: "\0020"; display: block; height: 0; overflow: hidden; }
    .social-buttons:after { clear: both; }
    .social-buttons { zoom: 1; }
    
  </style>
<?php }

add_action('init', 'opengrapher_add_scripts');

function opengrapher_add_scripts() {
  wp_enqueue_script('jquery');
}

function opengrapher_scripts() {
  echo '
  <script>
    function plusone_vote( obj ) {
      if(typeof _gaq != "undefined") {
        _gaq.push(["_trackEvent","plusone",obj.state]);
      }
      
      if(typeof clicky != "undefined") {
        clicky.log(response,"Google Plus One " + obj.state);
      }
    }
    
    window.fbAsyncInit = function() {
      FB.init({appId: \''.opengrapher_value("fb_app_id").'\', status: true, cookie: true, xfbml: true});
      FB.Event.subscribe("edge.create",function(response) {
        if (response.indexOf("facebook.com") > 0) {
          // if the returned link contains "facebook.com". It is a "Like"
          // for your Facebook page
          if(typeof _gaq != "undefined") {
            _gaq.push(["_trackEvent","Facebook","Like",response]);
          }
          if(typeof clicky != "undefined") {
            clicky.log(response,"Facebook Like Facebook Page");
          }
        } else {
          // else, somebody is sharing the current page on their wall
          if(typeof _gaq != "undefined") {
            _gaq.push(["_trackEvent","Facebook","Share",response]);
          }
          if(typeof clicky != "undefined") {
            clicky.log(response,"Facebook Like / Share Post");
          }
        }
      });
      FB.Event.subscribe("message.send",function(response) {
        if(typeof clicky != "undefined") {
          _gaq.push(["_trackEvent","Facebook","Send",response]);
        }
        clicky.log(response,"Facebook Send Post");
      });
    };
    
    (function() {
      function opengrapher_setup() {
        if(typeof(jQuery) == "undefined") {
          return setTimeout(opengrapher_setup,100);
        }
        
        jQuery(document).ready(function($) {
          opengrapher_setup_facebook();
          opengrapher_setup_twitter();
          opengrapher_setup_plus_one();
          opengrapher_setup_linked_in();
          opengrapher_setup_stumble_upon();
        });
      }
      
      function opengrapher_setup_facebook() {
        var element = document.getElementById("fb-root");
        if(!element) {
          var b = document.getElementsByTagName("body")[0];
          
          element = document.createElement("div");
          element.id = "fb-root";
          b.insertBefore(element,b.firstChild);

          var e = document.createElement(\'script\'); e.async = true;
          e.src = document.location.protocol +
            \'//connect.facebook.net/en_US/all.js\';
          element.appendChild(e);
          
          var html = document.getElementsByTagName("html")[0];
          
          if(!html.getAttribute("xmlns:og")){
            var attr = document.createAttribute("xmlns:og");
            attr.value = "http://ogp.me/ns#";
            html.setAttributeNode(attr);
          }
          
          if(!html.getAttribute("xmlns:fb")){
            var attr = document.createAttribute("xmlns:fb");
            attr.value = "http://www.facebook.com/2008/fbml";
            html.setAttributeNode(attr);
          }
        }
      }
      
      function opengrapher_setup_twitter() {
        // Load Tweet Button Script
        var e = document.createElement("script");
        e.type="text/javascript"; e.async = true;
        e.src = document.location.protocol + "//platform.twitter.com/widgets.js";
        document.getElementsByTagName("head")[0].appendChild(e);
        
        jQuery(e).load(function() {
          function tweetIntentToAnalytics(intent_event) {
            if (intent_event) {
              var label = intent_event.data.tweet_id;
              if(typeof _gaq != "undefined") {
                _gaq.push(["_trackEvent", "twitter_web_intents", intent_event.type, label]);
              }
              
              if(typeof clicky != "undefined") {
                clicky.log(document.location.href,"Twitter "+label);
              }
            }
          }
          
          function followIntentToAnalytics(intent_event) {
            if (intent_event) {
              var label = intent_event.data.user_id + " (" +
        	    intent_event.data.screen_name + ")";
        	    
        	    if(typeof _gaq != "undefined") {
                _gaq.push(["_trackEvent", "twitter_web_intents", intent_event.type, label]);
              }
              
              if(typeof clicky != "undefined") {
                clicky.log(document.location.href,"Twitter "+label);
              }
            }
          }
          
          twttr.events.bind("tweet",    tweetIntentToAnalytics);
          twttr.events.bind("follow",   followIntentToAnalytics);
        });
      }
      
      function opengrapher_setup_plus_one() {
        if(document.location.protocol != "http:") {
          return false;
        }
        var e = document.createElement("script");
        e.type="text/javascript"; e.async = true;
        e.src = document.location.protocol + "//apis.google.com/js/plusone.js";
        document.getElementsByTagName("head")[0].appendChild(e);
      }
      
      function opengrapher_setup_linked_in() {
        var e = document.createElement("script");
        e.type="text/javascript"; e.async = true;
        e.src = document.location.protocol + "//platform.linkedin.com/in.js";
        document.getElementsByTagName("head")[0].appendChild(e);
      }
      
      function opengrapher_setup_stumble_upon() {
        var li = document.createElement("script"); 
        li.type = "text/javascript"; 
        li.async = true; 
        li.src = document.location.protocol + "//platform.stumbleupon.com/1/widgets.js"; 
        
        var s = document.getElementsByTagName("script")[0]; 
        // s.parentNode.insertBefore(li, s); 
        document.getElementsByTagName("head")[0].appendChild(li);
      }
      
      opengrapher_setup();
      
      
      
    }());
  </script>';
}

function opengrapher_get_fbml($content="") {
  $opengrapher_ret = '<ul class="social-buttons custom">';
  if(get_option("opengrapher_display_disable") != 'on') {
    $opengrapher_ret .= '<li class="'.get_option('opengrapher_display_css_class','opengrapher-like-button').'">';
    $opengrapher_ret .= '<fb:like layout='.get_option('opengrapher_display_layout','button_count');
    $opengrapher_ret .= ' show_faces='.(get_option('opengrapher_display_show_faces','false') ? 'true' : 'false');
    //$opengrapher_ret .= ' width='.get_option('opengrapher_display_width','450');
    $opengrapher_ret .= ' action='.get_option('opengrapher_display_verb','like');
    $opengrapher_ret .= ' font='.get_option('opengrapher_display_font','arial');
    $opengrapher_ret .= ' send='.(get_option('opengrapher_display_send',true) ? 'true' : 'false');
    $opengrapher_ret .= ' colorscheme='.get_option('opengrapher_display_color_scheme','light').'></fb:like></li>';
  }
  
  if(get_option("opengrapher_display_twitter_disable") != 'on') {
    $opengrapher_ret .= '<li class="'.get_option('opengrapher_display_css_twitter_class','tweet-button').'"><a href="http://twitter.com/share" data-url="'. get_permalink() .'" ';
    $opengrapher_ret .= 'data-count="'.get_option('opengrapher_display_twitter_count','horizontal').'" data-text="'. get_the_title() .'" data-via="'.get_option('opengrapher_display_twitter_screen_name','gunnertech').'" ';
    $opengrapher_ret .= 'class="twitter-share-button"></a></li>';
  }
  
  if(get_option("opengrapher_display_plus_one_disable") != 'on') {
    $opengrapher_ret .= '<li class="'.get_option('opengrapher_display_css_plus_one_class','plus-one-button').'">';
    $opengrapher_ret .= '<div class="g-plusone" data-size="'.get_option('opengrapher_display_plus_one_size','medium').'" ';
    $opengrapher_ret .= 'data-count="'.(get_option('opengrapher_display_plus_one_count',true) ? 'true' : 'false').'" callback="plusone_vote"></div></li>';
  }
  
  if(get_option("opengrapher_display_linked_in_disable") != 'on') {
    $opengrapher_ret .= '<li class="linked-in-button"><script type="in/share" data-url="'. get_permalink() .'" data-counter="'.get_option('opengrapher_display_linked_in_count_side','right').'"></script></li>';
  }
  
  if(get_option("opengrapher_display_stumble_upon_disable") != 'on') {
    $opengrapher_ret .= '<li class="stumble-upon-button"><su:badge layout="'.get_option('opengrapher_display_stumble_upon_style','1').'" location="'. get_permalink() .'"></su:badge></li>';
  }
  
  $opengrapher_ret .= '</ul>';
	
	return $opengrapher_ret;
}



function do_opengrapher_like_button($attr, $content=null) {		
  extract(shortcode_atts(array(
		'class'	=> get_option('opengrapher_display_css_class','opengrapher-like-button'),
		'layout'	=> get_option('opengrapher_display_layout','layout'),
		'show_faces'	=> (get_option('opengrapher_display_show_faces','false') ? 'true' : 'false'),
		'width'	=> get_option('opengrapher_display_width','450'),
		'action' => get_option('opengrapher_display_verb','like'),
		'font' => get_option('opengrapher_display_font','arial'),
		'colorscheme' => get_option('opengrapher_display_color_scheme','light')
	), $attr));


	$opengrapher_ret = '<div class="'.$class.'">';
	$opengrapher_ret .= '<fb:like layout='.$layout;
	$opengrapher_ret .= ' show_faces='.$show_faces;
  $opengrapher_ret .= ' width='.$width;
  $opengrapher_ret .= ' action='.$action;
  $opengrapher_ret .= ' font='.$font;
  $opengrapher_ret .= ' colorscheme='.$colorscheme.'></fb:like></div>';

	//return $opengrapher_ret;
	return opengrapher_get_fbml();
}
