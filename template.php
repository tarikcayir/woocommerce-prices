<?php
/**
 * Template for "Woocommerce Prices" Wordpress plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit; 
}

?>
<div id='wooprices' class='wrap'>

	<div id='icon-woocommerce-prices' class='icon32'><br></div>
	<h2><?php _e('WooCommerce Prices', 'woocommerce_prices'); ?></h2>
	
	<?php if(isset($_POST['wooprices_nonce'])) { ?>
	<div id='message' class='updated'>
		<p><strong><?php esc_attr_e('Settings saved.') ?></strong></p>
	</div>
	<?php } ?>	
	
	<?php wp_nonce_field('wooprices', 'wooprices_nonce'); ?>
	
	<div class='prices-inputs'>
	
		<div class='inputs-groups'>
			<div>
				<label for='select-action'><?php _e('Action', 'woocommerce_prices'); ?>:</label>
				<select id='select-action' name='select-action' class='wooprices-input'>
					<optgroup label='<?php _e('Regular Price', 'woocommerce_prices'); ?>'>					
						<option value='newRegularPrice'><?php _e('New Regular Price', 'woocommerce_prices'); ?></option>
						<option value='increaseRegularPrice'><?php _e('Increase Regular Price', 'woocommerce_prices'); ?> (+)</option>
						<option value='decreaseRegularPrice'><?php _e('Decrease Regular Price', 'woocommerce_prices'); ?> (-)</option>
					</optgroup>				
					<optgroup label='<?php _e('Sale Price', 'woocommerce_prices'); ?>'>
						<option value='newSalePrice' selected='selected'><?php _e('New Sale Price', 'woocommerce_prices'); ?></option>
						<option value='newSaleDiscount'><?php _e('New Sale Discount', 'woocommerce_prices'); ?> (-)</option>
						<option value='increaseSalePrice'><?php _e('Increase Sale Price', 'woocommerce_prices'); ?> (+)</option>
						<option value='decreaseSalePrice'><?php _e('Decrease Sale Price', 'woocommerce_prices'); ?> (-)</option>					
					</optgroup>
					<optgroup label='<?php _e('Sale From', 'woocommerce_prices'); ?>'>			
						<option value='newSaleFrom'><?php _e('New Sale From', 'woocommerce_prices'); ?></option>					
						<option value='increaseSaleFrom'><?php _e('Increase Sale From', 'woocommerce_prices'); ?> (+)</option>
						<option value='decreaseSaleFrom'><?php _e('Decrease Sale From', 'woocommerce_prices'); ?> (-)</option>					
					</optgroup>	
					<optgroup label='<?php _e('Sale To', 'woocommerce_prices'); ?>'>			
						<option value='newSaleTo'><?php _e('New Sale To', 'woocommerce_prices'); ?></option>					
						<option value='increaseSaleTo'><?php _e('Increase Sale To', 'woocommerce_prices'); ?> (+)</option>
						<option value='decreaseSaleTo'><?php _e('Decrease Sale To', 'woocommerce_prices'); ?> (-)</option>					
					</optgroup>	
				</select>
			</div>	
			<div class='action-value-field'>
				<label for='action-value'><?php _e('Value', 'woocommerce_prices'); ?>:</label>
				<input type='text' id='action-value' name='action-value' class='wooprices-input' value='' placeholder='<?php _e('Enter amount or percentage', 'woocommerce_prices'); ?>' />
			</div>
			<div class='sale-days-field'>
				<label for='action-value'><?php _e('Days', 'woocommerce_prices'); ?>:</label>
				<input type='text' id='sale-days' name='sale-days' class='wooprices-input' value='' placeholder='<?php _e('Enter number of days', 'woocommerce_prices'); ?>' />
			</div>			
			<div class='sale-from-field'>
				<label for='sale-from'><?php _e('Sale From', 'woocommerce_prices'); ?>:</label>
				<input type='text' id='sale-from' name='sale-from' class='wooprices-input' value='' />
			</div>
			<div class='sale-to-field'>
				<label for='sale-to'><?php _e('Sale To', 'woocommerce_prices'); ?>:</label>
				<input type='text' id='sale-to' name='sale-to' class='wooprices-input' value='' />
			</div>
		</div>
		
		<div class='inputs-groups'>
			<div>
				<label for='select-category'><?php _e('Category', 'woocommerce_prices'); ?>:</label>
				<?php
					$args = array(
						'id' => 'select-category',
						'name' => 'select-category',
						'class' => 'wooprices-input',
						'hierarchical' => true,
						'taxonomy' => 'product_cat',
						'show_option_none' => 'Select a category...'
					);
					wp_dropdown_categories($args);
				?>
			</div>		
			<div class='select-spacer'><?php _e('OR', 'woocommerce_prices'); ?></div>
			<div>
				<label for='select-taxonomy'><?php _e('Taxonomy', 'woocommerce_prices'); ?>:</label>
				<select id='select-taxonomy' name='select-taxonomy' class='wooprices-input'>
					<?php echo $this->get_taxonomy_options(); ?>
				</select>
			</div>
			<div>
				<label for='select-term'><?php _e('Term', 'woocommerce_prices'); ?>:</label>
				<select id='select-term' name='select-term' class='wooprices-input'></select>	
			</div>
		</div>
		
	<!-- end .prices-inputs --></div>
	
	<div class='prices-buttons'>
		<input type='button' class='button edit-button' value='<?php _e('Edit Prices', 'woocommerce_prices'); ?>' />
		<input type='button' class='button get-button' value='<?php _e('Get Prices', 'woocommerce_prices'); ?>' />
		<input type='button' class='button save-button' value='<?php _e('Save Prices', 'woocommerce_prices'); ?>' />	
		<span class='saving-products'><?php _e('Saving products', 'woocommerce_prices'); ?></span>
		<span class='save-success'><?php _e('Products saved', 'woocommerce_prices'); ?></span>
	</div>

	<div id='prices'>
		<div class='prices-loading'>
			<img src='<?php echo plugins_url('images/loading.gif', __FILE__); ?>' alt='' />
			<p><?php _e('Loading Products', 'woocommerce_prices'); ?></p>
		</div>
		<div id='products-grid'></div>
	</div>

<!-- end #wooprices --></div>
<?php // END