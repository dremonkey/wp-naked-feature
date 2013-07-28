<?php


class naked_feature_settings_controller extends nu_settings
{
	/*** private static variables ***/
	private static $_debug 			= false; // toggle debug information
	private static $_instance 	= null; // stores class instance


	public $options_page_key; // used to create the page slug
	public $options_group;		// ... can't remember ... - Andre
	public $options_key;			// var name used to store the options in the db
	public $cap_level;				// the user capability level required for access
	public $page_title; 			// the settings page title
	public $menu_title;				// the settings page menu title


	/**
	 * get_instance
	 *
	 * Retrieves an instance of this class or creates a new one if it doesn't exist
	 */
	static function get_instance() {

		if( null === self::$_instance )
			self::$_instance = new self();

		return self::$_instance;
	} 


	private function __construct()
	{
		// initialize the settings page
		$this->init_settings();
	}


	/**
	 * Sets the values of all class variables. Called by self::init_settings().
	 */
	public function set_class_vars()
	{
		$this->options_page_key = 'naked_feature';
		$this->options_group 		= 'naked_feature_options_group';
		$this->options_key 			= 'naked_feature_options';
		$this->cap_level 				= 'manage_options';
		$this->page_title 			= __( 'Features Settings', 'naked_feature' );
		$this->menu_title 			= __( 'Features', 'naked_feature' );
	}


	/**
	 * Called by self::reg_setting_sections().
	 *
	 * @return (array) A list of setting sections.
	 */
	public function get_setting_sections()
	{
		$sections = array(
    	'section_general' => array(
    		'title' 		=> 'General',
    		'callback' 	=> array( &$this, 'get_section_desc' ),
    		'page'			=> $this->options_page_key,
    	),
    );

		$sections = apply_filters( 'nf_setting_sections', $sections );

		return $sections;
	}


	/**
	 * Called by self::reg_setting_fields().
	 *
	 * @return (array) A list of setting fields
	 */
	public function get_setting_fields()
	{
		$cbs1 = $this->_get_checkboxes( 'content_types' );
		$cbs2 = $this->_get_checkboxes( 'image_sizes' );

		$fields = array(
			// set post type to enable feature support for
    	'content_types' => array(
    		'title' 		=> 'Content Types',
    		'callback' 	=> array( &$this, 'build_form_fields' ),
    		'page'		  => $this->options_page_key,
    		'section' 	=> 'section_general',
    		'args' => array(
    			'id' 		=> 'feature_content_types',
    			'type' 	=> 'multi-checkbox-horizontal',
    			'desc' 	=> __( 'Select the content types that you would like to enable support for. This will add the Feature Sections metabox to the edit page for those content types.', 'naked_feature' ),
    			'options' => $cbs1,
    		)	
    	),
    	// set images sizes available to features
    	'image_sizes' => array(
    		'title' 		=> 'Image Sizes',
    		'callback' 	=> array( &$this, 'build_form_fields' ),
    		'page' 			=> $this->options_page_key,
    		'section' 	=> 'section_general',
    		'args' => array(
    			'id'		=> 'feature_image_sizes',
    			'type' 	=> 'multi-checkbox-vertical',
    			'desc' 	=> __( 'List of sizes that you want available in your feature section(s). This is used to retrieve the correct image source urls.', 'naked_feature' ),
    			'options' => $cbs2,
    		)
    	),
		);

		return $fields;
	}


	/**
	 Helpers
	 */


	/**
	 * @return (array) the default options values 
	 */
	public function get_default_option_values()
	{
		return array(
			'feature_image_sizes' => array('large'),
    	'feature_content_types' => array('post'),
		);
	}


	/**
	 * Retrieves a section description
	 *
	 * We don't need a section desc so just returning an empty string
	 */
	public function get_section_desc( $args )
	{
		return '';
	}


	private function _get_checkboxes( $type )
	{
		switch ($type) {
			case 'content_types':
				// get content types
				$args = array(
					'public' => true,
					'exclude_from_search' => false,
				);
				$checkboxes = get_post_types( $args, 'names' );
				break;
			
			case 'image_sizes':
				$checkboxes = get_intermediate_image_sizes();
				$checkboxes[] = 'full';
				break;
		}

		return $checkboxes;
	}
}


/**
 Template Tags
 */

function naked_feature_get_option( $option )
{
	$settings = naked_feature_settings_controller::get_instance();
	return $settings->get_option_value( $option );
}

/**
 * @todo clean this up later when I am sure that I didn't break anything
 */

// function naked_gallery_get_option( $option ) 
// {
// 	$gallery = new naked_gallery_settings_controller();
// 	return $gallery->get_option_value( $option );
// }


// class naked_feature_settings_controller extends nu_singleton
// {
// 	private static $debug = false;

// 	public static $options_page_key  	= 'naked_feature';
// 	public static $options_group_key 	= 'naked_feature_options_group';
// 	public static $options_key 		 		= 'naked_feature_options';

// 	protected function __construct()
// 	{
// 		$this->views_dir = dirname(__DIR__) . '/views/';

