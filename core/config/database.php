<?php
/**
 * @file
 *	Configures the database.
 *	- creates new tables
 *	- updates existing tables
 * 	- deletes tables (* not implemented yet)
 */

class naked_feature_db_config
{
	public static $table_features = 'naked_feature_content';
	public static $table_sections = 'naked_feature_sections';

	private static $db_version = 1;

	public static function init()
	{
		global $wpdb;

		// check if it is a network activation - if so, run the activation function for each blog id
		if ( function_exists('is_multisite') && is_multisite() ) {
		
			if ( is_network_admin() ) {
		    $old_blog = $wpdb->blogid;

				// Get all blog ids
				$blogids = $wpdb->get_col( $wpdb->prepare("SELECT blog_id FROM $wpdb->blogs") );
				
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					self::create_features_table();
					self::create_sections_table();
				}

				switch_to_blog( $old_blog );
				return;
			}
		}

		self::create_features_table();
		self::create_sections_table();
	}


	/**
	 * Create the naked_ads table
	 *
	 * ---- Columns ----
	 * 	id  			 : primary identifier
	 *	post_id 	 : wordpress post_id
	 *	section_id : the section id
	 *	position 	 : the position of this feature within a section
	 */
	private static function create_features_table()
	{
		global $wpdb;

		$table = $wpdb->prefix . self::$table_features;

		$engine = "ENGINE=MyISAM";

		if ( !empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( !empty( $wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		$comments = array(
			'feature_id' => 'primary identifier for the feature item',
			'post_id' 	 => 'wordpress post_id',
			'section_id' => 'id indicating which section this feature belongs to',
			'position' 	 => 'the position of this feature within a section'
		);

		$sql = $wpdb->prepare( 
			"CREATE TABLE IF NOT EXISTS {$table} (
				feature_id int(10) NOT NULL AUTO_INCREMENT COMMENT %s,
	  	  post_id bigint(20) NOT NULL COMMENT %s,
	  	  section_id int(10) NOT NULL COMMENT %s,
	  		position int(10) NOT NULL COMMENT %s,
	  		PRIMARY KEY (feature_id),
	  		UNIQUE (section_id, post_id)
		 	) {$engine} {$charset_collate}",
				$comments['feature_id'],
				$comments['post_id'],
				$comments['section_id'],
				$comments['position']
		);

		update_option( 'naked_feature_db_version', self::$db_version );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta($sql);
	}


	private static function create_sections_table()
	{
		global $wpdb;

		$table = $wpdb->prefix . self::$table_sections;

		$engine = "ENGINE=InnoDB";

		if ( !empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( !empty( $wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		$comments = array(
			'section_id' => 'primary identifier for the section',
			'name' 		 	 => 'the name of the section',
			'delta' 		 => 'machine safe name. created from the name and must be unique',
			'size' 	  	 => 'total number of features this section can hold'
		);

		$sql = $wpdb->prepare( 
			"CREATE TABLE IF NOT EXISTS {$table} (
				section_id int(10) NOT NULL AUTO_INCREMENT COMMENT %s,
	  	 	name varchar(255) NOT NULL COMMENT %s,
	  	 	delta varchar(255) NOT NULL COMMENT %s,
	  		size int(10) NOT NULL COMMENT %s,
	  		PRIMARY KEY (section_id),
	  		UNIQUE (delta)
		 	) {$engine} {$charset_collate}",
				$comments['section_id'],
				$comments['name'],
				$comments['delta'],
				$comments['size']
		);

		update_option( 'naked_feature_db_version', self::$db_version );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta($sql);
	}


	public static function delete()
	{

	}
} 