<?php

class WSSVSC_Admin
{
	public function __construct()
	{
		add_action('admin_enqueue_scripts', array($this, 'WSSVSC_enqueue_select2_jquery'));
		add_action('admin_init', array($this, 'WSSVSC_register_settings'));
		add_action('admin_menu', array($this, 'WSSVSC_admin_menu'));

		add_action('woocommerce_process_product_meta', array($this, 'WSSVSC_custom_save'));
		add_action('woocommerce_product_after_variable_attributes', array($this, 'WSSVSC_add_variation_settings_fields'), 2000, 3);
		add_action('woocommerce_save_product_variation', array($this, 'WSSVSC_woo_add_custom_variation_fields_save'), 10, 2);
	}

	public function WSSVSC_enqueue_select2_jquery()
	{
		wp_register_style('WSSVSCselect2css', WSSVSC_PLUGINURL . '/css/select2.css', false, '1.0', 'all');
		wp_register_script('WSSVSCselect2', WSSVSC_PLUGINURL . '/js/select2.js', array('jquery'), '1.0', true);
		wp_enqueue_style('WSSVSCselect2css');
		wp_enqueue_script('WSSVSCselect2');
	}

	public function WSSVSC_add_variation_settings_fields($loop, $variation_data, $variation_post)
	{
		echo "<div style='background-color: #eee;padding: 5px 18px;'>";
		woocommerce_wp_text_input(array(
			'id'            => '_wssvsc_custom_name' . $variation_post->ID,
			'name'          => '_wssvsc_custom_name[' . $variation_post->ID . ']',
			'value'         => get_post_meta($variation_post->ID, '_wssvsc_custom_name', true),
			'type'          => 'text',
			'label'         => __('Variation Single Product For Custom Name', 'woocommerce'),
			'description'   => __('This Option support for just Work for <strong>Show Variations As Single Product</strong>.', 'woocommerce'),
			'wrapper_class' => 'form-row form-row-full',
		));
		$_wssvsc_exclude = get_post_meta($variation_post->ID, '_wssvsc_exclude', true);
?>
		<label>
			<?php echo __('&nbsp; Exclude Variation in shop and category page', 'woocommerce'); ?>
			<input type="checkbox" class="checkbox" value='yes' name="_wssvsc_exclude[<?php echo $variation_post->ID; ?>]" <?php checked($_wssvsc_exclude == 'yes', true);  ?> />
		</label>
		<?php
		$args = ['hide_empty' => false];
		$product_categories = get_terms('product_cat', $args);
		$curre_product_categories = wp_get_post_terms($variation_post->ID, 'product_cat', array('fields' => 'ids'));
		?>
		<p class="form-field">
			<label><?php _e('Variation Categories - note: if none are selected then this variation won\'t be shown', 'woocommerce'); ?></label>
			<select name="variation_cat[<?php echo $variation_post->ID; ?>][]" class="js-bg-basic-multiple" multiple="multiple" data-placeholder="<?php _e('Search Product Category', 'woocommerce'); ?>">
				<?php
				foreach ($product_categories as $key_categories => $value_categories) {
					echo '<option value="' . $value_categories->term_id . '" ' . ((in_array($value_categories->term_id, $curre_product_categories)) ? 'selected' : '') . '>' . $value_categories->name . '</option>';
				}
				?>
			</select>
		</p>
		<style type="text/css">
			.js-bg-basic-multiple {
				width: 100%;
			}
		</style>
		<script>
			jQuery(document).ready(function() {
				jQuery('.js-bg-basic-multiple').select2();
			});
		</script>
	<?php
		echo "</div>";
	}

	public function WSSVSC_woo_add_custom_variation_fields_save($post_id)
	{
		$product = wc_get_product($post_id);

		foreach ($product->get_variation_attributes() as $taxonomya => $terms_sluga) {
			// wp_set_post_terms($post_id, $terms_sluga, ltrim($taxonomya, 'attribute_'));
		}

		// // applies parent categories to variation
		// if (!metadata_exists('post', $post_id, '_wssvsc_exclude')) {
		// 	$parent_product_id = wp_get_post_parent_id($post_id);
		// 	if ($parent_product_id) {
		// 		$terms = (array) wp_get_post_terms($parent_product_id, 'product_cat', array('fields' => 'ids'));
		// 		wp_set_post_terms($post_id, $terms, 'product_cat');
		// 	}
		// }

		// update categories
		wp_set_post_terms($post_id, $_POST['variation_cat'][$post_id], 'product_cat');

		if ($_wssvsc_custom_name = $_POST['_wssvsc_custom_name'][$post_id]) {
			update_post_meta($post_id, '_wssvsc_custom_name', esc_attr($_wssvsc_custom_name));
		}

		if ($_wssvsc_exclude = $_POST['_wssvsc_exclude'][$post_id]) {
			update_post_meta($post_id, '_wssvsc_exclude', esc_attr($_wssvsc_exclude));
		}
	}

