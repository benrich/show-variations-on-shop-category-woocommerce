<?php

class WSSVSC_Frontend
{
	public function __construct()
	{
		if (get_option('gmwsvs_enable_setting') == 'yes') {
			if (get_option('gmwsvs_optionc') == 'singlevari') {
				add_action('wp', array($this, 'on_page_known'));
				add_action('woocommerce_product_query', array($this, 'WSSVSC_woocommerce_product_query'));
			}
		}
	}

	public function on_page_known()
	{
		if (!is_product_category()) return;

		add_filter('the_title', array($this, 'edit_title_on_filter_colour_pages'), 10, 2);
	}

	public function WSSVSC_woocommerce_product_query($q)
	{
		$excluded_cats = get_option('gmwsvs_exclude_cat');

		if (empty($excluded_cats)) $excluded_cats = [];

		$cat_id = $q->get_queried_object_id();

		// if current category is one of our allowed categories
		if (in_array($cat_id, $excluded_cats)) {

			$post_types = ['product_variation'];
			$meta_query = (array) $q->get('meta_query');

			// refers to product page > variation > 'exclude this variation'
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key' => '_wssvsc_exclude',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key' => '_wssvsc_exclude',
					'value' => 'yes',
					'compare' => '!=',
				),
			);

			// if showing parent product then add product post_type
			if (get_option('gmwsvs_hide_parent_product') != 'yes') {
				$post_types[] = 'product';
			}

			$q->set('post_type', $post_types);
			$q->set('meta_query', $meta_query);
		}

		return $q;
	}

	public function edit_title_on_filter_colour_pages($title, $id = null)
	{
		$included_cats = get_option('gmwsvs_exclude_cat');

		// if current category is in the list of included categories then maybe use custom title
		if ($id && is_product_category($included_cats)) {
			$custom_name = get_post_meta($id, '_wssvsc_custom_name', true);

			if ($custom_name) {
				$title = $custom_name;
			}
		}

		return $title;
	}
}
