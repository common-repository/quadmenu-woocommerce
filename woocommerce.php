<?php
/*
 * Plugin Name: QuadMenu - WooCommerce
 * Plugin URI:  http://www.quadmenu.com
 * Description: WooCommerce Mega Menu plugin which allow you to create product sliders and carousel menus.
 * Version:     1.0.0
 * Author:      WooCommerce Mega Menu
 * Author URI:  http://www.quadmenu.com
 * License:     GPL-2.0+
 * Copyright:   2018 QuadMenu (http://www.quadmenu.com)
 * Text Domain: quadmenu
 */

if (!defined('ABSPATH')) {
    die('-1');
}

if (!class_exists('QuadMenu_WooCommerce')) :

    class QuadMenu_WooCommerce {

        function __construct() {

            add_action('admin_notices', array($this, 'required'), 10);

            add_action('admin_init', array($this, 'navmenu'), 40);

            add_filter('quadmenu_item_object_class', array($this, 'item_object_class'), -10, 4);
            add_filter('quadmenu_custom_nav_menu_items', array($this, 'nav_menu_items'));
            add_filter('quadmenu_nav_menu_item_fields', array($this, 'nav_menu_item_fields'));

            if (is_admin())
                return;

            add_action('init', array($this, 'includes'));
        }

        function includes() {

            if (class_exists('QuadMenuItem')) {

                require_once plugin_dir_path(__FILE__) . 'frontend/QuadMenuItemProduct.class.php';
                require_once plugin_dir_path(__FILE__) . 'frontend/QuadMenuItemProductCat.class.php';
            }
        }

        function required() {

            $path = 'quadmenu/quadmenu.php';

            $pro = 'quadmenu-pro/quadmenu.php';

            if (is_plugin_active($path)) {
                return;
            }

            $plugin = plugin_basename($path);

            $link = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1', 'activate-plugin_' . $plugin) . '" class="edit">' . __('activate', 'quadmenu') . '</a>';

            $all_plugins = get_plugins();

            if (isset($all_plugins[$path])) :
                ?>

                <div class="updated">
                    <p>
                        <?php printf(__('QuadMenu Divi requires QuadMenu. Please %s the QuadMenu plugin.', 'quadmenu'), $link); ?>
                    </p>
                </div>
                <?php
            elseif (is_plugin_active($pro)):
                ?>
                <div class="error">
                    <p>
                        <?php printf(__('Please deactivate QuadMenu Divi.', 'quadmenu'), $link); ?>
                    </p>
                </div>
                <?php
            else :
                ?>
                <div class="updated">
                    <p>
                        <?php printf(__('QuadMenu Divi requires QuadMenu. Please install the QuadMenu plugin.', 'quadmenu'), $link); ?>
                    </p>
                    <p class="submit">
                        <a href="<?php echo admin_url('plugin-install.php?tab=search&type=term&s=quadmenu') ?>" class='button button-secondary'><?php _e('Install QuadMenu', 'quadmenu'); ?></a>
                    </p>
                </div>
            <?php
            endif;
        }

        public function navmenu() {

            if (function_exists('is_quadmenu') && is_quadmenu()) {
                require_once plugin_dir_path(__FILE__) . 'backend/product.php';
                require_once plugin_dir_path(__FILE__) . 'backend/product_cat.php';
            }
        }

        function nav_menu_items($items) {

            $items['product'] = array(
                'label' => esc_html__('Product', 'quadmenu'),
                'title' => esc_html__('Product', 'quadmenu'),
                'panels' => array(
                    'general' => array(
                        'title' => esc_html__('General', 'quadmenu'),
                        'icon' => 'dashicons dashicons-admin-settings',
                        'settings' => array('subtitle', 'badge', 'float', 'hidden', 'dropdown'),
                    ),
                    'icon' => array(
                        'title' => esc_html__('Icon', 'quadmenu'),
                        'icon' => 'dashicons dashicons-art',
                        'settings' => array('icon'),
                    ),
                    'product' => array(
                        'title' => esc_html__('Product', 'quadmenu'),
                        'icon' => 'dashicons dashicons-cart',
                        'settings' => array('thumb', 'price', 'rating', 'product_description', 'add_to_cart'),
                    ),
                ),
                'parent' => array('main', 'column', 'custom', 'post_type', 'post_type_archive', 'taxonomy'),
            );

            $items['product_cat'] = array(
                'label' => esc_html__('Product Category', 'quadmenu'),
                'title' => esc_html__('Product Category', 'quadmenu'),
                'panels' => array(
                    'general' => array(
                        'title' => esc_html__('General', 'quadmenu'),
                        'icon' => 'dashicons dashicons-admin-settings',
                        'settings' => array('subtitle', 'badge', 'float', 'hidden', 'dropdown'),
                    ),
                    'icon' => array(
                        'title' => esc_html__('Icon', 'quadmenu'),
                        'icon' => 'dashicons dashicons-art',
                        'settings' => array('icon'),
                    ),
                    'query' => array(
                        'title' => esc_html__('Query', 'quadmenu'),
                        'icon' => 'dashicons dashicons-update',
                        'settings' => array('items', 'limit', 'orderby', 'order'),
                    ),
                    'carousel' => array(
                        'title' => esc_html__('Carousel', 'quadmenu'),
                        'icon' => 'dashicons dashicons-image-flip-horizontal',
                        'settings' => array('speed', 'autoplay', 'autoplay_speed', 'dots', 'pagination', 'category'),
                    ),
                    'product' => array(
                        'title' => esc_html__('Products', 'quadmenu'),
                        'icon' => 'dashicons dashicons-cart',
                        'settings' => array('thumb', 'price', 'rating', 'product_description', 'add_to_cart'),
                    ),
                ),
                'parent' => array('main', 'column', 'custom', 'post_type', 'post_type_archive', 'taxonomy'),
            );

            return $items;
        }

        function nav_menu_item_fields($settings) {

            $settings['category'] = array(
                'id' => 'quadmenu-settings[category]',
                'db' => 'category',
                'type' => 'checkbox',
                'title' => esc_html__('Category', 'quadmenu'),
                'placeholder' => esc_html__('Show category', 'quadmenu'),
                'default' => 'off',
            );

            $settings['items'] = array(
                'id' => 'quadmenu-settings[items]',
                'db' => 'items',
                'type' => 'number',
                'title' => esc_html__('Items', 'quadmenu'),
                'ops' => array(
                    'step' => 1,
                    'min' => 0,
                    'max' => 6
                ),
                'default' => 1,
            );

            $settings['limit'] = array(
                'id' => 'quadmenu-settings[limit]',
                'db' => 'limit',
                'type' => 'number',
                'title' => esc_html__('Limit', 'quadmenu'),
                'ops' => array(
                    'step' => 1,
                    'min' => 1,
                    'max' => 12
                ),
                'default' => 3,
            );

            $settings['orderby'] = array(
                'id' => 'quadmenu-settings[orderby]',
                'db' => 'orderby',
                'type' => 'select',
                'title' => esc_html__('Orderby', 'quadmenu'),
                'ops' => array(
                    'top_rated_products' => esc_html__('Top rated products', 'quadmenu'),
                    'best_selling_products' => esc_html__('Best selling products', 'quadmenu'),
                    'sale_products' => esc_html__('Sale products', 'quadmenu'),
                    'date' => esc_html__('Latest products', 'quadmenu'),
                    'price' => esc_html__('Price', 'quadmenu'),
                    'popularity' => esc_html__('Popularity', 'quadmenu'),
                ),
                'default' => 'date',
            );

            $settings['order'] = array(
                'id' => 'quadmenu-settings[order]',
                'db' => 'order',
                'type' => 'select',
                'title' => esc_html__('Order', 'quadmenu'),
                'ops' => array(
                    'ASC' => esc_html__('Ascending', 'quadmenu'),
                    'DESC' => esc_html__('Descending', 'quadmenu'),
                ),
                'default' => 'DESC',
            );

            $settings['rating'] = array(
                'id' => 'quadmenu-settings[rating]',
                'db' => 'rating',
                'type' => 'checkbox',
                'title' => esc_html__('Rating', 'quadmenu'),
                'placeholder' => esc_html__('Show product rating', 'quadmenu'),
                'default' => 'on',
            );

            $settings['price'] = array(
                'id' => 'quadmenu-settings[price]',
                'db' => 'price',
                'type' => 'checkbox',
                'title' => esc_html__('Price', 'quadmenu'),
                'placeholder' => esc_html__('Show product price', 'quadmenu'),
                'default' => 'on',
            );

            $settings['product_description'] = array(
                'id' => 'quadmenu-settings[product_description]',
                'db' => 'product_description',
                'type' => 'checkbox',
                'title' => esc_html__('Description', 'quadmenu'),
                'placeholder' => esc_html__('Show product description', 'quadmenu'),
                'default' => 'on',
            );

            $settings['add_to_cart'] = array(
                'id' => 'quadmenu-settings[add_to_cart]',
                'db' => 'add_to_cart',
                'type' => 'checkbox',
                'title' => esc_html__('Add To Cart', 'quadmenu'),
                'placeholder' => esc_html__('Show add to cart button', 'quadmenu'),
                'default' => 'on',
            );

            // Carousel
            // -----------------------------------------------------------------

            $settings['speed'] = array(
                'id' => 'quadmenu-settings[speed]',
                'db' => 'speed',
                'type' => 'number',
                'title' => esc_html__('Speed', 'quadmenu'),
                'ops' => array(
                    'step' => 100,
                    'min' => 100,
                    'max' => 10000
                ),
                'default' => 1500,
            );

            $settings['autoplay'] = array(
                'id' => 'quadmenu-settings[autoplay]',
                'db' => 'autoplay',
                'type' => 'checkbox',
                'title' => esc_html__('Autoplay', 'quadmenu'),
                'placeholder' => esc_html__('Run carousel automatically', 'quadmenu'),
                'default' => 'off',
            );

            $settings['autoplay_speed'] = array(
                'id' => 'quadmenu-settings[autoplay_speed]',
                'db' => 'autoplay_speed',
                'type' => 'number',
                'title' => esc_html__('Autoplay Speed', 'quadmenu'),
                'placeholder' => esc_html__('Time between 2 consecutive slides (in ms)', 'quadmenu'),
                'ops' => array(
                    'step' => 100,
                    'min' => 100,
                    'max' => 10000
                ),
                'default' => 500,
            );

            $settings['dots'] = array(
                'id' => 'quadmenu-settings[dots]',
                'db' => 'dots',
                'type' => 'checkbox',
                'placeholder' => esc_html__('Show dots control', 'quadmenu'),
                'title' => esc_html__('Dots', 'quadmenu'),
                'default' => 'on',
            );

            $settings['pagination'] = array(
                'id' => 'quadmenu-settings[pagination]',
                'db' => 'pagination',
                'type' => 'checkbox',
                'placeholder' => esc_html__('Show pagination control', 'quadmenu'),
                'title' => esc_html__('Pagination', 'quadmenu'),
                'default' => 'on',
            );

            return $settings;
        }

        function item_object_class($class, $item, $id, $auto_child = '') {

            switch ($item->quadmenu) {

                case 'product';
                    $class = 'QuadMenuItemProduct';
                    break;

                case 'product_cat':
                    $class = 'QuadMenuItemProductCat';
                    break;
            }

            return $class;
        }

    }

    new QuadMenu_WooCommerce();

endif;