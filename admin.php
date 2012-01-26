<?php

function opengrapher_admin_init() {
  global $wp_version;
  
  // all admin functions are disabled in old versions
  if ( !function_exists('is_multisite') && version_compare( $wp_version, '3.0', '<' ) ) {
      
      function opengrapher_version_warning() {
          echo "
          <div id='opengrapher-warning' class='updated fade'><p><strong>".sprintf(__('OpenGrapher %s requires WordPress 3.0 or higher.'), AKISMET_VERSION) ."</strong> ".sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version, or <a href="%s">downgrade to version 2.4 of the OpenGrapher plugin</a>.'), 'http://codex.wordpress.org/Upgrading_WordPress', 'http://wordpress.org/extend/plugins/akismet/download/'). "</p></div>
          ";
      }
      add_action('admin_notices', 'opengrapher_version_warning'); 
      
      return; 
  }
  
  $custom_types = get_post_types();
  foreach ($custom_types as $type) {
	  add_meta_box('opengrahper-box', __('OpenGrapher Settings'), 'opengrapher_meta_box', $type, 'normal');
  }
  
	wp_register_style('opengrapher.css', OPENGRAPHER_PLUGIN_URL . 'opengrapher.css');
	wp_enqueue_style('opengrapher.css');
	wp_register_script('opengrapher.js', OPENGRAPHER_PLUGIN_URL . 'opengrapher.js', array('jquery'));
	wp_enqueue_script('opengrapher.js');
	
	opengrapher_register_settings();
	add_action('save_post','opengrapher_meta_save');
}
add_action('admin_menu', 'opengrapher_create_settings_menu',12);
add_action('admin_init', 'opengrapher_admin_init');


function opengrapher_create_settings_menu() {
  add_menu_page('OpenGrapher Settings', 'OpenGrapher', 'delete_posts', "opengrapher-settings", 'opengrapher_settings_page',OPENGRAPHER_PLUGIN_URL.'opengrapher.png');
  add_submenu_page('opengrapher-settings','Display Settings', 'Display', 'delete_posts', 'opengrapher-settings-display', 'opengrapher_display_page');
}

function opengrapher_meta_box($content_obj) {
  include_once dirname( __FILE__ ) . '/meta.php';
	
	echo '<input type="hidden" name="opengrapher_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
}

function opengrapher_register_settings() {
  foreach(opengrapher_properties() as $p) {
    register_setting( 'opengrapher-settings-group', "opengrapher_".$p );
  }
  
  foreach(
    array(
      'above_content','below_content','on_individual_posts','on_individual_pages','on_home_page',
      'show_faces','layout','color_scheme','verb','font','width','css_class','send','disable',
      'twitter_count', 'twitter_screen_name','twitter_disable',
      'plus_one_size','plus_one_count','plus_one_disable',
      'linked_in_count_side','linked_in_disable',
      'stumble_upon_style','stumble_upon_disable'
    ) as $d) {
    register_setting( 'opengrapher-display-group', "opengrapher_display_".$d );  
  }
}

