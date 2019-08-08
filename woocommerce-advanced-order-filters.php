<?php
/**
 * Plugin Name: WooCommerce Advanced Order Filters
 * Plugin URI: http://www.skyverge.com/product/woocommerce-filter-orders/
 * Description: Adds the ability to filter orders by attributes, shipping method, or coupon used.
 * Author: Lukas Besch
 * Author URI: https://lukasbesch.com/
 * Version: 1.2.0
 * Text Domain: wc-advanced-order-filters
 *
 * GitHub Plugin URI: lukasbesch/woocommerce-advanced-order-filters/
 * GitHub Branch: master
 *
 * Copyright: (c) 2019 Lukas Besch (connect@lukasbesch.com)
 * Copyright: (c) 2015-2017 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Advanced-Order-Filters
 * @author    Lukas Besch
 * @category  Admin
 * @copyright Copyright (c) 2015-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

defined( 'ABSPATH' ) or exit;

// fire it up!
add_action( 'plugins_loaded', 'wc_advanced_order_filters');


/**
 * Plugin Description
 *
 * Adds custom filtering to the orders screen.
 */
class WC_Advanced_Order_Filters {

	const VERSION = '1.2.0';

	/** @var WC_Advanced_Order_Filters single instance of this plugin */
	protected static $instance;

	/** @var string The Attribute slug */
	public $filterableAttribute;

	/**
	 * WC_Filter_Orders constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load translations
		add_action( 'init', array( $this, 'load_translation' ) );

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {

			$this->filterableAttribute = 'pa_popup-store';

			// adds the additional filtering dropdowns to the orders page
			add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_coupon_used' ) );
			add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipping_method_used' ) );
			add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_attribute' ) );

			// makes filterable
			add_filter( 'posts_join',  array( $this, 'add_order_items_join' ) );
			add_filter( 'posts_where', array( $this, 'add_filterable_where' ) );
		}
	}


	/** Plugin methods ***************************************/


	/**
	 * Adds the coupon filtering dropdown to the orders list
	 *
	 * @since 1.0.0
	 */
	public function filter_orders_by_coupon_used() {
		if ( ! $this->is_orders_page()  ) {
			return;
		}

        $args = array(
            'posts_per_page' => - 1,
            'orderby'        => 'title',
            'order'          => 'asc',
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
        );

        $coupons = get_posts( $args );

        if ( empty( $coupons ) ) {
            return;
        }

		$this->select_html('coupon', $coupons, '_coupons_used', 'post_title');
	}


	/**
	 * Adds the shipping method filtering dropdown to the orders list
	 *
	 * @since 1.2.0
	 */
	public function filter_orders_by_shipping_method_used()
	{
		if (!$this->is_orders_page()) {
			return;
		}

		$shipping_methods = WC()->shipping->get_shipping_methods();

		if (empty($shipping_methods)) {
			return;
		}

		$this->select_html('shipping method', $shipping_methods, '_shipping_method_used', 'method_title');
	}

	/**
	 * Adds the shipping method filtering dropdown to the orders list
	 *
	 * @since 1.2.0
	 */
	public function filter_orders_by_attribute() {
	    if ( ! $this->is_orders_page()  ) {
            return;
		}

		$attributes = get_terms( [
			'taxonomy' => $this->filterableAttribute
		] );

		if ( empty( $attributes ) ) {
		    return;
        }

		?><input type="hidden" name="_attribute_name" value="<?php esc_attr_e( $this->filterableAttribute ); ?>"><?php
        $this->select_html('attribute', $attributes, '_attributes_used', 'slug', 'name');
	}

	/**
     * Print the option for the filter dropdown.
     *
	 * @param $item
	 * @param $getKey
	 * @param $valueKey
	 * @param $labelKey
	 */
	public function option_html($item, $getKey, $valueKey, $labelKey = false) {
	    $labelKey = !empty($labelKey) ? $labelKey : $valueKey;
	    $selected = isset( $_GET[$getKey] ) ? selected( $item->{$valueKey}, $_GET[$getKey], false ) : '';
	    ?>
        <option value="<?php esc_attr_e( $item->{$valueKey} ); ?>" <?php esc_attr_e( $selected ); ?>>
            <?php echo esc_html( $item->{$labelKey} ); ?>
        </option>
        <?php
    }

