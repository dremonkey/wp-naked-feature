<?php

/**
 * Singleton to handle sections.
 *
 * @uses nu_singleton
 *
 * Originally had planned to build and store fully instantiated section objects. 
 * However I encountered some problems when feature content was added/deleted
 * from a section because I wasn't correct updating the cached section objects here.
 *
 * @todo
 *	Consider caching instantiated section objects here... OR alternatively removing
 *	this class entirely if there is no advantages to caching instantiated section 
 *	objects here
 */

class naked_feature_sections extends nu_singleton
{
	// all section ids
	public $section_ids = array();

	// section objects before running through the section constructor
	private $sections_by_delta = array(); 
	private $sections_by_id = array(); 

	protected function __construct()
	{
		$this->model = naked_feature_model::get_instance();
		$sections = $this->model->retrieve_sections();

		foreach( $sections as $section ) {
			$this->section_ids[] = $section->section_id;
			$this->sections_by_delta[ $section->delta ] = $section;
			$this->sections_by_id[ $section->section_id ] = $section;    
		}
	}


	/**
	 * Retrieves an array of all section objects. The array keys will be either the section_id 
	 * or delta depending on what is requested. Defaults to the section id.
	 *
	 * @param $key (string)
	 *	Specify the value that you want to use as the key for each section. Either
	 *	section_id or delta.
	 */
	public function get_sections( $key='section_id' )
	{
		$sections = array(
			'section_id' => $this->sections_by_id,
			'delta' => $this->sections_by_delta
		);
		
		return $sections[ $key ];
	}


	/**
	 * Deletes all feature content from all sections that match post_id
	 */
	public function delete_features_by_post_id( $post_id )
	{
		$args = array(
			 'post_id' => $post_id
		);
		
		$this->model->delete_feature( $args );
	}
}