function opengrapher_display_page() { ?>
  <div class="wrap">
    <h2>OpenGrapher Options</h2>
    <p>This page lets you set global options for your site's OpenGraph options.</p>
    <form method="post" action="options.php" class="opengrapher-options">
      <?php settings_fields( 'opengrapher-display-group' ); ?>
      <h3>General</h3>
      <p>
        <label for="opengrapher_display_above_content">Display Above Content</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_above_content','on')=='on') ?> name="opengrapher_display_above_content" />
      </p>
      <p>
        <label for="opengrapher_display_below_content">Display Below Content</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_below_content','on')=='on') ?> name="opengrapher_display_below_content" />
      </p>
      <p>
        <label for="opengrapher_display_on_individual_posts">Display on Individual Posts</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_on_individual_posts','on')=='on') ?> name="opengrapher_display_on_individual_posts" />
      </p>
      <p>
        <label for="opengrapher_display_on_individual_pages">Display on Individual Pages</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_on_individual_pages','on')=='on') ?> name="opengrapher_display_on_individual_pages" />
      </p>
      <p>
        <label for="opengrapher_display_on_home_page">Display on Home Page</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_on_home_page','on')=='on') ?> name="opengrapher_display_on_home_page" />
      </p>
      
      <h3>Facebook</h3>
      <p>For more information, please <a href="http://developers.facebook.com/docs/reference/plugins/like/">see the official Facebook button page</a>.</p>
      <p>
        <label for="opengrapher_display_disable">Disable</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_disable') == 'on') ?> name="opengrapher_display_disable" />
      </p>
      <p>
        <label for="opengrapher_display_show_faces">Show Faces</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_show_faces') == 'on') ?> name="opengrapher_display_show_faces" />
      </p>
      <p>
        <label for="opengrapher_display_send">Show Send Button</label>
        <input type="checkbox" value="true" <?php checked(get_option('opengrapher_display_send',true) == 'true') ?> name="opengrapher_display_send" />
      </p>
      <p>
        <label for="opengrapher_display_layout">Layout</label>
        <select name="opengrapher_display_layout">
          <option value="box_count" <?php selected(get_option("opengrapher_display_layout")=='box_count') ?>>Box Count</option>
          <option value="standard" <?php selected(get_option("opengrapher_display_layout")=='standard') ?>>Standard</option>
          <option value="button_count" <?php selected(get_option("opengrapher_display_layout")=='button_count') ?>>Button Count</option>
        </select>
      </p>
      <p>
        <label for="opengrapher_display_color_scheme">Color Scheme</label>
        <select name="opengrapher_display_color_scheme">
          <option value="light" <?php selected(get_option("opengrapher_display_color_scheme")=='light') ?>>Light</option>
          <option value="dark" <?php selected(get_option("opengrapher_display_color_scheme")=='dark') ?>>Dark</option>
        </select>
      </p>
      <p>
        <label for="opengrapher_display_verb">Verb to Display</label>
        <select name="opengrapher_display_verb">
          <option value="like" <?php selected(get_option("opengrapher_display_verb")=='like') ?>>Like</option>
          <option value="recommend" <?php selected(get_option("opengrapher_display_verb")=='recommend') ?>>Recommend</option>
        </select>
      </p>
      <p>
        <label for="opengrapher_display_font">Font</label>
        <select name="opengrapher_display_font">
          <?php foreach(array('arial','lucida grande','segoe ui','tahoma','trebuchet ms','verdana') as $font): ?>
            <option value="<?php echo $font ?>" <?php selected(get_option("opengrapher_display_font")==$font) ?>><?php echo $font ?></option>
          <?php endforeach; ?>
        </select>
      </p>
      <!--p>
        <label for="opengrapher_display_width">Width</label>
        <input type="text" value="<?php echo get_option("opengrapher_display_width","450") ?>" name="opengrapher_display_width" />
      </p>
      <p>
        <label for="opengrapher_display_css_class">CSS Class</label>
        <input type="text" value="<?php echo get_option("opengrapher_display_css_class","opengrapher-like-button") ?>" name="opengrapher_display_css_class" />
      </p-->
      
      <h3>Twitter</h3>
      <p>For more information, please <a href="http://twitter.com/about/resources/tweetbutton">see the official Twitter button page</a>.</p> 
      <p>
        <label for="opengrapher_display_twitter_disable">Disable</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_twitter_disable') == 'on') ?> name="opengrapher_display_twitter_disable" />
      </p>
      <p>
        <label for="opengrapher_display_twitter_screen_name">Twitter Screen Name</label>
        <input type="text" value="<?php echo get_option('opengrapher_display_twitter_screen_name','gunnertech') ?>" name="opengrapher_display_twitter_screen_name" />
      </p>
      <p>
        <label for="opengrapher_display_twitter_count">Layout</label>
        <select name="opengrapher_display_twitter_count">
          <option value="horizontal" <?php selected(get_option("opengrapher_display_twitter_count")=='horizontal') ?>>Horizontal</option>
          <option value="vertical" <?php selected(get_option("opengrapher_display_twitter_count")=='vertical') ?>>Vertical</option>
          <option value="none" <?php selected(get_option("opengrapher_display_twitter_count")=='none') ?>>None</option>
        </select>
      </p>
      
      <h3>Plus One</h3>
      <p>For more information, please <a href="http://code.google.com/apis/+1button/">see the official Plus One button page</a>.</p>
      <p>
        <label for="opengrapher_display_plus_one_disable">Disable</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_plus_one_disable') == 'on') ?> name="opengrapher_display_plus_one_disable" />
      </p>
      <p>
        <label for="opengrapher_display_plus_one_count">Show Count</label>
        <input type="checkbox" value="true" <?php checked(get_option('opengrapher_display_plus_one_count',true) == 'true') ?> name="opengrapher_display_plus_one_count" />
      </p>
      <p>
        <label for="opengrapher_display_plus_one_size">Layout</label>
        <select name="opengrapher_display_plus_one_size">
          <option value="medium" <?php selected(get_option("opengrapher_display_plus_one_size")=='medium') ?>>Medium</option>
          <option value="small" <?php selected(get_option("opengrapher_display_plus_one_size")=='small') ?>>Small</option>
          <option value="standard" <?php selected(get_option("opengrapher_display_plus_one_size")=='standard') ?>>Standard</option>
          <option value="tall" <?php selected(get_option("opengrapher_display_plus_one_size")=='tall') ?>>Tall</option>
        </select>
      </p>
      
      <h3>LinkedIn</h3>
      <p>For more information, please <a href="http://www.stumbleupon.com/badges/">see the official StumbleUpon button page</a>.</p>
      <p>
        <label for="opengrapher_display_linked_in_disable">Disable</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_linked_in_disable') == 'on') ?> name="opengrapher_display_linked_in_disable" />
      </p>
      <p>
        <label for="opengrapher_display_linked_in_count_side">Layout</label>
        <select name="opengrapher_display_linked_in_count_side">
          <option value="right" <?php selected(get_option("opengrapher_display_linked_in_count_side")=='right') ?>>Right</option>
          <option value="top" <?php selected(get_option("opengrapher_display_linked_in_count_side")=='top') ?>>Top</option>
          <option value="" <?php selected(get_option("opengrapher_display_plus_one_size")==='') ?>>None</option>
        </select>
      </p>
      
      <h3>StumbleUpon</h3>
      <p>For more information, please <a href="http://www.stumbleupon.com/badges/">see the official StumbleUpon button page</a>.</p>
      <p>
        <label for="opengrapher_display_stumble_upon_disable">Disable</label>
        <input type="checkbox" <?php checked(get_option('opengrapher_display_stumble_upon_disable') == 'on') ?> name="opengrapher_display_stumble_upon_disable" />
      </p>
      <p>
        <label for="opengrapher_display_stumble_upon_style">Layout</label>
        <select name="opengrapher_display_stumble_upon_style">
          <option value="1" <?php selected(get_option("opengrapher_display_stumble_upon_style")=='1') ?>>Right Square Border</option>
          <option value="2" <?php selected(get_option("opengrapher_display_stumble_upon_style")=='2') ?>>Right Round Border</option>
          <option value="3" <?php selected(get_option("opengrapher_display_stumble_upon_style")=='3') ?>>Right No Border</option>
          <option value="4" <?php selected(get_option("opengrapher_display_stumble_upon_style")=='4') ?>>None Small</option>
          <option value="5" <?php selected(get_option("opengrapher_display_stumble_upon_style")=='5') ?>>Bottom Big</option>
          <option value="6" <?php selected(get_option("opengrapher_display_stumble_upon_style")=='6') ?>>None Big</option>
        </select>
      </p>
      
      <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
    </form>
  </div>
<?php }

