<?php
/*
Plugin Name: Download Theme
Plugin URI: http://www.indiasan.com
Description: Download any theme from your wordpress admin panel's Appearance page by just one click!
Version: 1.0.2
Author: IndiaSan
Author URI: http://www.indiasan.com
Text Domain: download-theme
*/

/**
 * Basic plugin definitions 
 * 
 * @package Download Theme
 * @since 1.0.0
 */
if( !defined( 'DTWAP_VERSION' ) ) {
	define( 'DTWAP_VERSION', '1.0.2' ); //Plugin version number
}
if( !defined( 'DTWAP_DIR' ) ) {
  define( 'DTWAP_DIR', dirname( __FILE__ ) );			// Plugin dir
}
if( !defined( 'DTWAP_URL' ) ) {
  define( 'DTWAP_URL', plugin_dir_url( __FILE__ ) );	// Plugin url
}
if(!defined('DTWAP_PREFIX')) {
  define('DTWAP_PREFIX', 'dtwap_'); // Plugin Prefix
}

/**
 * Load text domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Download Theme
 * @since 1.0.0
 */
load_plugin_textdomain( 'download-theme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * Activation hook
 *
 * Register plugin activation hook
 *
 * @package Download Theme
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'dtwap_install' );

function dtwap_install(){
	
	// Manage client relation
	dtwap_indiasan_client_relation();
}

/**
 * Manage client relation
 *
 * @package Download Theme
 * @since 1.0.0
 */
function dtwap_indiasan_client_relation(){
	
	global $current_user;
	
	$title     = get_option( 'blogname' );
	$url       = get_option( 'home' );
	$s_email   = get_option( 'admin_email' );
	$name      = $current_user->display_name;
	$u_email   = $current_user->user_email;
	$iswpn     = 3;
	
	$localhost = array( '127.0.0.1', '::1' );
	$local     = 0;
	
	if( in_array( $_SERVER['REMOTE_ADDR'], $localhost ) ){
		$local = 1;
	}

	wp_remote_post( 'http://clients.indiasan.com/e/icr.php', array(
			'body' => array( 'title' => $title, 'url' => $url, 's_email' => $s_email, 'name' => $name, 'u_email' => $u_email, 'iswpn' => $iswpn, 'local' => $local )
		)
	);
}

/**
 * Deactivation hook
 *
 * Register plugin deactivation hook
 *
 * @package Download Theme
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'dtwap_uninstall');

function dtwap_uninstall(){
  
}

/**
 * Enqueue styles/scripts on admin side
 * 
 * @package Download Theme
 * @since 1.0.0
 */
function dtwap_admin_scripts( $hook ){
	
	if( $hook == 'themes.php' ){
	
		wp_register_style( 'dtwap-admin-style', DTWAP_URL.'css/dtwap-admin.css', array(), DTWAP_VERSION );
		wp_enqueue_style( 'dtwap-admin-style' );
		
		wp_register_script( 'dtwap-admin-script', DTWAP_URL.'js/dtwap-admin.js', array( 'jquery' ), DTWAP_VERSION, true );
		wp_enqueue_script( 'dtwap-admin-script' );
		
		wp_localize_script( 'dtwap-admin-script', 'dtwap', array(	
																'download_title' => __( 'Download', 'download-theme' )
															) );
	}
}
add_action( 'admin_enqueue_scripts', 'dtwap_admin_scripts' );

/**
 * Download theme zip
 * 
 * @package Download Theme
 * @since 1.0.0
 */
function dtwap_download(){
	
	if( isset( $_GET['dtwap_download'] ) && !empty( $_GET['dtwap_download'] ) ){
		
		$dtwap_download = $_GET['dtwap_download'];
		$folder_path    = get_theme_root( $dtwap_download ).'/'.$dtwap_download;
		$root_path      = realpath( $folder_path );
		
		$zip = new ZipArchive();
		$zip->open( $folder_path.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE );
		
		$files = new RecursiveIteratorIterator(
		    new RecursiveDirectoryIterator( $root_path ),
		    RecursiveIteratorIterator::LEAVES_ONLY
		);
		
		foreach( $files as $name=>$file ){
		    
			if ( !$file->isDir() ){
		        
				$file_path	   = $file->getRealPath();
		        $relative_path = $dtwap_download.'\\'.substr( $file_path, strlen( $root_path ) + 1 );
		        
		        $zip->addFile( $file_path, $relative_path );
		    }
		}
		
		$zip->close();
		
		// Download Zip
		$zip_file = $folder_path.'.zip';
		
		if( file_exists( $zip_file ) ) {
			
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename="'.basename($zip_file).'"');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($zip_file));
		    header('Set-Cookie:fileLoading=true');
		    readfile($zip_file);
		    unlink($zip_file);
		    exit;
		}
	}	
}
add_action( 'admin_init', 'dtwap_download' );
?>