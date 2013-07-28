<?php
/**
 * @file
 *	Template for a section create/edit page
 */
?>

<div class="wrap">

	<div class="pagetitle">
		<?php screen_icon(); ?>
		
		<h2>
			<?php $text = $action == 'edit' ? 'Edit' : 'Add New'; ?>
			<?php printf( __('%s Feature Section', 'naked_feature' ), $text ); ?>
		</h2>
	</div>

	<div id="msg-box"></div>

	<form action="/" name="edit_section" id="edit-section">

		<fieldset class="section">

			<div class="fields">
				
				<div class="field-wrapper">
					<label>Name</label>
					<p class="description"><?php echo __( 'The name of this feature section.' , 'naked_feature' ) ?></p>
					<input type="text" name="name" tabindex="1" id="name" value="<?php echo $section->name ?>"/>
				</div>

				<div class="field-wrapper">
					<label>Size</label>
					<p class="description"><?php echo __( 'The maximum number of items that this feature section can hold', 'naked_feature' ) ?></p>
					<input type="text" name="size" tabindex="2" id="size" value="<?php echo $section->size ?>"/>
				</div>

			</div><!-- /.fields -->

		</fieldset>

		<div class="submitbox">
			<?php if( $action == 'edit' ) : ?>
				<input class="button-primary" type="submit" value="Update" />
				<a href="#" id="delete-<?php echo $id ?>" class="submitdelete delete">Delete</a>
		  		<input type="hidden" name="section_id" value="<?php echo $id ?>" />
		  		<input type="hidden" name="delete_ids" value="" />
		  <?php else : ?>
		  	<input class="button-primary" type="submit" value="Save" />
			<?php endif; ?>
		</div>

		<input type="hidden" name="action" value="edit_section" />
		
	</form>

</div>