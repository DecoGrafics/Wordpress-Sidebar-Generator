<?php 
add_action( 'admin_enqueue_scripts', function(){
	if( SSGM()->isPluginPage() ){

		$id = SSGM()->config( 'id' );

		/* Tipped
		--------------*/
		SSGM()->addStyle( $id . '-tipped', array(
			'src' => SSGM()->assetsURL( 'tipped/tipped.css' ),
			'ver' => '4.6.0',
		));

		SSGM()->addScript( $id . '-tipped', array(
			'src' => SSGM()->assetsURL( 'tipped/tipped.js' ),
			'ver' => '4.6.0',
		));

		/* Select2
		--------------*/
		SSGM()->addStyle( $id . '-select2', array(
			'src' => SSGM()->assetsURL( 'select2/css/select2.min.css' ),
			'ver' => '4.0.3',
		));

		SSGM()->addScript( $id . '-select2', array(
			'src' => SSGM()->assetsURL( 'select2/js/select2.min.js' ),
			'ver' => '4.0.3',
		));

		/* Plugin
		--------------*/
		SSGM()->addStyle( $id .'-admin', array(
			'src' => SSGM()->assetsURL( 'css/styles-admin.css' ),
		));

		SSGM()->addScript( $id .'-admin', array(
			'src' => SSGM()->assetsURL( 'js/config-admin.js' ),
			'deps' => array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-slider'),
			'smk_sidebar_config' => array(
				'conditions_selector' => apply_filters( 'smk_sidebar_condition_selector', array() ), 
			)
		));



		// /* Tipped
		// --------------*/
		// wp_register_style( $id . '-tipped', SSGM()->assetsURL() 'tipped/tipped.css', '', '4.6.0' );
		// wp_enqueue_style( $id . '-tipped' );
		
		// wp_register_script( $id . '-tipped', SSGM()->assetsURL() 'tipped/tipped.js', false, '4.6.0', true );
		// wp_enqueue_script( $id . '-tipped' );
		
		// /* Select2
		// ---------------*/
		// wp_register_style( $id . '-select2', SSGM()->assetsURL() 'select2/css/select2.min.css', '', '4.0.3' );
		// wp_enqueue_style( $id . '-select2' );
		
		// wp_register_script( $id . '-select2', SSGM()->assetsURL() 'select2/js/select2.min.js', false, '4.0.3', true );
		// wp_enqueue_script( $id . '-select2' );
		
		// /* Sidebar generator
		// -------------------------*/
		// wp_register_style( $id, SSGM()->assetsURL() 'styles.css', '', SSGM()->version );
		// wp_enqueue_style( $id );
		
		// wp_register_script( $id, SSGM()->assetsURL() 'scripts.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-slider'), SSGM()->version, true );
		// wp_enqueue_script( $id );

		// wp_localize_script( $id, 'smk_sidebar_config', array(
		// 	'conditions_selector' => apply_filters( 'smk_sidebar_condition_selector', array() ), 
		// ));
	}
});