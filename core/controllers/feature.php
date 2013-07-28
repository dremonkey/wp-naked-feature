<?php

class naked_feature_controller extends nu_singleton
{
	public static $metabox_id = '_feature_sections';
	public static $capability = 'edit_naked_feature';
	public static $menu_slug = 'features';
	public static $new_section_slug = 'new-feature-section';
	public static $edit_section_slug = 'feature-section';

	protected function __construct()
	{
		// set up global variable
		global $naked_feature;

		$this->plugin_url = plugin_dir_url( dirname(__DIR__) );
		$this->metaboxes_dir = dirname(__DIR__) . '/metaboxes/';
		$this->views_dir 	= dirname(__DIR__) . '/views/';


		// register and load js and css
		add_action( 'init', array( &$this, 'reg_js' ) );
		add_action( 'init', array( &$this, 'reg_css' ) );
		add_filter( 'nu_load_js', array( &$this, 'load_js' ) );
		add_filter( 'nu_load_css', array( &$this, 'load_css' ) );
		add_action( 'nu_after_load_js', array( &$this, 'localize_script' ) );
		

		// JSON API Stuff
		add_filter( 'json_api_controllers' , array( &$this, 'add_jsonapi_controller' ) );
		add_filter( 'json_api_feature_controller_path' , array( &$this, 'set_jsonapi_controller_path' ) );

		if( is_admin() ) {
				
			add_action( 'init', array( &$this, 'admin_reg_metaboxes' ) );

			// Create our admin page
			add_action( 'admin_menu', array( &$this, 'admin_reg_page' ) );

			// Register our AJAX handlers
			add_action( 'wp_ajax_edit_section', array( &$this, 'ajax_edit_section' ) );
			add_action( 'wp_ajax_edit_features_list', array( &$this, 'ajax_edit_features_list' ) );

			// Tap into the save_post hook so that we can save data to the feature_content table
			add_action( 'save_post', array( &$this, 'update_on_save_post' ), 10, 2 );

			// Tap into trash_post and delete_post so that if a post is removed, we remove it from the feature_content table
			add_action( 'trash_post', array( &$this, 'update_on_delete_post'), 10, 1 );
			add_action( 'delete_post', array( &$this, 'update_on_delete_post'), 10, 1 );
		}
	}


	public function reg_js()
	{
		/** 
		 * Register Frontend Scripts
		 */

		// base directory where the script files are
		$base_dir = $this->plugin_url . 'inc/js/';

		// basename for registration. should be similar to the plugin name.
		$basename = 'naked_feature';

		/**
		 * Register Frontend Script
		 */
		$deps = array( 'jquery', 'backbone', 'underscore', 'jquery_smartresize' );
		nu_lazy_load_js::reg_js( $base_dir . 'jquery.feature.js', $deps, NF_PLUGIN_VERSION, true, $basename );


		/**
		 * Register Admin Scripts
		 *
		 * Files prefixed with 'nu' are registered in the naked-utils plugin
		 */
		$deps = array(
			'jquery-ui-sortable',
			'nu-admin-jquery_naked_form_handler'
		);

		nu_lazy_load_js::reg_js( $base_dir . 'admin/feature.js', $deps, NF_PLUGIN_VERSION, false, $basename );
	}


	public function load_js( $scripts )
	{
		// -- Lazy Load Frontend Scripts --
		$widget_id = feature_section_widget::$base_id;

		// check if the widget is used before enqueuing the script. Because we compile the js
    if( is_active_widget( '', '',  $widget_id ) ) {
        $scripts['sitewide'][] = 'naked_feature-jquery_feature';
    }


		// -- Lazy Load Admin Scripts --
		$scripts['admin'][self::$menu_slug][] = 'naked_feature-admin-feature';
		$scripts['admin'][self::$new_section_slug][] = 'naked_feature-admin-feature';

    // load naked_feature-admin-features on all submenu pages
    $sections = $this->_get_sections();
		foreach( $sections as $section ) {
			/** 
			 * the second array key should be the same as the menu_slug that was
			 * registered when you registered the submenu item 
			 */
			$scripts['admin'][ trim( $section->delta .'-features' ) ][] = 'naked_feature-admin-feature';
		}

		return $scripts;
	}


