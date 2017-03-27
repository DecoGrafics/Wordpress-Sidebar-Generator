<?php
namespace SmkSidebar;

abstract class Foundation {

	//------------------------------------//--------------------------------------//
	
	/**
	 * Page
	 *
	 * Create the admin page
	 *
	 * @return string 
	 */
	abstract public function page();

	//------------------------------------//--------------------------------------//
	
	/**
	 * Init the object
	 *
	 * Create a new instance of this plugin.
	 *
	 * @return void 
	 */
	public function init(){
		add_action( 'widgets_init', array( $this, 'registerGeneratedSidebars' ) );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sanitize data
	 *
	 * Sanitize the data sent to the server. Unset all invalid or empty(none) conditions.
	 *
	 * @return array The sanitized data 
	 */
	// public function sanitizeData( $data ) {
	// 	if( is_array( $data ) && !empty( $data ) ){
	// 		$new_data = $data;
	// 		foreach ($data as $sidebar_id => $sidebar_settings) {
	// 			if( !empty($sidebar_settings['conditions']) && is_array($sidebar_settings['conditions']) ){
	// 				foreach ($sidebar_settings['conditions'] as $key => $condition) {
	// 					if( !empty($condition['if']) && $condition['if'] == 'none' ){
	// 						unset( $new_data[ $sidebar_id ]['conditions'][ $key ] );
	// 					}
	// 					else{
	// 						continue;
	// 					}
	// 				}
	// 			}
	// 		}
	// 		$data = $new_data;
	// 	}
	// 	return $data;
	// }

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar Widgets
	 *
	 * Get all sidebar with all widgets assigned to it.
	 *
	 * @return array
	 */
	public function sidebarWidgets(){
		return wp_get_sidebars_widgets();
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * All saved widgets types
	 *
	 * Get all saved widget types.
	 *
	 * @return array
	 */
	public function widgetsTypes(){
		$all = $this->sidebarWidgets();
		$widgets = array();
		foreach ($all as $part) {
			foreach ($part as $key => $widget) {
				$widget_option_name = 'widget_'. substr($widget, 0, -2);
				$widgets[ $widget_option_name ] = $widget_option_name;
			}
		}
		return $widgets;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Widgets Options
	 *
	 * Get all data(options) for each widget type.
	 *
	 * @return array
	 */
	public function widgetsOptions(){
		$options = array();
		foreach ($this->widgetsTypes() as $key => $value) {
			$options[ $value ] = get_option( $value );
		}
		return $options;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * All registered sidebars
	 *
	 * Get all registered sidebars.
	 *
	 * @return array
	 */
	public function allRegisteredSidebars(){
		global $wp_registered_sidebars;	
		$all_sidebars = array();
		
		if ( $wp_registered_sidebars && ! is_wp_error( $wp_registered_sidebars ) ) {
			
			foreach ( $wp_registered_sidebars as $sidebar ) {
				$all_sidebars[ $sidebar['id'] ] = $sidebar;
			}
			
		}
		
		return $all_sidebars;
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
		$all = get_option( SSGM()->config('option_name'), array() );
		if( !empty( $all['sidebars'] ) ){
			return $all['sidebars'];
		}
		else{
			return array();
		}
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Register sidebars
	 *
	 * Register all generated sidebars
	 *
	 * @hook widgets_init
	 * @return void 
	 */
	public function registerGeneratedSidebars() {

		//Catch saved options
		$sidebars = get_option( SSGM()->config('option_name'), array() );

		//Make sure if we have valid sidebars
		if ( !empty( $sidebars['sidebars'] ) && is_array( $sidebars['sidebars'] ) ){

			//Register each sidebar
			foreach ($sidebars['sidebars'] as $sidebar) {
				if( isset($sidebar) && !empty($sidebar) ){

					register_sidebar(
						array(
							'name'          => $sidebar['name'],
							'id'            => $sidebar['id'],
							'description'   => $sidebar['description'],
							'before_widget' => SSGM()->config( 'before_widget' ),
							'after_widget'  => SSGM()->config( 'after_widget' ),
							'before_title'  => SSGM()->config( 'before_title' ),
							'after_title'   => SSGM()->config( 'after_title' ),
						)
					);

				}
			}

		}

	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * All static sidebars
	 *
	 * Get all static sidebars.
	 *
	 * @return array
	 */
	public function allStaticSidebars(){
		$all = $this->allRegisteredSidebars();
		$generated = SSGM()->allGeneratedSidebars();
		$static = array();
		foreach ( $all as $key => $value) {
			if( ! array_key_exists($key, $generated) ){
				$static[ $key ] = $value;
			}
		}
		return $static;
	}

}