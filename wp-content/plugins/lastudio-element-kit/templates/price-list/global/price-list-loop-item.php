<?php
/**
 * Price list item template
 */

$item_title_attr = $this->get_item_inline_editing_attributes( 'item_title', 'price_list', $this->_processed_index, 'price-list__item-title' );
$item_price_attr = $this->get_item_inline_editing_attributes( 'item_price', 'price_list', $this->_processed_index, 'price-list__item-price' );
$item_desc_attr = $this->get_item_inline_editing_attributes( 'item_text', 'price_list', $this->_processed_index, 'price-list__item-desc' );

?>
<li class="price-list__item"><?php
	echo $this->_open_price_item_link( 'item_url' );
	echo '<div class="price-list__item-inner">';
	echo $this->get_price_image('<div class="price-list__item-img-wrap">%s</div>', 'price-list__item-img');
	echo '<div class="price-list__item-content">';
		echo '<div class="price-list__item-title__wrapper">';
			echo $this->_loop_item( array( 'item_title' ), '<h4 ' . $item_title_attr . '>%s</h4>' );
			echo '<div class="price-list__item-separator"></div>';
			echo $this->_loop_item( array( 'item_price' ), '<div ' . $item_price_attr . '>%s</div>' );
		echo '</div>';
		echo $this->_loop_item( array( 'item_text' ), '<div ' . $item_desc_attr . '>%s</div>' );
	echo '</div>';
	echo '</div>';
	echo $this->_close_price_item_link( 'item_url' );
?></li>