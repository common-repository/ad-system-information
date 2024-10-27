<?php
/**
 * System Info - AdPress Addon 
 *
 * @author Abid Omar
 * @version 1.0
 * @package Main
 */
/*
  Plugin Name: System Info 
  Plugin URI: http://wpadpress.com
  Description: Displays system info and debugging features for WordPress and the AdPress plugin
  Author: Abid Omar
  Author URI: http://omarabid.com
  Version: 1.0 
  Text Domain: wp-asi
 */

// Don't load directly
if (!defined('ABSPATH')) {
   die('-1');
}

// Register the Add-on (for AdPress)
add_filter('adpress_addons', 'asi_register_addon');

function asi_register_addon($addons)
{
   $addon = array(
	  'id' => 'asi',
	  'title' => 'System Info',
	  'description' => __('Displays system info and debugging features for the AdPress plugin', 'wp-asi'),
	  'author' => 'Abid Omar',
	  'version' => '1.0',
	  'basename' => plugin_basename(__FILE__),
	  'settings' => 'adpress-asi',
   );
   array_push($addons, $addon);

   return $addons;
}


// Define some paths
define('ASI_URLPATH', trailingslashit(WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__))));

// PAD i18n
function asi_i18n() {
   $plugin_dir = basename(dirname(__FILE__));
   load_plugin_textdomain( 'wp-asi', false, $plugin_dir );
}
add_action('plugins_loaded', 'asi_i18n');

// Register the Add-on Settings Page 
add_action('admin_menu', 'asi_menu', 20);
function asi_menu() {
   $asi_mang =   add_management_page( 'System Info', 'System Info', 'manage_options', 'adpress-asi', 'asi_page' );
   $asi_adp =   add_submenu_page('adpress-campaigns', 'AdPress | System Info', 'System Info', 'manage_options', 'adpress-asi', 'asi_page');
   add_action("load-$asi_mang", 'asi_settings_help');
   add_action("load-$asi_mang", 'asi_actions');
   add_action("load-$asi_adp", 'asi_settings_help');
   add_action("load-$asi_adp", 'asi_actions');
}

// Load the Settings Page
function asi_page() {
   require_once('settings.php');
}

// Display Dropdown Help
function asi_settings_help() {
   global $current_screen;
   $current_screen->add_help_tab(array(
	  'id' => 'asi_help_tab',
	  'title' => __('System Information', 'wp-asi'),
	  'content' => '<p>' . __('Displays System Information for debugging', 'wp-asi') . '</p>'
   ));
}

// Register the Settings page Scripts and Styles
add_action('admin_print_scripts', 'asi_load_scripts', 20);
add_action('admin_print_styles', 'asi_load_styles', 20);

// Load Scripts
function asi_load_scripts()
{
   // Current Screen
   global $current_screen;
   if ($current_screen->id === 'adpress_page_adpress-asi') {	

   }
}

// Load Styles
function asi_load_styles()
{
   // Current Screen
   global $current_screen;
   if ($current_screen->id === 'adpress_page_adpress-asi' || $current_screen->id === 'tools_page_adpress-asi') {	
	  wp_enqueue_style('asi_settings', ASI_URLPATH . 'files/css/style.css');
   }
}

/*
 * Handle Settings Page  Actions
 */
function asi_actions()
{
   if (isset($_GET['action'])) {
	  switch($_GET['action']) {
	  case 'download':
		 nocache_headers();

		 header( "Content-type: text/plain" );
		 header( 'Content-Disposition: attachment; filename="system-info.txt"' );

		 asi_sys_info();
		 exit;
		 break;
	  case 'reinstall':
		 $install = new wp_adpress_install();
		 break;
	  }
   }
}

/**
 * Echo the Sys Info
 */
function asi_sys_info() {
   if (!class_exists('Browser')) {
	  require_once ('lib/browser.php');
   }

   global $wpdb,
	  $wpadpress;
   if (!isset($wpadpress)) {
		$adversion = "not available";
   } else {
		$adversion = $wpadpress->version;
   }

   $browser = new Browser();
   if ( get_bloginfo( 'version' ) < '3.4' ) {
	  $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
	  $theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
   } else {
	  $theme_data = wp_get_theme();
	  $theme      = $theme_data->Name . ' ' . $theme_data->Version;
   }

   // Try to identifty the hosting provider
   $host = false;
   if( defined( 'WPE_APIKEY' ) ) {
	  $host = 'WP Engine';
   } elseif( defined( 'PAGELYBIN' ) ) {
	  $host = 'Pagely';
   }
?>
### Begin System Info ###

## Please include this information when posting support requests ##


Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

AdPress Version:          <?php echo $adversion . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $theme . "\n"; ?>
<?php if( $host ) : ?>
Host:                     <?php echo $host . "\n"; ?>
<?php endif; ?>

<?php echo $browser ; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>

Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
WordPress Memory Limit:   <?php echo ( edd_let_to_num( WP_MEMORY_LIMIT )/( 1024 ) )."MB"; ?><?php echo "\n"; ?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>
Page For Posts:           <?php $id = get_option( 'page_for_posts' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>

<?php
   $request['cmd'] = '_notify-validate';

   $params = array(
	  'sslverify'                => false,
	  'timeout'                => 60,
	  'user-agent'        => 'EDD/' . EDD_VERSION,
	  'body'                        => $request
   );

   $response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

   if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
	  $WP_REMOTE_POST =  'wp_remote_post() works' . "\n";
} else {
   $WP_REMOTE_POST =  'wp_remote_post() does not work' . "\n";
}
?>
   WP Remote Post:           <?php echo $WP_REMOTE_POST; ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.'; ?><?php echo "\n"; ?>
SOAP Client:              <?php echo ( class_exists( 'SoapClient' ) ) ? 'Your server has the SOAP Client enabled.' : 'Your server does not have the SOAP Client enabled.'; ?><?php echo "\n"; ?>
SUHOSIN:                  <?php echo ( extension_loaded( 'suhosin' ) ) ? 'Your server has SUHOSIN installed.' : 'Your server does not have SUHOSIN installed.'; ?><?php echo "\n"; ?>

ACTIVE PLUGINS:

<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
   // If the plugin isn't active, don't show it.
   if ( ! in_array( $plugin_path, $active_plugins ) )
	  continue;

   echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}

if ( is_multisite() ) :
?>

NETWORK ACTIVE PLUGINS:

<?php
   $plugins = wp_get_active_network_plugins();
$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

foreach ( $plugins as $plugin_path ) {
   $plugin_base = plugin_basename( $plugin_path );

   // If the plugin isn't active, don't show it.
   if ( ! array_key_exists( $plugin_base, $active_plugins ) )
	  continue;

   $plugin = get_plugin_data( $plugin_path );

   echo $plugin['Name'] . ' :' . $plugin['Version'] ."\n";
}

endif;

?>
AdPress Settings
<?php
var_export(get_option('adpress_settings'), array());
echo "\n";
var_export(get_option('adpress_gateways'), array());
echo "\n";
var_export(get_option('adpress_image_settings'), array());
echo "\n";
var_export(get_option('adpress_link_settings'), array());
echo "\n";
var_export(get_option('adpress_flash_settings'), array());
echo "\n";
var_export(get_option('adpress_license_settings'), array());
echo "\n";
?>
### End System Info ###
<?php
}
