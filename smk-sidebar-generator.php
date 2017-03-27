<?php
/* 
 * Plugin Name: Sidebar Generator and Manager by ZeroWP
 * Plugin URI:  http://zerowp.com/sidebar-generator
 * Description: Generate an unlimited number of sidebars and assign them to any page, using the conditional options, without touching a single line of code.
 * Author:      ZeroWP Team
 * Author URI:  http://zerowp.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: smk-sidebar-generator
 * Domain Path: /lang
 *
 * Version:     4.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class SmkSidebarGenerator{

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '4.0';

	/**
	 * HTML helpers
	 *
	 * @var object 
	 */
	public $html;
	
	/**
	 * This is the only instance of this class.
	 *
	 * @var string
	 */
	protected static $_instance = null;
	
	//------------------------------------//--------------------------------------//
	
	/**
	 * Plugin instance
	 *
	 * Makes sure that just one instance is allowed.
	 *
	 * @return object 
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Cloning is forbidden.
	 *
	 * @return void 
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'smk-sidebar-generator' ), '4.0' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @return void 
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'smk-sidebar-generator' ), '4.0' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Plugin configuration
	 *
	 * @param string $key Optional. Get the config value by key.
	 * @return mixed 
	 */
	public function config( $key = false ){
		$settings = array(
			'version'                => $this->version,
			'page_name'              => __('Sidebar Generator', 'smk-sidebar-generator'),
			'slug'                   => 'smk_sidebar_generator',
			'capability'             => 'manage_options',
			'option_name'            => 'smk_sidebar_generator',
			'settings_register_name' => 'smk_sidebar_generator_register',
			
			// Menu settings
			'menu_name'	             => __('Sidebars', 'smk-sidebar-generator'),
			'menu_parent'            => 'themes.php',
			'menu_priority'          => 60,
			'menu_icon'              => 'dashicons-layout',

			// Widget settings
			'before_widget'          => '<div id="%1$s" class="widget %2$s">',
			'after_widget'           => '</div>',
			'before_title'           => '<h3 class="widget-title">',
			'after_title'            => '</h3>'
		);

		if( !empty($key) && array_key_exists($key, $settings) ){
			return $settings[ $key ];
		}
		else{
			return $settings;
		}
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Build it!
	 */
	public function __construct() {
		$this->constants();
		$this->includeCore();
		$this->initPlugin();

		do_action( 'smk_sidebar_loaded' );
	}
	
	//------------------------------------//--------------------------------------//
	
	/**
	 * Define constants
	 *
	 * @return void 
	 */
	private function constants() {
		$this->define( 'SMK_SIDEBAR_PLUGIN_FILE', __FILE__ );
		$this->define( 'SMK_SIDEBAR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'SMK_SIDEBAR_VERSION', $this->version );

		$this->define( 'SMK_SIDEBAR_PATH', $this->rootPath() );
		$this->define( 'SMK_SIDEBAR_URL', $this->rootURL() );
		$this->define( 'SMK_SIDEBAR_URI', SMK_SIDEBAR_URL );//Alias
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Define a constant
	 *
	 * @param string $name The constant name
	 * @param mixed $value The constant value
	 * @return void 
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Include core files
	 *
	 * @return void 
	 */
	private function includeCore() {
		include $this->rootPath() . "autoloader.php";
		include $this->rootPath() . "functions.php";

	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Init the plugin
	 *
	 * @return void 
	 */
	private function initPlugin() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'adminAssets' ) );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Init the plugin
	 *
	 * @return void 
	 */
	public function init() {
		do_action( 'before_smk_sidebar_init' );

		$this->loadTextDomain();
		
		$this->html = new SmkSidebar\Html;

		do_action( 'smk_sidebar_init' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Localize
	 *
	 * @return void 
	 */
	public function loadTextDomain(){
		load_plugin_textdomain( 'smk-sidebar-generator', false, dirname( plugin_basename(__FILE__) ) . '/lang' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Assets
	 *
	 * Register/Enqueue global admin assets
	 *
	 * @return void 
	 */
	public function adminAssets(){
		if( $this->isPluginPage() ){
			$id     = 'smk-sidebar-generator';
			$assets = $this->rootURL() . 'assets/';
		
			/* Tipped
			--------------*/
			wp_register_style( $id . '-tipped', $assets . 'tipped/tipped.css', '', '4.6.0' );
			wp_enqueue_style( $id . '-tipped' );
			
			wp_register_script( $id . '-tipped', $assets . 'tipped/tipped.js', false, '4.6.0', true );
			wp_enqueue_script( $id . '-tipped' );
			
			/* Select2
			---------------*/
			wp_register_style( $id . '-select2', $assets . 'select2/css/select2.min.css', '', '4.0.3' );
			wp_enqueue_style( $id . '-select2' );
			
			wp_register_script( $id . '-select2', $assets . 'select2/js/select2.min.js', false, '4.0.3', true );
			wp_enqueue_script( $id . '-select2' );
			
			/* Sidebar generator
			-------------------------*/
			wp_register_style( $id, $assets . 'styles.css', '', $this->version );
			wp_enqueue_style( $id );
			
			wp_register_script( $id, $assets . 'scripts.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-slider'), $this->version, true );
			wp_enqueue_script( $id );
		}
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Actions when the plugin is installed
	 *
	 * @return void
	 */
	public function install() {
		// TODO: Implement the migration from version 3.x to 4.0
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Get Root URL
	 *
	 * @return string
	 */
	public function rootURL(){
		return plugin_dir_url( __FILE__ );
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Get Root PATH
	 *
	 * @return string
	 */
	public function rootPath(){
		return plugin_dir_path( __FILE__ );
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Current user object
	 *
	 * @return object
	 */
	public function curentUser(){
		return wp_get_current_user();
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Is plugin page
	 *
	 * Determine if the current request is on plugin page from admin side. Used mainly
	 * to enqueue scripts and styles only on this page.
	 *
	 * @return bool 
	 */
	public function isPluginPage(){
		return is_admin() && isset( $_GET['page'] ) && $_GET[ 'page' ] == $this->config( 'slug' );
	}

	//------------------------------------//--------------------------------------//

	/**
	 * The prefix for sidebar ID
	 *
	 * Generate the prefix for sidebar ID based on current WP setup
	 * This prefix is not unique and it's changed each time the WP is updated or 
	 * the theme is switched
	 * 
	 * @return string 
	 */
	public function prefix(){
		$theme             = get_option( 'current_theme', '' );
		$wordpress_version = get_bloginfo( 'version', '' );
		// Make the prefix
		$string = 's' . substr( $theme, 0, 1 ) . $wordpress_version;
		$string = preg_replace('/[^\w-]/', '', $string);
		return sanitize_key( strtolower( $string ) );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * All generated sidebars
	 *
	 * Get all generated sidebars.
	 *
	 * @return array
	 */
	public function allGeneratedSidebars(){
		$all = get_option( $this->config('option_name'), array() );
		if( !empty( $all['sidebars'] ) ){
			return (array) $all['sidebars'];
		}
		else{
			return array();
		}
	}

}


/*
-------------------------------------------------------------------------------
Main plugin instance
-------------------------------------------------------------------------------
*/
function SSGM() {
	return SmkSidebarGenerator::instance();
}

/*
-------------------------------------------------------------------------------
Rock it!
-------------------------------------------------------------------------------
*/
SSGM();