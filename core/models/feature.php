<?php

class naked_feature_model extends nu_singleton
{
	private static $debug = false; // set to true to output debug data

	public static $cache_key_sections = 'naked_feature_sections';

	private $sections_table = '';
	private $features_table = '';

	private static $cached = true; // to disable all cache set to false

	protected function __construct()
	{
		$this->set_var('db_tables');
	}


	public function set_var( $type, $value=null )
	{	
		global $wpdb;

		switch( $type ) {
			case 'db_tables' :
				$this->sections_table = $wpdb->prefix . naked_feature_db_config::$table_sections;
				$this->features_table = $wpdb->prefix . naked_feature_db_config::$table_features; 
				break;
		}
	}


	/**
	 RETRIEVE
	 */

	/**
	 * Retrieves all sections
	 */
	public function retrieve_sections( $cached=true )
	{
		global $wpdb;
		
		$key = self::$cache_key_sections;
		$data = array(); // return object

		if( $cached && self::$cached )
			$data = get_transient( $key );
		
		if( !$data ) {
			$db_table = $this->sections_table;
			$query = "SELECT * FROM {$db_table} s ORDER BY s.name ASC";

			$expiration = 60 * 60 * 24 * 30; // 1 month
			$data = $wpdb->get_results( $query );

			// set the cache
			set_transient( $key, $data, $expiration );
		}

		return $data;
	}


	/**
	 * Retrieves a single section
	 *
	 * @uses retrieve_sections to make use of the caching done there
	 */
	public function retrieve_section( $mixed )
	{
		$sections = $this->retrieve_sections();

		if( is_numeric( $section_id = $mixed ) ) {
			// get the section data
			foreach( $sections as $section ) {
				if( $section->section_id == $section_id )
					return $section;
			}
		}
		else if( is_string( $delta = $mixed ) ) {
			// -features will be present if a page string is being passed. the page is constructed
			// by adding concatenating $delta and '-features' so we just remove the '-features' part
			// to get the delta
			$delta = str_replace( '-features', '', $delta );

			// get the section data
			foreach( $sections as $section ) {
				if( $section->delta == $delta )
					return $section;
			}
		}

		return null;
	}


	/**
	 * Retrieves all features that are part of a particular section
	 */
	public function retrieve_features_by_sid( $section_id, $cached=true )
	{
		global $wpdb;

		if( empty( $section_id ) )
			return new WP_Error('retrieve_feature_by_section_failed', __( "Cannot retrieve features. You must set a section id", 'naked_feature' ) );

		if( !is_numeric( $section_id ) )
			return new WP_Error('retrieve_feature_by_section_failed', __( "Section ID must be a number", 'naked_feature' ) );
		
		$key = $this->_get_content_cache_key( array( 'section_id' => $section_id ) );
		$data = array(); // return object

		if( $cached && self::$cached )
			$data = get_transient( $key );
		
		if( !$data ) {
			$db_table = $this->features_table;
			$fields = 'f.feature_id, f.post_id, f.section_id, f.position, p.post_author, p.post_title, p.post_date, p.guid, p.post_type ';
			$query = $wpdb->prepare( "SELECT {$fields} FROM {$db_table} f INNER JOIN {$wpdb->posts} p ON f.post_id = p.ID WHERE f.section_id=%d ORDER BY f.position", $section_id );
			
			$expiration = 60 * 60 * 24; // 1 day
			$data = $wpdb->get_results( $query );

			// set the cache
			set_transient( $key, $data, $expiration );
		}
		
		return $data;
	}


	/**
	 * Retrieves a single feature by feature id(s)
	 *
	 * No caching is done here because this is currently only used when deleting features
	 */
	public function retrieve_features_by_fid( $feature_ids ) 
	{
		global $wpdb;

		if( empty( $feature_ids ) )
			return new WP_Error( 'retrieve_features_by_id_failed', __( "Cannot retrieve features. You must set a feature id", 'naked_feature' ) );

		// make sure we are dealing with a string
		$feature_ids = strval( $feature_ids );

		$db_table = $this->features_table;
		$fields = 'f.feature_id, f.post_id, f.section_id, f.position, p.post_author, p.post_title, p.post_date, p.guid, p.post_type ';
		$query = $wpdb->prepare( "SELECT {$fields} FROM {$db_table} f INNER JOIN {$wpdb->posts} p ON f.post_id = p.ID WHERE f.feature_id IN (%s)", $feature_ids );
		
		$expiration = 60 * 60 * 24 * 30; // 1 month
		$data = $wpdb->get_results( $query );
		
		return $data;
	}


