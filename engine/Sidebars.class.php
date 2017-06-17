<?php
namespace SmkSidebar;

class Sidebars{

	//------------------------------------//--------------------------------------//
	
	/**
	 * Get generated sidebars from wp_options
	 *
	 * @return array|(bool)false if no generated sidebars available
	 */
	public function getGenerated(){
		$all = get_option( SSGM()->config('option_name'), false );
		return !empty( $all['sidebars'] ) ? $all['sidebars'] : false;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Get static sidebars declared by themes or plugins
	 *
	 * @return array 
	 */
	public function getStatic(){
		$all       = $this->getAll();
		$generated = $this->getGenerated();
		$static    = array();

		if( !empty( $all ) ){
			foreach ( $all as $key => $value) {
				if( ! array_key_exists($key, (array) $generated) ){
					$static[ $key ] = $value;
				}
			}
		}

		return $static;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Get all sidebars.
	 * 
	 * Generated and static
	 *
	 * @return array 
	 */
	public function getAll(){
		global $wp_registered_sidebars;	
		$all_sidebars = false;
		
		if ( !empty($wp_registered_sidebars) && ! is_wp_error( $wp_registered_sidebars ) ) {
			
			foreach ( $wp_registered_sidebars as $sidebar ) {
				$all_sidebars[ $sidebar['id'] ] = $sidebar;
			}
			
		}
		
		return $all_sidebars;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Get a list of all sidebars.
	 * 
	 * Get a list of all sidebars: sidebar_id => sidebar_name
	 *
	 * @return array 
	 */
	public function getNamesList(){
		$sidebars     = $this->getAll();
		$all_sidebars = false;

		if( !empty( $sidebars ) ){
			foreach ( $sidebars as $sidebar ) {
				$all_sidebars[ $sidebar[ 'id' ] ] = $sidebar[ 'name' ];
			}
		}

		return $all_sidebars;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Get active sidebars with their widgets
	 *
	 * @return array 
	 */
	public function getActive(){
		$sidebars = $this->getAll();
		$active = array();
		
		if( !empty($sidebars) ){
			foreach ($sidebars as $id => $sidebar) {
				if( is_active_sidebar( $id )  ){
					$active[ $id ] = $sidebar;
				}
			}
		}
		
		return $active;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Get all conditions from all sidebars, filtered and grouped by main condition
	 *
	 * @return array 
	 */
	public function getConditions(){
		$conditions = array();
		$sidebars   = $this->getGenerated();
		
		if( !empty($sidebars) ){
			foreach ( $sidebars as $sidebar_id => $sidebar ) {
				if( !empty($sidebar['conditions']) ){
					foreach ($sidebar['conditions'] as $condition) {
						if( !empty($condition['if']) && !empty($condition['equalto']) && $condition['if'] !== 'none' ){
							$conditions[ $condition['if'] ][] = array(
								'replace' => $sidebar['replace'],
								'with' => $sidebar_id,
								'equalto' => $condition['equalto'],
							);
						}
					}
				}
			}
		}

		return $conditions;
	}

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

}