<?php

/*
Controller name: Features
Controller description: Data retrieval for features
*/

class json_api_feature_controller
{
	public function get_features()
  {
    $section = isset( $_GET['section'] ) ? $_GET['section'] : '';
    if( $section ) { 
      $section = new naked_feature_section( $section );
      return $this->feature_results( $section->features );
    }
    elseif( $_GET['dev'] ) {
      
      $section = $this->get_section_for_dev();
      
      if( !$section ) {
        return __( 'To see sample data, create a section first', 'naked_feature' );
      }
      else {
        return $this->feature_results( $section->features ); 
      } 
    }

    die();
  }


  /**
   * Returns the first section (if it exists) for demo purposes
   */
  private function get_section_for_dev()
  {
    $sections_instance = naked_feature_sections::get_instance();
    $sections = $sections_instance->get_sections();

    if( empty( $sections ) ) {
      return false;
    }
    else {
      // grab the first section
      $section_id = array_shift( array_keys( $sections ) );
      $section = new naked_feature_section( $section_id );
      return $section;
    }

    return false;
  }

  protected function feature_results( $features ) 
  {
    global $wp_query;
    return array(
      'count' => count( $features ),
      'features' => $features
    );
  }
}