	/**
	 * Retrieves features by post id
	 */
	public function retrieve_features_by_pid( $post_id, $cached=true ) 
	{
		global $wpdb;

		if( empty( $post_id ) )
			return new WP_Error( 'retrieve_features_by_postID_failed', __( "Cannot retrieve features. You must set a post id", 'naked_feature' ) );

		if( !is_numeric( $post_id ) )
			return new WP_Error( 'retrieve_features_by_postID_failed',__( "Post ID must be a number", 'naked_feature' ) );

		$key = $this->_get_content_cache_key( array( 'post_id' => $post_id ) );
		$data = array(); // return object

		if( $cached && self::$cached )
			$data = get_transient( $key );
		
		if( !$data ) {
			$db_table = $this->features_table;
			$fields = 'f.feature_id, f.post_id, f.section_id, f.position, p.post_author, p.post_title, p.post_date, p.guid, p.post_type ';
			$query = $wpdb->prepare( "SELECT {$fields} FROM {$db_table} f INNER JOIN {$wpdb->posts} p ON f.post_id = p.ID WHERE f.post_id=%d", $post_id );
			
			$expiration = 60 * 60 * 24; // 1 day
			$data = $wpdb->get_results( $query );

			// set the cache
			set_transient( $key, $data, $expiration );
		}
		
		return $data;
	}


	/**
	 --------------------| CREATE METHODS |--------------------
	 */

	/**
	 * Create a new feature section
	 */
	public function create_section( $data )
	{
		global $wpdb;

		$db_table = $this->sections_table;

		extract( $data );
		
		if( empty( $name ) ) 
			return __( "You must set a name for this feature section", 'naked_feature' );

		$delta = $this->_get_delta( $name );

		if( !$delta )
			return __( "Use a different name. The name $name has already been used.", 'naked_feature' );

		if( empty( $size ) )
			return __("You must set a size", 'naked_feature' );

		if( !is_numeric( $size ) )
			return __( "Size must be a number", 'naked_feature' );

		// add the data. $wpdb->insert returns false if data could not be inserted.
		$result = $wpdb->insert( $db_table, 
			array( 
				'name' 	=> $name,
				'delta' => $delta,
				'size' 	=> $size
			),
			array( '%s','%s','%d' ) 
		);

		if( $result ) {
			// clear the cache
			delete_transient( self::$cache_key_sections );
			return $wpdb->insert_id;
		}
		else {
			var_dump( $wpdb->last_query );
			$wpdb->print_error();
		}

		return false;
	}


	/**
	 * Create a new feature content item within a section
	 */
	public function create_feature( $data )
	{
		global $wpdb;
		$db_table = $this->features_table;

		extract( $data );	

		if( empty( $post_id ) ) 
			return __( "You must set a post id", 'naked_feature' );
			
		if( empty( $section_id ) ) 
			return __( "You must set a section", 'naked_feature' );

		// increment the positions of all existing feature content in a section
		// before inserting the new item
		if( $this->_increment_positions( $section_id ) === false )
			return __( "Failed to increment positions", 'naked_feature' );

		// add the data. $wpdb->insert returns false if data could not be inserted.
		$result = $wpdb->insert( $db_table, 
			array( 
				'post_id' 	 => $post_id,
				'section_id' => $section_id,
				'position' 	 => 1,
			),
			array( '%d','%d', '%d' ) 
		);

		if( $result ) {
			// clear the cache
			$key = $this->_get_content_cache_key( array( 'section_id' => $section_id ) );
			delete_transient( $key );
			return $wpdb->insert_id;
		}
		else {
			var_dump( $wpdb->last_query );
			$wpdb->print_error();
		}

		return false;
	}


	/**
	 --------------------| UPDATE METHODS |--------------------
	 */

	/**
	 * Updates a single section with new data
	 *
	 * @param $data (array)
	 *	key => value pair mapping column to new data
	 */
	public function update_section( $data )
	{
		global $wpdb;
		$db_table = $this->sections_table;

		$where = array(
			"section_id" => $data['section_id'],
		);

		$where_format = array( '%s' );

		$result = $wpdb->update( $db_table, $data, $where, $format=null, $where_format );

		if( is_int( $result ) ) {
			delete_transient( self::$cache_key_sections );
			return $result;
		}
		else {
			var_dump( $wpdb->last_query );
			$wpdb->print_error();
		}

		return false;
	}