function opengrapher_settings_page() {  ?>
<div class="wrap">
  <h2>OpenGrapher Options</h2>
  <h3>(Facebook Only)</h3>
  <p>This page lets you set global options for your site's OpenGraph options.</p>
  <p>You will be able to override any of these settings inside each Post or Page edit screen.</p>
  <p>Next to each is a global default, which will be set if you leave the field blank.</p>
  <p>In order for OpenGraph to work, you must set the admins (separate each admin id with a comma), page_id or app_id.</p>
  <form method="post" action="options.php" class="opengrapher-options">
    <?php settings_fields( 'opengrapher-settings-group' ); ?>
    <?php foreach(opengrapher_properties() as $p): $content = get_option("opengrapher_".$p); ?>
      <h3 class="hndle">
        Open Graph <?php echo ucwords(str_replace("_"," ",$p)) ?>
        <?php if(!$content && $p != 'image' && $d = opengrapher_value($p,true)): ?>
          <small> (Will default to: <?php echo $d ?>) </small>
        <?php endif; ?>
      </h3>
      <?php if($p == 'image'): ?>
        <input class="upload_image_value the-value" type="text" size="36" name="opengrapher_<?php echo $p ?>" value="<?php echo get_option("opengrapher_".$p) ?>" /> 
        <input class="upload_image_button" type="button" value="Upload Image" /> Enter a URL or Click "Upload an Image"
      <?php else: ?>
        <input style="width:100%;" type="text" name="opengrapher_<?php echo $p ?>" value="<?php echo get_option("opengrapher_".$p) ?>" />
      <?php endif; ?>
    <?php endforeach; ?>
    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
  </form>
</div>
<?php }


