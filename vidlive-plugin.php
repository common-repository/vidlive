<?php
/**
* Plugin Name: VidLive
* Description: Easily add your VidLive widgets in WordPress. VidLive is the easiest way to automatically stream “Currently Live" Facebook video from your website. Add our embed code once and you are done!
* Version: 1.1
* Author: VidLive
* Author URI: https://vidlive.co
**/

defined( 'ABSPATH' ) or die();
include 'vidlive-widget.php';

const VIDLIVE_BASE = 'https://vidlive.co';

const VIDLIVE_API_EDIT_URL = VIDLIVE_BASE . '/widgets/{id}/edit';
const VIDLIVE_API_URL = VIDLIVE_BASE . '/api/get_widgets';
const VIDLIVE_JS_URL = VIDLIVE_BASE . '/embed/{id}/embed.js';
const VIDLIVE_IFRAME_URL = VIDLIVE_BASE . '/iframe/{id}';
const VIDLIVE_YT_JS_URL = VIDLIVE_BASE . '/yt-embed/{id}/embed.js';
const VIDLIVE_YT_IFRAME_URL = VIDLIVE_BASE . '/yt-iframe/{id}';

function vidlive_get_widgets( $key ) {
	
	$request = wp_remote_get( VIDLIVE_API_URL . '?api_key=' . $key );
	$results = json_decode( wp_remote_retrieve_body( $request ) );

	if(empty($results)) {
		return false;
	} else {

		$cache = array();

		foreach($results as $widget) {
			$cache[$widget->id] = $widget->provider;
		}

		update_option( '_vidlive_widgets', $cache );
		return $results;
	}
	
}

function vidlive_plugin_settings() { 

	if($_POST) {
		
		if(empty($_POST['vidlive_api_key']) || !vidlive_get_widgets( sanitize_text_field($_POST['vidlive_api_key']))) {
			$errors['vidlive_api_key'] = 'API key invalid. Please enter a valid API key.';
		} else {
			$messages['vidlive_api_key'] = 'Success! You are connected to your VidLive account.';
			update_option('vidlive_api_key', sanitize_text_field($_POST['vidlive_api_key']));
		}
		
		if(in_array($_POST['vidlive_embedding'], array('iframe', 'javascript'))) {
			update_option('vidlive_embedding', sanitize_text_field($_POST['vidlive_embedding']));	
		} else {
			update_option('vidlive_embedding', 'javascript');	
		}
		
	}
	
	if(isset($_GET['dismiss']) && sanitize_key($_GET['dismiss']) == 'true') {
		update_option('vidlive_show_panel', true);
	}

?>

<div class="wrap">
	<h1>VidLive Plugin</h1>
	
	<nav class="nav-tab-wrapper">
	  <?php if(get_option('vidlive_api_key') && vidlive_get_widgets( sanitize_text_field ( get_option('vidlive_api_key') ) )  || isset($messages['vidlive_api_key'])) { ?>
	  <a href="?page=vidlive-plugin-settings" class="nav-tab <?php echo (sanitize_key($_GET['tab']) == '' || isset($messages['vidlive_api_key'])) ? ' nav-tab-active' : ''; ?>">Widgets</a>
	  <?php } ?>
	  <a href="?page=vidlive-plugin-settings&tab=settings" class="nav-tab <?php echo (sanitize_key($_GET['tab']) == 'settings' && !isset($messages['vidlive_api_key']) || !empty(get_option('vidlive_api_key')) && !vidlive_get_widgets( sanitize_text_field ( get_option('vidlive_api_key' ) ) ) || empty(get_option('vidlive_api_key'))) ? ' nav-tab-active' : ''; ?>">Settings</a>
	</nav>
	
	<?php echo isset($errors['vidlive_api_key']) ? '<div class="error notice"><p>' . $errors['vidlive_api_key'] . '</p></div>' : ''; ?>
	<?php echo isset($messages['vidlive_api_key']) ? '<div class="updated notice"><p>' . $messages['vidlive_api_key'] . '</p></div>' : ''; ?>
	
	<div class="tab-content">

	<?php if(sanitize_key($_GET['tab']) == '' && get_option('vidlive_api_key') && vidlive_get_widgets( sanitize_text_field ( get_option('vidlive_api_key') ) ) || isset($messages['vidlive_api_key'])) { $widgets = vidlive_get_widgets( sanitize_text_field ( get_option('vidlive_api_key') ) ); ?>

	<br />
	<table class="wp-list-table widefat fixed striped forms">
	    <thead>
	        <tr>
	            <th scope="col" id="form_name" class="manage-column column-form_name column-primary"><span>Name</span></th>
	            <th scope="col" id="shortcode" class="manage-column column-shortcode">Shortcode</th>
	        </tr>
	    </thead>
	
	    <tbody id="the-list" data-wp-lists="list:form">
		    <?php if(count($widgets) > 0) { ?>
		    <?php foreach($widgets as $widget) { ?>
	        <tr>
	            <td class="form_name column-form_name column-primary" data-colname="Name"><a target="_blank" href="<?php echo str_replace('{id}', $widget->id, VIDLIVE_API_EDIT_URL); ?>"><strong><?php echo $widget->widget_name; ?></strong></a>
	</td>
	            <td class="shortcode column-shortcode"
	                data-colname="Shortcode"><input type="text" value="[vidlive id=&quot;<?php echo $widget->id; ?>&quot;]"></td>
	
	        </tr>
	        <?php } } ?>
	
	    </tbody>
	</table>
	
	<?php } else {  ?>

	<form method="POST">
	<table class="form-table" role="presentation">
	
	<tbody>

		<?php if(!get_option('vidlive_show_panel')) { ?>
		<div class="vidlive-panel">
			<div style="padding: 20px;">
				<a class="close-btn" href="<?php echo $_SERVER['REQUEST_URI'] . '&dismiss=true'; ?>">Dismiss</a>
				<h3>VidLive for WordPress</h3>
				<p>If you have not already done so, please head over to <a href="https://www.vidlive.co/" target="_blank">VidLive.co</a> to register and get your API Key. Once you have registered you can generate your API key by going to your <a href="https://www.vidlive.co/account/profile" target="_blank">Account -> Profile page</a>.
				<br />
				<br />
				Simply enter your API key below to connect WordPess to your VidLive account. Try VidLive Free for 7 days!</p>
			</div>
		</div>		
		<?php } ?>
		
		<tr>
		<th scope="row"><label for="vidlive_api_key">API Key</label></th>
		<td><input name="vidlive_api_key" type="text" id="vidlive_api_key" class="regular-text" value="<?php echo esc_attr( get_option('vidlive_api_key') ); ?>"></td>
		</tr>
	<tr>
	<th scope="row"><label for="vidlive_embedding">Embedding Type</label></th>
		<td>
		<select name="vidlive_embedding" id="vidlive_embedding">
			<option value="iframe" <?php selected( esc_attr ( get_option('vidlive_embedding', 'javascript') ), 'iframe' ); ?>>iFrame</option>
			<option value="javascript" <?php selected( esc_attr ( get_option('vidlive_embedding', 'javascript' ) ), 'javascript' ); ?>>JavaScript (default)</option>
		</select>
		<p class="description">This option should only be changed if you are having issues with the current WordPress theme.</p>
		</td>
	</tr>
	
	</tbody>
	</table>

	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</form>
	
	<?php } ?>

	</div>

</div>

<?php
	
}

