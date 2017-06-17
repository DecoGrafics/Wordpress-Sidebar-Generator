<?php 
/**
 * Sidebar shortcode
 *
 * Callback for 'smk_sidebar' sortcode
 *
 * @param array $atts Shortcode atributes 
 * @return string 
 */
function smk_sidebar_shortcode( $atts ){
	extract( shortcode_atts( array(
		'id' => null,
	), $atts ) );

	dynamic_sidebar($id);
}

/**
 * Plugin version
 *
 * Get the current plugin version.
 * 
 * @return string 
 */
function smk_sidebar_version(){
	return SSGM()->version;
}

/**
 * Plugin root path
 *
 * @return string 
 */
function smk_sidebar_path(){
	return SSGM()->rootPath();
}

/**
 * Plugin root URI
 *
 * @return string 
 */
function smk_sidebar_uri(){
	return SSGM()->rootURL();
}

/**
 * Print the sidebar content by ID
 *
 * @param string $id Sidebar ID
 * @return string The sidebar content
 */
function smk_sidebar($id){
	dynamic_sidebar($id);
}

/**
 * Get a list of all sidebars: sidebar_id => sidebar_name
 *
 * @return array|bool(false) 
 */
function smk_get_all_sidebars(){
	_deprecated_function( __FUNCTION__, '4.0', 'SSGM()->sidebars->getNamesList()' );
	return SSGM()->sidebars->getNamesList();
}

/**
 * Debug fn
 *
 * @return string 
 */
function ssgm_d( $val, $title = false ){
	echo '<pre>';

	if( $title ){
		echo '<p><strong>'. $title .'</strong></p>';
	}

	print_r( $val );

	echo '</pre>';
}