// 		add_action( 'admin_menu', array( &$this, 'add_options_menu' ) );
// 		add_action( 'admin_init', array( &$this, 'reg_settings' ) );
// 	}


// 	public function add_options_menu()
// 	{
// 		$page_title = 'Features Settings';
// 		$menu_title = 'Features';
// 		$capability = 'manage_options';
// 		$menu_slug 	= self::$options_page_key;
// 		$callback 	= array( &$this, 'get_options_page' );

//   	add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback );
// 	}


// 	public function get_options_page() 
// 	{
// 		$page_key = self::$options_page_key;
// 		$options_group = self::$options_group_key;

//     $tpl_path = $this->views_dir . 'settings.php';
// 		include( $tpl_path );
// 	}


// 	public function reg_settings()
// 	{
// 		// Register the settings. All settings will be stored in one options field as an array.
//    		register_setting( self::$options_group_key, self::$options_key );

// 	    // Add the setting section(s)
// 	    $sections = array(
// 	    	'section_general' 	=> array(
// 	    		'title' 		=> '',
// 	    		'callback' 		=> array( &$this, 'get_section_desc' ),
// 	    		'page'			=> self::$options_page_key,
// 	    	),
// 	    );
    
// 	    foreach( $sections as $id=>$section ) {
// 	    	extract( $section );
// 	    	add_settings_section( $id, $title, $callback, $page );	
// 	    }

// 	    $fields = array(
// 	    	// set post type to enable feature support for
// 	    	'content_types' => array(
// 	    		'title' 		=> 'Content Types',
// 	    		'callback' 		=> array( &$this, 'build_form_fields' ),
// 	    		'page'		  	=> self::$options_page_key,
// 	    		'section' 		=> 'section_general',
// 	    		'args' => array(
// 	    			'id' 	=> 'feature_content_types',
// 	    			'type' 	=> 'checkbox_horizontal',
// 	    			'desc' 	=> __( 'Select the content types that you would like to enable support for. This will add the Feature Sections metabox to the edit page for those content types.', 'naked_feature' ),
// 	    		)	
// 	    	),

// 	    	'image_sizes' => array(
// 	    		'title' 		=> 'Image Sizes',
// 	    		'callback' 		=> array( &$this, 'build_form_fields' ),
// 	    		'page' 			=> self::$options_page_key,
// 	    		'section' 		=> 'section_general',
// 	    		'args' => array(
// 	    			'id'	=> 'feature_image_sizes',
// 	    			'type' 	=> 'checkbox_horizontal',
// 	    			'desc' 	=> __( 'List of sizes that you want available in your feature section(s). This is used to retrieve the correct image source urls.', 'naked_feature' ),
// 	    		)
// 	    	),
// 	    );

//     	foreach( $fields as $id=>$field ) {
// 			extract( $field );
//     		add_settings_field( $id, $title, $callback, $page, $section, $args );
//     	}
// 	}


// 	public function build_form_fields( $args ) 
// 	{
// 		extract( $args );

// 		// prepare the template variables
// 		$name 		= self::$options_key . "[$id]";
// 		$options 	= self::get_options();

// 		if( $id == 'feature_content_types' ) {

// 			// get content types
// 			$args = array(
// 				'public' => true,
// 				'exclude_from_search' => false,
// 			);
// 			$checkboxes = get_post_types( $args, 'names' );
// 			$values 	= $options[ $id ];

// 			if( self::$debug )
// 				nu_debug::var_dump( $values );
// 		}
// 		elseif( $id == 'feature_image_sizes' ) {

// 			$checkboxes = get_intermediate_image_sizes();
// 			$checkboxes[] = 'full';
			
// 			// the image sizes (set by the user )
// 			$values = $options[ $id ];

// 			if( self::$debug )
// 				nu_debug::var_dump( $values );
// 		}
// 		else {
// 			// default form builder (used for text inputs)
// 			$value = esc_attr( $options[ $id ] );
// 		}

// 		$tpl_path = $this->views_dir . 'setting-fields/'. $type .'.php';
// 		include( $tpl_path );
// 	}


// 	public function get_section_desc( $args )
// 	{
// 		echo '';
// 	}


// 	/**
// 	 * Returns an array of all theme options. If an option has been previously set, the 
// 	 * stored option value will be return. If not, then the default option value will be 
// 	 * returned.
// 	 *
// 	 * @uses get_option()
// 	 */
// 	public static function get_options()
// 	{
// 		$options = (array) get_option( self::$options_key );

// 		$defaults = array(
//     	'feature_image_sizes' => array('full'),
//     	'feature_content_types' => array('post'),
//   	);

//     // Merge with defaults
//     $options = array_merge( $defaults, $options );

//   	return $options;
// 	}


// 	/**
// 	 * get_option_value
// 	 *
// 	 * Retrieves a single option value from the 'general' theme options.
// 	 *
// 	 * @return (mixed)
// 	 */
// 	public static function get_option_value( $option )
// 	{
// 		$options = self::get_options();
// 		$value = isset( $options[ $option ] ) ? $options[ $option ] : null;
// 		return $value;
// 	}
// }