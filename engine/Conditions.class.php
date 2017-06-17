<?php
namespace SmkSidebar;

class Conditions{
	
	public function __construct(){
		add_filter( 'smk_sidebar_condition_selector', array( $this, 'conditionSelector' ) );
		add_filter( 'smk_sidebar_condition_main_title', array( $this, 'mainConditionTitle' ), 20, 2 );
		add_action( 'smk_sidebars_list_form_end', array( $this, 'debug' ) );
	}

	public function conditionSelector( $cond ){
		$cond['post_type'] = $this->createGroup( __( 'Single post type', 'smk-sidebar-generator' ), $this->getPostTypes() );
		$cond['taxonomy'] = $this->createGroup( __( 'Taxonomy', 'smk-sidebar-generator' ), $this->getTaxonomies() );

		return $cond;
	}

	public function mainConditionTitle( $content, $save ){
		$cond = apply_filters( 'smk_sidebar_condition_selector', array() );
		$the_type = explode('::', $save);
		if( !empty($the_type[0]) ){
			$content = $cond[ $the_type[0] ]['label'] .': <em>'. $cond[ $the_type[0] ]['options'][ $save ] .'</em>';
		}
		return $content;
	}

	/*
	-------------------------------------------------------------------------------
	Helpers
	-------------------------------------------------------------------------------
	*/
	public function createGroup( $group_name, $options ){
		return array(
			'label' => $group_name,
			'options' => $options,
		);
	}

	/*
	-------------------------------------------------------------------------------
	Queries
	-------------------------------------------------------------------------------
	*/
	public function getPostTypes(){
		$prefix = 'post_type::';
		$pt_args = array(
			'public'   => true,
			'_builtin' => false
		);
		$pt = array(
			$prefix . 'post' => _x('Posts', 'Post type name', 'smk-sidebar-generator'),
			$prefix . 'page' => _x('Pages', 'Post type name', 'smk-sidebar-generator'),
		);
		$post_types = get_post_types( $pt_args, 'objects' );
		if( !empty($post_types) ){
			foreach ($post_types as $post_type) {
				$pt[ $prefix . $post_type->name ] = $post_type->label;
			}
		}
		return $pt;
	}

	public function getTaxonomies(){
		$prefix     = 'taxonomy::';
		$taxonomies = get_taxonomies( array(), 'objects' );
		$tax_list   = array();
		// $exclude    = array( 
		// 	'nav_menu', 
		// 	'link_category', 
		// 	'post_format', // Maybe later
		// 	'edd_log_type', // Easy Digital Downloads logs tax
		// );

		if( !empty($taxonomies) ){
			foreach ($taxonomies as $taxonomy) {
				if( !empty($taxonomy->public) ){
					$tax_list[ $prefix . $taxonomy->name ] = $taxonomy->label . '( '. $taxonomy->name .' )';
				}
			}
		}

		return $tax_list;
	}

	/*
	-------------------------------------------------------------------------------
	Debug
	-------------------------------------------------------------------------------
	*/
	public function debug(){
		echo '<pre>';
		print_r( apply_filters( 'smk_sidebar_condition_selector', array() ) );
		echo '</pre>';
	}


}