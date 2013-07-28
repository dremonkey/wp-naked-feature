<?php
/**
 * Template for feature_section widget settings
 */
?>

<!-- Title Field -->
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>">
		<?php echo __( 'Title:', 'naked_feature' ) ?>
	</label>
	<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title'] ?>" class="widefat"/>
</p>


<!-- Hide Title Field -->
<p>
	<?php $checked = checked( $instance['hide_title'], 1, false ) ?>
	<input type="checkbox" id="<?php echo $this->get_field_id('hide_title'); ?>" name="<?php echo $this->get_field_name('hide_title'); ?>" value="1" <?php echo $checked ?> />
	<label for="<?php echo $this->get_field_id('hide_title'); ?>">
		<?php echo __( 'Should the title be hidden?') ?>
	</label>
</p>


<!-- Feature Section ID Field -->
<p>
	<label for="<?php echo $this->get_field_id('feature_section_id'); ?>">
		<?php echo __( 'Feature Sections', 'naked_feature' ) ?>:
	</label>
	<select id="<?php echo $this->get_field_id('feature_section_id') ?>" name="<?php echo $this->get_field_name('feature_section_id') ?>" class="widefat">
		<?php foreach( $sections as $id=>$section ) : ?>
			<?php $selected = $instance['feature_section_id'] == $id ? 'selected="yes"' : ''; ?>
			<option value="<?php echo $id ?>" <?php echo $selected ?> ><?php echo $section->name ?></option>
		<?php endforeach; ?>
	</select>
</p>


<!-- Display Count Field -->
<p>
	<label for="<?php echo $this->get_field_id('feature_display_count') ?>">
		<?php echo __( 'How many feature items do you want to display?', 'naked_feature' ) ?>
	</label>
	<select id="<?php echo $this->get_field_id('feature_display_count') ?>" name="<?php echo $this->get_field_name('feature_display_count') ?>">

		<?php for( $i=1; $i<=10; $i++ ) : ?>
			<?php $selected = $instance['feature_display_count'] == $i ? 'selected="yes"' : ''; ?>
			<option value="<?php echo $i ?>" <?php echo $selected ?> ><?php echo $i ?></option>
		<?php endfor; ?>

	</select>
</p>


<!-- Show Post Title -->
<p>
	<?php $checked = checked( $instance['show_post_title'], 1, false ) ?>
	<input type="checkbox" id="<?php echo $this->get_field_id('show_post_title'); ?>" name="<?php echo $this->get_field_name('show_post_title'); ?>" value="1" <?php echo $checked ?> />
	<label for="<?php echo $this->get_field_id('show_post_title'); ?>">
		<?php echo __( 'Show the Post Title?') ?>
	</label>
</p>


<!-- Show Post Content -->
<p>
	<?php $checked = checked( $instance['show_post_content'], 1, false ) ?>
	<input type="checkbox" id="<?php echo $this->get_field_id('show_post_content'); ?>" name="<?php echo $this->get_field_name('show_post_content'); ?>" value="1" <?php echo $checked ?> />
	<label for="<?php echo $this->get_field_id('show_post_content'); ?>">
		<?php echo __( 'Show the Post Content?') ?>
	</label>
</p>


<!-- Link to Featured Post -->
<p>
	<?php $checked = checked( $instance['link_to_feature'], 1, false ) ?>
	<input type="checkbox" id="<?php echo $this->get_field_id('link_to_feature'); ?>" name="<?php echo $this->get_field_name('link_to_feature'); ?>" value="1" <?php echo $checked ?> />
	<label for="<?php echo $this->get_field_id('link_to_feature'); ?>">
		<?php echo __( 'Link to the featured items original post?') ?>
	</label>
</p>


<!-- Select Style Field -->
<p>
	<label for="<?php echo $this->get_field_id('style') ?>">
		<?php echo __( 'Select a display style', 'naked_feature' ) ?>

		<select id="<?php echo $this->get_field_id('style') ?>" name="<?php echo $this->get_field_name('style') ?>">

			<?php foreach( $styles as $style ) : ?>
				<?php $selected = $instance['style'] == $style ? 'selected="yes"' : ''; ?>
				<option value="<?php echo $style ?>" <?php echo $selected ?> >
					<?php echo $style ?>
				</option>
			<?php endforeach; ?>
		</select>

	</label>
</p>


<!-- More Link Target Field -->
<p>
	<label for="<?php echo $this->get_field_id('more_link_href'); ?>">
		<?php echo __( 'More Link Target:', 'naked_feature' ) ?>
	</label>
	<input type="text" id="<?php echo $this->get_field_id('more_link_href'); ?>" name="<?php echo $this->get_field_name('more_link_href'); ?>" value="<?php echo $instance['more_link_href'] ?>" class="widefat"/>
	<span class="description"><?php _e( 'If empty, a more link will not be displayed', 'naked_feature' ) ?></span>
</p>


<p>
	<label for="<?php echo $this->get_field_id('more_link_text'); ?>">
		<?php echo __( 'More Link Text:', 'naked_feature' ) ?>
	</label>
	<input type="text" id="<?php echo $this->get_field_id('more_link_text'); ?>" name="<?php echo $this->get_field_name('more_link_text'); ?>" value="<?php echo $instance['more_link_text'] ?>" class="widefat"/>
</p>


<!-- Special Classes Field -->
<p>
	<label for="<?php echo $this->get_field_id('feature_section_classes'); ?>">
		<?php echo __( 'Classes:', 'naked_feature' ) ?>
	</label>
	<textarea id="<?php echo $this->get_field_id('feature_section_classes'); ?>" name="<?php echo $this->get_field_name('feature_section_classes'); ?>" rows="4" class="widefat"><?php echo $instance['feature_section_classes'] ?></textarea>
	<span class="description"><?php echo sprintf( __( 'Classes are key / value pairs, where the key is the position of the feature item you want the class applied too. Classes for different feature items should be placed on new lines, and to add a class to the wrapper, use 0 as the key. So to add a class to the wrapper, first item, and third item you would write this: %s', 'naked_feature'), '<br/><br/>0 = wrapper-class<br/>1 = class-1 class-2<br/>3 = class-3' ) ?></span>
</p>


<!-- Use AJAX Field -->
<p>
	<?php $checked = checked( $instance['ajax_load'], 1, false ) ?>
	<input type="checkbox" id="<?php echo $this->get_field_id('ajax_load'); ?>" name="<?php echo $this->get_field_name('ajax_load'); ?>" value="1" <?php echo $checked ?> />
	<label for="<?php echo $this->get_field_id('ajax_load'); ?>">
		<?php echo __( 'Ajax Load?') ?>
	</label>
	<span class="description" style="display:block;"><?php _e( 'Experimental. Only works with the "grid" style right now', 'naked_feature' ); ?></span>
</p>