<?php 
require_once SMK_SIDEBAR_PATH . 'warnings/abstract-warning.php';

class SMK_SIDEBAR_NoPlugin_Warning extends SMK_SIDEBAR_Astract_Warning{

	public function notice(){
		
		$output = '';
		
		if( count( $this->data ) > 1 ){
			$message = __( 'Please install and activate the following plugins:', 'smk-sidebar-generator' );
		}
		else{
			$message = __( 'Please install and activate this plugin:', 'smk-sidebar-generator' );
		}

		$output .= '<h2>' . $message .'</h2>';


		$output .= '<ul class="ssgm-required-plugins-list">';
			foreach ($this->data as $plugin_slug => $plugin) {
				$plugin_name = '<div class="ssgm-plugin-info-title">'. $plugin['plugin_name'] .'</div>';

				if( !empty( $plugin['plugin_uri'] ) ){
					$button = '<a href="'. esc_url_raw( $plugin['plugin_uri'] ) .'" class="ssgm-plugin-info-button" target="_blank">'. __( 'Get the plugin', 'smk-sidebar-generator' ) .'</a>';
				}
				else{
					$button = '<a href="#" onclick="return false;" class="ssgm-plugin-info-button disabled">'. __( 'Get the plugin', 'smk-sidebar-generator' ) .'</a>';
				}

				$output .= '<li>'. $plugin_name . $button .'</li>';
			}
		$output .= '</ul>';

		return $output;
	}

}