function register_vidlive_menu() {
	add_menu_page( 'VidLive', 'VidLive', 'manage_options', 'vidlive-plugin-settings', 'vidlive_plugin_settings', 'dashicons-controls-play' );
}

function vidlive_shortcode( $parameters ){
	if(!isset($parameters['id'])) {
		return "Please copy the shortcode again from the VidLive Widgets Tab.";
	} else {
		return vidlive_generate_embed_code($parameters['id']);
	}
}

function vidlive_register_widget() {
    register_widget( 'Vidlive_Widget' );
}

function vidlive_generate_embed_code( $id ) {

	$id = intval($id);

	if(get_option('_vidlive_widgets') === false) {
		if(get_option('vidlive_api_key')) {
			vidlive_get_widgets( sanitize_text_field ( get_option('vidlive_api_key') ) );
		}		
	}

	$provider = get_option('_vidlive_widgets')[$id];

	$providers = array(
		'facebook'=> array(
			'id'=> 'vidlive-embed-' . $id, 
			'iframe_url'=> VIDLIVE_IFRAME_URL, 
			'js_url'=> VIDLIVE_JS_URL
		),
		'youtube'=> array(
			'id'=> 'vidlive-yt-embed-' . $id, 
			'iframe_url'=> VIDLIVE_YT_IFRAME_URL, 
			'js_url'=> VIDLIVE_YT_JS_URL
		)
	);

	if(get_option('_vidlive_widgets') === false || !array_key_exists($provider, $providers)) {
		$provider = 'facebook';
	}

	if(get_option('vidlive_embedding', 'javascript') == 'iframe') {
		return '<iframe id="' . $providers[$provider]['id'] . '" src="' . str_replace('{id}', $id, $providers[$provider]['iframe_url']) . '" style="width:100%;height:100%;border:none;overflow: hidden;" allowTransparency="true" allowfullscreen="true"></iframe><script>window.addEventListener(\'message\', function(e) { if(e.data[0] == \'setHeight-' . $id . '\') { document.getElementById(\'' . $providers[$provider]['id'] . '\').style.height = e.data[1] + "px"; } }, false);</script>';
	} else {
		wp_enqueue_script($providers[$provider]['id'], str_replace('{id}', $id, $providers[$provider]['js_url']));
		return '<div id="'. $providers[$provider]['id'] . '"></div>';
	}
}

function vidlive_activation_hook() {
	delete_option('vidlive_show_panel');
}

function vidlive_admin_style() {
  wp_enqueue_style('admin-styles', plugin_dir_url( __FILE__ ) . 'css/vidlive.css');
}

function vidlive_settings_filter( $links ) {
	$links[] = '<a href="' . admin_url( 'admin.php?page=vidlive-plugin-settings' ) . '">' . __('Settings') . '</a>';
	return array_reverse($links, true);
}

// Action Hooks
add_action( 'admin_menu', 'register_vidlive_menu' );
add_action( 'widgets_init', 'vidlive_register_widget' );

register_activation_hook( __FILE__, 'vidlive_activation_hook' );

// Shortcode Hooks
add_shortcode( 'vidlive', 'vidlive_shortcode' );

// Settings Filter
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'vidlive_settings_filter' );

// Plugin CSS Style
add_action('admin_enqueue_scripts', 'vidlive_admin_style');