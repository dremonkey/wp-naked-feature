<?php

/**
 * Grid 3-1-3 Pattern
 *
 * Currently does not support AJAX
 *
 * @todo merge this view and the grid.inc view together
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
            <div class="group group-1"><div class="container"><div class="row">

            <?php $i = 0; ?>
            <?php foreach ($features as $feature) : ?>

                <?php // Do Item Grouping
                if (($i+1)%4==0) : ?>
                    </div><!-- /row --></div><!-- /container --></div><!-- /group -->
                    <div class="group group-alt group-<?php echo ($i+1)/4 + 1 ?>"><div class="container"><div class="row">
                <?php endif; ?>

                <?php // setup class var
                $class = $classes[ $i+1 ] . ' item'; ?>

                <div class="<?php echo $class ?>">
                    <div class="item-inner">
                        <?php // add a link to the original content if necessary
                        if( $link_to_feature ) : ?>
                        <a class="item-link" href="<?php echo $feature->permalink ?>" title="<?php echo $feature->post_title ?>">
                        <?php endif; ?>

                        <?php if ($feature->img) : ?>
                            <div class="img-wrap">
                                
                                <?php // image data
                                $src = $feature->img['sizes']['full']['src'];
                                $alt = $feature->img['alt'] ?>

                                <img src="<?php echo $src ?>" alt="<?php echo $alt ?>" />
                            </div>
                        <?php endif; ?>
                        <?php if( $show_post_content ) : ?>
                            <h1 class="title"><?php echo $feature->post_title ?></h1>
                            <div class="description"><?php echo $feature->content ?></div>
                        <?php endif; ?>

                        <?php // show the edit link if set
                        if( $edit = $feature->edit_link ) echo $edit ?>

                        <?php // close the link to the original content if necessary
                        if( $link_to_feature ) : ?></a><?php endif; ?>

                    </div>
                </div>

                <?php // Do Item Grouping
                if (($i+1)%4==0) : ?>
                    </div><!-- /row --></div><!-- /container --></div><!-- /group -->
                    <div class="group group-<?php echo ($i+1)/4 + 2 ?>"><div class="container"><div class="row">
                <?php endif; ?>
                
                <?php // stop output if display_count is met
                $i++;
                if( $i == $display_count ) break; ?>

            <?php endforeach; ?>

            <!-- close the final row,container, and group divs-->
            </div><!-- /row --></div><!-- /container --></div><!-- /group -->

        </div><!-- .item-list -->
    </div><!-- .inner -->
</section>