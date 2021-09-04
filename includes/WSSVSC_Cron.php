<?php

class WSSVSC_Cron
{
	public function __construct()
	{
		// add_action('init', array($this, 'init'));
	}

	public function init()
	{
		$args = array(
			'post_type' => 'product_variation',
			'posts_per_page' => 200,
			'meta_query' => array(
				array(
					'key' => '_wssvsc_exclude',
					'value' => 'yes',
					'compare' => 'NOT EXISTS'
				)
			),
		);

		$the_query = new WP_Query($args);

		if ($the_query->have_posts()) {
			while ($the_query->have_posts()) {
				$the_query->the_post();

				$variation_id = get_the_ID();

				// BR: I don't think this is needed
				// However might be needed for filtering IF variation post types don't have parent attributes,
				// which they probably wont.
				// $variation = wc_get_product($variation_id);
				// print_r($variation->get_variation_attributes());
				// foreach ($variation->get_variation_attributes() as $taxonomya => $terms_sluga) {
				// 	wp_set_post_terms($variation_id, $terms_sluga, ltrim($taxonomya, 'attribute_'));
				// }

				$parent_product_id = wp_get_post_parent_id($variation_id);

				if ($parent_product_id) {
					$terms = (array) wp_get_post_terms($parent_product_id, 'product_cat', array('fields' => 'ids'));
					wp_set_post_terms($variation_id, $terms, 'product_cat');
				}
			}
		}
	}
}