	/**
     * Print the select for the filter dropdown.
     *
	 * @param      $name
	 * @param      $items
	 * @param      $getKey
	 * @param      $valueKey
	 * @param bool $labelKey
	 */
	public function select_html($name, $items, $getKey, $valueKey, $labelKey = false) {
	    $labelKey = !empty($labelKey) ? $labelKey : $valueKey;
	    ?>
        <select name="<?php esc_attr_e( $getKey ) ?>" id="<?php esc_attr_e( 'dropdown' . $getKey ) ?>">
            <option value="">
                <?php
                /* translators: dropdown placeholder on orders admin screen */
                printf( esc_html__( 'Filter by %d', 'wc-advanced-order-filters' ), $name );
                ?>
            </option>
			<?php
			foreach ( $items as $item ) {
				$this->option_html($item, $getKey, $valueKey, $labelKey);
			}
			?>
        </select>
        <?php
    }


	/**
	 * Modify SQL JOIN for filtering the orders
	 *
	 * @since 1.0.0
	 *
	 * @param string $join JOIN part of the sql query
	 * @return string $join modified JOIN part of sql query
	 */
	public function add_order_items_join( $join ) {
		global $wpdb;

		if ( ! $this->is_orders_page() ) {
			return $join;
		}

		if ( ( isset( $_GET['_coupons_used'] ) && ! empty( $_GET['_coupons_used'] ) )
			|| (isset( $_GET['_shipping_method_used'] ) && ! empty( $_GET['_shipping_method_used'] ) )
			|| (isset( $_GET['_attributes_used'] ) && ! empty( $_GET['_attributes_used'] ) )
		) {
			$join .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_items woi ON {$wpdb->posts}.ID = woi.order_id";
		}

		if ( isset( $_GET['_attributes_used'] ) && ! empty( $_GET['_attributes_used'] ) ) {
			$join .= " LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim ON woi.order_item_id = woim.order_item_id";
		}

		return $join;
	}


	/**
	 * Modify SQL WHERE for filtering the orders
	 *
	 * @since 1.0.0
	 *
	 * @param string $where WHERE part of the sql query
	 * @return string $where modified WHERE part of sql query
	 */
	public function add_filterable_where( $where ) {
		global $wpdb;

		if ( ! $this->is_orders_page() ) {
            return $where;
		}

		// Main WHERE query part
		if ( isset( $_GET['_coupons_used'] ) && ! empty( $_GET['_coupons_used'] ) ) {
			$where .= $wpdb->prepare( " AND woi.order_item_type='coupon' AND woi.order_item_name='%s'", wc_clean( $_GET['_coupons_used'] ) );
		}
		if ( isset( $_GET['_shipping_method_used'] ) && ! empty( $_GET['_shipping_method_used'] ) ) {
			$where .= $wpdb->prepare( " AND woi.order_item_type='shipping' AND woi.order_item_name='%s'", wc_clean( $_GET['_shipping_method_used'] ) );
		}
		if ( isset( $_GET['_attributes_used'], $_GET['_attribute_name'] ) && ! empty( $_GET['_attributes_used'] ) && ! empty( $_GET['_attribute_name'] ) ) {
			$where .= $wpdb->prepare( " AND woim.meta_key='%s' AND woim.meta_value='%s'", wc_clean( $_GET['_attribute_name'] ), wc_clean( $_GET['_attributes_used'] ), );
		}

		return $where;
	}


	/** Helper methods ***************************************/

	/**
	 * Load Translations
	 *
	 * @since 1.0.0
	 */
	public function load_translation() {
		// localization
		load_plugin_textdomain( 'wc-advanced-order-filters', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}


	/**
	 * Main WC_Additional_Order_Filters Instance, ensures only one instance
	 * is/can be loaded
	 *
	 * @return WC_Advanced_Order_Filters
	 *@see WC_Additional_Order_Filters()
	 * @since 1.1.0
	 *
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.1.0
	 */
	public function __clone() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'wc-advanced-order-filters' ), 'Additional Filters for WooCommerce Orders' ), '1.1.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.1.0
	 */
	public function __wakeup() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'wc-advanced-order-filters' ), 'Additional Filters for WooCommerce Orders' ), '1.1.0' );
	}

	/**
     * Checks if current type is a shop_order.
     *
	 * @return bool
	 */
	private function is_orders_page() {
	    global $typenow;
	    return 'shop_order' === $typenow;
    }

}


/**
 * Returns the One True Instance of WC_Additional_Order_Filters
 *
 * @return \WC_Advanced_Order_Filters
 * @since 1.1.0
 *
 */
function wc_advanced_order_filters() {
	return WC_Advanced_Order_Filters::instance();
}
