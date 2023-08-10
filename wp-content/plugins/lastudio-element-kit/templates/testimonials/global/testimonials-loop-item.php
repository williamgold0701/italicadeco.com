<?php
/**
 * Testimonials item template
 */


$preset = $this->get_settings( 'preset' );

$item_image = $this->_loop_item( array( 'item_image', 'url' ), '%s' );
$item_image = apply_filters('lakit_wp_get_attachment_image_url', $item_image);

$post_classes = ['lakit-testimonials__item'];
$post_classes[] = $this->_loop_item( array( 'el_class' ), '%s' );
$post_classes[] = $this->_loop_item( array( '_id' ), 'elementor-repeater-item-%s' );

if(filter_var( $this->get_settings_for_display('enable_carousel'), FILTER_VALIDATE_BOOLEAN )){
    $post_classes[] = 'swiper-slide';
}
else{
    $post_classes[] = lastudio_kit_helper()->col_new_classes('columns', $this->get_settings_for_display());
}

?>
<div class="<?php echo esc_attr(join(' ', $post_classes)); ?>">
    <?php
    if(!empty($item_image) && $preset == 'type-12'){
        echo '<div class="lakit-testimonials__figure">';
        do_action('lastudio-kit/testimonials/output/before_image', $preset);
        echo sprintf('<span class="lakit-testimonials__tag-img"><span style="background-image: url(\'%1$s\')"></span></span>', $item_image );
        do_action('lastudio-kit/testimonials/output/after_image', $preset);
        echo '</div>';
    }
    ?>
	<div class="lakit-testimonials__item-inner">
		<div class="lakit-testimonials__content"><?php
            if(!empty($item_image) && $preset != 'type-12'){
                echo '<div class="lakit-testimonials__figure">';
                do_action('lastudio-kit/testimonials/output/before_image', $preset);
                echo sprintf('<span class="lakit-testimonials__tag-img"><span style="background-image: url(\'%1$s\')"></span></span>', $item_image );
                do_action('lastudio-kit/testimonials/output/after_image', $preset);
                echo '</div>';
            }

            echo '<div class="lakit-testimonials__comment">';
                if($preset == 'type-11'){
                    if($this->get_settings('replace_star')){
                        ?>
                        <div class="lakit-testimonials__rating has-replace"><span class="star-rating"><?php
                                if(has_action('lastudio-kit/testimonials/output/star_rating')){
                                    do_action('lastudio-kit/testimonials/output/star_rating', $preset);
                                }else{
                                    echo '<svg width="19" height="16" viewBox="0 0 19 16" xmlns="http://www.w3.org/2000/svg"><path d="M4.203 16c2.034 0 3.594-1.7 3.594-3.752 0-2.124-1.356-3.61-3.255-3.61-.339 0-.813.07-.881.07C3.864 6.442 5.831 3.611 8 2.124L5.492 0C2.372 2.336 0 6.3 0 10.62 0 14.087 1.966 16 4.203 16zm11 0c2.034 0 3.661-1.7 3.661-3.752 0-2.124-1.423-3.61-3.322-3.61-.339 0-.813.07-.881.07.271-2.266 2.17-5.097 4.339-6.584L16.492 0C13.372 2.336 11 6.3 11 10.62c0 3.468 1.966 5.38 4.203 5.38z" fill="currentColor" fill-rule="nonzero"/></svg>';
                                }
                                ?></span></div>
                        <?php
                    }
                    else{
                        $item_rating = $this->_loop_item( array( 'item_rating' ), '%d' );
                        if(absint($item_rating)> 0){
                            $percentage =  (absint($item_rating) * 10) . '%';
                            echo '<div class="lakit-testimonials__rating"><span class="star-rating"><span style="width: '.$percentage.'"></span></span></div>';
                        }
                    }
                }
                echo $this->_loop_item( array( 'item_comment' ), '<div>%s</div>' );
            echo '</div>';

            if($preset == 'type-10' || $preset == 'type-11'){
                echo '<div class="lakit-testimonials__infowrap">';
                echo '<div class="lakit-testimonials__infowrap2">';
            }

            echo $this->_loop_item( array( 'item_name' ), '<div class="lakit-testimonials__name"><span>%s</span></div>' );
            echo $this->_loop_item( array( 'item_position' ), '<div class="lakit-testimonials__position"><span>%s</span></div>' );

            if($preset == 'type-10' || $preset == 'type-11'){
                echo '</div>';
            }
            if($preset != 'type-11'){
                if($this->get_settings('replace_star')){
                    ?>
                    <div class="lakit-testimonials__rating has-replace"><span class="star-rating"><?php
                            if(has_action('lastudio-kit/testimonials/output/star_rating')){
                                do_action('lastudio-kit/testimonials/output/star_rating', $preset);
                            }else{
                                echo '<svg width="19" height="16" viewBox="0 0 19 16" xmlns="http://www.w3.org/2000/svg"><path d="M4.203 16c2.034 0 3.594-1.7 3.594-3.752 0-2.124-1.356-3.61-3.255-3.61-.339 0-.813.07-.881.07C3.864 6.442 5.831 3.611 8 2.124L5.492 0C2.372 2.336 0 6.3 0 10.62 0 14.087 1.966 16 4.203 16zm11 0c2.034 0 3.661-1.7 3.661-3.752 0-2.124-1.423-3.61-3.322-3.61-.339 0-.813.07-.881.07.271-2.266 2.17-5.097 4.339-6.584L16.492 0C13.372 2.336 11 6.3 11 10.62c0 3.468 1.966 5.38 4.203 5.38z" fill="currentColor" fill-rule="nonzero"/></svg>';
                            }
                            ?></span></div>
                    <?php
                }
                else{
                    $item_rating = $this->_loop_item( array( 'item_rating' ), '%d' );
                    if(absint($item_rating)> 0){
                        $percentage =  (absint($item_rating) * 10) . '%';
                        echo '<div class="lakit-testimonials__rating"><span class="star-rating"><span style="width: '.$percentage.'"></span></span></div>';
                    }
                }
            }
            if($preset == 'type-10' || $preset == 'type-11'){
                echo '</div>';
            }
		?></div>
	</div>
</div>