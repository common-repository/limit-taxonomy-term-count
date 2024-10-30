<?php
/*
Plugin Name: Limit taxonomy term count
Description: Allows the use of the "limit" argument when calling register_taxonomy
Version: 1.0
Author: Felix Eve
License: GPLv2 or later
Text Domain: limit-taxonomy-term-count
*/

class LimitTaxonomyTermCount
{
    /**
     * An array of all javascript data we need to add to the page.
     * Is built up during each call to metabox and then set using wp_localize_script in the wp_footer function.
     *
     * @var array
     */
    private $javascript_data = [];

    /**
     * LimitTaxonomyTermCount constructor.
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes'], 10);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('save_post', [$this, 'save_post']);
    }

    /**
     * Add meta boxes callback to switch out any with the limit attribute for our custom one.
     */
    public function add_meta_boxes() {
        foreach (get_taxonomies(['show_ui' => true], 'object') as $taxonomy) {
            if (empty($taxonomy->limit) || $taxonomy->limit == -1 || $taxonomy->hierarchical == true) {
                continue;
            }
            foreach ($taxonomy->object_type as $object_type) {
                $box_name = 'tagsdiv-' . $taxonomy->name;
                remove_meta_box($box_name, $object_type, 'side');
                add_meta_box($box_name, $taxonomy->labels->singular_name, [$this, 'metabox'], $object_type, 'side', 'default', ['taxonomy' => $taxonomy->name]);
            }
        }
    }

    /**
     * Build the custom taxonomy metabox that allow limiting the number of tags added.
     *
     * @param $post
     * @param $box
     */
    public function metabox($post, $box) {

        $taxonomy_name = $box['args']['taxonomy'];
        $taxonomy = get_taxonomy($taxonomy_name);
        $disabled = !current_user_can($taxonomy->cap->assign_terms);
        $select_name = $taxonomy_name . '-lttc-taxonomy-select[]';
        $all_terms = get_terms(['taxonomy' => $taxonomy_name]);
        $post_terms = wp_get_post_terms($post->ID, $taxonomy_name);

        $select_attributes = [
            'name'     => $select_name,
            'id'       => $select_name,
            'multiple' => true,
        ];

        if ($disabled) {
            $select_attributes['disabled'] = 'disabled';
        }

        $build = $this->create_tag('select', $select_attributes);

        // Loop terms to add options
        foreach ($all_terms as $term) {
            $option_attributes = ['value' => $term->name];
            if ($this->term_is_selected($term, $post_terms)) {
                $option_attributes['selected'] = 'selected';
            }
            $build .= $this->create_tag('option', $option_attributes) . $term->name . '</option>';
        }
        $build .= '</select>';

        // Build the JS data including the select2 options.
        $data[$taxonomy_name] = [
            'name'            => $select_name,
            'select2_options' => [
                'tags'                   => true,
                'disabled'               => $disabled,
                'maximumSelectionLength' => $taxonomy->limit,
                'multiple'               => true,
                'width'                  => '100%',
            ],
        ];

        // Allow other plugins to alter the JS data before we set it.
        $data = apply_filters('limit_taxonomy_term_count_js_data', $data);

        // Combine these settings with any settings for other metaboxes that have already been set.
        $this->javascript_data = array_merge($this->javascript_data, $data);

        wp_localize_script('limit-taxonomy-term-count', 'lttc_data', $this->javascript_data);

        echo $build;

    }

    /**
     * Check if the term passed in is in the array of post terms.
     *
     * @param $term
     * @param $post_terms
     * @return bool
     */
    private function term_is_selected($term, $post_terms) {
        foreach ($post_terms as $post_term) {
            if ($post_term->term_id == $term->term_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build html for a tag with supplied attributes.
     *
     * @param $name
     * @param $attributes
     * @return string
     */
    private function create_tag($name, $attributes) {
        $build = '<' . $name;
        foreach ($attributes as $key => $value) {
            if (is_bool($value) && $value) {
                $build .= ' ' . $key;
            } else if (!is_bool($value)) {
                $build .= ' ' . $key . '="' . $value . '"';
            }
        }
        $build .= '>';
        return $build;
    }

    /**
     * Callback for after a post has been saved.
     * Used to save the taxonomy terms posted from our custom metabox.
     * @param $post_id
     */
    public function save_post($post_id) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'lttc-taxonomy-select') !== false) {
                $taxonomy_name = str_replace('-lttc-taxonomy-select', '', $key);
                wp_set_post_terms($post_id, $value, $taxonomy_name);
            }
        }
    }

    /**
     * Callback to enqueue scripts and styles.
     * Note we add our custom JS to the footer so we can localize it during the add_meta_boxes action.
     */
    public function admin_enqueue_scripts() {

        // Only enqueue select2 script if it's not already been added.
        if (!wp_script_is('select2', 'enqueued')) {
            wp_enqueue_script('select2', plugin_dir_url(__FILE__) . 'vendor/select2/js/select2.min.js', ['jquery']);
        }

        // Only enqueue select2 style if it's not already been added.
        if (!wp_style_is('select2', 'enqueued')) {
            wp_enqueue_style('select2', plugin_dir_url(__FILE__) . 'vendor/select2/css/select2.min.css');
        }

        wp_enqueue_script('limit-taxonomy-term-count', plugin_dir_url(__FILE__) . 'js/limit-taxonomy-term-count.js', ['jquery', 'select2'], false, true);

    }
}

$LimitTaxonomyTermCount = new LimitTaxonomyTermCount();