	/**
	 * Updates all the features in a specific section. Currently only used/needed to update the 
	 * feature positions, so may be more appropriate to call this function update_feature_positions
	 *
	 * @param $data (array)
	 *	key => value pair mapping column to new data
	 */
	public function update_features( $data )
	{
		global $wpdb;
		$db_table = $this->features_table;

		// should create two variables: $map (array) and $feature_ids (string)
		extract( $data );

		if( isset( $map ) && isset( $feature_ids ) && isset( $section_id ) ) {

			// Prepare our sql query. This will update multiple rows at the same time.
			// http://www.karlrixon.co.uk/articles/sql/update-multiple-rows-with-different-values-and-a-single-sql-query/
			$query = "UPDATE {$db_table} SET position = CASE feature_id ";

			foreach ( $map as $position => $id ) {
				// +1 because $position is zero indexed and we want to start at 1 rather than 0
			  $query .= sprintf( "WHEN %d THEN %d + 1 ", $id, $position );
			}

			$query .= "END WHERE feature_id IN ({$feature_ids})";

			$result = $wpdb->query( $query );

			if( is_int( $result ) ) {
				// clear the cache
				$args = array( 'section_id' => $section_id );
				$key = $this->_get_content_cache_key( $args );
				delete_transient( $key );
				
				return $result;
			}
			else {
				var_dump( $wpdb->last_query );
				$wpdb->print_error();
			}
		}
	
		return false;
	}


	/**
	 --------------------| DELETE METHODS |--------------------
	 */

	/**
	 * Deletes feature content form the feature_content table. 
	 * 
	 * If a post_id + section_id combination is passed in then only those entries where the 
	 * post_id is in the matching section will be deleted. However if only a post_id is passed 
	 * in then all features in all sections matching that post_id will be deleted.
	 *
	 * If feature_id(s) is passed in only those entries will be deleted. Feature ID is unique
	 * so post_id(s) and section_id(s) do not affect the delete condition here
	 *
	 * @param $args (array)
 	 *	Accepted key/values.
	 *		post_id 		- int
	 *		section_ids - mixed (array or string) 
	 *		feature_ids - int
	 *		called_by 	- string
	 */
	public function delete_feature( $args )
	{
		global $wpdb;

		$db_table = $this->features_table;

		if( self::$debug ) {
			nu_debug::var_dump( $args );
		}

		extract( $args );

		if( isset( $post_id ) ) {
			if( isset( $section_ids ) ) { 

				// if $section_ids is an array convert it to a string
				if( is_array( $section_ids ) )
					$section_ids = implode( ',' , $section_ids );

				// make sure that $section_ids is a string
				$section_ids = strval( $section_ids );

				// remove the features postmeta data
				$array_section_ids = explode( ',' , $section_ids );
				foreach( $array_section_ids as $section_id ) {
					// retrieve all the features of the section we want so that their postmeta can be deleted
					$features = $this->retrieve_features_by_sid( $section_id );

					if( is_wp_error( $features ) ) {
						nu_debug::var_dump( $features );
						return __( 'Aborting. Could not delete metadata', 'naked_feature' );  
					}

					// delete the postmeta
					if( !isset( $called_by ) || $called_by != 'post_update' ) {
						if( !$this->_delete_features_from_metadata( $features ) )
							return __( 'Aborting. Could not delete metadata', 'naked_feature' );
					}
				} 

				$query = $wpdb->prepare( "DELETE FROM {$db_table} WHERE post_id=%d AND section_id IN ({$section_ids})", $post_id );
			}
			else {
				/**
				 * No section id so delete all features with $post_id and their metadata. This part should only run when the 
				 * post  itself is deleted.
				 */ 

				$features = $this->retrieve_features_by_pid( $post_id );

				if( is_wp_error( $features ) ) {
					nu_debug::var_dump( $features );
					return __( 'Aborting. Could not delete metadata', 'naked_feature' );  
				}

				// delete the postmeta
				if( !isset( $called_by ) || $called_by != 'post_update' ) { 
					if( !$this->_delete_features_from_metadata( $features, true ) )
						return __( 'Aborting. Could not delete metadata', 'naked_feature' );
				}

				$query = $wpdb->prepare( "DELETE FROM {$db_table} WHERE post_id=%d", $post_id );
			}
		}
		elseif( isset( $section_ids ) ) {
			/**
			 * This part should only run when a whole section or sections is being deleted.
			 */

			if( is_numeric( $sid = $section_ids ) ) {
				// in this case we are only dealing with a single section_id so make sure that $section_id 
				// is a string so that it will be passed into the query correctly
				$sid = strval( $sid );

				// retrieve all the features of the section we want so that their postmeta can be deleted
				$features = $this->retrieve_features_by_sid( $sid );

				// delete the postmeta
				if( !isset( $called_by ) || $called_by != 'post_update' ) {
					if( !$this->_delete_features_from_metadata( $features ) )
						return __( 'Aborting. Could not delete metadata', 'naked_feature' );
				}

			}
			elseif( is_string( $section_ids ) ) {
				// if $section_ids is a string convert to an array but don't overwrite the existing 
				// string value
				$array_section_ids = explode( ',' , $section_ids );
				foreach( $array_section_ids as $section_id ) {
					// retrieve all the features of the section we want so that their postmeta can be deleted
					$features = $this->retrieve_features_by_sid( $section_id );

					// delete the postmeta
					if( !isset( $called_by ) || $called_by != 'post_update' ) {
						if( !$this->_delete_features_from_metadata( $features ) )
							return __( 'Aborting. Could not delete metadata', 'naked_feature' );
					}
				}
			}

			$query = $wpdb->prepare( "DELETE FROM {$db_table} WHERE section_id IN (%s)", $section_ids );
		}
		elseif( isset( $feature_ids ) ) {
			// delete all features and their metadata that match feature_ids
			$features = $this->retrieve_features_by_fid( $feature_ids );

			if( is_wp_error( $features ) ) {
				nu_debug::var_dump( $features );
				return __( 'Aborting. Could not delete metadata', 'naked_feature' );
			}

			// delete the postmeta
			if( !isset( $called_by ) || $called_by != 'post_update' ) {
				if( !$this->_delete_features_from_metadata( $features ) )
					return __( 'Aborting. Could not delete metadata', 'naked_feature' );
			}

			$query = $wpdb->prepare( "DELETE FROM {$db_table} WHERE feature_id IN (%s)", $feature_ids );
		}

		$result = $wpdb->query( $query );

		if( is_int( $result ) ) {

			// clear all caches
			if( $result > 0 )
				$this->_clear_all_content_caches();
			
			return $result;
		}
		else {
			var_dump( $wpdb->last_query );
			$wpdb->print_error();
		}
		
		return false;
	}


