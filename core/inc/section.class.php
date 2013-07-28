<?php

class naked_feature_section
{
	private static $debug = false;

	public $id = null;
	public $delta = '';
	public $name = '';
	public $features = array();
	public $size = -1;

	// other properties
	public $edit_link = '';

	/**
	 * @param $mixed (string or int)
	 *	Construct a new section by delta or id
	 */
	public function __construct( $mixed )
	{
		$this->model = $model = naked_feature_model::get_instance();
		$data = $this->model->retrieve_section( $mixed );
		
		// set all the class properties/variables
		$this->id 		= $data->section_id;
		$this->name 	= $data->name;
		$this->delta 	= $data->delta;
		$this->size 	= $data->size;

		// attempt to retrieve features from object cache first
		$cache_key = $this->delta;
		$cache_group = 'feature_sections';
		$features = wp_cache_get( $cache_key, $cache_group );

		if (!$features) {
			$features = $this->_get_features();
			wp_cache_set( $cache_key, $features, $cache_group);
		}

		$this->features = $features;

		// set the edit link
		$this->_set_edit_link();
	}


	private function _set_edit_link()
	{
		$page = $this->delta . '-features';
		$id = $this->id;
		$this->edit_link = admin_url('admin.php') . '?page=' . $page . '&action=edit&id=' . $id;
		return;
	}


	/**
	 * @return array of features
	 */
	private function _get_features()
	{
		$features = $this->model->retrieve_features_by_sid( $this->id );

		if( is_wp_error( $features ) ) {
			nu_debug::var_dump( $features );
			return array();
		}

		$r = array(); // an array of feature items

		nu_debug('Features', $features, 3);
		
		// grab all the ids
		foreach( $features as $f ) {
			$ids[] = $f->post_id;
		}

		// turn $ids array into a string
		$ids = implode(',', $ids); 

		if( !$r ) {
			// grab the posts
			$posts = get_posts(array('numberposts'=>count($features),'include'=>$ids));

			foreach ($features as $f) {
				foreach ($posts as $p) {
					$id = $p->ID;

					// move to the next one if post ids don't match
					if ($f->post_id == $id) {

						// set permalink
						$f->permalink = get_permalink( $id );

						// set the title attr
						$f->title_attr = esc_attr( $p->post_title );

						// clean and apply filters to content
						$content 	= apply_filters( 'the_content', $p->post_content );
						$content 	= str_replace( ']]>', ']]&gt;', $content );
						$content 	= str_replace( '&nbsp;', ' ', $content );
						$content 	= trim( $content );

						// set feature content
						$f->content = $content;

						// set the edit link
						$f->edit_link = $this->_get_edit_link( $id );

						// set image urls
						$f->img = $this->_get_thumb( $id );
						$this->_style_date( $f );

						// add extra section information
						$f->section_delta = $this->delta;
						$f->section_name = $this->name;

						$r[] = $f;
					}
				}
			}
		}
		
		return $r;
	}


	public function feature_exists( $post_id ) 
	{
		return $this->model->feature_exists( $post_id, $this->id );
	}


	public function create_feature( $data )
	{
		if( count( $this->features ) == $this->size ) {
			// delete the last item in the list
			$feature = array_pop( $this->features );
			
			$arg = array( 'feature_ids' => $feature->feature_id );
			$this->model->delete_feature( $arg );
		}
		
		// create the new feature
		$this->model->create_feature( $data );

	}


	private function _style_date( &$feature )
	{
		$feature->pub_date = mysql2date( 'Y/m/d' , $feature->post_date );
		$feature->pub_time = mysql2date( 'g:i A', $feature->post_date );
	}


	/**
	 * @uses naked_feature_settings_controller::$options_key
	 * @uses nu_media_utils::get_img_src()
	 */
	private function _get_thumb( $post_id )
	{
		$thumb = null;
		if( $sizes = naked_feature_get_option('feature_image_sizes') ) {
			if( !empty( $sizes ) ) {
				$thumb = nu_media_utils::get_img_src( $post_id, $sizes );
			}
		}
		return $thumb;
	}


	private function _get_edit_link( $post_id )
	{
		if ( !$url = get_edit_post_link( $post_id ) )
			return;
		
		$link = '<a class="edit-link" href="' . $url . '" title="Edit Feature">Edit</a>';

		return $link;
	}
}