	public function WSSVSC_admin_menu()
	{
		add_options_page('Woo Variation Settings', 'Woo Variation Settings', 'manage_options', 'WSSVSC', array($this, 'WSSVSC_page'));
	}

	public function WSSVSC_page()
	{
	?>
		<div>
			<h2><?php _e('WooCommerce Shop & Category Setting', 'gmwsvs'); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields('gmwsvs_options_group');
				$gmwsvs_enable_setting = get_option('gmwsvs_enable_setting');
				$gmwsvs_hide_parent_product = get_option('gmwsvs_hide_parent_product');
				$gmwsvs_optionc = get_option('gmwsvs_optionc');
				$gmwsvs_exclude_cat = get_option('gmwsvs_exclude_cat');
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="gmwsvs_enable_setting"><?php _e('Enable', 'gmwsvs'); ?></label>
						</th>
						<td>
							<input class="regular-text" type="checkbox" id="gmwsvs_enable_setting" <?php echo (($gmwsvs_enable_setting == 'yes') ? 'checked' : ''); ?> name="gmwsvs_enable_setting" value="yes" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php _e('Option', 'gmtrip'); ?></label></th>
						<td>
							<input type="radio" name="gmwsvs_optionc" <?php echo ($gmwsvs_optionc == 'singlevari') ? 'checked' : ''; ?> value="singlevari"><?php _e('WooCommerce Show Variations As Single Product On Shop & Category', 'gmwsvs'); ?><br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="gmwsvs_hide_parent_product"><?php _e('Variable Parent Product', 'gmwsvs'); ?></label>
						</th>
						<td>
							<input class="regular-text" type="checkbox" id="gmwsvs_hide_parent_product" <?php echo (($gmwsvs_hide_parent_product == 'yes') ? 'checked' : ''); ?> name="gmwsvs_hide_parent_product" value="yes" />
							<?php _e('Hide Parent Product of Variable Product', 'gmwsvs'); ?>
							<p class="description"><?php _e('<strong>Note:</strong> This option will be work for just <strong>Show Variations As Single Product</strong>', 'gmwsvs'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Include Category', 'gmwsvs'); ?></label>
						</th>
						<td>
							<ul class="gmwsvs_exclude">
								<?php
								$args = array(
									'taxonomy'              => 'product_cat',
									'selected_cats'         => $gmwsvs_exclude_cat
								);
								wp_terms_checklist(0, $args);
								?>
							</ul>
						</td>
					</tr>
				</table>
				<input type="hidden" name="action_wssvs_op" value="update">
				<?php submit_button(); ?>
			</form>

		</div>
		<Style>
			.gmwsvs_exclude .children {
				margin-left: 25px;
			}
		</Style>
<?php
	}

	public function WSSVSC_register_settings()
	{
		register_setting('gmwsvs_options_group', 'gmwsvs_enable_setting', array($this, 'gmwsvs_accesstoken_callback'));
		register_setting('gmwsvs_options_group', 'gmwsvs_optionc', array($this, 'gmwsvs_accesstoken_callback'));
		register_setting('gmwsvs_options_group', 'gmwsvs_hide_parent_product', array($this, 'gmwsvs_accesstoken_callback'));

		if (isset($_REQUEST['action_wssvs_op']) && $_REQUEST['action_wssvs_op'] == 'update') {
			update_option('gmwsvs_exclude_cat', $_REQUEST['tax_input']['product_cat']);
		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'run_process') {
			wp_redirect(get_admin_url() . 'options-general.php?page=WSSVSC&msg=success');
			exit;
		}
	}

	public function gmwsvs_accesstoken_callback($option)
	{
		return $option;
	}
}
