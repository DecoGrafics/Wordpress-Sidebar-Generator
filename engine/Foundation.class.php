<?php
namespace SmkSidebar;

class Foundation {

	public function __construct(){
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'registerSetting' ) );
		add_action( 'wp_ajax_smk-sidebar-generator_load_equalto', array( $this, 'equaltoAjax' ) );
		add_filter( 'plugin_action_links_' . SMK_SIDEBAR_PLUGIN_BASENAME, array( $this, 'addManageLink' ) );

		add_action( 'smk_sidebars_list_form_begin', array( $this, 'addHiddenFields' ) );
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
	 * Add manage link
	 *
	 * Applied to the list of links to display on the plugins page (beside the activate/deactivate links). 
	 *
	 * @param array Old links for this plugin
	 * @return array New links for this plugin 
	 */
	function addManageLink( $links ) {
		$links[] = '<a href="'. esc_url( get_admin_url( null, 'admin.php?page='. SSGM()->config('slug') ) ) .'">'. 
			__( 'Manage', 'smk-sidebar-generator' ) 
		.'</a>';
		return $links;
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
	 * Add hidden fields
	 *
	 * Add hidden fields in form.
	 *
	 * @hook smk_sidebars_list_form_begin
	 * @return void 
	 */
	public function addHiddenFields(){
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

		// Single sidebar template
		echo '<div class="smk-sidebars-list-template" style="display: none;">';
			$this->sidebarListTemplate();
		echo '</div>';

		// Single condition template
		echo '<div class="smk-sidebars-condition-template" style="display: none;">';
			echo $this->aSingleCondition('__cond_name__', '', 0, 'all');
		echo '</div>';

		echo '<div class="wrap sbg-clearfix">';
		
			$this->pageOpen();
				do_action( 'smk_sidebars_list_form_begin' );
				
				$this->buildSections();

				do_action( 'smk_sidebars_list_form_end' );
			$this->pageClose();

		echo '<div>'; // .wrap
		// Debug start
		
		// $this->debug( SSGM()->sidebars->getActive(), 'Active' );
		// $this->debug( SSGM()->sidebars->getConditions(), 'Filtered Conditions' );
		// $this->debug( SSGM()->sidebars->allStaticSidebars(), 'All static sidebars' );
		// $this->debug( SSGM()->sidebars->getStatic(), 'All Generated sidebars' );
		// $this->debug( SSGM()->sidebars->getGenerated(), 'All Generated sidebars' );
		// $this->debug( SSGM()->sidebars->getAll(), 'All Registered sidebars' );
		// $this->debug( SSGM()->sidebars->sidebarWidgets(), 'Sidebars' );
		// $this->debug( SSGM()->sidebars->widgetsTypes(), 'Types' );
		// $this->debug( SSGM()->sidebars->widgetsOptions(), 'Options' );
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
		$html = '<h2>'. SSGM()->config( 'plugin_name' );
		$html .= $this->buildTopMenu();
		$html .= '</h2>';
		$html .= '<div class="smk-sidebars-container">';
		$html .= '<div>'. $this->addNewButton() .'</div>';
		$html .= '<form method="post" action="options.php" class="smk-sidebar-generator_main_form">';
		
		do_action( 'smk_before_sidebars_container' );

		echo $html;
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
		$html .= '</div>'; // .smk-sidebars-container
		
		echo $html;

		do_action( 'smk_after_sidebars_container' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sections
	 *
	 * @return array 
	 */
	public function sections(){
		return apply_filters( 'smk_sidebar_generator_sections', array(
			array(
				'id' => 'sidebars',
				'title' => __( 'Sidebars', 'smk-sidebar-generator' ),
				'callback' => array( $this, 'sectionSidebars' ),
			),
			array(
				'id' => 'settings',
				'title' => __( 'Settings', 'smk-sidebar-generator' ),
				'callback' => array( $this, 'sectionSettings' ),
			),
			array(
				'title' => __( 'Official page', 'smk-sidebar-generator' ),
				'url' => 'https://wordpress.org/plugins/smk-sidebar-generator/',
			),
		));
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Sidebars section callback
	 *
	 * @return string 
	 */
	public function sectionSidebars(){
		do_action( 'smk_before_sidebars_list' );

		$this->allSidebarsList();

		do_action( 'smk_after_sidebars_list' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Settings section callback
	 *
	 * @return string 
	 */
	public function sectionSettings(){
		do_action( 'smk_before_sidebars_settings' );

		echo 'Settings section';

		do_action( 'smk_after_sidebars_settings' );
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Top menu
	 *
	 * @return string 
	 */
	public function buildTopMenu(){
		$links = '';
		$sections = $this->sections();
		if( !empty( $sections ) ){
			foreach ($sections as $section) {
				$id = !empty( $section[ 'id' ] ) ? ' data-menu-item-id="'. esc_attr( $section[ 'id' ] ) .'"' : '';
				$url = !empty( $section[ 'url' ] ) ? ' href="'. esc_url_raw( $section[ 'url' ] ) .'" target="_blank"' : ' href="#"';
				$links .= apply_filters( 
					'smk_sidebar_generator_sections_menu_item', 
					'<a '. $url . $id .'>'. $section[ 'title' ] .'</a>', 
					$section 
				);
			}
		}
		return $links;
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * Build sections
	 *
	 * @return string 
	 */
	public function buildSections(){
		$sections = $this->sections();
		if( !empty( $sections ) ){
			foreach ($sections as $section) {
				if( !empty( $section[ 'callback' ] ) && is_callable( $section[ 'callback' ] ) ){
					$id = !empty( $section[ 'id' ] ) ? esc_url_raw( $section[ 'id' ] ) : '';
					echo '<div class="smk-section" data-section-id="'. $id .'">';
					
					call_user_func( $section[ 'callback' ] );
					$this->saveButton( 'sidebars' === $section[ 'id' ] );
					
					echo '</div>';
				}
			}
		}
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * "Add new" button
	 *
	 * @return string 
	 */
	public function addNewButton(){
		return '<span class="button add-new-sidebar" data-sidebars-prefix="'. SSGM()->prefix() .'">'. __('Add new', 'smk-sidebar-generator') .'</span>';
	}

	//------------------------------------//--------------------------------------//
	
	/**
	 * "Save" button
	 *
	 * @return string 
	 */
	public function saveButton( $show_add_new = false ){
		echo '<div class="sbg-submit-block">
			'. ( $show_add_new ? $this->addNewButton() : '' ) .'
			<input type="submit" name="submit" class="save-sidebars button button-primary" value="'. __( 'Save sidebars', 'smk-sidebar-generator' ) .'">
		</div>';
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
		$all = SSGM()->sidebars->getGenerated();
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

				// Conditions placeholder
				$the_sidebar .= SSGM()->html->input(
					'', // ID
					$name. '[conditions]', 
					'', 
					array(
						'type' => 'hidden',
					)
				);

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
								$the_sidebar .= $this->conditionsEmpty();
							}
						$the_sidebar .= '</div>'; //.created-conditions
					
					$the_sidebar .= '<div class="smk-sidebar-row sbg-clearfix">';
						$the_sidebar .= '<div class="grid-12">';
							$the_sidebar .= '<label>'. __( 'Select a new condition for:', 'smk-sidebar-generator' ) .'</label>';
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

	public function conditionsEmpty(){
		$the_sidebar = '<div class="condition-placeholder sbg-clearfix">';
			$the_sidebar .= '<span>'. __( 'Add a new condition using the following field', 'smk-sidebar-generator' ) .'</span>';
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
		$sidebars = SSGM()->sidebars->getStatic();
		$static   = '';

		if( !empty($sidebars) ){	
			foreach ($sidebars as $key => $value) {
				$static[ $key ] = $value['name'];
			}
		}

		$replace = !empty( $sidebar_data['replace'] ) ? $sidebar_data['replace'] : array();

		return '<label>'. __('Sidebars to replace:', 'smk-sidebar-generator') .'</label>'. 
			SSGM()->html->select(
				'', // ID
				$name. '[replace][]', 
				$replace,
				array(
					'multiple' => 'multiple',
					'options'  => $static,
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

		$nr = absint( $index );
		$saved = '';

		if ( !empty( $sidebar_data ) ) {
			if ( !empty( $sidebar_data['conditions'][ $nr ]['if'] ) ) {
				$saved = $sidebar_data['conditions'][ $nr ]['if'];
			}
		}

		return '<span class="condition-label">'. __('Replace if', 'smk-sidebar-generator') .' </span>'
			.'<span class="cond-info">'. apply_filters( 'smk_sidebar_condition_main_title', '', $saved ) .'</span>'.
			SSGM()->html->input(
				'', // ID
				$name. '[conditions]['. $nr .'][if]', 
				$saved, 
				array(
					'type' => 'hidden',
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
		return SSGM()->html->select(
				'', // ID
				'', //Name
				'post_type::post', //Value
				array(
					'options' => apply_filters( 'smk_sidebar_condition_selector', array() ),
					'class' => array('main-condition-selector'),
					'style'    => 'width: 300px',
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
		$nr = absint( $index );
		$first_cond_value = '';
		$saved = '';
		if ( !empty( $sidebar_data ) ) {
			if ( !empty( $sidebar_data['conditions'][ $nr ]['if'] ) ) {
				$first_cond_value = $sidebar_data['conditions'][ $nr ]['if'];
			}

			if ( !empty( $sidebar_data['conditions'][ $nr ]['equalto'] ) ) {
				$saved = $sidebar_data['conditions'][ $nr ]['equalto'];
			}
		}

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
					'data-main-cond' => $first_cond_value,
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
		
		// TODO: Implement a proper field to search for items based on first condidition
		//       The following code is not working properly

		$the_type       = explode('::', $type);
		$options        = array();
		// $all_conditions = smk_sidebar_conditions_filter();

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
	 * Sidebar Template
	 *
	 * Create the template for new sidebars generation
	 *
	 * @return string The HTML.
	 */
	public function sidebarListTemplate($echo = true){
		$sidebar_data = array( 
			'name'        => sprintf( __('Widgets Area %s', 'smk-sidebar-generator'), '__index__' ),
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