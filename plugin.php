<?php
/*
Plugin Name: Naked Feature
Description: Provides 'features' section(s) that can be placed anywhere on the site and a backend to manage those features. This plugin (like the rest of the 'naked' series) utilizes classes found in the 'naked-utils' plugin so make sure that is installed.
Author: Andre Deutmeyer
Version: 0.1.3
*/

define( 'NF_PLUGIN_VERSION', '0.1.3'); // for static resources

// include files that will be used on activation
require_once( dirname(__FILE__) . '/core/config/database.php' );


/** 
 * @note
 *	files are added through the 'plugins_loaded' hook so that we
 *	can ensure that naked-utils is loaded before trying to use
 * 	classes and function declared there. 
 */
add_action( 'plugins_loaded', 'naked_feature_init' );

// warn if naked-utils or JSON-API is not installed
add_action( 'admin_notices', 'naked_feature_activation_notice');


/**
 * Initializes all files
 */
function naked_feature_init()
{
	// only load these if naked-utils and json-api is active otherwise we get all sorts of errors
	if( class_exists( 'nu_singleton' ) ) {

		// include the models
		require_once( dirname(__FILE__) . '/core/models/feature.php' );

		// include the controllers
		require_once( dirname(__FILE__) . '/core/controllers/feature.php' );
		require_once( dirname(__FILE__) . '/core/controllers/widget.php' );
		require_once( dirname(__FILE__) . '/core/controllers/json-api/feature.php' );
		require_once( dirname(__FILE__) . '/core/controllers/settings.php' );

		// inclue the classes
		require_once( dirname(__FILE__) . '/core/inc/sections.class.php' );
		require_once( dirname(__FILE__) . '/core/inc/section.class.php' );
		require_once( dirname(__FILE__) . '/core/inc/feature_widgets.class.php' );

		// instantiate the controllers (if needed)
		naked_feature_controller::get_instance();
		naked_feature_widget_controller::get_instance();

		if( is_admin() )
			naked_feature_settings_controller::get_instance();
	}
}


function naked_feature_activation_notice()
{
	$json_api_exists = class_exists( 'JSON_API' );

	if( !defined ( 'NAKED_UTILS' ) ) {
		// warn if naked-utils is not active
		if( current_user_can( 'install_plugins' ) ) {
			echo '<div class="error"><p>';
      printf( __('Naked Feature requires Naked Utils. Please make sure that you have installed and activated <a href="%s">Naked Utils</a>. They are like peas in a pod.', 'naked_feature' ), '#' );
      echo "</p></div>";
		}
	}
	
	if( !$json_api_exists ) {
		// warn if json-api is not active
		if( current_user_can( 'install_plugins' ) ) {
			echo '<div class="error"><p>';
	    printf( __('Naked Feature requires JSON-API to work. Please make sure that you have installed and activated <a href="%s">JSON-API</a>. They are like peas in a pod.', 'naked_feature' ), 'http://wordpress.org/extend/plugins/json-api/' );
	    echo "</p></div>";
	  }
	}
	elseif( $json_api_exists ) {
		// warn if the feature controller has not been activated
		$active_controllers = explode(',', get_option('json_api_controllers', 'core'));
		if( !in_array( 'feature', $active_controllers ) ) {
			echo '<div class="error"><p>';
      		printf( __( 'You must activate the "Features" controller on the <a href="%s">JSON-API options page</a> in order to use naked_feature', 'naked_feature' ), get_admin_url( '', 'options-general.php?page=json-api' ) );
      		echo "</p></div>";
		}
	}
}


register_activation_hook( __FILE__ , array( 'naked_feature_db_config', 'init' ) );
register_deactivation_hook( __FILE__ , array( 'naked_feature_db_config', 'delete' ) );