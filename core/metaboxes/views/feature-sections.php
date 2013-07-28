<?php
/**
 * Template for the feature-section metabox
 */

global $post;

$instance = naked_feature_sections::get_instance();
$sections = $instance->get_sections();

?>

<ul class="checklist">
<?php foreach ($sections as $section): ?>

	<?php 
		
		/**
		 * If the_field( 'NAME' ) changes, you must also change the NAME in naked_feature_controller->update_feature_content()
		 * othewise the updated data will not be saved to the feature_content table
		 */
		$mb->the_field( 'sections', WPALCHEMY_FIELD_HINT_CHECKBOX_MULTI ); 

	?>
	<li>
		<input type="checkbox" name="<?php $mb->the_name(); ?>" value="<?php echo $section->section_id; ?>" <?php $mb->the_checkbox_state( $section->section_id ); ?>/> 
		<?php echo $section->name; ?>
	</li>
	
<?php endforeach; ?>
</ul>

<p class="howto"><?php printf( __( 'Select where this %s will be featured', 'naked_feature' ), $post->post_type ) ?> </p>