<?php
/**
 * Posts template
 */

global $post;

$metadata = $this->get_settings_for_display('metadata');

$output = '';

if (!empty($metadata)) {
	foreach ($metadata as $meta) {
		$item_type = isset($meta['item_type']) ? $meta['item_type'] : '';
		$meta_icon = $this->_get_icon_setting($meta['item_icon'], '<span class="meta--icon">%s</span>', '', false);
		$meta_label = !empty($meta['item_label']) ? sprintf('<span class="meta--label">%s</span>', $meta['item_label']) : '';
		$meta_value = '';
		$item_type_class = '';

		switch ($item_type) {
			case 'description':
			case 'client':
			case 'date':
			case 'awards':
			case 'custom_field_1':
			case 'custom_field_2':
			case 'custom_field_3':
				$meta_value = get_post_meta( $post->ID, '_pf_' . $item_type, true );
				$item_type_class = 'pf__' . $item_type;
				break;
		}

		if (!empty($meta_value)) {
			$meta_value = sprintf('<span class="meta--value">%s</span>', $meta_value);
		}

		if (!empty($meta_value)) {
			$output .= sprintf('<div class="lakit-pf-meta__item lakit-pf-meta__item--%4$s %5$s">%1$s%2$s%3$s</div>', $meta_icon, $meta_label, $meta_value, $item_type, $item_type_class);
		}

	}

	if (!empty($output)) {
		echo sprintf('<div class="lakit-pf-metalist lakit-pf-metalist1">%s</div>', $output);
	}
}