	/**
	 * Deletes a section(s) and removes all features from that section(s). Designed to be able
	 * to delete multiple sections at once.
	 *
	 * @param $section_ids (mixed - array or int )
	 *	an array or section_ids (int) to delete or a single section_id (int)
	 */
	public function delete_section( $section_ids )
	{
		global $wpdb;

		$db_table = $this->sections_table;

		if( isset( $section_ids ) ) {
			if( is_array( $section_ids ) )
				$section_ids = implode( ',' , $section_ids );

			// make sure that $section_ids is a string
			$section_ids = strval( $section_ids );

			// first remove the features that are in those sections
			$args = array(
				'section_ids' => $section_ids,
			);

			$result = $this->delete_feature( $args );

			if( is_int( $result ) ) {

				// if the features were successfully deleted, delete the section
				$query = $wpdb->prepare( "DELETE FROM {$db_table} WHERE section_id IN (%s)", $section_ids ); 
				$result = $wpdb->query( $query );	

				if( $result ) {
					// if the section(s) were successfully deleted, clear the cache
					delete_transient( self::$cache_key_sections );
					return $result;
				}
				else {
					var_dump( $wpdb->last_query );
					$wpdb->print_error();

					return sprintf( __( 'Features from section(s) %s were deleted, however section(s) %s could not be deleted', 'naked_feature' ), $section_ids, $section_ids );
				}

			} 
			else {
				var_dump( $wpdb->last_query );
				$wpdb->print_error();
			}
		}

		return false;
	}


