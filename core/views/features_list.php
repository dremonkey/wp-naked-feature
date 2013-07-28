<?php
/**
 * Template for the section features list/management page
 */
?>

<?php if( !$id && ( !$action || $action == 'edit' ) ) : ?>

<div class="wrap">

	<div class="pagetitle">
		<?php screen_icon(); ?>
		<h2>
			<?php echo $title ?>
			<a href="<?php echo $edit_link ?>" id="edit-section" class="btn"><?php echo __( 'Edit', 'naked_feature' ) ?></a>
		</h2>
	</div>

	<div id="msg-box"></div>

	<ul class="sortable widefat" id="features">
	<!-- The header for our list -->
	<li class="list-header">
		
		<div class="column title">
			<span class="inner"><?php echo __( 'Title', 'naked_feature' ) ?></span>
		</div>
		
		<div class="column author">
			<span class="inner"><?php echo __( 'Author', 'naked_feature' ) ?></span>
		</div>
		
		<div class="column date">
			<span class="inner"><?php echo __( 'Date', 'naked_feature' ) ?></span>
		</div>
		
		<div class="column actions">
			<span class="inner"><?php echo __( 'Actions', 'naked_feature' ) ?></span>
		</div>

	</li>
	<?php if ( $section->features ) : ?>
		<?php foreach( $section->features as $k=>$feature) : ?>

			<?php $k += 1; // adjust $k so that it starts at 1 ?>

			<li id="feature-<?php echo $feature->feature_id ?>" class="feature">

				<div class="column title">
					<a href="<?php echo get_edit_post_link( $feature->post_id ) ?>" class="inner">
						<?php echo $feature->post_title ?>
					</a>
				</div>

				<div class="column author">
					<span class="inner">
						<?php echo get_the_author_meta( 'display_name', $feature->post_author) ?>
					</span>
				</div>

				<div class="column date">
					<span class="inner">
						<strong><?php echo $feature->pub_date ?></strong>
						<em><?php echo $feature->pub_time ?></em>
					</span>
				</div>

				<div class="column actions">
					<span class="inner">
						<a id="delete-<?php echo $feature->feature_id ?>" href="#" class="delete">
	  					<?php echo __( 'delete', 'naked_feature' ) ?>
	  				</a>
					</span>
				</div>
				
			</li>

		<?php endforeach; ?>
	<?php endif; ?>

	</ul>

	<form action="/" name="edit_features_list" id="edit-features-list">
  	<input class="button-primary" type="submit" value="Update" />
  	<input type="hidden" name="order" value="" />
  	<input type="hidden" name="delete_ids" value="" />
    <input type="hidden" name="action" value="edit_features_list" />
    <input type="hidden" name="section_id" value="<?php echo $section->id ?>" />
  </form>

</div><!-- .wrap -->

<?php else: ?>

	<!-- The Edit Section Page -->
  <?php include('section.php') ?>

<?php endif; ?>