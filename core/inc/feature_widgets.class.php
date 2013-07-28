<?php

/**
 * feature_section_widget
 * 
 * Used to place a feature section on the page
 */
class feature_section_widget extends WP_Widget
{
	public static $base_id = 'feature-section';
	public $feature_div_id = '';
 
  public function __construct()
  {
  	$base_id = self::$base_id;
  	$name = __( 'Feature Section', 'naked_feature' );

  	$widget_ops = array(
  		// classname that will be added to the widget on the frontend
    	'classname' => 'feature-section',
    	// description displayed in admin
    	'description' => __( "Used to place a feature section", 'naked_feature' ) 
    );

  	parent::__construct( $base_id, $name, $widget_ops );
  }

	/**
	 * Displays the Widget
	 */
	public function widget( $args, $instance )
	{
		global $naked_feature;

		extract( $args );

		// set the widget as active in our global variable
		$naked_feature['active_widgets'][] = $widget_id;

		$section_id = $instance['feature_section_id'];
		$section_obj = new naked_feature_section( $section_id );

		// merge saved values with default values
		$instance = $this->_get_widget_options( $instance );

		// prepare template variables
		$section 			= strtolower( $section_obj->delta );
		$title 				= $instance['title'];
		$hide_title 		= isset( $instance['hide_title'] ) ? $instance['hide_title'] : false;
		$style 				= $instance['style'];
		$features 			= $this->_get_features( $section_obj, $style );
		$display_count 		= $instance['feature_display_count'];
		$show_post_content 	= $instance['show_post_content'];
		$show_post_title = $instance['show_post_title'];
		$link_to_feature	= $instance['link_to_feature'];
		$ajax_load 			= $instance['ajax_load'];
		$more_link_href		= $instance['more_link_href'];
		$more_link_text		= $instance['more_link_text'];


		// set up the classes
		$classes = $this->_get_classes( $instance );
		$wrapper_classes = isset( $classes[0] ) ? $classes[0] : '';

		// get the view
		$tpl_path = dirname(__DIR__) . '/views/widget/feature-section.php';
		include( $tpl_path );
	}

	/**
     * Saves the widgets settings.
     */
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;

		$keys = array(
			'title' => 'string',
			'hide_title' => 'int',
			'feature_section_id' => 'int',
			'feature_display_count' => 'int',
			'feature_section_classes' => 'string',
			'style' => 'string',
			'show_post_content' => 'int',
			'show_post_title'	=> 'int',
			'link_to_feature' => 'int', // whether to link to the original post
			'more_link_href' => 'string', // link to an archive page or similar
			'more_link_text' => 'string', // text for the above link
			'ajax_load' => 'int',
		);

		foreach( $keys as $key=>$data_type ) {
			$instance[ $key ] = $this->sanitize( $new_instance[ $key ], $data_type ); 
		}
		
		return $instance;
	}


	private function sanitize( $data, $data_type )
	{
		switch( $data_type ) {
			case 'int':
				$data = intval( $data );
				break;
			case 'string':
				// $data = trim( $data, '\n\r\t\0\x0B" "' );
				$data = trim( $data );
				$data = strip_tags( $data );
				break;
		}

		return $data;
	}


	/**
	 * Creates the edit form for the widget.
	 *
	 */
	public function form( $instance )
	{
		$instance 	= $this->_get_widget_options( $instance );	
		$sections 	= naked_feature_sections::get_instance()->get_sections();
		$styles 	= $this->_get_styles();

		// get the view
		$tpl_path = dirname(__DIR__) . '/views/widget/feature-section-form.php';
		include( $tpl_path );
	}


	/**
	 Helpers
	 */

	private function _get_features( $section, $style )
	{
		$features = $section->features;
		if( 'carousel-video' == $style ) {
			foreach( $features as $feature ) {
				// get the video from the content
				$feature->video = $this->_get_video( $feature->content );
				$feature->content = $this->_clean_content( $feature->content );
			}
		}
		return $features;
	}


	/**
	 * _get_video
	 *
	 * Parses the post content and extracts the embedded video iframe/embed/object
	 *
	 * @return (str) html for the video element
	 */
	private function _get_video( $content )
	{
		preg_match( '/<iframe[^>]*>(.*?)<\/iframe>|<embed[^>]*>|<object[^>]*>(.*?)<\/object>/i', $content, $matches );
		
		$video = $matches ? $matches[0] : '';
		
		return $video;
	}


	/**
	 * _clean_content
	 *
	 * Removes all iframes/embeds/objects from the post content. This is usually used in
	 * tandem with self::_get_video()
	 *
	 * @param $content (str) the original post content
	 *
	 * @return (str) the cleaned post content
	 */
	private function _clean_content( $content )
	{
		// strip out any embedded stuff from the body content
		$content = preg_replace( '/<iframe[^>]*>(.*?)<\/iframe>|<embed[^>]*>|<object[^>]*>(.*?)<\/object>/i', '', $content );

		// remove leading/trailing whitespace
    $content = trim( $content );

		return $content;
	}


	private function _get_widget_options( $instance )
	{
		// Defaults
		$defaults = array(
			'title' => '',
			'hide_title' => 0,
			'feature_section_id' => 0,
			'feature_display_count' => 1,
			'feature_section_classes' => '',
			'style' => 'grid',
			'show_post_content' => 0,
			'show_post_title' => 1,
			'link_to_feature' => 1,
			'more_link_href' => '',
			'more_link_text' => '',
			'ajax_load' => 0,
		);

		// set up template variables
		$instance = wp_parse_args( (array) $instance, $defaults );
		return $instance;
	}


	private function _get_classes( $instance )
	{
		$classes = array(); // array of classes to return
		$temp = $instance['feature_section_classes'];
		$temp = preg_split( "/\n/", $temp );

		$special = array();
		foreach ( $temp as $value ) {
			if( $value )
				$special = array_merge( $special, explode( '=', $value ) );
		}

		$temp = $special;

		// default class values
		$default_classes = $instance['ajax_load'] ? 'placeholder feature-item' : 'feature-item';

		/**
		 * Add special class values. At this point $temp looks something like this:
		 *
		 * 	array( 
		 *		0 => pos1 (pos1 is an int), 
		 *		1 => classes for pos1 (this value is a string) 
		 * 	)
		 *
		 * So we need to fix it so that it looks like this:
		 *
		 * 	array( 
		 *		pos1 => classes for pos1, 
		 * 	)
		 *
		 */
		foreach( $temp as $i => $value ) {
			if( $i % 2 == 1 ) {
				$pos = $temp[ $i-1 ];
				$classes[ $pos ] = trim( $value ) . ' ' . $default_classes;
			}
		}

		// make sure that the default classes are set for all the features that will
		// be displayed
		$display_count = $instance['feature_display_count'];
		for( $i=1; $i<=$display_count; $i++ ) {
			if( $default_classes && ( !isset($classes ) || !isset( $classes[ $i ] ) ) )
				$classes[$i] = $default_classes;
		}

		return $classes;
	}


	/**
	 * _get_styles
	 *
	 * @return (array) list of styles that can be used
	 */
	private function _get_styles()
	{
		return array(
			'grid',
			'grid2',
			'carousel',
			'carousel-video',
		);
	}

}