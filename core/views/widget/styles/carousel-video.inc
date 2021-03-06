<?php

/**
 * Carousel for displaying Featured Items.
 *
 * Only shows one at a time.
 */

?>

<?php $id = $section . '-features'; ?>

<section id="<?php echo $id ?>" class="slide carousel features features-<?php echo $display_count ?> <?php echo $wrapper_classes ?>">
	<div class="carousel-inner">
		<div class="container">
		<?php $i = 0; ?>
		<?php foreach( $features as $feature ) : ?>
			
				<?php // setup class var
				$class = isset( $classes[ $i+1 ] ) ? $classes[ $i+1 ] : '';
				$class .= $i == 0 ? ' item active' : ' item'; ?>

				<div class="<?php echo $class ?>">
					<div class="item-inner row">
						<?php if( $feature->video ) : ?>
							<div class="video span9">
								<div class="inner"><?php echo $feature->video; ?></div>
							</div>
						<?php endif; ?>

						<?php if( $show_post_content ) : ?>
							<div class="carousel-caption span3">
								<h1 class="title"><?php echo $feature->post_title ?></h1>
								<?php echo $feature->content ?>
								<?php // show the edit link if set
								if( $edit = $feature->edit_link ) echo $edit ?>
							</div>
						<?php endif; ?>

						<?php // add a link to the original content if necessary
						if( !$link_to_feature ) : ?>
							<a href="<?php echo $feature->permalink ?>" title="<?php echo $feature->title_attr ?>"></a>
						<?php endif; ?>
					</div>
				</div>
			
			<?php // stop output if display_count is met
			$i++;
			if( $i == $display_count ) break; ?>

		<?php endforeach; ?>
		</div>
	</div>

	<a class="left carousel-control" href="#<?php echo $id ?>" data-slide="prev">&lsaquo;</a>
	<a class="right carousel-control" href="#<?php echo $id ?>" data-slide="next">&rsaquo;</a>

</section>