function opengrapher_meta_save($post_id) {
  if (!isset($_POST['opengrapher_meta_noncename'])) return $post_id;
	if (!wp_verify_nonce($_POST['opengrapher_meta_noncename'],__FILE__)) return $post_id;
	
	// check user permissions
	if ($_POST['post_type'] == 'page') {
		if (!current_user_can('edit_page', $post_id)) return $post_id;
	} else  {
		if (!current_user_can('edit_post', $post_id)) return $post_id;
	} 
	$current_data = get_post_meta($post_id, '_opengrapher_meta', TRUE);	
  
	$new_data = $_POST['_opengrapher_meta'];
	
	opengrapher_meta_clean($new_data);
 
	if ($current_data) {
		if (is_null($new_data)) delete_post_meta($post_id,'_opengrapher_meta');
		else update_post_meta($post_id,'_opengrapher_meta',$new_data);
	} elseif (!is_null($new_data)) {
		add_post_meta($post_id,'_opengrapher_meta',$new_data,TRUE);
	}
 
	return $post_id;
}

function opengrapher_meta_clean(&$arr) {
	if (is_array($arr)) {
		foreach ($arr as $i => $v) {
			if (is_array($arr[$i])) {
				opengrapher_meta_clean($arr[$i]);
 
				if (!count($arr[$i])) {
					unset($arr[$i]);
				}
			} else {
				if (trim($arr[$i]) == '') {
					unset($arr[$i]);
				} else {
				  $arr[$i] = opengrapher_sanitize_data($arr[$i]);
				}
			}
		}
 
		if (!count($arr)) {
			$arr = NULL;
		}
	}
}

function opengrapher_sanitize_data($test_value) {
  $siteurl = str_replace("/",'\/',preg_quote(get_option('siteurl')));
  $pattern = '/'.$siteurl.'(.+)/';
  $new_value = $test_value;
  if(is_array($test_value)) {
    foreach($test_value as $key => $value) {
      if(is_string($value) && preg_match($pattern, $value)) {
        $new_value[$key] = preg_replace($pattern,'\\1',$value);
      }
    }
  } else if($test_value && is_string($test_value) && preg_match($pattern, $test_value)) {
    $new_value = preg_replace($pattern,'\\1',$test_value);
  }
  
  return $new_value;
}

?>