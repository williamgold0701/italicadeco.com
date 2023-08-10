<?php
/**
 * Base Class
 *
 * @package YITH\CatalogMode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_WooCommerce_Catalog_Mode' ) ) {

	/**
	 * Implements features of YITH WooCommerce Catalog Mode plugin
	 *
	 * @class   YITH_WooCommerce_Catalog_Mode
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\CatalogMode
	 */
	class YITH_WooCommerce_Catalog_Mode {

		/**
		 * Panel object
		 *
		 * @since   1.0.0
		 * @var     /Yit_Plugin_Panel object
		 * @see     plugin-fw/lib/yit-plugin-panel.php
		 */
		protected $panel;

		/**
		 * Premium version landing link
		 *
		 * @var string
		 */
		protected $premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-catalog-mode/';

		/**
		 * Yith WooCommerce Catalog Mode panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wc_catalog_mode_panel';

		/**
		 * Single instance of the class
		 *
		 * @since 1.3.0
		 * @var YITH_WooCommerce_Catalog_Mode
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WooCommerce_Catalog_Mode
		 * @since 1.3.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function __construct() {

			// Load Plugin Framework.
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

			// Add action links.
			add_filter( 'plugin_action_links_' . plugin_basename( YWCTM_DIR . '/' . basename( YWCTM_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			$this->include_files();

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_admin' ) );
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );

			if ( ! is_admin() || $this->is_quick_view() || wp_doing_ajax() ) {

				add_action( 'init', array( $this, 'check_disable_shop' ), 11 );
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'hide_add_to_cart_loop' ), 5 );
				add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'hide_add_to_cart_loop_alt' ), 10, 2 );
				add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'avoid_add_to_cart' ), 10, 2 );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_frontend' ) );
				add_filter( 'ywctm_css_classes', array( $this, 'hide_atc_single_page' ) );
				add_filter( 'ywctm_css_classes', array( $this, 'hide_cart_widget' ) );

				if ( defined( 'YITH_WCWL' ) && YITH_WCWL ) {
					add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'hide_add_to_cart_wishlist' ), 10, 2 );
				}
			}

		}

		/**
		 * Files inclusion
		 *
		 * @return  void
		 * @since   2.0.0
		 */
		public function include_files() {
			include_once 'includes/ywctm-functions.php';
		}

		/**
		 * ADMIN FUNCTIONS
		 */

		/**
		 * Enqueue script file
		 *
		 * @return  void
		 * @since   2.0.0
		 */
		public function enqueue_scripts_admin() {

			wp_register_style( 'ywctm-admin', yit_load_css_file( YWCTM_ASSETS_URL . 'css/admin.css' ), array(), YWCTM_VERSION );

			if ( ! empty( $_GET['page'] ) && ( sanitize_text_field( wp_unslash( $_GET['page'] ) ) === $this->panel_page || 'yith_vendor_ctm_settings' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_enqueue_style( 'ywctm-admin' );
			}

		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return  void
		 * @since   1.0.0
		 * @use     /Yit_Plugin_Panel class
		 * @see     plugin-fw/lib/yit-plugin-panel.php
		 */
		public function add_menu_page() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			if ( defined( 'YWCTM_PREMIUM' ) && YWCTM_PREMIUM ) {
				$admin_tabs = array(
					'premium-settings' => esc_html_x( 'Settings', 'general settings tab name', 'yith-woocommerce-catalog-mode' ),
					'exclusions'       => esc_html_x( 'Exclusion List', 'exclusion settings tab name', 'yith-woocommerce-catalog-mode' ),
					'inquiry-form'     => esc_html_x( 'Inquiry Form', 'inquiry form settings tab name', 'yith-woocommerce-catalog-mode' ),
					'buttons-labels'   => esc_html_x( 'Buttons & Labels', 'buttons & labels settings tab name', 'yith-woocommerce-catalog-mode' ),
				);
				$help_tab   = array(
					'main_video' => array(
						/* translators: %1$s opening B tag - %2$s closing B tag */
						'desc' => sprintf( _x( 'Check this video to learn how to %1$sconvert your shop into a product catalog%2$s', '[HELP TAB] Video title', 'yith-woocommerce-catalog-mode' ), '<b>', ':</b>' ),
						'url'  => array(
							'it' => 'https://www.youtube.com/embed/5i8fTXTw97I',
							'en' => 'https://www.youtube.com/embed/Ku_8Yk3cDTg',
							'es' => 'https://www.youtube.com/embed/WX80if_6gEE',
						),
					),
					'playlists'  => array(
						'it' => 'https://www.youtube.com/watch?v=5i8fTXTw97I&list=PL9c19edGMs09CTincDLWuCumR9A7JwZ4C',
						'en' => 'https://www.youtube.com/watch?v=Ku_8Yk3cDTg&list=PLDriKG-6905mo3NWj8er7QVNirWeENSdy',
						'es' => 'https://www.youtube.com/watch?v=WX80if_6gEE&list=PL9Ka3j92PYJO9UgIkP3Yv53Nqf1uk5Tv0',
					),
					'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/4402976774161-YITH-WOOCOMMERCE-CATALOG-MODE',
				);
			} else {
				$admin_tabs = array(
					'settings' => esc_html__( 'Settings', 'yith-woocommerce-catalog-mode' ),
				);
				$help_tab   = array();
			}

			$args = array(
				'create_menu_page' => true,
				'plugin_slug'      => YWCTM_SLUG,
				'is_free'          => true,
				'parent_slug'      => '',
				'page_title'       => 'YITH WooCommerce Catalog Mode',
				'menu_title'       => 'Catalog Mode',
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YWCTM_DIR . '/plugin-options',
				'class'            => yith_set_wrapper_class(),
				'help_tab'         => $help_tab,
			);

			if ( ! defined( 'YWCTM_PREMIUM' ) ) {
				$args['premium_tab'] = array(
					'premium_features' => array(
						/* translators: %1$s opening B tag, %2$s closing B tag */
						sprintf( esc_html__( 'Enable the catalog mode rules %1$s only for guest %2$s or for %1$s users from specific countries %2$s  (Example: hide prices or add to cart buttons only to users from USA)', 'yith-woocommerce-catalog-mode' ), '<b>', '</b>' ),
						/* translators: %1$s opening B tag, %2$s closing B tag */
						sprintf( esc_html__( 'Enable the catalog mode on %1$s specific time ranges and/or dates only %2$s (Example: prevent new orders on Sunday or in December during Christmas holidays)', 'yith-woocommerce-catalog-mode' ), '<b>', '</b>' ),
						/* translators: %1$s opening B tag, %2$s closing B tag */
						sprintf( esc_html__( 'Use the %1$s Exclusion List %2$s to enable or disable the catalog mode %1$s only on specific products, categories or tag of your shop %2$s', 'yith-woocommerce-catalog-mode' ), '<b>', '</b>' ),
						esc_html__( 'Hide the Add to Cart button only on specific products and the product prices to all users or to guest users only', 'yith-woocommerce-catalog-mode' ),
						/* translators: %1$s opening B tag, %2$s closing B tag */
						sprintf( esc_html__( 'Use the Advanced Builder to %1$s create and design custom buttons or labels %2$s to replace add to cart and price in shop page and product page', 'yith-woocommerce-catalog-mode' ), '<b>', '</b>' ),
						/* translators: %1$s opening B tag, %2$s closing B tag */
						sprintf( esc_html__( '%1$s Add a custom inquiry form on the product page %2$s using the default form or choosing a plugin between Contact Form 7, Gravity Form, Ninja Forms, Formidable Forms or WP Forms', 'yith-woocommerce-catalog-mode' ), '<b>', '</b>' ),
						'<b>' . esc_html__( 'Regular updates, Translations and Premium Support', 'yith-woocommerce-catalog-mode' ) . '</b>',
					),
					'main_image_url'   => YWCTM_ASSETS_URL . 'images/get-premium-catalog-mode.jpg',
				);
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );

		}

		/**
		 * FRONTEND FUNCTIONS
		 */

		/**
		 * Check if shop must be disabled
		 *
		 * @return  void
		 * @since   2.0.3
		 */
		public function check_disable_shop() {
			if ( $this->disable_shop() ) {
				$priority = has_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ) );
				remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), $priority );
				add_filter( 'get_pages', array( $this, 'hide_cart_checkout_pages' ) );
				add_filter( 'wp_get_nav_menu_items', array( $this, 'hide_cart_checkout_pages' ) );
				add_filter( 'wp_nav_menu_objects', array( $this, 'hide_cart_checkout_pages' ) );
				add_action( 'wp', array( $this, 'check_pages_redirect' ) );
			}
		}

		/**
		 * Check if catalog mode is enabled for administrator
		 *
		 * @return  boolean
		 * @since   2.0.0
		 */
		public function check_user_admin_enable() {

			$vendor_id = ( defined( 'YWCTM_PREMIUM' ) && YWCTM_PREMIUM ) ? ywctm_get_vendor_id() : '';

			return ( ( current_user_can( 'administrator' ) || current_user_can( 'manage_vendor_store' ) ) && is_user_logged_in() && ( 'no' === get_option( 'ywctm_admin_view' . $vendor_id ) ) );
		}

		/**
		 * Removes Cart and checkout pages from menu
		 *
		 * @param array $pages Menu pages.
		 *
		 * @return  array
		 * @since   1.0.4
		 */
		public function hide_cart_checkout_pages( $pages ) {

			$excluded_pages = array(
				wc_get_page_id( 'cart' ),
				wc_get_page_id( 'checkout' ),
			);

			foreach ( $pages as $key => $page ) {
				if ( in_array( current_filter(), array( 'wp_get_nav_menu_items', 'wp_nav_menu_objects' ), true ) ) {
					$page_id = isset( $page->object_id ) ? $page->object_id : 0;

					if ( 'page' !== $page->object || 0 === $page_id ) {
						continue;
					}
				} else {
					$page_id = $page->ID;
				}

				if ( in_array( (int) $page_id, $excluded_pages, true ) ) {
					unset( $pages[ $key ] );

				}
			}

			return $pages;

		}

		/**
		 * Checks if "Cart & Checkout pages" needs to be hidden
		 *
		 * @return  boolean
		 * @since   1.0.2
		 */
		public function check_hide_cart_checkout_pages() {

			return $this->check_user_admin_enable() && $this->disable_shop();

		}

		/**
		 * Avoid Cart and Checkout Pages to be visited
		 *
		 * @return  void
		 * @since   1.0.4
		 */
		public function check_pages_redirect() {

			$cart     = is_page( wc_get_page_id( 'cart' ) );
			$checkout = is_page( wc_get_page_id( 'checkout' ) );

			wp_reset_postdata();

			if ( $cart || $checkout ) {
				wp_safe_redirect( home_url() );
				exit;
			}

		}

		/**
		 * Disable Shop
		 *
		 * @return  boolean
		 * @since   1.0.0
		 */
		public function disable_shop() {

			$disabled = false;

			if ( get_option( 'ywctm_disable_shop' ) === 'yes' ) {

				global $product;

				$product_id = $product && $product instanceof WC_Product ? $product->get_id() : '';

				if ( ywctm_is_wpml_active() && apply_filters( 'ywctm_wpml_use_default_language_settings', false ) ) {
					$product_id = yit_wpml_object_id( $product_id, 'product', true, wpml_get_default_language() );
				}

				$disabled = $this->apply_catalog_mode( $product_id );

			}

			return $disabled;

		}

		/**
		 * Check if Catalog mode must be applied to current user
		 *
		 * @param integer $product_id The product ID.
		 *
		 * @return  boolean
		 * @since   1.3.0
		 */
		public function apply_catalog_mode( $product_id ) {

			$apply = false;

			if ( ! $this->check_user_admin_enable() ) {
				$target_users = apply_filters( 'ywctm_get_vendor_option', get_option( 'ywctm_apply_users', 'all' ), $product_id, 'ywctm_apply_users' );

				$apply = 'all' === $target_users || ! is_user_logged_in();

				if ( is_callable( array( $this, 'country_check' ) ) ) {
					$apply = $this->country_check( $apply, $product_id );
				}

				// Applies date and time check only if the user needs to have Catalog Mode applied.
				if ( $apply ) {
					if ( is_callable( array( $this, 'timeframe_check' ) ) ) {
						$apply = $this->timeframe_check( $apply );
					}

					if ( is_callable( array( $this, 'dateframe_check' ) ) ) {
						$apply = $this->dateframe_check( $apply );
					}
				}
			}

			return apply_filters( 'ywctm_applied_roles', $apply, $product_id );

		}

		/**
		 * Hides "Add to cart" button, if not excluded, from loop page
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function hide_add_to_cart_loop() {

			$ywctm_modify_woocommerce_after_shop_loop_item = apply_filters( 'ywctm_modify_woocommerce_after_shop_loop_item', true );

			if ( $this->check_hide_add_cart() ) {

				if ( $ywctm_modify_woocommerce_after_shop_loop_item ) {
					remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}
				add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', 10 );

			} else {

				if ( $ywctm_modify_woocommerce_after_shop_loop_item ) {
					add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
				}
				remove_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string', 10 );

			}

		}

		/**
		 * Hides "Add to cart" button, if not excluded, from loop page (alternative method)
		 *
		 * @param string     $html    The button HTML.
		 * @param WC_Product $product The current product.
		 *
		 * @return  string
		 * @since   1.0.0
		 */
		public function hide_add_to_cart_loop_alt( $html, $product ) {
			return $this->check_hide_add_cart( false, $product->get_id() ) ? '' : $html;
		}

		/**
		 * Checks if "Add to cart" needs to be hidden
		 *
		 * @param boolean         $single            Check if is single page.
		 * @param integer|boolean $product_id        The product ID.
		 * @param boolean         $ignore_variations Should ignore variations.
		 *
		 * @return  boolean
		 * @since   1.0.0
		 */
		public function check_hide_add_cart( $single = false, $product_id = false, $ignore_variations = false ) {

			$hide = false;

			if ( apply_filters( 'ywctm_prices_only_on_cart', false ) ) {
				return $hide;
			}

			if ( $this->disable_shop() ) {
				$hide = true;
			} else {

				if ( $product_id ) {
					$product = wc_get_product( $product_id );
				} else {
					global $product;
					if ( ! $product instanceof WC_Product ) {
						global $post;
						$product = $post instanceof WP_Post ? wc_get_product( $post->ID ) : false;
					}
				}

				if ( ! $product || ( $product && ! $product instanceof WC_Product ) ) {
					return false;
				}

				if ( ywctm_is_wpml_active() && apply_filters( 'ywctm_wpml_use_default_language_settings', false ) ) {
					$base_product_id = yit_wpml_object_id( $product->get_id(), 'product', true, wpml_get_default_language() );
					$product         = wc_get_product( $base_product_id );

					if ( ! $product ) {
						return false;
					}
				}

				$atc_settings_general = apply_filters( 'ywctm_get_vendor_option', get_option( 'ywctm_hide_add_to_cart_settings' ), $product->get_id(), 'ywctm_hide_add_to_cart_settings' );
				$behavior             = $atc_settings_general['action'];
				$where                = $atc_settings_general['where'];
				$items                = $atc_settings_general['items'];
				$can_hide             = true;
				$exclusion            = false;

				if ( ! $single ) {
					$hide_variations = apply_filters( 'ywctm_get_vendor_option', get_option( 'ywctm_hide_variations' ), $product->get_id(), 'ywctm_hide_variations' );
					/**
					 * APPLY_FILTERS: ywctm_hide_variations_on_loop
					 *
					 * Hide variations only on loop.
					 *
					 * @param boolean $hide_variations Check if variations should be hidden on loop.
					 *
					 * @return boolean
					 */
					$hide_variations = apply_filters( 'ywctm_hide_variations_on_loop', $hide_variations );
					$is_variable     = $product->is_type( 'variable' );
					$is_grouped      = $product->is_type( 'grouped' );
					$can_hide        = ( ( $is_variable || $is_grouped ) ? 'yes' === $hide_variations : true );
				}

				if ( $ignore_variations ) {
					$can_hide = true;
				}

				if ( 'all' !== $items ) {
					$exclusion = apply_filters( 'ywctm_get_exclusion', ( 'hide' === $behavior ? 'show' : 'hide' ), $product->get_id(), 'atc', $behavior );
				}

				if ( ! $single ) {

					switch ( true ) {
						case 'hide' === $behavior && 'all' === $where && 'all' === $items:
						case 'hide' === $behavior && 'shop' === $where && 'all' === $items:
						case 'show' === $behavior && 'product' === $where && 'all' === $items:
						case 'hide' === $behavior && 'all' === $where && 'all' !== $items && 'hide' === $exclusion:
						case 'hide' === $behavior && 'shop' === $where && 'all' !== $items && 'hide' === $exclusion:
						case 'show' === $behavior && 'product' === $where && 'all' !== $items:
						case 'show' === $behavior && 'shop' === $where && 'all' !== $items && 'hide' === $exclusion:
						case 'show' === $behavior && 'all' === $where && 'all' !== $items && 'hide' === $exclusion:
							$hide_add_to_cart = true;
							break;
						default:
							$hide_add_to_cart = false;
					}
				} else {

					switch ( true ) {
						case 'hide' === $behavior && 'all' === $where && 'all' === $items:
						case 'hide' === $behavior && 'product' === $where && 'all' === $items:
						case 'show' === $behavior && 'shop' === $where && 'all' === $items:
						case 'hide' === $behavior && 'all' === $where && 'all' !== $items && 'hide' === $exclusion:
						case 'hide' === $behavior && 'product' === $where && 'all' !== $items && 'hide' === $exclusion:
						case 'show' === $behavior && 'shop' === $where && 'all' !== $items:
						case 'show' === $behavior && 'product' === $where && 'all' !== $items && 'hide' === $exclusion:
						case 'show' === $behavior && 'all' === $where && 'all' !== $items && 'hide' === $exclusion:
							$hide_add_to_cart = true;
							break;
						default:
							$hide_add_to_cart = false;
					}
				}

				// Set "Add to cart" button as hidden.
				if ( $hide_add_to_cart && $this->apply_catalog_mode( $product->get_id() ) && $can_hide ) {
					$hide = true;
				}

				// If "Add to cart" button is set as visible but price is hidden then hide it anyway.
				if ( apply_filters( 'ywctm_check_price_hidden', false, $product->get_id() ) && $can_hide ) {
					$hide = true;
				}

				if ( ! $single ) {
					$hide = apply_filters( 'ywctm_hide_on_loop_anyway', $hide, $product->get_id() );
				} else {
					$hide = apply_filters( 'ywctm_hide_on_single_anyway', $hide, $product->get_id() );
				}
			}

			return $hide;

		}

		/**
		 * Add plugin CSS rules if needed
		 *
		 * @return  void
		 * @since   2.0.0
		 */
		public function enqueue_styles_frontend() {

			/**
			 * APPLY_FILTERS: ywctm_css_classes
			 *
			 * CSS selectors of elements that should be hidden.
			 *
			 * @param array $args The CSS classes array.
			 *
			 * @return array
			 */
			$classes = apply_filters( 'ywctm_css_classes', array() );

			if ( ! empty( $classes ) ) {
				wp_enqueue_style( 'ywctm-frontend', yit_load_css_file( YWCTM_ASSETS_URL . 'css/frontend.css' ), array(), YWCTM_VERSION );
				$css = implode( ', ', $classes ) . '{display: none !important}';
				wp_add_inline_style( 'ywctm-frontend', $css );
			}

		}

		/**
		 * Hide cart widget if needed
		 *
		 * @param array $classes CSS Classes array.
		 *
		 * @return  array
		 * @since   1.3.7
		 */
		public function hide_cart_widget( $classes ) {

			if ( $this->disable_shop() ) {

				$args = array(
					'.widget.woocommerce.widget_shopping_cart',
				);

				$theme_name = ywctm_get_theme_name();

				if ( 'storefront' === $theme_name ) {
					$args[] = '.site-header-cart.menu';
				}

				if ( 'flatsome' === $theme_name ) {
					$args[] = '.cart-item.has-icon.has-dropdown';
				}
				/**
				 * APPLY_FILTERS: ywctm_cart_widget_classes
				 *
				 * CSS selector of cart widgets.
				 *
				 * @param array $args The CSS classes array.
				 *
				 * @return array
				 */
				$classes = array_merge( $classes, apply_filters( 'ywctm_cart_widget_classes', $args ) );

			}

			return $classes;

		}

		/**
		 * Hides "Add to cart" button from single product page
		 *
		 * @param array $classes CSS Classes array.
		 *
		 * @return  array
		 * @since   1.4.4
		 */
		public function hide_atc_single_page( $classes ) {

			if ( $this->check_hide_add_cart( true ) && is_singular() ) {

				$hide_variations = get_option( 'ywctm_hide_variations' );

				$args = array(
					'form.cart button.single_add_to_cart_button',
				);

				if ( ! class_exists( 'YITH_YWRAQ_Frontend' ) || ( ( class_exists( 'YITH_Request_Quote_Premium' ) ) && ! YITH_Request_Quote_Premium()->check_user_type() ) ) {
					$args[] = 'form.cart .quantity';
				}

				if ( 'yes' === $hide_variations ) {
					$args[] = 'table.variations';
					$args[] = 'form.variations_form';
					$args[] = '.single_variation_wrap .variations_button';
				}

				$theme_name = ywctm_get_theme_name();

				if ( 'storefront' === $theme_name ) {
					$args[] = '.storefront-sticky-add-to-cart__content-button';
				}

				/**
				 * APPLY_FILTERS: ywctm_catalog_classes
				 *
				 * CSS selector of add to cart buttons.
				 *
				 * @param array $args The CSS classes array.
				 *
				 * @return array
				 */
				$classes = array_merge( $classes, apply_filters( 'ywctm_catalog_classes', $args ) );

			}

			return $classes;

		}

		/**
		 * Checks if "Add to cart" needs to be avoided
		 *
		 * @param boolean $passed     Add to cart valid checker.
		 * @param integer $product_id The product ID.
		 *
		 * @return  boolean
		 * @since   1.0.5
		 */
		public function avoid_add_to_cart( $passed, $product_id ) {

			if ( apply_filters( 'ywctm_prices_only_on_cart', false ) ) {
				return $passed;
			}

			if ( $this->disable_shop() ) {
				$passed = false;
			} else {

				if ( ywctm_is_wpml_active() && apply_filters( 'ywctm_wpml_use_default_language_settings', false ) ) {
					$product_id = yit_wpml_object_id( $product_id, 'product', true, wpml_get_default_language() );
				}

				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					return true;
				}

				$atc_settings_general = apply_filters( 'ywctm_get_vendor_option', get_option( 'ywctm_hide_add_to_cart_settings' ), $product_id, 'ywctm_hide_add_to_cart_settings' );
				$behavior             = $atc_settings_general['action'];
				$where                = $atc_settings_general['where'];

				if ( 'all' !== $atc_settings_general['items'] ) {
					$behavior = apply_filters( 'ywctm_get_exclusion', ( 'hide' === $behavior ? 'show' : 'hide' ), $product_id, 'atc', $behavior );
				}

				$hide_add_to_cart = ( 'hide' === $behavior && 'all' === $where );

				// Set "Add to cart" button as hidden.
				if ( $hide_add_to_cart && $this->apply_catalog_mode( $product_id ) ) {
					$passed = false;
				}

				// If "Add to cart" button is set as visible but price is hidden then hide it anyway.
				if ( apply_filters( 'ywctm_check_price_hidden', false, $product_id ) ) {
					$passed = false;
				}

				if ( apply_filters( 'ywctm_hide_on_single_anyway', false, $product_id ) && apply_filters( 'ywctm_hide_on_loop_anyway', false, $product_id ) ) {
					$passed = false;
				}
			}

			return $passed;

		}

		/**
		 * Checks if "Add to cart" needs to be hidden
		 *
		 * @param boolean         $x          Unused.
		 * @param integer|boolean $product_id The Product ID.
		 *
		 * @return  bool
		 * @since   1.0.2
		 */
		public function check_add_to_cart_single( $x = true, $product_id = false ) {
			return $this->check_hide_add_cart( true, $product_id );
		}

		/**
		 * Checks if "Add to cart" needs to be hidden from loop page
		 *
		 * @return  boolean
		 * @since   1.0.6
		 */
		public function check_hide_add_cart_loop() {
			return $this->check_hide_add_cart();
		}

		/**
		 * PLUGIN INTEGRATIONS
		 */

		/**
		 * Say if the code is execute by quick view
		 *
		 * @return  boolean
		 * @since   1.0.7
		 */
		public function is_quick_view() {

			$actions = apply_filters( 'ywctm_quick_view_actions', array( 'yith_load_product_quick_view', 'yit_load_product_quick_view' ) );

			return wp_doing_ajax() && isset( $_REQUEST['action'] ) && in_array( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), $actions, true ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Hides add to cart on wishlist
		 *
		 * @param string     $value   Wishlist button.
		 * @param WC_Product $product The Product object.
		 *
		 * @return  string
		 * @since   1.2.2
		 */
		public function hide_add_to_cart_wishlist( $value, $product ) {

			global $yith_wcwl_is_wishlist;

			if ( $this->check_hide_add_cart( true, $product->get_id() ) && $yith_wcwl_is_wishlist ) {

				$value = '';

			}

			return $value;

		}

		/**
		 * Hide add to cart button in quick view
		 *
		 * @return  void
		 * @since   1.0.7
		 */
		public function hide_add_to_cart_quick_view() {

			if ( $this->check_hide_add_cart( true ) ) {

				$hide_variations = get_option( 'ywctm_hide_variations' );
				$args            = array(
					'form.cart button.single_add_to_cart_button',
				);

				$theme_name = ywctm_get_theme_name();

				if ( 'oceanwp' === $theme_name ) {
					$args[] = 'form.cart';
				}

				if ( ! class_exists( 'YITH_YWRAQ_Frontend' ) || ( ( class_exists( 'YITH_Request_Quote_Premium' ) ) && ! YITH_Request_Quote_Premium()->check_user_type() ) ) {
					$args[] = 'form.cart .quantity';
				}

				if ( 'yes' === $hide_variations ) {

					$args[] = 'table.variations';
					$args[] = 'form.variations_form';
					$args[] = '.single_variation_wrap .variations_button';

				}

				/**
				 * APPLY_FILTERS: ywctm_catalog_classes
				 *
				 * CSS selector of add to cart buttons.
				 *
				 * @param array $args The CSS classes array.
				 *
				 * @return array
				 */
				$classes = implode( ', ', apply_filters( 'ywctm_catalog_classes', $args ) );

				?>
				<style type="text/css">
					.ywctm-void, <?php echo esc_attr( $classes ); ?> {
						display: none !important;
					}
				</style>
				<?php
			}

		}

		/**
		 * YITH FRAMEWORK
		 */

		/**
		 * Load plugin framework
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Get the premium landing uri
		 *
		 * @return  string The premium landing link
		 * @since   1.0.0
		 */
		public function get_premium_landing_uri() {
			return apply_filters( 'yith_plugin_fw_premium_landing_uri', $this->premium_landing, YWCTM_SLUG );
		}

		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param array $links links plugin array.
		 *
		 * @return  array
		 * @since   1.0.0
		 * @use     plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {

			$links = yith_add_action_links( $links, $this->panel_page, false );

			return $links;

		}

		/**
		 * Plugin row meta
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param array  $new_row_meta_args Row meta args.
		 * @param array  $plugin_meta       Plugin meta.
		 * @param string $plugin_file       Plugin File.
		 * @param array  $plugin_data       Plugin data.
		 * @param string $status            Status.
		 * @param string $init_file         Init file.
		 *
		 * @return  array
		 * @since   1.0.0
		 * @use     plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWCTM_FREE_INIT' ) {

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YWCTM_SLUG;
			}

			return $new_row_meta_args;

		}

	}

}