	/**
	 * Ensures that when a feature is deleted the metadata associated with that
	 * content object (post or whatever) is also deleted. 
	 *
	 * This function runs everytime a feature is deleted, regardless of how it is
	 * deleted so it needs to make sure that the metadata actually exists before
	 * trying to delete it otherwise error will occur.
	 *
	 * @param $features (array of feature objects)
	 * @param $all (bool)
	 *	whether or not the whole entry should be deleted
	 */
	private function _delete_features_from_metadata( $features, $all=false )
	{
		$result = true;

		if( !empty( $features ) ) {
		
			$key = naked_feature_controller::$metabox_id;
			
			foreach( $features as $feature ) {

				// if any of the metadata delete / updates fail the stop and return false
				if( !$result ) 
					break;

				if( $all ) {
					/**
					 * Not altering... just deleting the entry. It should be noted that this is currently
					 * called only when a post is deleted, and in that case $meta should be empty. 
					 * However in the event that it is not it we delete it
					 */
					$meta = get_metadata( $feature->post_type, $feature->post_id, $key, true );

					if( !empty( $meta ) ) {
						$result = delete_metadata( $feature->post_type, $feature->post_id, $key );
					}
				}
				else {
					// altering but not deleting the entry (post)
					$meta = get_metadata( $feature->post_type, $feature->post_id, $key, true );

					if( !empty( $meta ) ) {

						$update = false; // determines if metadata needs to be updated
						$sections = $meta['sections'];

						if( self::$debug ) {
							nu_debug::var_dump( $sections );
						}
						
						foreach( $sections as $i=>$section ) {
							if( $section == $feature->section_id && isset( $sections[$i] ) ) {
								$update = true;
								unset( $sections[$i] );
							}
						}

						if( $update ) {
							$meta['sections'] = $sections;
							// update_metadata return false if nothing has changed (or update fails) so prior
							// to calling update_metadata we must be sure that data has changed
							$result = update_metadata( $feature->post_type, $feature->post_id, $key, $meta );
						}	
					}
				}
			}
		}

		return $result;
	}


	/**
	 --------------------| HELPER METHODS |--------------------
	 */

	/**
	 * Increments all positions for a specific section
	 */
	private function _increment_positions( $section_id )
	{
		global $wpdb;
		$db_table = $this->features_table;
		$query = $wpdb->prepare( "UPDATE {$db_table} SET position = position + 1 WHERE section_id=%d", $section_id );

		$result = $wpdb->query( $query );
		
		if( is_int( $result ) ){
			return $result;	
		}
		else {
			var_dump( $wpdb->last_query );
			$wpdb->print_error();
		}

		return false;
	}


	/**
	 * Simple convenience function to retrieve the feature content cache key
	 * when we need to access/modify the cache for a particular query
	 *
	 * @return (string) the cache key
	 */
	private function _get_content_cache_key( $args )
	{
		extract( $args );

		if( isset( $section_id ) ) {
			$key = 'naked_feature_content_sid_' . $section_id;
		}
		elseif( isset( $post_id ) ) {
			$key = 'naked_feature_content_pid_' . $post_id;
		}
		elseif( isset( $feature_id ) ) {
			$key = 'naked_feature_content_fid_' . $feature_id;
		}
		
		return $key;
	}


	/**
	 * Clears all feature content cache. Does not clear the section(s) cache
	 */
	private function _clear_all_content_caches()
	{
		$sections = $this->retrieve_sections();
		foreach( $sections as $section ) {
			$args = array( 'section_id' => $section->section_id );
			$key = $this->_get_content_cache_key( $args );
			delete_transient( $key );
		}
	}


	/**
	 * Determines if a feature exists based on post_id and section_id
	 *
	 * @return int (1) if feature exists or false if it does not
	 */
	public function feature_exists( $post_id, $section_id ) 
	{
		global $wpdb;
		
		$db_table = $this->features_table;

		$query = $wpdb->prepare( "SELECT 1 FROM {$db_table} WHERE post_id=%d AND section_id=%d", $post_id, $section_id );

		$exists = $wpdb->query( $query );

	  return $exists;
	}


	/**
	 * Takes the human readable name and converts it into a machine
	 * safe name (delta)
	 *
	 * @param $id (int)
	 * 	if $id is set then we are updating
	 */
	private function _get_delta( $name )
	{
		$delta = nu_utils::clean_string( $name );

		// check to see if the delta already exists
		if( !$this->_delta_exists( $delta ) )
			return $delta;
		
		return false;	
	}


	/**
 	 * Checks to see if the delta is already in use.
 	 */
	private function _delta_exists( $delta ) 
	{
		global $wpdb;

		$db_table = $this->sections_table;

		$query = $wpdb->prepare( "SELECT 1 FROM {$db_table} WHERE delta=%s", $delta, $type );	
		
		$exists = $wpdb->query( $query );

	  return $exists;
	}
}