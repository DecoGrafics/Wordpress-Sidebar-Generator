<?php 
add_action( 'ssgm:init', function(){

	// Make available all generated sidebars.
	new SmkSidebar\RegisterSidebars;

	// Create the page, menu and controls
	new SmkSidebar\Foundation;

	// Create and apply conditions
	new SmkSidebar\Conditions;

	// Get ads
	if( SSGM()->isPluginPage() ){

		new SmkSidebar\Ads;
	
	}

	add_shortcode( 'smk_sidebar', 'smk_sidebar_shortcode' );

});