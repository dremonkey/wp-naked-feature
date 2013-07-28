<?php

// get the types from the saved options value
$types = naked_feature_get_option('feature_content_types');

$feature_sections_mb = new WPAlchemy_MetaBox( array(
	'id' => self::$metabox_id,
	'title' => 'Feature Sections',
	'types' => $types,
	'context' => 'side', 
	'priority' => 'high',
	'template' => $this->metaboxes_dir . 'views/feature-sections.php'
) );