	/**
	 * Used to add javascript variables to a page
	 *
	 * Generates something that looks like this:
	 * 
	 *  <script type="text/javascript">
	 *	var example = {
     *		key: val
	 *	};
	 *  </script>
	 *
	 * @ref http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/
	 */
	public function localize_script()
	{
		if( is_admin() ) {
			// create a nonce for the admin ad forms
			$nonce = wp_create_nonce( 'ajax_naked_feature_nonce' ); 

			$data = array(
				'_naked_feature_nonce' => $nonce
			);

			wp_localize_script( 'naked_feature-admin-feature', 'naked_feature', $data );
		}
		else {

			// get the json_api_base
			$api_base = get_option('json_api_base', 'api');

			$data = array(
				'json_api_url' => trailingslashit( site_url( $api_base ) ),
			);

			wp_localize_script( 'naked_feature-jquery_feature', 'naked_feature_vars', $data );
		}
	}


	public function reg_css()
	{
		// base directory where the script files are
		$base = $this->plugin_url . 'inc/css/';

		// basename for registration. should be similar to the plugin name.
		$basename = 'naked_feature';

		nu_lazy_load_css::reg_css( $base . 'admin.css', null, NF_PLUGIN_VERSION, 'all', $basename );
	}


	/**
	 * Registers and lazy loads the css
	 */
	public function load_css( $styles )
	{
		// lazy load the styles
		$styles['admin'][self::$menu_slug][] = 'naked_feature-admin';
		$styles['admin'][self::$new_section_slug][] = 'naked_feature-admin';

		// load naked_feature-admin on all submenu pages
		$sections = $this->_get_sections();
		foreach( $sections as $section ) {
			// the second array key should be the same as the menu_slug that was registered when you registered the submenu item 
			$styles['admin'][ trim( $section->delta .'-features' ) ][] = 'naked_feature-admin';
		}

		return $styles; 
	}


  /**
	 Admin
   */


	public function admin_reg_page() 
	{
		global $wp_version;

		// Add the new capability required to access this page.
		$this->admin_set_access();

		$page_title = __( 'Features Manager', 'naked_feature' );
		$menu_title = __( 'Features', 'naked_feature' );
		$capability = self::$capability;
		$menu_slug 	= self::$menu_slug;
		$callback 	= array( &$this, 'admin_feature_list_page' );
		$icon_url 	= $this->plugin_url . 'inc/img/ic_menu_features.png';
		$position		= 6;

    $page = add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url, $position );

    // for submenus set $parent_slug to $menu_slug
    $parent_slug = $menu_slug;

    // Feature Section(s) management submenu items
    $sections = $this->_get_sections();

    $first = true;
    foreach( $sections as $key=>$section ) {

  		$parent_slug = $parent_slug;
  		$page_title	 = sprintf( __('%s Features Manager', 'naked_feature'), ucwords( $section->name ) );
  		$menu_title	 = ucwords( $section->name );
  		$capability  = self::$capability;
  		$menu_slug 	 = $first ? $parent_slug : trim( $section->delta .'-features' );
  		$callback 	 = array( &$this, 'admin_feature_list_page' );
    	
    	add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );

