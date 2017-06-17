<?php
/**
 * Ads
 *
 * Display ads from zerowp.com ONLY on plugin page from admin side.
 * This does not affect the frontend or other pages from admin.
 *
 */
namespace SmkSidebar;

class Ads{

	public $html;
	
	public function __construct(){
		$this->html = $this->getAds();

		add_action( 'smk_after_sidebars_container', array( $this, 'injectAds' ), 99 );
	}

	public function getAds(){
		
		// TODO: Cache request for 12 hours, for example. 

		$html = wp_remote_get( 'http://zerowp.com/special-rest-access/?mode=ads&plugin=smk-sidebar-generator' );
		$html = wp_remote_retrieve_body( $html );

		return json_decode( $html );
	}

	public function injectAds(){

		// TODO: Manage ads

		echo '<pre>';
		print_r( $this->html );
		echo '</pre>';
	}

}