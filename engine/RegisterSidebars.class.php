<?php
namespace SmkSidebar;

class RegisterSidebars{
	
	public function __construct(){
		add_action( 'widgets_init', array( $this, 'registerGeneratedSidebars' ), 11 );
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
		$sidebars = SSGM()->sidebars->getGenerated();

		//Make sure if we have valid sidebars
		if ( !empty( $sidebars ) ){

			//Register each sidebar
			foreach ($sidebars as $sidebar) {
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

}