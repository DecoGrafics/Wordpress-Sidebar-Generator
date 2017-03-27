<?php
namespace SmkSidebar;

class Generator extends Foundation {

	public function __construct(){
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'registerSetting' ) );
		add_action( 'wp_ajax_smk-sidebar-generator_load_equalto', array( $this, 'equaltoAjax' ) );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Menu
	 *
	 * Create a new submenu for this plugin.
	 *	
	 * @hook admin_menu
	 * @uses $this->page() to get the page display.
	 * @return void 
	 */
	public function menu(){
		$settings = SSGM()->config();

		add_menu_page(
			$settings['menu_name'], 
			$settings['menu_name'], 
			$settings['capability'], 
			$settings['slug'], 
			array( $this, 'page' ) ,
			$settings['menu_icon'], 
			$settings['menu_priority']
		);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Register setting
	 *
	 * Register setting. This allows to update the option on form submit.
	 *
	 * @hook admin_init
	 * @return void 
	 */
	public function registerSetting() {
		$settings = SSGM()->config();
		register_setting( $settings['settings_register_name'], $settings['option_name']/*, array( &$this, 'sanitizeData' )*/ );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Page
	 *
	 * Create the admin page
	 *
	 * @return string 
	 */
	public function page(){
		echo '<div class="smk-sidebars-list-template" style="display: none;">';
			$this->sidebarListTemplate();
		echo '</div>';
		echo '<div class="smk-sidebars-condition-template" style="display: none;">';
			echo $this->aSingleCondition('__cond_name__', '', 0, 'all');
		echo '</div>';
		$this->pageOpen();
			settings_fields( SSGM()->config('settings_register_name') );

			$counter = get_option( SSGM()->config('option_name'), array() );
			$counterval = ! empty( $counter['counter'] ) ? absint( $counter['counter'] ) : intval( '0' );
			echo SSGM()->html->input(
				'smk-sidebar-generator-counter', // ID
				SSGM()->config( 'option_name' ) . '[counter]', 
				absint( $counterval ), 
				array(
					'type' => 'hidden',
				)
			);
			
			$this->allSidebarsList();
			
		submit_button();
		$this->pageClose();

		//Filtered conditions
		$conditions = array();
		foreach (SSGM()->allGeneratedSidebars() as $sidebar_id => $sidebar) {
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

		$this->debug( $conditions, 'Filtered Conditions' );



		// Debug start
		// $this->debug( $this->allStaticSidebars(), 'All static sidebars' );
		$this->debug( SSGM()->allGeneratedSidebars(), 'All generated sidebars' );
		// global $sidebars_widgets;
		// $this->debug( $sidebars_widgets, 'All sidebars and their widgets' );
		// $this->debug( smk_sidebar_conditions_filter(), 'All conditions' );


	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Page Open
	 *
	 * Outputs the top part of HTML for this page. 
	 *
	 * @return string 
	 */
	public function pageOpen($echo = true){
		$html = '<div class="wrap sbg-clearfix">';
		$html .= '<h2>'. SSGM()->config( 'page_name' ) .'</h2>';
		$html .= '<div class="smk-sidebars-container">';
		$html .= '<h3>
				'. __('Sidebars', 'smk-sidebar-generator') .'
				<span class="tip dashicons-before dashicons-editor-help" title="'. __('All available sidebars.', 'smk-sidebar-generator') .'"></span>
				<span class="add-new-h2 add-new-sidebar" data-sidebars-prefix="'. SSGM()->prefix() .'">'. __('Add new', 'smk-sidebar-generator') .'</span>
			</h3>';
		$html .= '<form method="post" action="options.php" class="smk-sidebar-generator_main_form">';
		if( $echo ) { echo $html; } else { return $html; }
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Page Close
	 *
	 * Outputs the bottom part of HTML for this page. 
	 *
	 * @return string 
	 */
	public function pageClose($echo = true){
		$html = '</form>';
		$html .= '</div>';
		$html .= $this->allRemovedSidebarsList( false );
		$html .= '</div>';
		if( $echo ) { echo $html; } else { return $html; }
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * All sidebars list
	 *
	 * All sidebars list
	 *
	 * @return string The HTML.
	 */
	public function allSidebarsList($echo = true){
		$all = SSGM()->allGeneratedSidebars();
		$list = '<div id="smk-sidebars" class="accordion-container smk-sidebars-list">';
		$list .= '<ul class="connected-sidebars-lists">';
			if( !empty( $all ) ){
				foreach ( (array) $all as $id => $s ) {
					$list .= $this->aSingleListItem( $s );
				}
			}
			else{
				$list .= '<li id="no-sidebars-notice" class="no-sidebars">
					<h3>'. __('You don\'t have any generated sidebars.', 'smk-sidebar-generator') .'</h3>
					<p>'. __('Create a new sidebar by pressing the above "Add new" button.', 'smk-sidebar-generator') .'</p>
				</li>';
			}
		$list .= '</ul>';
		$list .= '</div>';
		if( $echo ) { echo $list; } else { return $list; }
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * A single sidebar list
	 *
	 * Output the HTML for a single sidebar list.
	 *
	 * @param array $sidebar_data Sidebar data
	 * @param array $settings Sidebar custom settings
	 * @return string The HTML.
	 */
	public function aSingleListItem($sidebar_data, $settings = false){
		$settings = ( $settings && is_array( $settings ) ) ? $settings : SSGM()->config();
		$name     = $settings['option_name'] .'[sidebars]['. $sidebar_data['id'] .']';
		
		// All pages
		$all_pages = get_pages();
		$pages_options = '';
		foreach ( $all_pages as $page ) {
			$pages_options .= '<option value="' . $page->ID . '">';
			$pages_options .= $page->post_title;
			$pages_options .= '</option>';
		}

		if( !empty($sidebar_data) ) : 
			$the_sidebar = $this->sidebarAccordion('open', $sidebar_data, $settings, false);

				$the_sidebar .= $this->fieldId($name, $sidebar_data);
				
				$the_sidebar .= '<div class="smk-sidebar-row sbg-clearfix">';

					$the_sidebar .= '<div class="grid-5 sidebar-info-tabs">
						<label>
							<span class="sidebar-info-tab" data-id="name">'. __('Name', 'smk-sidebar-generator') .'</span>
							<span class="sidebar-info-tab" data-id="description">'. __('Description', 'smk-sidebar-generator') .'</span>
							<span class="sidebar-info-tab" data-id="shortcode">'. __('Shortcode', 'smk-sidebar-generator') .'</span>
						</label>
						<div class="tabs">
							<div data-target="name">'. $this->fieldName($name, $sidebar_data) .'</div> 
							<div data-target="description">'. $this->fieldDescription($name, $sidebar_data) .'</div> 
							<div data-target="shortcode">
								<code class="smk-sidebar-shortcode">[smk_sidebar id="'. $sidebar_data['id'] .'"]</code>
							</div> 
						</div>
					</div>';

					$the_sidebar .= '<div class="grid-7 sidebar-to-replace-container">'.
						$this->fieldToReplace($name, $sidebar_data)
					.'</div>';

				$the_sidebar .= '</div>';//.smk-sidebar-row

				$the_sidebar .= '<div class="smk-sidebar-row sbg-clearfix">';
					// Conditions
					$the_sidebar .= '<div class="conditions-all grid-12">';

						$the_sidebar .= '<label>'.  
							__('Replace the sidebars only if it meets the following conditions:', 'smk-sidebar-generator')
						.'</label>';

						$the_sidebar .= '<div class="created-conditions">';
							if( !empty($sidebar_data['conditions']) ){
								foreach ( (array) $sidebar_data['conditions'] as $index => $condition) {
									$the_sidebar .= $this->aSingleCondition($name, $sidebar_data, $index, $condition['if']);
								}
							}
							else{
								$the_sidebar .= $this->aSingleCondition($name, $sidebar_data, 0, 'all');
							}
						$the_sidebar .= '</div>'; //.created-conditions
					
					$the_sidebar .= '<div class="smk-sidebar-row sbg-clearfix">';
						$the_sidebar .= '<div class="grid-12">';
							$the_sidebar .= 'Condition for: ';
							$the_sidebar .= $this->fieldConditionSelector();
							$the_sidebar .= ' <button class="condition-add button" data-name="'. $name .'" data-sidebar-id="'. $sidebar_data['id'] .'">'. 
								__('Add condition', 'smk-sidebar-generator') 
							.'</button>';
						$the_sidebar .= '</div>';
					$the_sidebar .= '</div>';
					


					$the_sidebar .= '</div>'; //.conditions-all
				$the_sidebar .= '</div>'; //.smk-sidebar-row

			$the_sidebar .= $this->sidebarAccordion('close', $sidebar_data, $settings, false);
		return $the_sidebar;
		endif;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar Accordion
	 *
	 * Global parts of a single sidebar accordion
	 * 
	 * @param string $part `open` or `close`
	 * @return string The HTML.
	 */
	public function sidebarAccordion($part, $sidebar_data = array(), $settings = array(), $echo = true){

		$class    = ( !empty( $settings['class'] ) ) ? ' '. $settings['class'] : '';
		if( $part == 'open' ){

			$signs = '<span class="info-signs">';
				$signs .= '<span title="'. __( 'Replaces at least one sidebar', 'smk-sidebar-generator' ) .'" data-info="replaces" class="dashicons dashicons-visibility"></span>';
				$signs .= '<span title="'. __( 'Conditions are enabled', 'smk-sidebar-generator' ) .'" data-info="has_conditions" class="dashicons dashicons-filter"></span>';
			$signs .= '</span>';

			$the_sidebar = '
			<li id="'. $sidebar_data['id'] .'" class="control-section accordion-section'. $class .'">
				<h3 class="accordion-section-title hndle">
					<span class="smk-sidebar-section-icon dashicons dashicons-editor-justify"></span> 
					<span class="name">'. $sidebar_data['name'] .'</span>&nbsp;
					<span class="description">'. $sidebar_data['description'] .'</span>&nbsp;
					<div class="moderate-sidebar">
						<span class="smk-delete-sidebar">'. __('Delete', 'smk-sidebar-generator') .'</span>
						<span class="smk-restore-sidebar">'. __('Restore', 'smk-sidebar-generator') .'</span>
					</div>
					'. $signs .'
				</h3>
				<div class="accordion-section-content" style="display: none;">
					<div class="inside">';
		}
		elseif( $part == 'close' ){
					$the_sidebar = '</div>
				</div>
			</li>';
		}
		else{
			$the_sidebar = '';
		}

		if( $echo ) { echo $the_sidebar; } else { return $the_sidebar; }
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * A single condition
	 *
	 * Display a single condition
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function aSingleCondition($name, $sidebar_data, $index = 0, $condition_if = 'all'){
		$the_sidebar = '<div class="condition-parent sbg-clearfix">';
			$the_sidebar .= '<span class="smk-sidebar-condition-icon dashicons dashicons-menu"></span>';
			$the_sidebar .= '<div class="conditions-first">';
				$the_sidebar .= $this->fieldConditionMain( $name, $sidebar_data, $index );
			$the_sidebar .= '</div>';
			$the_sidebar .= '<div class="conditions-second">';
				$the_sidebar .= $this->fieldConditionEqualTo($name, $sidebar_data, $index, $condition_if);
			$the_sidebar .= '</div>';
		$the_sidebar .= ' <span class="condition-remove" title="'. __('Remove condition', 'smk-sidebar-generator') .'"> <i class="dashicons dashicons-no-alt"></i> </span>';
		$the_sidebar .= '</div>';
		return $the_sidebar;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar field name
	 *
	 * Display sidebar name field
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function fieldName($name, $sidebar_data){
		return SSGM()->html->input(
				'', // ID
				$name. '[name]', 
				$sidebar_data['name'], 
				array(
					'type' => 'text',
					'class' => array( 'smk-sidebar-name', 'widefat' ),
					'placeholder' => __( 'The sidebar name', 'smk-sidebar-generator' ),
				)
			);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar field ID
	 *
	 * Display sidebar ID field
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function fieldId($name, $sidebar_data){
		return SSGM()->html->input(
			'', // ID
			$name. '[id]', 
			$sidebar_data['id'], 
			array(
				'type' => 'hidden',
				'class' => array( 'smk-sidebar-id' ),
			)
		);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar field description
	 *
	 * Display sidebar description field
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function fieldDescription($name, $sidebar_data){
		return SSGM()->html->input(
				'', // ID
				$name. '[description]', 
				$sidebar_data['description'], 
				array(
					'type' => 'text',
					'class' => array( 'smk-sidebar-description', 'widefat' ),
					'placeholder' => __( 'A short description for this sidebar', 'smk-sidebar-generator' ),
				)
			);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar field To Replace
	 *
	 * Display sidebar To Replace field
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function fieldToReplace($name, $sidebar_data){

		// To replace
		$static   = $this->allStaticSidebars();
		$static_sidebars = '';
		foreach ($static as $key => $value) {
			$static_sidebars[ $key ] = $value['name'];
		}

		$replace = !empty( $sidebar_data['replace'] ) ? $sidebar_data['replace'] : array();

		return '<label>'. __('Sidebars to replace:', 'smk-sidebar-generator') .'</label>'. 
			SSGM()->html->select(
				'', // ID
				$name. '[replace][]', 
				$replace,
				array(
					'multiple' => 'multiple',
					'options'  => $static_sidebars,
					'size'     => 1,
					'class'    => array( 'sidebars-to-replace-select' ),
					'style'    => 'width: 100%',
				)
			);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar field Condition main
	 *
	 * Display sidebar Condition main field
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function fieldConditionMain($name, $sidebar_data, $index = 0){

		$saved = ! empty( $sidebar_data['conditions'][ absint( $index ) ]['if'] ) ? 
		            $sidebar_data['conditions'][ absint( $index ) ]['if'] : '';

		return '<span class="condition-label">'. __('Replace if', 'smk-sidebar-generator') .' </span>'.
			SSGM()->html->input(
				'', // ID
				$name. '[conditions]['. absint( $index ) .'][if]', 
				$saved, 
				array(
					'class' => array('cond-field', 'condition-if'),
					'style'    => 'width: 100%',
				)
			);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Condition selector
	 *
	 * Display sidebar Condition main field
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function fieldConditionSelector(){

		// $options = array( 'none' => __('None', 'smk-sidebar-generator') );
		$options = array();
		$all_conditions = smk_sidebar_conditions_filter();
		if( !empty($all_conditions) && is_array($all_conditions) ){
			foreach ($all_conditions as $type => $class) {
				if( class_exists($class) ){
					$newclass     = new $class;
					$newoptions   = $newclass->getMainData();
					if( !empty($newoptions) && is_array($newoptions) ){
						$options[] = $newoptions;
					}
				}
			}
		}

		return SSGM()->html->select(
				'', // ID
				'', //Name
				'', //Value
				array(
					'options' => $options,
					'class' => array('main-condition-selector'),
					'style'    => 'width: 200px',
				)
			);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar field Condition EqualTo
	 *
	 * Display sidebar Condition EqualTo field
	 *
	 * @param string $name HTML field name
	 * @param string $sidebar_data Data for current sidebar
	 * @return string The HTML
	 */
	public function fieldConditionEqualTo($name, $sidebar_data, $index = 0, $type){

		$saved = ! empty( $sidebar_data['conditions'][ absint( $index ) ]['equalto'] ) ? $sidebar_data['conditions'][ absint( $index ) ]['equalto'] : '';

		return '<span class="condition-label">'. __('and is equal to', 'smk-sidebar-generator') .'</span>' . 
			SSGM()->html->select(
				'', // ID
				$name. '[conditions]['. absint( $index ) .'][equalto][]', 
				$saved, 
				array(
					'options' => $this->getEqualToOptions($type),
					'multiple' => 'multiple',
					'size' => 1,
					'class' => array('cond-field', 'condition-equalto'),
					'style'    => 'width: 100%',
				)
			);
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Get Equal to Options
	 *
	 * @param string $type Example pot_type::page 
	 * @return array
	 */
	public function getEqualToOptions($type){
		$the_type       = explode('::', $type);
		$options        = array();
		$all_conditions = smk_sidebar_conditions_filter();

		if( !empty($all_conditions) && is_array($all_conditions) ){
			if( array_key_exists($the_type[0], $all_conditions) ){
				$class = $all_conditions[ $the_type[0] ];
				if( class_exists($class) ){
					$newclass     = new $class;
					$newoptions   = $newclass->getSecondaryData( $type );
					if( !empty($newoptions) && is_array($newoptions) ){
						$options = $options + (array) $newoptions;
					}
				}
			}
		}

		return $options;
	}

	public function equaltoAjax(){	
		$data = $_POST['data'];
		$type = $data['condition_if'];
		$opt = $this->getEqualToOptions($type);

		echo json_encode( $opt );

		die();
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * All removed sidebars list
	 *
	 * All removed sidebars list
	 *
	 * @return string The HTML.
	 */
	public function allRemovedSidebarsList($echo = true){
		$list = '<div class="smk-sidebars-container removed-sidebars">
			<h3>
				'. __('Removed', 'smk-sidebar-generator') .'
				<span class="tip dashicons-before dashicons-editor-help" title="'. __('These sidebars will be removed on the next page refresh.', 'smk-sidebar-generator') .'"></span>
			</h3>
			<div id="smk-removed-sidebars" class="accordion-container smk-sidebars-list">
				<ul class="connected-sidebars-lists"></ul>
			</div>
		</div>';
		if( $echo ) { echo $list; } else { return $list; }
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebar Template
	 *
	 * Create the template for new sidebars generation
	 *
	 * @return string The HTML.
	 */
	public function sidebarListTemplate($echo = true){
		$sidebar_data = array( 
			'name'        => sprintf( __('New sidebar %s', 'smk-sidebar-generator'), '__index__' ),
			'id'          => '__id__',
			'description' => '',
		);

		$settings = array( 
			'option_name' => SSGM()->config('option_name'),
			'class'       => 'sidebar-template',
		);
		$item = $this->aSingleListItem( $sidebar_data, $settings );
		if( $echo ) { echo $item; } else { return $item; }
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Debug
	 *
	 * Debud saved data
	 * 
	 * @param array $data The data to debug.
	 * @return string 
	 */
	public function debug($data = array(), $title = ''){
		if( is_array($data) ){
			array_walk_recursive( $data, array( $this, 'debugFilter' ) );
		}
		if( !empty($title) ){
			echo '<h3>'. $title .'</h3>';
		}
		echo '<pre>';
			print_r($data);
		echo '</pre>';
	}

	//------------------------------------//--------------------------------------//

	/**
	 * Debug filter
	 *
	 * Debud filter special characters.
	 * 
	 * @param array $data The data to filter.
	 * @return array 
	 */
	public function debugFilter(&$data){
		$data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
	}

}