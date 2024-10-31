<?php
if (!defined('ABSPATH')) {
    die('-1');
}

class QuadMenuItemProductCat extends QuadMenuItemProduct {

    protected $type = 'product_cat';
    public $query_args = array();

    function init() {

        $this->product_cat = false;

        if (0 < $this->depth) {

            $this->args->has_description = (bool) $this->item->description;

            $this->args->has_subtitle = (bool) ($this->args->has_subtitle && !$this->args->has_description);

            $this->args->has_thumbnail = (bool) ($this->item->thumb);

            $this->args->has_category = (bool) ($this->item->category === 'on');

            $this->args->has_price = (bool) ($this->item->price === 'on');

            $this->args->has_rating = (bool) ($this->item->rating === 'on');

            $this->args->has_add_to_cart = (bool) ($this->item->add_to_cart === 'on');

            if ($this->args->has_thumbnail) {
                $this->args->has_subtitle = false;
            }
        }
    }

    protected function parse_query_args() {

        $this->query_args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'posts_per_page' => $this->item->limit,
            'tax_query' => array(),
            'orderby' => $this->item->orderby,
            'order' => $this->item->order,
        );

        $this->query_args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'terms' => $this->item->object_id,
            'field' => 'ID',
            'operator' => 'IN',
        );

        if ('sale_products' === $this->item->orderby && function_exists('wc_get_product_ids_on_sale')) {
            $this->query_args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
        }

        if ('best_selling_products' === $this->item->orderby) {
            $this->query_args['meta_key'] = '';
            $this->query_args['order'] = 'DESC';
            $this->query_args['orderby'] = 'meta_value_num';
        }

        return $this->query_args;
    }

    protected function query_products() {
        if ('top_rated_products' === $this->item->orderby && class_exists('WC_Shortcode_Products')) {
            add_filter('posts_clauses', array('WC_Shortcode_Products', 'order_by_rating_post_clauses'));
            query_posts($this->parse_query_args());
            remove_filter('posts_clauses', array('WC_Shortcode_Products', 'order_by_rating_post_clauses'));
        } else {
            query_posts($this->parse_query_args());
        }
    }

    function get_start_el() {

        $item_output = '';

        $this->add_item_description();

        $this->add_item_classes();

        $this->add_item_classes_prefix();

        $this->add_item_classes_current();

        $this->add_item_classes_quadmenu();

        $id = $this->get_item_id();

        $this->item_classes[] = $this->args->has_category ? 'quadmenu-has-category' : '';

        $class = $this->get_item_classes();

        $item_output .= '<li' . $id . $class . '>';

        $this->add_link_atts();

        $this->add_link_atts_toggle();

        $item_output .= $this->get_link();

        return $item_output;
    }

    function get_item_data() {

        return ' data-items="' . esc_attr($this->item->items) . '" data-pagination="' . esc_attr($this->item->pagination) . '" data-dots="' . esc_attr($this->item->dots) . '" data-speed="' . esc_attr($this->item->speed) . '" data-autoplay="' . esc_attr($this->item->autoplay) . '"  data-autoplay_speed="' . esc_attr($this->item->autoplay_speed) . '"';
    }

    function get_products() {

        $this->query_products();
        ?>
        <ul class="owl-carousel" <?php echo $this->get_item_data(); ?>>
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php $this->get_product(); ?>                        
                    <?php
                endwhile;
            endif;
            wp_reset_query();
            ?>
        </ul>
        <?php
    }

    function get_product() {

        $this->item->ID = $this->item->object_id = get_the_ID();

        if (function_exists('wc_get_product')) {
            $this->product = wc_get_product($this->item->object_id);
        }

        if (!$this->product)
            return;

        $this->item_atts['href'] = get_permalink();
        $this->item_atts['title'] = get_the_title(get_the_ID());
        $this->item_atts['target'] = '';
        $this->item_atts['rel'] = '';
        $this->item->title = get_the_title(get_the_ID());
        $this->args->has_icon = false;
        $this->args->has_subtitle = false;
        $this->args->has_badge = false;

        $this->item_classes = array_diff($this->item_classes, array('quadmenu-item-type-product_cat', 'quadmenu-has-icon', 'quadmenu-has-badge', 'quadmenu-has-subtitle', 'quadmenu-has-background', 'quadmenu-dropdown-left', 'quadmenu-dropdown-right'));

        $this->item_classes[] = 'quadmenu-item-type-product';
        $this->item_classes[] = 'quadmenu-item-type-panel';
        $this->item_classes[] = 'woocommerce';
        ?>
        <li <?php echo $this->get_item_id(); ?> <?php echo $this->get_item_classes(); ?>>
            <?php echo parent::get_link(); ?>
        </li>

        <?php
    }

    function get_link() {

        $item_output = $atts = '';

        foreach ($this->item_atts as $attr => $value) {

            if (empty($value))
                continue;

            if ($attr == 'href') {
                $value = esc_url($value);
            } elseif ($attr == 'title') {
                $value = sanitize_title($value);
            } else {
                $value = esc_attr($value);
            }

            $atts .= ' ' . esc_attr($attr) . '="' . $value . '"';
        }

        ob_start();
        ?>
        <?php echo $this->args->before; ?>
        <?php if ($this->args->has_category) : ?>
            <a <?php echo $atts; ?>>
                <span class="quadmenu-item-content">
                    <?php echo $this->args->link_before; ?>
                    <?php echo $this->get_icon(); ?>
                    <?php echo $this->get_title(); ?>
                    <?php echo $this->get_badge(); ?>
                    <?php echo $this->get_subtitle(); ?>
                    <?php echo $this->args->link_after; ?>
                </span>
            </a>
        <?php endif; ?>
        <?php if ($this->item->items > 0 || !$this->args->has_category) : ?>
            <?php echo $this->get_products(); ?>
        <?php endif; ?>
        <?php echo $this->args->after; ?>
        <?php
        return ob_get_clean();
    }

    function get_description() {
        if (0 < $this->depth && ($this->item->product_description === 'on')) {

            $post = get_post($this->item->object_id);

            if (isset($post->post_excerpt)) {
                ob_start();
                ?>
                <span class="quadmenu-description">
                    <?php echo wp_trim_words(wpautop($this->clean_item_content($post->post_excerpt ? $post->post_excerpt : $post->post_content)), 10); ?>
                </span>
                <?php
                return ob_get_clean();
            }
        }
    }

}