    	$first = false;
    }

    // Add new features section submenu item
    $parent_slug = $parent_slug;
    $page_title	 = __( 'Add New Feature Section', 'naked_feature' );
    $menu_title	 = __( 'Add New Section', 'naked_feature' );
    $capability  = self::$capability;
    $menu_slug 	 = self::$new_section_slug;
    $callback 	 = array( &$this, 'admin_new_section_page' );
    
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
	}


	private function admin_set_access()
	{
		$roles = array( 'administrator', 'editor' );

		$roles = apply_filters(  'naked_feature_access' , $roles );

		foreach( $roles as $r ){
			$role = get_role( $r );
			if( $role ) $role->add_cap( self::$capability );	
		}
	}


	public function admin_feature_list_page()
	{
		$instance = naked_feature_sections::get_instance();
		$page 		= isset( $_GET['page'] ) ? $_GET['page'] : '';
		$action 	= isset( $_GET['action'] ) ? $_GET['action'] : '';
		$id 			= isset( $_GET['id'] ) ? $_GET['id']: '';

		// if $page = the menu slug then we want the data for the first section
		if( $page == self::$menu_slug ) {
			$section = array_shift( $instance->get_sections() );
			$section = new naked_feature_section( $section->delta );
		}
		else {
			$section = new naked_feature_section( $page );
		}

		// prepare the variables
		$title = ucwords( sprintf( __( '%s Features Section', 'naked_feature' ), $section->name ) );
		$edit_link = $section->edit_link;

		// correct the edit link for the first menu subpage
		if( $page == self::$menu_slug ) {
			$edit_link = str_replace( $section->delta . '-features', self::$menu_slug, $edit_link );
		}

		$tpl_path = $this->views_dir . 'features_list.php';
		include( $tpl_path );
	}


	public function admin_new_section_page()
	{
		$tpl_path = $this->views_dir . 'section.php';
		include( $tpl_path );
	}


	private function _get_sections()
	{
		$instance = naked_feature_sections::get_instance();
    $sections = $instance->get_sections();
    return $sections;
	}


	/**
	 * includes all files in the metaboxes/specs dir
	 */
	public function admin_reg_metaboxes()
	{
		$specs_dir = $this->metaboxes_dir . 'specs/';
		foreach ( scandir( $specs_dir ) as $filename ) {
		    $path = $specs_dir . $filename;
		    if ( is_file($path) ) {
		      require_once $path;
		    }
		}
	}


	/**
	 Update / Delete Post Concurrent Action
	 */

	/**
	 * Updates the feature_content table when the save_post action is triggered
	 */
	public function update_on_save_post( $post_id, $post )
	{
		if( $post->post_type != 'revision' && $post->post_status == 'publish' ) {
			
			if( !isset( $_REQUEST[ self::$metabox_id . '_nonce' ] ) )
				return;

			if( $nonce = $_REQUEST[ self::$metabox_id . '_nonce' ] ) {
				if( !wp_verify_nonce( $nonce, self::$metabox_id ) )
					die("Security check failed when trying to update the feature_content table");
			}

			// get an instance of the sections class
			nu_singleton::delete_instance('naked_feature_sections');
			$instance = naked_feature_sections::get_instance();

			// get the section(s) that feature content should be added / removed from
			$section_ids = $instance->section_ids;

			// array of features to add
			$adds = array(); 

			// array of features to remove. this will be overwritten
			// later if $_REQUEST[self::$metabox_id]['sections'] is set
			$removes = $section_ids; 

			if( isset( $_REQUEST[self::$metabox_id]['sections'] ) ) {

				// array of section_ids to have content added
				$adds = $_REQUEST[self::$metabox_id]['sections'];
				
				// array of section_ids to have content removed
				$removes = array_diff( $section_ids, $adds );

				// create feature(s)
				foreach( $adds as $section_id ) {
					$section = new naked_feature_section( $section_id );

					// check to see if the feature has been previously added already
					if( $section->feature_exists( $post_id ) )
						continue; // do nothing
					else {
						// create the feature
						$data = array(
							'section_id' => $section_id,
							'post_id' => $post_id
						);

						$section->create_feature( $data );
					}
				}
			}
			
			// delete feature(s)
			if( !empty( $removes ) ) {
				$model = naked_feature_model::get_instance();	

				$args = array(
					'post_id'  => $post_id,
					'section_ids' => $removes,
					// pass the called_by argument so that we know we don't have to update the meta
					'called_by' => 'post_update'
				);

				$model->delete_feature( $args );
			}
		}
	}


	public function update_on_delete_post( $post_id ) 
	{
		$instance = naked_feature_sections::get_instance();
		$instance->delete_features_by_post_id( $post_id );
	}


	/**
	 AJAX
	 */
	public function ajax_edit_section()
	{
		if( empty($_POST) ) 
			die( 'No data sent' );

		$nonce = $_POST['nonce'];
		if( !wp_verify_nonce( $nonce, 'ajax_naked_feature_nonce' ) )
			die( 'Failed security check. Cannot modify the section.' );

		if( !current_user_can( self::$capability ) )
			die( 'You do not have the permission to do that' );
		
		$model = naked_feature_model::get_instance();

		$data = $_POST;

		// data cleanup
		unset( $data['action'] );
		unset( $data['nonce'] );

		$r['data'] = $data;
			
		if( $data['section_id'] ) {
			// if $data['section_id'] already exists we are updating or deleting
			if( $data['delete_ids'] ) {
				// delete
				$response = $model->delete_section( $data['delete_ids'] );

				if( !is_numeric( $response ) ) {
					$r['status'] = 'error';
					$r['msg'] = $response ? $response : '';
				}
				else {
					$r['status'] = 'success';
					$r['msg'] = __( 'Successfully deleted', 'naked_feature' );
					
					$page = self::$new_section_slug;
					$redirect_url = admin_url('admin.php') . '?page=' . $page;

					$r['redirect'] = $redirect_url;
				}

			}
			else {
				// data cleanup
				unset( $data['delete_ids'] );

				// update
				$response = $model->update_section( $data );

				if( !is_numeric( $response ) ) {
					$r['status'] = 'error';
					$r['msg'] = $response ? $response : '';
				}
				elseif ( $response == 0 ) {
					$r['status'] = 'warning';
					$r['msg'] = __( 'Nothing changed', 'naked_feature' );
				}
				else {
					$r['status'] = 'success';
					$r['msg'] = __( 'Successfully updated', 'naked_feature' );
				}
			}
		}
		else {
			// if no $data['id'] exists we are creating
			$response = $model->create_section( $data );

			if( !is_numeric( $response ) ) {
				$r['status'] = 'error';
				$r['msg'] = $response ? $response : '';
			}
			else {
				$r['status'] = 'success';
				$r['msg'] = __( 'Successfully created', 'naked_feature' );
				$r['section_id'] = $response;	
				
				$section = $model->retrieve_section( $response );

				// check if this is the first feature created... if so we need to set the $page to
				// the menu_slug otherwise we will get a permission denied page
				$instance = naked_feature_sections::get_instance();
				if( 1 == count( $instance->section_ids ) )
					$page = self::$menu_slug;
				else 
					$page = $section->delta .'-features';
				
				$redirect_url = admin_url('admin.php') . '?page=' . $page . '&action=edit&id=' . $response;

				$r['redirect'] = $redirect_url;
			}
		}

		echo json_encode( $r );

		// make sure to die/exit at the end or this will not work
		die;
	} 


	public function ajax_edit_features_list()
	{
		if( empty($_POST) ) 
			die( 'No data sent' );

		$nonce = $_POST['nonce'];
		if( !wp_verify_nonce( $nonce, 'ajax_naked_feature_nonce' ) )
			die( 'Failed security check. Cannot modify the section.' );

		if( !current_user_can( self::$capability ) )
			die( 'You do not have the permission to do that' );

		$data = $_POST;

		// data cleanup
		unset( $data['action'] );
		unset( $data['nonce'] );

		// If the order changed and items are being deleted
		if( $data['delete_ids'] && $data['order'] ) {
			$delete_res = $this->_delete_features( $data['delete_ids'] );
			$update_res = $this->_update_features( $data['order'], $data['section_id'] );

			if( !is_numeric( $delete_res ) && !is_numeric( $update_res ) ) {
				$r['status'] = 'error';
				$r['msg'] = $delete_res . ' ' . $update_res;
			}
			elseif( $r1 = !is_numeric( $delete_res ) || $r2 = !is_numeric( $update_res ) ) {
				$r['status'] = 'error';

				if( !$r1 ) 
					$r['msg'] = $delete_res . __( 'BUT successfully updated the order', 'naked_feature' );

				if( !$r2 )
					$r['msg'] = $update_res . __( 'BUT successfully deleted features', 'naked_feature' );
			}
			else {
				$r['status'] = 'success';
				$r['msg'] = __( 'Successfully updated', 'naked_feature' );
			}

		}
		// elseif( $data['delete_ids'] ) {
		// 	// only deleting... no change of order
		// 	$response = $this->_delete_features( $data['delete_ids'] );

		// 	if( !is_numeric( $response ) ) {
		// 		$r['status'] = 'error';
		// 		$r['msg'] = $response;
		// 	}
		// }
		elseif( $data['order'] ) {
			// only change of order... no deleting
			$response = $this->_update_features( $data['order'], $data['section_id'] );

			if( !is_numeric( $response ) ) {
				$r['status'] = 'error';
				$r['msg'] = $response;
			}
			else {
				$r['status'] = 'success';
				$r['msg'] = __( 'Successfully updated', 'naked_feature' );
			}
		}

		echo json_encode( $r );

		// make sure to die/exit at the end or this will not work
		die;
	}

	/**
	 * Helper function
	 */
	private function _delete_features( $delete_ids )
	{
		$model = naked_feature_model::get_instance();
		
		$args = array( 'feature_ids' => $delete_ids );
		$response = $model->delete_feature( $args );
		return $response;
	} 


	private function _update_features( $order, $section_id ) 
	{
		$model = naked_feature_model::get_instance();

		// Create an array mapping position to the feature_id
		$pos_id_map = explode( ',', $order );
		// var_dump( $pos_id_map );
		$data = array(
			'feature_ids' => $order,
			'map' => $pos_id_map,
			'section_id' => $section_id
		);
		$response = $model->update_features( $data );
		return $response;
	}


	/**
	 JSON-API
	 */

	/**
	 * Adds 'Features' to the list of available controllers on the JSON-API settings page
	 */
	public function add_jsonapi_controller( $controllers )
	{
		$controllers[] = 'Feature';
		return $controllers;
	}


	/**
	 * Sets the correct path to the json_api_features_controller because by default JSON-API 
	 * assumes that the controller is in the json-api/controller directory 
	 *
	 * @uses json_api_[controller]_controller_path filter
	 */
	public function set_jsonapi_controller_path( $path )
	{
		$path = dirname( __FILE__ ) . '/json-api/feature.php';
		return $path;
	}
}