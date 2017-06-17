<?php 
final class Smk_Sidebar_Plugin{

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version;

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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'smk-sidebar-generator' ), '1.0' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @return void 
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'smk-sidebar-generator' ), '1.0' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Plugin configuration
	 *
	 * @param string $key Optional. Get the config value by key.
	 * @return mixed 
	 */
	public function config( $key = false ){
		return ssgm_config( $key );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Build it!
	 */
	public function __construct() {
		$this->version = SMK_SIDEBAR_VERSION;
		
		/* Include core
		--------------------*/
		include_once $this->rootPath() . "autoloader.php";
		include_once $this->rootPath() . "functions.php";
		
		$this->assets = new SmkSidebar\Assets;

		/* Activation and deactivation hooks
		-----------------------------------------*/
		register_activation_hook( SMK_SIDEBAR_PLUGIN_FILE, array( $this, 'onActivation' ) );
		register_deactivation_hook( SMK_SIDEBAR_PLUGIN_FILE, array( $this, 'onDeactivation' ) );

		/* Init core
		-----------------*/
		add_action( $this->config( 'action_name' ), array( $this, 'init' ), 0 );
		add_action( 'widgets_init', array( $this, 'initWidgets' ), 0 );

		/* Load components, if any...
		----------------------------------*/
		$this->loadComponents();

		/* Plugin fully loaded and executed
		----------------------------------------*/
		do_action( 'ssgm:loaded' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Init the plugin.
	 * 
	 * Attached to `init` action hook. Init functions and classes here.
	 *
	 * @return void 
	 */
	public function init() {
		do_action( 'ssgm:before_init' );

		$this->loadTextDomain();

		$this->html = new SmkSidebar\Html;
		$this->sidebars = new SmkSidebar\Sidebars;

		// Call plugin classes/functions here.
		do_action( 'ssgm:init' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Init the widgets of this plugin
	 *
	 * @return void 
	 */
	public function initWidgets() {
		do_action( 'ssgm:widgets_init' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Localize
	 *
	 * @return void 
	 */
	public function loadTextDomain(){
		load_plugin_textdomain( 
			'smk-sidebar-generator', 
			false, 
			$this->config( 'lang_path' ) 
		);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Load components
	 *
	 * @return void 
	 */
	public function loadComponents(){
		$components = glob( SMK_SIDEBAR_PATH .'components/*', GLOB_ONLYDIR );
		foreach ($components as $component_path) {
			require_once trailingslashit( $component_path ) .'component.php';
		}
	}

	/*
	-------------------------------------------------------------------------------
	Styles
	-------------------------------------------------------------------------------
	*/
	public function addStyles( $styles ){
		$this->assets->addStyles( $styles );
	}

	public function addStyle( $handle, $s = false ){
		$this->assets->addStyle( $handle, $s );
	}

	/*
	-------------------------------------------------------------------------------
	Scripts
	-------------------------------------------------------------------------------
	*/
	public function addScripts( $scripts ){
		$this->assets->addScripts( $scripts );
	}
	public function addScript( $handle, $s = false ){
		$this->assets->addScript( $handle, $s );
	}

	/*
	-------------------------------------------------------------------------------
	Widgets
	-------------------------------------------------------------------------------
	*/
	public function addWidget( $widget_classname ){
		register_widget( $widget_classname );
	}

	public function addWidgets( $widgets ){
		if( !empty( $widgets ) ){
			foreach ($widgets as $widget_classname) {
				register_widget( $widget_classname );
			}
		}
	}

	/*
	-------------------------------------------------------------------------------
	Sidebars
	-------------------------------------------------------------------------------
	*/
	public function addSidebar( $sidebar_id, $sidebar_name, $sidebar_args = array() ){
		$sidebar = wp_parse_args( array(
			'id'   => $sidebar_id,
			'name' => $sidebar_name,
		), $sidebar_args);

		$sidebar = wp_parse_args( $sidebar, array(
			'before_widget' => $this->config( 'before_widget' ),
			'after_widget'  => $this->config( 'after_widget' ),
			'before_title'  => $this->config( 'before_title' ),
			'after_title'   => $this->config( 'after_title' ),
		));

		register_sidebar( apply_filters( 'ssgm:sidebar_args', $sidebar ) );
	}

	public function addSidebars( $sidebars ){
		foreach ($sidebars as $sidebar) {
			$this->addSidebar( $sidebar['id'], $sidebar['name'], $sidebar );
		}
	}

	/*
	-------------------------------------------------------------------------------
	Menus
	-------------------------------------------------------------------------------
	*/
	public function addMenu( $location, $description ) {
		register_nav_menus( array( $location => $description ) );
	}
	public function addMenus( $locations = array() ) {
		register_nav_menus( $locations );
	}

	/*
	-------------------------------------------------------------------------------
	Support
	-------------------------------------------------------------------------------
	*/
	public function addSupport( $feature ) {
		add_theme_support( $feature );
	}

	public function addSupports( $features ) {
		if( !empty($features) ){
			foreach ($features as $feature) {
				add_theme_support( $feature );
			}
		}
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Actions when the plugin is activated
	 *
	 * @return void
	 */
	public function onActivation() {
		// Code to be executed on plugin activation
		do_action( 'ssgm:on_activation' );
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Actions when the plugin is deactivated
	 *
	 * @return void
	 */
	public function onDeactivation() {
		// Code to be executed on plugin deactivation
		do_action( 'ssgm:on_deactivation' );
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Get Root URL
	 *
	 * @return string
	 */
	public function rootURL(){
		return SMK_SIDEBAR_URL;
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Get Root PATH
	 *
	 * @return string
	 */
	public function rootPath(){
		return SMK_SIDEBAR_PATH;
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Get assets url.
	 * 
	 * @param string $file Optionally specify a file name
	 *
	 * @return string
	 */
	public function assetsURL( $file = false ){
		$path = SMK_SIDEBAR_URL . 'assets/';
		
		if( $file ){
			$path = $path . $file;
		}

		return $path;
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
	return Smk_Sidebar_Plugin::instance();
}

/*
-------------------------------------------------------------------------------
Rock it!
-------------------------------------------------------------------------------
*/
SSGM();