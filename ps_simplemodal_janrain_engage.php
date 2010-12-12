<?php
/*
Plugin Name: SimpleModal Janrain Engage
Plugin URI: http://soderlind.no
Description: Adds Janrain Engage (rpx) to SimpleModal Login. The Janrain Engage and SimpleModal Login plugins must be installed and working.
Version: 1.0.0
Author: Per S
Author URI: http://soderlind.no
*/
/*

Changelog:
v1.0: Initial release

*/
/*
Credits: 
	This template is based on the template at http://pressography.com/plugins/wordpress-plugin-template/ 
	My changes are documented at http://soderlind.no/archives/2010/03/04/wordpress-plugin-template/
*/

if (!class_exists('ps_simplemodal_janrain_engage')) {
    class ps_simplemodal_janrain_engage {
		/**
		* @var string $localizationDomain Domain used for localization
		*/
		var $localizationDomain = "ps_simplemodal_janrain_engage";

		/**
		* @var string $url The url to this plugin
		*/ 
		var $url = '';
		/**
		* @var string $urlpath The path to this plugin
		*/
		var $urlpath = '';

		//Class Functions
		/**
		* PHP 4 Compatible Constructor
		*/
		function ps_simplemodal_janrain_engage(){$this->__construct();}

		/**
		* PHP 5 Constructor
		*/        
		function __construct(){
		    //Language Setup
		    $locale = get_locale();
			$mo = plugins_url("/languages/" . $this->localizationDomain . "-".$locale.".mo", __FILE__);	
		    load_textdomain($this->localizationDomain, $mo);
		    //"Constants" setup
			$this->url = plugins_url(basename(__FILE__), __FILE__);
			$this->urlpath = plugins_url('', __FILE__);

			//Actions
			add_action("init", array(&$this,"ps_simplemodal_janrain_engage_init"));
			add_action('wp_print_styles', array(&$this,'ps_simplemodal_janrain_engage_style'));
			//Filters
			add_filter('simplemodal_login_form', array(&$this,'ps_simplemodal_janrain_engage_login_form'));
			add_filter('simplemodal_registration_form', array(&$this,'ps_simplemodal_janrain_engage_registration_form'));
			add_filter('simplemodal_reset_form', array(&$this,'ps_simplemodal_janrain_engage_reset_form'));		    
		}


		function ps_simplemodal_janrain_engage_dependency_check() {	
			$missing_plugin = "";
			$required_plugins_assoc =  array('rpx/rpx.php' => 'Janrain Engage','simplemodal-login/simplemodal-login.php' => 'SimpleModal Login');
			if((get_option(RPX_API_KEY_OPTION) != "") && (get_option('simplemodal_login_options') != "")) {
				$required_plugins = array('rpx/rpx.php','simplemodal-login/simplemodal-login.php');
				$active_plugins = get_option('active_plugins');
				foreach ($required_plugins as $required_plugin) {
				    if ( !in_array( $required_plugin , $active_plugins )) {
						$missing_plugin .= $required_plugins_assoc[$required_plugin] . " "; 
					}
				}
				if ($missing_plugin == "")
					return; // everything is ok
			}

		    $message = sprintf('<p>This plugin requires %s, which you do not have. Add and activate the missing plugin</p>', $missing_plugin); 

		    if( function_exists('deactivate_plugins') ) {
		        deactivate_plugins(__FILE__); 
			}
			exit($message);
		}

		
		function ps_simplemodal_janrain_engage_init() {
			// remove Janrain Engage default login and register form buttons
			remove_action('login_head',   'rpx_login_head');
		    remove_action('login_form',    'rpx_login_form');
		    remove_action('register_form', 'rpx_register_form');
		    remove_action('wp_head', 'rpx_login_head');
			remove_action('wp_footer', 'rpx_wp_footer');
			
			wp_register_style('ps_simplemodal_janrain_engage_style',  $this->url . "?ps_simplemodal_janrain_engage_style",'1.1');
	    }


		function ps_simplemodal_janrain_engage_style() {
			if( !is_admin() ) {					
				wp_enqueue_style('ps_simplemodal_janrain_engage_style');
			}
		}


		function ps_simplemodal_janrain_engage_login_form($form) {
			$users_can_register = get_option('users_can_register') ? true : false;
			$options = get_option('simplemodal_login_options');
			$rpx_api_key = get_option(RPX_API_KEY_OPTION);
		  	if ($rpx_api_key == ''){ $rpx_api_key = strip_tags($_POST[RPX_API_KEY_OPTION]); }
		  	if ($rpx_api_key != ''){
		    	$rpx_rp = rpx_get_rp($rpx_api_key);
			}
		
			$output = sprintf('
		<div id="modalrpx" style="float:left;padding:8px;margin-right:0 auto;">
		<iframe src="%s://%s/openid/embed?token_url=%s" scrolling="no" frameBorder="no" allowtransparency="true" style="width:350px;height:240px;margin:0;padding:0;"></iframe>
		</div>
		<div style="float:right;width=350px;">
		<form name="loginform" id="loginform" action="%s" method="post">
			<div class="title">%s </div>
			<div class="simplemodal-login-fields">
			<p>
				<label>%s<br />
				<input type="text" name="log" class="user_login input" value="" size="20" tabindex="10" /></label>
			</p>
			<p>
				<label>%s<br />
				<input type="password" name="pwd" class="user_pass input" value="" size="20" tabindex="20" /></label>
			</p>',
				$rpx_rp['realmScheme'],
				$rpx_rp['realm'],
				RPX_TOKEN_URL,
				site_url('wp-login.php', 'login_post'),
				__('Or, Login', 'ps_simplemodal_janrain_engage'),
				__('Username', 'ps_simplemodal_janrain_engage'),
				__('Password', 'ps_simplemodal_janrain_engage')
			);

			ob_start();
			do_action('login_form');
			$output .= ob_get_clean();
			$output .= sprintf('
			<p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" class="rememberme" value="forever" tabindex="90" />%s</label></p>
			<p class="submit">
				<input type="submit" name="wp-submit" value="%s" tabindex="100" />
				<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
				<input type="hidden" name="testcookie" value="1" />
			</p>
			<p class="nav">',
				__('Remember Me', 'ps_simplemodal_janrain_engage'),
				__('Log In', 'ps_simplemodal_janrain_engage'),
				__('Cancel', 'ps_simplemodal_janrain_engage')
			);

			if ($users_can_register && $options['registration']) {
				$output .= sprintf('<a class="simplemodal-register" href="%s">%s</a>', 
					site_url('wp-login.php?action=register', 'login'), 
					__('Register', 'ps_simplemodal_janrain_engage')
				);
			}

			if (($users_can_register && $options['registration']) && $options['reset']) {
				$output .= ' | ';
			}

			if ($options['reset']) {
				$output .= sprintf('<a class="simplemodal-forgotpw" href="%s" title="%s">%s</a>',
					site_url('wp-login.php?action=lostpassword', 'login'),
					__('Password Lost and Found', 'ps_simplemodal_janrain_engage'),
					__('Lost your password?', 'ps_simplemodal_janrain_engage')
				);
			}

			$output .= ' 
			</p>
			</div>
			<div class="simplemodal-login-activity" style="display:none;"></div>
		</form>
		</div>';

			return $output;
		}


		function ps_simplemodal_janrain_engage_registration_form() {
			$users_can_register = get_option('users_can_register') ? true : false;
			$options = get_option('simplemodal_login_options');
			$output .= sprintf('

		<div style="float:right;width=350px;">
		<form name="registerform" id="registerform" action="%s" method="post">
			<div class="title">%s</div>
			<div class="simplemodal-login-fields">
			<p>
				<label>%s<br />
				<input type="text" name="user_login" class="user_login input" value="" size="20" tabindex="10" /></label>
			</p>
			<p>
				<label>%s<br />
				<input type="text" name="user_email" class="user_email input" value="" size="25" tabindex="20" /></label>
			</p>',
				site_url('wp-login.php?action=register', 'login_post'),
				__('Or, Register', 'ps_simplemodal_janrain_engage'),
				__('Username', 'ps_simplemodal_janrain_engage'),
				__('E-mail', 'ps_simplemodal_janrain_engage')
			);

			ob_start();
			do_action('register_form');
			$output .= ob_get_clean();			
			
			$output .= sprintf('
			<p class="reg_passmail">%s</p>
			<p class="submit">
				<input type="submit" name="wp-submit" value="%s" tabindex="100" />
				<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
			</p>
			<p class="nav">
				<a class="simplemodal-login" href="%s">%s</a>',
						__('A password will be e-mailed to you.', 'ps_simplemodal_janrain_engage'),
						__('Register', 'ps_simplemodal_janrain_engage'),
						__('Cancel', 'ps_simplemodal_janrain_engage'),
						site_url('wp-login.php', 'login'),
						__('Log in', 'ps_simplemodal_janrain_engage')
					);

					if ($options['reset']) {
						$output .= sprintf(' | <a class="simplemodal-forgotpw" href="%s" title="%s">%s</a>',
							site_url('wp-login.php?action=lostpassword', 'login'),
							__('Password Lost and Found', 'ps_simplemodal_janrain_engage'),
							__('Lost your password?', 'ps_simplemodal_janrain_engage')
						);
					}

					$output .= '
			</p>
			</div>
			<div class="simplemodal-login-activity" style="display:none;"></div>
		</form></div>';

			return $output;
		}


		function ps_simplemodal_janrain_engage_reset_form() {
			$users_can_register = get_option('users_can_register') ? true : false;
			$options = get_option('simplemodal_login_options');
			$output .= sprintf('

		<div style="float:right;width=350px;">
		<form name="lostpasswordform" id="lostpasswordform" action="%s" method="post">
			<div class="title">%s</div>
			<div class="simplemodal-login-fields">
			<p>
				<label>%s<br />
				<input type="text" name="user_login" class="user_login input" value="" size="20" tabindex="10" /></label>
			</p>',

			site_url('wp-login.php?action=lostpassword', 'login_post'),
			__('Reset Password', 'ps_simplemodal_janrain_engage'),
			__('Username or E-mail:', 'ps_simplemodal_janrain_engage')
			);
			
			ob_start();
			do_action('lostpassword_form');
			$output .= ob_get_clean();
			
			$output .= sprintf('
			<p class="submit">
				<input type="submit" name="wp-submit" value="%s" tabindex="100" />
				<input type="button" class="simplemodal-close" value="%s" tabindex="101" />
			</p>
			<p class="nav">
				<a class="simplemodal-login" href="%s">%s</a>',
					__('Get New Password', 'ps_simplemodal_janrain_engage'),
					__('Cancel', 'ps_simplemodal_janrain_engage'),
					site_url('wp-login.php', 'login'),
					__('Log in', 'ps_simplemodal_janrain_engage')
				);

				if ($users_can_register && $options['registration']) {
					$output .= sprintf('| <a class="simplemodal-register" href="%s">%s</a>', site_url('wp-login.php?action=register', 'login'), __('Register', 'ps_simplemodal_janrain_engage'));
				}

				$output .= '
			</p>
			</div>
			<div class="simplemodal-login-activity" style="display:none;"></div>
		</form></div>';

			return $output;
		}
	} //End Class
} //End if class exists statement


if (isset($_GET['ps_simplemodal_janrain_engage_style'])) {
	Header("content-type: text/css");
	echo<<<ENDCSS
/**
* @desc modify the SimpleModal Login style
* @author Per Soderlind - soderlind.no
*/

#simplemodal-login-container {width:710px;background:#fff; border:1px solid #e5e5e5; -moz-border-radius:11px; -webkit-border-radius:11px; border-radius:5px; -moz-box-shadow:rgba(153,153,153,1) 0 4px 18px; -webkit-box-shadow:rgba(153,153,153,1) 0 4px 18px; box-shadow:rgba(153,153,153,1) 0 4px 18px;}
#simplemodal-login-container form {border:0; -moz-box-shadow:none; -webkit-box-shadow:none; box-shadow:none;}
.simplemodal-login-credit {clear:both;width:700px;font-size:11px; padding-top:4px; text-align:center; bottom:0;}


ENDCSS;
} else {
	if (class_exists('ps_simplemodal_janrain_engage')) { 
		register_activation_hook(__FILE__, array('ps_simplemodal_janrain_engage','ps_simplemodal_janrain_engage_dependency_check')); 	
    	$ps_simplemodal_janrain_engage_var = new ps_simplemodal_janrain_engage();
	}
}
?>