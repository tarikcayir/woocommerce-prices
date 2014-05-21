<?php
/*

Plugin Name: WooCommerce Prices
Description: This plugin allows for custom editing of product prices in WooCommerce. Thanks to Anthony for sponsoring this plugin.
Version: 1.0.2
Author: sydcode
Author URI: http://profiles.wordpress.org/sydcode
Text Domain: woocommerce_prices

Requires: Wordpress 3.3+

Instructions:
1. Upload the `woocommerce-prices` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit prices using the link in the products menu.

Support: 
http://profiles.wordpress.org/sydcode
http://www.freelancer.com.au/u/sydcode.html

*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit; 
}

add_action('plugins_loaded', array(WooPrices::get_instance(), 'setup'));

class WooPrices {
	
	protected static $instance = null;
	const PLUGIN_VERSION = '1.0.1';
	const PLUGIN_SLUG = 'woocommerce_prices';

 /**
	* Constructor
	*/
	function __construct() {}
	
 /**
	* Get instance
	*/	
	public static function get_instance() {
		null === self::$instance and self::$instance = new self;
	 	return self::$instance;
	}		
	
 /**
	* Setup
	*/	
	public function setup() {
		add_action('admin_menu', array($this, 'admin_menu'));		
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));		
		add_action('wp_ajax_get_terms_options', array($this, 'get_terms_options'));
		add_action('wp_ajax_get_products', array($this, 'get_products'));
		add_action('wp_ajax_save_products', array($this, 'save_products'));
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
	}
	
 /**
	* Create products submenu
	*/
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=product', 
			'Prices', 
			'Prices', 
			'manage_options', 
			self::PLUGIN_SLUG, 
			array($this, 'admin_template')
		);
	}

 /**
	* Create action link
	*
	* @return array	
	*/	
	public function plugin_action_links($links) { 
		$link = "<a href='edit.php?post_type=product&page=" . self::PLUGIN_SLUG . "'>Prices</a>"; 
		array_unshift($links, $link); 
		return $links; 
	}	

 /**
	* Load admin template
	*
	* @return text
	*/
	public function admin_template() {
		if (!current_user_can('manage_options'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		else {
			ob_start();
			include('template.php');
			return ob_end_flush();
		}
	}	

 /**
	* Load stylesheets and scripts for admin template
	*/
	public function admin_enqueue_scripts($hook) {
		// Only load on plugin page
		if ('product_page_woocommerce_prices' != $hook) {
			return;
		}
		// jQuery SlickGrid stylesheets
		$url = plugins_url('/slickgrid/slick.grid.css', __FILE__);
		wp_enqueue_style('jquery-slickgrid', $url, array(), '2.2');
		$url = plugins_url('/slickgrid/slick-default-theme.css', __FILE__);
		wp_enqueue_style('jquery-slickgrid-theme', $url, array(), '2.2');			
		$url = plugins_url('/slickgrid/slick.columnpicker.css', __FILE__);
		wp_enqueue_style('jquery-slickgrid-columnpicker', $url, array(), '2.2');			
		$url = plugins_url('/slickgrid/jquery.event.drag-2.2.js', __FILE__);			
		// jQuery SlickGrid scripts
		wp_enqueue_script('jquery-event-drag', $url, array('jquery'), '2.2');			
		$url = plugins_url('/slickgrid/slick.core.js', __FILE__);
		wp_enqueue_script('jquery-slickgrid-core', $url, array('jquery', 'jquery-ui-core', 'jquery-event-drag'), '2.2');	
		$url = plugins_url('/slickgrid/slick.grid.js', __FILE__);
		wp_enqueue_script('jquery-slickgrid-grid', $url, array('jquery-slickgrid-core'), '2.2');	
		$url = plugins_url('/slickgrid/slick.editors.js', __FILE__);
		wp_enqueue_script('jquery-slickgrid-editors', $url, array('jquery-slickgrid-core'), '2.2');		
		$url = plugins_url('/slickgrid/slick.checkboxselectcolumn.js', __FILE__);
		wp_enqueue_script('jquery-slickgrid-checkboxselectcolumn', $url, array('jquery-slickgrid-core'), '2.2');	
		$url = plugins_url('/slickgrid/slick.rowselectionmodel.js', __FILE__);
		wp_enqueue_script('jquery-slickgrid-rowselectionmodel', $url, array('jquery-slickgrid-core'), '2.2');					
		$url = plugins_url('/slickgrid/slick.columnpicker.js', __FILE__);
		wp_enqueue_script('jquery-slickgrid-columnpicker', $url, array('jquery-slickgrid-core'), '2.2');				
		// jQuery Smoothness theme
		$url = plugins_url('/smoothness/jquery-ui-1.10.3.custom.min.css', __FILE__);
		wp_enqueue_style('jquery-smoothness', $url, array(), '1.10.3');	
		// Accounting.JS script
		$url = plugins_url('accounting.min.js', __FILE__);
		wp_enqueue_script('accounting-js', $url, array(), '0.3.2');	
		// Plugin stylesheet and script
		$url = plugins_url('style.css', __FILE__);
		wp_enqueue_style(self::PLUGIN_SLUG, $url, array(), self::PLUGIN_VERSION);	
		$url = plugins_url('script.js', __FILE__);
		$args = array(
			'jquery', 
			'jquery-ui-core',
			'jquery-ui-datepicker',
			'jquery-slickgrid-core',
			'jquery-slickgrid-grid',
			'accounting-js'
		);
		wp_enqueue_script(self::PLUGIN_SLUG, $url, $args, self::PLUGIN_VERSION);
		$data = array(
			'pluginURL' => plugins_url('', __FILE__),
			'decimalSeparator' => get_option('woocommerce_price_decimal_sep')
		);
		wp_localize_script(self::PLUGIN_SLUG, 'wooPrices', $data);
	}	
	
 /**
	* Get options for taxonomy dropdown
	*
	* @return string
	*/
	public function get_taxonomy_options() {	
		$options = "<option value=''>Select a taxonomy...</option>";
		$taxonomies = get_object_taxonomies('product', 'objects'); 
		foreach($taxonomies as $taxonomy) {
			$options .= sprintf("<option value='%s'>%s</option>", $taxonomy->name, $taxonomy->label);
		}
		return $options;
	}
	
 /**
	* Get options for terms dropdown
	*
	* @return string	
	*/
	public function get_terms_options() {	
		// Security check
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wooprices')) {
			wp_die(__('Security check failed', 'woocommerce_prices'));	
		}
		// Get terms
		$taxonomy = $_POST['taxonomy'];
		$options = "<option value=''>Select a term...</option>";
		if (!empty($taxonomy)) {
			$terms = get_terms($taxonomy); 
			foreach($terms as $term) {
				$options .= sprintf("<option value='%s'>%s</option>", $term->term_id, $term->name);
			}
		}
		exit($options);
	}	
	
 /**
	* Get product data for grid
	*
	* @return string	
	*/
	public function get_products() {
		// Check that WooCommerce is active
		if (!function_exists('wc_format_localized_price')) {
			wp_die(__('WooCommerce Prices requires WooCommerce', 'woocommerce_prices'));
		}		
		// Security check
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wooprices')) {
			wp_die(__('Security check failed', 'woocommerce_prices'));
		}
		// Get input data
		$category = empty($_POST['category']) ? '' : $_POST['category'];
		$taxonomy = empty($_POST['taxonomy']) ? '' : $_POST['taxonomy'];
		$term_id = empty($_POST['term_id']) ? '' : $_POST['term_id'];
		// Get posts for simple products
		$args = array(
			'posts_per_page' => -1, 
			'post_type' => 'product',
			'tax_query' => array(array(
				'taxonomy' => 'product_type',
				'field' => 'slug',
				'terms' => 'simple'
			))
		);
		// Filter for category or other taxonomy
		if (!empty($category) && $category > 0) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => 'simple'
				),			
				array(
					'taxonomy' => 'product_cat',
					'field' => 'id',
					'terms' => $category
				)
			);
		} elseif (!empty($taxonomy) && !empty($term_id)) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => 'simple'
				),			
				array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $term_id
				)
			);
		}
		$posts = get_posts($args);
		// Build grid data
		$data = array();
		$url = plugins_url('images/delete.png', __FILE__);
		foreach ($posts as $post) {
			// Get prices
			$regular_price = get_post_meta($post->ID, '_regular_price', true);
			if ('' != $regular_price) {
				$regular_price = wc_format_localized_price($regular_price);
			}
			$sale_price = get_post_meta($post->ID, '_sale_price', true);
			if ('' != $sale_price) {
				$sale_price = wc_format_localized_price($sale_price);
			}
			// Get sale dates
			$sale_from = get_post_meta($post->ID, '_sale_price_dates_from', true);
			if ('' != $sale_from) {
				$sale_from = date('Y-m-d', $sale_from);
			}
			$sale_to = get_post_meta($post->ID, '_sale_price_dates_to', true);
			if ('' != $sale_to) {
				$sale_to = date('Y-m-d', $sale_to);
			}
			// Create row object
			$product = new stdClass();
			$product->ID = $post->ID;
			$product->title = $post->post_title;
			$product->regular_price = $regular_price;
			$product->sale_price = $sale_price;			
			$product->sale_from = $sale_from;
			$product->sale_to = $sale_to;
			$data[] = $product; 	
		}
		// Output grid data
		$data = json_encode($data);
		exit($data);		
	}
	
 /**
	* Save grid data to products
	*/
	public function save_products() {
		// Check that WooCommerce is active
		if (!function_exists('wc_format_decimal')) {
			wp_die(__('WooCommerce Prices requires WooCommerce', 'woocommerce_prices'));
		}		
		// Security check
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wooprices')) {
			wp_die(__('Security check failed', 'woocommerce_prices'));
		}	
		$products = $_POST['products'];
		// Update sale price and dates
		foreach ($products as $product) {
			$ID = $product['ID'];
			$regular_price = wc_format_decimal($product['regular_price']);
			$sale_price = wc_format_decimal($product['sale_price']);
			$sale_from = strtotime($product['sale_from']);
			$sale_to = strtotime($product['sale_to']);
			// Update prices
			update_post_meta($ID, '_regular_price', $regular_price);
			update_post_meta($ID, '_sale_price', $sale_price);
			update_post_meta($ID, '_sale_price_dates_from', $sale_from);
			update_post_meta($ID, '_sale_price_dates_to', $sale_to);
			// Update current price
			if ('' == $sale_price) {
				update_post_meta($ID, '_price', $regular_price);
			} else {
				$sale_active = true;
				$now = time();
				// Sale has not begun
				if (!empty($sale_from) && $sale_from > $now) {
					$sale_active = false;
				}
				// Sale has finished
				if (!empty($sale_to) && $sale_to < $now) {
					$sale_active = false;
				}							
				if ($sale_active) {
					update_post_meta($ID, '_price', $sale_price);
				} else {
					update_post_meta($ID, '_price', $regular_price);
				}
			}		
		}
		exit;
	}
	
} // End Class WooPrices