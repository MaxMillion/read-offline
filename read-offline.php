<?php
/*
Plugin Name: Read Offline
Plugin URI: http://soderlind.no/archives/2012/10/01/read-offline/
Description: Read Offline allows you to download or print posts and pages. You can download the posts as PDF, ePub or mobi
Author: Per Soderlind
Version: 0.2.8
Author URI: http://soderlind.no
Text Domain: read-offline
Domain Path: /languages
*/
defined( 'ABSPATH' ) or die();

define( 'READOFFLINE_PATH',   __DIR__);
define( 'READOFFLINE_URL',   plugin_dir_url( __FILE__ ));
define( 'READOFFLINE_CACHE', WP_CONTENT_DIR . '/cache/read-offline');
define( 'READOFFLINE_VERSION', '0.2.8' );


if ( version_compare( PHP_VERSION, '5.3.0' ) < 0 ) {
    return add_action( 'admin_notices', 'read_offline_admin_notice_php_version' );
}



Read_Offline_Loader::autoload( READOFFLINE_PATH . '/include'); // autoload includes/class.*.php files

if ( is_admin() ) {
 	new Read_Offline_Admin_Settings ();
}
if (get_option( 'Read_Offline_Admin_Settings' )) {
	add_action( 'init', function(){
			//Read_Offline::get_instance();
			Read_Offline_Parser::get_instance();
			//Read_Offline_Shortcode::get_instance();
			Read_Offline_UX::get_instance();

	});
	add_action( 'widgets_init', function(){
	     register_widget( 'Read_Offline_Widget' );
	});
} else {
	if ( is_admin() ) {
		return add_action( 'admin_notices', 'read_offline_admin_notice_update_options',99 );
	}
}

/**
 * Load language file
 */
add_action('plugins_loaded', function(){
	load_plugin_textdomain( 'read-offline', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
});


function read_offline_admin_notice_php_version () {
    $msg[] = '<div class="error"><p>';
    $msg[] = 'Please upgarde PHP at least to version 5.3.0<br>';
    $msg[] = 'Your current PHP version is <strong>' . PHP_VERSION . '</strong>, which is not suitable for plugin <strong>Read Offline</strong>.';
    $msg[] = '</p></div>';
    echo implode( PHP_EOL, $msg );
}

function read_offline_admin_notice_update_options () {
    $msg[] = '<div class="updated"><p>';
    //$msg[] = '<strong>Read Offline</strong>:';
    $msg[] = __('Please configure','read-offline') . ' <a href="admin.php?page=read_offline_options"><strong>Read Offline</strong></a> ';
    $msg[] = '</p></div>';
    echo implode( PHP_EOL, $msg );
}

/**
 *
 */
class Read_Offline_Loader {
	private static  $dir = __DIR__;

	public static function autoload($dir = '' ) {
		if ( ! empty( $dir ) )
			self::$dir = $dir;

		spl_autoload_register(  __CLASS__ . '::loader'  );
	}

	private static function loader( $class_name ) {
		$class_path = trailingslashit(self::$dir) . 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

		if ( file_exists( $class_path ) )
			require_once $class_path;
	}
}

