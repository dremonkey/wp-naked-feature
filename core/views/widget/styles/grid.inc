<?php

/**
 * Standard Grid Display
 */

?>

<?php $id = $section . '-features'; ?>

<section id="<?php echo $id ?>" class="features widget brick feature-section features-<?php echo $display_count ?> <?php if (isset( $classes[ 0 ])) echo $classes[ 0 ] ?>">
	<div class="inner">

		<!-- Feature Section Title -->
		<?php if( $title && !$hide_title ) : ?>
			<div class="section-title-wrapper">
				<?php if( $more_link_href ) : ?>				
					<?php echo $before_title ?>
						<a href="<?php echo $more_link_href ?>" title="<?php echo esc_attr( $more_link_text ); ?>"><?php echo $title ?></a>
					<?php echo $after_title ?>
				<?php else : ?>
					<?php echo $before_title . $title . $after_title ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if( $more_link_text && $more_link_href ) : ?>
			<a class="more-link" href="<?php echo $more_link_href ?>" title="<?php echo esc_attr( $more_link_text ); ?>"><?php echo $more_link_text ?><span><b class="icon"></b></span></a>
		<?php endif; ?>

		<!-- Feature Section Items List-->
		<div class="item-list">
		<?php if( $ajax_load ) : ?>

			<?php // Create the placeholders 
			for( $i=1; $i<=$display_count; $i++ ) : ?>

				<?php 

					$guid = '';
					$post_title = '';
					
					if( isset( $features[ $i-1 ] ) ) {
						$feature = $features[ $i-1 ];
						$guid = $feature->guid;
						$post_title = $feature->post_title;
					}
					
				?>

				<div class="<?php if (isset( $classes[ $i ])) echo $classes[ $i ] ?>">
					<a href="<?php echo $guid ?>" title="<?php echo $post_title ?>" class="assistive-text"><?php echo $post_title ?></a>
					<div class="placeholder-box"></div>
				</div>

			<?php endfor; ?>

		<?php else : ?>

			<?php $i = 0; ?>
			<?php foreach( $features as $feature ) : ?>

				<?php // setup class var
				$class = $classes[ $i+1 ] . ' item masonry-brick'; ?>

				<div class="<?php echo $class ?> feature">
					<div class="inner">
					<?php // add a link to the original content if necessary
					if( $link_to_feature ) : ?>
						<a class="item-link" href="<?php echo $feature->permalink ?>" title="<?php echo $feature->post_title ?>">
						<?php endif; ?>

						<?php if ($feature->img) : ?>
							<div class="img-wrap">
								
								<?php // image data
								
								// default
								$image = $feature->img['sizes']['large'];
								$image = apply_filters('naked_feature_img', $image, $feature, $i, $section);
								
								$src = $image['src'];
								$alt = $feature->img['alt'] ?>

								<img src="<?php echo $src ?>" alt="<?php echo $alt ?>" width="<?php echo $image['width'] ?>" height="<?php echo $image['height'] ?>"/>
							</div>
						<?php endif; ?>

						<?php if( $show_post_title || $show_post_content ) : ?>	
							<div class="content-wrapper">
								<?php if( $show_post_title ) : ?>
									<h1 class="title"><?php echo $feature->post_title ?></h1>
								<?php endif; ?>
								<?php if( $show_post_content ) : ?>
									<div class="description"><?php echo $feature->content ?></div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						

						<?php // show the edit link if set
						//if( $edit = $feature->edit_link ) echo $edit ?>

					<?php // close the link to the original content if necessary
					if( $link_to_feature ): ?> 
						</a> 
					<?php endif; ?>

					</div>
				</div>
				
				<?php // stop output if display_count is met
				$i++;
				if( $i == $display_count ) break; ?>

			<?php endforeach; ?>

		<?php endif; ?>

		</div><!-- .item-list -->
	</div><!-- .inner -->
</section>