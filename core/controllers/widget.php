<?php
/**
 * Widget controller
 *
 * @package Naked Feature
 * @since 0.1
 */

class naked_feature_widget_controller extends nu_singleton
{
	protected function __construct()
	{
		$this->views_dir 	= dirname(__DIR__) . '/views/';

		// initialize the widget
		add_action( 'widgets_init', array( &$this, 'init_widget' ) );
		add_action( 'wp_footer', array( &$this, 'load_js_template' ), 0 );
	}


	public function init_widget() 
 	{	
  	 register_widget('feature_section_widget');
    }


    /**
 	 * Loads the js template that will be used to render the feature item(s)
	 *
	 * @todo Allow the theme to override this... i.e. if a template file exists in the
	 * the theme that should be loaded instead of this one
	 */
	public function load_js_template()
	{
		global $naked_feature;

        $widget_id = feature_section_widget::$base_id;

		// check if the widget is used before enqueuing the script
        if( !empty( $naked_feature['active_widgets'] ) ) {
    	   include(  $this->views_dir . 'widget/feature-tpl.inc' );
        }
	}
}