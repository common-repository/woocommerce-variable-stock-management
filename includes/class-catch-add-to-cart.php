<?php
/**
 * GQ
 *
 * @package   gq
 * @author    vimes1984 <churchill.c.j@gmail.com>
 * @license   GPL-2.0+
 * @link      http://buildawebdoctor.com
 * @copyright 2-7-2015 BAWD
 */

/**
 * GQ class.
 *
 * @package GQ
 * @author  vimes1984 <churchill.c.j@gmail.com>
 */
class catchaddtocart{
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = "1.0.0";

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = "gq";

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

           add_filter( 'woocommerce_add_to_cart_validation', array($this, 'my_filter_variable_add_to_cart_validation'), 10, 3 );
	}
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn"t been set, set it now.
		if (null == self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}
  /**
   *
   */
  public function my_filter_variable_add_to_cart_validation($valid, $product_id, $quantity ){

    $product = wc_get_product( $product_id );

    if('variable' != $product->product_type){

      return $valid;
    }

    if (!isset($_POST['variation_id']) || !preg_match('/^[0-9]+$/', $_POST['variation_id'])) {

      return $valid;
    } 

    $variation = wc_get_product( $_POST['variation_id'] );

    if (!$variation || !is_object($variation) || 'WC_Product_Variation' != get_class($variation)) {

      return $valid;
    }

    $variation_id = $variation->variation_id;

    $backorder = get_post_meta( $variation_id, '_backorders', true );

    if('no' != $backorder && 'notify' != $backorder){

      return $valid;
    }

    $deductornot = get_post_meta( $variation_id, '_deductornot', true );

    if('yes' != $deductornot){

      return $valid;
    }

    $deductamount = get_post_meta( $variation_id, '_deductamount', true );

    $currentstock = $product->get_stock_quantity();

    $reduceamount = intval($quantity) * intval($deductamount);

    if( $reduceamount - $currentstock > 0 ){

      $currentavail = intval($currentstock / $deductamount);

      $variation = wc_get_product( $variation_id );

      $we_have = '<b>' . $currentavail . '</b>' . ' "' . $variation->get_formatted_name() . '"';

      $valid = false;

      wc_add_notice( ''.__( 'You that goes over our available stock amount.' , 'woocommerce' ) . 
      __( 'We have: ' , 'woocommerce' ) . $we_have . __( ' available.' , 'woocommerce' ) , 'error' );
    }

    return $valid;
  }
}
